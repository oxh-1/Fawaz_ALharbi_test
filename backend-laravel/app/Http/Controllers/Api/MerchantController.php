<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function index(Request $request)
    {
        $query = Merchant::where('tenant_id', 1);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email',
            'category' => 'nullable|string',
            'status'   => 'nullable|string'
        ]);

        $validated['tenant_id'] = 1;
        $validated['joined'] = now()->toDateString();
        
        $merchant = Merchant::create($validated);
        return response()->json($merchant, 201);
    }

    public function update(Request $request, Merchant $merchant)
    {
        $validated = $request->validate([
            'name'     => 'sometimes|string',
            'email'    => 'sometimes|email',
            'category' => 'nullable|string',
            'status'   => 'nullable|string'
        ]);

        $merchant->update($validated);
        return response()->json($merchant);
    }

    public function destroy(Merchant $merchant)
    {
        $merchant->delete();
        return response()->json(['success' => true]);
    }
}
