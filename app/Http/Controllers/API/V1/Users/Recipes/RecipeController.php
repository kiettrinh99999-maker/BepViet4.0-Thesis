<?php

namespace App\Http\Controllers\API\V1\Users\Recipes;

use App\Models\Recipe;
use App\Models\Step; // Quan trọng: Thêm dòng này
use App\Http\Controllers\API\V1\BaseCRUDController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class RecipeController extends BaseCRUDController
{
    protected function setModel()
    {
        $this->model = Recipe::class;
    }

    protected function rules($id = null)
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cooking_time' => 'required|integer',
            'serving' => 'required|integer',
            'region_id' => 'nullable|exists:regions,id',
            'difficulty_id' => 'nullable|exists:difficulties,id',
            'recipe_category_id' => 'required|exists:recipe_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.quantity' => 'required|numeric',
            'ingredients.*.unit' => 'required|string|max:50',

            'steps' => 'required|array',
            'steps.*.step_name' => 'required|string',
            'steps.*.images' => 'nullable|array',
            'steps.*.images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function index()
    {
        $request = request(); 
        $userRegionId = $request->query('mock_user_region');

        $query = $this->model::with([
            'user:id,username',
            'region:id,name',
            'difficulty:id,name',
            'event:id,name',
        ])->where('status', 'active')
        ->withAvg('rates', 'score') 
        ->withCount('rates');

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('difficulty_id')) {
            $query->where('difficulty_id', $request->difficulty_id);
        }

        if ($userRegionId && !$request->filled('region_id')) {
            $query->orderByRaw("region_id = ? DESC", [$userRegionId]);
        }

        $data = $query->latest()->paginate(12);
        return $this->sendResponse($data, 'Lấy danh sách công thức thành công');
    }

     public function show( $key) {

         $request=request();

        $query = $this->model::with([

            'user.profile',

            'ingredients',

            'steps.step_images',

            'difficulty',

            'recipe_comments' => function($q) {

                $q->with('user.profile')->latest();

            }

    ])

    ->withAvg('rates as rating_avg', 'score')

    ->withCount('rates');

    if (is_numeric($key)) {

        $recipe = $query->find($key);

    } else {

        $idPart = explode('-', $key)[0];

        $recipe = is_numeric($idPart) ? $query->find($idPart) : $query->where('title_slug', $key)->first();

    }

    if (!$recipe) {

        return $this->sendError('Không tìm thấy công thức', [], 404);

    }
    $followerId = $request->query('user_id');

    $isFollowed = false;

    if ($followerId && $followerId != $recipe->user_id) {

    $followRecord = \App\Models\Follow::where('follower_id', $followerId)

                        ->where('following_id', $recipe->user_id)

                        ->first();
    if (!$followRecord) {

        \DB::table('follows')->insert([

            'follower_id' => $followerId,

            'following_id' => $recipe->user_id,

            'status'      => 'inactive',

            'created_at'  => now(),

            'updated_at'  => now()

        ]);

        $isFollowed = false;

    } else {

        $isFollowed = ($followRecord->status === 'active');

    }

}

    $recipe->is_followed = $isFollowed;

    return $this->sendResponse($recipe, 'Lấy chi tiết thành công');

}

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), $this->rules());
    if ($validator->fails()) {
        return $this->sendError('Lỗi dữ liệu đầu vào', $validator->errors(), 422);
    }

    DB::beginTransaction();
    $savedImagePaths = [];

    try {
        $recipeData = $request->except(['image', 'ingredients', 'steps']);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/recipes', 'public');
            $recipeData['image_path'] = "/". $path;
            $savedImagePaths[] = $path;
        }

        $recipeData['title_slug'] = Str::slug($request->title) . '-' . time();
        $recipeData['user_id']    = auth()->id() ?? 1; 
        $recipeData['status']     = 'pending';

        $recipe = Recipe::create($recipeData);
        foreach ($request->ingredients as $ingData) {
            $ingredientModel = \App\Models\Ingredient::firstOrCreate(
                ['name' => $ingData['name']],
                ['name_slug' => Str::slug($ingData['name'])]
            );
            if (isset($ingData['image']) && $ingData['image'] instanceof \Illuminate\Http\UploadedFile) {
                $ingImgPath = $ingData['image']->store('uploads/ingredients', 'public');
                $ingredientModel->image_path ="/".$ingImgPath;
                $ingredientModel->save();
                $savedImagePaths[] = $ingImgPath;
            }
            $recipe->ingredients()->attach($ingredientModel->id, [
                'quantity' => $ingData['quantity'],
                'unit'     => $ingData['unit']
            ]);
        }
        if ($request->has('steps')) {
            foreach ($request->steps as $stepData) {
                $step = new Step([
                    'step_name' => $stepData['step_name'],
                    'recipe_id' => $recipe->id
                ]);
                $step->save();
                if (isset($stepData['images']) && is_array($stepData['images'])) {
                    foreach ($stepData['images'] as $imgFile) {
                        $stepPath = $imgFile->store('uploads/steps', 'public');
                        $savedImagePaths[] = "/".$stepPath;
                        
                        $step->step_images()->create([
                            'image_path' =>$stepPath
                        ]);
                    }
                }
            }
        }

        DB::commit();
        $recipe->load(['ingredients', 'steps.step_images', 'user:id,username']);
        return $this->sendResponse($recipe, 'Tạo công thức thành công, vui lòng chờ duyệt.', 201);
    } catch (Exception $e) {
        DB::rollBack();
        foreach ($savedImagePaths as $path) {
            Storage::disk('public')->delete($path);
        }
        return $this->sendError('Lỗi hệ thống', [$e->getMessage()], 500);
    }
}
}