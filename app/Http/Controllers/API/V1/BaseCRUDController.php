<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseCRUDController extends Controller
{
    protected $model;

    // Các class con bắt buộc phải khai báo model và rules
    abstract protected function setModel();
    abstract protected function rules($id = null);

    public function __construct()
    {
        $this->setModel();
    }

    // --- 1. HELPER CHUẨN HÓA JSON ---
    public function sendResponse($data, $message = 'Thành công', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }
        return response()->json($response, $code);
    }

    // --- 2. CÁC HÀM CRUD CƠ BẢN ---

    // GET /api/resource
    public function index()
    {
        // Lấy dữ liệu phân trang (mặc định 10)
        $data = $this->model::latest()->paginate(10);
        return $this->sendResponse($data, 'Lấy danh sách thành công.');
    }

    // GET /api/resource/{id}
    public function show($id)
    {
        $item = $this->model::find($id);
        if (is_null($item)) {
            return $this->sendError('Không tìm thấy dữ liệu.');
        }
        return $this->sendResponse($item, 'Lấy chi tiết thành công.');
    }

    // POST /api/resource
    public function store(Request $request)
    {
        // Validate
        $validator = \Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendError('Lỗi dữ liệu đầu vào', $validator->errors(), 422);
        }
        // Create
        $item = $this->model::create($request->all());
        return $this->sendResponse($item, 'Tạo mới thành công.', 201);
    }

    // PUT /api/resource/{id}
    public function update(Request $request, $id)
    {
        $item = $this->model::find($id);
        if (is_null($item)) {
            return $this->sendError('Không tìm thấy dữ liệu để cập nhật.');
        }

        $validator = \Validator::make($request->all(), $this->rules($id));
        if ($validator->fails()) {
            return $this->sendError('Lỗi dữ liệu đầu vào', $validator->errors(), 422);
        }

        $item->update($request->all());
        return $this->sendResponse($item, 'Cập nhật thành công.');
    }

    // DELETE /api/resource/{id}
    public function destroy($id)
    {
        $item = $this->model::find($id);
        if (is_null($item)) {
            return $this->sendError('Không tìm thấy dữ liệu để xóa.');
        }

        $item->delete();
        return $this->sendResponse([], 'Xóa thành công.');
    }
}
