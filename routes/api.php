<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Users\Recipes\RecipeController;
use App\Http\Controllers\Api\V1\Config\ConfigController;
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
    Route::apiResource('recipes', RecipeController::class);
    Route::apiResource('config', ConfigController::class);
    //Quy: api cho question
    Route::apiResource('questions', QuestionController::class);
    //Quy: api cho answer
    Route::apiResource('answers', AnswerController::class);
});
