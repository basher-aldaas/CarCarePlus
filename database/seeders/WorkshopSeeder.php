<?php

namespace Database\Seeders;

use App\Enums\WorkshopStatus;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\Wallet;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class WorkshopSeeder extends Seeder
{
    /** Points a freshly-created workshop owner starts with. */
    private const STARTING_POINTS = 10;

    /**
     * Seed 20 partner workshops across Saudi cities. The first is the flagship
     * tied to the existing workshop@system.com account; each of the rest gets
     * its own freshly-created workshop-role owner. Keyed by user_id, so the
     * whole seeder is safe to re-run.
     */
    public function run(): void
    {
        foreach ($this->workshops() as $index => $data) {
            $owner = $index === 0
                ? User::where('email', 'workshop@system.com')->first()
                : $this->makeOwner($index, $data);

            // Flagship owner missing (UserSeeder not run yet) — skip it.
            if (! $owner) {
                continue;
            }

            Workshop::updateOrCreate(
                ['user_id' => $owner->id],
                [
                    'name' => $data['name'],
                    'name_ar' => $data['name_ar'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'status' => $data['status']->value,
                    'rating_avg' => $data['rating_avg'],
                ],
            );
        }
    }

    /**
     * Create (idempotently) the owner account for a generated workshop: a
     * workshop-role user with a starting wallet and points balance, matching
     * how UserSeeder provisions the other seeded accounts.
     */
    private function makeOwner(int $index, array $data): User
    {
        $owner = User::firstOrCreate(
            ['email' => "workshop{$index}@system.com"],
            [
                'name' => $data['name_ar'],
                'phone' => '05' . str_pad((string) (10_000_000 + $index), 8, '0', STR_PAD_LEFT),
                'password' => bcrypt('password123'),
                'is_active' => true,
            ],
        );

        if (! $owner->hasRole('workshop')) {
            $owner->assignRole('workshop');
        }

        Wallet::firstOrCreate(['user_id' => $owner->id], ['balance' => 0]);
        UserPoint::firstOrCreate(['customer_id' => $owner->id], ['balance' => self::STARTING_POINTS]);

        return $owner;
    }

    /**
     * The 20 workshops to seed. Coordinates are the real city centers so the
     * "nearby workshops" distance search returns sensible results.
     *
     * @return array<int, array<string, mixed>>
     */
    private function workshops(): array
    {
        return [
            ['name' => 'Al-Tamayoz Auto Service', 'name_ar' => 'مركز صيانة التميز',        'city' => 'Riyadh',         'address' => 'المنطقة الصناعية، مخرج 18، الرياض',       'latitude' => 24.6333000, 'longitude' => 46.7167000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.80],
            ['name' => 'Al-Faris Car Care',       'name_ar' => 'مركز الفارس للعناية بالسيارات', 'city' => 'Jeddah',      'address' => 'حي الصناعية، شارع الملك فهد، جدة',        'latitude' => 21.4858000, 'longitude' => 39.1925000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.60],
            ['name' => 'Golden Wrench Workshop',  'name_ar' => 'ورشة المفتاح الذهبي',        'city' => 'Mecca',          'address' => 'حي العزيزية، مكة المكرمة',               'latitude' => 21.3891000, 'longitude' => 39.8579000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.30],
            ['name' => 'Al-Sura Fast Service',    'name_ar' => 'مركز السرعة للصيانة السريعة',  'city' => 'Medina',        'address' => 'حي القبلتين، المدينة المنورة',           'latitude' => 24.5247000, 'longitude' => 39.5692000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.10],
            ['name' => 'Al-Khaleej Auto Center',  'name_ar' => 'مركز الخليج للسيارات',       'city' => 'Dammam',         'address' => 'حي الصناعية الأولى، الدمام',             'latitude' => 26.4207000, 'longitude' => 50.0888000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.75],
            ['name' => 'Precision Motors',        'name_ar' => 'ورشة الدقة للسيارات',        'city' => 'Khobar',         'address' => 'شارع الأمير فيصل بن فهد، الخبر',          'latitude' => 26.2794000, 'longitude' => 50.2083000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.55],
            ['name' => 'Al-Amana Garage',         'name_ar' => 'كراج الأمانة',              'city' => 'Dhahran',        'address' => 'حي الدوحة، الظهران',                     'latitude' => 26.2361000, 'longitude' => 50.0393000, 'status' => WorkshopStatus::APPROVED,  'rating_avg' => 3.90],
            ['name' => 'Turbo Fix Workshop',      'name_ar' => 'ورشة توربو للإصلاح',         'city' => 'Taif',           'address' => 'حي الشهداء الشمالية، الطائف',            'latitude' => 21.2703000, 'longitude' => 40.4158000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.20],
            ['name' => 'Al-Watania Auto Repair',  'name_ar' => 'الوطنية لإصلاح السيارات',     'city' => 'Buraidah',       'address' => 'حي الصفراء، بريدة',                     'latitude' => 26.3260000, 'longitude' => 43.9750000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.00],
            ['name' => 'Desert Drive Service',    'name_ar' => 'مركز قيادة الصحراء',        'city' => 'Tabuk',          'address' => 'حي المروج، تبوك',                       'latitude' => 28.3835000, 'longitude' => 36.5662000, 'status' => WorkshopStatus::INACTIVE,  'rating_avg' => 3.60],
            ['name' => 'Al-Jabal Motors',         'name_ar' => 'مركز الجبل للسيارات',        'city' => 'Abha',           'address' => 'حي المنسك، أبها',                       'latitude' => 18.2465000, 'longitude' => 42.5117000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.45],
            ['name' => 'Speed Line Garage',       'name_ar' => 'كراج خط السرعة',            'city' => 'Khamis Mushait', 'address' => 'حي الأندلس، خميس مشيط',                 'latitude' => 18.3000000, 'longitude' => 42.7300000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.15],
            ['name' => 'Al-Rowad Auto Care',      'name_ar' => 'الرواد للعناية بالسيارات',    'city' => 'Hail',           'address' => 'حي النقرة، حائل',                       'latitude' => 27.5114000, 'longitude' => 41.7208000, 'status' => WorkshopStatus::APPROVED,  'rating_avg' => 3.80],
            ['name' => 'Elite Car Workshop',      'name_ar' => 'ورشة النخبة للسيارات',       'city' => 'Najran',         'address' => 'حي الفهد، نجران',                       'latitude' => 17.4933000, 'longitude' => 44.1277000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.05],
            ['name' => 'Al-Bahr Marine & Auto',   'name_ar' => 'مركز البحر للسيارات',        'city' => 'Jubail',         'address' => 'حي الفناتير، الجبيل',                   'latitude' => 27.0174000, 'longitude' => 49.6251000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.65],
            ['name' => 'Coastal Auto Service',    'name_ar' => 'مركز الساحل للصيانة',        'city' => 'Yanbu',          'address' => 'حي الشاطئ، ينبع',                       'latitude' => 24.0895000, 'longitude' => 38.0618000, 'status' => WorkshopStatus::SUSPENDED, 'rating_avg' => 3.50],
            ['name' => 'Al-Waha Car Center',      'name_ar' => 'مركز الواحة للسيارات',       'city' => 'Al Ahsa',        'address' => 'حي الهفوف الصناعي، الأحساء',            'latitude' => 25.3833000, 'longitude' => 49.5860000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.35],
            ['name' => 'Pearl Auto Garage',       'name_ar' => 'كراج اللؤلؤة',              'city' => 'Qatif',          'address' => 'حي الشويكة، القطيف',                    'latitude' => 26.5196000, 'longitude' => 50.0115000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.25],
            ['name' => 'Southern Star Motors',    'name_ar' => 'نجمة الجنوب للسيارات',       'city' => 'Jazan',          'address' => 'حي الروضة، جازان',                      'latitude' => 16.8892000, 'longitude' => 42.5511000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 3.95],
            ['name' => 'Al-Qassim Auto Hub',      'name_ar' => 'مركز القصيم للسيارات',       'city' => 'Unaizah',        'address' => 'حي الجامعة، عنيزة',                     'latitude' => 26.0843000, 'longitude' => 43.9935000, 'status' => WorkshopStatus::ACTIVE,    'rating_avg' => 4.50],
        ];
    }
}