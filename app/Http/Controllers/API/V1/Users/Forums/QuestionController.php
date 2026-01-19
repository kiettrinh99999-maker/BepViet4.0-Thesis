<?php

namespace App\Http\Controllers\API\V1\Users\Forums;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Http\Controllers\API\V1\BaseCRUDController;

class QuestionController extends BaseCRUDController
{
    protected function setModel(){
        $this->model=Question::class;
    }

    //Rule của question
    protected function rules($id = null)
    {
        return [
            'title' => 'required|string|max:255',
            'title_slug' => 'required|string|max:255',
            'description' => 'required|string',
            'image_path' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'status' => 'required|in:active,inactive'
        ];
    }

    public function index(){
        // Lấy danh sách question mới nhất, kèm thông tin người tạo và số lượng câu trả lời (phân trang)
        $data = $this->model::with('user')->withCount('answers')->latest()->paginate(10);
        return $this->sendResponse($data, 'Lấy danh sách câu hỏi thành công');
    }

    public function show($id)
    {
        // Lấy chi tiết câu hỏi theo ID, kèm người tạo và tổng số câu trả lời
        $item = $this->model::with('user')->withCount('answers')->find($id);
        if (is_null($item)) {
            return $this->sendError('Không tìm thấy dữ liệu.');
        }
        return $this->sendResponse($item, 'Lấy chi tiết thành công.');
    }
}
