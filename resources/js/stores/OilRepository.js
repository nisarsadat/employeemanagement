import { defineStore } from 'pinia';
import { reactive } from 'vue';
import axios from 'axios';

function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
    return;
  }
  axios.defaults.headers.common['Content-Type'] = type;
}

export const useOilRepository = defineStore('OilRepository', {
  state() {
    return {
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      loading: false,

      OilAccounts: reactive([]),
      OilAccount: reactive({}),

      IncomeOils: reactive([]),
      IncomeOil: reactive({}),

      ExpenseOils: reactive([]),
      ExpenseOil: reactive({}),

      createDialog: false,
      updateDialog: false,

      Search: '',
      searchField: null,
      searchValue: null,
      from: null,
      to: null,

      yearsLoading: false,

      year: null,
      month: null,

      availableYears: reactive([]),

      summary: reactive({
        totalExpense: 0,
        totalIncome: 0,
        totalOilQuantity: 0,
        expenseCount: 0,
        incomeCount: 0,
        oilAccountsCount: 0,
        filteredExpense: 0,
        filteredIncome: 0,

        total_quantity_all_accounts: 0,
        total_income_quantity: 0,
        total_expense_quantity: 0,
        filtered_expense_quantity: 0,
        filtered_balance: null,
        has_filters: false,
      }),
    };
  },

  actions: {
    async FetchYears(type) {
      this.yearsLoading = true;
      try {
        const resp = await axios.get(`/${type}/years`);
        const payload = resp.data;

        this.availableYears = Array.isArray(payload)
          ? payload
          : Array.isArray(payload?.data)
          ? payload.data
          : Array.isArray(payload?.years)
          ? payload.years
          : [];
      } finally {
        this.yearsLoading = false;
      }
    },

    async FetchExpenseOilDateOptions(params = {}) {
      return await axios.get('/expense-oils/date-options', { params });
    },

    async DownloadExpenseOilsExcel({
      search = '',
      search_field = null,
      search_value = null,
      oil_account_id = null,
      employee_id = null,
      year = null,
      month = null,
      onDownloadProgress = null,
    } = {}) {
      const response = await axios.get('/expense-oils/export/excel', {
        params: {
          search,
          search_field,
          search_value,
          oil_account_id,
          employee_id,
          year,
          month,
        },
        responseType: 'blob',
        onDownloadProgress,
      });

      const blob = new Blob([response.data], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      });

      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;

      const disposition = response.headers['content-disposition'];
      let fileName = 'expense_oils.xlsx';

      if (disposition) {
        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
        if (match?.[1]) {
          fileName = match[1].replace(/['"]/g, '');
        }
      }

      link.setAttribute('download', fileName);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);

      return response;
    },
    async FetchOilAccounts({ page, itemsPerPage } = {}) {
      this.loading = true;
      setContentType('application/json');

      const p = page ?? this.page;
      const pp = itemsPerPage ?? this.itemsPerPage;

      try {
        const resp = await axios.get(`/oil-accounts`, {
          params: {
            page: p,
            perPage: pp,
            search: this.Search || '',
            year: this.year ?? undefined,
            month: this.month ?? undefined,
          },
        });

        this.OilAccounts = resp?.data?.data ?? [];
        this.totalItems = resp?.data?.meta?.total ?? 0;

        if (resp?.data?.summary) this.summary = resp.data.summary;
      } finally {
        this.loading = false;
      }
    },

    async FetchOilSummary() {
      setContentType('application/json');

      const resp = await axios.get(`/oil/summary`, {
        params: {
          search: this.Search || undefined,
          year: this.year ?? undefined,
          month: this.month ?? undefined,
          from: this.from ?? undefined,
          to: this.to ?? undefined,
        },
      });

      this.summary = resp?.data ?? {
        totalExpense: 0,
        totalIncome: 0,
        totalOilQuantity: 0,
        expenseCount: 0,
        incomeCount: 0,
        oilAccountsCount: 0,
        filteredExpense: 0,
        filteredIncome: 0,
      };
    },

    async FetchOilAccount(id) {
      setContentType('application/json');
      const resp = await axios.get(`/oil-accounts/${id}`);
      this.OilAccount = resp?.data?.data ?? {};
    },

    async CreateOilAccount(payload) {
      setContentType('application/json');
      await axios.post(`/oil-accounts`, payload);
      this.createDialog = false;
      await this.FetchOilAccounts({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateOilAccount(id, payload) {
      setContentType('application/json');
      await axios.put(`/oil-accounts/${id}`, payload);
      this.updateDialog = false;
      await this.FetchOilAccounts({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteOilAccount(id) {
      await axios.delete(`/oil-accounts/${id}`);
      await this.FetchOilAccounts({ page: this.page, itemsPerPage: this.itemsPerPage });
    },
    async FetchIncomeOilFilterOptions(params = {}) {
      return await axios.get('/income-oils/filter-options', { params });
    },
    async FetchIncomeOils({
      page,
      itemsPerPage,
      search = this.Search,
      search_field = this.searchField,
      search_value = this.searchValue,
      year = this.year,
      month = this.month,
      oil_account_id = null,
      from = this.from,
      to = this.to,
    } = {}) {
      this.loading = true;
      setContentType('application/json');

      const p = page ?? this.page;
      const pp = itemsPerPage ?? this.itemsPerPage;

      try {
        this.page = p;
        this.itemsPerPage = pp;
        this.Search = search ?? '';
        this.searchField = search_field ?? null;
        this.searchValue = search_value ?? null;
        this.year = year ?? null;
        this.month = month ?? null;
        this.from = from ?? null;
        this.to = to ?? null;

        const resp = await axios.get(`/income-oils`, {
          params: {
            page: p,
            perPage: pp,
            search: this.Search || '',
            search_field: this.searchField ?? undefined,
            search_value: this.searchValue ?? undefined,
            from: this.from ?? undefined,
            to: this.to ?? undefined,
            year: this.year ?? undefined,
            month: this.month ?? undefined,
            oil_account_id: oil_account_id ?? undefined,
          },
        });

        this.IncomeOils = resp?.data?.data ?? [];
        this.totalItems = resp?.data?.meta?.total ?? 0;

        if (resp?.data?.summary) this.summary = resp.data.summary;
      } finally {
        this.loading = false;
      }
    },

    async FetchIncomeOil(id) {
      setContentType('application/json');
      const resp = await axios.get(`/income-oils/${id}`);
      this.IncomeOil = resp?.data?.data ?? {};
    },

    async CreateIncomeOil(payload) {
      setContentType('application/json');
      await axios.post(`/income-oils`, payload);
      this.createDialog = false;
      await this.FetchIncomeOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateIncomeOil(id, payload) {
      setContentType('application/json');
      await axios.put(`/income-oils/${id}`, payload);
      this.updateDialog = false;
      await this.FetchIncomeOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteIncomeOil(id) {
      await axios.delete(`/income-oils/${id}`);
      await this.FetchIncomeOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async FetchExpenseOils({
      page,
      itemsPerPage,
      search = this.Search,
      search_field = this.searchField,
      search_value = this.searchValue,
      year = this.year,
      month = this.month,
      oil_account_id = null,
      employee_id = null,
    } = {}) {
      this.loading = true;
      setContentType('application/json');

      const p = page ?? this.page;
      const pp = itemsPerPage ?? this.itemsPerPage;

      try {
        this.page = p;
        this.itemsPerPage = pp;
        this.Search = search ?? '';
        this.searchField = search_field ?? null;
        this.searchValue = search_value ?? null;
        this.year = year ?? null;
        this.month = month ?? null;

        const resp = await axios.get(`/expense-oils`, {
          params: {
            page: p,
            perPage: pp,
            search: this.Search || '',
            search_field: this.searchField ?? undefined,
            search_value: this.searchValue ?? undefined,
            year: this.year ?? undefined,
            month: this.month ?? undefined,
            oil_account_id: oil_account_id ?? undefined,
            employee_id: employee_id ?? undefined,
          },
        });

        this.ExpenseOils = resp?.data?.data ?? [];
        this.totalItems = resp?.data?.meta?.total ?? 0;

        if (resp?.data?.summary) this.summary = resp.data.summary;
      } finally {
        this.loading = false;
      }
    },
    async FetchExpenseOilFilterOptions(params = {}) {
      return await axios.get('/expense-oils/filter-options', { params });
    },

    async FetchExpenseOil(id) {
      setContentType('application/json');
      const resp = await axios.get(`/expense-oils/${id}`);
      this.ExpenseOil = resp?.data?.data ?? {};
    },

    async CreateExpenseOil(payload) {
      setContentType('application/json');
      await axios.post(`/expense-oils`, payload);
      this.createDialog = false;
      await this.FetchExpenseOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateExpenseOil(id, payload) {
      setContentType('application/json');
      await axios.put(`/expense-oils/${id}`, payload);
      this.updateDialog = false;
      await this.FetchExpenseOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteExpenseOil(id) {
      await axios.delete(`/expense-oils/${id}`);
      await this.FetchExpenseOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    ResetExpenseOilsFilters() {
      this.page = 1;
      this.itemsPerPage = 10;
      this.Search = '';
      this.searchField = null;
      this.year = null;
      this.month = null;
      this.searchValue = null;
      this.from = null;
      this.to = null;

      this.summary = {
        totalExpense: 0,
        totalIncome: 0,
        totalOilQuantity: 0,
        expenseCount: 0,
        incomeCount: 0,
        oilAccountsCount: 0,
        filteredExpense: 0,
        filteredIncome: 0,
        total_quantity_all_accounts: 0,
        total_income_quantity: 0,
        total_expense_quantity: 0,
        filtered_expense_quantity: 0,
        filtered_balance: null,
        has_filters: false,
      };
    },
  },
});
