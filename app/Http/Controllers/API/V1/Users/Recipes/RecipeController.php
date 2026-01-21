<?php

namespace App\Http\Controllers\API\V1\Users\Recipes;

use App\Models\Recipe;
use App\Http\Controllers\API\V1\BaseCRUDController;
use Illuminate\Http\Request;

class RecipeController extends BaseCRUDController
{
    protected function setModel(){
        $this->model = Recipe::class;
    }

    protected function rules($id = null)
    {
        return [
            'title' => 'required|max:255',
            'cooking_time' => 'required|integer',
        ];
    }
    //Nếu người dùng đăng nhập thì ưu tiên lấy theo vùng miền
    public function index(){
    // 1. KHAI BÁO BIẾN REQUEST TỪ HELPER (Thêm dòng này)
    $request = request(); 
    // Các đoạn dưới giữ nguyên, dùng biến $request vừa tạo ở trên
    $userRegionId = $request->query('mock_user_region');

    $query = $this->model::with([
        'user:id,username',
        'region:id,name',
        'difficulty:id,name',
        'event:id,name',
    ])->where('status', 'active')
    ->withAvg('rates', 'score') 
    ->withCount('rates');

    // 2. Lọc theo Vùng miền
    if ($request->filled('region_id')) {
        $query->where('region_id', $request->region_id);
    }

    // 3. Lọc theo Sự kiện
    if ($request->filled('event_id')) {
        $query->where('event_id', $request->event_id);
    }

    // 4. Lọc theo Độ khó
    if ($request->filled('difficulty_id')) {
        $query->where('difficulty_id', $request->difficulty_id);
    }

    // 5. Xử lý logic ưu tiên vùng miền
    // Logic: Nếu người dùng KHÔNG đang lọc theo vùng cụ thể thì mới ưu tiên vùng của họ
    if ($userRegionId && !$request->filled('region_id')) {
        $query->orderByRaw("region_id = ? DESC", [$userRegionId]);
    }

    $data = $query->latest()->paginate(12); // Nên để 12 để giao diện đẹp hơn
    
    return $this->sendResponse($data, 'Lấy danh sách công thức thành công');
}


    //Tìm theo id hoặc tên công thức
    public function show($key){
        $query = $this->model::with(['user.profile', 'ingredients', 'steps','steps.step_images']);
        if (is_numeric($key)) {
            $recipe = $query->find($key);
        } else {
            $recipe = $query->where('title_slug', 'LIKE', "%{$key}%")->first();
        }
        if (!$recipe) {
            return $this->sendError('Không tìm thấy công thức', [], 404);
        }
        return $this->sendResponse($recipe, 'Lấy chi tiết công thức thành công');
    }
}