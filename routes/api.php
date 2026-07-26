<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('ai')
    ->middleware(['throttle:api', 'ai.key'])
    ->group(function () {
        Route::get('/health', [AiController::class, 'health']);
        Route::post('/chat', [AiController::class, 'chat']);
        Route::post('/translate', [AiController::class, 'translate']);
        Route::post('/summarize', [AiController::class, 'summarize']);
    });
