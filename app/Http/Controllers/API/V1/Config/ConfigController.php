<?php

namespace App\Http\Controllers\API\V1\Config;

use Illuminate\Http\Request;
use App\Models\SettingWeb;
use App\Http\Controllers\API\V1\BaseCRUDController;
class ConfigController extends BaseCRUDController
{
        protected function setModel(){
        $this->model=SettingWeb::class;
    }
    protected function rules($id = null)
    {
        return [
        ];
    }
    public function getSettingWebActive()
    {
        // Lấy chi tiết câu hỏi theo ID, kèm người tạo và tổng số câu trả lời
        $item = $this->model::where('status', 'active')->first();
        if (is_null($item)) {
            return $this->sendError('Không tìm thấy dữ liệu.');
        }
        return $this->sendResponse($item, 'Lấy setting web thành công.');
    }
}
