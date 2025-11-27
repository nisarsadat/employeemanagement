<template>
  <v-expand-transition>
    <div v-if="expanded" class="inline-panel" :style="{ direction: dir }">
      <v-card class="simple-panel">
        <!-- سرلیک -->
        <div class="panel-header">
          <div class="header-content">
            <div class="title-section">
              <v-icon icon="mdi-information" class="mr-2 ml-12" size="20" />
              <strong>{{ titleText }}</strong>
            </div>
            <v-btn
              v-if="closable"
              icon
              size="small"
              variant="text"
              color="white"
              :title="'تړل'"
              @click="close"
            >
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </div>

        <v-divider />

        <!-- منځپانګه -->
        <div class="panel-body">
          <template v-if="loading">
            <div class="loading-state">
              <v-progress-circular indeterminate size="24" width="2" color="primary" />
              <span>لوډ کېږي...</span>
            </div>
          </template>

          <template v-else>
            <div class="three-cols">
              <!-- ستنه ۱: انځور (نور د شمېرنو بټنونه نشته) -->
              <div class="col-left" :style="{ '--image-w': imageWidth, '--image-h': imageHeight }">
                <div v-if="showBodyImage && hasImage" class="image-container">
                  <v-img :src="imageSrc" class="profile-image" cover>
                    <template #placeholder>
                      <div class="image-placeholder">
                        <v-icon icon="mdi-account" size="32" />
                      </div>
                    </template>
                  </v-img>
                </div>
              </div>

              <!-- ستنې ۲ او ۳: فیلډونه د “لیبل: ارزښت” په بڼه -->
              <div class="col-right">
                <div class="kv-grid">
                  <div v-for="(f, idx) in fieldsToShow" :key="idx" class="kv-row">
                    <span class="kv-label">{{ f.title }}</span> <span class="kv-sep">:</span>
                    <span class="kv-value">{{ displayValue(f.key) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>

        <v-divider />

        <!-- پای پټه -->
        <div class="panel-footer">
          <v-btn color="primary" variant="flat" size="large" @click="downloadPdf">
            PDF ښکته‌کول
          </v-btn>
          <v-spacer />
          <v-btn v-if="closable" color="primary" variant="tonal" size="large" @click="close">
            تړل
          </v-btn>
        </div>
      </v-card>
    </div>
  </v-expand-transition>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCalendar } from '../../composables/useCalendar';

const props = defineProps({
  id: { type: [String, Number], required: true },
  item: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  modelValue: { type: Boolean, default: false },

  title: { type: String, default: '' },
  titleKey: { type: String, default: '' },

  imageKey: { type: String, default: '' },
  fields: { type: Array, default: () => [] }, // [{ title, key }]

  dir: { type: String, default: 'rtl' },
  showBodyImage: { type: Boolean, default: true },
  closable: { type: Boolean, default: true },
  fileBaseUrl: { type: String, default: '' },

  imageWidth: { type: String, default: '40vh' },
  imageHeight: { type: String, default: '65vh' },

  dateKeys: {
    type: Array,
    default: () => [
      'join_date',
      'left_date',
      'birth_date',
      'date',
      'start_date',
      'end_date',
      'created_at',
      'updated_at',
    ],
  },
  excludeKeys: {
    type: Array,
    default: () => ['documents', 'picture', 'file_path', 'file', 'url', 'action'],
  },
});

const emit = defineEmits(['update:modelValue', 'fetch', 'close']);
const { locale } = useI18n();
const { formatDisplay } = useCalendar();

/* state */
const expanded = ref(props.modelValue);
watch(
  () => props.modelValue,
  (v) => (expanded.value = v)
);
watch(expanded, (v) => emit('update:modelValue', v));
function close() {
  expanded.value = false;
  emit('close');
}
function onKeydown(e) {
  if (!expanded.value) return;
  if (e.key === 'Escape' || e.key === 'Esc') close();
}
onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));

/* helpers */
function getByPath(obj, path) {
  if (!obj || !path) return undefined;
  return path.includes('.')
    ? path.split('.').reduce((o, k) => (o == null ? undefined : o[k]), obj)
    : obj[path];
}
const hasImage = computed(() => !!(props.imageKey && getByPath(props.item, props.imageKey)));

const titleText = computed(() => {
  if (props.title) return props.title;
  if (props.titleKey) {
    const t = getByPath(props.item, props.titleKey);
    if (t != null && t !== '') return String(t);
  }
  return 'جزییات';
});

function resolveImageUrl(src) {
  if (!src) return null;
  if (/^(blob:|data:|https?:\/\/)/i.test(src)) return src;
  const base =
    props.fileBaseUrl || import.meta.env.VITE_FILE_BASE_URL || import.meta.env.VITE_API_URL || '';
  if (!base) return src;
  return `${String(base).replace(/\/+$/, '')}/${String(src).replace(/^\/+/, '')}`;
}
const imageSrc = computed(() => resolveImageUrl(getByPath(props.item, props.imageKey)));

/* dates everywhere */
const ISO_STRICT = /^\d{4}-\d{2}-\d{2}$/;
function isDateKeyOrIso(key, val) {
  return (
    (key && props.dateKeys.includes(key)) || (typeof val === 'string' && ISO_STRICT.test(val))
  );
}
function displayValue(key) {
  const raw = getByPath(props.item, key);
  if (isDateKeyOrIso(key, raw)) return formatDisplay(raw, locale.value || 'fa') || '—';
  return raw == null || raw === '' ? '—' : String(raw);
}

/* show fields except excluded and image/doc paths */
const fieldsToShow = computed(() =>
  (props.fields || []).filter((f) => {
    const k = (f?.key || '').toString();
    if (!k) return false;
    if (props.imageKey && k === props.imageKey) return false;
    if (props.excludeKeys.some((ex) => k.startsWith(ex))) return false;
    return true;
  })
);

/* ================= PDF (ساده، خوښ شکل، RTL، پښتو) ================ */
async function b64(path) {
  const res = await fetch(path, { cache: 'no-cache' });
  if (!res.ok) throw new Error(`Font fetch failed ${res.status} @ ${path}`);
  const buf = await res.arrayBuffer();
  let bin = '',
    bytes = new Uint8Array(buf),
    chunk = 0x8000;
  for (let i = 0; i < bytes.length; i += chunk) {
    bin += String.fromCharCode.apply(null, bytes.subarray(i, i + chunk));
  }
  return btoa(bin);
}
async function imgB64(url) {
  if (!url) return null;
  const res = await fetch(url, { cache: 'no-cache' });
  if (!res.ok) return null;
  const blob = await res.blob();
  return await new Promise((r) => {
    const fr = new FileReader();
    fr.onloadend = () => r(fr.result);
    fr.readAsDataURL(blob);
  });
}

/* Build 4-column rows: Label, Value, Label, Value (نور شمېرنې نه اضافه کوو) */
function tableBodyRowsFour() {
  // Build pairs from fields only
  const pairs = (fieldsToShow.value || []).map((f) => ({
    label: f.title,
    value: displayValue(f.key),
  }));

  // Two pairs per row (4 cells)
  const body = [];
  for (let i = 0; i < pairs.length; i += 2) {
    const p1 = pairs[i];
    const p2 = pairs[i + 1];

    const row = [
      { text: p1.label, bold: true, margin: [0, 2, 0, 2] },
      { text: p1.value ?? '—', margin: [0, 2, 0, 2] },
    ];

    if (p2) {
      row.push(
        { text: p2.label, bold: true, margin: [0, 2, 0, 2] },
        { text: p2.value ?? '—', margin: [0, 2, 0, 2] },
      );
    } else {
      // pad if odd number of pairs
      row.push({ text: '', margin: [0, 2, 0, 2] }, { text: '', margin: [0, 2, 0, 2] });
    }

    body.push(row);
  }

  return body;
}

async function downloadPdf() {
  try {
    const pdfMake = (await import('pdfmake/build/pdfmake.js')).default;

    // Pashto/Arabic-friendly font
    const [reg, bold] = await Promise.all([
      b64('/fonts/Amiri-Regular.ttf'),
      b64('/fonts/Amiri-Bold.ttf'),
    ]);
    pdfMake.vfs = { ...(pdfMake.vfs || {}), 'Amiri-Regular.ttf': reg, 'Amiri-Bold.ttf': bold };
    pdfMake.fonts = {
      Amiri: {
        normal: 'Amiri-Regular.ttf',
        bold: 'Amiri-Bold.ttf',
        italics: 'Amiri-Regular.ttf',
        bolditalics: 'Amiri-Bold.ttf',
      },
    };

    const content = [];
    const fullName = (props.item?.name || 'کارمند').toString();

    // سرلیک
    content.push({ text: `${fullName} جزییات`, style: 'title', margin: [0, 0, 0, 10] });

    // انځور (اختیاري)
    const img = await imgB64(imageSrc.value);
    if (img) {
      content.push({ image: img, width: 130, alignment: 'center', margin: [0, 0, 0, 10] });
    }

    // څلور ستنې جدول: Label, Value, Label, Value (یوازې فیلډونه)
    content.push({
      table: {
        widths: ['auto', '*', 'auto', '*'],
        body: tableBodyRowsFour(),
      },
      layout: {
        hLineWidth: () => 0.4,
        vLineWidth: () => 0.0,
        hLineColor: () => '#e5e5e5',
        paddingLeft: () => 6,
        paddingRight: () => 6,
        paddingTop: () => 4,
        paddingBottom: () => 4,
      },
    });

    const doc = {
      pageSize: 'A4',
      pageMargins: [28, 28, 28, 28],
      defaultStyle: { font: 'Amiri', fontSize: 12 },
      styles: { title: { fontSize: 16, bold: true, alignment: 'right' } },
      content,
      rtl: true,
    };

    const nameSafe = fullName.replace(/[^\w\u0600-\u06FF\-]+/g, '_');
    pdfMake.createPdf(doc).download(`${nameSafe}.pdf`);
  } catch (e) {
    console.error('PDF export failed:', e);
  }
}
</script>

<style scoped>
.simple-panel {
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow: hidden;
}

/* سرلیک */
.panel-header {
  padding: 12px 16px;
  background: #98a4a8;
}
.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.title-section {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #333;
}

/* منځپانګه */
.panel-body {
  padding: 16px;
}
.loading-state {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 20px;
  justify-content: center;
  color: #777;
  font-size: 14px;
}

/* درې ستنې */
.three-cols {
  display: grid;
  grid-template-columns: var(--image-w, 40vh) 1fr 1fr;
  gap: 16px;
}

/* چپ ستن: انځور */
.col-left {
  display: flex;
  flex-direction: column;
}
.image-container {
  width: var(--image-w, 40vh);
  height: var(--image-h, 65vh);
}
.profile-image {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  border: 2px solid #1b1616;
}
.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0d0c0c;
  border-radius: 8px;
}

/* ښي لور: دوه ستنې key:value */
.col-right {
  grid-column: 2 / 4;
}
.kv-grid {
  display: grid;
  grid-template-columns: 1fr 1fr; /* دوه ستنې (هره خانه جلا) */
  gap: 8px 12px;
}
.kv-row {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  border: 1px solid #e0e0e0;
  background: #fff;
  padding: 8px 10px;
  border-radius: 6px;
  font-weight: 600;
}
.kv-label {
  white-space: nowrap;
}
.kv-sep {
  padding: 0 4px;
}
.kv-value {
  white-space: normal;
  word-break: break-word;
  overflow-wrap: anywhere;
  line-height: 1.6;
}

/* پای پټه */
.panel-footer {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  background: #f5f5f5;
}

/* ځواب ویونکی */
@media (max-width: 1200px) {
  .three-cols {
    grid-template-columns: var(--image-w, 40vh) 1fr;
  }
  .col-right {
    grid-column: 2 / 3;
  }
  .kv-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 768px) {
  .three-cols {
    grid-template-columns: 1fr;
  }
  .col-right {
    grid-column: 1 / 2;
  }
  .image-container {
    width: 100%;
    height: 60vh;
  }
  .kv-value {
    width: 100% !important;
    max-width: 100% !important;
  }
}
</style>
