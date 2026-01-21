<?php

namespace App\Http\Controllers\API\V1\Users\Recipes;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Http\Controllers\API\V1\BaseCRUDController;
class RecipeController extends BaseCRUDController
{
    protected function setModel(){
        $this->model=Recipe::class;
    }
    protected function rules($id = null)
    {
        return [
            'title' => 'required|max:255',
            'cooking_time' => 'required|integer',
            // ... thêm rule khác
        ];
    }

    public function index(){
        // Lấy danh sách công thức mới nhất kèm id và name của các bảng liên quan
        $data = Recipe::with([
            'user:id,username,created_at',
            'region:id,name',
            'difficulty:id,name',
            'event:id,name',
            'recipe_category:id,name'
        ])
        ->latest()
        ->paginate(10);
        return $this->sendResponse($data, 'Lấy danh sách công thức thành công');
    }
}
