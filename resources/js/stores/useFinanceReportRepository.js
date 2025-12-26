// ✅ src/stores/FinancesRepository.js
import { defineStore } from "pinia";
import { ref, reactive } from "vue";
import axios from "axios";

export const useFinancesRepository = defineStore("FinancesRepository", () => {
  const loading = ref(false);

  // dialogs
  const createDialog = ref(false);
  const updateDialog = ref(false);

  // pagination
  const page = ref(1);
  const itemsPerPage = ref(10);
  const totalItems = ref(0);

  // filters
  const Search = ref("");

  // data
  const Finances = ref([]);
  const Finance = reactive({});

  const editor = reactive(blankRow());

  function blankRow() {
    return {
      id: null,
      account_id: null,
      office_employee_id: null,
      resource_id: null,
      money_amount_number: 0,
      money_amount_words: "",
      date: null,
      expense_result: "",
      payer: "",
      receiver: "",
      notes: "",
    };
  }

  function setBlankEditorRow() {
    Object.assign(editor, blankRow());
  }

  function openCreateDialog() {
    setBlankEditorRow();
    createDialog.value = true;
    updateDialog.value = false;
  }

  async function openEditDialog(id) {
    await FetchFinanceData(id);
    Object.assign(editor, blankRow(), Finance);
    updateDialog.value = true;
    createDialog.value = false;
  }

  function closeDialogs() {
    createDialog.value = false;
    updateDialog.value = false;
  }

  async function FetchFinancesData({ page: p, itemsPerPage: ipp }) {
    loading.value = true;
    try {
      const resp = await axios.get("/finances", {
        params: { page: p, perPage: ipp, search: Search.value || "" },
      });
      Finances.value = resp.data?.data ?? resp.data ?? [];
      totalItems.value = resp.data?.total ?? 0;
    } finally {
      loading.value = false;
    }
  }

  async function FetchFinanceData(id) {
    loading.value = true;
    try {
      const resp = await axios.get(`/finances/${id}`);
      Object.assign(Finance, resp.data || {});
    } finally {
      loading.value = false;
    }
  }

  async function CreateFinance(payload) {
    loading.value = true;
    try {
      await axios.post("/finances", payload);
    } finally {
      loading.value = false;
    }
  }

  async function UpdateFinance(id, payload) {
    loading.value = true;
    try {
      await axios.put(`/finances/${id}`, payload);
    } finally {
      loading.value = false;
    }
  }

  async function DeleteFinance(id) {
    loading.value = true;
    try {
      await axios.delete(`/finances/${id}`);
    } finally {
      loading.value = false;
    }
  }

  return {
    loading,

    // dialogs
    createDialog,
    updateDialog,
    openCreateDialog,
    openEditDialog,
    closeDialogs,

    // pagination & filters
    page,
    itemsPerPage,
    totalItems,
    Search,

    // data
    Finances,
    Finance,

    // editor
    editor,
    setBlankEditorRow,

    // api
    FetchFinancesData,
    FetchFinanceData,
    CreateFinance,
    UpdateFinance,
    DeleteFinance,
  };
});
