<?php

use App\Http\Controllers\Api\V1\Admins\Config\ConfigController;
use App\Http\Controllers\Api\V1\Admins\DashboardController;
use App\Http\Controllers\API\V1\Admins\ManageCategory\ManageBlogCategoryController;
use App\Http\Controllers\API\V1\Admins\ManageCategory\ManageEventController;
use App\Http\Controllers\API\V1\Admins\ManageCategory\ManageRecipeCategoryController;
use App\Http\Controllers\API\V1\Admins\ManageCategory\ManageRegionController;
use App\Http\Controllers\Api\V1\Admins\ReportController;
use App\Http\Controllers\API\V1\Users\Blogs\BlogController;
use App\Http\Controllers\API\V1\Users\Collections\CollectionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Users\Recipes\RecipeController;
use App\Http\Controllers\Api\V1\Users\Forums\QuestionController;
use App\Http\Controllers\Api\V1\Users\Forums\AnswerController;
use App\Http\Controllers\API\V1\Users\Follow\FollowController;
use App\Http\Controllers\API\V1\Users\Ingreient\IngredientController;
use App\Http\Controllers\API\V1\Users\Profile\ProfileController;
use App\Http\Controllers\API\V1\Users\AuthController;
use Illuminate\Support\Facades\DB; 


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
    //API cho recipes
    Route::apiResource('recipes', RecipeController::class);
    //API cho blogs
    Route::apiResource('blogs', BlogController::class);
    //API cho config
    Route::apiResource('config',  ConfigController::class);
    //Lấy dữ liệu vùng miền, độ khó
    Route::get('get-event-region',[ConfigController::class,'get_region_event_dif']);
    //API cho question
    Route::apiResource('questions', QuestionController::class);
    //API cho answer
    Route::apiResource('answers', AnswerController::class);
    //API lấy answer theo question id
    Route::get('questions/{id}/answers', [AnswerController::class, 'listByQuestionId']);

    //APi follow
    Route::post('toggle-follow', [FollowController::class, 'toggleFollow']);

    //API nguyên liệu
    Route::apiResource('ingredient', IngredientController::class);

    //API cho collections
    Route::apiResource('collections', CollectionController::class); 
    //profile
    Route::get('profile', [ProfileController::class, 'index']);
    Route::post('profile/update', [ProfileController::class, 'update']);
    
    // QUẢN LÝ DANH MỤC
    //API danh mục vùng miền
    Route::apiResource('regions', ManageRegionController::class);
    //event
    Route::apiResource('events', ManageEventController::class);

    //  Danh mục Blog 
    Route::apiResource('blog-categories', ManageBlogCategoryController::class);

    // Danh mục Bữa ăn 
    Route::apiResource('recipe-categories', ManageRecipeCategoryController::class);
    
    //collection
    Route::post('collections/{id}/remove-recipe', [CollectionController::class, 'removeRecipe']); // Xóa món
    //API user login
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
            Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            // Route::put('/profile', [AuthController::class, 'updateProfile']);
            // Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
    });
});

