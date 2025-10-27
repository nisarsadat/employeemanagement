import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import axios from "axios";

export const useFinanceReportRepository = defineStore("FinanceReportRepository", () => {
  const loading = ref(false);

  // Income data
  const income = reactive({
    labels: [],
    series: [],
    totals: {},
    range: {},
  });

  // Expense data
  const expense = reactive({
    labels: [],
    series: [],
    totals: {},
    range: {},
  });

  // Accounts data
  const accounts = reactive({
    names: [],
    values: [],
    total: 0,
    as_of: null,
  });

  // Fetch income report
  async function fetchIncome(period = "daily") {
    loading.value = true;
    const resp = await axios.get(`/finance-reports/income?period=${period}`);
    const data = resp.data;
    income.labels = data.labels || [];
    income.series = data.series?.income || [];
    income.totals = data.totals || {};
    income.range = data.range || {};
    loading.value = false;
  }

  // Fetch expense report
  async function fetchExpense(period = "daily") {
    loading.value = true;
    const resp = await axios.get(`/finance-reports/expense?period=${period}`);
    const data = resp.data;
    expense.labels = data.labels || [];
    expense.series = data.series?.expense || [];
    expense.totals = data.totals || {};
    expense.range = data.range || {};
    loading.value = false;
  }

  // Fetch accounts report
  async function fetchAccounts() {
    loading.value = true;
    const resp = await axios.get(`/finance-reports/accounts`);
    const data = resp.data;
    accounts.names = data.accounts?.map((a) => a.name) || [];
    accounts.values = data.accounts?.map((a) => Number(a.price ?? 0)) || [];
    accounts.total = data.total || 0;
    accounts.as_of = data.as_of || null;
    loading.value = false;
  }

  return {
    loading,
    income,
    expense,
    accounts,
    fetchIncome,
    fetchExpense,
    fetchAccounts,
  };
});
