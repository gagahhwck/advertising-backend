<?php

namespace App\Http\Controllers;

use App\Models\Advertising\Order;
use App\Models\Advertising\Payment;

class PaymentController extends Controller
{
    public function store(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Order Invalid'
            ],422);
        }

        // 1. call payment gateway (Midtrans)
        $paymentData = $this->createGateway($order);

        // 2. simpan payment
        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'transaction_id' => $paymentData['token'],
            'payment_url' => $paymentData['redirect_url'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Payment created',
            'data' => $payment
        ]);
    }

    private function createGateway(Order $order)
    {
        return [
            'token' => 'PAY-' . $order->order_code,
            'redirect_url' => 'https://payment-gateway.com/pay/' . $order->id
        ];
    }
}
