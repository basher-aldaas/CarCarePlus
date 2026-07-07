<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. حساب Super Admin
        $superAdmin = User::factory()->create([
            'name' => 'المدير العام',
            'email' => 'superadmin@system.com',
            'phone' => '0500000001',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        // 2. حساب Admin (مدير فرع)
        $admin = User::factory()->create([
            'name' => 'مدير الفرع الرئيسي',
            'email' => 'admin@system.com',
            'phone' => '0500000002',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // 3. حساب ورشة شريكة (Workshop)
        $workshop = User::factory()->create([
            'name' => 'مركز صيانة التميز',
            'email' => 'workshop@system.com',
            'phone' => '0500000003',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $workshop->assignRole('workshop');

        // 4. حساب عميل أفراد
        $customer = User::factory()->create([
            'name' => 'أحمد العتيبي (عميل)',
            'email' => 'customer@system.com',
            'phone' => '0500000004',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $customer->assignRole('customer_personal');

        // 5. حساب عميل شركة / أسطول
        $companyCustomer = User::factory()->create([
            'name' => 'شركة أسطول النقل',
            'email' => 'company@system.com',
            'phone' => '0500000005',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $companyCustomer->assignRole('customer_company');

        // 6. موظف غسيل
        $washer = User::factory()->create([
            'name' => 'فني غسيل 1',
            'email' => 'washer@system.com',
            'phone' => '0500000006',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $washer->assignRole('employee_washer');

        // 7. موظف ميكانيكي
        $mechanic = User::factory()->create([
            'name' => 'مهندس ميكانيكي 1',
            'email' => 'mechanic@system.com',
            'phone' => '0500000007',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $mechanic->assignRole('employee_mechanic');

        // إنشاء عملاء عشوائيين (20 عميل)
        User::factory(20)->create()->each(function ($user) {
            $user->assignRole('customer_personal');
        });
    }
}
