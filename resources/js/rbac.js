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
 * - For API resource modules → use API name (e.g. "office_employees.view")
 */
export const ROUTE_CANDIDATES = [
  // Core App Pages
  { slug: 'dashboards', path: '/app/dashboards', perm: 'dashboards.view' },
  { slug: 'activeandinactiveemployees', path: '/app/activeandinactiveemployees', perm: 'activeandinactiveemployees.view' },
  { slug: 'users', path: '/app/users', perm: 'users.view' },
  { slug: 'officeemployee', path: '/app/officeemployee', perm: 'officeemployee.view' },
  { slug: 'resources', path: '/app/resources', perm: 'resources.view' },
  { slug: 'events', path: '/app/events', perm: 'events.view' },
  { slug: 'delevirycaptive', path: '/app/delevirycaptive', perm: 'delevirycaptive.view' },
  { slug: 'informations', path: '/app/informations', perm: 'informations.view' },

  // ✅ Inline Info Pages (matching PHP exactly)
  { slug: 'inlineinfo', path: '/app/inlineinfo', perm: 'inlineinfo.view' },
  { slug: 'resourceinfo', path: '/app/resourceinfo', perm: 'resourceinfo.view' },
  { slug: 'resourcetab', path: '/app/resourcetab', perm: 'resourcetab.view' },
  { slug: 'eventtabl', path: '/app/eventtabl', perm: 'eventtabl.view' },

  { slug: 'fifthyformatsroute', path: '/app/fifthyformatsroute', perm: 'fifthyformatsroute.view' },
  { slug: 'finincalindex', path: '/app/finincalindex', perm: 'finincalindex.view' },
  { slug: 'map-afg', path: '/app/map-afg', perm: 'map-afg.view' },
  { slug: 'offlinemap', path: '/app/offlinemap', perm: 'offlinemap.view' },
  { slug: 'roles', path: '/app/roles', perm: 'roles.view' },

  // Dashboard Pages
  { slug: 'PeopleDashboard', path: '/app/PeopleDashboard', perm: 'PeopleDashboard.view' },
  { slug: 'InfoEnevtDashboard', path: '/app/InfoEnevtDashboard', perm: 'InfoEnevtDashboard.view' },
  { slug: 'FifthyFormatDashboard', path: '/app/FifthyFormatDashboard', perm: 'FifthyFormatDashboard.view' },
  { slug: 'FininaceDashboard', path: '/app/FininaceDashboard', perm: 'FininaceDashboard.view' },

  // ---- API modules (use API perms) ----
  { slug: 'resedints', path: '/app/resedintemployee', perm: 'resedints.view' },
  { slug: 'office_employees', path: '/app/officeemployee', perm: 'office_employees.view' },
  { slug: 'resources_api', path: '/app/resources', perm: 'resources_api.view' },
  { slug: 'informations_api', path: '/app/informations', perm: 'informations_api.view' },
  { slug: 'events_api', path: '/app/events', perm: 'events_api.view' },
  { slug: 'captive_deliveries', path: '/app/delevirycaptive', perm: 'captive_deliveries.view' },

  // ---- Finance ----
  { slug: 'account_types', path: '/app/account_types', perm: 'account_types.view' },
  { slug: 'accounts', path: '/app/accounts', perm: 'accounts.view' },
  { slug: 'incomes', path: '/app/incomes', perm: 'incomes.view' },
  { slug: 'expenses', path: '/app/expenses', perm: 'expenses.view' },
  { slug: 'oil_accounts', path: '/app/oil_accounts', perm: 'oil_accounts.view' },
  { slug: 'income_oils', path: '/app/income_oils', perm: 'income_oils.view' },
  { slug: 'expense_oils', path: '/app/expense_oils', perm: 'expense_oils.view' },
  { slug: 'oil_reports', path: '/app/oil_reports', perm: 'oil_reports.view' },
  { slug: 'finance_reports', path: '/app/finance_reports', perm: 'finance_reports.view' },

  // ---- 56 Formats (Base, One, Two, Three, Four) ----
  // I will generate these automatically in the next message if you confirm
];

/** Get permission by slug */
export function permFor(slug) {
  return ROUTE_CANDIDATES.find((i) => i.slug === slug)?.perm || null;
}

/** Return the first accessible path */
export function firstAccessiblePath(user) {
  if (!user) return null;
  const roles = user.roles ?? [];
  const perms = user.permissions ?? [];
  if (roles.includes('admin') || perms.includes('*')) return ROUTE_CANDIDATES[0]?.path ?? null;

  for (const item of ROUTE_CANDIDATES) {
    if (canAccess({ roles, permissions: perms }, item.perm)) return item.path;
  }
  return null;
}
