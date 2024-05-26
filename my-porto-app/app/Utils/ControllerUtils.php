<?php

namespace App\Utils;
use App\Models\Payment;

class ControllerUtils
{
    public static function generateNewSessionId() 
    {
        return uniqid();
    }

    public static function checkPaymentSession() {
        $sessionId = Cache::get('payment_session_id');
        $paymentStatus = Cache::get('payment_status');
        $expiryTime = Cache::get('payment_expiry_time');
    
        if ($paymentStatus === 'completed' || time() > $expiryTime) {
            $sessionId = generateNewSessionId();
    
            Cache::put('payment_session_id', $sessionId);
            Cache::put('payment_expiry_time', strtotime('+1 hour'));
        }
    
        return $sessionId;
    }

    public static function getDataBySessionId($sessionId)
    {
        $payment = Payment::where('session_id', $sessionId)->first();
        \Log::info('----- get Data by SessionId -----');
        \Log::info($payment);
        if ($payment) {
            return [
                'cart' => $payment->cart,
                'totalAmount' => $payment->total_amount,
                'invoiceNumber' => $payment->invoice_number ?? null,
                'paymentChannel' => $payment->payment_channel ?? null,
                'userId' => $payment->user_id ?? null,
                'status' => $payment->status ?? null,
                'type' => $payment->type ?? null,
                'orderType' => $payment->order_type ?? null,
                'createDate' => $payment->create_date ?? null,
                'updateDate' => $payment->updateDate ?? null,
                'expiredDate' => $payment->expired_date ?? null,
                'paymentCode' => $payment->payment_code ?? null
            ];
        } else {
            return null;
        }
    }

    public static function updatePaymentStatus($invoice, $paymentCode, $paymentChannel)
    {
        $currentDateTime = new \DateTime();
        $currentDateTime->modify('+7 hours');
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s'); 
        $payment = Payment::where('invoice_number', $invoice)->first();
        if($payment) {
            $payment->status = 'SUCCESS';
            $payment->payment_code = $paymentCode;
            $payment->payment_channel = 'Doku Checkout -> ' . $paymentChannel;
            $payment->update_date = $formattedDateTime;
            $payment->save();
            \Log::info('update payment status ok!');
            return 'ok';
        } else {
            \Log::error('Payment record not found for invoice : ' . $invoice);
            return [
                "httpCode" => 404,
                "message" => "Payment record not found",
            ];
        }
    }
}