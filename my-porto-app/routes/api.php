<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DokuController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/auth/v1/token', [DokuController::class, 'generateAccessToken']);
Route::post('/snap/danamon-inquiry', [DokuController::class, 'inquiryDanamon']);
Route::post('/test/tokenB2B', [DokuController::class, 'generateQRIS']);