<?php

namespace App\Support;

/**
 * Records the global search is allowed to look through.
 * `perms` is any-of: a user who can view the module sees its rows.
 */
class GlobalSearchSources
{
    public static function all(): array
    {
        return [
            self::src('کارمند', 'mdi-badge-account-outline', '/app/officeemployeeform/{id}', ['officeemployee.view'], \App\Models\OfficeEmployee::class, ['name', 'father_name', 'grand_father_name', 'nick_name', 'code_number', 'tazkira', 'phone_number'], ['name', 'nick_name', 'code_number'], ['father_name', 'code_number', 'phone_number']),
            self::src('منبع', 'mdi-database-outline', '/app/resourcesform/{id}', ['resources.view'], \App\Models\Resource::class, ['name', 'father_name', 'grand_father_name', 'nick_name', 'code_number', 'tazkira', 'phone_number'], ['name', 'nick_name', 'code_number'], ['father_name', 'code_number', 'phone_number']),
            self::src('اطلاعات', 'mdi-file-document-multiple-outline', '/app/informationform/{id}', ['informations.view'], \App\Models\Information::class, ['name', 'nick_name', 'father_name', 'grand_father_name', 'organ', 'tafseel', 'note', 'maktop_number'], ['name', 'nick_name'], ['father_name', 'organ']),
            self::src('واقعه', 'mdi-alert-circle-outline', '/app/eventsform/{id}', ['events.view'], \App\Models\Event::class, ['target_person', 'criminal', 'event_type', 'event_specific_type', 'event_zone', 'event_hawza', 'event_specific_place', 'tafseel', 'note'], ['target_person', 'criminal', 'event_specific_place', 'event_type'], ['event_zone', 'event_specific_place']),
            self::src('جلسه', 'mdi-calendar-account-outline', '/app/meetings/form/{id}', ['meetings.view'], \App\Models\Meeting::class, ['meeting_title', 'organ', 'leader_name', 'specific_place', 'province', 'participant', 'note'], ['meeting_title', 'leader_name'], ['organ', 'specific_place']),
            self::src('ارزیابی امنیتی', 'mdi-shield-check-outline', '/app/security-assessments/form/{id}', ['securityassessments.view'], \App\Models\SecurityAssessment::class, ['place_name', 'responsible_owner_name', 'specific_place', 'security_responsible_name', 'province', 'note'], ['place_name', 'responsible_owner_name'], ['province', 'specific_place']),
            self::src('زندانی تسلیم شده', 'mdi-handcuffs', '/app/delevirycaptiveform/{id}', ['delevirycaptive.view', 'captives.view'], \App\Models\CaptiveDelivery::class, ['name', 'father_name', 'grand_father_name', 'nickname', 'tazkira', 'phone_number', 'captive_place'], ['name', 'nickname'], ['father_name', 'tazkira', 'phone_number']),
            self::src('صادره وارده', 'mdi-swap-horizontal-bold', '/app/outgoingincomingform/{id}', ['outgoingincomming.view'], \App\Models\OutgoingIncoming::class, ['peshnehad', 'marja', 'who_sighn', 'tafseel', 'notes', 'page', 'dusya'], ['peshnehad', 'marja', 'who_sighn'], ['marja', 'page']),
            self::src('استفاده کننده', 'mdi-account-outline', '/app/users', ['users.view'], \App\Models\User::class, ['name', 'username'], ['name', 'username'], ['username']),
            self::src('نقش', 'mdi-shield-account-outline', '/app/roles', ['roles.view'], \Spatie\Permission\Models\Role::class, ['name'], ['name'], []),
            self::src('حاضری راپور', 'mdi-chart-donut-variant', '/app/khayratreports', ['khayratreport.view'], \App\Models\KhayratReport::class, ['zone', 'hawza', 'specefic_place'], ['zone', 'hawza', 'specefic_place'], ['hawza', 'specefic_place']),
            self::src('نوت راپور', 'mdi-note-text-outline', '/app/reportnote', ['reports.view'], \App\Models\ReportNote::class, ['report_note', 'type'], ['report_note'], ['type']),
            self::src('حساب', 'mdi-bank', '/app/finance/accounts', ['finincalindex.view'], \App\Models\Account::class, ['name', 'details'], ['name'], ['details']),
            self::src('مصرف', 'mdi-cash-minus', '/app/finance/cashflow/expense', ['finincalindex.view'], \App\Models\Finance::class, ['receiver', 'payer', 'money_distributer', 'notes', 'money_amount_words', 'employee_phone'], ['receiver', 'payer', 'money_amount_words'], ['payer', 'notes']),
            self::src('حساب تیل', 'mdi-oil', '/app/finance/oil/accounts', ['finincalindex.view'], \App\Models\OilAccount::class, ['name'], ['name'], []),
            self::src('مصرف تیل', 'mdi-fuel', '/app/finance/oil/expense', ['finincalindex.view'], \App\Models\ExpenseOil::class, ['name', 'father_name', 'account_name', 'tafseel', 'note'], ['name', 'account_name'], ['father_name', 'account_name']),
            self::src('نقطه نقشه', 'mdi-map-marker', '/app/map-afg', ['map-afg.view'], \App\Models\MapPoint::class, ['name', 'father_name', 'code_number', 'phone', 'note'], ['name', 'code_number'], ['father_name', 'phone']),

            self::src('ولایت', 'mdi-map-marker-outline', '/app/settings/provinces', ['provinces.view', 'settings-provinces.view'], \App\Models\Province::class, ['name'], ['name'], []),
            self::src('ولسوالی', 'mdi-map-marker-radius-outline', '/app/settings/districts', ['districts.view', 'settings-districts.view'], \App\Models\District::class, ['name'], ['name'], []),
            self::src('آمریت', 'mdi-map-outline', '/app/settings/zones/zones', ['zones.view', 'settings-zones.view'], \App\Models\Zone::class, ['name'], ['name'], []),
            self::src('حوزه', 'mdi-map-legend', '/app/settings/zones/hawzas', ['hawzas.view', 'settings-hawzas.view'], \App\Models\Hawza::class, ['name'], ['name'], []),
            self::src('نوع رویداد', 'mdi-shape-outline', '/app/event-types', ['event_types.view', 'event-types.view'], \App\Models\EventType::class, ['name'], ['name'], []),
            self::src('نوع مشخص رویداد', 'mdi-shape-plus-outline', '/app/event-specific-types', ['event_specific_types.view', 'event-specific-types.view'], \App\Models\EventSpecificType::class, ['name'], ['name'], []),
            self::src('نوع جنایی', 'mdi-shape-plus-outline', '/app/jenayee-type', ['jenayee_types.view', 'jenayee-type.view'], \App\Models\JenayeeType::class, ['name'], ['name'], []),
            self::src('نوع سیاسی', 'mdi-shape-plus-outline', '/app/seyasee-type', ['seyasee_types.view', 'seyasee-type.view'], \App\Models\SeyaseeType::class, ['name'], ['name'], []),
            self::src('ارگان', 'mdi-domain', '/app/organs', ['organs.view'], \App\Models\Organ::class, ['name'], ['name'], []),
            self::src('وظیفه', 'mdi-briefcase-outline', '/app/jobs-type', ['jobs.view', 'jobs-type.view'], \App\Models\JobType::class, ['name'], ['name'], []),
            self::src('نوع فعالیت منبع', 'mdi-clipboard-list-outline', '/app/resource-activity-types', ['resource_activity_types.view', 'resource-activity-types.view'], \App\Models\ResourceActivityType::class, ['name'], ['name'], []),
            self::src('موقف جهادی', 'mdi-hand-peace', '/app/mawqafjahadi', ['mawqafjahadi.view'], \App\Models\MawqafJahadi::class, ['name'], ['name'], []),
            self::src('فارمت آمریت', 'mdi-map-marker-multiple-outline', '/app/formatzone', ['formatzone.view'], \App\Models\FormatZone::class, ['name'], ['name'], []),
            self::src('نوعیت اسناد', 'mdi-file-document-outline', '/app/docstypesindex', ['docstypes.view', 'docstypesindex.view'], \App\Models\DocsType::class, ['name'], ['name'], []),
            self::src('مرجع اسناد', 'mdi-file-sign', '/app/marjas', ['marjas.view'], \App\Models\Marja::class, ['name'], ['name'], []),
            self::src('۵۶ فارمت', 'mdi-book-outline', '/app/letryformatindex', ['letryformatindex.view'], \App\Models\LetryFormat::class, ['letry_format_name'], ['letry_format_name'], []),

            self::src('فساد ساعین', 'mdi-alert-octagon-outline', '/app/saeen-fasadform/{id}', ['saeen_fasads.view'], \App\Models\fifthyFormats\SaeenFasad::class, ['name', 'nick_name', 'father_name', 'phone_number', 'resource_code'], ['name', 'nick_name'], ['father_name', 'phone_number']),
            self::src('فساد', 'mdi-alert-outline', '/app/fasadform/{id}', ['fasads.view'], \App\Models\fifthyFormats\Fasad::class, ['name', 'nick_name', 'father_name', 'phone_number', 'resource_code'], ['name', 'nick_name'], ['father_name', 'phone_number']),
            self::src('جاسوس', 'mdi-incognito', '/jawasis/edit/{id}', ['jawasis.view'], \App\Models\fifthyFormats\Jawasis::class, ['name', 'nick_name', 'father_name', 'phone_number'], ['name', 'nick_name'], ['father_name', 'phone_number']),
            self::src('سرقت', 'mdi-bag-personal', '/app/heistform/{id}', ['heists.view', 'heist.view'], \App\Models\fifthyFormats\Heist::class, ['name', 'father_name', 'phone_number'], ['name'], ['father_name', 'phone_number']),
            self::src('بندی', 'mdi-handcuffs', '/app/captiveform/{id}', ['captives.view'], \App\Models\fifthyFormats\Captive::class, ['name', 'nick_name', 'father_name', 'phone_number'], ['name', 'nick_name'], ['father_name', 'phone_number']),
            self::src('ملک', 'mdi-home-city-outline', '/app/mulkanoform/{id}', ['mulkanos.view'], \App\Models\fifthyFormats\Mulkano::class, ['name', 'father_name', 'phone_number'], ['name'], ['father_name', 'phone_number']),
            self::src('دخول و خروج', 'mdi-door-open', '/app/entryexitform/{id}', ['entry_exits.view'], \App\Models\fifthyFormats\EntryExit::class, ['zone', 'specific_place', 'notes'], ['specific_place', 'zone'], ['zone', 'notes']),
            self::src('صرافی', 'mdi-cash-multiple', '/app/sarafyanosform/{id}', ['sarafyanos.view'], \App\Models\fifthyFormats\Sarafyano::class, ['name', 'sarafi_name', 'father_name', 'phone_number', 'market_name'], ['sarafi_name', 'name'], ['father_name', 'phone_number']),
            self::src('کلب', 'mdi-cards-club', '/app/clubform/{id}', ['clubs.view'], \App\Models\fifthyFormats\Clubs::class, ['club_name', 'name', 'father_name', 'phone_number'], ['club_name', 'name'], ['father_name', 'phone_number']),
            self::src('مهاجرت', 'mdi-airplane', '/app/mahajeratform/{id}', ['mahajerats.view'], \App\Models\fifthyFormats\Mahajerat::class, ['name', 'father_name', 'phone_number'], ['name'], ['father_name', 'phone_number']),
            self::src('سرباز', 'mdi-account-hard-hat', '/app/soldierform/{id}', ['soldiers.view'], \App\Models\fifthyFormats\Soldier::class, ['name', 'father_name', 'phone_number'], ['name'], ['father_name', 'phone_number']),
            self::src('مواد مخدر', 'mdi-pill', '/app/mawadform/{id}', ['mawads.view'], \App\Models\fifthyformatone\Mawad::class, ['name', 'nickname', 'father_name', 'mawad_sales_phone'], ['name', 'nickname'], ['father_name', 'mawad_sales_phone']),
            self::src('مجرم گمشده', 'mdi-account-question-outline', '/app/lost-mujremeen-form/{id}', ['lost_mujremeens.view'], \App\Models\fifthyformatone\LostMujremeen::class, ['name', 'nickname', 'father_name', 'phone_number'], ['name', 'nickname'], ['father_name', 'phone_number']),
            self::src('خانه سرباز قدیمی', 'mdi-home-outline', '/app/old-soldiers-home-form/{id}', ['old_soldiers_homes.view'], \App\Models\fifthyformatone\OldSoldiersHomes::class, ['name', 'father_name', 'phone_number', 'specific_place'], ['name', 'specific_place'], ['father_name', 'phone_number']),
            self::src('دزد', 'mdi-incognito', '/app/robbers/{id}', ['robbers.view'], \App\Models\fifthyformatone\Robbers::class, ['name', 'nickname', 'father_name', 'phone_number'], ['name', 'nickname'], ['father_name', 'phone_number']),
            self::src('فرد مسلح غیرمسؤول', 'mdi-account-alert-outline', '/app/non-responsable-people-form/{id}', ['non_responsable_people.view'], \App\Models\fifthyformatone\NonResponsablePeople::class, ['name', 'nick_name', 'father_name', 'phone_number'], ['name', 'nick_name'], ['father_name', 'phone_number']),
            self::src('خانه مشکوک', 'mdi-home-alert-outline', '/app/mashkok-home-form/{id}', ['mashkok_homes.view'], \App\Models\fifthyformatone\MashkokHomes::class, ['specific_place', 'zone', 'tafseel', 'note'], ['specific_place', 'zone'], ['zone', 'tafseel']),
            self::src('ساحه آسیب‌دیده', 'mdi-home-flood', '/app/damage-places/form/{id}', ['damage_places.view'], \App\Models\fifthyformatone\DamagePlaces::class, ['specific_place', 'tahdid_place', 'masool_name', 'masool_phone', 'zone'], ['tahdid_place', 'masool_name', 'specific_place'], ['masool_name', 'zone']),
            self::src('ساحه رساننده آسیب', 'mdi-alert-decagram-outline', '/app/damage-rasan-placeform/{id}', ['damage_rasan_places.view'], \App\Models\fifthyformatone\DamageRasanPlaces::class, ['damage_place', 'specific_place', 'zone', 'note'], ['damage_place', 'specific_place'], ['zone']),
            self::src('دکان مشکوک', 'mdi-store-alert-outline', '/app/mashkok-shops/form/{id}', ['mashkok_shops.view'], \App\Models\fifthyformatone\MashkokShops::class, ['market_name', 'name', 'father_name', 'phone_numbers', 'specific_place'], ['market_name', 'name'], ['father_name', 'specific_place']),
            self::src('لوچک', 'mdi-account-search-outline', '/app/luchakanoform/{id}', ['luchakanos.view'], \App\Models\fifthyformatone\Luchakano::class, ['name', 'nickname', 'father_name', 'phone_number'], ['name', 'nickname'], ['father_name', 'phone_number']),
            self::src('مکتب', 'mdi-school-outline', '/app/schools/form/{id}', ['schools.view'], \App\Models\fifthyformatstow\School::class, ['school_name', 'principle_name', 'principle_phone'], ['school_name', 'principle_name'], ['principle_name', 'principle_phone']),
            self::src('ایستگاه بس', 'mdi-bus-stop', '/app/hadabusstopform/{id}', ['hado_bus_stops.view'], \App\Models\fifthyformatstow\HadoBusStop::class, ['name', 'responsible_name', 'responsible_phone'], ['name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('شرکت ترانسپورتی', 'mdi-truck-outline', '/app/transport-companies/{id}/edit', ['transport_companies.view'], \App\Models\fifthyformatstow\TransportCompany::class, ['transport_company_name', 'responsible_name', 'responsible_phone'], ['transport_company_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('کمپنی فروش موتر', 'mdi-car-outline', '/app/car-sales-companies/{id}', ['car_sales_companies.view'], \App\Models\fifthyformatstow\CarSalesCompany::class, ['car_sales_company_name', 'responsible_name', 'responsible_phone'], ['car_sales_company_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('پارکینگ', 'mdi-parking', '/app/parkingform/{id}', ['parkings.view'], \App\Models\fifthyformatstow\Parking::class, ['parking_name', 'responsible_name', 'responsible_phone'], ['parking_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('بانک', 'mdi-bank-outline', '/app/bankform/{id}', ['banks.view'], \App\Models\fifthyformatstow\Bank::class, ['bank_name', 'responsible_name', 'responsible_phone'], ['bank_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('آپارتمان', 'mdi-office-building-outline', '/app/apartmentform/{id}', ['apartments.view'], \App\Models\fifthyformatstow\Apartment::class, ['block_name', 'shahrak_name', 'responsible_name', 'responsible_phone'], ['block_name', 'shahrak_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('رهنمای معاملات', 'mdi-handshake-outline', '/app/rahnoma-mamlats/{id}', ['rahnoma_mamlats.view'], \App\Models\fifthyformatstow\RahnomaMamlat::class, ['name', 'responsible_name', 'responsible_phone'], ['name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('شفاخانه', 'mdi-hospital-box-outline', '/app/hospitalform/{id}', ['hosppitals.view'], \App\Models\fifthyformatstow\Hosppital::class, ['name', 'responsible_name', 'responsible_phone'], ['name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('کارگاه', 'mdi-hammer-wrench', '/app/workshops/{id}', ['work_shops.view'], \App\Models\fifthyformatstow\WorkShop::class, ['workshop_name', 'responsible_name', 'responsible_phone'], ['workshop_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('بدماش', 'mdi-account-alert', '/app/badmashanoform/{id}', ['badmashanos.view'], \App\Models\fifthyformatsthree\Badmashano::class, ['name', 'nickname', 'father_name', 'phone_number'], ['name', 'nickname'], ['father_name', 'phone_number']),
            self::src('سرا', 'mdi-warehouse', '/app/sarayanoform/{id}', ['sarayanos.view'], \App\Models\fifthyformatsthree\Sarayano::class, ['saray_name', 'responsible_name', 'responsible_phone'], ['saray_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('دفتر امارت', 'mdi-office-building', '/app/emarat-offices/form/{id}', ['emarat_offices.view'], \App\Models\fifthyformatsthree\EmaratOffice::class, ['re_name', 'specific_place', 'zone'], ['re_name', 'specific_place'], ['zone']),
            self::src('لیله و اطاق کرایی', 'mdi-bed-outline', '/app/hostelform/{id}', ['hostels.view'], \App\Models\fifthyformatsthree\Hostel::class, ['hostel_name', 'responsible_name', 'responsible_phone'], ['hostel_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('هوتل', 'mdi-bed', '/app/hotelform/{id}', ['hotels.view'], \App\Models\fifthyformatsthree\Hotel::class, ['hotel_name', 'responsible_name', 'responsible_phone'], ['hotel_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('مدرسه', 'mdi-book-open-page-variant-outline', '/app/madrasas/form/{id}', ['madrasas.view'], \App\Models\fifthyformatsthree\Madrasa::class, ['name', 'responsible_name', 'responsible_phone'], ['name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('فرد مشکوک', 'mdi-account-question', '/app/mashkokpeopleform/{id}', ['mashkok_people.view'], \App\Models\fifthyformatsthree\MashkokPeople::class, ['name', 'father_name', 'phone_number'], ['name'], ['father_name', 'phone_number']),
            self::src('مجاهد', 'mdi-account-star-outline', '/app/mujahedinoform/{id}', ['mujahedinos.view'], \App\Models\fifthyformatsthree\Mujahedino::class, ['name', 'nickname', 'father_name', 'phone_number', 'organ'], ['name', 'nickname'], ['father_name', 'organ']),
            self::src('قومندان جهادی قدیمی', 'mdi-account-clock-outline', '/app/oldjahadisform/{id}', ['old_jahadis.view'], \App\Models\fifthyformatsthree\OldJahadi::class, ['name', 'nick_name', 'father_name', 'phone_number', 'organ'], ['name', 'nick_name'], ['father_name', 'organ']),
            self::src('تاجر', 'mdi-account-cash-outline', '/app/tujaransform/{id}', ['tujarans.view'], \App\Models\fifthyformatsthree\Tujaran::class, ['name', 'father_name', 'phone_number', 'organ'], ['name'], ['father_name', 'organ']),
            self::src('چک‌پوینت', 'mdi-gate', '/app/checkpointform/{id}', ['checkpoints.view'], \App\Models\fifthyformatfour\Checkpoint::class, ['checkpoint_name', 'check_point_place', 'officer_name', 'officer_phone', 'organ'], ['checkpoint_name', 'officer_name'], ['officer_name', 'organ']),
            self::src('مسجد', 'mdi-mosque', '/app/masjidform/{id}', ['masjids.view'], \App\Models\fifthyformatfour\Masjid::class, ['masjid_name', 'responsible_name'], ['masjid_name', 'responsible_name'], ['responsible_name']),
            self::src('شاهراه', 'mdi-road-variant', '/app/highwayform/{id}', ['highways.view'], \App\Models\fifthyformatfour\Highway::class, ['high_way_name', 'specific_place', 'zone'], ['high_way_name', 'specific_place'], ['zone']),
            self::src('شرکت', 'mdi-domain', '/app/companyform/{id}', ['companies.view'], \App\Models\fifthyformatfour\Company::class, ['com_name', 'responsible_name', 'responsible_phone'], ['com_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('ساحه دزدی شده', 'mdi-map-marker-alert-outline', '/app/done-lost-places/form/{id}', ['donelostplaces.view'], \App\Models\fifthyformatfour\Donelostplace::class, ['place_name', 'lost_person_name', 'lost_person_phone'], ['place_name', 'lost_person_name'], ['lost_person_name', 'lost_person_phone']),
            self::src('پروژه اقتصادی', 'mdi-chart-line', '/app/econemyprojectform/{id}', ['econemyprojects.view'], \App\Models\fifthyformatfour\EconemyProject::class, ['name', 'responsiable_name', 'responsible_phone'], ['name', 'responsiable_name'], ['responsiable_name', 'responsible_phone']),
            self::src('ساحه دستگیری', 'mdi-map-marker-account', '/app/criminal-capture-place-form/{id}', ['criminalcaptureplaces.view'], \App\Models\fifthyformatfour\CriminalCapturePlace::class, ['name', 'father_name', 'specefic_place', 'zone'], ['name', 'specefic_place'], ['father_name', 'zone']),
            self::src('پمپ', 'mdi-gas-station-outline', '/app/pumpform/{id}', ['pumps.view'], \App\Models\fifthyformatfour\Pump::class, ['pump_name', 'responsible_name', 'responsible_phone'], ['pump_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('شورا', 'mdi-account-group-outline', '/app/shoraform/{id}', ['shoras.view'], \App\Models\fifthyformatfour\Shora::class, ['shora_name', 'responsible_name', 'responsible_phone', 'shora_meeting_place'], ['shora_name', 'responsible_name'], ['responsible_name', 'shora_meeting_place']),
            self::src('مکان حکومتی', 'mdi-bank', '/app/governmentplaceform/{id}', ['governmentplaces.view'], \App\Models\fifthyformatfour\GovernmentPlace::class, ['government_place_name', 'responsible_name', 'responsible_phone'], ['government_place_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('سوپرمارکت', 'mdi-cart-outline', '/app/supermarketform/{id}', ['supermarkets.view'], \App\Models\fifthyformatfour\SuperMarket::class, ['supermarket_name', 'responsible_name', 'responsible_phone'], ['supermarket_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('پارک', 'mdi-pine-tree', '/app/parkform/{id}', ['parks.view'], \App\Models\fifthyformatfour\Park::class, ['park_name', 'responsible_name', 'responsible_phone'], ['park_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('موسسه', 'mdi-domain', '/app/complexform/{id}', ['complexes.view'], \App\Models\fifthyformatfour\Complex::class, ['complex_name', 'responsible_name', 'responsible_phone', 'activity_place'], ['complex_name', 'responsible_name'], ['responsible_name', 'activity_place']),
            self::src('مکان دیپلوماتیک', 'mdi-flag-outline', '/app/deplomaticplaceform/{id}', ['deplomaticplaces.view'], \App\Models\fifthyformatfour\DeplomaticPlace::class, ['deplomatic_place_name', 'responsible_name', 'responsible_phone'], ['deplomatic_place_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('پوهنتون', 'mdi-school', '/app/universityform/{id}', ['universities.view'], \App\Models\fifthyformatfour\University::class, ['uni_name', 'responsible_name', 'responsible_phone'], ['uni_name', 'responsible_name'], ['responsible_name', 'responsible_phone']),
            self::src('وکیل', 'mdi-gavel', '/app/wakilanatform/{id}', ['wakilanos.view'], \App\Models\fifthyformatfour\Wakilano::class, ['name', 'f_name', 'phone_number'], ['name'], ['f_name', 'phone_number']),
        ];
    }

    private static function src(
        string $label,
        string $icon,
        string $path,
        array $perms,
        string $model,
        array $search,
        array $title,
        array $subtitle
    ): array {
        return compact('label', 'icon', 'path', 'perms', 'model', 'search', 'title', 'subtitle');
    }
}
