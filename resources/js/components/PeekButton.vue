<template>
  <v-expand-transition>
    <div v-if="expanded" class="inline-panel" :style="{ direction: dir }">
      <v-card class="simple-panel">
        <!-- Header -->
        <div class="panel-header">
          <div class="header-content">
            <div class="title-section">
              <v-icon icon="mdi-information" class="mr-2" size="20" />
              <strong class="title-text">{{ titleText }}</strong>
            </div>
            <div class="header-actions">
              <v-btn
                color="primary"
                variant="text"
                size="small"
                @click="downloadPdf"
                class="download-btn"
                prepend-icon="mdi-download"
              >
                PDF
              </v-btn>
              <v-btn
                v-if="closable"
                icon
                size="small"
                variant="text"
                color="grey-darken-1"
                :title="'تړل'"
                @click="close"
                class="close-btn"
              >
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </div>
          </div>
        </div>

        <v-divider />

        <!-- Content -->
        <div class="panel-body">
          <template v-if="loading">
            <div class="loading-state">
              <v-progress-circular indeterminate size="24" width="2" color="primary" />
              <span>لوډ کېږي...</span>
            </div>
          </template>

          <template v-else>
            <div v-if="!item" class="no-data">
              <v-icon icon="mdi-alert-circle" size="48" color="grey" />
              <div>هیڅ معلومات نشته</div>
            </div>
            
            <div v-else class="layout-container">
              <!-- Column 1: Image -->
              <div class="col-image" :style="{ '--image-w': imageWidth, '--image-h': imageHeight }">
                <div v-if="showBodyImage" class="image-container">
                  <div v-if="hasImage" class="image-wrapper">
                    <v-img :src="imageSrc" class="profile-image" cover>
                      <template #placeholder>
                        <div class="image-placeholder">
                          <v-icon icon="mdi-account" size="32" color="white" />
                        </div>
                      </template>
                    </v-img>
                  </div>
                  <div v-else class="icon-placeholder">
                    <v-icon icon="mdi-account" size="48" color="white" />
                  </div>
                  
                  <!-- Name under image -->
                  <div v-if="displayName" class="image-name">
                    <div class="name-text">{{ displayName }}</div>
                    <div v-if="displayPosition" class="position-text">{{ displayPosition }}</div>
                  </div>
                </div>
              </div>

              <!-- Columns 2-5: Fields in 4 columns grid -->
              <div class="col-fields">
                <div v-if="fieldsToShow.length === 0" class="no-fields">
                  <v-icon icon="mdi-database-off" size="32" color="grey" />
                  <div>هیڅ فیلډونه نشته</div>
                </div>
                <div v-else class="fields-grid">
                  <div v-for="(f, idx) in fieldsToShow" :key="idx" class="field-item">
                    <div class="field-label">{{ f.title }}</div>
                    <div class="field-value">{{ displayValue(f.key) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>

        <v-divider v-if="closable" />

        <!-- Footer -->
        <div v-if="closable" class="panel-footer">
          <v-spacer />
          <v-btn color="grey-darken-1" variant="outlined" size="small" @click="close">
            تړل
          </v-btn>
        </div>
      </v-card>
    </div>
  </v-expand-transition>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCalendar } from '../../composables/useCalendar';

const props = defineProps({
  id: { type: [String, Number], required: true },
  item: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  modelValue: { type: Boolean, default: false },

  title: { type: String, default: '' },
  titleKey: { type: String, default: '' },
  nameKey: { type: String, default: 'name' },
  positionKey: { type: String, default: 'position' },

  imageKey: { type: String, default: '' },
  fields: { type: Array, default: () => [] },

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

// Debug: Log when component receives props
console.log('InlinePanel Debug:', {
  id: props.id,
  modelValue: props.modelValue,
  item: props.item,
  fields: props.fields,
  loading: props.loading
});

const { locale } = useI18n();
const { formatDisplay } = useCalendar();

/* state */
const expanded = ref(props.modelValue);

// Watch for modelValue changes from parent
watch(
  () => props.modelValue,
  (newVal) => {
    console.log('modelValue changed:', newVal);
    expanded.value = newVal;
    
    // If panel is opening and item is null, emit fetch event
    if (newVal && !props.item && !props.loading) {
      emit('fetch', props.id);
    }
  },
  { immediate: true }
);

// Watch expanded and emit to parent
watch(expanded, (newVal) => {
  console.log('expanded changed:', newVal);
  emit('update:modelValue', newVal);
});

function close() {
  console.log('Closing panel');
  expanded.value = false;
  emit('close');
}

// Manual open function - can be called from parent via ref
function open() {
  console.log('Opening panel manually');
  expanded.value = true;
  emit('update:modelValue', true);
}

function onKeydown(e) {
  if (!expanded.value) return;
  if (e.key === 'Escape' || e.key === 'Esc') close();
}

onMounted(() => {
  console.log('Panel mounted, adding keydown listener');
  window.addEventListener('keydown', onKeydown);
  
  // Auto-open if modelValue is true on mount
  if (props.modelValue && !props.item && !props.loading) {
    emit('fetch', props.id);
  }
});

onUnmounted(() => {
  console.log('Panel unmounted, removing keydown listener');
  window.removeEventListener('keydown', onKeydown);
});

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

const displayName = computed(() => {
  const name = getByPath(props.item, props.nameKey);
  return name != null && name !== '' ? String(name) : null;
});

const displayPosition = computed(() => {
  const position = getByPath(props.item, props.positionKey);
  return position != null && position !== '' ? String(position) : null;
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
  console.log(`Getting value for ${key}:`, raw); // Debug
  if (isDateKeyOrIso(key, raw)) return formatDisplay(raw, locale.value || 'fa') || '—';
  return raw == null || raw === '' ? '—' : String(raw);
}

/* Fix RTL text splitting */
function fixRtlText(text) {
  if (!text) return text;
  
  const compoundWords = [
    'نام مستعار', 'نام پدر', 'تخلص', 'آدرس', 'ایمیل', 'تلفن', 'موبایل',
    'شماره', 'تاریخ', 'زمان', 'مکان', 'نمبر', 'کود', 'رمز', 'پاسورد'
  ];
  
  let fixedText = String(text);
  
  compoundWords.forEach(word => {
    const regex = new RegExp(word.replace(/\s+/g, '\\s+'), 'g');
    fixedText = fixedText.replace(regex, word.replace(/\s+/g, '\u00A0'));
  });
  
  return fixedText;
}

/* show fields except excluded and image/doc paths */
const fieldsToShow = computed(() => {
  console.log('Computing fieldsToShow:', {
    propsFields: props.fields,
    item: props.item,
    hasItem: !!props.item
  });

  if (!props.item) {
    console.log('No item data, returning empty array');
    return [];
  }

  // If fields are provided in props, use them
  if (props.fields && props.fields.length > 0) {
    const filtered = props.fields
      .filter(f => {
        if (!f || !f.key) return false;
        
        // Check if key should be excluded
        const shouldExclude = props.excludeKeys.some(ex => 
          f.key.toString().startsWith(ex)
        );
        
        if (shouldExclude) return false;
        
        // Don't exclude image key if it's not the imageKey prop
        if (props.imageKey && f.key === props.imageKey) return false;
        
        return true;
      })
      .map(f => ({
        ...f,
        title: fixRtlText(f.title || f.key)
      }));
    
    console.log('Using provided fields, filtered:', filtered);
    return filtered;
  }
  
  // Generate fields from item object
  console.log('Generating fields from item object');
  const generatedFields = Object.keys(props.item)
    .filter(key => {
      // Skip excluded keys
      if (props.excludeKeys.some(ex => key.startsWith(ex))) return false;
      if (props.imageKey && key === props.imageKey) return false;
      if (!key || key === 'id' || key === '_id') return false;
      return true;
    })
    .map(key => ({
      title: fixRtlText(key.replace(/_/g, ' ')),
      key: key
    }));
  
  console.log('Generated fields:', generatedFields);
  return generatedFields;
});

/* ================= PDF Generation ================ */
async function b64(path) {
  try {
    const res = await fetch(path, { cache: 'no-cache' });
    if (!res.ok) throw new Error(`Font fetch failed ${res.status} @ ${path}`);
    const buf = await res.arrayBuffer();
    let bin = '';
    const bytes = new Uint8Array(buf);
    const chunk = 0x8000;
    for (let i = 0; i < bytes.length; i += chunk) {
      bin += String.fromCharCode.apply(null, bytes.subarray(i, i + chunk));
    }
    return btoa(bin);
  } catch (error) {
    console.error('Failed to load font:', error);
    return null;
  }
}

async function imgB64(url) {
  if (!url) return null;
  try {
    const res = await fetch(url, { cache: 'no-cache' });
    if (!res.ok) return null;
    const blob = await res.blob();
    return await new Promise((resolve, reject) => {
      const fr = new FileReader();
      fr.onloadend = () => resolve(fr.result);
      fr.onerror = reject;
      fr.readAsDataURL(blob);
    });
  } catch {
    return null;
  }
}

function createFourColumnTable() {
  const fields = fieldsToShow.value;
  console.log('Creating PDF table with fields:', fields);
  
  const rows = [];
  
  for (let i = 0; i < fields.length; i += 4) {
    const row = [];
    
    for (let j = 0; j < 4; j++) {
      const fieldIndex = i + j;
      if (fieldIndex < fields.length) {
        const field = fields[fieldIndex];
        const value = displayValue(field.key);
        
        row.push({
          stack: [
            { 
              text: field.title.replace(/\u00A0/g, ' '),
              style: 'fieldLabel', 
              margin: [0, 0, 0, 2], 
              alignment: 'right' 
            },
            { 
              text: value, 
              style: 'fieldValue', 
              alignment: 'right' 
            }
          ],
          margin: [4, 4, 4, 4],
        });
      } else {
        row.push({ text: '', margin: [4, 4, 4, 4] });
      }
    }
    
    rows.push(row);
  }
  
  return rows;
}

async function downloadPdf() {
  try {
    console.log('Starting PDF download...');
    const pdfMake = (await import('pdfmake/build/pdfmake.js')).default;

    const [regular, bold] = await Promise.all([
      b64('/fonts/Amiri-Regular.ttf'),
      b64('/fonts/Amiri-Bold.ttf'),
    ]);

    if (regular && bold) {
      pdfMake.vfs = {
        ...(pdfMake.vfs || {}),
        'Amiri-Regular.ttf': regular,
        'Amiri-Bold.ttf': bold,
      };
      pdfMake.fonts = {
        Amiri: {
          normal: 'Amiri-Regular.ttf',
          bold: 'Amiri-Bold.ttf',
          italics: 'Amiri-Regular.ttf',
          bolditalics: 'Amiri-Bold.ttf',
        },
      };
    }

    const content = [];
    const fullName = displayName.value || 'کارمند';
    const position = displayPosition.value;

    content.push({
      text: `جزییات ${fullName}`,
      style: 'header',
      margin: [0, 0, 0, 16],
      alignment: 'right',
    });

    const img = await imgB64(imageSrc.value);
    const columns = [];

    columns.push({
      width: '*',
      stack: [
        { text: fullName, style: 'name', margin: [0, 0, 0, 4], alignment: 'right' },
        position ? { text: position, style: 'position', margin: [0, 0, 0, 8], alignment: 'right' } : null,
        { 
          text: `تاریخ\u00A0تولید: ${new Date().toLocaleDateString('fa-IR')}`,
          style: 'date',
          alignment: 'right'
        },
      ].filter(Boolean),
    });

    if (img) {
      columns.push({
        width: 100,
        image: img,
        fit: [80, 100],
        alignment: 'left',
        margin: [0, 0, 0, 8],
      });
    } else {
      columns.push({
        width: 100,
        stack: [
          {
            table: {
              widths: [80],
              body: [[{ text: '', fillColor: '#1976d2', height: 100 }]],
            },
            layout: 'noBorders',
            alignment: 'left',
            margin: [0, 0, 0, 8],
          },
          {
            text: '👤',
            fontSize: 32,
            color: '#ffffff',
            alignment: 'center',
            absolutePosition: { x: 20, y: 40 },
          },
        ],
      });
    }

    content.push({
      columns: columns,
      margin: [0, 0, 0, 20],
    });

    const tableRows = createFourColumnTable();
    
    if (tableRows.length > 0) {
      content.push({
        table: {
          widths: ['25%', '25%', '25%', '25%'],
          body: tableRows,
        },
        layout: {
          hLineWidth: () => 0,
          vLineWidth: () => 0,
          paddingLeft: () => 8,
          paddingRight: () => 8,
          paddingTop: () => 4,
          paddingBottom: () => 4,
        },
        margin: [0, 0, 0, 20],
      });
    } else {
      content.push({
        text: 'هیڅ معلومات نشته',
        style: 'fieldValue',
        alignment: 'right',
        margin: [0, 0, 0, 20],
      });
    }

    content.push({
      text: `شماره: ${props.id}`,
      style: 'footer',
      alignment: 'right',
      margin: [0, 20, 0, 0],
    });

    const doc = {
      pageSize: 'A4',
      pageMargins: [20, 20, 20, 20],
      defaultStyle: {
        font: regular ? 'Amiri' : 'Roboto',
        fontSize: 10,
        color: '#333333',
        alignment: 'right',
      },
      styles: {
        header: {
          fontSize: 16,
          bold: true,
          color: '#1976d2',
          alignment: 'right',
        },
        name: {
          fontSize: 14,
          bold: true,
          color: '#333333',
          alignment: 'right',
        },
        position: {
          fontSize: 12,
          color: '#666666',
          alignment: 'right',
        },
        date: {
          fontSize: 10,
          color: '#888888',
          margin: [0, 8, 0, 0],
          alignment: 'right',
        },
        fieldLabel: {
          fontSize: 10,
          bold: true,
          color: '#555555',
          alignment: 'right',
          margin: [0, 0, 0, 2],
        },
        fieldValue: {
          fontSize: 10,
          color: '#333333',
          alignment: 'right',
        },
        footer: {
          fontSize: 9,
          color: '#999999',
          alignment: 'right',
        },
      },
      content: content,
      rtl: true,
    };

    const safeName = fullName.replace(/[^\w\u0600-\u06FF\-]+/g, '_');
    const fileName = `${safeName}_جزییات.pdf`;
    console.log('Downloading PDF:', fileName);
    pdfMake.createPdf(doc).download(fileName);
  } catch (error) {
    console.error('PDF export failed:', error);
    alert('د PDF تولید کې ستونزه رامنځ ته شوه. مهرباني وکړئ بیا هڅه وکړئ.');
  }
}

// Expose open method to parent component
defineExpose({
  open,
  close
});
</script>

<style scoped>
.simple-panel {
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  overflow: hidden;
  background: white;
}

/* Header */
.panel-header {
  padding: 8px 12px;
  background: #f8f9fa;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.title-section {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #333;
  flex: 1;
}

.title-text {
  unicode-bidi: plaintext;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 4px;
}

.close-btn,
.download-btn {
  min-width: auto !important;
  padding: 4px 8px !important;
  height: 32px !important;
}

/* Body */
.panel-body {
  padding: 16px;
}

.loading-state {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 20px;
  justify-content: center;
  color: #666;
  font-size: 14px;
}

.no-data {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  color: #666;
  text-align: center;
  gap: 12px;
}

.no-fields {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  color: #666;
  text-align: center;
  gap: 12px;
}

/* Layout: Image + 4 columns */
.layout-container {
  display: grid;
  grid-template-columns: var(--image-w, 40vh) 1fr;
  gap: 16px;
  align-items: start;
}

/* Image column */
.col-image {
  position: relative;
}

.image-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.image-wrapper {
  width: var(--image-w, 40vh);
  height: var(--image-h, 65vh);
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid #ddd;
  background: white;
}

.profile-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
}

.icon-placeholder {
  width: var(--image-w, 40vh);
  height: var(--image-h, 65vh);
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1976d2, #1565c0);
  border-radius: 4px;
  border: 1px solid #1565c0;
}

.image-name {
  text-align: center;
  padding: 0 4px;
}

.name-text {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin-bottom: 2px;
  line-height: 1.3;
}

.position-text {
  font-size: 13px;
  color: #666;
  font-weight: 500;
}

/* Fields column - 4 columns grid */
.col-fields {
  grid-column: 2;
}

.fields-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px 8px;
  align-items: start;
}

.field-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 6px;
  min-height: 48px;
}

.field-label {
  font-size: 12px;
  font-weight: 600;
  color: #555;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  line-height: 1.2;
  text-align: right;
  unicode-bidi: plaintext;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.field-value {
  font-size: 13px;
  color: #333;
  font-weight: 500;
  line-height: 1.4;
  word-break: break-word;
  overflow-wrap: anywhere;
  text-align: right;
  min-height: 18px;
}

/* Footer */
.panel-footer {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  background: #f8f9fa;
}

/* Print styles */
@media print {
  .simple-panel {
    border: none !important;
    box-shadow: none !important;
    break-inside: avoid;
    direction: rtl !important;
  }
  
  .panel-header {
    background: white !important;
  }
  
  .header-actions,
  .panel-footer {
    display: none !important;
  }
  
  .layout-container {
    grid-template-columns: 30% 70% !important;
    gap: 12px !important;
  }
  
  .image-wrapper,
  .icon-placeholder {
    width: 100% !important;
    height: 250px !important;
  }
  
  .fields-grid {
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 8px 6px !important;
  }
  
  .field-item {
    padding: 4px !important;
    min-height: 40px !important;
  }
  
  .field-label {
    font-size: 10px !important;
  }
  
  .field-value {
    font-size: 11px !important;
  }
  
  .name-text {
    font-size: 14px !important;
  }
  
  .position-text {
    font-size: 12px !important;
  }
}

/* Responsive */
@media (max-width: 1400px) {
  .fields-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 1200px) {
  .layout-container {
    grid-template-columns: 30vh 1fr;
  }
  
  .image-wrapper,
  .icon-placeholder {
    width: 30vh;
    height: 50vh;
  }
  
  .fields-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 960px) {
  .layout-container {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .col-fields {
    grid-column: 1;
  }
  
  .image-wrapper,
  .icon-placeholder {
    width: 100%;
    max-width: 300px;
    height: 400px;
    margin: 0 auto;
  }
  
  .fields-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .layout-container {
    gap: 16px;
  }
  
  .image-wrapper,
  .icon-placeholder {
    height: 350px;
  }
  
  .name-text {
    font-size: 15px;
  }
  
  .position-text {
    font-size: 12px;
  }
  
  .fields-grid {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .field-item {
    padding: 5px;
    min-height: 40px;
  }
  
  .field-label {
    font-size: 11px;
  }
  
  .field-value {
    font-size: 12px;
  }
}

@media (max-width: 480px) {
  .panel-header {
    padding: 6px 8px;
  }
  
  .panel-body {
    padding: 12px;
  }
  
  .image-wrapper,
  .icon-placeholder {
    height: 300px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  
  .header-actions {
    width: 100%;
    justify-content: flex-end;
  }
}
</style>