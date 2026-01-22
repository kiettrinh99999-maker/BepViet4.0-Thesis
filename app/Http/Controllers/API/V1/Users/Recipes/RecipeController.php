<?php

namespace App\Http\Controllers\API\V1\Users\Recipes;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\Recipe;
use App\Models\Step;
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
}
