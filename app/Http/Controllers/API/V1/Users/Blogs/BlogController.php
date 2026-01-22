<?php

namespace App\Http\Controllers\API\V1\Users\Blogs;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends BaseCRUDController
{
    // 1. Khai báo Model sử dụng (Bắt buộc theo BaseCRUDController)
    protected function setModel()
    {
        $this->model = Blog::class;
    }

    // 2. Khai báo Rules validate (Bắt buộc)
    protected function rules($id = null)
    {
        return [
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'blog_category_id' => 'required|integer|exists:blog_categories,id',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Override lại hàm index để lấy thêm quan hệ và lọc
     */
   public function index()
    {
        $request = request();
    // 1. Query cơ bản kèm quan hệ
    $query = $this->model::with(['blog_category', 'user.profile'])
        ->where('status', 'active');

    // 2. Xử lý lọc theo danh mục (Server-side filtering)
    // Nếu client gửi category_id lên và khác 'all'
    if ($request->has('category_id') && $request->category_id != 'all') {
        $query->where('blog_category_id', $request->category_id);
    }

    // 3. Phân trang (Server-side pagination)
    // paginate(6) tự động lấy tham số '?page=' từ URL
    $blogs = $query->orderBy('created_at', 'desc')->paginate(6);

    // 4. Trả về cấu trúc JSON chuẩn có kèm thông tin phân trang
    return response()->json([
        'success' => true,
        'message' => 'Lấy danh sách thành công',
        'data'    => $blogs->items(), // Chỉ lấy mảng bài viết
        'pagination' => [
            'total'        => $blogs->total(),
            'per_page'     => $blogs->perPage(),
            'current_page' => $blogs->currentPage(),
            'last_page'    => $blogs->lastPage(),
            'from'         => $blogs->firstItem(),
            'to'           => $blogs->lastItem(),
        ]
    ]);
}

    /**
     * API riêng để lấy danh sách danh mục cho Frontend
     * GET /api/blog-categories
     */
    public function getCategories()
    {
        $categories = BlogCategory::where('status', 'active')->get();
        return $this->sendResponse($categories, 'Lấy danh mục thành công.');
    }
}