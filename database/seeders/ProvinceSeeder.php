<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            'کابل',
            'هرات',
            'ننگرهار',
            'بلخ',
            'قندهار',
            'غور',
            'لغمان',
            'کندوز',
            'پکتیا',
            'زابل',
            'بغلان',
            'بدخشان',
            'بامیان',
            'بادغیس',
            'میدان وردک',
            'لوگر',
            'سمنگان',
            'تخار',
            'نورستان',
            'فاریاب',
            'سرپل',
            'پکتیکا',
            'فراه',
            'هلمند',
            'نیمروز',
            'غزنی',
            'ارزگان',
            'کاپیسا',
            'پروان',
            'پنجشیر',
            'جوزجان',
            'خوست',
            'کنر',
            'دایکندی',
        ];

        foreach ($provinces as $name) {
            Province::firstOrCreate(['name' => $name]);
        }
    }
}