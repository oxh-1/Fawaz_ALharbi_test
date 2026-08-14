<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Domain;
use Illuminate\Support\Facades\Log;

/**
 * DomainValidation Middleware
 * Checks every incoming HTTP request's Host header against the domains table.
 * - localhost / 127.0.0.1 are always allowed (dev)
 * - Registered active domains are allowed
 * - Everything else → 403 JSON response
 */
class DomainValidation
{
    public function handle(Request $request, Closure $next)
    {
        $host = preg_replace('/:\d+$/', '', $request->getHost()); // strip port

        // Always allow dev origins
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return $next($request);
        }

        $domain = Domain::where('domain', $host)
            ->where('status', 'active')
            ->first();

        if (!$domain) {
            Log::warning("[DomainValidation] Rejected unknown domain: {$host}", [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'valid'   => false,
                'message' => "Domain '{$host}' is not registered.",
                'code'    => 'DOMAIN_NOT_FOUND',
            ], 403);
        }

        // Attach tenant to request for downstream use
        $request->attributes->set('tenant_id', $domain->tenant_id);

        return $next($request);
    }
}
