<?php

namespace App\Http\Controllers\API\V1\Admins;

use App\Http\Controllers\API\V1\BaseCRUDController;
use App\Models\RecipeReport;
use Carbon\Carbon; // Nhớ import

class ReportController extends BaseCRUDController
{
    protected function setModel() { 
        $this->model = RecipeReport::class; 
    }

    protected function rules($id = null) {
        return [
        'status' => 'sometimes|in:pending,reviewed,dismissed',
        ];
    }

    public function index()
    {
        $query = RecipeReport::with([
            'recipe.user.profile',
            'recipe.difficulty',
            'recipe.event',
            'user.profile'
        ]);
        $request = request();
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->created_at);
        }
        $data = $query->orderBy('created_at', 'desc')->paginate(10);
        return $this->sendResponse($data, 'Lấy danh sách thành công');
    }

    public function show($id)
    {
        $report = RecipeReport::with([
            'recipe.user.profile',
            'recipe.difficulty',
            'recipe.event',
            'recipe.ingredients',
            'recipe.steps',
            'recipe.steps.step_images',
            'recipe.region',
            'user.profile',
        ])->findOrFail($id);

        return $this->sendResponse($report, 'Lấy chi tiết báo cáo thành công');
    }
    
}