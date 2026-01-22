<?php

namespace App\Http\Controllers\API\V1\Admins\ManageCategory;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\Event; 
use Illuminate\Http\Request;

class ManageEventController extends BaseCRUDController
{
    /**
     * 1. Khai báo Model Event để Controller biết đang làm việc với bảng nào
     */
    protected function setModel()
    {
        $this->model = Event::class;
    }

    /**
     * 2. Validate dữ liệu đầu vào (Giống hệt Region)
     * - Tên: bắt buộc, không trùng lặp
     * - Trạng thái: active/inactive
     */
    protected function rules($id = null)
    {
        return [
            'name'   => 'required|string|max:255|unique:events,name,' . $id,
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * 3. Lấy danh sách (Đã bỏ tìm kiếm theo yêu cầu)
     * GET /api/events
     */
    public function index()
    {
        // Dùng helper request() thay vì truyền tham số để tránh lỗi class cha
        $request = request(); 
        $query = $this->model::query();

        // Chỉ giữ lại lọc theo trạng thái (nếu sau này cần tab Active/Inactive)
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Sắp xếp: Mới nhất lên đầu
        $query->orderBy('created_at', 'desc');
        
        // Lấy dữ liệu
        $data = $query->get();

        return $this->sendResponse($data, 'Lấy danh sách sự kiện thành công');
    }

    // Các hàm store (Thêm) và update (Sửa) đã có sẵn trong BaseCRUDController 
    // và sẽ tự động dùng rules() ở trên. Bạn không cần viết lại.
}