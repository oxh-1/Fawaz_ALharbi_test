<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function getSettings()
    {
        $settings = DB::table('c2_settings')->where('tenant_id', 1)->first();
        $notifs = $settings && $settings->notifications ? json_decode($settings->notifications, true) : [
            'email_enabled'     => true,
            'sms_enabled'       => true,
            'whatsapp_enabled'  => false,
            'push_enabled'      => true,
            'booking_alerts'    => true,
            'review_alerts'     => true,
            'settlement_alerts' => true,
            'marketing_alerts'  => false,
        ];

        return response()->json($notifs);
    }

    public function saveSettings(Request $request)
    {
        $settings = $request->input('settings', $request->all());

        DB::table('c2_settings')->where('tenant_id', 1)->update([
            'notifications' => json_encode($settings),
            'updated_at'    => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Notification preferences saved.']);
    }

    public function testEmail(Request $request)
    {
        $email = $request->input('email', 'admin@company2.sa');
        return response()->json([
            'success' => true,
            'message' => "Test notification sent successfully to {$email}."
        ]);
    }

    public function getLogs(Request $request)
    {
        $logs = [
            ['id' => 1, 'type' => 'Email', 'recipient' => 'ahmed@mail.com', 'subject' => 'Booking Confirmed #1024', 'status' => 'Delivered', 'date' => now()->subMinutes(12)->toDateTimeString()],
            ['id' => 2, 'type' => 'SMS', 'recipient' => '+966501111111', 'subject' => 'Your code is 4821', 'status' => 'Delivered', 'date' => now()->subHours(1)->toDateTimeString()],
            ['id' => 3, 'type' => 'Push', 'recipient' => 'Device_iPhone14', 'subject' => 'New review received (5 stars)', 'status' => 'Delivered', 'date' => now()->subHours(3)->toDateTimeString()],
            ['id' => 4, 'type' => 'Email', 'recipient' => 'sara@ex.com', 'subject' => 'Settlement processed: SET-4921', 'status' => 'Delivered', 'date' => now()->subDay()->toDateTimeString()],
        ];

        return response()->json($logs);
    }
}
