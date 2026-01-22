<?php

namespace App\Http\Controllers\API\V1\Users\Collections;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CollectionController extends BaseCRUDController
{
    /**
     * 1. Khai báo Model
     */
    protected function setModel()
    {
        $this->model = Collection::class;
    }

    /**
     * 2. Khai báo Rules Validate
     */
    protected function rules($id = null)
    {
        return [
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * 3. Hàm STORE: Tạo bộ sưu tập mới
     */
    public function store(Request $request)
    {
        // 1. Lấy User từ Token (Đã qua middleware auth:sanctum)
        $user = $request->user();

        // 2. Validate
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Dữ liệu không hợp lệ', $validator->errors(), 422);
        }

        // 3. Khởi tạo model
        $collection = new Collection();
        $collection->fill($request->except('image'));
        $collection->user_id = $user->id; // Gán ID của người đang đăng nhập
        $collection->status = 'active';

        // 4. Xử lý Upload Ảnh
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'col_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/collections', $filename, 'public');
            $collection->image_path = $path;
        }

        $collection->save();

        return $this->sendResponse($collection, 'Tạo bộ sưu tập thành công', 201);
    }

    /**
     * 4. Hàm UPDATE: Cập nhật bộ sưu tập
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        $collection = Collection::find($id);

        if (!$collection) {
            return $this->sendError('Không tìm thấy bộ sưu tập');
        }

        // --- CHECK QUYỀN SỞ HỮU ---
        if ($collection->user_id !== $user->id) {
            return $this->sendError('Bạn không có quyền chỉnh sửa bộ sưu tập này', [], 403);
        }

        // Validate
        $validator = Validator::make($request->all(), $this->rules($id));
        if ($validator->fails()) {
            return $this->sendError('Dữ liệu không hợp lệ', $validator->errors(), 422);
        }

        // Cập nhật tên
        $collection->name = $request->name;

        // Xử lý Upload Ảnh mới
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ
            if ($collection->image_path && Storage::disk('public')->exists($collection->image_path)) {
                Storage::disk('public')->delete($collection->image_path);
            }

            // Lưu ảnh mới
            $file = $request->file('image');
            $filename = 'col_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/collections', $filename, 'public');
            $collection->image_path = $path;
        }

        $collection->save();

        return $this->sendResponse($collection, 'Cập nhật thành công');
    }

    /**
     * 5. Hàm DESTROY: Xóa bộ sưu tập (Soft Delete)
     */
    public function destroy($id)
    {
        // Lấy user từ token (Sửa lại cú pháp chuẩn)
        $user = request()->user();
        
        $collection = Collection::find($id);

        if (!$collection) {
            return $this->sendError('Không tìm thấy bộ sưu tập');
        }

        // --- CHECK QUYỀN SỞ HỮU ---
        if ($collection->user_id !== $user->id) {
            return $this->sendError('Bạn không có quyền xóa bộ sưu tập này', [], 403);
        }

        // Xóa mềm (Soft Delete)
        $collection->status = 'inactive';
        $collection->save();

        return $this->sendResponse([], 'Đã xóa bộ sưu tập');
    }

    /**
     * 6. SHOW: Xem chi tiết (Có thể public hoặc private tùy logic)
     */
    public function show($id)
    {
        // Lấy collection kèm theo danh sách recipes active
        $collection = Collection::with(['recipes' => function($query) {
            $query->wherePivot('status', 'active') 
                  ->select('recipes.id', 'recipes.title', 'recipes.image_path', 'recipes.cooking_time', 'recipes.difficulty_id')
                  ->with('difficulty')
                  ->withCount('rates')
                  ->withAvg('rates', 'score');
        }])->find($id);

        if (!$collection) {
            return $this->sendError('Không tìm thấy bộ sưu tập');
        }

        // (Tuỳ chọn) Nếu muốn chỉ chủ nhân mới xem được thì thêm check ở đây
        // if ($collection->user_id !== request()->user()->id) return 403...

        return $this->sendResponse($collection, 'Lấy chi tiết thành công');
    }

    /**
     * 7. REMOVE RECIPE: Xóa công thức khỏi bộ sưu tập
     */
    public function removeRecipe(Request $request, $id)
    {
        $user = $request->user(); // Lấy user hiện tại

        $collection = Collection::find($id);
        
        if (!$collection) {
            return $this->sendError('Bộ sưu tập không tồn tại');
        }

        // --- CHECK QUYỀN SỞ HỮU (Quan trọng: Không cho xóa món của người khác) ---
        if ($collection->user_id !== $user->id) {
            return $this->sendError('Bạn không có quyền chỉnh sửa bộ sưu tập này', [], 403);
        }

        // Update bảng trung gian (recipe_collections) set status = inactive
        $collection->recipes()->updateExistingPivot($request->recipe_id, ['status' => 'inactive']);

        return $this->sendResponse([], 'Đã xóa công thức khỏi bộ sưu tập');
    }
}