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
}
