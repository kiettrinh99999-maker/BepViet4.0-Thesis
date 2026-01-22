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
        
        // Lọc theo thời gian nấu
        if ($request->filled('min_time')) {
            $query->where('cooking_time', '>=', $request->min_time);
        }
        if ($request->filled('max_time')) {
            $query->where('cooking_time', '<=', $request->max_time);
        }
        
        // Sắp xếp theo vùng miền của user nếu có
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

}
