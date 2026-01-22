<?php

namespace App\Http\Controllers\API\V1\Admins;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ManageUserController extends Controller
{
    /**
     * Lấy danh sách người dùng (kèm tìm kiếm & bộ lọc)
     * GET /api/admin/users
     */
    public function index(Request $request)
    {
        // Eager load profile để lấy tên, sđt, ảnh
        $query = User::with('profile');
        $query->where('role', '!=', 'admin');
      
        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thành công',
            'data'    => $users->items(),
        ]);
    }

    /**
     * Khóa / Mở khóa tài khoản
     * PATCH /api/admin/users/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        // Tìm user, nếu không thấy trả về lỗi
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false, 
                'message' => 'Người dùng không tồn tại'
            ], 404);
        }

        // Không cho phép tự khóa chính mình (nếu đang login là admin này)
        if ($user->id == request()->user()->id) {
            return response()->json([
                'success' => false, 
                'message' => 'Bạn không thể khóa tài khoản của chính mình'
            ], 403);
        }

        // Đảo ngược trạng thái
        $newStatus = ($user->status === 'active') ? 'inactive' : 'active';
        $user->status = $newStatus;
        $user->save();

        $msg = ($newStatus === 'active') ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản';

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data'    => [
                'id' => $user->id,
                'status' => $user->status
            ]
        ]);
    }
}