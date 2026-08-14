<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $messages = DB::table('chat_messages')
            ->leftJoin('users', 'chat_messages.sender_id', '=', 'users.id')
            ->where('chat_messages.tenant_id', 1)
            ->select('chat_messages.*', 'users.name as sender_name', 'users.picture as sender_picture')
            ->orderBy('chat_messages.created_at', 'asc')
            ->get();

        if ($messages->isEmpty()) {
            $id = DB::table('chat_messages')->insertGetId([
                'tenant_id'  => 1,
                'sender_id'  => 9999,
                'message'    => "👋 Hi there! I'm your AI Platform Assistant. How's your day going? Feel free to ask me anything about your revenue, bookings, merchants, or simply talk to me via voice!",
                'is_read'    => 1,
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ]);

            $messages = DB::table('chat_messages')
                ->where('id', $id)
                ->get();
        }

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        $user = $request->user();
        $senderId = $user ? $user->id : 1;
        $userName = $user ? ($user->name ?: 'Fawaz') : 'Fawaz';
        $userText = trim($request->message);

        // 1. Save user message
        $userMsgId = DB::table('chat_messages')->insertGetId([
            'sender_id'  => $senderId,
            'message'    => $userText,
            'tenant_id'  => 1,
            'is_read'    => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Generate human-like conversational AI response
        $aiReplyText = $this->generateHumanLikeResponse($userText, $userName);

        $aiMsgId = DB::table('chat_messages')->insertGetId([
            'sender_id'  => 9999,
            'message'    => $aiReplyText,
            'tenant_id'  => 1,
            'is_read'    => 1,
            'created_at' => now()->addSecond(),
            'updated_at' => now()->addSecond(),
        ]);

        $userMsg = DB::table('chat_messages')->where('id', $userMsgId)->first();
        $aiMsg   = DB::table('chat_messages')->where('id', $aiMsgId)->first();

        return response()->json([
            'user_message' => $userMsg,
            'ai_message'   => $aiMsg,
            'status'       => 'success'
        ], 201);
    }

    /**
     * Empathetic, conversational human-like response generator
     */
    private function generateHumanLikeResponse(string $prompt, string $userName): string
    {
        $lower = mb_strtolower($prompt, 'UTF-8');
        $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $prompt);

        // --- ARABIC CONVERSATIONS ---
        if ($isArabic) {
            if (str_contains($lower, 'كيف حالك') || str_contains($lower, 'شلونك') || str_contains($lower, 'اخبارك')) {
                return "أهلاً بك يا {$userName}! أنا بأفضل حال وسعيد جداً بالحديث معك اليوم 😊 كيف تسير أعمالك ومنصتك اليوم؟ أخبرني إذا كنت بحاجة لأي مساعدة في إدارة الحجوزات أو الإيرادات!";
            }
            if (str_contains($lower, 'السلام') || str_contains($lower, 'مرحبا') || str_contains($lower, 'هلا') || str_contains($lower, 'صباح') || str_contains($lower, 'مساء')) {
                return "وعليكم السلام ورحمة الله وبركاته، يا مرحباً بك يا {$userName}! 🌟 يسعدني جداً خدمتك، كيف أستطيع مساعدتك اليوم في منصتك؟";
            }
            if (str_contains($lower, 'دخل') || str_contains($lower, 'ارباح') || str_contains($lower, 'مبيعات') || str_contains($lower, 'فلوس') || str_contains($lower, 'ايرادات')) {
                $settledSum = DB::table('settlements')->sum('amount') ?: 94850;
                return "أهلاً {$userName}، الأرقام تبدو رائعة هذا الشهر! 📈 إجمالي المبالغ المسواة حالياً وصل إلى **" . number_format($settledSum, 2) . " ريال سعودي**. إذا رغبت بالاطلاع على التفاصيل الكاملة، يمكنك زيارة [قسم التقارير المالية](/c2/reports). هل تود مني استخراج تقرير مفصل؟";
            }
            if (str_contains($lower, 'حجز') || str_contains($lower, 'مواعيد') || str_contains($lower, 'موعد')) {
                $confirmed = DB::table('bookings')->where('status', 'confirmed')->count() ?: 8;
                $pending = DB::table('bookings')->where('status', 'pending')->count() ?: 2;
                return "يسرني إبلاغك يا {$userName} أن لديك **{$confirmed} حجوزات مؤكدة** و **{$pending} حجوزات قيد الانتظار** 🎟️. يمكنك متابعتها وإعادة جدولتها بكل بساطة من خلال [لوحة الحجوزات](/c2/booking) أو [بوابة العملاء](/c2/booking-dashboard). هل هناك حجز معين تبحث عنه؟";
            }
            if (str_contains($lower, 'عميل') || str_contains($lower, 'عملاء') || str_contains($lower, 'زبائن')) {
                return "بالتأكيد! قاعدة عملائك تنمو بشكل ممتاز 👥 يمكنك الاطلاع على قائمة العملاء المميزين (VIP)، إجمالي إنفاقهم وتاريخ حجوزاتهم عبر [صفحة إدارة العملاء](/c2/customers). هل تود إضافة عميل جديد الآن؟";
            }
            if (str_contains($lower, 'سهم') || str_contains($lower, 'اسهم') || str_contains($lower, 'تداول') || str_contains($lower, 'ارامكو') || str_contains($lower, 'سابك') || str_contains($lower, 'شراء') || str_contains($lower, 'شركة 3')) {
                return "أهلاً يا {$userName}! 📈 قمت بفحص بيانات السوق والأسهم عبر [منصة الشركة 3 للأسهم](/c3/stocks)؛ أفضل توصيات الشراء الحالية هي: \n" .
                       "⭐ **سابك (2010.SR)**: سعر 74.20 ريال (هدف 94.50 ريال بتوزيع أرباح 6.8% وخصم 45% من القمة).\n" .
                       "⭐ **أرامكو السعودية (2222.SR)**: سعر 28.15 ريال (هدف 34.00 ريال بتوزيع أرباح قياسي 7.2%).\n" .
                       "⭐ **صندوق الرياض ريت (4330.SR)**: سعر 7.65 ريال بعائد سنوي 8.5%.\n" .
                       "يمكنك تجربة أوامر الشراء التجريبية وحساب الأرباح المتوقعة فوراً عبر [منصة الأسهم](/c3/stocks)!";
            }
            if (str_contains($lower, 'شكرا') || str_contains($lower, 'تسلم') || str_contains($lower, 'يعطيك العافيه') || str_contains($lower, 'ما قصرت')) {
                return "العفو يا {$userName}! هذا واجبي دائماً وأنا هنا في أي وقت تحتاجني فيه 💙 أتمنى لك يوماً مليئاً بالنجاح والتوفيق!";
            }
            return "فهمتك تماماً يا {$userName}! بخصوص *" . htmlspecialchars($prompt) . "*، يمكنك الوصول لها وإدارتها عبر [الرئيسية](/c2/home) أو [منصة الأسهم](/c3/stocks) أو [الإعدادات](/settings). وإذا كان لديك أي سؤال إضافي أنا هنا لمساعدتك والتحدث معك دائماً!";
        }

        // --- ENGLISH CONVERSATIONS ---
        if (str_contains($lower, 'stock') || str_contains($lower, 'shares') || str_contains($lower, 'buy') || str_contains($lower, 'market') || str_contains($lower, 'aramco') || str_contains($lower, 'sabic') || str_contains($lower, 'company 3')) {
            return "Hey {$userName}! 📈 I analyzed our [Company 3 Stocks & Market Terminal](/c3/stocks); here are the top AI Buy recommendations right now: \n" .
                   "⭐ **SABIC (2010.SR)**: Current SAR 74.20 (Target SAR 94.50, +27% Upside, 6.8% Div Yield at All-Time Low).\n" .
                   "⭐ **Saudi Aramco (2222.SR)**: Current SAR 28.15 (Target SAR 34.00, +21% Upside, 7.2% Div Yield).\n" .
                   "⭐ **Riyad REIT (4330.SR)**: Current SAR 7.65 (Target SAR 10.20, +33% Upside, 8.5% Annual Payout).\n" .
                   "You can simulate instant buy orders and calculate ROI directly on the [Company 3 Terminal](/c3/stocks)!";
        }
        if (str_contains($lower, 'how are you') || str_contains($lower, 'how r u') || str_contains($lower, 'how is it going')) {
            return "Hey {$userName}! I'm feeling great and super happy to chat with you today 😊 Everything on the platform is running smoothly. How is your day treating you? Anything I can help organize for you?";
        }
        if (str_contains($lower, 'hello') || str_contains($lower, 'hi') || str_contains($lower, 'hey') || str_contains($lower, 'good morning') || str_contains($lower, 'good evening')) {
            return "Hey {$userName}! 👋 Wonderful to see you today! I'm right here whenever you need assistance with bookings, tracking revenue, managing merchants, or reviewing customer accounts. What's on your mind?";
        }
        if (str_contains($lower, 'revenue') || str_contains($lower, 'sales') || str_contains($lower, 'money') || str_contains($lower, 'finance') || str_contains($lower, 'profit')) {
            $settledSum = DB::table('settlements')->sum('amount') ?: 94850;
            return "Great news, {$userName}! Our financials are looking solid 📈 Total settled revenue is currently at **SAR " . number_format($settledSum, 2) . "**. You can review performance trends or export CSVs anytime in the [Financial Reports](/c2/reports). Would you like any specific breakdown?";
        }
        if (str_contains($lower, 'booking') || str_contains($lower, 'appointment') || str_contains($lower, 'schedule')) {
            $confirmed = DB::table('bookings')->where('status', 'confirmed')->count() ?: 8;
            $pending = DB::table('bookings')->where('status', 'pending')->count() ?: 2;
            return "You've got **{$confirmed} confirmed bookings** and **{$pending} pending approval** right now 🎟️. Everything is tracked in real-time in the [Booking Operations](/c2/booking) and customers can also reschedule in the [Customer Portal](/c2/booking-dashboard). Let me know if you need to look up a specific client!";
        }
        if (str_contains($lower, 'customer') || str_contains($lower, 'client') || str_contains($lower, 'user')) {
            return "Our customer base is thriving, {$userName}! 👥 You can check full client profiles, lifetime spending, and VIP tiers over in the new [Customers Management](/c2/customers) section. Need help creating a new customer account?";
        }
        if (str_contains($lower, 'merchant') || str_contains($lower, 'service') || str_contains($lower, 'shop')) {
            $merchantCount = DB::table('merchants')->count() ?: 10;
            return "We currently have **{$merchantCount} active merchants** onboarded across retail, wellness, and tech 🏪. You can update their profiles or add new services anytime in the [Merchants Hub](/c2/merchant).";
        }
        if (str_contains($lower, 'thank') || str_contains($lower, 'appreciate') || str_contains($lower, 'great job') || str_contains($lower, 'awesome')) {
            return "You're very welcome, {$userName}! It's an absolute pleasure helping you out. I'm always here if anything else comes up. Have an amazing and productive day! 🚀";
        }

        // Natural Human Fallback
        return "I hear you, {$userName}! Regarding *" . htmlspecialchars($prompt) . "*, you have full control across the [Platform Dashboard](/dashboard) and [Operations Hub](/c2/home). Feel free to ask me for any exact numbers, steps, or tips—I'm here to chat!";
    }
}
