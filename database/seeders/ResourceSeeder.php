<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $firstNames = [
            'Ahmad', 'Safiullah', 'Rahim', 'Karim', 'Nasim', 'Farid', 'Bilal', 'Hamid', 'Jamal', 'Wali',
            'Qadir', 'Latif', 'Nazar', 'Shafi', 'Ehsan', 'Haroon', 'Fahim', 'Naim', 'Kabir', 'Yasin',
        ];

        $fatherNames = [
            'Abdul Rahman', 'Mohammad Nabi', 'Ghulam Nabi', 'Rahmatullah', 'Sayed Karim',
            'Abdul Ahad', 'Mirza Khan', 'Haji Karim', 'Noor Alam', 'Bashir Ahmad',
        ];

        $grandFatherNames = [
            'Karim Khan', 'Rahim Gul', 'Abdul Jalil', 'Sultan Mohammad', 'Noor Mohammad',
            'Sher Alam', 'Gul Ahmad', 'Abdul Kabir', 'Habibullah', 'Faiz Mohammad',
        ];

        $nickNames = [
            'Jan', 'Khan', 'Safi', 'Bacha', 'Lalai', 'Hamdam', 'Wror', 'Aziz', 'Dost', 'Qari',
        ];

        $provinces = ['کابل', 'هرات', 'بلخ', 'ننگرهار', 'کندهار', 'بغلان', 'کندز', 'تخار'];
        $districts = ['ناحیه اول', 'ناحیه دوم', 'ناحیه سوم', 'مرکز', 'ده سبز', 'پغمان', 'انجیل', 'شهرک'];
        $villages = ['قریه گلها', 'ده افغانان', 'شهر نو', 'کارته نو', 'قلعه فتح الله', 'پل سرخ', 'سرک اول', 'گذرگاه'];

        $projects = ['Project A', 'Project B', 'Project C', 'Special Project'];
        $zones = ['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5'];
        $hawzas = ['Hawza 1', 'Hawza 2', 'Hawza 3', 'Hawza 4', 'Hawza 5'];

        $jobs = ['Teacher', 'Driver', 'Clerk', 'Manager', 'Engineer', 'Shopkeeper', 'Farmer', 'Guard'];
        $jobPlaces = ['Government Office', 'Private Company', 'Market', 'School', 'NGO Office', 'District Office'];
        $faculties = ['Computer Science', 'Law', 'Economics', 'Engineering', 'Literature', null];
        $elmiLevels = ['Bachelor', 'Master', 'Diploma', '12 Pass', '10 Pass'];
        $skills = ['Programming', 'Management', 'Driving', 'Teaching', 'Communication', 'Reporting', 'Networking'];
        $languages = ['Dari', 'Pashto', 'Uzbeki', 'Turkmeni'];
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'O+', 'O-'];
        $nations = ['Afghan', 'Tajik', 'Pashtun', 'Hazara', 'Uzbek'];

        $records = [];

        for ($i = 1; $i <= 100; $i++) {
            $name = $firstNames[array_rand($firstNames)] . ' ' . $i;
            $fatherName = $fatherNames[array_rand($fatherNames)];
            $grandFatherName = $grandFatherNames[array_rand($grandFatherNames)];
            $nickName = $nickNames[array_rand($nickNames)];
            $mainProvince = $provinces[array_rand($provinces)];
            $mainDistrict = $districts[array_rand($districts)];
            $mainVillage = $villages[array_rand($villages)];

            $lat = mt_rand(34000000, 37000000) / 1000000;
            $lng = mt_rand(62000000, 71000000) / 1000000;

            $records[] = [
                'picture' => null,
                'name' => $name,
                'father_name' => $fatherName,
                'grand_father_name' => $grandFatherName,
                'nick_name' => $nickName,
                'code_number' => 'R-' . str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT),
                'tazkira' => 'TK-' . now()->format('Ymd') . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'project' => $projects[array_rand($projects)],
                'zone' => $zones[array_rand($zones)],
                'hawza' => $hawzas[array_rand($hawzas)],
                'specefic_place' => 'Specific Place ' . $i,

                'main_province' => $mainProvince,
                'main_district' => $mainDistrict,
                'main_village' => $mainVillage,

                'now_province' => $provinces[array_rand($provinces)],
                'now_district' => $districts[array_rand($districts)],
                'now_village' => $villages[array_rand($villages)],
                'now_specific_place' => 'Current Place ' . $i,
                'now_street_number' => (string) rand(1, 50),
                'now_home_number' => (string) rand(1, 200),

                'old_job' => $jobs[array_rand($jobs)],
                'now_job' => $jobs[array_rand($jobs)],
                'job_place' => $jobPlaces[array_rand($jobPlaces)],
                'letry_format_name' => 'Format ' . chr(64 + (($i % 26) ?: 26)),

                'activity' => rand(0, 1) ? 'فعال' : 'غیر فعال',
                'halat_madani' => rand(0, 1) ? 'مجرد' : 'متاهل',
                'age' => (string) rand(18, 60),
                'elmi_level' => $elmiLevels[array_rand($elmiLevels)],
                'faculty' => $faculties[array_rand($faculties)],
                'specific_skills' => $skills[array_rand($skills)],
                'train' => rand(0, 1) ? 'Yes' : 'No',
                'trust_percentage' => rand(50, 100) . '%',

                'resource_saved_date' => now()->subDays(rand(0, 365))->format('Y-m-d'),
                'resource_activity_type' => rand(0, 1) ? 'Active' : 'Inactive',

                'office_employee_id' => 101,

                'resedint_code' => 'RC-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'resource_cover' => null,
                'language' => $languages[array_rand($languages)],
                'nation' => $nations[array_rand($nations)],
                'phone_number' => '07' . rand(10000000, 99999999),
                'phone_number_1' => '07' . rand(10000000, 99999999),
                'phone_number_2' => rand(0, 1) ? '07' . rand(10000000, 99999999) : null,
                'Province_addr' => $mainProvince,
                'blood_group' => $bloodGroups[array_rand($bloodGroups)],

                'coordinates' => $lng . ',' . $lat,
                'note' => 'Seeded resource record #' . $i,

                'format_owner_type' => null,
                'format_owner_id' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('resources')->insert($records);
    }
}