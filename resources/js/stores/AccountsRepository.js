// src/stores/AccountsRepository.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';

/**
 * Keep the same "logic" style you use:
 * - setContentType before requests
 * - GET with page/perPage/search
 * - create/update/delete with dialogs + refresh list
 */
function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
    return;
  }
  axios.defaults.headers.common['Content-Type'] = type;
}

export const useAccountsRepository = defineStore('AccountsRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination
      page: 1,
      itemsPerPage: 10,

      // data
      Account: reactive([]),
      Accounts: reactive([]),
      AccountType: reactive([]),
      AccountTypes: reactive([]),
      // UI
      createDailogAccount: false,
      updateDailogAccount: false,
      createDailogAccountType: false,
      updateDailogAccountType: false,

      // loading & meta
      isLoading: false,
      loading: false,
      error: null,
      selectedItems: [],
      totalItems: 0,
      itemKey: 'id',

      // filters
      Search: '',
      clearSearch: '',
    };
  },

  actions: {
    // List (with pagination + search)
    async FetchAccountsData({ page, itemsPerPage }) {
      this.loading = true;
      setContentType('application/json');
      const resp = await axios.get(
        `/accounts?page=${page}&perPage=${itemsPerPage}&search=${this.Search}`
      );
      this.Accounts = resp.data.data;
      this.totalItems = resp?.data?.meta?.total ?? 0;
      this.loading = false;
    },

    // Create
    async CreateAccount(formData) {
      setContentType('application/json');
      // If your form sends FormData (files), switch to multipart:
      // setContentType("multipart/form-data");

      const config = {
        method: 'POST',
        url: '/accounts',
        data: formData,
      };

      await axios(config);
      toast.success('Account successfully created', { autoClose: 1000 });
      this.createDailogAccount = false;

      await this.FetchAccountsData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // Read one
    async FetchAccountData(id) {
      setContentType('application/json');
      const resp = await axios.get(`/accounts/${id}`);
      this.Account = resp.data.data;
    },

    // Update
    async UpdateAccount(id, data) {
      setContentType('application/json');

      const config = {
        method: 'PUT',
        url: `/accounts/${id}`,
        data,
      };

      await axios(config);
      toast.success('Account successfully updated', { autoClose: 1000 });
      this.updateDailogAccount = false;

      await this.FetchAccountsData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // Delete
    async DeleteAccount(id) {
      const config = {
        method: 'DELETE',
        url: `/accounts/${id}`,
      };

      await axios(config);
      toast.success('Account successfully deleted', { autoClose: 1000 });

      await this.FetchAccountsData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },
    // ===================== ACCOUNT TYPES (use same repo) =====================
    async FetchAccountTypesData({ page, itemsPerPage }) {
      this.loading = true;
      setContentType('application/json');
      const resp = await axios.get(
        `/account-types?page=${page}&perPage=${itemsPerPage}&search=${this.Search}`
      );
      this.AccountTypes = resp?.data?.data ?? [];
      this.totalItems = resp?.data?.meta?.total ?? 0;
      this.loading = false;
    },

    async CreateAccountType(payload) {
      setContentType('application/json');
      await axios.post(`/account-types`, payload);
      // toast.success("Account type created", { autoClose: 1000 }); // if you use toast
      this.createDailogAccountType = false;
      await this.FetchAccountTypesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async FetchAccountTypeData(id) {
      setContentType('application/json');
      const resp = await axios.get(`/account-types/${id}`);
      this.AccountType = resp?.data?.data ?? {};
    },

    async UpdateAccountType(id, payload) {
      setContentType('application/json');
      await axios.put(`/account-types/${id}`, payload);
      // toast.success("Account type updated", { autoClose: 1000 });
      this.updateDailogAccountType = false;
      await this.FetchAccountTypesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteAccountType(id) {
      await axios.delete(`/account-types/${id}`);
      // toast.success("Account type deleted", { autoClose: 1000 });
      await this.FetchAccountTypesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    // Helper: ISO yyyy-mm-dd
    getCurrentDate() {
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, '0');
      const day = String(today.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
  },
});
