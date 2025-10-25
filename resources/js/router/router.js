import { createRouter, createWebHistory } from 'vue-router';
import Home from '../pages/Home.vue';
import Login from '../pages/Login.vue';
import User from '../pages/User/User.vue';
import Register from '../pages/Register.vue';
import OfficeEmployeesPage from '../pages/OfficeEmployee/OfficeEmployeesPage.vue';
import ResedintEmployee from '../pages/ResedintsEmployee/ResedintEmployee.vue';
import ZoneEmployee from '../pages/ZoneEmployee/ZoneEmployee.vue';
import Resources from '../pages/Resources/Resources.vue';
import Events from '../pages/Events/Events.vue';
import DeleviryCaptive from '../pages/DeleviryCaptive/DeleviryCaptive.vue';
import Informations from '../pages/Informations/Informations.vue';
import FifthyFormatsRoute from '../components/FiftyFormatsRoute.vue';
import Fasads from '../pages/FiftyFormats/Fasads.vue';
import SaeenFasad from '../pages/FiftyFormats/SaeenFasad.vue';
import Heist from '../pages/FiftyFormats/Heist.vue';
import Captives from '../pages/FiftyFormats/Captives.vue';
import Mulkanos from '../pages/FiftyFormats/Mulkanos.vue';
import EntryExits from '../pages/FiftyFormats/EntryExit.vue';
import Sarafyanos from '../pages/FiftyFormats/Sarafyano.vue';
import Clubs from '../pages/FiftyFormats/Clubs.vue';
import Mahajerats from '../pages/FiftyFormats/Mahajerat.vue';
import Soldiers from '../pages/FiftyFormats/Soldiers.vue';
import Mawads from '../pages/FiftyFormats/Mawads.vue';
import LostMujremeens from '../pages/FiftyFormats/LostMujremens.vue';
import OldSoldiersHomes from '../pages/FiftyFormats/OldSoldiersHomes.vue';
import Robbers from '../pages/FiftyFormats/Robbers.vue';
import NonResposablePeople from '../pages/FiftyFormats/NonResponsablePeople.vue';
import MashkokHomes from '../pages/FiftyFormats/MashkokHomes.vue';
import DamagePlacses from '../pages/FiftyFormats/DamagePlacses.vue';
import DamageRasanPlaces from '../pages/FiftyFormats/DamageRasanPlaces.vue';
import MashkokShops from '../pages/FiftyFormats/MashkokShops.vue';
import Luchakanos from '../pages/FiftyFormats/Luchakanos.vue';
import Schools from '../pages/FiftyFormats/Schools.vue';
import HadoBusStop from '../pages/FiftyFormats/HadoBusStop.vue';
import TransportCompany from '../pages/FiftyFormats/TransportCompany.vue';
import CarSalesCompany from '../pages/FiftyFormats/CarSalesCompany.vue';
import Parkings from '../pages/FiftyFormats/Parkings.vue';
import Banks from '../pages/FiftyFormats/Bank.vue';
import Apartments from '../pages/FiftyFormats/Apartments.vue';
import RahnomaMamlats from '../pages/FiftyFormats/RahnomaMamlat.vue';
import Hosppitals from '../pages/FiftyFormats/Hosppital.vue';
import WorkShops from '../pages/FiftyFormats/WorkShops.vue';
import Badmashanos from '../pages/FiftyFormats/Badmashano.vue';
import Sarayanos from '../pages/FiftyFormats/Sarayanos.vue';
import Hotels from '../pages/FiftyFormats/Hotels.vue';
import Hostels from '../pages/FiftyFormats/Hostels.vue';
import Tujarans from '../pages/FiftyFormats/Tujarans.vue';
import Mujahedinos from '../pages/FiftyFormats/Mujahedinos.vue';
import EmaratOffices from '../pages/FiftyFormats/EmaratOffices.vue';
import OldJahadis from '../pages/FiftyFormats/OldJahadis.vue';
import MashkokPeopls from '../pages/FiftyFormats/MashkokPeoples.vue';
import Madrasa from '../pages/FiftyFormats/Madrasa.vue';
import Inlineinfoindex from '../pages/InlineInfo/inlineinfoindex.vue';
import ResourceInfo from '../pages/InlineInfo/ResourceInfo.vue';
import Resourcetab from '../pages/InlineInfo/Resourcetab.vue';
import Eventtab from '../pages/InlineInfo/Eventtab.vue';
import Wakilanos from '../pages/FiftyFormats/Wakilano.vue';
import Universities from '../pages/FiftyFormats/University.vue';
import Masjids from '../pages/FiftyFormats/Masjid.vue';
import HighWays from '../pages/FiftyFormats/Highway.vue';
import Companies from '../pages/FiftyFormats/Company.vue';
import DoneLostPlaces from '../pages/FiftyFormats/DoneLostPlaces.vue';
import EconemyProjects from '../pages/FiftyFormats/EconemyProject.vue';
import CriminalCapturePlaces from '../pages/FiftyFormats/CriminalCapturePlace.vue';
import Pumps from '../pages/FiftyFormats/pump.vue';
import Shoras from '../pages/FiftyFormats/Shora.vue';
import CheckPoints from '../pages/FiftyFormats/Checkpoint.vue';
import GovernmentPlaces from '../pages/FiftyFormats/GovernmentPlace.vue';
import Parks from '../pages/FiftyFormats/Park.vue';
import Complexes from '../pages/FiftyFormats/Complex.vue';
import DeplomaticPlaces from '../pages/FiftyFormats/DeplomaticPlace.vue';
import SuperMarkets from '../pages/FiftyFormats/SuperMarket.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Home },
    { path: '/login', component: Login },
    { path: '/users', component: User },
    { path: '/register', component: Register },
    { path: '/officeemployee', component: OfficeEmployeesPage },
    { path: '/resedintemployee', component: ResedintEmployee },
    { path: '/zoneemployee', component: ZoneEmployee },
    { path: '/resources', component: Resources },
    { path: '/events', component: Events },
    { path: '/delevirycaptive', component: DeleviryCaptive },
    { path: '/informations', component: Informations },
    { path: '/fifthyformatsroute', component: FifthyFormatsRoute },
    { path: '/inlineinfo', component: Inlineinfoindex },
    { path: '/resourceinfo', component: ResourceInfo },
    { path: '/resourcetab', component: Resourcetab },
    { path: '/eventtabl', component: Eventtab },
    // fifthy formats
    { path: '/fasads', component: Fasads },
    { path: '/saeenfasad', component: SaeenFasad },
    { path: '/heist', component: Heist },
    { path: '/captives', component: Captives },
    { path: '/mulkanos', component: Mulkanos },
    { path: '/entry-exits', component: EntryExits },
    { path: '/sarafyanos', component: Sarafyanos },
    { path: '/clubs', component: Clubs },
    { path: '/mahajerats', component: Mahajerats },
    { path: '/soldiers', component: Soldiers },
    // fifthy formats one
    { path: '/mawads', component: Mawads },
    { path: '/lostmujremeens', component: LostMujremeens },
    { path: '/oldsoldiershomes', component: OldSoldiersHomes },
    { path: '/robbers', component: Robbers },
    { path: '/nonresponsablepeople', component: NonResposablePeople },
    { path: '/mashkokhomes', component: MashkokHomes },
    { path: '/damageplaces', component: DamagePlacses },
    { path: '/damagerasanplaces', component: DamageRasanPlaces },
    { path: '/mashkokshops', component: MashkokShops },
    { path: '/luchakanos', component: Luchakanos },
    //fifthy fomats tow
    { path: '/schools', component: Schools },
    { path: '/hadobusstops', component: HadoBusStop },
    { path: '/transportcompanies', component: TransportCompany },
    { path: '/carsalescompanies', component: CarSalesCompany },
    { path: '/parkings', component: Parkings },
    { path: '/banks', component: Banks },
    { path: '/apartments', component: Apartments },
    { path: '/rahnomamamlats', component: RahnomaMamlats },
    { path: '/hosppitals', component: Hosppitals },
    { path: '/workshops', component: WorkShops },
    // fifthy formats three
    { path: '/badmashanos', component: Badmashanos },
    { path: '/sarayanos', component: Sarayanos },
    { path: '/hotels', component: Hotels },
    { path: '/hostels', component: Hostels },
    { path: '/tujarans', component: Tujarans },
    { path: '/mujahedinos', component: Mujahedinos },
    { path: '/emaratoffices', component: EmaratOffices },
    { path: '/oldjahadis', component: OldJahadis },
    { path: '/mashkokpeoples', component: MashkokPeopls },
    { path: '/madrasas', component: Madrasa },
    // fifthy formats three
    { path: '/wakilanos', component: Wakilanos },
    { path: '/universities', component: Universities },
    { path: '/masjids', component: Masjids },
    { path: '/highways', component: HighWays },
    { path: '/companies', component: Companies },
    { path: '/donelostplaces', component: DoneLostPlaces },
    { path: '/econemyprojects', component: EconemyProjects },
    { path: '/criminalcaptureplaces', component: CriminalCapturePlaces },
    { path: '/pumps', component: Pumps },
    { path: '/shoras', component: Shoras },
    { path: '/checkpoints', component: CheckPoints },
    { path: '/governmentplaces', component: GovernmentPlaces },
    { path: '/supermarkets', component: SuperMarkets },
    { path: '/parks', component: Parks },
    { path: '/complexes', component: Complexes },
    { path: '/deplomaticplaces', component: DeplomaticPlaces },
    // {
    //     path: "/home",
    //     component: home,
    //     children: [
    //         { path: "/Dashboard", component: Dashboard },
    //         { path: "/moneyAccount", component: MoneyAccount },
    //         { path: "/apartment", component: Apartment },
    //         { path: "/reservission", component: AddReservission },
    //         { path: "/users", component: User },
    //         { path: "/SystemSetting", component: Systemsetting },
    //         { path: "/Currency", component: Currency },
    //         { path: "/Expense", component: Expense },
    //         { path: "/ExpenseCategory", component: Expensecategory },
    //         { path: "/Owners", component: Owners },
    //         { path: "/Customer", component: Customer },
    //         { path: "/Salary", component: Salary },
    //         { path: "/Flat", component: Flat },
    //         { path: "/LoginPage", component: LoginPage },
    //         { path: "/Floor", component: Floor },
    //         { path: "/Employee", component: Employee },
    //         { path: "/Rent", component: Rent },
    //         { path: "/ReservationReport", component: ReservationReport },
    //         { path: "/AccountSummary", component: AccountSummary },
    //         { path: "/CustomerReport", component: CustomerReport },
    //         { path: "/DocumentAtachment", component: DocumentAtachment },
    //         { path: "/MonthlyIncome", component: MonthlyIncome },
    //         { path: "/Occupancy", component: Occupancy },
    //         { path: "/RentCollection", component: RentCollection },
    //         { path: "/MonthlyIncome", component: MonthlyIncome },
    //     ],
    // },
  ],
});

// router.beforeEach((to, from, next) => {
//     const loginRepository = useLoginRepository();
//     console.log("isLoggedIn?", loginRepository.isLoggedIn); // Check if this updates correctly
//     if (to.path !== "/" && !loginRepository.isLoggedIn) {
//         next("/");
//     } else {
//         next();
//     }
// });

export default router;
