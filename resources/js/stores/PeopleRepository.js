import { defineStore } from "pinia";
import { reactive, ref } from "vue";

// Import Axios
// import { toast } from "vue3-toastify";
import { useRouter } from "vue-router";
// import { axios, setContentType } from "@/plugins/axiosInstance";
import axios from "axios";
import setContentType from "axios";
export let usePeopleRepository = defineStore("PeopleRepository", {
    state() {
        return {
            router: useRouter(),
            page: 1,
            itemsPerPage: 10,
            Owner: reactive([]),
            Owners: reactive([]),
            Employee: reactive([]),
            Employees: reactive([]),
            Customer: reactive([]),
            Cusomers: reactive([]),
            Users: reactive([]),
            User: reactive([]),
            createDailog: false,
            updateDailog: false,
            createDailogEmployee: false,
            updateDailogEmployee: false,
            createDailogUsers: false,
            updateDailogUser: false,
            isLoading: false,
            error: null,
            loading: false,
            selectedItems: [],
            totalItems: 0,
            itemKey: "id",
            Search: "",
            clearSearch: "",
        };
    },
    actions: {
        async GetCurrencies() {
            this.loading = true;
            setContentType("application/json");
            const response = await axios.get(`/currencies`);
            this.currency = response.data.data;

            console.log(response.data.data);
            this.loading = false;
        },
        async GetAccounts() {
            this.loading = true;
            setContentType("application/json");
            const response = await axios.get(`/accounts`);

            this.account = response.data.data;

            console.log(response.data.data);
            this.loading = false;
        },
        getCurrentDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = (today.getMonth() + 1).toString().padStart(2, "0");
            const day = today.getDate().toString().padStart(2, "0");
            return `${year}-${month}-${day}`;
        },

        async FetchOwnersData({ page, itemsPerPage }) {
            this.loading = true;
            setContentType("application/json");
            const response = await axios.get(
                `owners?page=${page}&perPage=${itemsPerPage}&search=${this.Search}`
            );
            this.Owners = response.data.data;
            this.totalItems = response.data.meta.total;
            this.loading = false;
        },
        async CreateOwner(formData) {
            // Adding a custom header to the Axios request
            setContentType("application/json");

            const config = {
                method: "POST",
                url: "/owners",
                data: formData,
            };

            // Using Axios to make a GET request with async/await and custom headers
            const response = await axios(config);
            toast.success("Owner Succesfully Created", {
                autoClose: 1000,
            });
            this.createDailog = false;
            this.FetchOwnersData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        async DeleteOwner(id) {
            const config = {
                method: "DELETE",
                url: "/owners/" + id,
            };

            const response = await axios(config);
            toast.success("Owner Succesfully Deleted", {
                autoClose: 1000,
            });
            this.FetchOwnersData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        async FetchOwnerData(id) {
            setContentType("application/json");
            const response = await axios.get(`/owners/${id}`);

            this.Owner = response.data.data; // Assign the fetched data directly to this.people
        },
        async UpdateOwner(id, data) {
            console.log(data);

            // Adding a custom header to the Axios request
            setContentType("application/json");

            const config = {
                method: "PUT",
                url: "/owners/" + id,
                data: data,
            };

            // Using Axios to make a post request with async/await and custom headers
            const response = await axios(config);
            toast.success("Owner Succesfully Updated", {
                autoClose: 1000,
            });

            this.updateDailog = false;
            this.FetchOwnersData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        // Employee
        async FetchEmployeesData({ page, itemsPerPage }) {
            this.loading = true;
            setContentType("application/json");
            const response = await axios.get(
                `employees?page=${page}&perPage=${itemsPerPage}&search=${this.Search}`
            );
            this.Employees = response.data.data;
            this.totalItems = response.data.meta.total;
            this.loading = false;
        },
        async CreateEmployee(formData) {
            // Adding a custom header to the Axios request
            setContentType("application/json");
            setContentType("multipart/form-data");

            const config = {
                method: "POST",
                url: "/employees",
                data: formData,
            };

            // Using Axios to make a GET request with async/await and custom headers
            const response = await axios(config);
            toast.success("Employee Succesfully Created", {
                autoClose: 1000,
            });
            this.createDailogEmployee = false;
            this.FetchEmployeesData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        async DeleteEmployee(id) {
            const config = {
                method: "DELETE",
                url: "/employees/" + id,
            };

            const response = await axios(config);
            toast.success("Employee Succesfully Deleted", {
                autoClose: 1000,
            });
            this.FetchEmployeesData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        async FetchEmployeeData(id) {
            setContentType("application/json");
            const response = await axios.get(`/employees/${id}`);

            this.Employee = response.data.data; // Assign the fetched data directly to this.people
        },
        async UpdateEmployee(id, data) {
            console.log(data);

            // Adding a custom header to the Axios request
            setContentType("application/json");

            const config = {
                method: "PUT",
                url: "/employees/" + id,
                data: data,
            };

            // Using Axios to make a post request with async/await and custom headers
            const response = await axios(config);
            toast.success("Employee Succesfully Updated", {
                autoClose: 1000,
            });

            this.updateDailogEmployee = false;
            this.FetchEmployeesData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        // User
        async FetchUsersData({ page, itemsPerPage }) {
            this.loading = true;
            setContentType("application/json");
            const response = await axios.get(
                `users?page=${page}&perPage=${itemsPerPage}&search=${this.Search}`
            );
            this.Users = response.data.data;
            this.totalItems = response.data.meta.total;
            this.loading = false;
        },
        async CreateUser(formData) {
            // Adding a custom header to the Axios request
            setContentType("application/json");
            setContentType("multipart/form-data");

            const config = {
                method: "POST",
                url: "/users",
                data: formData,
            };

            // Using Axios to make a GET request with async/await and custom headers
            const response = await axios(config);
            toast.success("User Succesfully Created", {
                autoClose: 1000,
            });
            this.createDailog = false;
            this.FetchUsersData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        async DeleteUser(id) {
            const config = {
                method: "DELETE",
                url: "/users/" + id,
            };

            const response = await axios(config);
            toast.success("User Succesfully Deleted", {
                autoClose: 1000,
            });
            this.FetchUsersData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        async FetchUserData(id) {
            setContentType("application/json");
            const response = await axios.get(`/users/${id}`);

            this.User = response.data.data; // Assign the fetched data directly to this.people
        },
        async UpdateEmployee(id, data) {
            console.log(data);

            // Adding a custom header to the Axios request
            setContentType("application/json");

            const config = {
                method: "PUT",
                url: "/users/" + id,
                data: data,
            };

            // Using Axios to make a post request with async/await and custom headers
            const response = await axios(config);
            toast.success("User Succesfully Updated", {
                autoClose: 1000,
            });

            this.updateDailog = false;
            this.FetchUsersData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        // Customer
        async FetchCustomersData({ page, itemsPerPage }) {
            this.loading = true;
            setContentType("application/json");
            const response = await axios.get(
                `customers?page=${page}&perPage=${itemsPerPage}&search=${this.Search}`
            );
            this.Customers = response.data.data;
            this.totalItems = response.data.meta.total;
            this.loading = false;
        },
        async CreateCustomer(formData) {
            // Adding a custom header to the Axios request
            setContentType("application/json");
            setContentType("multipart/form-data");

            const config = {
                method: "POST",
                url: "/customers",
                data: formData,
            };

            // Using Axios to make a GET request with async/await and custom headers
            const response = await axios(config);
            toast.success("Customer Succesfully Created", {
                autoClose: 1000,
            });
            this.createDailog = false;
            this.FetchCustomersData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        async DeleteCustomer(id) {
            const config = {
                method: "DELETE",
                url: "/customers/" + id,
            };

            const response = await axios(config);
            toast.success("Customer Succesfully Deleted", {
                autoClose: 1000,
            });
            this.FetchCustomersData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
        async FetchCustomerData(id) {
            setContentType("application/json");
            const response = await axios.get(`/customers/${id}`);

            this.Customer = response.data.data; // Assign the fetched data directly to this.people
        },
        async UpdateCustomer(id, data) {
            console.log(data);

            // Adding a custom header to the Axios request
            setContentType("application/json");

            const config = {
                method: "PUT",
                url: "/customers/" + id,
                data: data,
            };

            // Using Axios to make a post request with async/await and custom headers
            const response = await axios(config);
            toast.success("Customer Succesfully Updated", {
                autoClose: 1000,
            });

            this.updateDailog = false;
            this.FetchCustomersData({
                page: this.page,
                itemsPerPage: this.itemsPerPage,
            });
        },
    },
});
