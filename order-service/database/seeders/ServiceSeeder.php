<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name_en' => 'Wash Only',          'name_ar' => 'غسيل فقط',        'unit_price' => 5],
            ['name_en' => 'Ironing Only',       'name_ar' => 'كوي فقط',         'unit_price' => 4],
            ['name_en' => 'Wash and Iron',      'name_ar' => 'غسيل وكوي',       'unit_price' => 8],
            ['name_en' => 'Dry Cleaning',       'name_ar' => 'تنظيف جاف',       'unit_price' => 15],
            ['name_en' => 'Blankets',           'name_ar' => 'بطانيات',         'unit_price' => 25],
            ['name_en' => 'Thobe Wash and Iron','name_ar' => 'غسيل وكوي ثوب',   'unit_price' => 7],
            ['name_en' => 'Abaya Cleaning',     'name_ar' => 'تنظيف عباية',     'unit_price' => 12],
            ['name_en' => 'Curtains',           'name_ar' => 'ستائر',           'unit_price' => 20],
            ['name_en' => 'Shoes Cleaning',     'name_ar' => 'تنظيف أحذية',     'unit_price' => 18],
            ['name_en' => 'Carpet (small)',     'name_ar' => 'سجادة صغيرة',     'unit_price' => 30, 'is_active' => false],
        ];

        // updateOrCreate: running the seeder again won't add the same service twice
        foreach ($services as $service) {
            Service::updateOrCreate(['name_en' => $service['name_en']], $service);
        }
    }
}
