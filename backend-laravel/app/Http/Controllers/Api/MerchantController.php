<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MerchantController extends Controller
{
    // GET /api/merchants
    public function index(Request $request)
    {
        $query = Merchant::where('tenant_id', $request->user()->tenant_id)
            ->with('category:id,name,icon');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $merchants = $query->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json(['success' => true, 'data' => $merchants]);
    }

    // POST /api/merchants
    public function store(Request $request)
    {
        $this->authorize('merchants.create');

        $v = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'address'     => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'status'      => 'nullable|in:active,inactive,pending,suspended',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $merchant = Merchant::create([
            ...$v->validated(),
            'tenant_id'   => $request->user()->tenant_id,
            'joined_date' => now()->toDateString(),
        ]);

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'tenant_id' => $request->user()->tenant_id,
            'action'    => 'create',
            'model'     => 'Merchant',
            'model_id'  => $merchant->id,
            'new_data'  => $merchant->toArray(),
        ]);

        return response()->json(['success' => true, 'data' => $merchant->load('category')], 201);
    }

    // GET /api/merchants/{id}
    public function show(Request $request, Merchant $merchant)
    {
        $this->checkTenant($merchant, $request);
        return response()->json(['success' => true, 'data' => $merchant->load('category', 'services')]);
    }

    // PUT /api/merchants/{id}
    public function update(Request $request, Merchant $merchant)
    {
        $this->authorize('merchants.edit');
        $this->checkTenant($merchant, $request);

        $old = $merchant->toArray();

        $v = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string|max:255',
            'email'       => 'sometimes|required|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'address'     => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'status'      => 'nullable|in:active,inactive,pending,suspended',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $merchant->update($v->validated());

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'tenant_id' => $request->user()->tenant_id,
            'action'    => 'update',
            'model'     => 'Merchant',
            'model_id'  => $merchant->id,
            'old_data'  => $old,
            'new_data'  => $merchant->fresh()->toArray(),
        ]);

        return response()->json(['success' => true, 'data' => $merchant->fresh()->load('category')]);
    }

    // DELETE /api/merchants/{id}
    public function destroy(Request $request, Merchant $merchant)
    {
        $this->authorize('merchants.delete');
        $this->checkTenant($merchant, $request);

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'tenant_id' => $request->user()->tenant_id,
            'action'    => 'delete',
            'model'     => 'Merchant',
            'model_id'  => $merchant->id,
            'old_data'  => $merchant->toArray(),
        ]);

        $merchant->delete();

        return response()->json(['success' => true, 'message' => 'Merchant deleted.']);
    }

    // PATCH /api/merchants/{id}/status
    public function updateStatus(Request $request, Merchant $merchant)
    {
        $this->authorize('merchants.edit');
        $this->checkTenant($merchant, $request);

        $v = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,pending,suspended',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $merchant->update(['status' => $request->status]);

        return response()->json(['success' => true, 'data' => $merchant]);
    }

    private function checkTenant(Merchant $merchant, Request $request): void
    {
        if ($merchant->tenant_id !== $request->user()->tenant_id && !$request->user()->is_super_admin) {
            abort(403, 'Access denied to this merchant.');
        }
    }
}
