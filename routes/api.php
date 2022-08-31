<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthorizationsController;

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

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {

        Route::middleware('throttle:' . config('api.rate_limits.sign'))
            ->group(function () {
                Route::post('authorizations', [AuthorizationsController::class, 'store']); // 登录
                Route::put('authorizations/current', [AuthorizationsController::class, 'update']);// 刷新token
                Route::delete('authorizations/current', [AuthorizationsController::class, 'destroy']);// 删除token
            });

        Route::middleware('throttle:' . config('api.rate_limits.access'))
            ->group(function () {
                // 游客可以访问的接口
//                Route::get('test', [IndexController::class, 'test']); // test
//                Route::get('demo', [IndexController::class, 'demo']); // test

                // 登录后可以访问的接口
                Route::middleware('auth:api')->group(function () {
//                    Route::get('user', [UserController::class, 'me']); // 当前登录用户信息
//                    Route::resource('feedback', FeedbackController::class); // 问题反馈
                });
            });
    });
