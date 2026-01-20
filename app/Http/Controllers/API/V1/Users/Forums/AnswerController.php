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
            'content'     => 'required|string',
            'question_id' => 'required|integer|exists:questions,id',
            'parent_id'   => 'nullable|integer|exists:answers,id',
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

    public function store(Request $request)
    {
        //Kiểm tra dữ liệu đầu vào với rule
        $validator = \Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->sendError('Lỗi dữ liệu đầu vào', $validator->errors(), 422);
        }

        //Tạo item Answer
        $answer = Answer::create([
            'content'     => $request->content,
            'question_id' => $request->question_id,
            'parent_id'   => $request->parent_id, // null nếu là trả lời gốc
            //'user_id'     => auth()->id(),
            'user_id' => 1, // user test
            'status'      => 'active',
        ]);

        return $this->sendResponse($answer, 'Gửi câu trả lời thành công', 201);
    }
}
