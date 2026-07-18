<?php
namespace Database\Seeders;

use App\Models\Car;
use App\Models\Company;
use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. المجموعات الثابتة الأساسية (Lookup Tables)
            CarTypeSeeder::class,
            CarBrandSeeder::class,
            CategorySeeder::class,
            MaterialUnitSeeder::class,
            ProblemTypeSeeder::class,
            PricingRuleTypeSeeder::class,

            // 2. إعدادات وتكوين النظام الثابتة والأدوار
            PointsConfigSeeder::class,
            SystemSettingSeeder::class,
            RolePermissionSeeder::class,
            AiRuleSeeder::class,           // <-- أضفه هنا (إعدادات AI ثابتة)
            SuggestedProblemSeeder::class, // <-- أضفه هنا (مشاكل مقترحة للنظام)

            // 3. الخدمات والمواد التشغيلية
            ServiceSeeder::class,
            SubServiceSeeder::class,
            MaterialSeeder::class,

            // 4. الحسابات والأدوار (قبل الفروع والورش لأنها تعتمد على المستخدمين)
            UserSeeder::class,

            // 5. البنية التحتية والمقرات (تعتمد على المستخدمين)
            BranchSeeder::class,
            WorkshopSeeder::class,

            // 6. الموظفين والسيارات والباقات
            EmployeeSeeder::class,
            CarSeeder::class,
            PackageSeeder::class,

            // 6. ربط باقات النظام بالخدمات (بناءً على الباقات والخدمات التي تم إنشاؤها فوق)
            PackageServiceSeeder::class,                  // <-- أضفه هنا
            PackageServiceSubServiceSeeder::class,        // <-- أضفه هنا


        ]);
    }
}
