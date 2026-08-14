<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function getSettings()
    {
        $settings = DB::table('c2_settings')->where('tenant_id', 1)->first();
        if (!$settings) {
            $defaultData = [
                'company_data'  => json_encode([
                    'name'        => 'Company 2 Platform',
                    'email'       => 'support@company2.sa',
                    'phone'       => '+966 50 123 4567',
                    'address'     => 'King Fahd Road, Riyadh, Saudi Arabia',
                    'vat_number'  => '300123456700003',
                    'cr_number'   => '1010123456',
                    'currency'    => 'SAR',
                ]),
                'appearance'    => json_encode([
                    'theme'         => 'light',
                    'primary_color' => '#00aaff',
                    'language'      => 'en',
                    'font_family'   => 'Inter',
                ]),
                'notifications' => json_encode([
                    'email_enabled'    => true,
                    'sms_enabled'      => true,
                    'whatsapp_enabled' => false,
                    'new_booking'      => true,
                    'booking_cancelled'=> true,
                    'review_received'  => true,
                ]),
                'security'      => json_encode([
                    'two_factor_auth'     => false,
                    'session_timeout_min' => 60,
                    'password_expiry_days'=> 90,
                ])
            ];

            $id = DB::table('c2_settings')->insertGetId(array_merge(['tenant_id' => 1, 'created_at' => now(), 'updated_at' => now()], $defaultData));
            $settings = DB::table('c2_settings')->where('id', $id)->first();
        }

        return response()->json([
            'company'       => json_decode($settings->company_data ?? '{}', true),
            'appearance'    => json_decode($settings->appearance ?? '{}', true),
            'notifications' => json_decode($settings->notifications ?? '{}', true),
            'security'      => json_decode($settings->security ?? '{}', true),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $settings = DB::table('c2_settings')->where('tenant_id', 1)->first();
        
        $data = [];
        if ($request->has('company')) {
            $data['company_data'] = json_encode($request->input('company'));
        }
        if ($request->has('appearance')) {
            $data['appearance'] = json_encode($request->input('appearance'));
        }
        if ($request->has('notifications')) {
            $data['notifications'] = json_encode($request->input('notifications'));
        }
        if ($request->has('security')) {
            $data['security'] = json_encode($request->input('security'));
        }

        $data['updated_at'] = now();

        if ($settings) {
            DB::table('c2_settings')->where('tenant_id', 1)->update($data);
        } else {
            $data['tenant_id'] = 1;
            $data['created_at'] = now();
            DB::table('c2_settings')->insert($data);
        }

        return response()->json(['success' => true, 'message' => 'Settings saved successfully']);
    }

    public function updateCompany(Request $request)
    {
        return $this->updateSettings($request);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();
        if (!$user || !Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);
        return response()->json(['success' => true, 'message' => 'Password updated successfully']);
    }
}
