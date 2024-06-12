<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Utils\ControllerUtils;
use App\Utils\DokuUtils;
use Carbon\Carbon;

class FrontendController extends Controller
{
    public function checkout(Request $request)
    {
        
        $cartData = $request->query('cart');
        $cart = json_decode(urldecode($cartData), true);

        

        $totalAmount = $request->query('totalAmount');

        // Lakukan operasi yang sesuai dengan data cart
        // Contoh: tampilkan data cart
        return view('ecommerceSample', ['totalAmount' => $totalAmount]);
    }

    public function getData(Request $request)
    {
        $sessionId = $request->session()->getId();

        $cartData = $request->query('cart');
        $cart = json_decode(urldecode($cartData), true);

        $totalAmount = $request->query('totalAmount');

        // Cek apakah session ID sudah ada di database
        $existingPayment = Payment::where('session_id', $sessionId)->first();

        // Jika session ID sudah ada, update entri yang ada
        if ($existingPayment) {
            $existingPayment->cart = $cart;
            $existingPayment->total_amount = $totalAmount;
            $existingPayment->save();
        } else {
            // Jika session ID belum ada, buat entri baru
            $payment = new Payment();
            $payment->session_id = $sessionId;
            $payment->cart = $cart;
            $payment->total_amount = $totalAmount;
            $payment->save();
        }

        // Redirect ke halaman /payment dengan mem-passing data $cart, $totalAmount, dan $sessionId
        return redirect()->route('checkout')->with(['cart' => $cart, 'totalAmount' => $totalAmount, 'sessionId' => $sessionId]);
    }
    public function testSessionId(Request $request)
    {
        $sessionId = $request->session()->getId();
        $sessionFromFunction = ControllerUtils::generateNewSessionId();

        $data = [
            "getSeesionId" => $sessionId,
            "functionSession" => $sessionFromFunction
        ];
        var_dump($data);
    }

    public function forwardData(Request $request)
    {
        $sessionId = $request->session()->getId();
        $cusName = $request->get('firstName') ?? null;
        $cusEmail = $request->get('email') ?? null;
        $paymentMethod = $request->get('pembayaran') ?? null;
        $dataPayment = ControllerUtils::getDataBySessionId($sessionId);
        $cart = $dataPayment['cart'] ?? null;
        $amount = $dataPayment['totalAmount'] ?? null;
        $data = [
            "sessionId" => $sessionId,
            "name" => $cusName,
            "email" => $cusEmail,
            "PaymentMethod" => $paymentMethod
        ];
        switch ($paymentMethod){
            case 'A':
                $generateUrl = DokuUtils::generateCheckoutUrl($sessionId,  $cusName, $cusEmail, $amount, 'demo', 'Doku Experience');
                $urlCheckout = $generateUrl['urlCheckout'] ?? null;
                $generateHttp = $generateUrl['httpCode'] ?? null;
                $generareMessage = $generateUrl['message'] ?? null;
        
                //validasi http status
                if($generateHttp === 200){
                    return redirect()->route('payment')->with(['sessionId' => $sessionId]);
                }else if($generateHttp === null){
                    echo "Something wrong with system, please contact our admin.";
                }else{
                    echo "Something wrong with website, please contact our admin.";
                };
            break;
            case 'B':
                echo "Something wrong with website, please contact our admin.";
            break;
            case 'C':
                echo "Something wrong with website, please contact our admin.";
            break;
        }
        
    }

    public function showPaymentPage(Request $request)
    {
        $cart = $request->session()->get('cart');
        $totalAmount = $request->session()->get('totalAmount');
        $sessionId = $request->session()->get('sessionId');
        return view('ecommerceSample', ['cart' => $cart, 'totalAmount' => $totalAmount, 'sessionId' => $sessionId]);
    }

    public function paymentPage(Request $request)
    {
        $sessionId = $request->session()->getId();
        $sessionIdOld = $request->session()->get('sessionId') ?? $sessionId;
        
        $payment = Payment::where('session_id', $sessionIdOld)->latest()->first();
    
        // Jika tidak ada pembayaran dengan sessionIdOld, gunakan sessionId saat ini
        if (!$payment) {
            $payment = Payment::where('session_id', $sessionId)->latest()->first();
            $request->session()->put('sessionId', $sessionId);
        } else {
            $request->session()->put('sessionId', $sessionIdOld);
        }
    
        if ($payment) {
            // Ambil data pembayaran dari database
            $cartItems = $payment->cart;
            $totalAmount = $payment->total_amount;
            $method = $payment->payment_channel;
            $invoice = $payment->invoice_number;
            $expiredDate = $payment->expired_date;
            $userId = $payment->user_id;
            $urlCheckout = $payment->payment_code;
            $statusPayment = $payment->status;
    
            // Perbarui status sesi
            $request->session()->put('status', $statusPayment);
    
            // Ambil data pengguna jika ada
            $userData = User::where('id', $userId)->latest()->first();
            $userName = $userData->name ?? null;
    
            // Hitung timeLeft jika statusnya masih 'PENDING'
            $now = Carbon::now();
            $expiryTime = Carbon::parse($expiredDate);
            $timeLeft = $expiryTime->diffInMinutes($now);
    
            // Ubah expiredDate menjadi Payment Date jika statusnya 'SUCCESS'
            if ($statusPayment == 'SUCCESS') {
                $expiredDate = $payment->update_date; // Ganti dengan kolom yang sesuai
            }
        } else {
            // Jika tidak ada pembayaran, atur semua data menjadi null
            $cartItems = null;
            $totalAmount = null;
            $method = null;
            $invoice = null;
            $expiredDate = null;
            $userId = null;
            $urlCheckout = null;
            $statusPayment = null;
            $userName = null;
            $timeLeft = null;
        }
    
        // Kirim data ke tampilan
        return view('payment', [
            'cartItems' => $cartItems, 
            'totalAmount' => $totalAmount, 
            'method' => $method, 
            'invoice' => $invoice, 
            'expiredDate' => $expiredDate, 
            'name' => $userName, 
            'urlCheckout' => $urlCheckout, 
            'status' => $statusPayment,
            'timeLeft' => $timeLeft
        ]);
    }
    
    

    public function resultPayment(Request $request, $invoice)
    {
        \Log::info('==== Callback Starting ====');
        \Log::info('Invoice : '.$invoice);
        $payment = Payment::where('invoice_number', $invoice)->latest()->first();
        $sessionId = $payment->session_id ?? null;
        return redirect()->route('payment')->with(['sessionId' => $sessionId]);
    }
}