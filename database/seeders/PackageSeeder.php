<?php

namespace Database\Seeders;

use App\Enums\PackageType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء الباقة لجدول packages (لعملاء الأفراد)
        $packageId = DB::table('packages')->insertGetId([
            'name' => 'Monthly Shiny Package',
            'description' => 'باقة اللمعان الشهري تشمل 4 غسلات كاملة وبسعر موفر',
            'type' => PackageType::MONTHLY->value,
            'is_company_package' => false,
            'price' => 350.00,
            'discount_pct' => 0.0,
            'services_count' => 4,
            'valid_days' => 30,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. ربط الباقة بالخدمة (مثلاً ربطها بخدمة الغسيل الإيكو اللي معرفها سابقاً برقم id = 1)
        DB::table('package_services')->insert([
            'package_id' => $packageId,
            'service_id' => 1,
            'allowed_count' => 4
        ]);

        // 3. باقة مخصصة لعملاء الشركات (أسطول سيارات)
        $companyPackageId = DB::table('packages')->insertGetId([
            'name' => 'Fleet Care Package',
            'description' => 'باقة صيانة وعناية شهرية لأسطول سيارات الشركات',
            'type' => PackageType::COMPANY->value,
            'is_company_package' => true,
            'price' => 2500.00,
            'discount_pct' => 10.0,
            'services_count' => 20,
            'valid_days' => 30,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('package_services')->insert([
            'package_id' => $companyPackageId,
            'service_id' => 1,
            'allowed_count' => 20
        ]);
    }
}
