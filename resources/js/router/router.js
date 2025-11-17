// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';

// ---------- Public ----------
import Login from '@/pages/Login.vue';

// ---------- Protected Shell (layout after login) ----------
import Home from '@/pages/Home.vue';

// ---------- Protected Child Pages (render inside Home.vue <router-view/>) ----------
import Dashboard from '@/pages/Dashboard/MainDashboard.vue';
import ActiveAndInactiveEmployees from '@/pages/ActiveInactive/ActiveAndInactiveEmployees.vue';

import User from '@/pages/User/User.vue';

import OfficeEmployeesPage from '@/pages/OfficeEmployee/OfficeEmployeesPage.vue';
import Resources from '@/pages/Resources/Resources.vue';
import Events from '@/pages/Events/Events.vue';
import DeleviryCaptive from '@/pages/DeleviryCaptive/DeleviryCaptive.vue';
import Informations from '@/pages/Informations/Informations.vue';

import FifthyFormatsRoute from '@/components/FiftyFormatsRoute.vue';

import Fasads from '@/pages/FiftyFormats/Fasads.vue';
import SaeenFasad from '@/pages/FiftyFormats/SaeenFasad.vue';
import Heist from '@/pages/FiftyFormats/Heist.vue';
import Captives from '@/pages/FiftyFormats/Captives.vue';
import Mulkanos from '@/pages/FiftyFormats/Mulkanos.vue';
import EntryExits from '@/pages/FiftyFormats/EntryExit.vue';
import Sarafyanos from '@/pages/FiftyFormats/Sarafyano.vue';
import Clubs from '@/pages/FiftyFormats/Clubs.vue';
import Mahajerats from '@/pages/FiftyFormats/Mahajerat.vue';
import Soldiers from '@/pages/FiftyFormats/Soldiers.vue';

import Mawads from '@/pages/FiftyFormats/Mawads.vue';
import LostMujremeens from '@/pages/FiftyFormats/LostMujremens.vue';
import OldSoldiersHomes from '@/pages/FiftyFormats/OldSoldiersHomes.vue';
import Robbers from '@/pages/FiftyFormats/Robbers.vue';
import NonResposablePeople from '@/pages/FiftyFormats/NonResponsablePeople.vue';
import MashkokHomes from '@/pages/FiftyFormats/MashkokHomes.vue';
import DamagePlacses from '@/pages/FiftyFormats/DamagePlacses.vue';
import DamageRasanPlaces from '@/pages/FiftyFormats/DamageRasanPlaces.vue';
import MashkokShops from '@/pages/FiftyFormats/MashkokShops.vue';
import Luchakanos from '@/pages/FiftyFormats/Luchakanos.vue';

import Schools from '@/pages/FiftyFormats/Schools.vue';
import HadoBusStop from '@/pages/FiftyFormats/HadoBusStop.vue';
import TransportCompany from '@/pages/FiftyFormats/TransportCompany.vue';
import CarSalesCompany from '@/pages/FiftyFormats/CarSalesCompany.vue';
import Parkings from '@/pages/FiftyFormats/Parkings.vue';
import Banks from '@/pages/FiftyFormats/Bank.vue';
import Apartments from '@/pages/FiftyFormats/Apartments.vue';
import RahnomaMamlats from '@/pages/FiftyFormats/RahnomaMamlat.vue';
import Hosppitals from '@/pages/FiftyFormats/Hosppital.vue';
import WorkShops from '@/pages/FiftyFormats/WorkShops.vue';

import Badmashanos from '@/pages/FiftyFormats/Badmashano.vue';
import Sarayanos from '@/pages/FiftyFormats/Sarayanos.vue';
import Hotels from '@/pages/FiftyFormats/Hotels.vue';
import Hostels from '@/pages/FiftyFormats/Hostels.vue';
import Tujarans from '@/pages/FiftyFormats/Tujarans.vue';
import Mujahedinos from '@/pages/FiftyFormats/Mujahedinos.vue';
import EmaratOffices from '@/pages/FiftyFormats/EmaratOffices.vue';
import OldJahadis from '@/pages/FiftyFormats/OldJahadis.vue';
import MashkokPeopls from '@/pages/FiftyFormats/MashkokPeoples.vue';
import Madrasa from '@/pages/FiftyFormats/Madrasa.vue';

import Wakilanos from '@/pages/FiftyFormats/Wakilano.vue';
import Universities from '@/pages/FiftyFormats/University.vue';
import Masjids from '@/pages/FiftyFormats/Masjid.vue';
import HighWays from '@/pages/FiftyFormats/Highway.vue';
import Companies from '@/pages/FiftyFormats/Company.vue';
import DoneLostPlaces from '@/pages/FiftyFormats/DoneLostPlaces.vue';
import EconemyProjects from '@/pages/FiftyFormats/EconemyProject.vue';
import CriminalCapturePlaces from '@/pages/FiftyFormats/CriminalCapturePlace.vue';
import Pumps from '@/pages/FiftyFormats/pump.vue';
import Shoras from '@/pages/FiftyFormats/Shora.vue';
import CheckPoints from '@/pages/FiftyFormats/Checkpoint.vue';
import GovernmentPlaces from '@/pages/FiftyFormats/GovernmentPlace.vue';
import Parks from '@/pages/FiftyFormats/Park.vue';
import Complexes from '@/pages/FiftyFormats/Complex.vue';
import DeplomaticPlaces from '@/pages/FiftyFormats/DeplomaticPlace.vue';
import SuperMarkets from '@/pages/FiftyFormats/SuperMarket.vue';

import finincalIndex from '@/pages/Finincal/finincalIndex.vue';

import Inlineinfoindex from '@/pages/InlineInfo/inlineinfoindex.vue';
import ResourceInfo from '@/pages/InlineInfo/ResourceInfo.vue';
import Resourcetab from '@/pages/InlineInfo/Resourcetab.vue';
import Eventtab from '@/pages/InlineInfo/Eventtab.vue';

import MapAfghanistan from '@/pages/Map/MapAfghanistan.vue';
import offlinemap from '@/pages/Map/offlinemap.vue';

import Roles from '@/pages/rbac/Roles.vue';
// import Permission from '@/pages/rbac/Permissions.vue';

import NoAccess from '@/pages/NoAccess.vue'; // simple page if blocked

// RBAC helpers
import { firstAccessiblePath, canAccess, permFor } from '@/rbac';
import FiftyFormatsReportPage from '../pages/reports/FiftyFormatsReportPage.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // Public (guest)
    {
      path: '/',
      name: 'Login',
      component: Login,
      meta: { guest: true },
    },

    // Protected shell (/app/*)
    {
      path: '/app',
      component: Home,
      meta: { requiresAuth: true },
      // When entering /app (no child), jump to the first allowed page
      // src/router/index.js
      beforeEnter: (to, from, next) => {
        // only auto-forward when the path is exactly /app
        if (to.path === '/app' || to.path === '/app/') {
          const roles = JSON.parse(sessionStorage.getItem('roles') || '[]');
          const permissions = JSON.parse(sessionStorage.getItem('permissions') || '[]');
          const target = firstAccessiblePath({ roles, permissions });
          return next(target || '/app/no-access');
        }
        next();
      },
      children: [
        // (If someone lands exactly on /app, child redirect is a safety net)
        { path: '', redirect: '/app/dashboards' },

        // Core (each with meta.perm)
        {
          path: 'dashboards',
          name: 'Dashboard',
          component: Dashboard,
          meta: { requiresAuth: true, perm: permFor('dashboards') },
        },
        {
          path: 'activeandinactiveemployees',
          component: ActiveAndInactiveEmployees,
          meta: { requiresAuth: true, perm: permFor('activeandinactiveemployees') },
        },

        // Users
        { path: 'users', component: User, meta: { requiresAuth: true, perm: permFor('users') } },

        // People & resources
        {
          path: 'officeemployee',
          component: OfficeEmployeesPage,
          meta: { requiresAuth: true, perm: permFor('officeemployee') },
        },

        {
          path: 'resources',
          component: Resources,
          meta: { requiresAuth: true, perm: permFor('resources') },
        },
        {
          path: 'events',
          component: Events,
          meta: { requiresAuth: true, perm: permFor('events') },
        },
        {
          path: 'delevirycaptive',
          component: DeleviryCaptive,
          meta: { requiresAuth: true, perm: permFor('delevirycaptive') },
        },
        {
          path: 'informations',
          component: Informations,
          meta: { requiresAuth: true, perm: permFor('informations') },
        },

        // Inline info
        {
          path: 'inlineinfo',
          component: Inlineinfoindex,
          meta: { requiresAuth: true, perm: permFor('inlineinfo') },
        },
        {
          path: 'resourceinfo',
          component: ResourceInfo,
          meta: { requiresAuth: true, perm: permFor('resourceinfo') },
        },
        {
          path: 'resourcetab',
          component: Resourcetab,
          meta: { requiresAuth: true, perm: permFor('resourcetab') },
        },
        {
          path: 'eventtabl',
          component: Eventtab,
          meta: { requiresAuth: true, perm: permFor('eventtabl') },
        },

        // 56 Formats hub
        {
          path: 'fifthyformatsroute',
          component: FifthyFormatsRoute,
          meta: { requiresAuth: true, perm: permFor('fifthyformatsroute') },
        },

        // 56 Formats — base
        {
          path: 'fasads',
          component: Fasads,
          meta: { requiresAuth: true, perm: permFor('fasads') },
        },
        {
          path: 'saeenfasad',
          component: SaeenFasad,
          meta: { requiresAuth: true, perm: permFor('saeenfasad') },
        },
        { path: 'heist', component: Heist, meta: { requiresAuth: true, perm: permFor('heist') } },
        {
          path: 'captives',
          component: Captives,
          meta: { requiresAuth: true, perm: permFor('captives') },
        },
        {
          path: 'mulkanos',
          component: Mulkanos,
          meta: { requiresAuth: true, perm: permFor('mulkanos') },
        },
        {
          path: 'entry-exits',
          component: EntryExits,
          meta: { requiresAuth: true, perm: permFor('entry-exits') },
        },
        {
          path: 'sarafyanos',
          component: Sarafyanos,
          meta: { requiresAuth: true, perm: permFor('sarafyanos') },
        },
        { path: 'clubs', component: Clubs, meta: { requiresAuth: true, perm: permFor('clubs') } },
        {
          path: 'mahajerats',
          component: Mahajerats,
          meta: { requiresAuth: true, perm: permFor('mahajerats') },
        },
        {
          path: 'soldiers',
          component: Soldiers,
          meta: { requiresAuth: true, perm: permFor('soldiers') },
        },

        // 56 Formats — one
        {
          path: 'mawads',
          component: Mawads,
          meta: { requiresAuth: true, perm: permFor('mawads') },
        },
        {
          path: 'lostmujremeens',
          component: LostMujremeens,
          meta: { requiresAuth: true, perm: permFor('lostmujremeens') },
        },
        {
          path: 'oldsoldiershomes',
          component: OldSoldiersHomes,
          meta: { requiresAuth: true, perm: permFor('oldsoldiershomes') },
        },
        {
          path: 'robbers',
          component: Robbers,
          meta: { requiresAuth: true, perm: permFor('robbers') },
        },
        {
          path: 'nonresponsablepeople',
          component: NonResposablePeople,
          meta: { requiresAuth: true, perm: permFor('nonresponsablepeople') },
        },
        {
          path: 'mashkokhomes',
          component: MashkokHomes,
          meta: { requiresAuth: true, perm: permFor('mashkokhomes') },
        },
        {
          path: 'damageplaces',
          component: DamagePlacses,
          meta: { requiresAuth: true, perm: permFor('damageplaces') },
        },
        {
          path: 'damagerasanplaces',
          component: DamageRasanPlaces,
          meta: { requiresAuth: true, perm: permFor('damagerasanplaces') },
        },
        {
          path: 'mashkokshops',
          component: MashkokShops,
          meta: { requiresAuth: true, perm: permFor('mashkokshops') },
        },
        {
          path: 'luchakanos',
          component: Luchakanos,
          meta: { requiresAuth: true, perm: permFor('luchakanos') },
        },

        // 56 Formats — two
        {
          path: 'schools',
          component: Schools,
          meta: { requiresAuth: true, perm: permFor('schools') },
        },
        {
          path: 'hadobusstops',
          component: HadoBusStop,
          meta: { requiresAuth: true, perm: permFor('hadobusstops') },
        },
        {
          path: 'transportcompanies',
          component: TransportCompany,
          meta: { requiresAuth: true, perm: permFor('transportcompanies') },
        },
        {
          path: 'carsalescompanies',
          component: CarSalesCompany,
          meta: { requiresAuth: true, perm: permFor('carsalescompanies') },
        },
        {
          path: 'parkings',
          component: Parkings,
          meta: { requiresAuth: true, perm: permFor('parkings') },
        },
        { path: 'banks', component: Banks, meta: { requiresAuth: true, perm: permFor('banks') } },
        {
          path: 'apartments',
          component: Apartments,
          meta: { requiresAuth: true, perm: permFor('apartments') },
        },
        {
          path: 'rahnomamamlats',
          component: RahnomaMamlats,
          meta: { requiresAuth: true, perm: permFor('rahnomamamlats') },
        },
        {
          path: 'hosppitals',
          component: Hosppitals,
          meta: { requiresAuth: true, perm: permFor('hosppitals') },
        },
        {
          path: 'workshops',
          component: WorkShops,
          meta: { requiresAuth: true, perm: permFor('workshops') },
        },

        // 56 Formats — three
        {
          path: 'badmashanos',
          component: Badmashanos,
          meta: { requiresAuth: true, perm: permFor('badmashanos') },
        },
        {
          path: 'sarayanos',
          component: Sarayanos,
          meta: { requiresAuth: true, perm: permFor('sarayanos') },
        },
        {
          path: 'hotels',
          component: Hotels,
          meta: { requiresAuth: true, perm: permFor('hotels') },
        },
        {
          path: 'hostels',
          component: Hostels,
          meta: { requiresAuth: true, perm: permFor('hostels') },
        },
        {
          path: 'tujarans',
          component: Tujarans,
          meta: { requiresAuth: true, perm: permFor('tujarans') },
        },
        {
          path: 'mujahedinos',
          component: Mujahedinos,
          meta: { requiresAuth: true, perm: permFor('mujahedinos') },
        },
        {
          path: 'emaratoffices',
          component: EmaratOffices,
          meta: { requiresAuth: true, perm: permFor('emaratoffices') },
        },
        {
          path: 'oldjahadis',
          component: OldJahadis,
          meta: { requiresAuth: true, perm: permFor('oldjahadis') },
        },
        {
          path: 'mashkokpeoples',
          component: MashkokPeopls,
          meta: { requiresAuth: true, perm: permFor('mashkokpeoples') },
        },
        {
          path: 'madrasas',
          component: Madrasa,
          meta: { requiresAuth: true, perm: permFor('madrasas') },
        },

        // 56 Formats — four
        {
          path: 'wakilanos',
          component: Wakilanos,
          meta: { requiresAuth: true, perm: permFor('wakilanos') },
        },
        {
          path: 'universities',
          component: Universities,
          meta: { requiresAuth: true, perm: permFor('universities') },
        },
        {
          path: 'masjids',
          component: Masjids,
          meta: { requiresAuth: true, perm: permFor('masjids') },
        },
        {
          path: 'highways',
          component: HighWays,
          meta: { requiresAuth: true, perm: permFor('highways') },
        },
        {
          path: 'companies',
          component: Companies,
          meta: { requiresAuth: true, perm: permFor('companies') },
        },
        {
          path: 'donelostplaces',
          component: DoneLostPlaces,
          meta: { requiresAuth: true, perm: permFor('donelostplaces') },
        },
        {
          path: 'econemyprojects',
          component: EconemyProjects,
          meta: { requiresAuth: true, perm: permFor('econemyprojects') },
        },
        {
          path: 'criminalcaptureplaces',
          component: CriminalCapturePlaces,
          meta: { requiresAuth: true, perm: permFor('criminalcaptureplaces') },
        },
        { path: 'pumps', component: Pumps, meta: { requiresAuth: true, perm: permFor('pumps') } },
        {
          path: 'shoras',
          component: Shoras,
          meta: { requiresAuth: true, perm: permFor('shoras') },
        },
        {
          path: 'checkpoints',
          component: CheckPoints,
          meta: { requiresAuth: true, perm: permFor('checkpoints') },
        },
        {
          path: 'governmentplaces',
          component: GovernmentPlaces,
          meta: { requiresAuth: true, perm: permFor('governmentplaces') },
        },
        {
          path: 'supermarkets',
          component: SuperMarkets,
          meta: { requiresAuth: true, perm: permFor('supermarkets') },
        },
        { path: 'parks', component: Parks, meta: { requiresAuth: true, perm: permFor('parks') } },
        {
          path: 'complexes',
          component: Complexes,
          meta: { requiresAuth: true, perm: permFor('complexes') },
        },
        {
          path: 'deplomaticplaces',
          component: DeplomaticPlaces,
          meta: { requiresAuth: true, perm: permFor('deplomaticplaces') },
        },

        // Finance
        {
          path: 'finincalindex',
          component: finincalIndex,
          meta: { requiresAuth: true, perm: permFor('finincalindex') },
        },
        {
          path: 'fiftyformatreports',
          component: FiftyFormatsReportPage,
        },

        // Map
        {
          path: 'map-afg',
          name: 'MapAfg',
          component: MapAfghanistan,
          meta: { requiresAuth: true, perm: permFor('map-afg') },
        },
        {
          path: 'offlinemap',
          component: offlinemap,
          meta: { requiresAuth: true, perm: permFor('offlinemap') },
        },

        // RBAC pages
        { path: 'roles', component: Roles, meta: { requiresAuth: true, perm: permFor('roles') } },
        // {
        //   path: 'permissions',
        //   component: Permission,
        //   meta: { requiresAuth: true, perm: permFor('permissions') },
        // },

        // No-access page
        { path: 'no-access', component: NoAccess, meta: { requiresAuth: true } },
      ],
    },

    // 404 fallback
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
});

// --------------- Global Auth + Permission Guard ---------------
router.beforeEach(async (to, from, next) => {
  const token = sessionStorage.getItem('token');
  const requiresAuth = to.matched.some((r) => r.meta?.requiresAuth);
  const isGuestOnly = to.matched.some((r) => r.meta?.guest);

  if (requiresAuth && !token) {
    return next('/'); // not logged in
  }
  if (isGuestOnly && token) {
    // already logged in → go to first accessible page
    const roles = JSON.parse(sessionStorage.getItem('roles') || '[]');
    const permissions = JSON.parse(sessionStorage.getItem('permissions') || '[]');
    const target = firstAccessiblePath({ roles, permissions });
    return next(target || '/app/no-access');
  }

  // Permission check for protected routes
  const requiredPerm = to.meta?.perm;
  if (requiresAuth && requiredPerm) {
    const roles = JSON.parse(sessionStorage.getItem('roles') || '[]');
    const permissions = JSON.parse(sessionStorage.getItem('permissions') || '[]');

    // inline canAccess to avoid circular import in some setups
    const hasWildcard = roles.includes('admin') || permissions.includes('*');
    const allowed = hasWildcard || permissions.includes(requiredPerm);

    if (!allowed) {
      const target = firstAccessiblePath({ roles, permissions });
      return next(target || '/app/no-access');
    }
  }

  next();
});

export default router;
