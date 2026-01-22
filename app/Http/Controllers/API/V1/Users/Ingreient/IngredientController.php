<?php

namespace App\Http\Controllers\API\V1\Users\Ingreient;

use App\Http\Controllers\API\V1\BaseCRUDController;
use Illuminate\Http\Request;
use App\Models\Ingredient;

class IngredientController extends BaseCRUDController
{
    protected function setModel()
    {
        $this->model = Ingredient::class;
    }

    protected function rules($id = null)
    {
        return [
           
        ];
    }
    public function index()
{
    $request=request();
    $query = $request->get('q');

    if ($query) {
        $data = $this->model::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'name_slug', 'image_path', 'created_at')
            ->limit(10)
            ->get();
        
        return $this->sendResponse($data, 'Tìm kiếm nguyên liệu thành công.');
    }

    $data = $this->model::latest()->paginate(20);
    return $this->sendResponse($data, 'Lấy danh sách nguyên liệu thành công.');
}

}
