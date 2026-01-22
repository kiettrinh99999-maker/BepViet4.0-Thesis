<?php

namespace App\Http\Controllers\API\V1\Users\Recipes;

use App\Models\Recipe;
use App\Models\Step;
use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\User;
use App\Models\Follow;
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
            'event_id' => 'nullable|exists:events,id', // Thêm event_id nếu cần
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

        $query = Recipe::with([
            'user:id,username',
            'region:id,name',
            'difficulty:id,name',
            'event:id,name',
        ])
        ->where('status', 'active')
        ->withAvg('rates', 'score')
        ->withCount('rates')
        ->where(function($q) {
            $q->whereDoesntHave('recipe_reports', function($subQuery) {
                $subQuery->where('status', 'reviewed');
            });
        });
        
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('difficulty_id')) {
            $query->where('difficulty_id', $request->difficulty_id);
        }
        if ($request->filled('keyword')) {
        $keyword = $request->keyword;
        $query->where(function($q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('title_slug', 'like', '%' . $keyword . '%');
        });
    }
        if ($request->filled('min_time')) {
            $query->where('cooking_time', '>=', $request->min_time);
        }
        if ($request->filled('max_time')) {
            $query->where('cooking_time', '<=', $request->max_time);
        }
        if ($request->filled('mock_user_region') && !$request->filled('region_id')) {
            $query->orderByRaw('CASE WHEN region_id = ? THEN 0 ELSE 1 END', [$request->mock_user_region]);
        }

        return $this->sendResponse(
            $query->latest()->paginate(12),
            'Lấy danh sách công thức thành công'
        );
    }
   public function index_pending()
    {
        $request = request();

        $query = Recipe::with([
            'user:id,username',
            'region:id,name',
            'difficulty:id,name',
            'event:id,name',
        ])
        ->where('status', 'pending');
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('difficulty_id')) {
            $query->where('difficulty_id', $request->difficulty_id);
        }
        if ($request->filled('min_time')) {
            $query->where('cooking_time', '>=', $request->min_time);
        }
        if ($request->filled('max_time')) {
            $query->where('cooking_time', '<=', $request->max_time);
        }
        if ($request->filled('mock_user_region') && !$request->filled('region_id')) {
            $query->orderByRaw('CASE WHEN region_id = ? THEN 0 ELSE 1 END', [$request->mock_user_region]);
        }

        return $this->sendResponse(
            $query->latest()->paginate(12),
            'Lấy danh sách công thức thành công'
        );
    }

    /**
     * Chi tiết công thức
     */
    public function show($key)
    {
        $request = request();

        $query = Recipe::with([
            'user.profile',
            'ingredients',
            'steps.step_images',
            'difficulty',
            'recipe_comments' => function ($q) {
                $q->with('user.profile')->latest();
            }
        ])
        ->withAvg('rates as rating_avg', 'score')
        ->withCount('rates');

        $recipe = is_numeric($key)
            ? $query->find($key)
            : $query->where('title_slug', $key)->first();

        if (!$recipe) {
            return $this->sendError('Không tìm thấy công thức', [], 404);
        }

        // Kiểm tra follow
        $followerId = $request->query('user_id');
        $recipe->is_followed = false;

        if ($followerId && $followerId != $recipe->user_id) {
            $follow = Follow::where('follower_id', $followerId)
                ->where('following_id', $recipe->user_id)
                ->first();

            $recipe->is_followed = $follow && $follow->status === 'active';
        }

        return $this->sendResponse($recipe, 'Lấy chi tiết thành công');
    }

    /**
     * Xóa công thức (soft delete)
     */
    public function destroy($id)
    {
        $user = User::find(2); // giả lập user

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response()->json(['message' => 'Không tìm thấy bài viết'], 404);
        }

        if ($recipe->user_id !== $user->id) {
            return response()->json(['message' => 'Không có quyền xóa'], 403);
        }

        $recipe->update(['status' => 'inactive']);

        return response()->json(['message' => 'Xóa thành công']);
    }

    /**
     * Tạo công thức mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Lỗi dữ liệu đầu vào', $validator->errors(), 422);
        }
        
        DB::beginTransaction();
        $savedImagePaths = [];

        try {
            // Chuẩn bị dữ liệu recipe
            $recipeData = $request->only([
                'title', 'description', 'cooking_time', 'serving',
                'region_id', 'difficulty_id', 'recipe_category_id', 'event_id'
            ]);
            
            // Xử lý ảnh chính
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('uploads/recipes', 'public');
                $recipeData['image_path'] = "/" . $path;
                $savedImagePaths[] = $path;
            }
            
            // Tạo slug và thêm thông tin user
            $recipeData['title_slug'] = Str::slug($request->title) . '-' . time();
            $recipeData['user_id']    = auth()->id() ?? 1; 
            $recipeData['status']     = 'pending';
            
            // Tạo recipe
            $recipe = Recipe::create($recipeData);
            
            // Xử lý nguyên liệu
            foreach ($request->ingredients as $ingData) {
                $ingredientModel = \App\Models\Ingredient::firstOrCreate(
                    ['name' => $ingData['name']],
                    ['name_slug' => Str::slug($ingData['name'])]
                );
                
                // Xử lý ảnh nguyên liệu nếu có
                if (isset($ingData['image']) && $ingData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $ingImgPath = $ingData['image']->store('uploads/ingredients', 'public');
                    $ingredientModel->image_path = "/" . $ingImgPath;
                    $ingredientModel->save();
                    $savedImagePaths[] = $ingImgPath;
                }
                
                // Attach nguyên liệu vào recipe
                $recipe->ingredients()->attach($ingredientModel->id, [
                    'quantity' => $ingData['quantity'],
                    'unit'     => $ingData['unit']
                ]);
            }
            
            // Xử lý các bước thực hiện
            if ($request->has('steps') && is_array($request->steps)) {
                foreach ($request->steps as $stepData) {
                    $step = new Step([
                        'step_name' => $stepData['step_name'],
                        'recipe_id' => $recipe->id
                    ]);
                    $step->save();
                    
                    // Xử lý ảnh từng bước nếu có
                    if (isset($stepData['images']) && is_array($stepData['images'])) {
                        foreach ($stepData['images'] as $imgFile) {
                            if ($imgFile instanceof \Illuminate\Http\UploadedFile) {
                                $stepPath = $imgFile->store('uploads/steps', 'public');
                                $savedImagePaths[] = $stepPath;
                                
                                $step->step_images()->create([
                                    'image_path' => $stepPath
                                ]);
                            }
                        }
                    }
                }
            }
            
            DB::commit();
            
            // Load relations để trả về đầy đủ thông tin
            $recipe->load(['ingredients', 'steps.step_images', 'user:id,username']);
            
            return $this->sendResponse($recipe, 'Tạo công thức thành công, vui lòng chờ duyệt.', 201);
            
        } catch (Exception $e) {
            DB::rollBack();
            
            // Xóa các ảnh đã upload nếu có lỗi
            foreach ($savedImagePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            
            return $this->sendError('Lỗi hệ thống', ['error' => $e->getMessage()], 500);
        }
    }


    //
    /**
     * Cập nhật trạng thái công thức (Duyệt/Từ chối/Ẩn)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,pending,rejected,inactive',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Dữ liệu không hợp lệ', $validator->errors(), 422);
        }
        $recipe = Recipe::find($id);
        if (!$recipe) {
            return $this->sendError('Không tìm thấy công thức', [], 404);
        }
        try {
            $oldStatus = $recipe->status;
            $recipe->status = $request->status;
            $recipe->save();
            return $this->sendResponse(
                $recipe, 
                "Cập nhật trạng thái thành công từ '{$oldStatus}' sang '{$recipe->status}'"
            );
        } catch (Exception $e) {
            return $this->sendError('Lỗi hệ thống khi cập nhật trạng thái', ['error' => $e->getMessage()], 500);
        }
    }
}