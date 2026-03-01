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
                color="primary"
                variant="text"
                size="small"
                @click="downloadWord"
                class="download-btn"
                prepend-icon="mdi-file-word-outline"
              >
                Word
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

            <div v-else class="layout-container" :class="{ 'no-image': !shouldShowImage }">
              <!-- Image (ONLY if real picture exists) -->
              <div
                v-if="shouldShowImage"
                class="col-image"
                :style="{ '--image-w': imageWidth, '--image-h': imageHeight }"
              >
                <div class="image-container">
                  <div class="image-wrapper">
                    <v-img :src="imageSrc" class="profile-image" cover>
                      <template #placeholder>
                        <div class="image-placeholder"></div>
                      </template>
                    </v-img>
                  </div>

                  <!-- Name under image -->
                  <div v-if="displayName" class="image-name">
                    <div class="name-text">{{ displayName }}</div>
                    <div v-if="displayPosition" class="position-text">{{ displayPosition }}</div>
                  </div>
                </div>
              </div>

              <!-- Fields -->
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
          <v-btn color="grey-darken-1" variant="tonal" size="default" @click="close"> تړل </v-btn>
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

  const { locale } = useI18n();
  const { formatDisplay } = useCalendar();

  /* state */
  const expanded = ref(props.modelValue);

  watch(
    () => props.modelValue,
    (newVal) => {
      expanded.value = newVal;
      if (newVal && !props.item && !props.loading) emit('fetch', props.id);
    },
    { immediate: true }
  );

  watch(expanded, (newVal) => {
    emit('update:modelValue', newVal);
  });

  function close() {
    expanded.value = false;
    emit('close');
  }

  function open() {
    expanded.value = true;
    emit('update:modelValue', true);
  }

  function onKeydown(e) {
    if (!expanded.value) return;
    if (e.key === 'Escape' || e.key === 'Esc') close();
  }

  onMounted(() => {
    window.addEventListener('keydown', onKeydown);
    if (props.modelValue && !props.item && !props.loading) emit('fetch', props.id);
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
  });

  /* helpers */
  function getByPath(obj, path) {
    if (!obj || !path) return undefined;
    return path.includes('.')
      ? path.split('.').reduce((o, k) => (o == null ? undefined : o[k]), obj)
      : obj[path];
  }

  function resolveImageUrl(src) {
    if (!src) return null;
    if (/^(blob:|data:|https?:\/\/)/i.test(src)) return src;
    const base =
      props.fileBaseUrl || import.meta.env.VITE_FILE_BASE_URL || import.meta.env.VITE_API_URL || '';
    if (!base) return src;
    return `${String(base).replace(/\/+$/, '')}/${String(src).replace(/^\/+/, '')}`;
  }

  const imageRaw = computed(() => (props.imageKey ? getByPath(props.item, props.imageKey) : null));
  const imageSrc = computed(() => resolveImageUrl(imageRaw.value));
  const hasImage = computed(() => !!imageRaw.value);
  const shouldShowImage = computed(
    () => !!(props.showBodyImage && hasImage.value && imageSrc.value)
  );

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

  /* Fix RTL text splitting */
  function fixRtlText(text) {
    if (!text) return text;

    const compoundWords = [
      'نام مستعار',
      'نام پدر',
      'تخلص',
      'آدرس',
      'ایمیل',
      'تلفن',
      'موبایل',
      'شماره',
      'تاریخ',
      'زمان',
      'مکان',
      'نمبر',
      'کود',
      'رمز',
      'پاسورد',
    ];

    let fixedText = String(text);

    compoundWords.forEach((word) => {
      const regex = new RegExp(word.replace(/\s+/g, '\\s+'), 'g');
      fixedText = fixedText.replace(regex, word.replace(/\s+/g, '\u00A0'));
    });

    return fixedText;
  }

  /* show fields except excluded and image/doc paths */
  const fieldsToShow = computed(() => {
    if (!props.item) return [];

    if (props.fields && props.fields.length > 0) {
      return props.fields
        .filter((f) => {
          if (!f || !f.key) return false;

          const shouldExclude = props.excludeKeys.some((ex) => f.key.toString().startsWith(ex));
          if (shouldExclude) return false;

          if (props.imageKey && f.key === props.imageKey) return false;

          return true;
        })
        .map((f) => ({
          ...f,
          title: fixRtlText(f.title || f.key),
        }));
    }

    return Object.keys(props.item)
      .filter((key) => {
        if (props.excludeKeys.some((ex) => key.startsWith(ex))) return false;
        if (props.imageKey && key === props.imageKey) return false;
        if (!key || key === 'id' || key === '_id') return false;
        return true;
      })
      .map((key) => ({
        title: fixRtlText(key.replace(/_/g, ' ')),
        key,
      }));
  });
  function pdfRtlLabel(s) {
    if (!s) return s;

    // Normalize
    let t = String(s)
      .replace(/\u00A0/g, ' ')
      .trim();

    // Strong RTL embedding (forces RTL direction for the whole run)
    // RLE ... PDF
    t = `\u202B${t}\u202C`;

    // Stabilize word order for pdfMake:
    // use NBSP + ALM between words (stronger than ALM alone)
    t = t.replace(/\s+/g, `\u00A0\u061C`);

    return t;
  }
  /* ================= PDF Generation (OLD STYLE: Landscape, 4 columns + image on RIGHT) ================ */
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

  function buildFourColumnBodyRows() {
    const fields = fieldsToShow.value || [];
    const rows = [];

    for (let i = 0; i < fields.length; i += 4) {
      const row = [];
      for (let j = 0; j < 4; j++) {
        const idx = i + j;
        if (idx < fields.length) {
          const f = fields[idx];
          const value = displayValue(f.key);
          row.push({
            stack: [
              {
                text: pdfRtlLabel(String(f.title || '')),
                style: 'fieldLabel',
                margin: [0, 0, 0, 2],
                alignment: 'right',
                noWrap: true, // ✅ prevents pdfMake from re-breaking the label
              },
              { text: value, style: 'fieldValue', alignment: 'right' },
            ],
            margin: [6, 6, 6, 6],
          });
        } else {
          row.push({ text: '', margin: [6, 6, 6, 6] });
        }
      }
      rows.push(row);
    }

    return rows;
  }

  async function downloadPdf() {
    try {
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

      const fullName = displayName.value || '';
      const position = displayPosition.value || '';

      // ✅ if no picture -> do NOT render image block at all
      const img = shouldShowImage.value ? await imgB64(imageSrc.value) : null;
      // ✅ Fix RTL word order inside pdfMake only

      const tableRows = buildFourColumnBodyRows();

      const fieldsTable =
        tableRows.length > 0
          ? {
              table: {
                widths: ['25%', '25%', '25%', '25%'],
                body: tableRows,
              },
              layout: {
                hLineWidth: () => 0,
                vLineWidth: () => 0,
                paddingLeft: () => 6,
                paddingRight: () => 6,
                paddingTop: () => 4,
                paddingBottom: () => 4,
              },
            }
          : { text: 'هیڅ معلومات نشته', style: 'fieldValue', alignment: 'right' };

      // ✅ image block only if img exists
      const imageBlock = img
        ? {
            width: 220,
            stack: [
              { image: img, fit: [220, 300], alignment: 'center', margin: [0, 0, 0, 10] },
              ...(fullName
                ? [
                    {
                      text: fullName,
                      style: 'nameUnder',
                      alignment: 'center',
                      margin: [0, 0, 0, 2],
                    },
                  ]
                : []),
              ...(position ? [{ text: position, style: 'posUnder', alignment: 'center' }] : []),
            ],
          }
        : null;

      const contentColumns = img
        ? [
            { width: '*', stack: [fieldsTable] }, // fields (wide)
            { width: 220, stack: [imageBlock], margin: [12, 0, 0, 0] }, // image on right
          ]
        : [
            { width: '*', stack: [fieldsTable] }, // ✅ only fields, no empty column
          ];

      const doc = {
        pageSize: 'A4',
        pageOrientation: 'landscape', // ✅ old style
        pageMargins: [18, 18, 18, 18],
        defaultStyle: {
          font: regular ? 'Amiri' : 'Roboto',
          fontSize: 10,
          color: '#333333',
          alignment: 'right',
        },
        rtl: true,
        styles: {
          header: { fontSize: 16, bold: true, color: '#1976d2', alignment: 'right' },
          fieldLabel: { fontSize: 10, bold: true, color: '#555555', alignment: 'right' },
          fieldValue: { fontSize: 10, color: '#333333', alignment: 'right' },
          footer: { fontSize: 9, color: '#999999', alignment: 'right' },
          nameUnder: { fontSize: 12, bold: true, color: '#222' },
          posUnder: { fontSize: 10, color: '#666' },
        },
        content: [
          {
            text: pdfRtlLabel(`جزییات ${fullName}`),
            style: 'header',
            margin: [0, 0, 0, 12],
            alignment: 'right',
          },

          {
            columns: contentColumns,
            columnGap: 12,
            margin: [0, 0, 0, 10],
          },

          {
            text: `شماره: ${props.id}`,
            style: 'footer',
            alignment: 'right',
            margin: [0, 10, 0, 0],
          },
        ],
      };

      const safeName = (fullName || 'detail').replace(/[^\w\u0600-\u06FF\-]+/g, '_');
      const fileName = `${safeName}_جزییات.pdf`;
      pdfMake.createPdf(doc).download(fileName);
    } catch (error) {
      console.error('PDF export failed:', error);
      alert('د PDF تولید کې ستونزه رامنځ ته شوه. مهرباني وکړئ بیا هڅه وکړئ.');
    }
  }

  /* ================= Word Download (unchanged, but also hides image if missing) ================ */
  function escapeHtml(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function chunkIntoRows(arr, size) {
    const out = [];
    for (let i = 0; i < arr.length; i += size) out.push(arr.slice(i, i + size));
    return out;
  }

  async function downloadWord() {
    try {
      const fullName = displayName.value || '';
      const position = displayPosition.value || '';
      const img = shouldShowImage.value ? await imgB64(imageSrc.value) : null;

      const fields = (fieldsToShow.value || []).map((f) => ({
        title: String(f.title || '').replace(/\u00A0/g, ' '),
        value: displayValue(f.key),
      }));

      const rows = chunkIntoRows(fields, 4);

      const tableHtml = `
      <table class="fields">
        <tbody>
          ${rows
            .map((r) => {
              const rr = [...r];
              while (rr.length < 4) rr.push({ title: '', value: '' });

              return `<tr>
                ${rr
                  .map(
                    (c) => `<td>
                      <div class="lbl">${escapeHtml(c.title || '')}</div>
                      <div class="val">${escapeHtml(c.value || '—')}</div>
                    </td>`
                  )
                  .join('')}
              </tr>`;
            })
            .join('')}
        </tbody>
      </table>
    `;

      const html = `
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>${escapeHtml(fullName)}</title>
  <style>
    @page { size: A4 landscape; margin: 18mm; }
    body { direction: rtl; font-family: "Amiri", "Arial", sans-serif; color: #222; }
    .wrap { width: 100%; }
    .top { width: 100%; border-collapse: collapse; }
    .top td { vertical-align: top; }
    .imgBox { width: 260px; text-align: center; }
    .imgBox img { width: 260px; max-height: 360px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px; }
    .name { font-size: 18px; font-weight: 700; margin-top: 10px; }
    .pos { font-size: 14px; color: #666; margin-top: 2px; }
    .fields { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .fields td { width: 25%; padding: 8px 10px; vertical-align: top; }
    .lbl { font-size: 12px; font-weight: 700; color: #555; margin-bottom: 4px; }
    .val { font-size: 13px; font-weight: 500; color: #222; word-break: break-word; overflow-wrap: anywhere; }
    .footer { margin-top: 16px; font-size: 11px; color: #888; }
  </style>
</head>
<body>
  <div class="wrap">
    <table class="top">
      <tr>
        <td>
          ${tableHtml}
          <div class="footer">شماره: ${escapeHtml(props.id)}</div>
        </td>
        ${
          img
            ? `<td class="imgBox">
                <img src="${img}" />
                <div class="name">${escapeHtml(fullName)}</div>
                ${position ? `<div class="pos">${escapeHtml(position)}</div>` : ''}
              </td>`
            : ''
        }
      </tr>
    </table>
  </div>
</body>
</html>
    `.trim();

      const blob = new Blob(['\ufeff', html], { type: 'application/msword;charset=utf-8' });
      const url = URL.createObjectURL(blob);

      const safeName = (fullName || 'detail').replace(/[^\w\u0600-\u06FF\-]+/g, '_');
      const fileName = `${safeName}_جزییات.doc`;

      const a = document.createElement('a');
      a.href = url;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    } catch (e) {
      console.error('Word export failed:', e);
      alert('د Word فایل تولید کې ستونزه رامنځ ته شوه. مهرباني وکړئ بیا هڅه وکړئ.');
    }
  }

  defineExpose({ open, close });
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

  .no-data,
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

  /* Layout */
  .layout-container {
    display: grid;
    grid-template-columns: var(--image-w, 40vh) 1fr;
    gap: 16px;
    align-items: start;
  }

  /* ✅ If no image: show ONLY fields (no empty column, no placeholder) */
  .layout-container.no-image {
    grid-template-columns: 1fr;
  }

  .col-fields {
    grid-column: 2;
  }
  .layout-container.no-image .col-fields {
    grid-column: 1;
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
    background: #f5f5f5;
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

  /* Fields grid */
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
    .layout-container.no-image {
      grid-template-columns: 1fr;
    }

    .image-wrapper {
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

    .image-wrapper {
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

    .image-wrapper {
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

    .image-wrapper {
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
