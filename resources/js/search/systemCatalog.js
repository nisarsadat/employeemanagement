const pages = [
  { title: 'داشبورد', icon: 'mdi-view-dashboard-variant-outline', to: '/app/dashboards', perm: 'dashboards.view', aliases: ['dashboard', 'home'], suggest: true },
  { title: 'کارمندان', icon: 'mdi-badge-account-outline', to: '/app/officeemployee', perm: 'officeemployee.view', aliases: ['employee', 'employees', 'staff', 'کارمند'], suggest: true },
  { title: 'منابع', icon: 'mdi-database-outline', to: '/app/resources', perm: 'resources.view', aliases: ['resource', 'resources', 'منبع'], suggest: true },
  { title: 'اطلاعات', icon: 'mdi-file-document-multiple-outline', to: '/app/informations', perm: 'informations.view', aliases: ['information', 'info'], suggest: true },
  { title: 'واقعات', icon: 'mdi-alert-circle-outline', to: '/app/events', perm: 'events.view', aliases: ['event', 'events', 'واقعه'], suggest: true },
  { title: 'جلسات', icon: 'mdi-calendar-account-outline', to: '/app/meetings', perm: 'meetings.view', aliases: ['meeting', 'meetings', 'جلسه'], suggest: true },
  { title: 'ارزیابی امنیتی', icon: 'mdi-shield-check-outline', to: '/app/security-assessments', perm: 'securityassessments.view', aliases: ['security', 'assessment'] },
  { title: 'زندانی های تسلیم شده', icon: 'mdi-handcuffs', to: '/app/delevirycaptive', any: ['delevirycaptive.view', 'captives.view'], aliases: ['captive', 'prisoner', 'زندانی'] },
  { title: '۵۶ فارمت', icon: 'mdi-file-table-box-multiple-outline', to: '/app/fifthyformatsroute', perm: 'fifthyformatsroute.view', aliases: ['format', 'formats', '56', 'فارمت', 'فورم'], suggest: true },
  { title: 'بخش حساب مالی', icon: 'mdi-cash-register', to: '/app/finincalindex', perm: 'finincalindex.view', aliases: ['finance', 'money', 'حساب', 'مالی'] },
  { title: 'حسابونه', icon: 'mdi-bank', to: '/app/finance/accounts', perm: 'finincalindex.view', group: 'بخش حساب مالی', aliases: ['accounts', 'حساب'] },
  { title: 'بودیجه', icon: 'mdi-cash-plus', to: '/app/finance/cashflow/income', perm: 'finincalindex.view', group: 'بخش حساب مالی', aliases: ['income', 'budget'] },
  { title: 'مصارف', icon: 'mdi-cash-minus', to: '/app/finance/cashflow/expense', perm: 'finincalindex.view', group: 'بخش حساب مالی', aliases: ['expense', 'مصرف'] },
  { title: 'د تیلو حسابونه', icon: 'mdi-oil', to: '/app/finance/oil/accounts', perm: 'finincalindex.view', group: 'بخش حساب مالی', aliases: ['oil account'] },
  { title: 'راغلي تیل', icon: 'mdi-truck-delivery-outline', to: '/app/finance/oil/income', perm: 'finincalindex.view', group: 'بخش حساب مالی', aliases: ['oil income'] },
  { title: 'د تیلو مصرف', icon: 'mdi-fuel', to: '/app/finance/oil/expense', perm: 'finincalindex.view', group: 'بخش حساب مالی', aliases: ['oil expense'] },
  { title: 'صادره وارده', icon: 'mdi-swap-horizontal-bold', to: '/app/outgoingincommings', perm: 'outgoingincomming.view', aliases: ['outgoing', 'incoming', 'letter'] },
  { title: 'راپورها', icon: 'mdi-chart-box-multiple-outline', to: '/app/dailyfoulreports', perm: 'reports.view', aliases: ['reports', 'report', 'راپور'] },
  { title: 'راپور روزانه', icon: 'mdi-calendar-today', to: '/app/dailyfoulreports', perm: 'dailyfoulreports.view', group: 'راپورها', aliases: ['daily report'] },
  { title: 'راپور هفته وار', icon: 'mdi-calendar-week', to: '/app/weeklyfoulreports', perm: 'weeklyfoulreports.view', group: 'راپورها', aliases: ['weekly report'] },
  { title: 'راپور ماهانه', icon: 'mdi-calendar-month', to: '/app/monthlyfoulreports', perm: 'monthlyfoulreports.view', group: 'راپورها', aliases: ['monthly report'] },
  { title: 'راپور ربعه وار', icon: 'mdi-calendar-range', to: '/app/quarterlyfoulreports', perm: 'quarterlyfoulreports.view', group: 'راپورها', aliases: ['quarterly report'] },
  { title: 'راپور سالانه', icon: 'mdi-calendar', to: '/app/yearlyfoulreports', perm: 'yearlyfoulreports.view', group: 'راپورها', aliases: ['yearly report'] },
  { title: 'آرشیف', icon: 'mdi-archive-outline', to: '/app/alltimefoulreports', perm: 'alltimefoulreports.view', group: 'راپورها', aliases: ['archive'] },
  { title: 'نوت', icon: 'mdi-note-text-outline', to: '/app/reportnote', perm: 'reports.view', group: 'راپورها', aliases: ['note', 'notes'] },
  { title: 'غیر حاضری راپور', icon: 'mdi-chart-donut-variant', to: '/app/khayratreports', perm: 'khayratreport.view', aliases: ['khayrat'] },
  { title: 'نقشه', icon: 'mdi-map-marker-radius-outline', to: '/app/map-afg', perm: 'map-afg.view', aliases: ['map'] },
  { title: 'ارتباطی مرکز', icon: 'mdi-chat-processing-outline', to: '/app/inlineinfo', perm: 'inlineinfo.view', aliases: ['inline', 'center'] },
  { title: 'نقش ها', icon: 'mdi-shield-account-outline', to: '/app/roles', perm: 'roles.view', aliases: ['roles', 'role', 'permission'] },
  { title: 'استفاده کننده ها', icon: 'mdi-account-group-outline', to: '/app/users', perm: 'users.view', aliases: ['users', 'user', 'کاربر'] },
  { title: 'تنظیمات سیستم', icon: 'mdi-cog-outline', to: '/app/settings', any: ['settings.view', 'systemsetting.view'], aliases: ['settings', 'system'], suggest: true },
  { title: 'پروفایل', icon: 'mdi-account-circle-outline', to: '/app/profile', aliases: ['profile', 'account'] },
  { title: 'اطلاعیه ها', icon: 'mdi-bell-outline', to: '/app/notifications', perm: 'notifications.view', aliases: ['notifications', 'notification'] },
  { title: 'کارمندان فعال', icon: 'mdi-account-check-outline', to: '/app/activeemployees', perm: 'activeandinactiveemployees.view', aliases: ['active employees'] },
  { title: 'کارمندان غیر فعال', icon: 'mdi-account-off-outline', to: '/app/inactiveemployees', perm: 'activeandinactiveemployees.view', aliases: ['inactive employees'] },

  { title: 'ولایت', icon: 'mdi-map-marker-outline', to: '/app/settings/provinces', any: ['provinces.view', 'settings-provinces.view'], group: 'تنظیمات سیستم' },
  { title: 'والسوالی/حوزه', icon: 'mdi-map-marker-radius-outline', to: '/app/settings/districts', any: ['districts.view', 'settings-districts.view'], group: 'تنظیمات سیستم', aliases: ['district', 'ولسوالی'] },
  { title: 'آمریت', icon: 'mdi-map-outline', to: '/app/settings/zones/zones', any: ['zones.view', 'settings-zones.view'], group: 'تنظیمات سیستم' },
  { title: 'حوزه', icon: 'mdi-map-legend', to: '/app/settings/zones/hawzas', any: ['hawzas.view', 'settings-hawzas.view'], group: 'تنظیمات سیستم' },
  { title: 'نوع رویداد', icon: 'mdi-shape-outline', to: '/app/event-types', any: ['event_types.view', 'event-types.view'], group: 'تنظیمات سیستم' },
  { title: 'نوع مشخص رویداد', icon: 'mdi-shape-plus-outline', to: '/app/event-specific-types', any: ['event_specific_types.view', 'event-specific-types.view'], group: 'تنظیمات سیستم' },
  { title: 'نوع جنایی', icon: 'mdi-shape-plus-outline', to: '/app/jenayee-type', any: ['jenayee_types.view', 'jenayee-type.view'], group: 'تنظیمات سیستم' },
  { title: 'نوع سیاسی', icon: 'mdi-shape-plus-outline', to: '/app/seyasee-type', any: ['seyasee_types.view', 'seyasee-type.view'], group: 'تنظیمات سیستم' },
  { title: 'ارگان برای گفتکو های انجام شده', icon: 'mdi-domain', to: '/app/organs', perm: 'organs.view', group: 'تنظیمات سیستم', aliases: ['organ', 'ارگان'] },
  { title: 'مدیریت وظایف', icon: 'mdi-briefcase-outline', to: '/app/jobs-type', any: ['jobs.view', 'jobs-type.view'], group: 'تنظیمات سیستم', aliases: ['job', 'وظیفه'] },
  { title: 'نوع فعالیت منبع', icon: 'mdi-clipboard-list-outline', to: '/app/resource-activity-types', any: ['resource_activity_types.view', 'resource-activity-types.view'], group: 'تنظیمات سیستم' },
  { title: 'موقف جهادی', icon: 'mdi-hand-peace', to: '/app/mawqafjahadi', perm: 'mawqafjahadi.view', group: 'تنظیمات سیستم' },
  { title: 'فارمت آمریت', icon: 'mdi-map-marker-multiple-outline', to: '/app/formatzone', perm: 'formatzone.view', group: 'تنظیمات سیستم' },
  { title: 'نوعیت اسناد', icon: 'mdi-file-document-outline', to: '/app/docstypesindex', any: ['docstypes.view', 'docstypesindex.view'], group: 'تنظیمات سیستم' },
  { title: 'مرجع اسناد', icon: 'mdi-file-document-edit-outline', to: '/app/marjas', perm: 'marjas.view', group: 'تنظیمات سیستم' },
  { title: '۵۶ فارمټ ډراپ ډوون', icon: 'mdi-book', to: '/app/letryformatindex', perm: 'letryformatindex.view', group: 'تنظیمات سیستم' },
  { title: 'کتگوری اطلاعات', icon: 'mdi-book', to: '/app/infoCategory', perm: 'InfoCategory.view', group: 'تنظیمات سیستم' },
  { title: 'مشخصات سیستم', icon: 'mdi-cog-outline', to: '/app/settings/system-profile', group: 'تنظیمات سیستم', aliases: ['system profile', 'logo'] },

  { title: 'فورم‌های فساد ساعین', icon: 'mdi-alert-octagon-outline', to: '/app/saeenfasad', perm: 'saeen_fasads.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های فساد', icon: 'mdi-alert-outline', to: '/app/fasads', perm: 'fasads.view', group: '۵۶ فارمت' },
  { title: 'جواسیس', icon: 'mdi-incognito', to: '/app/jawasis', perm: 'jawasis.view', group: '۵۶ فارمت', aliases: ['جاسوس'] },
  { title: 'فورم‌های سرقت', icon: 'mdi-bag-personal', to: '/app/heist', any: ['heists.view', 'heist.view'], group: '۵۶ فارمت' },
  { title: 'فورم‌های بندی‌ها', icon: 'mdi-handcuffs', to: '/app/captives', perm: 'captives.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های ملک‌ها', icon: 'mdi-home-city-outline', to: '/app/mulkanos', perm: 'mulkanos.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های دخول/خروج', icon: 'mdi-door-open', to: '/app/entry-exits', perm: 'entry_exits.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های صرافی‌ها', icon: 'mdi-cash-multiple', to: '/app/sarafyanos', perm: 'sarafyanos.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های کلب‌ها', icon: 'mdi-cards-club', to: '/app/clubs', perm: 'clubs.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های مهاجرت', icon: 'mdi-airplane', to: '/app/mahajerats', perm: 'mahajerats.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های سربازان', icon: 'mdi-account-hard-hat', to: '/app/soldiers', perm: 'soldiers.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های مواد مخدر', icon: 'mdi-pill', to: '/app/mawads', perm: 'mawads.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های مجرمین گمشده', icon: 'mdi-account-question-outline', to: '/app/lostmujremeens', perm: 'lost_mujremeens.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های خانه‌های سربازان قدیمی', icon: 'mdi-home-outline', to: '/app/oldsoldiershomes', perm: 'old_soldiers_homes.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های دزدان', icon: 'mdi-incognito', to: '/app/robbers', perm: 'robbers.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های افراد مسلح غیرمسؤول', icon: 'mdi-account-alert-outline', to: '/app/nonresponsablepeople', perm: 'non_responsable_people.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های خانه‌های مشکوک', icon: 'mdi-home-alert-outline', to: '/app/mashkokhomes', perm: 'mashkok_homes.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های ساحات آسیب‌دیده', icon: 'mdi-home-flood', to: '/app/damageplaces', perm: 'damage_places.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های ساحات رسانندهٔ آسیب', icon: 'mdi-alert-decagram-outline', to: '/app/damagerasanplaces', perm: 'damage_rasan_places.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های دکان‌های مشکوک', icon: 'mdi-store-alert-outline', to: '/app/mashkokshops', perm: 'mashkok_shops.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های لوچک‌ها', icon: 'mdi-account-search-outline', to: '/app/luchakanos', perm: 'luchakanos.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های مکتب‌ها', icon: 'mdi-school-outline', to: '/app/schools', perm: 'schools.view', group: '۵۶ فارمت', aliases: ['school', 'مکتب'] },
  { title: 'فورم‌های ایستگاه‌های بس', icon: 'mdi-bus-stop', to: '/app/hadobusstops', perm: 'hado_bus_stops.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های شرکت‌های ترانسپورتی', icon: 'mdi-truck-outline', to: '/app/transportcompanies', perm: 'transport_companies.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های کمپنی‌های فروش موتر', icon: 'mdi-car-outline', to: '/app/carsalescompanies', perm: 'car_sales_companies.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های پارکینگ', icon: 'mdi-parking', to: '/app/parkings', perm: 'parkings.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های بانک‌ها', icon: 'mdi-bank-outline', to: '/app/banks', perm: 'banks.view', group: '۵۶ فارمت', aliases: ['bank', 'بانک'] },
  { title: 'فورم‌های آپارتمان‌ها', icon: 'mdi-office-building-outline', to: '/app/apartments', perm: 'apartments.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های رهنمای معاملات', icon: 'mdi-handshake-outline', to: '/app/rahnomamamlats', perm: 'rahnoma_mamlats.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های شفاخانه‌ها', icon: 'mdi-hospital-box-outline', to: '/app/hosppitals', perm: 'hosppitals.view', group: '۵۶ فارمت', aliases: ['hospital', 'شفاخانه'] },
  { title: 'فورم‌های کارگاه‌ها', icon: 'mdi-hammer-wrench', to: '/app/workshops', perm: 'work_shops.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های بدماش‌ها', icon: 'mdi-account-alert', to: '/app/badmashanos', perm: 'badmashanos.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های سرای‌ها', icon: 'mdi-warehouse', to: '/app/sarayanos', perm: 'sarayanos.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های دفاتر امارت', icon: 'mdi-office-building', to: '/app/emaratoffices', perm: 'emarat_offices.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های لیله ها و اطاق های کرایی', icon: 'mdi-bed-outline', to: '/app/hostels', perm: 'hostels.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های هوتل‌ها', icon: 'mdi-bed', to: '/app/hotels', perm: 'hotels.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های مدارس دینی', icon: 'mdi-book-open-page-variant-outline', to: '/app/madrasas', perm: 'madrasas.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های افراد مشکوک', icon: 'mdi-account-question', to: '/app/mashkokpeoples', perm: 'mashkok_people.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های مجاهدین', icon: 'mdi-account-star-outline', to: '/app/mujahedinos', perm: 'mujahedinos.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های قومندانان جهادی قدیمی', icon: 'mdi-account-clock-outline', to: '/app/oldjahadis', perm: 'old_jahadis.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های تاجران', icon: 'mdi-account-cash-outline', to: '/app/tujarans', perm: 'tujarans.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های چک‌پوینت‌ها', icon: 'mdi-shield-check-outline', to: '/app/checkpoints', perm: 'checkpoints.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های مسجدها', icon: 'mdi-home-roof', to: '/app/masjids', perm: 'masjids.view', group: '۵۶ فارمت', aliases: ['مسجد'] },
  { title: 'فورم‌های شاهراه‌ها', icon: 'mdi-road-variant', to: '/app/highways', perm: 'highways.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های شرکت‌ها', icon: 'mdi-domain', to: '/app/companies', perm: 'companies.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های ساحات دزدی شده', icon: 'mdi-map-marker-alert-outline', to: '/app/donelostplaces', perm: 'donelostplaces.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های پروژه‌های اقتصادی', icon: 'mdi-chart-line', to: '/app/econemyprojects', perm: 'econemyprojects.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های ساحات دستگیری مجرمین', icon: 'mdi-map-marker-account', to: '/app/criminalcaptureplaces', perm: 'criminalcaptureplaces.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های پمپ‌ها', icon: 'mdi-gas-station-outline', to: '/app/pumps', perm: 'pumps.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های شوراها', icon: 'mdi-account-group-outline', to: '/app/shoras', perm: 'shoras.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های اماکن حکومتی', icon: 'mdi-bank', to: '/app/governmentplaces', perm: 'governmentplaces.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های سوپرمارکت‌ها', icon: 'mdi-cart-outline', to: '/app/supermarkets', perm: 'supermarkets.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های پارک‌ها', icon: 'mdi-pine-tree', to: '/app/parks', perm: 'parks.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های موسسه ها و نهاد های داخلی', icon: 'mdi-domain', to: '/app/complexes', perm: 'complexes.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های اماکن دیپلوماتیک', icon: 'mdi-flag-outline', to: '/app/deplomaticplaces', perm: 'deplomaticplaces.view', group: '۵۶ فارمت' },
  { title: 'فورم‌های پوهنتون‌ها', icon: 'mdi-school', to: '/app/universities', perm: 'universities.view', group: '۵۶ فارمت', aliases: ['university', 'پوهنتون'] },
  { title: 'فورم‌های وکیلان', icon: 'mdi-gavel', to: '/app/wakilanos', perm: 'wakilanos.view', group: '۵۶ فارمت' },
];

function norm(value) {
  return String(value || '')
    .toLowerCase()
    .replace(/[ي]/g, 'ی')
    .replace(/[ك]/g, 'ک')
    .replace(/[ة]/g, 'ه')
    .replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
    .replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
    .trim();
}

function allowed(page, can) {
  if (page.any?.length) return page.any.some((perm) => can(perm));
  if (page.perm) return can(permOf(page));
  return true;
}

function permOf(page) {
  return page.perm;
}

function toResult(page, t, section) {
  return {
    key: `system-${page.to}-${page.title}`,
    kind: 'system',
    section,
    title: t(page.title),
    subtitle: t(page.group || 'صفحه سیستم'),
    icon: page.icon,
    path: page.to,
    label: page.group || 'سیستم',
  };
}

export function visibleSystemPages(can) {
  return pages.filter((page) => allowed(page, can));
}

export function matchSystemPages(query, can, t) {
  const q = norm(query);
  if (!q) return [];

  return visibleSystemPages(can)
    .filter((page) => {
      const hay = [page.title, t(page.title), page.group, page.to, ...(page.aliases || [])]
        .map(norm)
        .join(' ');
      return hay.includes(q);
    })
    .slice(0, 8)
    .map((page) => toResult(page, t, 'system'));
}

export function suggestedSystemPages(can, t, limit = 6) {
  const visible = visibleSystemPages(can);
  const picked = visible.filter((page) => page.suggest);
  const list = (picked.length ? picked : visible).slice(0, limit);
  return list.map((page) => toResult(page, t, 'pages'));
}
