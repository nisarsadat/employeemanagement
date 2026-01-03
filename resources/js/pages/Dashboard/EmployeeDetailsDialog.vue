<!-- src/components/dialogs/EmployeeDetailsDialog.vue -->
<template>
  <v-dialog v-model="model" max-width="920" scrollable :scrim="true">
    <v-card rounded="xl" class="details-card" :dir="rtl ? 'rtl' : 'ltr'">
      <!-- Header -->
      <div class="px-5 pt-5 pb-3 d-flex align-center justify-space-between">
        <div class="d-flex align-center" style="gap: 12px;">
          <v-avatar size="46" class="avatar-glow">
            <span class="text-white font-weight-bold">
              {{ initials(employee?.name) }}
            </span>
          </v-avatar>

          <div class="min-w-0">
            <div class="text-h6 font-weight-black text-truncate">
              {{ employee?.name || '—' }}
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ t('کود') }}: {{ employee?.code_number || '—' }}
              <span class="mx-2">•</span>
              {{ t('رتبه') }}: {{ employee?.rank ?? '—' }}
            </div>
          </div>
        </div>

        <div class="d-flex align-center" style="gap: 8px;">
          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon
                variant="text"
                :disabled="loading"
                @click="copyText(String(employee?.code_number || ''))"
              >
                <v-icon>mdi-content-copy</v-icon>
              </v-btn>
            </template>
            <span>{{ t('کپی کود') }}</span>
          </v-tooltip>

          <v-btn icon variant="text" @click="close">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
      </div>

      <v-divider />

      <!-- Body -->
      <v-card-text class="pa-5">
        <div v-if="loading" class="py-10 text-center">
          <v-progress-circular indeterminate size="52" color="primary" class="mb-4" />
          <div class="text-body-2 text-medium-emphasis">{{ t('در حال بارگذاری...') }}</div>
        </div>

        <template v-else>
          <!-- ✅ Clickable route cards inside popup -->
          <div class="popup-cards">
            <!-- Employee Details -->
            <div
              class="popup-card"
              role="button"
              tabindex="0"
              @click="goEmployeeDetails"
              @keydown.enter="goEmployeeDetails"
            >
              <div class="pc-icon primary">
                <v-icon color="primary">mdi-account</v-icon>
              </div>
              <div class="pc-body">
                <div class="pc-title">{{ t('مشاهده ') }}</div>
                <div class="pc-sub">{{ t('جزئیات کامل کارمند') }}</div>
              </div>
              <v-icon class="pc-arrow" color="primary">mdi-chevron-left</v-icon>
            </div>

            <!-- Resources -->
            <div
              class="popup-card"
              role="button"
              tabindex="0"
              @click="goResources"
              @keydown.enter="goResources"
            >
              <div class="pc-icon teal">
                <v-icon color="teal">mdi-folder</v-icon>
              </div>
              <div class="pc-body">
                <div class="pc-title">{{ t('منابع') }}</div>
                <div class="pc-sub">
                  {{ t('تعداد') }}: {{ num(employee?.resources_count) }}
                </div>
              </div>
              <v-icon class="pc-arrow" color="teal">mdi-chevron-left</v-icon>
            </div>

            <!-- Informations -->
            <div
              class="popup-card"
              role="button"
              tabindex="0"
              @click="goInformations"
              @keydown.enter="goInformations"
            >
              <div class="pc-icon indigo">
                <v-icon color="indigo">mdi-file-document</v-icon>
              </div>
              <div class="pc-body">
                <div class="pc-title">{{ t('اطلاعات') }}</div>
                <div class="pc-sub">
                  {{ t('تعداد') }}: {{ num(employee?.informations_count) }}
                </div>
              </div>
              <v-icon class="pc-arrow" color="indigo">mdi-chevron-left</v-icon>
            </div>

            <!-- Events -->
            <div
              class="popup-card"
              role="button"
              tabindex="0"
              @click="goEvents"
              @keydown.enter="goEvents"
            >
              <div class="pc-icon cyan">
                <v-icon color="teal-darken-1">mdi-calendar</v-icon>
              </div>
              <div class="pc-body">
                <div class="pc-title">{{ t('رویدادها') }}</div>
                <div class="pc-sub">
                  {{ t('تعداد') }}: {{ num(employee?.events_count) }}
                </div>
              </div>
              <v-icon class="pc-arrow" color="teal-darken-1">mdi-chevron-left</v-icon>
            </div>
          </div>

          <!-- Metrics -->
          <div class="metrics-grid mt-5">
            <div class="metric-box">
              <div class="metric-title">{{ t('منابع') }}</div>
              <div class="metric-value">{{ num(employee?.resources_count) }}</div>
            </div>

            <div class="metric-box">
              <div class="metric-title">{{ t('اطلاعات') }}</div>
              <div class="metric-value">{{ num(employee?.informations_count) }}</div>
            </div>

            <div class="metric-box">
              <div class="metric-title">{{ t('رویدادها') }}</div>
              <div class="metric-value">{{ num(employee?.events_count) }}</div>
            </div>

            <div class="metric-box">
              <div class="metric-title">{{ t('امتیاز') }}</div>
              <div class="metric-value">{{ num(employee?.score) }}</div>
            </div>
          </div>

          <!-- Score bar -->
          <div class="mt-5">
            <div class="d-flex align-center justify-space-between mb-2">
              <div class="text-body-2 font-weight-bold">{{ t('امتیاز') }}</div>
              <div class="text-caption text-medium-emphasis">{{ scoreLabel(employee?.score) }}</div>
            </div>

            <v-progress-linear
              :model-value="scorePercentage(employee?.score)"
              height="10"
              rounded
              :color="scoreColor(employee?.score)"
              bg-color="rgba(0,0,0,0.06)"
            />
          </div>

          <v-divider class="my-5" />

          <!-- Basic details -->
          <div class="details-grid">
            <div class="detail-row">
              <div class="label">{{ t('نام') }}</div>
              <div class="value">{{ employee?.name || '—' }}</div>
            </div>

            <div class="detail-row">
              <div class="label">{{ t('کود') }}</div>
              <div class="value">{{ employee?.code_number || '—' }}</div>
            </div>

            <div class="detail-row">
              <div class="label">{{ t('رتبه') }}</div>
              <div class="value">{{ employee?.rank ?? '—' }}</div>
            </div>

            <div class="detail-row">
              <div class="label">{{ t('امتیاز') }}</div>
              <div class="value">{{ num(employee?.score) }}</div>
            </div>
          </div>
        </template>
      </v-card-text>

      <v-divider />

      <!-- Footer -->
      <div class="px-5 py-4 d-flex align-center justify-end" style="gap: 10px;">
        <!-- ✅ Added: PDF / Word (simple table export) -->
        <v-tooltip location="top">
          <template #activator="{ props }">
            <v-btn
              v-bind="props"
              variant="tonal"
              prepend-icon="mdi-file-pdf-box"
              :disabled="loading || !employee"
              @click="exportPdf"
            >
              {{ t('PDF') }}
            </v-btn>
          </template>
          <span>{{ t('دانلود PDF') }}</span>
        </v-tooltip>

        <v-tooltip location="top">
          <template #activator="{ props }">
            <v-btn
              v-bind="props"
              variant="tonal"
              prepend-icon="mdi-file-word-box"
              :disabled="loading || !employee"
              @click="exportWord"
            >
              {{ t('Word') }}
            </v-btn>
          </template>
          <span>{{ t('دانلود Word') }}</span>
        </v-tooltip>

        <v-btn variant="text" @click="close">{{ t('بستن') }}</v-btn>
      </div>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  employee: { type: Object, default: null },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'close']);

const { t, locale } = useI18n();
const router = useRouter();

const rtl = computed(() => String(locale.value).toLowerCase().startsWith('fa'));

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
});

function close() {
  model.value = false;
  emit('close');
}

/* ✅ safe navigation (won’t route while loading) */
function go(path, query = {}) {
  if (props.loading) return;
  close(); // close popup before navigating
  router.push({ path, query });
}

/* ✅ ROUTES (edit paths if yours are different) */
function goEmployeeDetails() {
  const id = props.employee?.id;
  if (!id) return;
  go(`/app/officeemployee/${id}`);
}

function goResources() {
  go('/app/resources');
}

function goInformations() {
  go('/app/informations');
}

function goEvents() {
  go('/app/events');
}

/* Helpers */
function num(v) {
  const n = Number(v);
  return Number.isFinite(n) ? n.toLocaleString() : '0';
}

function initials(name) {
  if (!name) return '—';
  return String(name)
    .split(' ')
    .filter(Boolean)
    .map((w) => w[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
}

async function copyText(text) {
  try {
    if (!text) return;
    await navigator.clipboard.writeText(text);
  } catch (_) {
    // ignore
  }
}

/* Score helpers */
function scorePercentage(score) {
  const s = Number(score) || 0;
  const maxScore = Math.max(s, 100);
  return Math.min((s / maxScore) * 100, 100);
}
function scoreColor(score) {
  const p = scorePercentage(score);
  if (p >= 80) return 'success';
  if (p >= 60) return 'primary';
  if (p >= 40) return 'warning';
  return 'error';
}
function scoreLabel(score) {
  const p = scorePercentage(score);
  if (p >= 80) return t('عالی');
  if (p >= 60) return t('خوب');
  if (p >= 40) return t('متوسط');
  return t('نیازمند بهبود');
}

/* ---------------------------
   ✅ Simple Table Export (PDF/Word)
   - PDF: window.print() from a clean HTML document
   - Word: msword blob (HTML)
   - Simple 2-column table label/value
---------------------------- */
function escapeHtml(v) {
  return String(v ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function buildRows() {
  const e = props.employee || {};
  return [
    { label: t('نام'), value: e.name ?? '—' },
    { label: t('کود'), value: e.code_number ?? '—' },
    { label: t('رتبه'), value: e.rank ?? '—' },
    { label: t('امتیاز'), value: num(e.score) },
    { label: t('منابع'), value: num(e.resources_count) },
    { label: t('اطلاعات'), value: num(e.informations_count) },
    { label: t('رویدادها'), value: num(e.events_count) },
  ];
}

function exportHtmlDocument() {
  const e = props.employee || {};
  const dir = rtl.value ? 'rtl' : 'ltr';
  const lang = rtl.value ? 'fa' : 'en';
  const title = `${t('پروفایل کارمند')} - ${e.name || ''}`.trim();

  const rows = buildRows()
    .map(
      (r) => `
        <tr>
          <th>${escapeHtml(r.label)}</th>
          <td>${escapeHtml(r.value)}</td>
        </tr>
      `
    )
    .join('');

  // ✅ Simple clean doc: no images, no app header UI; only title + table + signatures
  return `
<!doctype html>
<html lang="${escapeHtml(lang)}" dir="${escapeHtml(dir)}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${escapeHtml(title)}</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; margin: 28px; color: #111; }
    h1 { font-size: 18px; margin: 0 0 12px 0; }
    .meta { font-size: 12px; color: #555; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 10px 12px; vertical-align: top; }
    th { width: 32%; text-align: start; background: #f6f6f6; font-weight: 700; }
    td { text-align: start; }
    .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 26px; }
    .line { border-top: 1px solid #333; padding-top: 8px; font-size: 12px; color: #333; }
    @media print {
      body { margin: 14mm; }
      a { color: inherit; text-decoration: none; }
    }
  </style>
</head>
<body>
  <h1>${escapeHtml(t('پروفایل کارمند'))}</h1>
  <div class="meta">${escapeHtml(t('نام'))}: ${escapeHtml(e.name || '—')} &nbsp; • &nbsp; ${escapeHtml(t('کود'))}: ${escapeHtml(e.code_number || '—')}</div>

  <table>
    <tbody>
      ${rows}
    </tbody>
  </table>

  <div class="sign">
    <div class="line">${escapeHtml(t('امضا'))}</div>
    <div class="line">${escapeHtml(t('تاریخ'))}</div>
  </div>
</body>
</html>
  `.trim();
}

function openPrintWindow(html, title = 'print') {
  const w = window.open('', '_blank');
  if (!w) return;
  w.document.open();
  w.document.write(html);
  w.document.close();
  // Ensure styles loaded before printing
  w.onload = () => {
    w.document.title = title;
    w.focus();
    w.print();
    // optional: w.close();
  };
}

function downloadBlob(content, mime, filename) {
  const blob = new Blob([content], { type: mime });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

function safeFileName(name) {
  return String(name || 'employee')
    .trim()
    .replace(/[\\/:*?"<>|]+/g, '-')
    .replace(/\s+/g, '_')
    .slice(0, 80);
}

function exportPdf() {
  if (props.loading || !props.employee) return;
  const e = props.employee;
  const html = exportHtmlDocument();
  const fname = `Employee_${safeFileName(e?.name)}_${e?.code_number || ''}.pdf`.replace(/_+\.pdf$/, '.pdf');
  // PDF via print dialog (user can save as PDF)
  openPrintWindow(html, fname);
}

function exportWord() {
  if (props.loading || !props.employee) return;
  const e = props.employee;
  const html = exportHtmlDocument();

  // Word-compatible HTML
  const wordHtml =
    `<!doctype html><html><head><meta charset="utf-8"></head><body>${html
      .replace(/^[\s\S]*<body>/i, '')
      .replace(/<\/body>[\s\S]*$/i, '')}</body></html>`;

  const fname = `Employee_${safeFileName(e?.name)}_${e?.code_number || ''}.doc`.replace(/_+\.doc$/, '.doc');
  downloadBlob(wordHtml, 'application/msword;charset=utf-8', fname);
}
</script>

<style scoped>
.details-card {
  border: 1px solid rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.avatar-glow {
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-primary), 1),
    rgba(var(--v-theme-secondary), 1)
  );
  box-shadow: 0 10px 22px rgba(var(--v-theme-primary), 0.22);
}

/* ✅ popup route cards */
.popup-cards {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.popup-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 14px;
  border-radius: 18px;
  border: 1px solid rgba(0,0,0,0.08);
  background: rgba(0,0,0,0.02);
  cursor: pointer;
  transition: transform 0.14s ease, box-shadow 0.14s ease, border-color 0.14s ease;
  user-select: none;
}
.popup-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 30px rgba(0,0,0,0.10);
  border-color: rgba(var(--v-theme-primary), 0.22);
}

.pc-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(var(--v-theme-primary), 0.10);
}
.pc-icon.primary { background: rgba(var(--v-theme-primary), 0.10); }
.pc-icon.teal { background: rgba(var(--v-theme-teal), 0.10); }
.pc-icon.indigo { background: rgba(var(--v-theme-indigo), 0.10); }
.pc-icon.cyan { background: rgba(var(--v-theme-teal), 0.10); }

.pc-body {
  min-width: 0;
  flex: 1;
}
.pc-title {
  font-weight: 900;
  font-size: 0.95rem;
  line-height: 1.2;
}
.pc-sub {
  margin-top: 4px;
  font-size: 0.78rem;
  color: rgba(0,0,0,0.62);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pc-arrow {
  opacity: 0.85;
}

/* Metrics */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
}

.metric-box {
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 16px;
  padding: 12px 14px;
  background: rgba(0, 0, 0, 0.02);
}

.metric-title {
  font-size: 0.8rem;
  color: rgba(0, 0, 0, 0.62);
  margin-bottom: 6px;
}

.metric-value {
  font-size: 1.25rem;
  font-weight: 900;
}

/* Details */
.details-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px 16px;
}

.detail-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 10px 12px;
  border: 1px dashed rgba(0, 0, 0, 0.08);
  border-radius: 14px;
  background: white;
}

.label {
  font-size: 0.82rem;
  color: rgba(0, 0, 0, 0.62);
  white-space: nowrap;
}

.value {
  font-weight: 800;
  text-align: end;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (max-width: 960px) {
  .popup-cards {
    grid-template-columns: 1fr;
  }
  .metrics-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .details-grid {
    grid-template-columns: 1fr;
  }
}

/* Dark mode */
:deep(.v-theme--dark) .details-card {
  border-color: rgba(255, 255, 255, 0.08);
  background: #1e1e1e;
}
:deep(.v-theme--dark) .popup-card {
  border-color: rgba(255,255,255,0.10);
  background: rgba(255,255,255,0.04);
}
:deep(.v-theme--dark) .pc-sub {
  color: rgba(255,255,255,0.70);
}
:deep(.v-theme--dark) .metric-box {
  border-color: rgba(255, 255, 255, 0.10);
  background: rgba(255, 255, 255, 0.04);
}
:deep(.v-theme--dark) .metric-title {
  color: rgba(255, 255, 255, 0.70);
}
:deep(.v-theme--dark) .detail-row {
  border-color: rgba(255, 255, 255, 0.10);
  background: rgba(255, 255, 255, 0.03);
}
:deep(.v-theme--dark) .label {
  color: rgba(255, 255, 255, 0.70);
}
</style>
