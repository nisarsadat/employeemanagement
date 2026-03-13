<?php
// config/rbac.php

return [

    // Default CRUD actions used for each module
    'actions' => ['view', 'create', 'update', 'delete'],

    // ---- Modules ----
    // NOTE:
    // - These slugs must match frontend permFor(slug) usage
    // - For pages: "<slug>.view"
    // - For api resources: "<slug>.view" as well (same CRUD rule)
    'modules' => [

        // ---- App Pages (routes in /app/*) ----
        'dashboards',
        'activeandinactiveemployees',

        'users',
        'officeemployee',
        'resources',
        'events',
        'delevirycaptive',
        'informations',
        'managercomment',
        'direction',

        'attachmentDownload',
        'inlineinfo',
        'resourceinfo',
        'resourcetab',
        'eventtabl',

        'fifthyformatsroute',
        'finincalindex',

        'map-afg',
        'offlinemap',

        'roles',
        'rolepermissions',

        // notification + chat + reports + system
        'notifications',
        'chatbox',
        'khayratreport',
        'systemsetting',
        'outgoingincomming',

        // foul reports
        'reports',
        'dailyfoulreports',
        'monthlyfoulreports',
        'yearlyfoulreports',
        'weeklyfoulreports',
        'quarterlyfoulreports',
        'alltimefoulreports',

        // settings pages
        'settings-provinces',
        'settings-districts',
        'settings-zones',
        'settings-hawzas',
        'event-types',
        'event-specific-types',
        'jenayee-type',
        'seyasee-type',
        'organs',
        'jobs-type',
        'resource-activity-types',
        'formatzone',
        'settings',
        'mawqafjahadi',
        'docstypesindex',
        'marjas',
        'event_types',
        'provinces',
        'districts',
        'zones',
        'hawzas',
        'event_specific_types',
        'jenayee_types',
        'seyasee_types',
        'organs',
        'jobs',
        'resource_activity_types',
        'mawqafjahadi',
        'formatzone',
        'docstypes',
        'marjas',
        'letryformatindex',
        'InfoCategory',
        // ---- API Resources (exact controller/resource slugs) ----
        'users',
        'office_employees',
        'captive_deliveries',

        // ---- Finance API ----
        'account_types',
        'accounts',
        'incomes',
        'expenses',
        'oil_accounts',
        'income_oils',
        'expense_oils',

        // ---- 56 Formats (API/page slugs) ----
        // Base
        'fasads','saeen_fasads','heists','captives','mulkanos','entry_exits',
        'sarafyanos','clubs','mahajerats','soldiers', 'jawasis',

        // One
        'mawads','lost_mujremeens','old_soldiers_homes','robbers',
        'non_responsable_people','mashkok_homes','damage_places',
        'damage_rasan_places','mashkok_shops','luchakanos',

        // Two
        'schools','hado_bus_stops','transport_companies','car_sales_companies',
        'parkings','banks','apartments','rahnoma_mamlats','hosppitals','work_shops',

        // Three
        'badmashanos','sarayanos','hotels','hostels','tujarans','mujahedinos',
        'emarat_offices','old_jahadis','mashkok_people','madrasas',

        // Four
        'wakilanos','universities','masjids','highways','companies','donelostplaces',
        'econemyprojects','criminalcaptureplaces','pumps','shoras','checkpoints',
        'governmentplaces','supermarkets','parks','complexes','deplomaticplaces',
    ],

    // Optional role bundles
    'roles' => [
        'admin'   => '*', // all permissions

        'manager' => [
            // view + update basics
            'office_employees.view','office_employees.update',
            'captive_deliveries.view','captive_deliveries.update',

            // pages
            'dashboards.view',
            'officeemployee.view','officeemployee.update',
            'resources.view','resources.update',
            'events.view','events.update',
            'delevirycaptive.view','delevirycaptive.update',
            'informations.view','informations.update',

            // system settings + its cards
            'systemsetting.view',
            'settings-provinces.view',
            'settings-districts.view',
            'settings-zones.view',
            'settings-hawzas.view',
            'event-types.view',
            'event-specific-types.view',
            'jenayee-type.view',
            'seyasee-type.view',
            'organs.view',
            'jobs-type.view',
            'resource-activity-types.view',
            'mawqafjahadi.view',
            'formatzone.view',
            'docstypesindex.view',
            'marjas.view',
        ],

        'viewer'  => [
            'dashboards.view',
            'activeandinactiveemployees.view',
            'users.view',
            'officeemployee.view',
            'resources.view',
            'events.view',
            'delevirycaptive.view',
            'informations.view',

            'inlineinfo.view',
            'resourceinfo.view',
            'resourcetab.view',
            'eventtabl.view',

            'fifthyformatsroute.view',
            'finincalindex.view',

            // sample 56 formats views
            'fasads.view','mawads.view','schools.view',

            // optional: system settings for viewer
            'systemsetting.view',
            'settings-provinces.view',
            'settings-districts.view',
            'settings-zones.view',
            'settings-hawzas.view',
            'event-types.view',
            'event-specific-types.view',
            'jenayee-type.view',
            'seyasee-type.view',
            'organs.view',
            'jobs-type.view',
            'resource-activity-types.view',
            'mawqafjahadi.view',
            'formatzone.view',
            'docstypesindex.view',
            'marjas.view',
        ],
    ],
];
