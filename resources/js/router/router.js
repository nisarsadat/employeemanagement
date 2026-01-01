// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';

// ---------- Public ----------
import Login from '@/pages/Login.vue';

// ---------- Protected Shell (layout after login) ----------
import Home from '@/pages/Home.vue';

// ---------- Protected Child Pages (render inside Home.vue <router-view/>) ----------
import Dashboard from '@/pages/Dashboard/Dashboard.vue';
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
import OfficeEmployeesActivityReport from '../pages/reports/OfficeEmployeesActivityReport.vue';
import OfficeEmployeeRelationPage from '../pages/reports/OfficeEmployeeRelationPage.vue';
import EventReport from '../pages/reports/EventActivityReport.vue';
import InformationActivityReport from '../pages/reports/InformationActivityReport.vue';
import InformationsForm from '../pages/Informations/InformationsForm.vue';
import OfficeEmployeeForm from '../pages/OfficeEmployee/OfficeEmployeeForm.vue';
import ResourcesForm from '../pages/Resources/ResourcesForm.vue';
import EventsForm from '../pages/Events/EventsForm.vue';
import DeleviryCaptiveForm from '../pages/DeleviryCaptive/DeleviryCaptiveForm.vue';
import CaptiveForm from '../pages/FiftyFormats/CaptiveForm.vue';
import HeistForm from '../pages/FiftyFormats/HeistForm.vue';
import FasadForm from '../pages/FiftyFormats/FasadForm.vue';
import SaeenFasadForm from '../pages/FiftyFormats/SaeenFasadForm.vue';
import EntryExitForm from '../pages/FiftyFormats/EntryExitForm.vue';
import SarafyanoForm from '../pages/FiftyFormats/SarafyanoForm.vue';
import MahajeratForm from '../pages/FiftyFormats/MahajeratForm.vue';
import ClubForm from '../pages/FiftyFormats/ClubForm.vue';
import SoldierForm from '../pages/FiftyFormats/SoldierForm.vue';
import MawadForm from '../pages/FiftyFormats/MawadForm.vue';
import MulkanoForm from '../pages/FiftyFormats/MulkanoForm.vue';
import LostMujremeenForm from '../pages/FiftyFormats/LostMujremeenForm.vue';
import OldSoldiersHomeForm from '../pages/FiftyFormats/OldSoldiersHomeForm.vue';
import RobberForm from '../pages/FiftyFormats/RobberForm.vue';
import NonResponsablePeopleForm from '../pages/FiftyFormats/NonResponsablePeopleForm.vue';
import MashkokHomeForm from '../pages/FiftyFormats/MashkokHomeForm.vue';
import DamagePlaceForm from '../pages/FiftyFormats/DamagePlaceForm.vue';
import DamageRasanPlaceForm from '../pages/FiftyFormats/DamageRasanPlaceForm.vue';
import MashkokShopForm from '../pages/FiftyFormats/MashkokShopForm.vue';
import LuchakanoForm from '../pages/FiftyFormats/LuchakanoForm.vue';
import SchoolForm from '../pages/FiftyFormats/SchoolForm.vue';
import HadaBusStopForm from '../pages/FiftyFormats/HadaBusStopForm.vue';
import TransportCompanyForm from '../pages/FiftyFormats/TransportCompanyForm.vue';
import ParkingForm from '../pages/FiftyFormats/ParkingForm.vue';
import BankForm from '../pages/FiftyFormats/BankForm.vue';
import CarSalesCompanyForm from '../pages/FiftyFormats/CarSalesCompanyForm.vue';
import ApartmentForm from '../pages/FiftyFormats/ApartmentForm.vue';
import RahnomaMamlatForm from '../pages/FiftyFormats/RahnomaMamlatForm.vue';
import HospitalForm from '../pages/FiftyFormats/HospitalForm.vue';
import WorkShopForm from '../pages/FiftyFormats/WorkShopForm.vue';
import BadmashanoForm from '../pages/FiftyFormats/BadmashanoForm.vue';
import SarayanoForm from '../pages/FiftyFormats/SarayanoForm.vue';
import HostelForm from '../pages/FiftyFormats/HostelForm.vue';
import EmaratOfficeForm from '../pages/FiftyFormats/EmaratOfficeForm.vue';
import HotelForm from '../pages/FiftyFormats/HotelForm.vue';
import MadrasaForm from '../pages/FiftyFormats/MadrasaForm.vue';
import MashkokPeopleForm from '../pages/FiftyFormats/MashkokPeopleForm.vue';
import MujahedinoForm from '../pages/FiftyFormats/MujahedinoForm.vue';
import OldJahadisForm from '../pages/FiftyFormats/OldJahadisForm.vue';
import TujaransForm from '../pages/FiftyFormats/TujaransForm.vue';
import MasjidForm from '../pages/FiftyFormats/MasjidForm.vue';
import CheckpointForm from '../pages/FiftyFormats/CheckpointForm.vue';
import HighwayForm from '../pages/FiftyFormats/HighwayForm.vue';
import CompanyForm from '../pages/FiftyFormats/CompanyForm.vue';
import DoneLostPlaceForm from '../pages/FiftyFormats/DoneLostPlaceForm.vue';
import CriminalCapturePlaceForm from '../pages/FiftyFormats/CriminalCapturePlaceForm.vue';
import EconemyProjectForm from '../pages/FiftyFormats/EconemyProjectForm.vue';
import PumpForm from '../pages/FiftyFormats/PumpForm.vue';
import GovernmentPlaceForm from '../pages/FiftyFormats/GovernmentPlaceForm.vue';
import SuperMarketForm from '../pages/FiftyFormats/SuperMarketForm.vue';
import ShoraForm from '../pages/FiftyFormats/ShoraForm.vue';
import ParkForm from '../pages/FiftyFormats/ParkForm.vue';
import ComplexForm from '../pages/FiftyFormats/ComplexForm.vue';
import DeplomaticPlaceForm from '../pages/FiftyFormats/DeplomaticPlaceForm.vue';
import UniversityForm from '../pages/FiftyFormats/UniversityForm.vue';
import WakilanoForm from '../pages/FiftyFormats/WakilanoForm.vue';
import InformationPrintDialog from '../pages/Informations/InformationPrintDialog.vue';
import NotificationsPage from '../components/NotificationsPage.vue';
import ActiveEmployeesIndex from '../pages/ActiveInactive/ActiveEmployeesIndex.vue';
import InactiveEmployeesIndex from '../pages/ActiveInactive/InactiveEmployeesIndex.vue';
import ActiveResource from '../pages/ActiveInactiveResource/ActiveResource.vue';
import InactiveResource from '../pages/ActiveInactiveResource/InactiveResource.vue';
import DailyFoulReport from '../pages/reports/DailyFoulReport.vue';
import MonthlyFoulReport from '../pages/reports/MonthlyFoulReport.vue';
import YearlyFoulReport from '../pages/reports/YearlyFoulReport.vue';
import WeeklyFoulReports from '../pages/reports/WeeklyFoulReports.vue';
import FoulQuarterlyReports from '../pages/reports/FoulQuarterlyReports.vue';
import AllTimeFoulReports from '../pages/reports/AllTimeFoulReports.vue';
import OutgoingIncomingForm from '../pages/OutgoingIncoming/OutgoingIncomingForm.vue';
import OutgoingIncomingIndex from '../pages/OutgoingIncoming/OutgoingIncomingIndex.vue';
import RolePermissions from '../pages/rbac/RolePermissions.vue';
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
        {
          path: 'notifications', // full path: /app/notifications
          name: 'notifications', // matches router.push({ name: 'notifications' })
          component: NotificationsPage,
          meta: {
            requiresAuth: true,
            title: 'Notifications',
          },
        },
        // Core (each with meta.perm)
        {
          path: 'dashboards',
          name: 'Dashboard',
          component: Dashboard,
          meta: { requiresAuth: true, perm: permFor('dashboards') },
        },
        {
          path: 'activeemployees',
          component: ActiveEmployeesIndex,
          meta: { requiresAuth: true, perm: permFor('activeandinactiveemployees') },
        },
        {
          path: 'inactiveemployees',
          component: InactiveEmployeesIndex,
          meta: { requiresAuth: true, perm: permFor('activeandinactiveemployees') },
        },
        // Users
        { path: 'users', component: User, meta: { requiresAuth: true, perm: permFor('users') } },

        // People & resources
        {
          path: 'officeemployee',
          component: OfficeEmployeesPage,
          meta: { requiresAuth: true, perm: permFor('officeemployee') },
          name: 'officeemployee',
        },
        {
          path: 'officeemployeeform/:id?',
          name: 'officeemployeeform',
          component: OfficeEmployeeForm,
          meta: { requiresAuth: true, perm: permFor('officeemployee') },
        },

        {
          path: 'resources',
          component: Resources,
          meta: { requiresAuth: true, perm: permFor('resources') },
        },
        {
          path: '/app/activeresource',
          name: 'ActiveResource',
          component: ActiveResource,
        },
        {
          path: '/app/inactiveresource',
          name: 'InactiveResource',
          component: InactiveResource,
        },
        {
          path: 'resourcesform',
          name: 'resources.create',
          component: ResourcesForm,
          props: true,
        },

        // 🔹 RESOURCES EDIT
        {
          path: 'resourcesform/:id',
          name: 'resources.edit',
          component: ResourcesForm,
          props: true,
        },
        {
          path: 'events',
          component: Events,
          meta: { requiresAuth: true, perm: permFor('events') },
        },
        {
          path: '/app/eventsform',
          name: 'EventsCreate',
          component: EventsForm,
        },
        {
          path: '/app/eventsform/:id',
          name: 'EventsEdit',
          component: EventsForm,
          props: true,
        },
        {
          path: 'delevirycaptive',
          component: DeleviryCaptive,
          meta: { requiresAuth: true, perm: permFor('delevirycaptive') },
        },
        {
          path: '/app/delevirycaptiveform/:id?',
          name: 'DeleviryCaptiveForm',
          component: DeleviryCaptiveForm,
          props: true,
        },
        {
          path: 'informations',
          component: Informations,
          meta: { requiresAuth: true, perm: permFor('informations') },
        },
        {
          path: 'informationform',
          name: 'informationform.create',
          component: InformationsForm,
          meta: { requiresAuth: true, perm: permFor('informations') },
        },
        {
          path: '/app/informations/print/:id',
          name: 'InformationPrint',
          component: InformationPrintDialog,
        },
        {
          path: 'informationform/:id/:relation?',
          name: 'informationform.edit',
          component: InformationsForm,
          props: true, // so you get `id` + `relation` as props
          meta: { requiresAuth: true, perm: permFor('informations') },
        },
        // reports
        {
          path: 'dailyfoulreports',
          name: 'dailyfoulreports',
          component: DailyFoulReport,
          // meta: { requiresAuth: true, perm: permFor('informations') },
        },
        {
          path: 'monthlyfoulreports',
          name: 'monthlyfoulreports',
          component: MonthlyFoulReport,
          // meta: { requiresAuth: true, perm: permFor('informations') },
        },
        {
          path: 'yearlyfoulreports',
          name: 'yearlyfoulreports',
          component: YearlyFoulReport,
          // meta: { requiresAuth: true, perm: permFor('informations') },
        },
        {
          path: 'weeklyfoulreports',
          name: 'weeklyfoulreports',
          component: WeeklyFoulReports,
          // meta: { requiresAuth: true, perm: permFor('informations') },
        },
        {
          path: 'quarterlyfoulreports',
          name: 'quarterlyfoulreports',
          component: FoulQuarterlyReports,
          // meta: { requiresAuth: true, perm: permFor('informations') },
        },
        {
          path: 'alltimefoulreports',
          name: 'alltimefoulreports',
          component: AllTimeFoulReports,
          // meta: { requiresAuth: true, perm: permFor('informations') },
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
        // outgoing incomming
        {
          path: 'outgoingincommings',
          component: OutgoingIncomingIndex,
          // meta: { requiresAuth: true, perm: permFor('outgoingincomming') },
        },
        {
          path: '/app/outgoingincomingform',
          name: 'OutgoingIncomingCreate',
          component: OutgoingIncomingForm,

          // meta: {
          //   requiresAuth: true,
          //   title: 'Create Outgoing / Incoming',
          // },
        },

        {
          path: '/app/outgoingincomingform/:id',
          name: 'OutgoingIncomingEdit',
          component: OutgoingIncomingForm,

          // meta: {
          //   requiresAuth: true,
          //   title: 'Edit Outgoing / Incoming',
          // },
          props: true,
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
          path: '/app/fasadform',
          name: 'fasads.create',
          component: FasadForm,
          meta: { requiresAuth: true },
        },
        {
          path: '/app/fasadform/:id',
          name: 'fasads.edit',
          props: true,
          component: FasadForm,
          meta: { requiresAuth: true },
        },
        {
          path: 'saeenfasad',
          component: SaeenFasad,
          meta: { requiresAuth: true, perm: permFor('saeenfasad') },
        },
        {
          path: '/app/saeen-fasadform',
          name: 'saeenFasads.create',
          component: SaeenFasadForm,
          meta: { requiresAuth: true },
        },
        {
          path: '/app/saeen-fasadform/:id',
          name: 'saeenFasads.edit',
          props: true,
          component: SaeenFasadForm,

          meta: { requiresAuth: true },
        },
        { path: 'heist', component: Heist, meta: { requiresAuth: true, perm: permFor('heist') } },
        // 👇 Create new heist
        {
          path: '/app/heistform',
          name: 'HeistsCreate',
          component: HeistForm,
        },

        // 👇 Edit heist
        {
          path: '/app/heistform/:id',
          name: 'HeistsEdit',
          props: true,
          component: HeistForm,
        },
        {
          path: 'captives',
          component: Captives,
          meta: { requiresAuth: true, perm: permFor('captives') },
        },
        {
          path: '/app/captiveform',
          name: 'CaptiveCreate',
          component: CaptiveForm,
        },
        {
          path: '/app/captiveform/:id',
          name: 'CaptiveEdit',
          component: CaptiveForm,
          props: true,
        },
        {
          path: '/app/resources/:code',
          name: 'ResourceDetailsByCode',
          component: Resources,
          props: true,
        },

        {
          path: 'mulkanos',
          component: Mulkanos,
          meta: { requiresAuth: true, perm: permFor('mulkanos') },
        },
        {
          path: '/app/mulkanoform',
          name: 'mulkano-create',
          component: MulkanoForm,
        },
        {
          path: '/app/mulkanoform/:id',
          name: 'mulkano-edit',
          props: true,
          component: MulkanoForm,
        },
        {
          path: 'entry-exits',
          component: EntryExits,
          meta: { requiresAuth: true, perm: permFor('entry-exits') },
        },
        {
          path: 'entryexitform',
          name: 'EntryExitCreate',
          component: EntryExitForm,
        },

        // ✅ ENTRY/EXIT EDIT
        {
          path: 'entryexitform/:id',
          name: 'EntryExitEdit',
          component: EntryExitForm,

          props: true,
        },

        {
          path: 'sarafyanos',
          component: Sarafyanos,
          meta: { requiresAuth: true, perm: permFor('sarafyanos') },
        },
        {
          path: '/app/sarafyanosform',
          name: 'SarafyanoCreate',
          component: SarafyanoForm,
        },
        {
          path: '/app/sarafyanosform/:id',
          name: 'SarafyanoEdit',
          component: SarafyanoForm,
        },
        { path: 'clubs', component: Clubs, meta: { requiresAuth: true, perm: permFor('clubs') } },
        {
          path: '/app/clubform',
          name: 'ClubCreate',
          component: ClubForm,
        },
        {
          path: '/app/clubform/:id',
          name: 'ClubEdit',
          props: true,
          component: ClubForm,
        },
        {
          path: 'mahajerats',
          component: Mahajerats,
          meta: { requiresAuth: true, perm: permFor('mahajerats') },
        },
        {
          path: '/app/mahajeratform',
          name: 'MahajeratsCreate',
          component: MahajeratForm,
        },
        {
          path: '/app/mahajeratform/:id',
          name: 'MahajeratsEdit',
          props: true,
          component: MahajeratForm,
        },
        {
          path: 'soldiers',
          component: Soldiers,
          meta: { requiresAuth: true, perm: permFor('soldiers') },
        },
        // e.g. in src/router/index.js or wherever your routes are defined

        {
          path: '/app/soldiers',
          name: 'SoldiersIndex',
          component: SoldierForm,
        },
        {
          path: '/app/soldierform/:id?',
          name: 'SoldierForm',
          component: SoldierForm,
        },

        // 56 Formats — one
        {
          path: 'mawads',
          component: Mawads,
          meta: { requiresAuth: true, perm: permFor('mawads') },
        },
        {
          path: '/app/mawads',
          name: 'MawadsIndex',
          component: MawadForm,
        },
        {
          path: '/app/mawadform/:id?',
          name: 'MawadForm',
          component: MawadForm,
        },

        {
          path: 'lostmujremeens',
          component: LostMujremeens,
          meta: { requiresAuth: true, perm: permFor('lostmujremeens') },
        },
        {
          path: '/app/lost-mujremeen-form',
          name: 'lost.mujremeens.create',
          component: LostMujremeenForm,
        },
        {
          path: '/app/lost-mujremeen-form/:id',
          name: 'lost.mujremeens.edit',
          props: true,
          component: LostMujremeenForm,
        },
        {
          path: 'oldsoldiershomes',
          component: OldSoldiersHomes,
          meta: { requiresAuth: true, perm: permFor('oldsoldiershomes') },
        },
        {
          path: '/app/old-soldiers-home-form',
          name: 'OldSoldiersHomeCreate',
          component: OldSoldiersHomeForm,
        },

        {
          path: '/app/old-soldiers-home-form/:id',
          name: 'OldSoldiersHomeEdit',
          props: true,
          component: OldSoldiersHomeForm,
        },
        {
          path: 'robbers',
          component: Robbers,
          meta: { requiresAuth: true, perm: permFor('robbers') },
        },
        {
          path: '/app/robbers/create',
          name: 'RobbersCreate',
          component: RobberForm,
        },
        {
          path: '/app/robbers/:id',
          name: 'RobbersEdit',
          component: RobberForm,
          props: true,
        },
        {
          path: 'nonresponsablepeople',
          component: NonResposablePeople,
          meta: { requiresAuth: true, perm: permFor('nonresponsablepeople') },
        },
        {
          path: '/app/non-responsable-people-form',
          name: 'NonResponsablePeopleCreate',
          component: NonResponsablePeopleForm,
        },
        {
          path: '/app/non-responsable-people-form/:id',
          name: 'NonResponsablePeopleEdit',
          props: true,
          component: NonResponsablePeopleForm,
        },
        {
          path: 'mashkokhomes',
          component: MashkokHomes,
          meta: { requiresAuth: true, perm: permFor('mashkokhomes') },
        },
        {
          path: '/app/mashkok-home',
          name: 'MashkokHomeCreate',
          component: MashkokHomeForm,
        },
        {
          path: '/app/mashkok-home/:id',
          name: 'MashkokHomeEdit',
          props: true,
          component: MashkokHomeForm,
        },
        {
          path: 'damageplaces',
          component: DamagePlacses,
          meta: { requiresAuth: true, perm: permFor('damageplaces') },
        },
        // DamagePlaces (فورم ۵۰: نقاط آسیب)
        {
          path: '/app/damage-places',
          name: 'DamagePlacesIndex',
          component: DamagePlaceForm,
        },

        {
          path: '/app/damage-places/form/:id?',
          name: 'DamagePlaceForm',
          component: DamagePlaceForm,
          props: true,
        },

        {
          path: 'damagerasanplaces',
          component: DamageRasanPlaces,
          meta: { requiresAuth: true, perm: permFor('damagerasanplaces') },
        },
        {
          path: '/app/damage-rasan-placeform',
          name: 'DamageRasanPlaceCreate',
          component: DamageRasanPlaceForm,
        },
        {
          path: '/app/damage-rasan-placeform/:id',
          name: 'DamageRasanPlaceEdit',
          props: true,
          component: DamageRasanPlaceForm,
        },
        {
          path: 'mashkokshops',
          component: MashkokShops,
          meta: { requiresAuth: true, perm: permFor('mashkokshops') },
        },
        {
          path: '/app/mashkok-shops',
          name: 'MashkokShopsIndex',
          component: MashkokShopForm,
        },

        {
          path: '/app/mashkok-shops/form/:id?',
          name: 'MashkokShopForm',
          props: true,
          component: MashkokShopForm,
        },

        {
          path: 'luchakanos',
          component: Luchakanos,
          meta: { requiresAuth: true, perm: permFor('luchakanos') },
        },
        {
          path: '/app/luchakanoform',
          name: 'LuchakanoCreate',
          component: LuchakanoForm,
        },
        {
          path: '/app/luchakanoform/:id',
          name: 'LuchakanoEdit',
          component: LuchakanoForm,
        },

        // 56 Formats — two
        {
          path: 'schools',
          component: Schools,
          meta: { requiresAuth: true, perm: permFor('schools') },
        },
        {
          path: '/app/schools',
          name: 'SchoolsIndex',
          component: SchoolForm,
        },

        {
          path: '/app/schools/form/:id?',
          name: 'SchoolForm',
          props: true,
          component: SchoolForm,
        },

        {
          path: 'hadobusstops',
          component: HadoBusStop,
          meta: { requiresAuth: true, perm: permFor('hadobusstops') },
        },
        {
          path: '/app/hadabusstopform',
          name: 'HadaBusStopCreate',
          component: HadaBusStopForm,
        },
        {
          path: '/app/hadabusstopform/:id',
          name: 'HadaBusStopEdit',
          props: true,
          component: HadaBusStopForm,
        },
        {
          path: 'transportcompanies',
          component: TransportCompany,
          meta: { requiresAuth: true, perm: permFor('transportcompanies') },
        },
        {
          path: '/app/transport-companies/new',
          name: 'TransportCompanyCreate',
          component: TransportCompanyForm,
        },

        {
          path: '/app/transport-companies/:id/edit',
          name: 'TransportCompanyEdit',
          props: true,
          component: TransportCompanyForm,
        },

        {
          path: 'carsalescompanies',
          component: CarSalesCompany,
          name: 'CarSalesCompaniesIndex',
          meta: { requiresAuth: true, perm: permFor('carsalescompanies') },
        },
        {
          path: '/app/car-sales-companies/new',
          name: 'CarSalesCompanyCreate',
          component: CarSalesCompanyForm,
          meta: { requiresAuth: true },
        },
        {
          path: '/app/car-sales-companies/:id',
          name: 'CarSalesCompanyEdit',
          component: CarSalesCompanyForm,
          props: true,
          meta: { requiresAuth: true },
        },
        {
          path: 'parkings',
          component: Parkings,
          meta: { requiresAuth: true, perm: permFor('parkings') },
        },
        {
          path: '/app/parkingform',
          name: 'ParkingCreate',
          component: ParkingForm,
        },

        {
          path: '/app/parkingform/:id',
          name: 'ParkingEdit',
          component: ParkingForm,
          props: true,
        },
        { path: 'banks', component: Banks, meta: { requiresAuth: true, perm: permFor('banks') } },
        {
          path: '/app/bankform',
          name: 'BankCreate',
          component: BankForm,
        },

        {
          path: '/app/bankform/:id',
          name: 'BankEdit',
          props: true,
          component: BankForm,
        },
        {
          path: 'apartments',
          component: Apartments,
          meta: { requiresAuth: true, perm: permFor('apartments') },
        },
        {
          path: '/app/apartmentform',
          name: 'ApartmentCreate',
          component: ApartmentForm,
        },
        {
          path: '/app/apartmentform/:id',
          name: 'ApartmentEdit',
          component: ApartmentForm,
          props: true,
        },
        {
          path: 'rahnomamamlats',
          component: RahnomaMamlats,
          meta: { requiresAuth: true, perm: permFor('rahnomamamlats') },
        },
        {
          path: '/app/rahnoma-mamlats/create',
          name: 'RahnomaMamlatCreate',
          component: RahnomaMamlatForm,
        },
        {
          path: '/app/rahnoma-mamlats/:id',
          name: 'RahnomaMamlatEdit',
          props: true,
          component: RahnomaMamlatForm,
        },
        {
          path: 'hosppitals',
          component: Hosppitals,
          meta: { requiresAuth: true, perm: permFor('hosppitals') },
        },
        {
          path: '/app/hospitalform',
          name: 'HospitalCreate',
          component: HospitalForm,
        },
        {
          path: '/app/hospitalform/:id',
          name: 'HospitalEdit',
          props: true,
          component: HospitalForm,
        },
        {
          path: 'workshops',
          component: WorkShops,
          meta: { requiresAuth: true, perm: permFor('workshops') },
        },
        {
          path: '/app/workshops/create',
          name: 'WorkShopCreate',
          component: WorkShopForm,
        },
        {
          path: '/app/workshops/:id',
          name: 'WorkShopEdit',
          props: true,
          component: WorkShopForm,
        },

        // 56 Formats — three
        {
          path: 'badmashanos',
          component: Badmashanos,
          meta: { requiresAuth: true, perm: permFor('badmashanos') },
        },
        {
          path: '/app/badmashanoform',
          name: 'BadmashanoCreate',
          component: BadmashanoForm,
        },
        {
          path: '/app/badmashanoform/:id',
          name: 'BadmashanoEdit',
          props: true,
          component: BadmashanoForm,
        },
        {
          path: 'sarayanos',
          component: Sarayanos,
          meta: { requiresAuth: true, perm: permFor('sarayanos') },
        },
        {
          path: '/app/sarayanoform',
          name: 'SarayanoCreate',
          meta: { requiresAuth: true },
          component: SarayanoForm,
        },
        {
          path: '/app/sarayanoform/:id',
          name: 'SarayanoEdit',
          meta: { requiresAuth: true },
          props: true,
          component: SarayanoForm,
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
          path: '/app/hotelform',
          component: HotelForm,
        },
        {
          path: '/app/hotelform/:id',
          component: HotelForm,
          props: true,
        },
        {
          path: '/app/hostelform',
          name: 'HostelCreate',
          component: HostelForm,
        },

        {
          path: '/app/hostelform/:id',
          name: 'HostelEdit',
          component: HostelForm,
          props: true,
        },
        {
          path: 'tujarans',
          component: Tujarans,
          meta: { requiresAuth: true, perm: permFor('tujarans') },
        },
        {
          path: '/app/tujarans',
          name: 'TujaransIndex',
          component: TujaransForm,
        },
        {
          path: '/app/tujaransform/:id?',
          name: 'TujaransForm',
          component: TujaransForm,
          props: true,
        },

        {
          path: 'mujahedinos',
          component: Mujahedinos,
          meta: { requiresAuth: true, perm: permFor('mujahedinos') },
        },
        // src/router/index.js (or wherever your routes live)
        {
          path: '/app/mujahedino',
          name: 'MujahedinoIndex',
          component: MujahedinoForm,
        },
        {
          path: '/app/mujahedinoform/:id?',
          name: 'MujahedinoForm',
          component: MujahedinoForm,
          props: true,
        },

        {
          path: 'emaratoffices',
          component: EmaratOffices,
          meta: { requiresAuth: true, perm: permFor('emaratoffices') },
        },
        {
          path: '/app/emarat-offices/form',
          name: 'EmaratOfficeCreate',
          component: EmaratOfficeForm,
        },
        {
          path: '/app/emarat-offices/form/:id',
          name: 'EmaratOfficeEdit',
          component: EmaratOfficeForm,
          props: true,
        },
        {
          path: 'oldjahadis',
          component: OldJahadis,
          meta: { requiresAuth: true, perm: permFor('oldjahadis') },
        },
        {
          path: '/app/oldjahadis',
          name: 'OldJahadisIndex',
          component: OldJahadisForm,
        },
        {
          path: '/app/oldjahadisform/:id?',
          name: 'OldJahadisForm',
          component: OldJahadisForm,
          props: true,
        },

        {
          path: 'mashkokpeoples',
          component: MashkokPeopls,
          meta: { requiresAuth: true, perm: permFor('mashkokpeoples') },
        },
        {
          path: '/app/mashkokpeopleform',
          name: 'MashkokPeopleCreate',
          component: MashkokPeopleForm,
        },
        {
          path: '/app/mashkokpeopleform/:id',
          name: 'MashkokPeopleEdit',
          component: MashkokPeopleForm,
          props: true,
        },
        {
          path: 'madrasas',
          component: Madrasa,
          meta: { requiresAuth: true, perm: permFor('madrasas') },
        },
        // ...
        {
          path: '/app/madrasas',
          name: 'MadrasasIndex',
          component: MadrasaForm,
        },
        {
          path: '/app/madrasas/form/:id?',
          name: 'MadrasaForm',
          component: MadrasaForm,
          props: true,
        },
        // ...

        // 56 Formats — four
        {
          path: 'wakilanos',
          component: Wakilanos,
          meta: { requiresAuth: true, perm: permFor('wakilanos') },
        },
        {
          path: '/app/wakilanatform',
          name: 'WakilanoCreate',
          component: WakilanoForm,
        },
        {
          path: '/app/wakilanatform/:id',
          name: 'WakilanoEdit',
          component: WakilanoForm,
          props: true,
        },
        {
          path: 'universities',
          component: Universities,
          meta: { requiresAuth: true, perm: permFor('universities') },
        },
        {
          path: '/app/universityform',
          name: 'universities.create',
          component: UniversityForm,
        },
        {
          path: '/app/universityform/:id',
          name: 'universities.edit',
          component: UniversityForm,
          props: true,
        },
        {
          path: 'masjids',
          component: Masjids,
          meta: { requiresAuth: true, perm: permFor('masjids') },
        },
        {
          path: '/app/masjid',
          name: 'MasjidIndex',
          component: MasjidForm,
        },
        {
          path: '/app/masjidform/:id?',
          name: 'MasjidForm',
          component: MasjidForm,
          props: true,
        },
        {
          path: 'highways',
          component: HighWays,
          meta: { requiresAuth: true, perm: permFor('highways') },
        },
        {
          path: '/app/highwayform',
          name: 'HighwayCreate',
          component: HighwayForm,
        },
        {
          path: '/app/highwayform/:id',
          name: 'HighwayEdit',
          component: HighwayForm,
          props: true,
        },
        {
          path: 'companies',
          component: Companies,
          meta: { requiresAuth: true, perm: permFor('companies') },
        },
        {
          path: '/app/companyform',
          name: 'CompanyCreate',
          component: CompanyForm,
        },
        {
          path: '/app/companyform/:id',
          name: 'CompanyEdit',
          component: CompanyForm,
          props: true,
        },
        {
          path: 'donelostplaces',
          component: DoneLostPlaces,
          meta: { requiresAuth: true, perm: permFor('donelostplaces') },
        },
        {
          path: '/app/done-lost-places/form',
          name: 'DoneLostPlaceCreate',
          component: DoneLostPlaceForm,
        },
        {
          path: '/app/done-lost-places/form/:id',
          name: 'DoneLostPlaceEdit',
          component: DoneLostPlaceForm,
          props: true,
        },
        {
          path: 'econemyprojects',
          component: EconemyProjects,
          meta: { requiresAuth: true, perm: permFor('econemyprojects') },
        },
        {
          path: '/app/econemyprojectform',
          name: 'EconemyProjectCreate',
          component: EconemyProjectForm,
        },
        {
          path: '/app/econemyprojectform/:id',
          name: 'EconemyProjectEdit',
          component: EconemyProjectForm,
          props: true,
        },
        {
          path: 'criminalcaptureplaces',
          component: CriminalCapturePlaces,
          meta: { requiresAuth: true, perm: permFor('criminalcaptureplaces') },
        },
        {
          path: '/app/criminal-capture-place-form',
          name: 'CriminalCapturePlaceCreate',
          component: CriminalCapturePlaceForm,
        },
        {
          path: '/app/criminal-capture-place-form/:id',
          name: 'CriminalCapturePlaceEdit',
          component: CriminalCapturePlaceForm,
          props: true,
        },
        { path: 'pumps', component: Pumps, meta: { requiresAuth: true, perm: permFor('pumps') } },
        {
          path: '/app/pumpform',
          name: 'PumpCreate',
          component: PumpForm,
        },
        {
          path: '/app/pumpform/:id',
          name: 'PumpEdit',
          component: PumpForm,
          props: true,
        },
        {
          path: 'shoras',
          component: Shoras,
          meta: { requiresAuth: true, perm: permFor('shoras') },
        },
        {
          path: '/app/shoraform',
          name: 'ShoraCreate',
          component: ShoraForm,
        },
        {
          path: '/app/shoraform/:id',
          name: 'ShoraEdit',
          component: ShoraForm,
          props: true,
        },
        {
          path: 'checkpoints',
          component: CheckPoints,
          meta: { requiresAuth: true, perm: permFor('checkpoints') },
        },
        {
          path: '/app/checkpointform',
          name: 'CheckpointCreate',
          component: CheckpointForm,
        },
        {
          path: '/app/checkpointform/:id',
          name: 'CheckpointEdit',
          component: CheckpointForm,
          props: true,
        },
        {
          path: 'governmentplaces',
          component: GovernmentPlaces,
          meta: { requiresAuth: true, perm: permFor('governmentplaces') },
        },
        {
          path: '/app/governmentplaceform',
          name: 'GovernmentPlaceCreate',
          component: GovernmentPlaceForm,
        },
        {
          path: '/app/governmentplaceform/:id',
          name: 'GovernmentPlaceEdit',
          component: GovernmentPlaceForm,
          props: true,
        },

        {
          path: 'supermarkets',
          component: SuperMarkets,
          meta: { requiresAuth: true, perm: permFor('supermarkets') },
        },
        {
          path: '/app/supermarketform',
          name: 'SuperMarketCreate',
          component: SuperMarketForm,
        },
        {
          path: '/app/supermarketform/:id',
          name: 'SuperMarketEdit',
          component: SuperMarketForm,
          props: true,
        },
        { path: 'parks', component: Parks, meta: { requiresAuth: true, perm: permFor('parks') } },
        {
          path: '/app/parkform',
          name: 'ParkCreate',
          component: ParkForm,
        },
        {
          path: '/app/parkform/:id',
          name: 'ParkEdit',
          component: ParkForm,
          props: true,
        },
        {
          path: 'complexes',
          component: Complexes,
          meta: { requiresAuth: true, perm: permFor('complexes') },
        },
        {
          path: '/app/complexform',
          name: 'ComplexCreate',
          component: ComplexForm,
        },
        {
          path: '/app/complexform/:id',
          name: 'ComplexEdit',
          component: ComplexForm,
          props: true,
        },
        {
          path: 'deplomaticplaces',
          component: DeplomaticPlaces,
          meta: { requiresAuth: true, perm: permFor('deplomaticplaces') },
        },
        {
          path: '/app/deplomaticplaceform',
          name: 'DeplomaticPlaceCreate',
          component: DeplomaticPlaceForm,
        },
        {
          path: '/app/deplomaticplaceform/:id',
          name: 'DeplomaticPlaceEdit',
          component: DeplomaticPlaceForm,
          props: true,
        },

        // Finance
        {
          path: 'finincalindex',
          component: finincalIndex,
          meta: { requiresAuth: true, perm: permFor('finincalindex') },
        },
        // reports
        {
          path: 'fiftyformatreports',
          component: FiftyFormatsReportPage,
          meta: { requiresAuth: true, perm: permFor('fiftyformatreports') },
        },
        {
          path: 'officeemployeereports',
          component: OfficeEmployeesActivityReport,
          meta: { requiresAuth: true, perm: permFor('officeemployeereports') },
        },
        {
          path: '/officeemployeerelations/:id/:relation',
          component: OfficeEmployeeRelationPage,
          name: 'office-employees-activity-report',
        },
        {
          path: 'eventreports',
          component: EventReport,
          meta: { requiresAuth: true, perm: permFor('eventreports') },
        },
        {
          path: 'informationreports',
          component: InformationActivityReport,
          meta: { requiresAuth: true, perm: permFor('informationreports') },
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
        {
          path: 'roles',
          name: 'roles',
          component: Roles,
          meta: { requiresAuth: true, perm: permFor('roles') },
        },
        // {
        //   path: 'permissions',
        //   component: Permission,
        //   meta: { requiresAuth: true, perm: permFor('permissions') },
        // },
        // router.js (snippet)
        {
          path: '/rbac/roles/:id/permissions',
          name: 'RolePermissions',
          component: RolePermissions,
          meta: { requiresAuth: true, perm: permFor('rolepermissions') },
        },

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
