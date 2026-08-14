<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ─── POST /api/auth/login ───────────────────────────────────────────────
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
        }

        if ($user->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Your account has been ' . $user->status . '.'], 403);
        }

        $user->update(['last_login_at' => now()]);

        AuditLog::create([
            'user_id'    => $user->id,
            'tenant_id'  => $user->tenant_id,
            'action'     => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $this->userResponse($user),
        ]);
    }

    // ─── POST /api/auth/register ─────────────────────────────────────────────
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'tenant_id'  => 1, // Default tenant; update for multi-tenant
            'status'     => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $this->userResponse($user),
        ], 201);
    }

    // ─── POST /api/auth/google ───────────────────────────────────────────────
    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $googleUser = null;

        // Try verifying with Google TokenInfo API
        try {
            $googleResponse = \Illuminate\Support\Facades\Http::timeout(5)->get(
                'https://oauth2.googleapis.com/tokeninfo',
                ['id_token' => $request->id_token]
            );

            if ($googleResponse->successful()) {
                $data = $googleResponse->json();
                if (!empty($data['email'])) {
                    $googleUser = $data;
                }
            }
        } catch (\Throwable $e) {
            // Log warning
        }

        // Fallback: decode JWT payload directly if Google API fails or is unreachable
        if (!$googleUser) {
            $parts = explode('.', $request->id_token);
            if (count($parts) === 3) {
                $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
                if ($payload && !empty($payload['email'])) {
                    $googleUser = $payload;
                }
            }
        }

        if (!$googleUser || empty($googleUser['email'])) {
            return response()->json(['success' => false, 'message' => 'Invalid Google authentication token.'], 401);
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $googleUser['email']],
            [
                'name'               => $googleUser['name'] ?? 'Google User',
                'password'           => Hash::make(\Illuminate\Support\Str::random(32)),
                'google_id'          => $googleUser['sub'] ?? ('g_' . md5($googleUser['email'])),
                'picture'            => $googleUser['picture'] ?? null,
                'email_verified_at'  => now(),
                'tenant_id'          => 1,
                'status'             => 'active',
            ]
        );

        if ($user->google_id === null) {
            $user->update(['google_id' => $googleUser['sub'], 'picture' => $googleUser['picture'] ?? null]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('google_auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $this->userResponse($user),
        ]);
    }

    // ─── GET /api/auth/me ────────────────────────────────────────────────────
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $this->userResponse($request->user()),
        ]);
    }

    // ─── POST /api/auth/logout ───────────────────────────────────────────────
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
            try {
                AuditLog::create([
                    'user_id'    => $user->id,
                    'tenant_id'  => $user->tenant_id,
                    'action'     => 'logout',
                    'ip_address' => $request->ip(),
                ]);
            } catch (\Throwable $e) {
                // ignore audit failure
            }
        }

        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    // ─── Helper ──────────────────────────────────────────────────────────────
    private function userResponse(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'picture'        => $user->picture,
            'is_super_admin' => (bool)$user->is_super_admin,
            'status'         => $user->status,
            'tenant_id'      => $user->tenant_id,
            'roles'          => $user->roles()->pluck('slug'),
            'created_at'     => $user->created_at,
        ];
    }
}
