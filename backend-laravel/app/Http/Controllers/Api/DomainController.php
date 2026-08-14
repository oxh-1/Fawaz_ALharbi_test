<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    /**
     * GET /api/domain/check?host=example.com
     * Called by the Vue frontend on every load to validate the domain.
     */
    public function check(Request $request)
    {
        $host = $request->query('host', $request->getHost());

        // Strip port if present
        $host = preg_replace('/:\d+$/', '', $host);

        // Always allow localhost / 127.0.0.1 for development
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return response()->json([
                'valid'     => true,
                'tenant_id' => 1,
                'domain'    => $host,
                'type'      => 'development',
            ]);
        }

        // Look up domain in DB
        $domain = Domain::where('domain', $host)
            ->where('status', 'active')
            ->with('tenant:id,name,slug,status')
            ->first();

        if (!$domain) {
            // Log the invalid domain attempt
            \Log::warning("[DomainValidation] Unknown domain attempted: {$host}", [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);

            return response()->json([
                'valid'   => false,
                'domain'  => $host,
                'message' => "Domain '{$host}' is not registered in this system.",
            ], 403);
        }

        if ($domain->tenant && $domain->tenant->status !== 'active') {
            return response()->json([
                'valid'   => false,
                'domain'  => $host,
                'message' => "The tenant for this domain is {$domain->tenant->status}.",
            ], 403);
        }

        return response()->json([
            'valid'     => true,
            'tenant_id' => $domain->tenant_id,
            'domain'    => $host,
            'type'      => $domain->type,
            'tenant'    => $domain->tenant,
        ]);
    }

    // GET /api/admin/domains
    public function index(Request $request)
    {
        $domains = Domain::with('tenant:id,name,slug')->orderByDesc('created_at')->get();
        return response()->json(['success' => true, 'data' => $domains]);
    }

    // POST /api/admin/domains
    public function store(Request $request)
    {
        $request->validate([
            'domain'    => 'required|string|unique:domains,domain',
            'tenant_id' => 'required|exists:tenants,id',
            'type'      => 'required|in:main,subdomain,custom',
        ]);

        $domain = Domain::create($request->only('domain', 'tenant_id', 'type'));

        return response()->json(['success' => true, 'data' => $domain], 201);
    }

    // DELETE /api/admin/domains/{domain}
    public function destroy(Domain $domain)
    {
        $domain->delete();
        return response()->json(['success' => true, 'message' => 'Domain removed.']);
    }
}
