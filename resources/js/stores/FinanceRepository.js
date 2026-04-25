import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useFinanceRepository = defineStore('FinanceRepository', () => {
  const loading = ref(false);

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

  const officeEmployees = ref([]);
  const officeEmployeesLoading = ref(false);

  async function FetchOfficeEmployeesOptions({ search = '' } = {}) {
    officeEmployeesLoading.value = true;
    try {
      const resp = await axios.get('/office-employees', { params: { search } });
      officeEmployees.value = resp.data?.data ?? resp.data ?? [];
    } finally {
      officeEmployeesLoading.value = false;
    }
  }

  const resources = ref([]);
  const resourcesLoading = ref(false);

  async function FetchResourcesOptions({ search = '' } = {}) {
    resourcesLoading.value = true;
    try {
      const resp = await axios.get('/resources', { params: { search } });
      resources.value = resp.data?.data ?? resp.data ?? [];
    } finally {
      resourcesLoading.value = false;
    }
  }

  const events = ref([]);
  const eventsLoading = ref(false);

  async function FetchEventsOptions({ search = '' } = {}) {
    eventsLoading.value = true;
    try {
      const resp = await axios.get('/events', { params: { search } });
      events.value = resp.data?.data ?? resp.data ?? [];
    } finally {
      eventsLoading.value = false;
    }
  }

  const informations = ref([]);
  const informationsLoading = ref(false);

  async function FetchInformationsOptions({ search = '' } = {}) {
    informationsLoading.value = true;
    try {
      const resp = await axios.get('/informations', { params: { search } });
      informations.value = resp.data?.data ?? resp.data ?? [];
    } finally {
      informationsLoading.value = false;
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

  const accountsYear = ref(null);
  const accountsMonth = ref(null);

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
  const incomesSearchValue = ref(null);
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
  const financesSearchField = ref(null);
  const financesAccountId = ref(null);
  const financesOfficeEmployeeId = ref(null);
  const financesSearchValue = ref(null);

  const availableYears = ref([]);
  const yearsLoading = ref(false);

  const financesYear = ref(null);
  const financesMonth = ref(null);

  const financesSummary = ref({
    total_balance_all_accounts: '0.00',
    total_income_all: '0.00',
    total_outcome_all: '0.00',
    filtered_outcome: '0.00',
    filtered_income: null,
    filtered_balance: null,
    has_filters: false,
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
        params: { page, perPage, search, year: year ?? undefined, month: month ?? undefined },
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
    search_field = null,
    search_value = incomesSearchValue.value,
    account_id = incomesAccountId.value,
    year = incomesYear.value,
    month = incomesMonth.value,
  } = {}) {
    loading.value = true;
    try {
      incomesPage.value = page;
      incomesPerPage.value = perPage;
      incomesSearch.value = search;
      incomesSearchValue.value = search_value ?? null;
      incomesAccountId.value = account_id ?? null;
      incomesYear.value = year ?? null;
      incomesMonth.value = month ?? null;

      const resp = await axios.get('/incomes', {
        params: {
          page,
          perPage,
          search,
          search_field: search_field ?? undefined,
          search_value: search_value ?? undefined,
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
  async function FetchIncomeFilterOptions(params = {}) {
    return await axios.get('/incomes/filter-options', { params });
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
    incomesSearchValue.value = null;
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
    search_field = financesSearchField.value,
    search_value = financesSearchValue.value,
    account_id = financesAccountId.value,
    office_employee_id = financesOfficeEmployeeId.value,
    year = financesYear.value,
    month = financesMonth.value,
    date_field = 'date',
  } = {}) {
    loading.value = true;
    try {
      financesPage.value = page;
      financesPerPage.value = perPage;
      financesSearch.value = search;
      financesSearchField.value = search_field ?? null;
      financesSearchValue.value = search_value ?? null;
      financesAccountId.value = account_id ?? null;
      financesOfficeEmployeeId.value = office_employee_id ?? null;
      financesYear.value = year ?? null;
      financesMonth.value = month ?? null;

      const resp = await axios.get('/finances', {
        params: {
          page,
          perPage,
          search,
          search_field: search_field ?? undefined,
          search_value: search_value ?? undefined,
          account_id: account_id ?? undefined,
          office_employee_id: office_employee_id ?? undefined,
          year: year ?? undefined,
          month: month ?? undefined,
          date_field,
        },
      });

      financesSummary.value = resp.data?.summary ?? {
        total_balance_all_accounts: '0.00',
        total_income_all: '0.00',
        total_outcome_all: '0.00',
        filtered_outcome: '0.00',
        filtered_income: null,
        filtered_balance: null,
        has_filters: false,
      };

      const { data, total } = unwrapListResponse(resp);
      finances.value = data;
      financesTotalItems.value = total;
    } finally {
      loading.value = false;
    }
  }
  async function FetchFinanceFilterOptions(params = {}) {
    return await axios.get('/finances/filter-options', { params });
  }
  async function FetchFinanceDateOptions(params = {}) {
    return await axios.get('/finances/date-options', { params });
  }

  async function DownloadFinancesExcel({
    search = '',
    search_field = null,
    search_value = null,
    account_id = null,
    office_employee_id = null,
    year = null,
    month = null,
    date_field = 'date',
    onDownloadProgress = null,
  } = {}) {
    const response = await axios.get('/finances/export/excel', {
      params: {
        search,
        search_field,
        search_value,
        account_id,
        office_employee_id,
        year,
        month,
        date_field,
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
    let fileName = 'finances.xlsx';

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
  }
  async function FetchFinanceData(id) {
    const resp = await axios.get(`/finances/${id}`);
    finance.value = resp.data?.data ?? resp.data;
    return finance.value;
  }
  async function OpenFinancesPrintView(payload = {}) {
    const {
      filters = {},
      selected_columns = [],
      column_labels = {},
      print_labels = {},
      locale = 'fa',
      title = 'چاپ حساب مالی',
      file_name,
      module,
      ...directFilters
    } = payload;

    const params = {
      // ✅ supports both styles:
      // 1) payload.filters.search
      // 2) payload.search
      ...directFilters,
      ...filters,

      selected_columns,
      column_labels,
      print_labels,
      locale,
      title,
    };

    const response = await axios.get('/finances/print', {
      params,
      responseType: 'text',
    });

    const printWindow = window.open('', '_blank');

    if (!printWindow) return;

    printWindow.document.open();
    printWindow.document.write(response.data);
    printWindow.document.close();
    printWindow.focus();
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
    financesPerPage.value = 10;
    financesSearch.value = '';
    financesSearchField.value = null;
    financesAccountId.value = null;
    financesOfficeEmployeeId.value = null;
    financesYear.value = null;
    financesMonth.value = null;
    financesSearchValue.value = null;

    financesSummary.value = {
      total_balance_all_accounts: '0.00',
      total_income_all: '0.00',
      total_outcome_all: '0.00',
      filtered_outcome: '0.00',
      filtered_income: null,
      filtered_balance: null,
      has_filters: false,
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

  return {
    loading,

    // Dropdowns
    officeEmployees,
    officeEmployeesLoading,
    FetchOfficeEmployeesOptions,

    resources,
    resourcesLoading,
    FetchResourcesOptions,

    events,
    eventsLoading,
    FetchEventsOptions,

    informations,
    informationsLoading,
    FetchInformationsOptions,

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
    incomesSearchValue,
    FetchIncomeFilterOptions,
    // Finances
    finances,
    finance,
    financesSummary,
    financesTotalItems,
    financesPage,
    financesPerPage,
    financesSearch,
    financesSearchField,
    financesAccountId,
    financesOfficeEmployeeId,
    financesYear,
    financesMonth,
    FetchFinancesData,
    FetchFinanceDateOptions,
    DownloadFinancesExcel,
    FetchFinanceData,
    CreateFinance,
    UpdateFinance,
    DeleteFinance,
    ResetFinancesFilters,
    financesSearchValue,
    FetchFinanceFilterOptions,
    availableYears,
    yearsLoading,
    FetchYears,
    OpenFinancesPrintView,
  };
});
