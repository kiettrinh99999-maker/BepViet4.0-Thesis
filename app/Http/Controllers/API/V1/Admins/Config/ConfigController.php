<?php

namespace App\Http\Controllers\Api\V1\Admins\Config;

use App\Http\Controllers\Api\V1\BaseCRUDController;
use App\Models\SettingWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// --- THÊM CÁC MODEL CẦN THIẾT ---
use App\Models\Region;
use App\Models\Difficulty;
use App\Models\Event;

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
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ];
    }

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

        $data = $request->except('image');
        
        if ($request->hasFile('image')) {
            // 1. Xóa ảnh cũ
            if ($settings->image_path) {
                $oldPath = str_replace('/storage/', '', $settings->image_path);
                $oldPath = ltrim($oldPath, '/'); 

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // 2. Lưu ảnh mới
            $file = $request->file('image');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/config', $filename, 'public'); 

            // 3. Lưu đường dẫn
            $data['image_path'] = 'uploads/config/' . $filename; 
        }

        // Cập nhật Database
        $settings->update($data);

        return $this->sendResponse($settings, 'Cập nhật cấu hình thành công.');
    }

    // Lấy dữ liệu chung
    public function get_region_event_dif(){
        // Lấy id và name của 3 bảng
        $regions = Region::select('id', 'name')->get();
        $difficulties = Difficulty::select('id', 'name')->get();
        $events = Event::select('id', 'name')->get();

        $data = [
            'regions' => $regions,
            'difficulties' => $difficulties,
            'events' => $events
        ];
        return $this->sendResponse($data, 'Lấy dữ liệu bộ lọc thành công');
    }
}