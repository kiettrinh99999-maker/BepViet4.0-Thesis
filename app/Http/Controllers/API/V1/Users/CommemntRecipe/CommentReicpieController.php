<?php

namespace App\Http\Controllers\API\V1\Users\CommemntRecipe;

use Illuminate\Http\Request;
use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\RecipeComment;
use App\Models\Rate;
use App\Models\Recipe;
use App\Events\NewComment; // Đảm bảo đã import Event
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class CommentReicpieController extends BaseCRUDController
{
    protected function setModel()
    {
        $this->model = RecipeComment::class;
    }

    protected function rules($id = null)
    {
        return [
            'recipe_id' => 'required|exists:recipes,id',
            'user_id'   => 'required|exists:users,id', // Giữ nguyên
            'content'   => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:recipe_comments,id',
        ];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Lỗi dữ liệu đầu vào', $validator->errors(), 422);
        }
        try {
            $comment = $this->model::create([
                'recipe_id' => $request->recipe_id,
                'user_id'   => $request->user_id,
                'content'   => $request->content,
                'parent_id' => $request->parent_id ?? null,
            ]);
            $comment->load('user.profile');
            if (class_exists('App\Events\NewComment')) {
                broadcast(new \App\Events\NewComment($comment))->toOthers();
            }
            return $this->sendResponse($comment, 'Bình luận thành công.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Lỗi Server', ['error' => $e->getMessage()], 500);
        }
    }
    
    // Hàm rate giữ nguyên
    public function rate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipe_id' => 'required|exists:recipes,id',
            'user_id'   => 'required|exists:users,id',
            'rating'    => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Dữ liệu đánh giá không hợp lệ', $validator->errors(), 422);
        }

        try {
            $rate = Rate::updateOrCreate(
                [
                    'user_id'   => $request->user_id,
                    'recipe_id' => $request->recipe_id
                ],
                [
                    'score'     => $request->rating 
                ]
            );

            $recipe = Recipe::find($request->recipe_id);
            $newAvg = $recipe->rates()->avg('score'); 
            $count  = $recipe->rates()->count();

            return $this->sendResponse([
                'my_rating' => $rate->score,
                'new_avg'   => round($newAvg, 1),
                'count'     => $count
            ], 'Đánh giá thành công.');

        } catch (\Exception $e) {
            return $this->sendError('Lỗi Server', ['error' => $e->getMessage()], 500);
        }
    }
public function getRecipeComments($recipeId)
{
    try {
        $comments = RecipeComment::with(['user.profile', 'replies.user.profile'])
            ->where('recipe_id', $recipeId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($comments, 'Danh sách bình luận.');
        
    } catch (\Exception $e) {
        return $this->sendError('Lỗi Server', ['error' => $e->getMessage()], 500);
    }   
}
}