<template>
  <v-container fluid class="pt-6" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- Filters -->
    <v-card rounded="xl" class="shadow-sm mb-4">
      <v-card-text class="pa-4">
        <div class="d-flex align-center justify-space-between flex-wrap gap-2">
          <v-text-field
            v-model="repo.financesSearch"
            label="جستجو"
            density="comfortable"
            variant="underlined"
            prepend-inner-icon="mdi-magnify"
            @keyup.enter="fetchAll"
            clearable
            @click:clear="clearSearch"
          />

          <!-- ✅ Actions (Create + PDF + Word) -->
          <div class="d-flex gap-2 flex-wrap">
            <v-btn color="error" prepend-icon="mdi-plus" @click="openCreate">ثبت مصرف</v-btn>

            <v-btn
              color="primary"
              variant="tonal"
              prepend-icon="mdi-file-pdf-box"
              @click="exportPdfAll"
            >
              PDF
            </v-btn>

            <v-btn
              color="primary"
              variant="tonal"
              prepend-icon="mdi-file-word-box"
              @click="exportWordAll"
            >
              Word
            </v-btn>
          </div>
        </div>
      </v-card-text>
    </v-card>

    <!-- Table -->
    <v-card rounded="xl" class="shadow-sm">
      <v-card-text class="pa-0">
        <FilterableTable
          :loading="repo.loading"
          :headers="headersFT"
          :rows="rowsFT"
          :filters="filters"
          :topGroups="topGroups"
          :totalItems="repo.financesTotalItems"
          :page="repo.financesPage"
          :perPage="repo.financesPerPage"
          @update:page="onPage"
          @update:perPage="onPerPage"
        >
          <!-- ✅ Actions like Informations -->
          <template #actions="{ item }">
            <div class="d-flex align-center ga-2">
              <!-- ویرایش -->
              <v-tooltip text="ویرایش مصرف" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-icon
                    size="small"
                    v-bind="tooltipProps"
                    color="amber-darken-2"
                    @click.stop="startEdit(item)"
                  >
                    mdi-pencil
                  </v-icon>
                </template>
              </v-tooltip>

              <!-- حذف -->
              <v-tooltip text="حذف مصرف" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-icon
                    size="small"
                    v-bind="tooltipProps"
                    color="red-darken-2"
                    @click.stop="openDeleteDialog(item)"
                  >
                    mdi-delete
                  </v-icon>
                </template>
              </v-tooltip>
            </div>
          </template>
        </FilterableTable>
      </v-card-text>
    </v-card>

    <!-- Create/Edit Dialog -->
    <FinanceFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <!-- ✅ Delete confirm (Informations style) -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="rtl"
      @confirm="handleDeleteConfirmed"
    />
  </v-container>
</template>

<script setup>
  import { computed, ref, onMounted } from 'vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import FinanceFormDialog from './FinancesFormDialog.vue';
  import { useFinanceRepository } from '@/stores/financeRepository';

  const rtl = ref(true);
  const repo = useFinanceRepository();

  const dialog = ref(false);
  const editId = ref(null);

  // ✅ delete (Informations style)
  const deleteDialog = ref(false);
  const deleteTarget = ref(null);

  /* helper to unwrap row/raw like Informations */
  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  const headersFT = [
    { title: 'د کارمند نوم', key: 'employee_name', sortable: true},

    { title: 'د پیسو اندازه (په عدد)', key: 'money_amount_number', sortable: true},
    { title: 'د پیسو اندازه (په تورو)', key: 'money_amount_words', sortable: false },

    { title: 'کود منبع', key: 'source_code', sortable: false, width: 160 },
    { title: 'شماره تماس منبع', key: 'source_phone', sortable: false},

    { title: 'د مصرف وجه/علت', key: 'expense_result', sortable: false, width: 200 },

    { title: 'د پیسو ورکولو نیټه', key: 'date', sortable: true }
  ];

  function toDate10(v) {
    return (v ?? '').toString().slice(0, 10);
  }

  // ✅ REMOVED: numberToDariWords()
  // ✅ REMOVED: auto money_amount_words generation

  const rowsFT = computed(() => {
    const startIndex = (repo.financesPage - 1) * repo.financesPerPage;

    return (repo.finances || []).map((it, i) => {
      const amount = it?.money_amount_number ?? it?.amount ?? '';

      return {
        ...it,
        index: startIndex + i + 1,

        employee_name: it?.payer ?? it?.receiver ?? it?.employee_name ?? '',

        // money
        money_amount_number: amount,
        // ✅ keep whatever backend returns, do NOT overwrite
        money_amount_words: it?.money_amount_words ?? '',

        // source
        source_code:
          it?.resource?.code_number ?? it?.account?.code_number ?? it?.source_code ?? '—',
        source_phone:
          it?.resource?.phone_number ?? it?.account?.phone_number ?? it?.source_phone ?? '—',

        expense_result: it?.expense_result ?? '',
        notes: it?.notes ?? '',
        date: toDate10(it?.date),
        signature: it?.signature ?? '—',
      };
    });
  });

  const filters = [];
  const topGroups = [];

  function fetchAll() {
    repo.FetchFinancesData({
      page: repo.financesPage,
      perPage: repo.financesPerPage,
      search: repo.financesSearch,
      account_id: repo.financesAccountId,
      office_employee_id: repo.financesOfficeEmployeeId,
    });
  }

  function clearSearch() {
    repo.financesSearch = '';
    fetchAll();
  }

  async function ensureAccountsLoaded() {
    if (Array.isArray(repo.accounts) && repo.accounts.length) return;
    await repo.FetchAccountsData({ page: 1, perPage: 2000, search: '' });
  }

  function openCreate() {
    editId.value = null;
    dialog.value = true;
  }

  function startEdit(payload) {
    const row = getRow(payload);
    editId.value = row?.id ?? null;
    if (!editId.value) return;
    dialog.value = true;
  }

  function onSaved() {
    fetchAll();
  }

  // ✅ DELETE like Informations
  function openDeleteDialog(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    deleteTarget.value = row;
    deleteDialog.value = true;
  }

  async function handleDeleteConfirmed(target) {
    const row = target || deleteTarget.value;
    if (!row?.id) {
      deleteDialog.value = false;
      deleteTarget.value = null;
      return;
    }

    try {
      await repo.DeleteFinance(row.id);
      await fetchAll();
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  function onPage(p) {
    repo.financesPage = p;
    fetchAll();
  }

  function onPerPage(pp) {
    repo.financesPerPage = pp;
    repo.financesPage = 1;
    fetchAll();
  }

  /**
   * ✅ build export (ALL rows)
   * keep grouped headers like before
   */
  function buildExportHtml(rows, title) {
    const year = new Date().getFullYear();

    const thead = `
      <tr>
        <th rowspan="2">شمېره</th>
        <th rowspan="2">د کارمند نوم</th>

        <th colspan="2">پول</th>
        <th colspan="2">منبع</th>

        <th rowspan="2">د مصرف وجه/علت</th>
        <th rowspan="2">د پیسو ورکولو نیټه</th>
        <th rowspan="2">کټه</th>
        <th rowspan="2">لاسیلیک</th>
      </tr>

      <tr>
        <th>د پیسو اندازه (په عدد)</th>
        <th>د پیسو اندازه (په تورو)</th>

        <th>کود</th>
        <th>شماره تماس</th>
      </tr>
    `;

    const esc = (v) =>
      String(v ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');

    const tbody = (rows || [])
      .map((r) => {
        return `
          <tr>
            <td>${esc(r.index)}</td>
            <td>${esc(r.employee_name)}</td>

            <td>${esc(r.money_amount_number)}</td>
            <td>${esc(r.money_amount_words)}</td>

            <td>${esc(r.source_code)}</td>
            <td>${esc(r.source_phone)}</td>

            <td>${esc(r.expense_result)}</td>
            <td>${esc(r.notes)}</td>
            <td>${esc(r.date)}</td>
            <td>${esc(r.signature)}</td>
          </tr>
        `;
      })
      .join('');

    return `
      <!doctype html>
      <html>
        <head>
          <meta charset="utf-8" />
          <title>${esc(title)}</title>
          <style>
            body { font-family: Arial, Tahoma, sans-serif; direction: rtl; padding: 18px; }
            .top { display:flex; justify-content: space-between; align-items:center; margin-bottom: 10px; }
            .title { font-weight: 700; font-size: 18px; text-align:center; flex:1; }
            .year { font-size: 13px; white-space: nowrap; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #111; padding: 6px 8px; font-size: 12px; vertical-align: middle; }
            th { font-weight: 700; text-align: center; }
            td { text-align: right; vertical-align: top; }
            .signs { display:flex; justify-content: space-between; margin-top: 18px; font-size: 12px; }
            .signs > div { width: 24%; text-align: center; }
          </style>
        </head>
        <body>
          <div class="top">
            <div class="year">کال: ${year}</div>
            <div class="title">${esc(title)}</div>
            <div style="width:120px"></div>
          </div>

          <table>
            <thead>${thead}</thead>
            <tbody>${
              tbody || "<tr><td colspan='10' style='text-align:center'>هیڅ معلومات نشته</td></tr>"
            }</tbody>
          </table>

          <div class="signs">
            <div>د مستند لاسلیک</div>
            <div>مدیر/امر لاسلیک</div>
            <div>د رئیس لاسلیک</div>
            <div>داوپراتیف تفتیش هیئت لاسلیک</div>
          </div>
        </body>
      </html>
    `;
  }

  // ✅ Export ALL
  function exportPdfAll() {
    const html = buildExportHtml(rowsFT.value || [], 'داوپراتیفي پیسو د مصرفي جدول');
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.open();
    w.document.write(html);
    w.document.close();
    w.focus();
    w.print();
  }

  function exportWordAll() {
    const html = buildExportHtml(rowsFT.value || [], 'داوپراتیفي پیسو د مصرفي جدول');
    const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `مصارف_${new Date().toISOString().slice(0, 10)}.doc`;
    document.body.appendChild(a);
    a.click();
    a.remove();

    setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  onMounted(async () => {
    await ensureAccountsLoaded();
    fetchAll();
  });
</script>

<style scoped>
  .gap-2 {
    gap: 8px;
  }

  :deep(.v-btn--icon) {
    transition: all 0.2s ease;
  }
  :deep(.v-btn--icon:hover) {
    transform: scale(1.1);
  }
</style>
