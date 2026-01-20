<?php

namespace App\Http\Controllers\Api\V1\Admins\Config;

use App\Http\Controllers\Api\V1\BaseCRUDController;
use App\Models\SettingWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfigController extends BaseCRUDController
{
    protected function setModel()
    {
        $this->model = SettingWeb::class;
    }

    protected function rules($id = null)
    {
        return [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:20',
            'email'     => 'required|email|max:255',
            'copyright' => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048', // Validate ảnh
        ];
    }

    // Ghi đè hàm update để xử lý upload ảnh
    public function update(Request $request, $id)
    {
        $settings = $this->model::find($id);
        if (!$settings) {
            return $this->sendError('Không tìm thấy cấu hình.');
        }

        // Validate dữ liệu
        $validator = \Validator::make($request->all(), $this->rules($id));
        if ($validator->fails()) {
            return $this->sendError('Lỗi dữ liệu đầu vào', $validator->errors(), 422);
        }

        $data = $request->except('image'); // Lấy hết data trừ file ảnh

        // Xử lý upload ảnh nếu có
        if ($request->hasFile('image')) {
            // 1. Xóa ảnh cũ nếu có (và không phải ảnh mặc định)
            if ($settings->image_path) {
                // Bước A: Làm sạch đường dẫn
                // Xóa chữ '/storage/' nếu lỡ lưu thừa (ví dụ: /storage/uploads/...)
                $oldPath = str_replace('/storage/', '', $settings->image_path);
                
                // Xóa dấu gạch chéo '/' ở đầu nếu có (ví dụ: /uploads/... -> uploads/...)
                $oldPath = ltrim($oldPath, '/'); 

                // Bước B: Kiểm tra và xóa file vật lý
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // 2. Lưu ảnh mới
            $file = $request->file('image');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/config', $filename, 'public'); // Lưu vào storage/app/public/uploads/config

            // 3. Lưu đường dẫn vào DB (Thêm prefix /storage/ để frontend dễ gọi)
            // Lưu ý: Cần chạy lệnh `php artisan storage:link`
            $data['image_path'] = 'uploads/config/' . $filename; 
        }

        // Cập nhật Database
        $settings->update($data);

        return $this->sendResponse($settings, 'Cập nhật cấu hình thành công.');
    }
}