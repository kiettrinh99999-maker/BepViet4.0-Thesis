<?php

namespace App\Http\Controllers\API\V1\Users\Forums;

use Illuminate\Http\Request;
use App\Models\Answer;
use App\Http\Controllers\API\V1\BaseCRUDController;

class AnswerController extends BaseCRUDController
{
    protected function setModel(){
        $this->model=Answer::class;
    }
    
    //Rule của answer
    protected function rules($id = null)
    {
        return [
           'content'   => 'required|string',
            'question_id' => 'required|integer|exists:questions,id',
            'parent_id' => 'nullable|integer|exists:answers,id',
            'user_id'   => 'required|integer|exists:users,id',
            'status'    => 'required|in:active,inactive'
        ];
    }
    public function listByQuestionId($id)
    {
        // Lấy các câu trả lời cấp 1 (parent_id = null) của câu hỏi theo question_id
        // Đồng thời load thông tin user và các câu trả lời con (đa cấp)
       $data = $this->model::with([
            'user',
            'answersRecursive'
        ])
        ->where('question_id', $id)
        ->whereNull('parent_id')
        ->get();

        return $this->sendResponse($data, 'Lấy danh sách câu trả lời thành công');
    }
}
