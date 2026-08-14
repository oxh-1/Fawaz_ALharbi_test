<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        $admin = User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@company2.com')],
            [
                'name'           => env('ADMIN_NAME', 'Super Admin (Fawaz)'),
                'password'       => Hash::make(env('ADMIN_PASSWORD', 'Admin123!')),
                'tenant_id'      => 1,
                'is_super_admin' => true,
                'status'         => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2. Accounting & Finance User
        $accountant = User::updateOrCreate(
            ['email' => 'accountant@company2.com'],
            [
                'name'           => 'Tariq Al-Ghamdi (Accounting)',
                'password'       => Hash::make('Accountant123!'),
                'tenant_id'      => 1,
                'is_super_admin' => false,
                'status'         => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 3. Customer Support Specialist
        $support = User::updateOrCreate(
            ['email' => 'support@company2.com'],
            [
                'name'           => 'Reem Al-Shammari (Support)',
                'password'       => Hash::make('Support123!'),
                'tenant_id'      => 1,
                'is_super_admin' => false,
                'status'         => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 4. Operations & Merchant Manager
        $operations = User::updateOrCreate(
            ['email' => 'operations@company2.com'],
            [
                'name'           => 'Majed Al-Harbi (Operations)',
                'password'       => Hash::make('Operations123!'),
                'tenant_id'      => 1,
                'is_super_admin' => false,
                'status'         => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Assign Roles if roles table exists
        $adminRole = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Administrator', 'tenant_id' => 1]);
        $accountantRole = Role::firstOrCreate(['slug' => 'accountant'], ['name' => 'Finance & Accounting Specialist', 'tenant_id' => 1]);
        $supportRole = Role::firstOrCreate(['slug' => 'support'], ['name' => 'Customer Support Specialist', 'tenant_id' => 1]);
        $operationsRole = Role::firstOrCreate(['slug' => 'operations'], ['name' => 'Operations & Merchant Manager', 'tenant_id' => 1]);

        $admin->roles()->sync([$adminRole->id]);
        $accountant->roles()->sync([$accountantRole->id]);
        $support->roles()->sync([$supportRole->id]);
        $operations->roles()->sync([$operationsRole->id]);

        $this->command->info('✅ Multi-role enterprise users seeded successfully.');
        $this->call([DemoDataSeeder::class]);
    }
}
