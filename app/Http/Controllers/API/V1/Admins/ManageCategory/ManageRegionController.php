<?php

namespace App\Http\Controllers\API\V1\Admins\ManageCategory;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\Region;
use Illuminate\Http\Request;

class ManageRegionController extends BaseCRUDController
{
    protected function setModel()
    {
        $this->model = Region::class;
    }

    protected function rules($id = null)
    {
        return [
            'name'   => 'required|string|max:255|unique:regions,name,' . $id,
            'status' => 'required|in:active,inactive',
        ];
    }

    public function index()
    {
        $request = request();
        $query = $this->model::query();

  
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $query->orderBy('created_at', 'desc');
        
        $data = $query->get();

        return $this->sendResponse($data, 'Lấy danh sách thành công');
    }

   
}