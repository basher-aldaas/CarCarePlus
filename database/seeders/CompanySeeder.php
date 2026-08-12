<?php

namespace Database\Seeders;

use App\Enums\CompanyStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Creates the company profile for the seeded company/fleet customer
     * (company@system.com from UserSeeder). Without it, that user has no
     * Company row and can't place multi-car company bookings.
     */
    public function run(): void
    {
        $owner = User::where('email', 'company@system.com')->first();

        if (! $owner) {
            return;
        }

        Company::firstOrCreate(
            ['customer_id' => $owner->id],
            [
                'name' => 'Transport Fleet Co.',
                'name_ar' => 'شركة أسطول النقل',
                'commercial_reg' => 'CR-1000001',
                'tax_number' => 'TAX-1000001',
                'address' => 'الرياض، المملكة العربية السعودية',
                'status' => CompanyStatus::APPROVED->value,
                'is_active' => true,
            ],
        );
    }
}