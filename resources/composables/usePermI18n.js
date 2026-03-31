// src/utils/permissionsLabelsPashto.js
// Keep English slugs for logic; show Pashto for UI.


// ---- Actions (CRUD) ----
export const ACTION_PASHTO = {
  view: 'لیدل',
  create: 'جوړول',
  update: 'تغییر',
  delete: 'حذف',
};

// ---- Modules/pages/resources -> Pashto labels ----
export const MODULE_PASHTO = {
  // ---- App Pages (routes) ----
  dashboards: 'ډشبورډ',
  activeandinactiveemployees: 'فعاله/غیرفعاله کارکوونکي',

  users: 'کاروونکي',
  officeemployee: 'د دفتر کارکوونکي',
  resources: 'منابع',
  events: 'واقعات',
  delevirycaptive: 'تسلیم شوی بندیان',
  informations: 'اطلاعات',

  inlineinfo: 'ارتباطی مرکز',
  resourceinfo: 'منبع معلومات',
  resourcetab: 'د اطلاعاتو فورم ',
  eventtabl: 'د پیښو فورم',

  fifthyformatsroute: 'د پنځوس فورمټونو صفحه',
  finincalindex: 'مالي بخش',

  'map-afg': 'د افغانستان نقشه',

  roles: 'رولونه/اجازې',
  rolepermissions: 'د رول صلاحیتونه',

  // notification + chat + reports + system
  notifications: 'نوتیفیکیشن',
  chatbox: 'چټ باکس',
  khayratreport: 'د خیریت راپور',
  systemsetting: 'سیسټم تنظیمات',
  outgoingincomming: 'صادره/وارده مکتوبونه',

  // foul reports
  dailyfoulreports: 'ورځني راپورونه',
  weeklyfoulreports: 'اوونیز راپورونه',
  monthlyfoulreports: 'میاشتني راپورونه',
  quarterlyfoulreports: 'ربع وار راپورونه',
  yearlyfoulreports: 'کلني راپورونه',
  alltimefoulreports: 'د ټول وخت راپورونه',

  // settings pages
  'settings-provinces': 'د ولایتونو تنظیمات',
  'settings-districts': 'د ولسوالیو تنظیمات',
  'settings-zones': 'د زونونو تنظیمات',
  'settings-hawzas': 'د حوزو تنظیمات',

  'event-types': 'د پېښو ډولونه تنظیمات',
  'event-specific-types': 'د پېښو ځانګړي ډولونه تنظیمات',
  'jenayee-type': 'جنایي ډول تنظیمات',
  'seyasee-type': 'سیاسي ډول تنظیمات',
  organs: 'ارګانونه تنظیمات',
  'jobs-type': 'د دندو ډولونه تنظیمات',
  'resource-activity-types': 'د سرچینو د فعالیت ډولونه تنظیمات',
  formatzone: 'د فارمیټ زون تنظمات',
  settings: 'تنظیمات',
  mawqafjahadi: 'جهادي موقف تنظیمات',
  docstypesindex: 'د اسنادو ډولونه تنظیمات',
  marjas: 'مرجع تنظیمات',

  Eventtab: 'د پیښو فورم',

  // ✅ Missing slugs (from UI screenshot)
  districts: 'ولسوالۍ تنظیمات',
  provinces: 'ولایتونه تنظیمات',
  zones: 'زونونه تنظیمات',
  hawzas: 'حوزې تنظیمات',
  jobs: 'دندې تنظیمات',

  docstypes: 'د اسنادو ډولونه تنظیمات',
  doctypes: 'د اسنادو ډولونه تنظیمات', // (just in case your slug is this)

  reports: 'راپورونه',
  infocategory: 'د اطلاعاتو کتګوري',
  letryformatindex: 'د ۵۶ فارمټ د سیستم د ترتیباتو برخه',
  managercomment: 'مالی برخه او د مدیر نظر',
  direction: 'د اطلاعاتو د تعقیب آمریت نظر',

  // These show as "... types" in UI (usually underscore slugs)
  event_types: 'د پېښو ډولونه تنظیمات',
  event_specific_types: 'د پېښو ځانګړي ډولونه تنظیمات',
  jenayee_types: 'جنایي ډولونه تنظیمات',
  seyasee_types: 'سیاسي ډولونه تنظیمات',
  resource_activity_types: 'د سرچینو د فعالیت ډولونه تنظیمات',

  // ---- API Resources ----
  office_employees: 'د دفتر کارکوونکي',
  captive_deliveries: 'د بندي سپارنې',

  // ---- Finance ----
  account_types: 'د حساب ډولونه',
  accounts: 'حسابونه',
  incomes: 'عواید',
  expenses: 'لګښتونه',
  oil_accounts: 'د تېلو حسابونه',
  income_oils: 'د تېلو عواید',
  expense_oils: 'د تېلو لګښتونه',

  // ---- 56 Formats ----
  fasads: 'فسادونه',
  jawasis: 'جواسیس',
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
  LetryFormatsIndex: '۶۵ فارمټ ډراپ ډوون',
  shoras: 'شوراګانې',
  checkpoints: 'چک پواینټونه',
  governmentplaces: 'دولتې ځایونه',
  supermarkets: 'سوپرمارکېټونه',
  parks: 'پارکونه',
  complexes: 'کمپلکسونه',
  deplomaticplaces: 'دیپلوماتیک ځایونه',
  // ✅ Missing (from UI screenshot)
  eventtab: 'د پېښو فورم',
  outgoingincomming: 'صادره/وارده مکتوبونه',
  attachmentDownload: 'د فایلونو ډانلوډ کول',
  attachmentdownload: 'د فایلونو ډانلوډ کول',

};

function beautify(s) {
  if (!s) return '';
  return String(s).replace(/[_-]/g, ' ').replace(/\s+/g, ' ').trim();
}

function parsePermission(perm) {
  const [module, action] = String(perm || '').split('.');
  return { module, action };
}

// Pashto-only label (no English slug)
export function labelForPermissionPashtoOnly(perm) {
  const { module, action } = parsePermission(perm);
  const modPs = MODULE_PASHTO[module];
  const actPs = ACTION_PASHTO[action];

  if (modPs && actPs) return `${modPs} (${actPs})`;
  if (modPs) return modPs;
  if (actPs) return actPs;

  const enPretty = [beautify(module), beautify(action)].filter(Boolean).join(' ');
  return enPretty || perm || '';
}

// Bilingual label (Pashto + slug)
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

// Items mapper for selects/list/cards
export function mapPermNamesToItemsBilingual(perms = []) {
  return perms.map((p) => ({ value: p, label: labelForPermissionBilingual(p) }));
}
