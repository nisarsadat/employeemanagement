// src/rbac.js

// A user with role 'admin' or permission '*' can access everything
export function canAccess(user, requiredPerm) {
  if (!requiredPerm) return true;
  const roles = user?.roles ?? [];
  const perms = user?.permissions ?? [];
  if (roles.includes('admin') || perms.includes('*')) return true;
  return perms.includes(requiredPerm);
}

/**
 * Route permission mapping based on config/rbac.php
 * RULES:
 * - For page modules → "<slug>.view"
 * - For API resource modules → "<slug>.view" as well (same)
 */

// ----- Core candidates (manual) -----
const CORE = [
  { slug: 'dashboards', path: '/app/dashboards', perm: 'dashboards.view' },

  // ✅ same perm for both routes
  {
    slug: 'activeandinactiveemployees',
    path: '/app/activeemployees',
    perm: 'activeandinactiveemployees.view',
  },
  {
    slug: 'activeandinactiveemployees',
    path: '/app/inactiveemployees',
    perm: 'activeandinactiveemployees.view',
  },

  { slug: 'users', path: '/app/users', perm: 'users.view' },

  { slug: 'officeemployee', path: '/app/officeemployee', perm: 'officeemployee.view' },
  { slug: 'resources', path: '/app/resources', perm: 'resources.view' },
  { slug: 'events', path: '/app/events', perm: 'events.view' },
  { slug: 'delevirycaptive', path: '/app/delevirycaptive', perm: 'delevirycaptive.view' },
  { slug: 'informations', path: '/app/informations', perm: 'informations.view' },

  // Inline info
  { slug: 'inlineinfo', path: '/app/inlineinfo', perm: 'inlineinfo.view' },
  { slug: 'resourceinfo', path: '/app/resourceinfo', perm: 'resourceinfo.view' },
  { slug: 'resourcetab', path: '/app/resourcetab', perm: 'resourcetab.view' },
  { slug: 'eventtabl', path: '/app/eventtabl', perm: 'eventtabl.view' },

  // hub + finance page
  { slug: 'fifthyformatsroute', path: '/app/fifthyformatsroute', perm: 'fifthyformatsroute.view' },
  { slug: 'finincalindex', path: '/app/finincalindex', perm: 'finincalindex.view' },

  // map
  { slug: 'map-afg', path: '/app/map-afg', perm: 'map-afg.view' },
  { slug: 'offlinemap', path: '/app/offlinemap', perm: 'offlinemap.view' },

  // rbac pages
  { slug: 'roles', path: '/app/roles', perm: 'roles.view' },
  { slug: 'rolepermissions', path: '/rbac/roles/:id/permissions', perm: 'rolepermissions.view' },

  // notifications (router path is plural)
  { slug: 'notifications', path: '/app/notifications', perm: 'notifications.view' },

  // foul reports
  { slug: 'dailyfoulreports', path: '/app/dailyfoulreports', perm: 'dailyfoulreports.view' },
  { slug: 'monthlyfoulreports', path: '/app/monthlyfoulreports', perm: 'monthlyfoulreports.view' },
  { slug: 'yearlyfoulreports', path: '/app/yearlyfoulreports', perm: 'yearlyfoulreports.view' },
  { slug: 'weeklyfoulreports', path: '/app/weeklyfoulreports', perm: 'weeklyfoulreports.view' },
  {
    slug: 'quarterlyfoulreports',
    path: '/app/quarterlyfoulreports',
    perm: 'quarterlyfoulreports.view',
  },
  { slug: 'alltimefoulreports', path: '/app/alltimefoulreports', perm: 'alltimefoulreports.view' },

  // activity reports
  { slug: 'fiftyformatreports', path: '/app/fiftyformatreports', perm: 'fiftyformatreports.view' },
  {
    slug: 'officeemployeereports',
    path: '/app/officeemployeereports',
    perm: 'officeemployeereports.view',
  },
  { slug: 'eventreports', path: '/app/eventreports', perm: 'eventreports.view' },
  { slug: 'informationreports', path: '/app/informationreports', perm: 'informationreports.view' },

  // system settings index
  { slug: 'systemsetting', path: '/app/settings', perm: 'systemsetting.view' },

  // settings pages
  { slug: 'settings-provinces', path: '/app/settings/provinces', perm: 'settings-provinces.view' },
  { slug: 'settings-districts', path: '/app/settings/districts', perm: 'settings-districts.view' },
  { slug: 'settings-zones', path: '/app/settings/zones/zones', perm: 'settings-zones.view' },
  { slug: 'settings-hawzas', path: '/app/settings/zones/hawzas', perm: 'settings-hawzas.view' },

  { slug: 'event-types', path: '/app/event-types', perm: 'event-types.view' },
  {
    slug: 'event-specific-types',
    path: '/app/event-specific-types',
    perm: 'event-specific-types.view',
  },
  { slug: 'jenayee-type', path: '/app/jenayee-type', perm: 'jenayee-type.view' },
  { slug: 'seyasee-type', path: '/app/seyasee-type', perm: 'seyasee-type.view' },
  { slug: 'organs', path: '/app/organs', perm: 'organs.view' },
  { slug: 'jobs-type', path: '/app/jobs-type', perm: 'jobs-type.view' },
  {
    slug: 'resource-activity-types',
    path: '/app/resource-activity-types',
    perm: 'resource-activity-types.view',
  },
  { slug: 'formatzone', path: '/app/formatzone', perm: 'formatzone.view' },
  { slug: 'mawqafjahadi', path: '/app/mawqafjahadi', perm: 'mawqafjahadi.view' },
  { slug: 'docstypesindex', path: '/app/docstypesindex', perm: 'docstypesindex.view' },
  { slug: 'marjas', path: '/app/marjas', perm: 'marjas.view' },
];

// ----- 56 Formats candidates (generated) -----
// IMPORTANT: these slugs must match what you use in permFor('...') and what backend issues as permissions
const FIFTY_FORMATS = [
  // Base
  { slug: 'fasads', path: '/app/fasads' },
  { slug: 'saeen_fasads', path: '/app/saeenfasad' },
  { slug: 'heists', path: '/app/heist' },
  { slug: 'captives', path: '/app/captives' },
  { slug: 'mulkanos', path: '/app/mulkanos' },
  { slug: 'entry_exits', path: '/app/entry-exits' },
  { slug: 'sarafyanos', path: '/app/sarafyanos' },
  { slug: 'clubs', path: '/app/clubs' },
  { slug: 'mahajerats', path: '/app/mahajerats' },
  { slug: 'soldiers', path: '/app/soldiers' },

  // One
  { slug: 'mawads', path: '/app/mawads' },
  { slug: 'lost_mujremeens', path: '/app/lostmujremeens' },
  { slug: 'old_soldiers_homes', path: '/app/oldsoldiershomes' },
  { slug: 'robbers', path: '/app/robbers' },
  { slug: 'non_responsable_people', path: '/app/nonresponsablepeople' },
  { slug: 'mashkok_homes', path: '/app/mashkokhomes' },
  { slug: 'damage_places', path: '/app/damageplaces' },
  { slug: 'damage_rasan_places', path: '/app/damagerasanplaces' },
  { slug: 'mashkok_shops', path: '/app/mashkokshops' },
  { slug: 'luchakanos', path: '/app/luchakanos' },

  // Two
  { slug: 'schools', path: '/app/schools' },
  { slug: 'hado_bus_stops', path: '/app/hadobusstops' },
  { slug: 'transport_companies', path: '/app/transportcompanies' },
  { slug: 'car_sales_companies', path: '/app/carsalescompanies' },
  { slug: 'parkings', path: '/app/parkings' },
  { slug: 'banks', path: '/app/banks' },
  { slug: 'apartments', path: '/app/apartments' },
  { slug: 'rahnoma_mamlats', path: '/app/rahnomamamlats' },
  { slug: 'hosppitals', path: '/app/hosppitals' },
  { slug: 'work_shops', path: '/app/workshops' },

  // Three
  { slug: 'badmashanos', path: '/app/badmashanos' },
  { slug: 'sarayanos', path: '/app/sarayanos' },
  { slug: 'hotels', path: '/app/hotels' },
  { slug: 'hostels', path: '/app/hostels' },
  { slug: 'tujarans', path: '/app/tujarans' },
  { slug: 'mujahedinos', path: '/app/mujahedino' }, // your router uses /app/mujahedino + module page /app/mujahedinoform
  { slug: 'emarat_offices', path: '/app/emaratoffices' },
  { slug: 'old_jahadis', path: '/app/oldjahadis' },
  { slug: 'mashkok_people', path: '/app/mashkokpeoples' },
  { slug: 'madrasas', path: '/app/madrasas' },

  // Four
  { slug: 'wakilanos', path: '/app/wakilanos' },
  { slug: 'universities', path: '/app/universities' },
  { slug: 'masjids', path: '/app/masjids' },
  { slug: 'highways', path: '/app/highways' },
  { slug: 'companies', path: '/app/companies' },
  { slug: 'donelostplaces', path: '/app/donelostplaces' },
  { slug: 'econemyprojects', path: '/app/econemyprojects' },
  { slug: 'criminalcaptureplaces', path: '/app/criminalcaptureplaces' },
  { slug: 'pumps', path: '/app/pumps' },
  { slug: 'shoras', path: '/app/shoras' },
  { slug: 'checkpoints', path: '/app/checkpoints' },
  { slug: 'governmentplaces', path: '/app/governmentplaces' },
  { slug: 'supermarkets', path: '/app/supermarkets' },
  { slug: 'parks', path: '/app/parks' },
  { slug: 'complexes', path: '/app/complexes' },
  { slug: 'deplomaticplaces', path: '/app/deplomaticplaces' },
].map((x) => ({ ...x, perm: `${x.slug}.view` }));

// ✅ Final exported candidates
export const ROUTE_CANDIDATES = [...CORE, ...FIFTY_FORMATS];

/** Get permission by slug (used by router meta.perm = permFor('...')) */
export function permFor(slug) {
  return ROUTE_CANDIDATES.find((i) => i.slug === slug)?.perm || null;
}

/** Return the first accessible path */
export function firstAccessiblePath({ roles = [], permissions = [] } = {}) {
  if (roles.includes('admin') || permissions.includes('*'))
    return ROUTE_CANDIDATES[0]?.path ?? null;

  for (const item of ROUTE_CANDIDATES) {
    if (canAccess({ roles, permissions }, item.perm)) return item.path;
  }
  return null;
}
