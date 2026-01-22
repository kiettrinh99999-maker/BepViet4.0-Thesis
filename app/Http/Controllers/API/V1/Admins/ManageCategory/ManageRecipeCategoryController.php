<?php

namespace App\Http\Controllers\API\V1\Admins\ManageCategory;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\RecipeCategory; // <--- Model Danh mục Món ăn (Meal)
use Illuminate\Http\Request;

class ManageRecipeCategoryController extends BaseCRUDController
{
    /**
     * 1. Khai báo Model RecipeCategory
     */
    protected function setModel()
    {
        $this->model = RecipeCategory::class;
    }

    /**
     * 2. Validate
     * Check trùng tên trong bảng 'recipe_categories'
     */
    protected function rules($id = null)
    {
        return [
            'name'   => 'required|string|max:255|unique:recipe_categories,name,' . $id,
            'status' => 'required|in:active,inactive',
        ];
    }

    
    public function index()
    {
        $request = request();
        $query = $this->model::query();

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $query->orderBy('created_at', 'desc');
        $data = $query->get();

        return $this->sendResponse($data, 'Lấy danh mục bữa ăn thành công');
    }
}