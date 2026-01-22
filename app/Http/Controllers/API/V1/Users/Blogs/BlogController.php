<?php

namespace App\Http\Controllers\API\V1\Users\Blogs;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Http\Request;

class BlogController extends BaseCRUDController
{
    // 1. Khai báo Model sử dụng
    protected function setModel()
    {
        $this->model = Blog::class;
    }

    // 2. Khai báo Rules validate (Dùng cho store/update sau này)
    protected function rules($id = null)
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'blog_category_id' => 'required|integer|exists:blog_categories,id',
            // Thêm các rule khác nếu cần
        ];
    }

    /**
     * Ghi đè phương thức destroy của BaseCRUDController
     * Để thực hiện Xóa mềm (đổi status) và Check quyền user
     */
    public function destroy($id)
    {
        // Lấy user hiện tại (hoặc User 2 để test)
        $user = request()->user() ?? User::find(2);

        // Tìm bài viết
        $blog = Blog::find($id);

        if (!$blog) {
            return $this->sendError('Không tìm thấy bài viết');
        }

        // Kiểm tra quyền chính chủ
        if ($blog->user_id !== $user->id) {
            return $this->sendError('Bạn không có quyền xóa bài này', [], 403);
        }

        // Thực hiện xóa mềm (Soft delete bằng cách đổi status)
        $blog->status = 'inactive';
        $blog->save();

        return $this->sendResponse([], 'Đã xóa bài viết thành công');
    }
}