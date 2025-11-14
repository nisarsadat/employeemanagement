// Keep English slugs for logic; show Pashto for UI.

// ---- Actions (CRUD) ----
export const ACTION_PASHTO = {
  view: 'لیدل',
  create: 'جوړول',
  update: 'تغییر',
  delete: 'خذف',
};

// ---- Modules/pages/resources -> Pashto labels ----
export const MODULE_PASHTO = {
  // App pages (routes)
  dashboards: 'ډشبورډونه',
  activeandinactiveemployees: 'فعاله/غیرفعاله کارکوونکي',
  users: 'کاروونکي',
  officeemployee: 'د دفتر کارکوونکي',
  resources: 'سرچینې (پاڼه)',
  events: 'پیښې (پاڼه)',
  delevirycaptive: 'د بندي سپارنه (پاڼه)',
  informations: 'معلومات (پاڼه)',
  inlineinfo: 'په-پاڼه دننه معلومات',
  resourceinfo: 'د سرچینې معلومات',
  resourcetab: 'د سرچینې ټب',
  eventtabl: 'د پیښو جدول',
  fifthyformatsroute: 'د پنځوس فورمټونو لاره',
  finincalindex: 'مالي مخ',
  'map-afg': 'د افغانستان نقشه',
  offlinemap: 'افلاین نقشه',
  roles: 'رولونه/اجازې',

  // Dashboards
  PeopleDashboard: 'د خلکو ډشبورډ',
  InfoEnevtDashboard: 'معلومات/پیښو ډشبورډ',
  FifthyFormatDashboard: 'د پنځوس فورمټونو ډشبورډ',
  FininaceDashboard: 'مالي ډشبورډ',
  Eventtab: 'د پیښو ټب',

  // API resources
  resedints: 'استوګن (API)',
  office_employees: 'د دفتر کارکوونکي (API)',
  resources_api: 'سرچینې (API)',
  informations_api: 'معلومات (API)',
  events_api: 'پیښې (API)',
  captive_deliveries: 'د بندي سپارنې (API)',

  // Finance
  account_types: 'د حساب ډولونه',
  accounts: 'حسابونه',
  incomes: 'عواید',
  expenses: 'لګښتونه',
  oil_accounts: 'د تېلو حسابونه',
  income_oils: 'د تېلو عواید',
  expense_oils: 'د تېلو لګښتونه',
  oil_reports: 'د تېلو رپوټونه',
  finance_reports: 'مالي رپوټونه',

  // 56-formats (extend as needed)
  fasads: 'فسادونه',
  saeen_fasads: 'سعین فسادونه',
  heists: 'وسله‌والې غلاوې',
  captives: 'بندیان',
  mulkanos: 'ملکانو',
  entry_exits: 'ننوتل/وتل',
  sarafyanos: 'صرافان',
  clubs: 'کلبونه',
  mahajerats: 'مهاجرتونه',
  soldiers: 'سرتېري',
  mawads: 'مواد',
  lost_mujremeens: 'ورک مجرمان',
  old_soldiers_homes: 'د زړو سرتېرو کورونه',
  robbers: 'غله',
  non_responsable_people: 'نامسووله کسان',
  mashkok_homes: 'مشکوک کورونه',
  damage_places: 'د تاوان ځایونه',
  damage_rasan_places: 'زیان‌رسوونکې ځایونه',
  mashkok_shops: 'مشکوک دوکانونه',
  luchakanos: 'لوچکانو',
  schools: 'ښوونځي',
  hado_bus_stops: 'هادو د بس تمځایونه',
  transport_companies: 'د ترانسپورټ شرکتونه',
  car_sales_companies: 'د موټرو پلور شرکتونه',
  parkings: 'د پارکینګ ځایونه',
  banks: 'بانکونه',
  apartments: 'اپارتمانونه',
  rahnoma_mamlats: 'راهنما معاملات',
  hosppitals: 'روغتونونه',
  work_shops: 'ورکشاپونه',
  badmashanos: 'بدماشانو',
  sarayanos: 'سرایانو',
  hotels: 'هوټلونه',
  hostels: 'هاستلونه',
  tujarans: 'تجاران',
  mujahedinos: 'مجاهدین',
  emarat_offices: 'د امارت دفترونه',
  old_jahadis: 'زاړه جهادیان',
  mashkok_people: 'مشکوک خلک',
  madrasas: 'مدرسې',
  wakilanos: 'وکیلان',
  universities: 'پوهنتونونه',
  masjids: 'مسجدونه',
  highways: 'لوى لارې',
  companies: 'کمپنۍ',
  donelostplaces: 'ورک شوې ځایونه',
  econemyprojects: 'اقتصادي پروژې',
  criminalcaptureplaces: 'د مجرم نیولو ځایونه',
  pumps: 'پمپونه',
  shoras: 'شوراګانې',
  checkpoints: 'چک پواینټونه',
  governmentplaces: 'دولتې ځایونه',
  supermarkets: 'سوپرمارکېټونه',
  parks: 'پارکونه',
  complexes: 'کمپلکسونه',
  deplomaticplaces: 'دیپلوماتیک ځایونه',
};

function beautify(s) {
  if (!s) return '';
  return String(s).replace(/[_-]/g, ' ').replace(/\s+/g, ' ').trim();
}

function parsePermission(perm) {
  const [module, action] = String(perm || '').split('.');
  return { module, action };
}

// ▶ "Pashto (Pashto Action) — module.action"
export function labelForPermissionBilingual(perm) {
  const { module, action } = parsePermission(perm);
  const modPs = MODULE_PASHTO[module];
  const actPs = ACTION_PASHTO[action];
  if (modPs && actPs) return `${modPs} (${actPs}) — ${module}.${action}`;
  if (modPs) return `${modPs} — ${module}.${action || ''}`.replace(/\.$/, '');
  if (actPs) return `${actPs} — ${module || ''}.${action}`.replace(/^\./, '');
  const enPretty = [beautify(module), beautify(action)].filter(Boolean).join('.');
  return enPretty || perm || '';
}

// ▶ For selects: [{ value: 'office_employees.view', label: 'سرچینې ... — office_employees.view' }]
export function mapPermNamesToItemsBilingual(perms = []) {
  return perms.map((p) => ({ value: p, label: labelForPermissionBilingual(p) }));
}
