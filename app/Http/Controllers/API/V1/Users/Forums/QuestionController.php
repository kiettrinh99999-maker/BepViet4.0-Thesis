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
        //Truy vấn tới bảng questions, nối với bảng users đế lấy username và lấy tổng số answer của 1 question
        $data = $this->model::with('user')->withCount('answers')->latest()->paginate(10);
        return $this->sendResponse($data, 'Lấy danh sách câu hỏi thành công');
    }
}
