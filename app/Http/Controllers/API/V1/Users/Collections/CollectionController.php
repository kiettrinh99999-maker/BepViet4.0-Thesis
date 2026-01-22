<?php

namespace App\Http\Controllers\API\V1\Users\Collections;

use App\Http\Controllers\API\V1\BaseCRUDController; // <--- Kế thừa cái này
use App\Models\Collection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CollectionController extends BaseCRUDController
{
    /**
     * 1. Khai báo Model cho BaseController biết
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
     * 3. Ghi đè hàm STORE (để xử lý upload ảnh + gán user_id)
     */
    public function store(Request $request)
    {
        // Validate bằng rules đã khai báo ở trên
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Dữ liệu không hợp lệ', $validator->errors(), 422);
        }

        $user = $request->user() ?? User::find(2);

        // Khởi tạo model
        $collection = new Collection();
        $collection->fill($request->except('image')); // Fill các trường cơ bản
        $collection->user_id = $user->id;             // Gán cứng user hiện tại
        $collection->status = 'active';

        // Xử lý Upload Ảnh
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
     * 4. Ghi đè hàm UPDATE (để check quyền + xử lý ảnh cũ/mới)
     */
    public function update(Request $request, $id)
    {
        $user = $request->user() ?? User::find(2);
        
        $collection = Collection::find($id);

        if (!$collection) {
            return $this->sendError('Không tìm thấy bộ sưu tập');
        }

        // Check quyền chính chủ
        if ($collection->user_id !== $user->id) {
            return $this->sendError('Bạn không có quyền chỉnh sửa', [], 403);
        }

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
     * 5. Ghi đè hàm DESTROY (để check quyền + xóa mềm)
     */
    public function destroy($id)
    {
        $user = request()->user() ?? User::find(2);
        
        $collection = Collection::find($id);

        if (!$collection) {
            return $this->sendError('Không tìm thấy bộ sưu tập');
        }

        if ($collection->user_id !== $user->id) {
            return $this->sendError('Bạn không có quyền xóa', [], 403);
        }

        // Xóa mềm (Soft Delete)
        $collection->status = 'inactive';
        $collection->save();

        return $this->sendResponse([], 'Đã xóa bộ sưu tập');
    }


    /**
     * 6. XEM CHI TIẾT BỘ SƯU TẬP (KÈM DANH SÁCH MÓN ĂN)
     * GET: /api/collections/{id}
     */
    public function show($id)
    {
        // Lấy collection kèm theo danh sách recipes
        $collection = Collection::with(['recipes' => function($query) {
            $query->wherePivot('status', 'active') // 1. Chỉ lấy món chưa bị xóa mềm
                  ->select('recipes.id', 'recipes.title', 'recipes.image_path', 'recipes.cooking_time', 'recipes.difficulty_id')
                  ->with('difficulty')
                  ->withCount('rates')          // 2. Đếm số lượng đánh giá
                  ->withAvg('rates', 'score');  // 3. Tính điểm trung bình
        }])->find($id);

        if (!$collection) {
            return $this->sendError('Không tìm thấy bộ sưu tập');
        }

        return $this->sendResponse($collection, 'Lấy chi tiết thành công');
    }


    /**
     * 8. XÓA CÔNG THỨC KHỎI BỘ SƯU TẬP
     * POST: /api/collections/{id}/remove-recipe
     * Body: { recipe_id: 123 }
     */
    public function removeRecipe(Request $request, $id)
    {
        $collection = Collection::find($id);
        if (!$collection) return $this->sendError('Bộ sưu tập không tồn tại');

        $collection->recipes()->updateExistingPivot($request->recipe_id, ['status' => 'inactive']);

        return $this->sendResponse([], 'Đã xóa công thức khỏi bộ sưu tập');
    }
}