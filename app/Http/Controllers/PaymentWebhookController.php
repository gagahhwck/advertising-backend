<?php

namespace App\Http\Controllers;

use App\Models\Advertising\Order;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. ambil order
        $order = Order::where('order_code', $request->order_id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payment = $order->payment;

        // 2. cek status dari gateway
        $status = $request->transaction_status;

        // 3. jika SUCCESS
        if ($status === 'settlement' || $status === 'paid') {

            $order->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

            $payment->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

            $order->content->update([
                'status' => 'waiting_approval'
            ]);
        }

        // 4. jika gagal
        if (in_array($status, ['expire', 'cancel', 'deny'])) {

            $order->update([
                'status' => 'failed'
            ]);

            $payment->update([
                'status' => 'failed'
            ]);

            $order->content->update([
                'status' => 'payment_failed'
            ]);
        }

        return response()->json(['message' => 'ok']);
    }
}
