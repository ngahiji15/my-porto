<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Utils\ControllerUtils;
use App\Utils\DokuUtils;

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
        $cusName = $request->get('firstName');
        $cusEmail = $request->get('email');
        $paymentMethod = $request->get('pembayaran');
        $dataPayment = ControllerUtils::getDataBySessionId($sessionId);
        $cart = $dataPayment['cart'] ?? null;
        $amount = $dataPayment['totalAmount'] ?? null;
        $data = [
            "sessionId" => $sessionId,
            "name" => $cusName,
            "email" => $cusEmail,
            "PaymentMethod" => $paymentMethod
        ];
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
            echo "Something wring with website, please contact our admin.";
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
        //$sessionId = $request->session()->get('sessionId');////$sessionId = 'iRf0RINDjwahcGdJVdzVKOlGR28lRqME8gfckPx6';
        \Log::info($sessionId);
        $payment = Payment::where('session_id', $sessionId)->latest()->first();
        \Log::info($payment);
        $cartItems = $payment->cart ?? null;
        $totalAmount = $payment->total_amount ?? null;
        $method = $payment->payment_channel ?? null;
        \Log::info($method);
        $invoice = $payment->invoice_number ?? null;
        $expiredDate = $payment->expired_date ?? null;
        $userId = $payment->user_id ?? null;
        $urlCheckout = $payment->payment_code ?? null;
        $statusPayment = $payment->status ?? null;
        \Log::info($userId);

        $userData = User::where('id', $userId)->latest()->first();
        $userName = $userData->name ?? null;

        \Log::info($totalAmount);
        //validasi sessionId (if SessionId == getSessionId --> check status (status == PENDING --> continue send data[url and other], status == COMPLETED --> Send data success payment already paid), SessionId != getSessionId --> send alert your session has ended, please try with new checkout.)
        return view('payment', ['cartItems' => $cartItems, 'totalAmount' => $totalAmount, 'method' => $method, 'invoice' => $invoice, 'expiredDate' => $expiredDate, 'name' => $userName, 'urlCheckout' => $urlCheckout, 'status'=> $statusPayment]);
    }
}
