<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        return response()->json(PricingPlan::orderBy('monthly_price')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'monthly_price' => 'required|numeric',
            'annual_price' => 'required|numeric',
            'description' => 'nullable|string',
            'featured' => 'boolean',
            'active' => 'boolean',
            'features' => 'nullable|array'
        ]);

        $validated['tenant_id'] = 1;
        $plan = PricingPlan::create($validated);
        return response()->json($plan, 201);
    }

    public function update(Request $request, PricingPlan $pricing)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'monthly_price' => 'sometimes|numeric',
            'annual_price' => 'sometimes|numeric',
            'description' => 'nullable|string',
            'featured' => 'boolean',
            'active' => 'boolean',
            'features' => 'nullable|array'
        ]);

        $pricing->update($validated);
        return response()->json($pricing);
    }
    
    public function toggle(PricingPlan $pricing)
    {
        $pricing->update(['active' => !$pricing->active]);
        return response()->json($pricing);
    }
}
