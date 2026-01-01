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
          />

          <!-- ✅ Actions (Create + PDF + Word) -->
          <div class="d-flex gap-2 flex-wrap">
            <v-btn color="error" prepend-icon="mdi-plus" @click="openCreate">ثبت مصرف</v-btn>

            <v-btn
              color="primary"
              variant="tonal"
              prepend-icon="mdi-file-pdf-box"
              @click="exportPdf"
            >
              PDF
            </v-btn>

            <v-btn
              color="primary"
              variant="tonal"
              prepend-icon="mdi-file-word-box"
              @click="exportWord"
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
          @edit="startEdit"
          @delete="askDelete"
        />
      </v-card-text>
    </v-card>

    <!-- Create/Edit Dialog -->
    <FinanceFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <!-- Delete confirm -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :title="'حذف مصرف'"
      :text="'آیا مطمئن هستید که این مصرف حذف شود؟'"
      @confirm="doDelete"
    />
  </v-container>
</template>

<script setup>
  import { computed, ref, onMounted } from 'vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import FinanceFormDialog from './FinancesFormDialog.vue';
  import { useFinanceRepository } from '@/stores/financeRepository';

  // RTL (replace with i18n)
  const rtl = ref(true);

  const repo = useFinanceRepository();

  const dialog = ref(false);
  const editId = ref(null);

  const deleteDialog = ref(false);
  const deleteId = ref(null);

  const accountOptions = computed(() => repo.accounts || []);

  /**
   * ✅ Table headers (visible in the app table)
   * Note: Your FilterableTable supports a flat header list.
   * The grouped headers (پول / منبع) are applied ONLY in export (PDF/Word) HTML.
   */
  const headersFT = [
    { title: 'شمېره', key: 'index', sortable: false, width: 80 },
    { title: 'د کارمند نوم', key: 'employee_name', sortable: true, width: 220 },

    { title: 'د پیسو اندازه (په عدد)', key: 'money_amount_number', sortable: true, width: 180 },
    { title: 'د پیسو اندازه (په تورو)', key: 'money_amount_words', sortable: false, width: 260 },

    { title: 'کود منبع', key: 'source_code', sortable: false, width: 160 },
    { title: 'شماره تماس منبع', key: 'source_phone', sortable: false, width: 180 },

    { title: 'د مصرف وجه/علت', key: 'expense_result', sortable: false, width: 200 },
    { title: 'د پیسو ورکولو نیټه', key: 'notes', sortable: false, width: 220 },

    { title: 'کټه', key: 'date', sortable: true, width: 140 },
    { title: 'لاسیلیک', key: 'signature', sortable: false, width: 120 },

    { title: 'عملیات', key: 'actions', sortable: false, width: 180 },
  ];

  function toDate10(v) {
    return (v ?? '').toString().slice(0, 10);
  }

  /**
   * ✅ Lightweight number-to-words (Dari)
   */
  function numberToDariWords(input) {
    const n = Number(
      String(input ?? '')
        .replace(/,/g, '')
        .trim()
    );
    if (!Number.isFinite(n)) return '';
    if (n === 0) return 'صفر';

    const ones = [
      '',
      'یک',
      'دو',
      'سه',
      'چهار',
      'پنج',
      'شش',
      'هفت',
      'هشت',
      'نه',
      'ده',
      'یازده',
      'دوازده',
      'سیزده',
      'چهارده',
      'پانزده',
      'شانزده',
      'هفده',
      'هجده',
      'نوزده',
    ];
    const tens = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
    const hundreds = [
      '',
      'یکصد',
      'دویست',
      'سیصد',
      'چهارصد',
      'پنجصد',
      'ششصد',
      'هفتصد',
      'هشتصد',
      'نهصد',
    ];

    const chunkToWords = (x) => {
      const out = [];
      if (x >= 100) {
        out.push(hundreds[Math.floor(x / 100)]);
        x = x % 100;
      }
      if (x >= 20) {
        out.push(tens[Math.floor(x / 10)]);
        x = x % 10;
      }
      if (x > 0 && x < 20) out.push(ones[x]);
      return out.filter(Boolean).join(' و ');
    };

    let x = Math.floor(Math.abs(n));
    const parts = [];

    const billion = Math.floor(x / 1_000_000_000);
    if (billion) {
      parts.push(`${chunkToWords(billion)} میلیارد`);
      x %= 1_000_000_000;
    }

    const million = Math.floor(x / 1_000_000);
    if (million) {
      parts.push(`${chunkToWords(million)} میلیون`);
      x %= 1_000_000;
    }

    const thousand = Math.floor(x / 1000);
    if (thousand) {
      parts.push(`${chunkToWords(thousand)} هزار`);
      x %= 1000;
    }

    if (x) parts.push(chunkToWords(x));

    return parts.join(' و ');
  }

  const rowsFT = computed(() => {
    const startIndex = (repo.financesPage - 1) * repo.financesPerPage;

    return (repo.finances || []).map((it, i) => {
      const amount = it?.money_amount_number ?? it?.amount ?? '';

      return {
        ...it,
        index: startIndex + i + 1,

        // employee name column (adjust if you want receiver)
        employee_name: it?.payer ?? it?.receiver ?? it?.employee_name ?? '',

        // money
        money_amount_number: amount,
        money_amount_words: numberToDariWords(amount),

        // source (منبع): prefer resource; fallback to account if your API uses account only
        source_code:
          it?.resource?.code_number ?? it?.account?.code_number ?? it?.source_code ?? '—',
        source_phone:
          it?.resource?.phone_number ?? it?.account?.phone_number ?? it?.source_phone ?? '—',

        // rest
        expense_result: it?.expense_result ?? '',
        notes: it?.notes ?? '',
        date: toDate10(it?.date),
        signature: it?.signature ?? '—',
      };
    });
  });

  // keep empty if you don't use them
  const filters = [];
  const topGroups = [];

  function fetchAll() {
    repo.FetchFinancesData({
      page: repo.financesPage,
      perPage: repo.financesPerPage,
      search: repo.financesSearch,
      account_id: repo.financesAccountId,
      office_employee_id: repo.financesOfficeEmployeeId, // optional
    });
  }

  async function ensureAccountsLoaded() {
    if (Array.isArray(repo.accounts) && repo.accounts.length) return;
    await repo.FetchAccountsData({ page: 1, perPage: 2000, search: '' });
  }

  function openCreate() {
    editId.value = null;
    dialog.value = true;
  }

  function startEdit(row) {
    editId.value = row?.id;
    dialog.value = true;
  }

  function onSaved() {
    fetchAll();
  }

  function askDelete(row) {
    deleteId.value = row?.id;
    deleteDialog.value = true;
  }

  async function doDelete() {
    if (!deleteId.value) return;
    await repo.DeleteFinance(deleteId.value);
    deleteDialog.value = false;
    deleteId.value = null;
    fetchAll();
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
   * ✅ Export HTML (PDF/Word) — grouped headers like your paper:
   * - پول => (عدد + تورو)
   * - منبع => (کود + شماره تماس)
   */
  function buildExportHtml() {
    const rows = rowsFT.value || [];

    const title = 'داوپراتیفي پیسو د مصرفي جدول';
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

    const tbody = rows
      .map((r) => {
        const esc = (v) =>
          String(v ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;');

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
      <title>${title}</title>
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
        <div class="title">${title}</div>
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
  </html>`;
  }

  function exportPdf() {
    const html = buildExportHtml();
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.open();
    w.document.write(html);
    w.document.close();
    w.focus();
    w.print();
  }

  function exportWord() {
    const html = buildExportHtml();
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
</style>
