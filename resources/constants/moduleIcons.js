// src/constants/moduleIcons.js

/**
 * ✅ Route ICONS (as you provided) — DO NOT change resource here
 */
export const ROUTE_ICONS = {
  office_employee: { label: 'کارمند', mdi: 'mdi-account' },
  resource: { label: 'منبع', mdi: 'mdi-circle' }, // ✅ keep as-is (route)

  fasad: { label: 'شر او فساد', mdi: 'mdi-account-multiple-minus' },
  saeen_fasad: { label: 'ساعین بالفساد', mdi: 'mdi-account-multiple-outline' },
  event: { label: 'پیښې', mdi: 'mdi-circle' },

  hotels: { label: 'میلمستونونو،مسافرخانو،هوټلونه او رسټوردانټ', mdi: 'mdi-silverware-fork-knife' },
  hostels: { label: 'لیلې او کرایي اطاقونه', mdi: 'mdi-bed' },
  tujarans: { label: 'علماوو ، متنفذینو ، تجارانو او عالي رتبه اشخاص', mdi: 'mdi-account-multiple-plus' },
  mujahedinos: { label: 'مجاهدین', mdi: 'mdi-account-check' },
  old_soldiers_homes: { label: 'د  پخواني چاروکو کورونه', mdi: 'mdi-home-search' },
  old_jahadis: { label: 'پخواني جهادي قومندانان', mdi: 'mdi-account-star' },
  mashkok_people: { label: 'مشکوک افراد', mdi: 'mdi-account-question' },
  madrasas: { label: 'دینې مدارس', mdi: 'mdi-town-hall' },

  schools: { label: 'ښونځیو او روزنیز مرکزونو او روزنیز مراکز', mdi: 'mdi-book-open-blank-variant-outline' },
  hado_bus_stops: { label: 'هډې', mdi: 'mdi-car-multiple' },
  transport_companies: { label: 'ترانسپورتي شرکتونه', mdi: 'mdi-bus-multiple' },
  car_sales_companies: { label: 'موټر پلورونځی', mdi: 'mdi-car-hatchback' },
  parkings: { label: 'موټرو تمځای(پارکینګ)', mdi: 'mdi-car-brake-parking' },
  banks: { label: 'بانکونه', mdi: 'mdi-bank' },
  apartments: { label: 'رهایشي بلاکونه', mdi: 'mdi-city' },
  rahnoma_mamlats: { label: 'د معاملاتو رهنما', mdi: 'mdi-store-search-outline' },
  hosppitals: { label: 'روغتوتونه او صحي مراکز', mdi: 'mdi-hospital-box' },
  work_shops: { label: 'مستري خانې', mdi: 'mdi-car-wrench' },

  wakilanos: { label: 'وکیلان او ملکان', mdi: 'mdi-account-edit' },
  sarayanos: { label: 'سرایونه', mdi: 'mdi-warehouse' },
  universities: { label: 'پوهنتونونو ، دلوړو زده کړو موسسو او انسټیټیوټونو', mdi: 'mdi-school' },
  masjids: { label: 'مساجد خاناقاوې او تکیه خانې', mdi: 'mdi-mosque' },
  companies: { label: 'فابریکې او شرکتونه', mdi: 'mdi-factory' },
  complexes: { label: 'داخلي موسسې او بنسټونه', mdi: 'mdi-traffic-light-outline' },

  captives: { label: 'راخوشي شوي بندیان', mdi: 'mdi-account-key' },
  econemy_projects: { label: 'مهم تاسیسات او اقتصادي پروژې', mdi: 'mdi-greenhouse' },
  criminal_capture_places: { label: 'هغې ساحې چې مجرمین پکې نیول شوی', mdi: 'mdi-account-alert' },
  pumps: { label: 'پمپونه', mdi: 'mdi-gas-station' },
  shoras: { label: 'محلي شوراګانې', mdi: 'mdi-account-switch' },
  parks: { label: 'تفریحي پارکونه او سیاحتي سیمې', mdi: 'mdi-tree-outline' },
  governmentplaces: { label: 'دولتي ځایونه', mdi: 'mdi-home-plus-outline' },
  supermarkets: { label: 'تجارتي مراکز او سوپر مارکیټونه', mdi: 'mdi-cart' },

  emarat_offices: { label: 'امارتي اداراتو،مسولینو کورونه او میلمسنونونه', mdi: 'mdi-briefcase' },
  deplomaticplaces: { label: 'دیپلوماتیک ځایونه،خارجي موسسې،دبهرنیانو د اوسیدو او کار ځایونه', mdi: 'mdi-briefcase-variant' },
  checkpoints: { label: 'چک پواینټونه او پوستې', mdi: 'mdi-shield-lock' },
};

/**
 * ✅ Extras for Fifty Formats
 */
export const FIFTY_ICONS_EXTRA = {
  jawasis: { mdi: 'mdi-account-multiple', label: 'جواسیس' },
  heist: { mdi: 'mdi-knife-military', label: 'سرقت کوونکي' },
  soldiers: { mdi: 'mdi-account-clock-outline', label: 'پخواني چارواکې' },
  mawads: { mdi: 'mdi-pill-off', label: 'د نشه یی توکو پلورونکي' },
  lost_mujremeens: { mdi: 'mdi-magnify', label: 'نادرکه شوي مجرمین' },
  robbers: { mdi: 'mdi-account-remove', label: 'اختطاف ګران' },
  non_responsable_people: { mdi: 'mdi-pistol', label: 'غیر مسؤوله وسله وال' },
  mashkok_homes: { mdi: 'mdi-home-minus', label: 'مشکوکو کورونه' },
  damage_places: { mdi: 'mdi-shield-home', label: 'آسیب پذیره ساحې' },
  damage_rasan_places: { mdi: 'mdi-rocket-launch', label: 'آسیب رسانه ساحې' }, // ✅ fixed typo
  mashkok_shops: { mdi: 'mdi-store-minus', label: 'مشکوک دوکانونه' },
  luchakanos: { mdi: 'mdi-account-convert', label: 'لوچکان او بی بند و باره افراد' },
  entry_exits: { mdi: 'mdi-highway', label: 'د ښار اړوند دخولي/خروجي دروازې او فرعي لارې' },
  sarafyanos: { mdi: 'mdi-cash-multiple', label: 'صرافیانې' },
  clubs: { mdi: 'mdi-weight-lifter', label: 'ورزشي کلپونه' },
  mahajerats: { mdi: 'mdi-home-group-plus', label: 'نوي راغلي کورونه' },
  badmashanos: { mdi: 'mdi-account-off', label: 'زورواکان او ولکه داران' },
  highways: { mdi: 'mdi-highway', label: 'څلور لارې او درې لارې' },
  done_lost_places: { mdi: 'mdi-home-remove', label: 'هغه ساحې چې اختطاف پکې ترسره شوی' },

  // ✅ missing ones that exist in routes/map sources
  mulkanos: { mdi: 'mdi-account-tie-outline', label: 'د کوڅو نمایندګان' },
  information: { mdi: 'mdi-card-text-outline', label: 'اطلاعات' },
  captive_delivery: { mdi: 'mdi-handshake-outline', label: 'تحویل اسیر' },
};

export const ICON_MAP = { ...ROUTE_ICONS, ...FIFTY_ICONS_EXTRA };

export function getMdi(iconKey) {
  return ICON_MAP?.[iconKey]?.mdi || 'mdi-cube';
}
export function getLabel(iconKey) {
  return ICON_MAP?.[iconKey]?.label || '';
}
