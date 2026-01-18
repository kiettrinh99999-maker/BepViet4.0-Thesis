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
            'parent_id' => 'required|integer|exists:comments,id',
            'user_id'   => 'required|integer|exists:users,id',
            'status'    => 'required|in:active,inactive'
        ];
    }
}
