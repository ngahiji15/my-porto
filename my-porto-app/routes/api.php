<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DokuController;
use App\Http\Controllers\ReproduceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/auth/v1/token', [DokuController::class, 'generateAccessToken']);
Route::post('/v1.1/transfer-va/inquiry', [DokuController::class, 'inquiryDanamon']);
Route::post('/test/tokenB2B', [DokuController::class, 'generateQRIS']);
Route::post('/v1/transfer-va/payment', [DokuController::class, 'notificationSnap']);
Route::post('/getPaymentUrl', [ReproduceController::class, 'getPaymentUrl']);
Route::post('/getBody', [ReproduceController::class, 'getBody']);
Route::post('/signatureChecker', [DokuController::class, 'signatureChecker']);
Route::post('/inquiry-bni', [ReproduceController::class, 'inquirybni']);
Route::post('/reproducePost', [ReproduceController::class, 'testPostMethodAllowed']);
//Route::post('/v1.1/transfer-va/inquiry', [DokuController::class, 'inquiryBri']);