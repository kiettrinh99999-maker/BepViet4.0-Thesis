<?php

namespace App\Http\Controllers\API\V1\Users\Blogs;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class BlogController extends BaseCRUDController
{
    // 1. Khai báo Model sử dụng
    protected function setModel()
    {
        $this->model = Blog::class;
    }

    // 2. Rules validate
    protected function rules($id = null)
    {
        return [
            'title'            => 'required|string|max:255',
            'description'      => 'required|string', 
            'blog_category_id' => 'required|integer|exists:blog_categories,id',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ];
    }

    /**
     * API Lấy danh sách bài viết (Public)
     * GET /api/blogs
     */
    public function index()
    {
        $request = request();
        
        // Load quan hệ: category và user profile
        $query = $this->model::with(['blog_category', 'user.profile'])
            ->where('status', 'active');

        // Lọc theo danh mục
        if ($request->has('category_id') && $request->category_id != 'all') {
            $query->where('blog_category_id', $request->category_id);
        }

        // Lọc theo từ khóa tìm kiếm
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%");
        }

        // Phân trang 6 bài/trang
        $blogs = $query->orderBy('created_at', 'desc')->paginate(6);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thành công',
            'data'    => $blogs->items(),
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
     * API Tạo bài viết mới (Yêu cầu đăng nhập)
     * POST /api/auth/add-blog
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Lỗi dữ liệu đầu vào', $validator->errors(), 422);
        }

        $data = $request->all();

        $data['user_id'] = $request->user()->id; // Lấy ID người đang đăng nhập
        
        $slug = Str::slug($request->title);
        if (Blog::where('title_slug', $slug)->exists()) {
            $slug .= '-' . time();
        }
        $data['title_slug'] = $slug;
        $data['status'] = 'active';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'blog_' . $data['user_id'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            // Lưu vào storage/app/public/uploads/blogs
            $path = $file->storeAs('uploads/blogs', $filename, 'public');
            $data['image_path'] = 'uploads/blogs/' . $filename;
        }

        $item = $this->model::create($data);

        return $this->sendResponse($item, 'Đăng bài viết thành công.', 201);
    }

    /**
     * API Lấy danh mục blog (Public)
     * GET /api/categories-blog
     */
    public function getCategories()
    {
        $categories = BlogCategory::where('status', 'active')->get();
        return $this->sendResponse($categories, 'Lấy danh mục thành công.');
    }

     
}