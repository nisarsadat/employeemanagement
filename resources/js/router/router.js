import { createRouter, createWebHistory } from "vue-router";
import Home from "../pages/Home.vue";
import Login from "../pages/Login.vue";
import User from "../pages/User/User.vue";
import Register from "../pages/Register.vue";
import OfficeEmployeesPage from "../pages/OfficeEmployee/OfficeEmployeesPage.vue";
import ResedintEmployee from "../pages/ResedintsEmployee/ResedintEmployee.vue";
import ZoneEmployee from "../pages/ZoneEmployee/ZoneEmployee.vue";
import Resources from "../pages/Resources/Resources.vue";
import Events from "../pages/Events/Events.vue";
import DeleviryCaptive from "../pages/DeleviryCaptive/DeleviryCaptive.vue";
import Informations from "../pages/Informations/Informations.vue";



const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/", component: Home },
        { path: "/login", component: Login },
        { path: "/users", component: User },
        { path: "/register", component: Register },
        { path: "/officeemployee", component: OfficeEmployeesPage },
        { path: "/resedintemployee", component: ResedintEmployee },
        { path: "/zoneemployee", component: ZoneEmployee },
        { path: "/resources", component: Resources },
        { path: "/events", component: Events },
        { path: "/delevirycaptive", component: DeleviryCaptive },
        { path: "/informations", component: Informations },
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
