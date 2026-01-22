<?php

namespace App\Http\Controllers\API\V1\Admins\ManageCategory;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\BlogCategory; // <--- Model Danh mục Blog
use Illuminate\Http\Request;

class ManageBlogCategoryController extends BaseCRUDController
{
    
    protected function setModel()
    {
        $this->model = BlogCategory::class;
    }

    
    protected function rules($id = null)
    {
        return [
            'name'   => 'required|string|max:255|unique:blog_categories,name,' . $id,
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

        return $this->sendResponse($data, 'Lấy danh mục blog thành công');
    }
}