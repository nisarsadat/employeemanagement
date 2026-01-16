<template>
  <v-navigation-drawer
    v-model="model"
    location="left"
    width="480"
    temporary
    :dir="rtl ? 'rtl' : 'ltr'"
    elevation="3"
    class="details-drawer"
  >
    <div class="drawer-content pa-5">
      <!-- Header -->
      <div class="header-section d-flex align-center justify-space-between mb-4">
        <div class="d-flex align-center" style="gap: 12px">
          <v-avatar size="42" class="elevation-2" :class="{ 'has-image': hasImage && !imageError }">
            <v-img
              :src="pictureResolved"
              cover
              :alt="item?.title || 'Location'"
              @error="imageError = true"
              class="cover-img"
            >
              <template v-slot:placeholder>
                <v-icon size="24" color="primary">mdi-map-marker</v-icon>
              </template>
            </v-img>
          </v-avatar>

          <div class="title-section">
            <h3 class="text-h6 font-weight-bold text-primary">
              {{ item?.title || item?.typeLabel || (rtl ? 'جزئیات' : 'Details') }}
            </h3>

            <!-- ✅ Type label becomes routable -->
            <div
              class="text-caption text-medium-emphasis d-flex align-center mt-1 type-link"
              :class="{ disabled: !typeRoute }"
              role="button"
              tabindex="0"
              @click="goTypeRoute"
              @keydown.enter="goTypeRoute"
              @keydown.space.prevent="goTypeRoute"
            >
              <v-icon size="16" class="mr-1">mdi-tag-outline</v-icon>
              <span class="text-primary text-decoration-underline">
                {{ item?.typeLabel || 'Location' }}
              </span>
              <v-icon v-if="typeRoute" size="14" class="ml-1">mdi-open-in-new</v-icon>
            </div>
            <!-- ✅ Header meta line: resource code + phone + employee code -->
            <div v-if="headerMeta.length" class="header-meta-line mt-2">
              <v-chip
                v-for="m in headerMeta"
                :key="m.key"
                size="x-small"
                variant="tonal"
                class="meta-chip"
              >
                <v-icon start size="14">{{ m.icon }}</v-icon>
                <span class="meta-label">{{ m.label }}:</span>
                <span class="meta-value">{{ m.value }}</span>
              </v-chip>
            </div>
          </div>
        </div>

        <div class="action-buttons d-flex align-center" style="gap: 8px">
          <v-tooltip :text="rtl ? 'رفتن به موقعیت' : 'Fly to location'" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn
                icon
                variant="flat"
                size="small"
                v-bind="props"
                @click="$emit('flyToItem', item)"
                :disabled="!item?.coordinates"
                class="elevation-1"
              >
                <v-icon>mdi-crosshairs-gps</v-icon>
              </v-btn>
            </template>
          </v-tooltip>

          <v-btn icon variant="text" size="small" @click="model = false" class="close-btn">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
      </div>

      <v-divider class="my-4" />

      <!-- Loading -->
      <v-skeleton-loader v-if="loading" type="image, article, actions" class="mb-4" />

      <!-- Empty -->
      <v-alert v-else-if="!data" type="info" variant="tonal" class="mb-4" :icon="false">
        <div class="d-flex align-center">
          <v-icon color="info" class="mr-3">mdi-information-outline</v-icon>
          <span>{{ rtl ? 'دیتایی موجود نیست' : 'No data available' }}</span>
        </div>
      </v-alert>

      <!-- ✅ Content -->
      <div v-else class="details-content">
        <!-- Image Preview -->
        <div v-if="hasImage && !imageError" class="image-preview mb-6">
          <div class="text-caption text-medium-emphasis mb-2 d-flex align-center">
            <v-icon size="16" class="mr-1">mdi-image</v-icon>
            {{ rtl ? 'تصویر' : 'Image' }}
          </div>
          <v-img
            :src="pictureResolved"
            :alt="item?.title || 'Image'"
            height="200"
            class="rounded-lg elevation-1 cover-img"
            cover
            @error="imageError = true"
          >
            <template v-slot:placeholder>
              <div class="d-flex align-center justify-center fill-height">
                <v-progress-circular indeterminate color="primary" />
              </div>
            </template>
          </v-img>
        </div>

        <!-- ✅ Scroll area -->
        <div class="data-grid nice-scroll">
          <div
            v-for="row in rows"
            :key="row.key"
            class="data-row pa-4 mb-3"
            :class="{ highlight: isImportantKey(row.key) }"
          >
            <div class="key-label text-caption font-weight-bold text-uppercase text-primary">
              {{ formatKey(row.key) }}
            </div>

            <div
              class="value-content text-body-1 mt-2"
              :class="{ 'json-data': isJsonString(row.value) }"
            >
              <template v-if="isJsonString(row.value)">
                <pre class="json-display nice-scroll">{{ prettyJson(row.value) }}</pre>
              </template>

              <template v-else-if="isUrl(row.value)">
                <a :href="row.value" target="_blank" class="text-primary text-decoration-none">
                  {{ truncateUrl(row.value) }}
                  <v-icon size="14" class="ml-1">mdi-open-in-new</v-icon>
                </a>
              </template>

              <template v-else-if="isBoolean(row.value)">
                <v-chip
                  size="small"
                  :color="row.value.toLowerCase() === 'true' ? 'success' : 'error'"
                  variant="flat"
                >
                  {{ row.value }}
                </v-chip>
              </template>

              <template v-else-if="isDate(row.value)">
                <div class="d-flex align-center">
                  <v-icon size="16" class="mr-2">mdi-calendar</v-icon>
                  {{ formatDate(row.value) }}
                </div>
              </template>

              <!-- ✅ Make selected fields routable -->
              <template v-else-if="fieldRouteFor(row.key, row.value)">
                <span
                  class="route-value text-primary text-decoration-underline"
                  role="button"
                  tabindex="0"
                  @click="goFieldRoute(row.key, row.value)"
                  @keydown.enter="goFieldRoute(row.key, row.value)"
                  @keydown.space.prevent="goFieldRoute(row.key, row.value)"
                >
                  {{ row.value }}
                  <v-icon size="14" class="ml-1">mdi-open-in-new</v-icon>
                </span>
              </template>

              <template v-else>
                {{ row.value }}
              </template>
            </div>

            <v-divider v-if="!isLastRow(row.key)" class="mt-3" />
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div v-if="!loading && data" class="footer-actions mt-6 pt-4">
        <v-divider class="mb-4" />
        <div class="d-flex justify-end" style="gap: 8px">
          <v-btn
            variant="outlined"
            color="primary"
            size="small"
            prepend-icon="mdi-content-copy"
            @click="copyData"
          >
            {{ rtl ? 'کپی' : 'Copy' }}
          </v-btn>

          <!-- ✅ Export PDF table -->
          <v-btn
            variant="flat"
            color="primary"
            size="small"
            prepend-icon="mdi-file-pdf-box"
            @click="exportPdf"
          >
            {{ rtl ? 'پی‌دی‌اف' : 'PDF' }}
          </v-btn>
        </div>
      </div>
    </div>
  </v-navigation-drawer>
</template>

<script setup>
  import { computed, ref, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import locationIcon from './location.png';

  const router = useRouter();

  const props = defineProps({
    modelValue: Boolean,
    rtl: Boolean,
    loading: Boolean,
    item: Object,
    data: [Object, Array, String, Number, Boolean, null],
  });

  const emit = defineEmits(['update:modelValue', 'flyToItem']);

  const imageError = ref(false);

  // ✅ reset error when drawer opens or item changes (very important)
  watch(
    () => [props.modelValue, props.item?.picture],
    () => {
      imageError.value = false;
    }
  );

  const model = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
  });

  const hasImage = computed(() => {
    const raw = props?.item?.picture;
    if (!raw) return false;
    const pic = String(raw).trim();
    return pic.length > 0 && pic !== 'null' && pic !== 'undefined';
  });

  function normalizeVal(v) {
    if (v === null || v === undefined) return '-';
    if (typeof v === 'object') return JSON.stringify(v, null, 2);
    return String(v);
  }

  const HIDE_KEYS = new Set([
    'id',
    'picture',
    'picture_url',
    'image_url',
    'photo_url',
    'photo',
    'image',
    'avatar',
    'old_picture',
    'profile_picture',
  ]);

  function shouldHideKey(key) {
    const k = String(key || '')
      .toLowerCase()
      .trim();
    if (HIDE_KEYS.has(k)) return true;
    if (k.includes('picture') || k.includes('photo') || k.includes('image') || k.includes('avatar'))
      return true;
    return false;
  }

  const rows = computed(() => {
    const d = props.data;
    if (!d) return [];

    if (typeof d !== 'object') return [{ key: 'Value', value: normalizeVal(d) }];

    if (Array.isArray(d)) {
      return [
        { key: 'Count', value: String(d.length) },
        { key: 'Items', value: normalizeVal(d) },
      ];
    }

    return Object.keys(d)
      .filter((k) => !shouldHideKey(k))
      .map((k) => ({ key: k, value: normalizeVal(d[k]) }));
  });
  function pickFirst(...vals) {
    for (const v of vals) {
      if (v === null || v === undefined) continue;
      const s = String(v).trim();
      if (s && s !== '-' && s !== 'null' && s !== 'undefined') return s;
    }
    return '';
  }

  const headerMeta = computed(() => {
    const d = props.data && typeof props.data === 'object' ? props.data : {};
    const it = props.item || {};

    // detect what record is opened
    const t = normalizeType(it?.type || it?.typeLabel);

    // -------------------------
    // ✅ RESOURCE record
    // want: resource.code_number + resource.phone_number + office_employee.code_number
    // -------------------------
    if (t.includes('resource')) {
      const resourceCode = pickFirst(
        d?.resource?.code_number,
        d?.code_number,
        d?.resource_code,
        it?.resource?.code_number,
        it?.code_number
      );

      const resourcePhone = pickFirst(
        d?.resource?.phone_number,
        d?.phone_number,
        d?.resource_phone,
        it?.resource?.phone_number,
        it?.phone_number
      );

      const officeEmployeeCode = pickFirst(
        d?.office_employee?.code_number,
        d?.officeEmployee?.code_number,
        d?.office_employee_code,
        it?.office_employee?.code_number,
        it?.officeEmployee?.code_number
      );

      const out = [];

      if (resourceCode) {
        out.push({
          key: 'resource_code',
          label: props.rtl ? 'کود منبع' : 'Resource',
          value: resourceCode,
          icon: 'mdi-barcode',
        });
      }

      if (officeEmployeeCode) {
        out.push({
          key: 'office_employee_code',
          label: props.rtl ? 'کود کارمند' : 'Employee',
          value: officeEmployeeCode,
          icon: 'mdi-account-badge-outline',
        });
      }

      if (resourcePhone) {
        out.push({
          key: 'resource_phone',
          label: props.rtl ? 'تیلفون' : 'Phone',
          value: resourcePhone,
          icon: 'mdi-phone',
        });
      }

      return out;
    }

    // -------------------------
    // ✅ OFFICE EMPLOYEE record
    // want: office_employee.code_number + office_employee.phone_number
    // -------------------------
    if (t.includes('officeemployee') || t.includes('employee')) {
      const employeeCode = pickFirst(
        d?.office_employee?.code_number,
        d?.code_number,
        d?.office_employee_code,
        it?.office_employee?.code_number,
        it?.code_number
      );

      const employeePhone = pickFirst(
        d?.office_employee?.phone_number,
        d?.phone_number,
        d?.office_employee_phone,
        it?.office_employee?.phone_number,
        it?.phone_number
      );

      const out = [];

      if (employeeCode) {
        out.push({
          key: 'employee_code',
          label: props.rtl ? 'کود کارمند' : 'Employee',
          value: employeeCode,
          icon: 'mdi-account-badge-outline',
        });
      }

      if (employeePhone) {
        out.push({
          key: 'employee_phone',
          label: props.rtl ? 'تیلفون' : 'Phone',
          value: employeePhone,
          icon: 'mdi-phone',
        });
      }

      return out;
    }

    // default: nothing
    return [];
  });

  const pictureResolved = computed(() => {
    if (imageError.value) return locationIcon;

    const raw = props?.item?.picture;
    if (!raw) return locationIcon;

    const pic = String(raw).trim();
    if (pic.startsWith('http://') || pic.startsWith('https://')) return pic;

    const origin = window.location.origin;
    const clean = pic.replace(/^\/+/, '');
    return `${origin}/${clean}`;
  });

  function isImportantKey(key) {
    const important = ['name', 'title', 'status', 'type', 'coordinates'];
    return important.includes(String(key || '').toLowerCase());
  }

  function formatKey(key) {
    return String(key || '')
      .replace(/([A-Z])/g, ' $1')
      .replace(/^./, (str) => str.toUpperCase());
  }

  function isJsonString(str) {
    if (typeof str !== 'string') return false;
    try {
      JSON.parse(str);
      return true;
    } catch {
      return false;
    }
  }

  function isUrl(str) {
    if (typeof str !== 'string') return false;
    try {
      new URL(str);
      return true;
    } catch {
      return false;
    }
  }

  function isBoolean(str) {
    if (typeof str !== 'string') return false;
    const s = str.toLowerCase();
    return s === 'true' || s === 'false';
  }

  function isDate(str) {
    if (typeof str !== 'string') return false;
    return !isNaN(Date.parse(str)) && !isJsonString(str);
  }

  function isLastRow(key) {
    const lastIndex = rows.value.length - 1;
    return rows.value[lastIndex]?.key === key;
  }

  function prettyJson(str) {
    try {
      return JSON.stringify(JSON.parse(str), null, 2);
    } catch {
      return str;
    }
  }

  function truncateUrl(url, max = 50) {
    if (url.length <= max) return url;
    return url.substring(0, max) + '...';
  }

  function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
  }

  function copyData() {
    const d = props.data;
    if (!d || typeof d !== 'object' || Array.isArray(d)) {
      navigator.clipboard.writeText(JSON.stringify(d, null, 2));
      return;
    }
    const cleaned = {};
    for (const k of Object.keys(d)) {
      if (!shouldHideKey(k)) cleaned[k] = d[k];
    }
    navigator.clipboard.writeText(JSON.stringify(cleaned, null, 2));
  }

  /* -----------------------------
   ✅ ROUTING (based on your router)
   - Type click: route to module list pages
   - Field value click: route to edit/details if id exists
------------------------------ */

  function normalizeType(s) {
    return String(s || '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '');
  }

  const typeRoute = computed(() => {
    const t = normalizeType(props.item?.type || props.item?.typeLabel);

    // main modules
    if (t.includes('resource')) return { path: '/app/resources' };
    if (t.includes('officeemployee') || t.includes('employee')) return { name: 'officeemployee' };
    if (t.includes('event')) return { path: '/app/events' };
    if (t.includes('information')) return { path: '/app/informations' };
    if (t.includes('deleviry') || t.includes('delivery') || t.includes('captive'))
      return { path: '/app/delevirycaptive' };

    // Fifty formats (map typeLabel keywords to routes)
    if (t.includes('fasad')) return { path: '/app/fasads' };
    if (t.includes('heist')) return { path: '/app/heist' };
    if (t.includes('mulkano')) return { path: '/app/mulkanos' };
    if (t.includes('bank')) return { path: '/app/banks' };
    if (t.includes('apartment')) return { path: '/app/apartments' };
    if (t.includes('school')) return { path: '/app/schools' };
    if (t.includes('masjid') || t.includes('mosque')) return { path: '/app/masjids' };
    if (t.includes('wakilano')) return { path: '/app/wakilanos' };
    if (t.includes('university')) return { path: '/app/universities' };
    if (t.includes('park')) return { path: '/app/parks' };
    if (t.includes('company')) return { path: '/app/companies' };

    return null;
  });

  function goTypeRoute() {
    if (!typeRoute.value) return;
    model.value = false;
    router.push(typeRoute.value);
  }

  // Field → route resolver (edit when id exists)
  function fieldRouteFor(key, value) {
    const k = String(key || '').toLowerCase();

    // Resources edit route exists
    if ((k === 'resource_id' || k === 'resourceid') && String(value).trim() !== '-') {
      return { name: 'resources.edit', params: { id: String(value).trim() } };
    }

    // Office employee edit route exists
    if (
      (k === 'office_employee_id' || k === 'officeemployee_id' || k === 'employee_id') &&
      String(value).trim() !== '-'
    ) {
      return { name: 'officeemployeeform', params: { id: String(value).trim() } };
    }

    // Informations edit route exists
    if ((k === 'information_id' || k === 'informationid') && String(value).trim() !== '-') {
      return { name: 'informationform.edit', params: { id: String(value).trim() } };
    }

    // Events edit route exists
    if ((k === 'event_id' || k === 'eventid') && String(value).trim() !== '-') {
      return { name: 'EventsEdit', params: { id: String(value).trim() } };
    }

    // Delivery captive edit route exists
    if (
      (k === 'delevirycaptive_id' || k === 'deliverycaptive_id') &&
      String(value).trim() !== '-'
    ) {
      return { name: 'DeleviryCaptiveForm', params: { id: String(value).trim() } };
    }

    // Also allow: if key is "code_number" and your app has /app/resources/:code route
    if (k === 'code_number' && String(value).trim() !== '-') {
      return { name: 'ResourceDetailsByCode', params: { code: String(value).trim() } };
    }

    return null;
  }

  function goFieldRoute(key, value) {
    const r = fieldRouteFor(key, value);
    if (!r) return;
    model.value = false;
    router.push(r);
  }

  /* -----------------------------
   ✅ Export PDF (table)
------------------------------ */

  function buildCleanPayload() {
    const d = props.data;
    let payload = d;

    if (d && typeof d === 'object' && !Array.isArray(d)) {
      const cleaned = {};
      for (const k of Object.keys(d)) {
        if (!shouldHideKey(k)) cleaned[k] = d[k];
      }
      payload = cleaned;
    }

    return payload;
  }

  function escapeHtml(s) {
    return String(s)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function exportPdf() {
    const payload = buildCleanPayload();
    const title = props.item?.title || props.item?.typeLabel || 'Details';

    const entries = [];
    if (payload && typeof payload === 'object' && !Array.isArray(payload)) {
      for (const k of Object.keys(payload)) {
        entries.push([k, normalizeVal(payload[k])]);
      }
    } else {
      entries.push(['Value', normalizeVal(payload)]);
    }

    const rowsHtml = entries
      .map(([k, v]) => {
        const val = isJsonString(v) ? prettyJson(v) : v;
        return `
        <tr>
          <td class="k">${escapeHtml(formatKey(k))}</td>
          <td class="v"><pre>${escapeHtml(val)}</pre></td>
        </tr>
      `;
      })
      .join('');

    const dir = props.rtl ? 'rtl' : 'ltr';

    const html = `
  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>${escapeHtml(title)}</title>
    <style>
      @page { size: A4; margin: 14mm; }
      body { font-family: Arial, sans-serif; direction: ${dir}; }
      h2 { margin: 0 0 10px 0; font-size: 16px; }
      .meta { margin-bottom: 12px; font-size: 12px; opacity: .75; }
      table { width: 100%; border-collapse: collapse; table-layout: fixed; }
      th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
      th { background: #f5f5f5; text-align: ${dir === 'rtl' ? 'right' : 'left'}; }
      td.k { width: 32%; font-weight: 700; }
      td.v { width: 68%; }
      pre { margin: 0; white-space: pre-wrap; word-break: break-word; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 11px; line-height: 1.45; }
      .footer { margin-top: 14px; font-size: 11px; opacity: .7; }
    </style>
  </head>
  <body>
    <h2>${escapeHtml(title)}</h2>
    <div class="meta">${escapeHtml(props.item?.typeLabel || '')}</div>

    <table>
      <thead>
        <tr>
          <th>${props.rtl ? 'فیلد' : 'Field'}</th>
          <th>${props.rtl ? 'مقدار' : 'Value'}</th>
        </tr>
      </thead>
      <tbody>
        ${rowsHtml}
      </tbody>
    </table>

    <div class="footer">${escapeHtml(new Date().toLocaleString())}</div>
  </body>
  </html>
  `;

    const w = window.open('', '_blank');
    if (!w) return;

    w.document.open();
    w.document.write(html);
    w.document.close();

    w.focus();
    setTimeout(() => w.print(), 250);
  }
</script>

<style scoped>
  .details-drawer {
    border-radius: 0 16px 16px 0 !important;
  }

  /* ✅ important for Vuetify drawer content height */
  :deep(.v-navigation-drawer__content) {
    height: 100%;
    overflow: hidden; /* ✅ prevent drawer body scroll */
  }

  .drawer-content {
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden; /* ✅ prevent drawer scroll */
  }

  .header-section {
    flex-shrink: 0;
  }

  .title-section {
    min-width: 0;
  }
  .title-section h3 {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .v-avatar.has-image {
    border: 3px solid rgb(var(--v-theme-primary));
  }

  .close-btn {
    transition: transform 0.2s;
  }
  .close-btn:hover {
    transform: rotate(90deg);
  }

  /* ✅ clickable */
  .type-link {
    cursor: pointer;
    user-select: none;
  }
  .type-link.disabled {
    cursor: default;
    opacity: 0.7;
  }
  .route-value {
    cursor: pointer;
    user-select: none;
  }

  /* ✅ make the content area a flex column so the scroll area can expand */
  .details-content {
    flex: 1;
    min-height: 0; /* ✅ CRITICAL: enables child overflow scroll */
    display: flex;
    flex-direction: column;
  }

  /* ✅ only this area scrolls */
  .data-grid {
    flex: 1;
    min-height: 0; /* ✅ CRITICAL */
    overflow-y: auto;
    padding-right: 4px;
    padding-left: 2px;
  }

  /* ✅ ensure cover always works */
  .cover-img :deep(img) {
    object-fit: cover !important;
  }

  /* code blocks scroll too */
  .json-display {
    margin: 0;
    padding: 12px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono',
      'Courier New', monospace;
    font-size: 12px;
    line-height: 1.5;
    overflow-x: auto;
    max-height: 200px;
    overflow-y: auto;
  }

  .footer-actions {
    flex-shrink: 0;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
  }

  /* ✅ beautiful scrollbar */
  .nice-scroll::-webkit-scrollbar {
    width: 10px;
    height: 10px;
  }
  .nice-scroll::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.06);
    border-radius: 999px;
  }
  .nice-scroll::-webkit-scrollbar-thumb {
    background: rgba(var(--v-theme-primary), 0.35);
    border-radius: 999px;
    border: 2px solid rgba(255, 255, 255, 0.6);
  }
  .nice-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(var(--v-theme-primary), 0.55);
  }

  .nice-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(var(--v-theme-primary), 0.45) rgba(0, 0, 0, 0.06);
  }

  .data-row {
    border-radius: 12px;
    background: rgba(var(--v-theme-primary), 0.02);
    border: 1px solid rgba(var(--v-theme-primary), 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .data-row.highlight {
    background: rgba(var(--v-theme-primary), 0.05);
    border-color: rgba(var(--v-theme-primary), 0.2);
  }
  .data-row:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .key-label {
    letter-spacing: 0.5px;
    opacity: 0.9;
  }
  .value-content {
    line-height: 1.6;
    word-break: break-word;
  }
  .header-meta-line {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap; /* ✅ keep in one line */
    overflow-x: auto; /* ✅ scroll if too long */
    padding-bottom: 2px;
  }

  .meta-chip {
    white-space: nowrap;
    flex: 0 0 auto;
  }

  .meta-label {
    font-weight: 700;
    opacity: 0.85;
    margin-inline-end: 4px; /* ✅ RTL/LTR safe */
  }

  .meta-value {
    font-weight: 600;
  }
</style>
