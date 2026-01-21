<?php
use App\Http\Controllers\Api\V1\Admins\DashboardController;
use App\Http\Controllers\Api\V1\Admins\Config\ConfigController;
use App\Http\Controllers\Api\V1\Admins\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Users\Recipes\RecipeController;
use App\Http\Controllers\Api\V1\Users\Forums\QuestionController;
use App\Http\Controllers\Api\V1\Users\Forums\AnswerController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group(['middleware' => ['api']], function () {
    //admin/dashboard
    Route::get('admin/dashboard', [DashboardController::class, 'index']);


    //admin/report
    // Đưa 'index' vào trong mảng
    Route::apiResource('admin/report', ReportController::class);

    //member và user
    Route::apiResource('recipes', RecipeController::class);
    Route::apiResource('config',  ConfigController::class);
    //API cho question
    Route::apiResource('questions', QuestionController::class);
    //API cho answer
    Route::apiResource('answers', AnswerController::class);
    //API lấy answer theo question id
    Route::get('questions/{id}/answers', [AnswerController::class, 'listByQuestionId']);
});
