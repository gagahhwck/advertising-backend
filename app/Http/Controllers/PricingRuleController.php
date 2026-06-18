<?php

namespace App\Http\Controllers;

use App\Models\Advertising\PricingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricingRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PricingRule::list();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id'                => ['nullable'],
            'event_category_id' => ['required','integer','exists:event_categories,id'],
            'price'             => ['required', function ($attribute, $value, $fail) {
                if (preg_replace('/[^\d]/', '', (string) $value) === '') {
                    $fail("The {$attribute} must be a valid number.");
                }
            }],
            'reason'            => ['nullable','string'],
            'is_active'         => ['required','boolean'],
        ]);

        $data['price'] = $this->normalizePrice($request->price ?? $data['price']);
        $data['created_by'] = Auth::user()->username;
        if ($request->id) {
            $data['updated_by'] = Auth::user()->username;
        }

        if ($data['is_active'] && !$request->id && PricingRule::where('event_category_id', $data['event_category_id'])
            ->where('is_active', true)
            ->exists()
        ) {
            return response()->json([
                'message' => 'Cannot add more price for this Event Category, because there is active price Exists'
            ], 422);
        }

        if ($request->id) {
            $price = PricingRule::updateOrCreate(['id' => $request->id], $data);
        } else {
            $price = PricingRule::create($data);
        }

        return response()->json([
            'success'   => true,
            'message'   => $request->id ? 'Rule Price Successfully Updated' : 'Rule Price Successfully Created',
            'data'      => $price
        ]);
    }

    private function normalizePrice($price)
    {
        return (int) preg_replace('/[^\d]/', '', (string) $price);
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
