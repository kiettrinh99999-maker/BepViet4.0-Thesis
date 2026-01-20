<?php

namespace App\Http\Controllers\Api\V1\Admins;

use App\Http\Controllers\Api\V1\BaseCRUDController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Recipe;
use App\Models\Blog;
use App\Models\SettingWeb;

class DashboardController extends BaseCRUDController
{
    protected function setModel() { $this->model = SettingWeb::class; }
    protected function rules($id = null) { return []; }

    public function index()
    {
        $request = request();

        // 1. Lấy danh sách các NĂM có dữ liệu người dùng đăng ký
        // Logic: Lấy cột created_at -> Tách năm -> Loại bỏ trùng -> Sắp xếp giảm dần
        $availableYears = User::selectRaw('YEAR(created_at) as year')
            ->distinct() // Loại bỏ năm trùng
            ->orderBy('year', 'desc')
            ->pluck('year') // Chỉ lấy mảng các số năm [2023, 2022...]
            ->toArray();

        // Nếu data trống (chưa có user nào), mặc định lấy năm hiện tại
        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        }

        // Lấy năm từ request, nếu không có thì lấy năm mới nhất trong DB
        $year = $request->input('year', $availableYears[0]);

        // 2. Thống kê (Giữ nguyên)
        $stats = [
            'total_recipes'   => Recipe::count(),
            'total_users'     => User::where('role', '!=', 'admin')->count(),
            'total_blogs'     => Blog::count(),
            'pending_recipes' => Recipe::where('status', 'pending')->count(),
        ];

        // 3. Biểu đồ (Giữ nguyên logic)
        $chartData = $this->getMonthlyUserGrowth($year);

        return $this->sendResponse([
            'stats' => $stats,
            'chart' => [
                'year'  => (int)$year,
                'label' => 'Người dùng mới',
                'data'  => $chartData
            ],
            // Trả thêm danh sách năm để Frontend render vào thẻ <select>
            'available_years' => $availableYears 
        ], 'Lấy dữ liệu Dashboard thành công');
    }

    private function getMonthlyUserGrowth($year)
    {
        $monthlyCounts = User::select(
            DB::raw('MONTH(created_at) as month'), 
            DB::raw('COUNT(*) as count')
        )
        ->whereYear('created_at', $year)
        ->where('role', '!=', 'admin')
        ->groupBy('month')
        ->pluck('count', 'month')
        ->toArray();

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $monthlyCounts[$i] ?? 0;
        }
        return $data;
    }
}