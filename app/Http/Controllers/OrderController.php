<?php

namespace App\Http\Controllers;

use App\Models\Advertising\Content;
use App\Models\Advertising\Order;
use App\Models\Advertising\PricingRule;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $content_id)
    {
        $content = Content::with('event.type')->findOrFail($content_id);

        // 1. VALIDASI STATE CONTENT
        if ($content->status !== 'draft') {
            return response()->json([
                'message' => 'Content On Progress or Not Valid'
            ], 422);
        }

        // 2. CEK apakah sudah ada order
        if ($content->order) {
            return response()->json([
                'message' => 'Order Already Processing'
            ], 422);
        }

        // 3. HITUNG PRICE dari pricing engine
        $price = $this->calculatePrice($content);

        // 4. CREATE ORDER
        $order = Order::create([
            'content_id' => $content->id,
            'order_code' => $this->generateOrderCode(),
            'amount' => $price,
            'status' => 'pending',
        ]);

        // 5. UPDATE CONTENT STATE
        $content->update([
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Order created',
            'data' => $order
        ]);
    }

    private function generateOrderCode()
    {
        return 'UIII-ORD-' . now()->format('YmdHis') . '-' . rand(100, 999);
    }

    private function calculatePrice(Content $content)
    {
        $category_id = $content->event->event_type ?? null;

        if (! $category_id) {
            return 50000;
        }

        return PricingRule::where('event_category_id', $category_id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->value('price')
            ?? 50000;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
