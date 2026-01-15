<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Users\Recipes\RecipeController;
use App\Http\Controllers\Api\V1\Config\ConfigController;

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
    
    Route::apiResource('recipes', RecipeController::class);
    Route::apiResource('config',ConfigController::class );
});
