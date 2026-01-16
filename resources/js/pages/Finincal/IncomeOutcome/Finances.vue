<!-- src/pages/Finances/FinancesIndex.vue -->
<template>
  <v-container fluid class="pt-6 page-bg" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- ✅ SUMMARY CARDS (Beautiful) -->
    <v-row class="mb-4" dense :dir="rtl ? 'rtl' : 'ltr'">
      <v-col cols="12" md="3">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">مجموع بیلانس همه حساب‌ها</div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(summary.total_balance_all_accounts) }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1 family">جمع کل حساب‌ها</div>
              </div>

              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-bank</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">مجموع عواید</div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(summary.total_income_all) }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1 family">جمع کل درآمد</div>
              </div>

              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-cash-plus</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">مجموع مصارف</div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(summary.total_outcome_all) }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1 family">جمع کل مصرف</div>
              </div>

              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-cash-minus</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3">
        <v-card rounded="xl" class="shadow-sm stat-card">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <div class="min-w-0">
                <div class="text-caption text-medium-emphasis family">مصارف همین لیست</div>
                <div class="text-h6 font-weight-bold stat-value">
                  {{ fmtMoney(summary.filtered_outcome) }}
                </div>
                <div class="text-caption text-medium-emphasis mt-1 family">همین صفحه/جستجو</div>
              </div>

              <div class="stat-icon-wrap">
                <v-icon size="26">mdi-filter</v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-card rounded="xl" class="shadow-sm mb-4 filters-card">
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

          <!-- ✅ Actions -->
          <div class="d-flex gap-2 flex-wrap">
            <v-btn color="error" prepend-icon="mdi-plus" class="text-none" @click="openCreate">
              ثبت مصرف
            </v-btn>

            <v-btn
              color="primary"
              variant="tonal"
              prepend-icon="mdi-file-pdf-box"
              class="text-none"
              @click="exportPdfAll"
            >
              PDF
            </v-btn>

            <v-btn
              color="primary"
              variant="tonal"
              prepend-icon="mdi-file-word-box"
              class="text-none"
              @click="exportWordAll"
            >
              Word
            </v-btn>
          </div>
        </div>
      </v-card-text>
    </v-card>

    <!-- Table -->
    <v-card rounded="xl" class="shadow-sm table-card">
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
          <template #actions="{ item }">
            <div class="d-flex align-center ga-2">
              <v-tooltip text="ویرایش مصرف" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-btn
                    v-bind="tooltipProps"
                    icon
                    size="x-small"
                    variant="tonal"
                    color="amber-darken-2"
                    class="icon-btn"
                    @click.stop="startEdit(item)"
                  >
                    <v-icon size="18">mdi-pencil</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>

              <v-tooltip text="حذف مصرف" location="top">
                <template #activator="{ props: tooltipProps }">
                  <v-btn
                    v-bind="tooltipProps"
                    icon
                    size="x-small"
                    variant="tonal"
                    color="red-darken-2"
                    class="icon-btn"
                    @click.stop="openDeleteDialog(item)"
                  >
                    <v-icon size="18">mdi-delete</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
            </div>
          </template>
        </FilterableTable>
      </v-card-text>
    </v-card>

    <!-- Create/Edit Dialog -->
    <FinanceFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <!-- Delete confirm -->
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

  const deleteDialog = ref(false);
  const deleteTarget = ref(null);

  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  const headersFT = [
    { title: 'دریافت کننده', key: 'employee_name', sortable: true },
    { title: 'پرداخت کننده', key: 'payer', sortable: true },

    { title: 'د پیسو اندازه (په عدد)', key: 'money_amount_number', sortable: true },
    { title: 'د پیسو اندازه (په تورو)', key: 'money_amount_words', sortable: false },

    { title: 'کود منبع', key: 'source_code', sortable: false, width: 160 },
    { title: 'شماره تماس منبع', key: 'source_phone', sortable: false },
    { title: 'شماره تماس کارمند', key: 'employee_phone', sortable: false },

    { title: 'د مصرف وجه/علت', key: 'expense_result', sortable: false, width: 200 },
    { title: 'د پیسو ورکولو نیټه', key: 'date', sortable: true },
  ];

  function toDate10(v) {
    return (v ?? '').toString().slice(0, 10);
  }

  const rowsFT = computed(() => {
    const startIndex = 0;

    return (repo.finances || []).map((it, i) => {
      const amount = it?.money_amount_number ?? it?.amount ?? '';

      return {
        ...it,
        index: startIndex + i + 1,

        employee_name:  it?.office_employee?.name ?? '',
        payer: it?.payer ??  it?.payer ?? '',

        money_amount_number: amount,
        money_amount_words: it?.money_amount_words ?? '',

        source_code: it?.resource?.code_number ?? it?.source_code ?? '—',
        source_nick_name: it?.resource?.nick_name ?? it?.source_nick_name ?? '—',
        source_phone: it?.resource?.phone_number ?? it?.source_phone ?? '—',
        employee_phone: it?.employee_phone ?? '—',

        expense_result: it?.expense_result ?? '',
        notes: it?.notes ?? '',
        date: toDate10(it?.date),
        signature: it?.signature ?? '—',
      };
    });
  });

  const filters = [];
  const topGroups = [];

  // ✅ summary from API
  const summary = computed(
    () =>
      repo.financesSummary || {
        total_balance_all_accounts: '0.00',
        total_income_all: '0.00',
        total_outcome_all: '0.00',
        filtered_outcome: '0.00',
      }
  );

  function fmtMoney(v) {
    const n = Number(v ?? 0);
    if (Number.isNaN(n)) return String(v ?? '');

    // if integer => show without .00
    if (Math.floor(n) === n) return String(n);

    // if has decimals => show as-is (trim trailing zeros)
    return String(n)
      .replace(/(\.\d*?)0+$/, '$1')
      .replace(/\.$/, '');
  }

  function fetchAll() {
    repo.FetchFinancesData({
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

  // export html
  function buildExportHtml(rows, title) {
    const year = new Date().getFullYear();

    const thead = `
    <tr>
      <th rowspan="2">شمېره</th>
      <th rowspan="2">د کارمند نوم</th>

      <th colspan="2">پیسو اندازه</th>
      <th colspan="2">منبع</th>

      <th rowspan="2">د پیسو ورکولو نیټه</th>

      <th rowspan="2">د کارمند دتلیفون شمیره</th>
      <th rowspan="2">د مصرف وجه/علت</th>

      <th rowspan="2">کتنه</th>
      <th rowspan="2">لاسیلیک</th>
    </tr>

    <tr>
      <th>په عدد</th>
      <th>په تورو</th>
      <th>مستعار نوم</th>
      <th>کود</th>
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
          <td>${esc(r.source_nick_name)}</td>
          <td>${esc(r.source_code)}</td>
          <td>${esc(r.date)}</td>
          <td>${esc(r.employee_phone)}</td>

          <td>${esc(r.expense_result)}</td>
          <td>${esc(r.notes)}</td>
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
          <div>د معتمد لاسلیک</div>
          <div>مدیر/آمر لاسلیک</div>
          <div>د رئیس لاسلیک</div>
          <div>داوپراتیفي تفتیش هیئت لاسلیک</div>
        </div>
      </body>
    </html>
  `;
  }

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
  /* subtle page background */
  .page-bg {
    min-height: 100%;
  }

  /* gaps */
  .gap-2 {
    gap: 8px;
  }

  /* Summary cards */
  .stat-card {
    border: 1px solid rgba(0, 0, 0, 0.05);
    overflow: hidden;
    position: relative;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.07), rgba(156, 39, 176, 0.05));
    pointer-events: none;
  }

  .stat-value {
    letter-spacing: 0.2px;
  }

  .stat-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(25, 118, 210, 0.08);
  }

  /* Filters + Table cards polish */
  .filters-card,
  .table-card {
    border: 1px solid rgba(0, 0, 0, 0.05);
  }

  /* action icon buttons */
  .icon-btn {
    border-radius: 10px;
  }

  /* keep table padding nice */
  :deep(.v-table) {
    border-radius: 16px;
  }
  /* ✅ Finance/Income/Accounts summary cards font + look */
  .finance-cards {
    font-family: var(--app-font) !important;
  }

  .finance-card {
    font-family: var(--app-font) !important;
    border: 1px solid rgba(0, 0, 0, 0.06);
    overflow: hidden;
    position: relative;
  }

  .finance-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.07), rgba(156, 39, 176, 0.05));
    pointer-events: none;
  }

  .finance-card .finance-card__value {
    font-family: var(--app-font) !important;
    letter-spacing: 0.2px;
  }

  .finance-card .finance-card__icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(25, 118, 210, 0.08);
  }
  .family {
    font-family: var(--app-font) !important;
  }
</style>
