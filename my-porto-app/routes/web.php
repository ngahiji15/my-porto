<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReproduceController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\BackendController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\Authenticate;

Route::get('/', function () {
    return view('home');
});


Route::get('/reproduceJquery', function () {
    return view('reproduce');
});;

Route::get('/sample-product', function () {
    return view('product');
});

Route::get('/contohbasket', function () {
    return view('contohbasket');
});

Route::get('/sample-checkout', [FrontendController::class, 'checkout']);
Route::get('/sample-payment', [BackendController::class, 'DokuCheckout']);

Route::get('/get-data-transactions', [FrontendController::class, 'getData']);

Route::get('/checkout', [FrontendController::class, 'showPaymentPage'])->name('checkout');
Route::get('/payment', [FrontendController::class, 'paymentPage'])->name('payment');
Route::get('/proceed-payment', [FrontendController::class, 'forwardData']);
Route::get('/test-session', [FrontendController::class, 'testSessionId']);
Route::get('/doku-checkout', [BackendController::class, 'generateCheckout']);
Route::get('/result/{invoice}', [FrontendController::class, 'resultPayment']);

//webhook
Route::get('/webhook/register', [WebhookController::class, 'login'])->name('login');


//testing


Route::middleware([Authenticate::class])->group(function () {
    Route::get('/haruslogin', [WebhookController::class, 'login']);
});

Route::get('/testcapturedata', [FrontendController::class, 'captureData']);

