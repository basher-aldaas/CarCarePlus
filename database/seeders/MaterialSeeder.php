<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        // جلب معرفات الوحدات التي أنشأناها في MaterialUnitSeeder
        $litreUnit = DB::table('material_units')->where('name', 'Litre')->first();
        $pieceUnit = DB::table('material_units')->where('name', 'Piece')->first();

        $materials = [
            [
                'id' => 1,
                'material_unit_id' => $litreUnit?->id,
                'name' => 'Synthetic Engine Oil 5W-30',
                'name_ar' => 'زيت محرك تخليقي 5W-30',
                'description' => 'زيت محرك عالي الجودة يخدم حتى 10,000 كم',
                'unit_price' => 45.00,
                'is_vip_material' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'material_unit_id' => $pieceUnit?->id,
                'name' => 'Premium Car Shampoo',
                'name_ar' => 'شامبو غسيل سيارات ممتاز',
                'description' => 'شامبو واكس عالي الرغوة لحماية الطلاء',
                'unit_price' => 15.00,
                'is_vip_material' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'material_unit_id' => $pieceUnit?->id,
                'name' => 'Microfiber Towel (Ultra Soft)',
                'name_ar' => 'منشفة مايكروفايبر ناعمة فائقة الامتصاص',
                'description' => 'مناشف خاصة لتجفيف حماية من الخدوش',
                'unit_price' => 8.00,
                'is_vip_material' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($materials as $material) {
            DB::table('materials')->updateOrInsert(['id' => $material['id']], $material);
        }
    }
}
