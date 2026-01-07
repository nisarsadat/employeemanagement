<?php
// config/rbac.php

return [

    // Default CRUD actions used for each module
    'actions' => ['view', 'create', 'update', 'delete'],

    // ---- Core (people/resources/info/events) ----
    'modules' => [

        // app pages (routes in /app/*) — for route guarding (view)
        // Keep these for page-level visibility:
        'dashboards',
        'activeandinactiveemployees',
        'users',
        'officeemployee',
        'resources',
        'events',
        'delevirycaptive',
        'informations',
        'inlineinfo',
        'resourceinfo',
        'resourcetab',
        'eventtabl',
        'fifthyformatsroute',
        'finincalindex',
        'map-afg',
        'offlinemap',
        'roles',
        // inline info 
        'Eventtab',
        // the notification 
        'notification',
        'chatbox',
        'khayratreport',
        'systemsetting',
        'outgiongincomming',

        // API resources (exact module slugs for controllers)
        'users',
        'resedints',
        'office_employees',
        'resources_api',        // different from page 'resources' to avoid clash if you want
        'informations_api',
        'events_api',
        'captive_deliveries',

        // Finance
        'account_types',
        'accounts',
        'incomes',
        'expenses',
        'oil_accounts',
        'income_oils',
        'expense_oils',
        'oil_reports',
        'finance_reports',
        'officeemployeereports',
        'eventreports',
        'informationreports',

        //
        'dailyfoulreports',
        'monthlyfoulreports',
        'yearlyfoulreports',
        'weeklyfoulreports',
        'quarterlyfoulreports',
        'alltimefoulreports',
        'rolepermissions',

        // ---- 56 Formats (match your API resource names / page slugs) ----
        // Base
        'fasads','saeen_fasads','heists','captives','mulkanos','entry_exits',
        'sarafyanos','clubs','mahajerats','soldiers',

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
            'resedints.view','resedints.update',
            'resources_api.view','resources_api.update',
            'informations_api.view','informations_api.update',
            'events_api.view','events_api.update', 'delevirycaptive.view'
        ],
        'viewer'  => [
            'dashboards.view',
            'office_employees.view','resedints.view','resources_api.view',
            'informations_api.view','events_api.view',
            'fasads.view','mawads.view','schools.view', // sample views
        ],
        
    ],
];
