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


                PointsConfigSeeder::class,
                SystemSettingSeeder::class,
                RolePermissionSeeder::class,
                AiRuleSeeder::class,
                SuggestedProblemSeeder::class,

                ServiceSeeder::class,
                SubServiceSeeder::class,
                MaterialSeeder::class,

                // أولاً أنشئ المستخدمين
                UserSeeder::class,

                // ثم الفروع لأنها تحتاج admin@system.com
                BranchSeeder::class,

                // ثم الورش
                WorkshopSeeder::class,

                // ثم الموظفين لأنهم يحتاجون User + Branch
                EmployeeSeeder::class,

                PackageSeeder::class,
                PackageServiceSeeder::class,
                PackageServiceSubServiceSeeder::class,
            ]);

    }
}
