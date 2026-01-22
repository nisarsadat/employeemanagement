import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

/**
 * Single repo for:
 *  - Accounts
 *  - Income
 *  - Finance (Expense/Outcome)
 *  - Dropdown options: OfficeEmployees + Resources
 */

export const useFinanceRepository = defineStore('FinanceRepository', () => {
  const loading = ref(false);

  // =========================
  // COMMON HELPERS
  // =========================
  function unwrapListResponse(resp) {
    const data = resp.data?.data ?? resp.data ?? [];
    const total =
      resp.data?.meta?.total ??
      resp.data?.total ??
      resp.data?.totalItems ??
      (Array.isArray(data) ? data.length : 0);

    return { data, total };
  }

  // =========================
  // DROPDOWN OPTIONS
  // =========================

  // Office Employees
  const officeEmployees = ref([]);
  const officeEmployeesLoading = ref(false);

  async function FetchOfficeEmployeesOptions({ search = '' } = {}) {
    officeEmployeesLoading.value = true;
    try {
      const resp = await axios.get('/office-employees', {
        params: { search },
      });
      officeEmployees.value = resp.data?.data ?? resp.data ?? [];
    } finally {
      officeEmployeesLoading.value = false;
    }
  }

  // Resources
  const resources = ref([]);
  const resourcesLoading = ref(false);

  async function FetchResourcesOptions({ search = '' } = {}) {
    resourcesLoading.value = true;
    try {
      const resp = await axios.get('/resources', {
        params: { search },
      });
      resources.value = resp.data?.data ?? resp.data ?? [];
    } finally {
      resourcesLoading.value = false;
    }
  }

  // =========================
  // ACCOUNTS STATE
  // =========================
  const accounts = ref([]);
  const account = ref(null);
  const accountsTotalItems = ref(0);

  const accountsPage = ref(1);
  const accountsPerPage = ref(10);
  const accountsSearch = ref('');

  // ✅ NEW
  const accountsYear = ref(null); // 2026
  const accountsMonth = ref(null); // 1..12

  // =========================
  // INCOMES STATE
  // =========================
  const incomes = ref([]);
  const income = ref(null);
  const incomesTotalItems = ref(0);

  const incomesPage = ref(1);
  const incomesPerPage = ref(10);
  const incomesSearch = ref('');
  const incomesAccountId = ref(null);

  // ✅ NEW
  const incomesYear = ref(null);
  const incomesMonth = ref(null);

  // =========================
  // FINANCES STATE
  // =========================
  const finances = ref([]);
  const finance = ref(null);
  const financesTotalItems = ref(0);

  const financesPage = ref(1);
  const financesPerPage = ref(10);
  const financesSearch = ref('');
  const financesAccountId = ref(null);
  const financesOfficeEmployeeId = ref(null);

  const availableYears = ref([]);
  const yearsLoading = ref(false);

  // ✅ NEW
  const financesYear = ref(null);
  const financesMonth = ref(null);

  // ✅ summary returned from backend
  const financesSummary = ref({
    total_balance_all_accounts: '0.00',
    total_income_all: '0.00',
    total_outcome_all: '0.00',
    filtered_outcome: '0.00',
  });

  // =========================
  // ACCOUNTS ACTIONS
  // =========================
  async function FetchAccountsData({
    page = accountsPage.value,
    perPage = accountsPerPage.value,
    search = accountsSearch.value,
    year = accountsYear.value,
    month = accountsMonth.value,
  } = {}) {
    loading.value = true;
    try {
      accountsPage.value = page;
      accountsPerPage.value = perPage;
      accountsSearch.value = search;
      accountsYear.value = year ?? null;
      accountsMonth.value = month ?? null;

      const resp = await axios.get('/accounts', {
        params: {
          page,
          perPage,
          search,
          year: year ?? undefined,
          month: month ?? undefined,
        },
      });

      const { data, total } = unwrapListResponse(resp);
      accounts.value = data;
      accountsTotalItems.value = total;
    } finally {
      loading.value = false;
    }
  }

  async function FetchAccountData(id) {
    const resp = await axios.get(`/accounts/${id}`);
    account.value = resp.data?.data ?? resp.data;
    return account.value;
  }

  async function CreateAccount(payload) {
    const resp = await axios.post('/accounts', payload);
    return resp.data?.data ?? resp.data;
  }

  async function UpdateAccount(id, payload) {
    const resp = await axios.put(`/accounts/${id}`, payload);
    return resp.data?.data ?? resp.data;
  }

  async function DeleteAccount(id) {
    await axios.delete(`/accounts/${id}`);
  }

  function ResetAccountsFilters() {
    accountsPage.value = 1;
    accountsPerPage.value = 10;
    accountsSearch.value = '';
    accountsYear.value = null;
    accountsMonth.value = null;
  }

  // =========================
  // INCOMES ACTIONS
  // =========================
  async function FetchIncomesData({
    page = incomesPage.value,
    perPage = incomesPerPage.value,
    search = incomesSearch.value,
    account_id = incomesAccountId.value,
    year = incomesYear.value,
    month = incomesMonth.value,
  } = {}) {
    loading.value = true;
    try {
      incomesPage.value = page;
      incomesPerPage.value = perPage;
      incomesSearch.value = search;
      incomesAccountId.value = account_id ?? null;
      incomesYear.value = year ?? null;
      incomesMonth.value = month ?? null;

      const resp = await axios.get('/incomes', {
        params: {
          page,
          perPage,
          search,
          account_id: account_id ?? undefined,
          year: year ?? undefined,
          month: month ?? undefined,
        },
      });

      const { data, total } = unwrapListResponse(resp);
      incomes.value = data;
      incomesTotalItems.value = total;
    } finally {
      loading.value = false;
    }
  }

  async function FetchIncomeData(id) {
    const resp = await axios.get(`/incomes/${id}`);
    income.value = resp.data?.data ?? resp.data;
    return income.value;
  }

  async function CreateIncome(payload) {
    const resp = await axios.post('/incomes', payload);
    return resp.data?.data ?? resp.data;
  }

  async function UpdateIncome(id, payload) {
    const resp = await axios.put(`/incomes/${id}`, payload);
    return resp.data?.data ?? resp.data;
  }

  async function DeleteIncome(id) {
    await axios.delete(`/incomes/${id}`);
  }

  function ResetIncomesFilters() {
    incomesPage.value = 1;
    incomesPerPage.value = 10;
    incomesSearch.value = '';
    incomesAccountId.value = null;
    incomesYear.value = null;
    incomesMonth.value = null;
  }

  // =========================
  // FINANCES ACTIONS
  // =========================
  async function FetchFinancesData({
    page = financesPage.value,
    perPage = financesPerPage.value,
    search = financesSearch.value,
    account_id = financesAccountId.value,
    office_employee_id = financesOfficeEmployeeId.value,
    year = financesYear.value,
    month = financesMonth.value,
  } = {}) {
    loading.value = true;
    try {
      financesPage.value = page;
      financesPerPage.value = perPage;
      financesSearch.value = search;
      financesAccountId.value = account_id ?? null;
      financesOfficeEmployeeId.value = office_employee_id ?? null;
      financesYear.value = year ?? null;
      financesMonth.value = month ?? null;

      const resp = await axios.get('/finances', {
        params: {
          search,
          account_id: account_id ?? undefined,
          office_employee_id: office_employee_id ?? undefined,
          year: year ?? undefined,
          month: month ?? undefined,
        },
      });

      // ✅ summary from backend
      financesSummary.value = resp.data?.summary ?? {
        total_balance_all_accounts: '0.00',
        total_income_all: '0.00',
        total_outcome_all: '0.00',
        filtered_outcome: '0.00',
      };

      const { data, total } = unwrapListResponse(resp);
      finances.value = data;
      financesTotalItems.value = total;
    } finally {
      loading.value = false;
    }
  }

  async function FetchFinanceData(id) {
    const resp = await axios.get(`/finances/${id}`);
    finance.value = resp.data?.data ?? resp.data;
    return finance.value;
  }

  async function CreateFinance(payload) {
    const resp = await axios.post('/finances', payload);
    return resp.data?.data ?? resp.data;
  }

  async function UpdateFinance(id, payload) {
    const resp = await axios.put(`/finances/${id}`, payload);
    return resp.data?.data ?? resp.data;
  }

  async function DeleteFinance(id) {
    await axios.delete(`/finances/${id}`);
  }

  function ResetFinancesFilters() {
    financesPage.value = 1;
    financesPerPage.value = 999999;
    financesSearch.value = '';
    financesAccountId.value = null;
    financesOfficeEmployeeId.value = null;
    financesYear.value = null;
    financesMonth.value = null;

    financesSummary.value = {
      total_balance_all_accounts: '0.00',
      total_income_all: '0.00',
      total_outcome_all: '0.00',
      filtered_outcome: '0.00',
    };
  }
  async function FetchYears(type) {
    yearsLoading.value = true;
    try {
      const resp = await axios.get(`/${type}/years`);
      availableYears.value = resp.data ?? [];
    } finally {
      yearsLoading.value = false;
    }
  }

  // =========================
  // EXPORT STORE API
  // =========================
  return {
    loading,

    // Dropdowns
    officeEmployees,
    officeEmployeesLoading,
    FetchOfficeEmployeesOptions,

    resources,
    resourcesLoading,
    FetchResourcesOptions,

    // Accounts
    accounts,
    account,
    accountsTotalItems,
    accountsPage,
    accountsPerPage,
    accountsSearch,
    accountsYear,
    accountsMonth,
    FetchAccountsData,
    FetchAccountData,
    CreateAccount,
    UpdateAccount,
    DeleteAccount,
    ResetAccountsFilters,

    // Incomes
    incomes,
    income,
    incomesTotalItems,
    incomesPage,
    incomesPerPage,
    incomesSearch,
    incomesAccountId,
    incomesYear,
    incomesMonth,
    FetchIncomesData,
    FetchIncomeData,
    CreateIncome,
    UpdateIncome,
    DeleteIncome,
    ResetIncomesFilters,

    // Finances
    finances,
    finance,
    financesSummary,
    financesTotalItems,
    financesPage,
    financesPerPage,
    financesSearch,
    financesAccountId,
    financesOfficeEmployeeId,
    financesYear,
    financesMonth,
    FetchFinancesData,
    FetchFinanceData,
    CreateFinance,
    UpdateFinance,
    DeleteFinance,
    ResetFinancesFilters,

    availableYears,
    yearsLoading,
    FetchYears,
  };
});
