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
        'event_date',
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
    return 'اړوند معلومات';
  });

  const displayName = computed(() => {
    const name = getByPath(props.item, props.nameKey);
    return name != null && name !== '' ? String(name) : null;
  });

  const displayPosition = computed(() => {
    const position = getByPath(props.item, props.positionKey);
    return position != null && position !== '' ? String(position) : null;
  });

  const isRtlDoc = computed(() => props.dir !== 'ltr');

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

  /* visual-only fix for titles inside component */
  function fixRtlText(text) {
    if (!text) return text;

    const compoundWords = [
      'نام مستعار',
      'نام پدر',
      'نام پدرکلان',
      'شماره تلفن',
      'شماره موبایل',
      'تاریخ تولد',
      'تاریخ شروع',
      'تاریخ ختم',
      'محل وظیفه',
      'محل سکونت',
      'آدرس فعلی',
      'آدرس اصلی',
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

  /* ================= Export Helpers ================= */

  function escapeHtml(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
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

  function chunkIntoRows(arr, size) {
    const out = [];
    for (let i = 0; i < arr.length; i += size) out.push(arr.slice(i, i + size));
    return out;
  }

  function getExportDensity(fieldsCount, hasImageBlock) {
    /**
     * Goal:
     * - fit one page for normal amount of data
     * - allow natural overflow only when data is truly huge
     */
    if (fieldsCount <= 8) {
      return {
        pageClass: 'density-loose',
        labelSize: 12,
        valueSize: 12,
        titleSize: 16,
        metaSize: 11,
        cellPadY: 7,
        cellPadX: 8,
        imageWidth: hasImageBlock ? 180 : 0,
        imageMaxHeight: 230,
        forceSinglePage: true,
      };
    }

    if (fieldsCount <= 16) {
      return {
        pageClass: 'density-normal',
        labelSize: 11,
        valueSize: 11,
        titleSize: 15,
        metaSize: 10,
        cellPadY: 5,
        cellPadX: 6,
        imageWidth: hasImageBlock ? 170 : 0,
        imageMaxHeight: 215,
        forceSinglePage: true,
      };
    }

    if (fieldsCount <= 28) {
      return {
        pageClass: 'density-compact',
        labelSize: 10,
        valueSize: 10,
        titleSize: 14,
        metaSize: 9,
        cellPadY: 4,
        cellPadX: 5,
        imageWidth: hasImageBlock ? 155 : 0,
        imageMaxHeight: 195,
        forceSinglePage: true,
      };
    }

    return {
      pageClass: 'density-huge',
      labelSize: 10,
      valueSize: 10,
      titleSize: 14,
      metaSize: 9,
      cellPadY: 4,
      cellPadX: 5,
      imageWidth: hasImageBlock ? 150 : 0,
      imageMaxHeight: 185,
      forceSinglePage: false,
    };
  }
  async function fileToDataUrl(url) {
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
    } catch (error) {
      console.error('Font load failed:', error);
      return null;
    }
  }
  function buildExportTableHtml(fields, isRtl) {
    const rows = chunkIntoRows(fields, 4);

    return `
    <table class="fields-table" dir="${isRtl ? 'rtl' : 'ltr'}">
      <tbody>
        ${rows
          .map((row) => {
            const rr = [...row];
            while (rr.length < 4) rr.push({ title: '', value: '' });

            return `
              <tr>
                ${rr
                  .map(
                    (cell) => `
                    <td>
                      <div class="field-label">${escapeHtml(cell.title || '')}</div>
                      <div class="field-value">${escapeHtml(cell.value || '—')}</div>
                    </td>
                  `
                  )
                  .join('')}
              </tr>
            `;
          })
          .join('')}
      </tbody>
    </table>
  `;
  }

  async function buildExportHtml({ forPrint = false } = {}) {
    const fullName = displayName.value || titleText.value || '';
    const position = displayPosition.value || '';
    const img = shouldShowImage.value ? await imgB64(imageSrc.value) : null;

    const amiriRegular = await fileToDataUrl('/fonts/Amiri-Regular.ttf');
    const amiriBold = await fileToDataUrl('/fonts/Amiri-Bold.ttf');

    const fields = (fieldsToShow.value || []).map((f) => ({
      title: String(f.title || '').replace(/\u00A0/g, ' '),
      value: displayValue(f.key),
    }));

    const isRtl = isRtlDoc.value;
    const direction = isRtl ? 'rtl' : 'ltr';
    const textAlign = isRtl ? 'right' : 'left';

    const density = getExportDensity(fields.length, !!img);
    const fieldsTable = buildExportTableHtml(fields, isRtl);

    const imageCell = img
      ? `
      <td class="image-cell" style="width:${density.imageWidth}px">
        <div class="image-box">
          <img src="${img}" alt="profile" />
          ${fullName ? `<div class="image-name">${escapeHtml(fullName)}</div>` : ''}
          ${position ? `<div class="image-position">${escapeHtml(position)}</div>` : ''}
          <div class="image-meta">شماره: ${escapeHtml(props.id)}</div>
        </div>
      </td>
    `
      : '';

    const dataCell = `
    <td class="data-cell">
      ${
        fullName
          ? `<div class="doc-title">${escapeHtml(fullName)}</div>`
          : `<div class="doc-title">${escapeHtml(titleText.value || 'اړوند معلومات')}</div>`
      }
      ${!img ? `<div class="doc-inline-meta">شماره: ${escapeHtml(props.id)}</div>` : ''}
      ${fieldsTable}
    </td>
  `;

    const mainRow = img
      ? isRtl
        ? `${imageCell}${dataCell}`
        : `${dataCell}${imageCell}`
      : `${dataCell}`;

    return `
<!DOCTYPE html>
<html lang="${isRtl ? 'fa' : 'en'}" dir="${direction}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>${escapeHtml(fullName || 'detail')}</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 8mm;
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      background: #ffffff;
      color: #222;
      font-family: ${amiriRegular ? "'ExportAmiri', 'Tahoma', 'Arial', sans-serif" : "'Tahoma', 'Arial', sans-serif"};
      direction: ${direction};
      text-align: ${textAlign};
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
    }

    body {
      padding: ${forPrint ? '0' : '10px'};
    }

    .page {
      width: 100%;
      ${density.forceSinglePage ? 'height: calc(100vh - 2px); overflow: hidden;' : ''}
      page-break-inside: avoid;
    }

    .sheet {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .sheet > tbody > tr > td {
      vertical-align: top;
    }

    .data-cell {
      width: auto;
      vertical-align: top;
    }

    .image-cell {
      vertical-align: top;
      ${isRtl ? 'padding-left: 10px;' : 'padding-right: 10px;'}
    }

    .doc-title {
      font-size: ${density.titleSize}px;
      font-weight: 700;
      color: #1976d2;
      margin: 0 0 6px 0;
      line-height: 1.25;
      direction: ${direction};
      text-align: ${textAlign};
      unicode-bidi: plaintext;
      word-break: break-word;
    }

    .doc-inline-meta {
      font-size: ${density.metaSize}px;
      color: #777;
      margin: 0 0 8px 0;
      direction: ${direction};
      text-align: ${textAlign};
      unicode-bidi: plaintext;
    }

    .image-box {
      text-align: center;
      direction: ${direction};
    }

    .image-box img {
      display: inline-block;
      width: auto;
      height: auto;
      max-width: 100%;
      max-height: ${density.imageMaxHeight}px;
      object-fit: contain;
      border: 1px solid #dcdcdc;
      border-radius: 4px;
      background: #fff;
    }

    .image-name {
      margin-top: 6px;
      font-size: ${Math.max(density.valueSize + 2, 11)}px;
      font-weight: 700;
      line-height: 1.25;
      direction: ${direction};
      unicode-bidi: plaintext;
      word-break: break-word;
    }

    .image-position {
      margin-top: 2px;
      font-size: ${density.valueSize}px;
      color: #666;
      line-height: 1.2;
      direction: ${direction};
      unicode-bidi: plaintext;
      word-break: break-word;
    }

    .image-meta {
      margin-top: 4px;
      font-size: ${density.metaSize}px;
      color: #777;
      line-height: 1.2;
      direction: ${direction};
      unicode-bidi: plaintext;
    }

    .fields-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      direction: ${direction};
    }

    .fields-table td {
      width: 25%;
      padding: ${density.cellPadY}px ${density.cellPadX}px;
      vertical-align: top;
      border: none;
    }

    .field-label {
      font-size: ${density.labelSize}px;
      font-weight: 700;
      color: #555;
      margin-bottom: 2px;
      line-height: 1.18;
      direction: ${direction};
      text-align: ${textAlign};
      unicode-bidi: plaintext;
      word-break: break-word;
      overflow-wrap: anywhere;
      white-space: normal;
    }

    .field-value {
      font-size: ${density.valueSize}px;
      font-weight: 500;
      color: #222;
      line-height: 1.24;
      direction: ${direction};
      text-align: ${textAlign};
      unicode-bidi: plaintext;
      word-break: break-word;
      overflow-wrap: anywhere;
      white-space: pre-wrap;
    }

    .footer-meta {
      margin-top: 8px;
      font-size: ${density.metaSize}px;
      color: #777;
      direction: ${direction};
      text-align: ${textAlign};
      unicode-bidi: plaintext;
    }

    ${
      density.forceSinglePage
        ? `
      .fields-table tr,
      .fields-table td,
      .image-box,
      .sheet,
      .page {
        page-break-inside: avoid !important;
      }
    `
        : `
      .page {
        height: auto;
        overflow: visible;
      }
    `
    }

    @font-face {
      font-family: 'ExportAmiri';
      src: url('${amiriRegular || ''}') format('truetype');
      font-weight: 400;
      font-style: normal;
    }

    @font-face {
      font-family: 'ExportAmiri';
      src: url('${amiriBold || amiriRegular || ''}') format('truetype');
      font-weight: 700;
      font-style: normal;
    }
    @media print {
      html, body {
        background: #fff;
      }

      body {
        padding: 0;
      }

      .page {
        width: 100%;
        ${
          density.forceSinglePage
            ? 'height: 100%; overflow: hidden;'
            : 'height: auto; overflow: visible;'
        }
      }
    }
  </style>
</head>
<body>
  <div class="page ${density.pageClass}">
    <table class="sheet">
      <tbody>
        <tr>
          ${mainRow}
        </tr>
      </tbody>
    </table>
    ${img ? `<div class="footer-meta">${escapeHtml(`شماره: ${props.id}`)}</div>` : ''}
  </div>
</body>
</html>
  `.trim();
  }

  function openPrintWindowAndPrint(html, title = 'document') {
    const printWindow = window.open('', '_blank', 'width=1400,height=900');

    if (!printWindow) {
      alert('مهرباني وکړئ د pop-up مخه خلاصه کړئ تر څو PDF چاپ شي.');
      return;
    }

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();

    const finalize = () => {
      try {
        printWindow.focus();
        printWindow.print();
      } catch (error) {
        console.error('Print failed:', error);
      }
    };

    const waitForImages = () => {
      const images = Array.from(printWindow.document.images || []);

      if (images.length === 0) {
        setTimeout(finalize, 250);
        return;
      }

      let loaded = 0;
      const done = () => {
        loaded += 1;
        if (loaded >= images.length) {
          setTimeout(finalize, 300);
        }
      };

      images.forEach((img) => {
        if (img.complete) {
          done();
        } else {
          img.onload = done;
          img.onerror = done;
        }
      });
    };

    if (printWindow.document.readyState === 'complete') {
      waitForImages();
    } else {
      printWindow.onload = waitForImages;
    }
  }

  /* ================= PDF Download ================= */
  /**
   * This uses browser print engine instead of pdfmake.
   * Reason:
   * - much better RTL rendering for Dari/Pashto/Arabic
   * - correct word order for labels and values
   * - much more stable layout
   */
  async function downloadPdf() {
    try {
      await nextTick();
      const html = await buildExportHtml({ forPrint: true });
      openPrintWindowAndPrint(html, displayName.value || titleText.value || 'detail');
    } catch (error) {
      console.error('PDF export failed:', error);
      alert('د PDF تولید کې ستونزه رامنځ ته شوه. مهرباني وکړئ بیا هڅه وکړئ.');
    }
  }

  /* ================= Word Download ================= */
  async function downloadWord() {
    try {
      await nextTick();
      const html = await buildExportHtml({ forPrint: false });

      const blob = new Blob(['\ufeff', html], { type: 'application/msword;charset=utf-8' });
      const url = URL.createObjectURL(blob);

      const safeName = String(displayName.value || titleText.value || 'detail')
        .trim()
        .replace(/[^\w\u0600-\u06FF\-]+/g, '_');

      const fileName = `${safeName}.doc`;

      const a = document.createElement('a');
      a.href = url;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      a.remove();

      setTimeout(() => URL.revokeObjectURL(url), 1000);
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
