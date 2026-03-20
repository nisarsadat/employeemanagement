<?php

return [

    'models' => [
        // FifthyFormats
        \App\Models\fifthyFormats\Mulkano::class,
        \App\Models\fifthyFormats\EntryExit::class,
        \App\Models\fifthyFormats\Sarafyano::class,
        \App\Models\fifthyFormats\Clubs::class,
        \App\Models\fifthyFormats\Soldier::class,

        // Format One
        \App\Models\fifthyformatone\LostMujremeen::class,
        \App\Models\fifthyformatone\OldSoldiersHomes::class,
        \App\Models\fifthyformatone\DamagePlaces::class,
        \App\Models\fifthyformatone\DamageRasanPlaces::class,

        // Format Two
        \App\Models\fifthyformatstow\WorkShop::class,
        \App\Models\fifthyformatstow\Hosppital::class,
        \App\Models\fifthyformatstow\RahnomaMamlat::class,
        \App\Models\fifthyformatstow\Apartment::class,
        \App\Models\fifthyformatstow\Bank::class,
        \App\Models\fifthyformatstow\TransportCompany::class,
        \App\Models\fifthyformatstow\HadoBusStop::class,
        \App\Models\fifthyformatstow\CarSalesCompany::class,
        \App\Models\fifthyformatstow\School::class,
        \App\Models\fifthyformatstow\Parking::class,

        // Format Four
        \App\Models\fifthyformatfour\Wakilano::class,
        \App\Models\fifthyformatfour\University::class,
        \App\Models\fifthyformatfour\Masjid::class,
        \App\Models\fifthyformatfour\Highway::class,
        \App\Models\fifthyformatfour\Company::class,
        \App\Models\fifthyformatfour\Donelostplace::class,
        \App\Models\fifthyformatfour\EconemyProject::class,
        \App\Models\fifthyformatfour\CriminalCapturePlace::class,
        \App\Models\fifthyformatfour\Pump::class,
        \App\Models\fifthyformatfour\Shora::class,
        \App\Models\fifthyformatfour\Checkpoint::class,
        \App\Models\fifthyformatfour\GovernmentPlace::class,
        \App\Models\fifthyformatfour\SuperMarket::class,
        \App\Models\fifthyformatfour\Park::class,
        \App\Models\fifthyformatfour\Complex::class,
        \App\Models\fifthyformatfour\DeplomaticPlace::class,

        // Format Three
        \App\Models\fifthyformatsthree\EmaratOffice::class,
        \App\Models\fifthyformatsthree\Hostel::class,
        \App\Models\fifthyformatsthree\Hotel::class,
        \App\Models\fifthyformatsthree\Madrasa::class,
        \App\Models\fifthyformatsthree\Mujahedino::class,
        \App\Models\fifthyformatsthree\OldJahadi::class,
        \App\Models\fifthyformatsthree\Sarayano::class,
        \App\Models\fifthyformatsthree\Tujaran::class,
    ],

    'labels' => [
        // FifthyFormats
        \App\Models\fifthyFormats\Mulkano::class => 'نماینده گان',
        \App\Models\fifthyFormats\EntryExit::class => 'دخولي، خروجي او فرعي لارې',
        \App\Models\fifthyFormats\Sarafyano::class => 'صرافان',
        \App\Models\fifthyFormats\Clubs::class => 'کلبونه',
        \App\Models\fifthyFormats\Soldier::class => 'سرتیري',

        // Format One
        \App\Models\fifthyformatone\LostMujremeen::class => 'ورک شوي مجرمین',
        \App\Models\fifthyformatone\OldSoldiersHomes::class => 'د پخوانیو عسکرو کورونه',
        \App\Models\fifthyformatone\DamagePlaces::class => 'زیانمن ځایونه',
        \App\Models\fifthyformatone\DamageRasanPlaces::class => 'زیان رسونکي ځایونه',

        // Format Two
        \App\Models\fifthyformatstow\WorkShop::class => 'ورکشاپونه',
        \App\Models\fifthyformatstow\Hosppital::class => 'روغتونونه',
        \App\Models\fifthyformatstow\RahnomaMamlat::class => 'راهنما معاملات',
        \App\Models\fifthyformatstow\Apartment::class => 'اپارتمانونه',
        \App\Models\fifthyformatstow\Bank::class => 'بانکونه',
        \App\Models\fifthyformatstow\TransportCompany::class => 'ترانسپورتي شرکتونه',
        \App\Models\fifthyformatstow\HadoBusStop::class => 'هډې',
        \App\Models\fifthyformatstow\CarSalesCompany::class => 'د موټر خرڅلاو شرکتونه',
        \App\Models\fifthyformatstow\School::class => 'ښوونیز مراکز',
        \App\Models\fifthyformatstow\Parking::class => 'پارکینګونه',

        // Format Four
        \App\Models\fifthyformatfour\Wakilano::class => 'وکیلان او ملکان',
        \App\Models\fifthyformatfour\University::class => 'پوهنتونونه',
        \App\Models\fifthyformatfour\Masjid::class => 'جوماتونه',
        \App\Models\fifthyformatfour\Highway::class => 'لویې لارې',
        \App\Models\fifthyformatfour\Company::class => 'شرکتونه او فابریکې',
        \App\Models\fifthyformatfour\Donelostplace::class => 'هغه ساحې چې غلا پکې ترسره شوي دي',
        \App\Models\fifthyformatfour\EconemyProject::class => 'اقتصادي پروژې',
        \App\Models\fifthyformatfour\CriminalCapturePlace::class => 'د مجرم نیولو ځایونه',
        \App\Models\fifthyformatfour\Pump::class => 'پمپونه',
        \App\Models\fifthyformatfour\Shora::class => 'شوراګانې',
        \App\Models\fifthyformatfour\Checkpoint::class => 'چک پاینتونه',
        \App\Models\fifthyformatfour\GovernmentPlace::class => 'دولتي ځایونه',
        \App\Models\fifthyformatfour\SuperMarket::class => 'سوپرمارکېټونه',
        \App\Models\fifthyformatfour\Park::class => 'پارکونه',
        \App\Models\fifthyformatfour\Complex::class => 'بنسټونه او موسسې',
        \App\Models\fifthyformatfour\DeplomaticPlace::class => 'دیپلوماتیک ځایونه',

        // Format Three
        \App\Models\fifthyformatsthree\EmaratOffice::class => 'امارتي کورونه او میلمستونونه',
        \App\Models\fifthyformatsthree\Hostel::class => 'لیلیې او کرایي اطاقونه',
        \App\Models\fifthyformatsthree\Hotel::class => 'هوټلونه',
        \App\Models\fifthyformatsthree\Madrasa::class => 'مدرسې',
        \App\Models\fifthyformatsthree\Mujahedino::class => 'مجاهدین',
        \App\Models\fifthyformatsthree\OldJahadi::class => 'پخواني جهادي قومندانان',
        \App\Models\fifthyformatsthree\Sarayano::class => 'سرایان',
        \App\Models\fifthyformatsthree\Tujaran::class => 'تجاران',
    ],

];