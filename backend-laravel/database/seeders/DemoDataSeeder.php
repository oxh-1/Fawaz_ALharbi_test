<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_US');
        $now = now();



        // 2. Clear Existing Demo Data safely
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('merchants')->truncate();
        DB::table('categories')->truncate();
        DB::table('services')->truncate();
        DB::table('bookings')->truncate();
        DB::table('reviews')->truncate();
        DB::table('pricing_plans')->truncate();
        DB::table('ads')->truncate();
        DB::table('contact_messages')->truncate();
        DB::table('content_pages')->truncate();
        DB::table('settlements')->truncate();
        DB::table('c2_settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. Seed 20 Categories
        $categories = [
            'Food & Beverage', 'Retail', 'Technology', 'Healthcare', 'Beauty & Wellness',
            'Education', 'Automotive', 'Real Estate', 'Logistics', 'Finance',
            'Entertainment', 'Hospitality', 'Sports & Fitness', 'Consulting', 'Events',
            'Marketing', 'Manufacturing', 'Agriculture', 'Home Services', 'Travel'
        ];
        $catIcons = ['🍔','🛍️','💻','🏥','💆','📚','🚗','🏢','🚚','💰','🎭','🏨','🏋️','📈','🎉','📱','🏭','🌾','🔧','✈️'];
        foreach ($categories as $i => $cat) {
            DB::table('categories')->insert([
                'tenant_id' => 1,
                'name' => $cat,
                'icon' => $catIcons[$i],
                'services' => rand(1, 15),
                'status' => 'Active',
                'created_at' => clone $now->subDays(rand(1, 30)),
            ]);
        }

        // 4. Seed 10 Merchants
        $merchants = [];
        for ($i = 0; $i < 10; $i++) {
            $name = $faker->company;
            $merchants[] = $name;
            DB::table('merchants')->insert([
                'tenant_id' => 1,
                'name' => $name,
                'email' => $faker->companyEmail,
                'category' => $faker->randomElement($categories),
                'joined' => clone $now->subDays(rand(1, 100)),
                'status' => $faker->randomElement(['Active', 'Active', 'Active', 'Pending', 'Inactive']),
                'created_at' => clone $now->subDays(rand(1, 100)),
            ]);
        }

        // 5. Seed 30 Services
        for ($i = 0; $i < 30; $i++) {
            DB::table('services')->insert([
                'tenant_id' => 1,
                'name' => $faker->catchPhrase,
                'category' => $faker->randomElement($categories),
                'merchant' => $faker->randomElement($merchants),
                'tags' => json_encode($faker->randomElements(['featured', 'new', 'premium', 'popular', 'discount'], rand(0, 2))),
                'active' => $faker->boolean(80),
                'created_at' => clone $now->subDays(rand(1, 50)),
            ]);
        }

        // 6. Seed Bookings
        $statuses = ['confirmed', 'pending', 'cancelled'];
        for ($i = 1; $i <= 15; $i++) {
            DB::table('bookings')->insert([
                'tenant_id' => 1,
                'customer_id' => 1,
                'merchant_id' => rand(1, 5),
                'service_id' => rand(1, 20),
                'client' => $faker->name,
                'merchant' => $faker->randomElement($merchants),
                'service' => $faker->catchPhrase,
                'scheduled_at' => clone $now->addDays(rand(1, 30))->setTime(rand(8, 20), 0, 0),
                'duration_minutes' => 60,
                'base_price' => rand(50, 300),
                'total_price' => rand(50, 300),
                'status' => $faker->randomElement($statuses),
                'payment_status' => 'completed',
                'created_at' => clone $now->subDays(rand(1, 30)),
            ]);
        }

        // 7. Seed Reviews
        for ($i = 0; $i < 15; $i++) {
            DB::table('reviews')->insert([
                'tenant_id' => 1,
                'author' => $faker->name,
                'merchant' => $faker->randomElement($merchants),
                'rating' => rand(1, 5),
                'text' => $faker->sentence(10),
                'date' => clone $now->subDays(rand(1, 30)),
                'status' => $faker->randomElement(['Approved', 'Approved', 'Pending', 'Rejected']),
                'created_at' => clone $now->subDays(rand(1, 30)),
            ]);
        }

        // 8. Seed Pricing Plans
        DB::table('pricing_plans')->insert([
            ['tenant_id' => 1, 'name' => 'Basic', 'monthly_price' => 49, 'annual_price' => 470, 'description' => 'Great for small startups', 'featured' => 0, 'active' => 1, 'features' => json_encode([['label' => '5 Merchants', 'included' => true], ['label' => 'Basic Support', 'included' => true]])],
            ['tenant_id' => 1, 'name' => 'Pro', 'monthly_price' => 149, 'annual_price' => 1430, 'description' => 'For growing teams', 'featured' => 1, 'active' => 1, 'features' => json_encode([['label' => '50 Merchants', 'included' => true], ['label' => 'Priority Support', 'included' => true]])],
            ['tenant_id' => 1, 'name' => 'Enterprise', 'monthly_price' => 399, 'annual_price' => 3830, 'description' => 'Large scale solutions', 'featured' => 0, 'active' => 0, 'features' => json_encode([['label' => 'Unlimited Merchants', 'included' => true], ['label' => 'Premium Support', 'included' => true]])],
        ]);

        // 9. Seed Ads
        for ($i = 0; $i < 8; $i++) {
            DB::table('ads')->insert([
                'tenant_id' => 1,
                'name' => $faker->words(3, true) . ' Campaign',
                'type' => $faker->randomElement(['Banner', 'Video', 'Sponsored', 'Push']),
                'start' => clone $now->subDays(rand(1, 10)),
                'end' => clone $now->addDays(rand(1, 30)),
                'impressions' => rand(1000, 50000),
                'clicks' => rand(100, 5000),
                'status' => $faker->randomElement(['Active', 'Inactive']),
                'created_at' => now(),
            ]);
        }

        // 10. Seed Content Pages
        $pages = ['About Us', 'Terms of Service', 'Privacy Policy', 'FAQ', 'Careers'];
        foreach ($pages as $p) {
            DB::table('content_pages')->insert([
                'tenant_id' => 1,
                'title' => $p,
                'slug' => '/' . strtolower(str_replace(' ', '-', $p)),
                'content' => $faker->paragraphs(3, true),
                'meta' => $faker->sentence,
                'status' => $faker->randomElement(['Published', 'Draft', 'Published']),
                'updated' => clone $now->subDays(rand(1, 30)),
                'created_at' => now(),
            ]);
        }

        // 11. Seed Settlements
        for ($i = 1; $i <= 10; $i++) {
            DB::table('settlements')->insert([
                'tenant_id' => 1,
                'settlement_id' => 'STL-' . (300 + $i),
                'merchant' => $faker->randomElement($merchants),
                'date' => clone $now->subDays(rand(1, 15)),
                'amount' => rand(1000, 15000),
                'method' => $faker->randomElement(['Bank Transfer', 'SADAD', 'Credit Card']),
                'status' => $faker->randomElement(['Paid', 'Pending', 'Processing']),
                'created_at' => now(),
            ]);
        }

        // 12. Seed Settings
        DB::table('c2_settings')->insert([
            'tenant_id' => 1,
            'company_data' => json_encode(['name' => 'Company 2 Nexus', 'website' => 'https://company2.com', 'email' => 'admin@company2.com', 'phone' => '+966500000000', 'address' => 'Riyadh, Saudi Arabia']),
            'appearance' => json_encode(['theme' => 'Light', 'primary_color' => '#00aaff', 'sidebar_style' => 'Full', 'compact_mode' => false]),
            'notifications' => json_encode(['email_alerts' => true, 'sms_alerts' => true, 'marketing' => true, 'admin_summary' => 'Weekly']),
            'security' => json_encode(['two_factor' => true, 'session_timeout' => '30m', 'ip_whitelist' => '']),
            'created_at' => now(),
        ]);
        
        DB::table('chat_messages')->updateOrInsert(
            ['id' => 1],
            ['tenant_id' => 1, 'sender_id' => 1, 'message' => 'System connected successfully.', 'is_read' => 0, 'created_at' => now()]
        );
    }
}
