<?php

namespace App\Http\Controllers\API\V1\Users\Forums;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Http\Controllers\API\V1\BaseCRUDController;
use Illuminate\Support\Str;

class QuestionController extends BaseCRUDController
{
    protected function setModel(){
        $this->model=Question::class;
    }

    //Rule của question
    protected function rules($id = null)
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image_path'  => 'nullable|string|max:255',
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

    public function store(Request $request)
    {
        //Kiểm tra dữ liệu đầu vào với rule
        $validator = \Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Lỗi dữ liệu đầu vào', $validator->errors(), 422);
        }

        // Tạo item Question
        $item = Question::create([
            'title'       => $request->title,
            'title_slug'  => Str::slug($request->title), //chuyển chuỗi title sang dạng slug
            'description' => $request->description,
            'image_path'  => $request->image_path,
            //'user_id'     => auth()->id(),
            'user_id' => 1, // user test
            'status'      => 'active',
        ]);

        // Update slug + id của question, tránh bị trùng slug
        $item->title_slug = Str::slug($item->title) . '-' . $item->id;
        $item->save();

        return $this->sendResponse($item, 'Tạo câu hỏi thành công', 201);
    }
}
