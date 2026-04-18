<template>
  <v-dialog v-model="model" max-width="520" scrollable>
    <v-card class="details-card" rounded="xl" elevation="10" :dir="rtl ? 'rtl' : 'ltr'">
      <!-- HEADER -->
      <v-card-title class="d-flex align-center justify-space-between">
        <div class="title-section">
          <div class="text-subtitle-1 font-weight-bold text-primary title-line">
            {{ item?.title || item?.typeLabel || (rtl ? 'جزییات' : 'Details') }}
          </div>

          <!-- Type label (routable) -->
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
              {{ item?.typeLabel || (rtl ? 'ډول' : 'Type') }}
            </span>
            <v-icon v-if="typeRoute" size="14" class="ml-1">mdi-open-in-new</v-icon>
          </div>
        </div>

        <div class="d-flex align-center" style="gap: 8px">
          <v-tooltip :text="rtl ? 'موقعیت ته لاړ شه' : 'Fly to location'" location="bottom">
            <template #activator="{ props: tip }">
              <v-btn
                icon
                variant="flat"
                size="small"
                v-bind="tip"
                @click="$emit('flyToItem', item)"
                :disabled="!item?.coordinates"
              >
                <v-icon>mdi-crosshairs-gps</v-icon>
              </v-btn>
            </template>
          </v-tooltip>

          <v-btn icon variant="text" size="small" @click="model = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
      </v-card-title>

      <v-divider />

      <!-- BODY -->
      <v-card-text class="pt-4">
        <v-skeleton-loader v-if="loading" type="article, actions" class="mb-2" />

        <v-alert v-else-if="!data" type="info" variant="tonal" class="mb-0" :icon="false">
          <div class="d-flex align-center">
            <v-icon color="info" class="mr-3">mdi-information-outline</v-icon>
            <span>{{ rtl ? 'هیڅ معلومات نشته' : 'No data available' }}</span>
          </div>
        </v-alert>

        <div v-else>
          <div v-if="headerMeta.length" class="header-meta-line">
            <v-chip
              v-for="m in headerMeta"
              :key="m.key"
              size="small"
              variant="tonal"
              class="meta-chip"
            >
              <v-icon start size="16">{{ m.icon }}</v-icon>
              <span class="meta-label">{{ m.label }}:</span>
              <span class="meta-value">{{ m.value }}</span>
            </v-chip>
          </div>
        </div>
      </v-card-text>

      <v-divider />

      <!-- FOOTER -->
      <v-card-actions class="justify-end px-4 py-3" style="gap: 8px">
        <v-btn
          v-if="typeRoute"
          color="primary"
          size="small"
          prepend-icon="mdi-open-in-new"
          @click="goTypeRoute"
          :disabled="loading"
        >
          {{ rtl ? 'مربوطه صفحې ته لاړ شه' : 'Open related page' }}
        </v-btn>

        <v-btn
          color="primary"
          size="small"
          prepend-icon="mdi-content-copy"
          @click="copyHeaderOnly"
          :disabled="!data || loading"
        >
          {{ rtl ? 'کاپي' : 'Copy' }}
        </v-btn>

        <v-btn
          color="primary"
          size="small"
          prepend-icon="mdi-file-pdf-box"
          @click="exportPdfHeaderOnly"
          :disabled="!data || loading"
        >
          {{ rtl ? 'پي ډي اېف' : 'PDF' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const props = defineProps({
  modelValue: Boolean,
  rtl: Boolean,
  loading: Boolean,
  item: Object,
  data: [Object, Array, String, Number, Boolean, null],
});

const emit = defineEmits(['update:modelValue', 'flyToItem']);

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
});

/* ---------------- Helpers ---------------- */
function pickFirst(...vals) {
  for (const v of vals) {
    if (v === null || v === undefined) continue;
    const s = String(v).trim();
    if (s && s !== '-' && s !== 'null' && s !== 'undefined') return s;
  }
  return '';
}

function joinPhones(...vals) {
  const out = [];
  for (const v of vals) {
    if (v === null || v === undefined) continue;
    const s = String(v).trim();
    if (!s || s === '-' || s === 'null' || s === 'undefined') continue;
    if (!out.includes(s)) out.push(s);
  }
  return out.join(' / ');
}

function normalizeType(s) {
  return String(s || '')
    .trim()
    .toLowerCase()
    .replace(/\s+/g, '')
    .replace(/[-_]/g, '');
}

/* ---------------- Header meta ---------------- */
const headerMeta = computed(() => {
  const d = props.data && typeof props.data === 'object' ? props.data : {};
  const it = props.item || {};
  const t = normalizeType(it?.type || it?.typeLabel);

  if (t.includes('officeemployee') || t.includes('employee')) {
    const nickName = pickFirst(
      d?.nick_name,
      d?.office_employee?.nick_name,
      d?.officeEmployee?.nick_name,
      it?.nick_name,
      it?._raw?.nick_name
    );

    const code = pickFirst(
      d?.code_number,
      d?.office_employee_code,
      d?.office_employee?.code_number,
      d?.officeEmployee?.code_number,
      it?.code_number,
      it?._raw?.code_number
    );

    const phone = pickFirst(
      d?.phone_number,
      d?.office_employee_phone,
      d?.office_employee?.phone_number,
      d?.officeEmployee?.phone_number,
      it?.phone_number,
      it?._raw?.phone_number
    );

    const out = [];
    if (nickName) {
      out.push({
        key: 'nick_name',
        label: props.rtl ? 'مستعار نوم' : 'Nick Name',
        value: nickName,
        icon: 'mdi-account-outline',
      });
    }
    if (code) {
      out.push({
        key: 'code',
        label: props.rtl ? 'د کارمند کود' : 'Office Employee Code',
        value: code,
        icon: 'mdi-account-badge-outline',
      });
    }
    if (phone) {
      out.push({
        key: 'phone',
        label: props.rtl ? 'د کارمند د تلیفون شمیره' : 'Phone',
        value: phone,
        icon: 'mdi-phone',
      });
    }
    return out;
  }

  if (t.includes('resource')) {
    const nickName = pickFirst(
      d?.nick_name,
      d?.resource?.nick_name,
      it?.nick_name,
      it?._raw?.nick_name,
      it?._raw?.resource?.nick_name
    );

    const code = pickFirst(
      d?.code_number,
      d?.resource_code,
      it?.code_number,
      it?._raw?.code_number
    );

    const phoneList = [d?.phone_number, d?.phone_number_1, d?.phone_number_2, d?.phone_number_3]
      .filter((v) => v !== null && v !== undefined)
      .map((v) => String(v).trim())
      .filter((v) => v && v !== '-' && v !== 'null' && v !== 'undefined');

    const phones = [...new Set(phoneList)].join(' / ');

    const out = [];

    if (nickName) {
      out.push({
        key: 'nick_name',
        label: props.rtl ? 'مستعار نوم' : 'Nick Name',
        value: nickName,
        icon: 'mdi-account-outline',
      });
    }

    if (code) {
      out.push({
        key: 'code',
        label: props.rtl ? 'د منبع کود' : 'Resource Code',
        value: code,
        icon: 'mdi-barcode',
      });
    }

    if (phones) {
      out.push({
        key: 'phones',
        label: props.rtl ? 'د منبع د تلیفون شمیرې' : 'Phones',
        value: phones,
        icon: 'mdi-phone-multiple',
      });
    }

    return out;
  }

  const resourceCode = pickFirst(
    d?.resource_code,
    d?.resource?.code_number,
    d?.resource?.code,
    it?.resource_code,
    it?._raw?.resource_code,
    it?._raw?.resource?.code_number
  );

  const resourcePhones = joinPhones(
    d?.resource_phone,
    d?.resource_phone_1,
    d?.resource_phone_2,
    d?.resource_phone_3,
    d?.resource?.phone_number,
    d?.resource?.phone_number_1,
    d?.resource?.phone_number_2,
    d?.resource?.phone_number_3,
    it?.resource_phone,
    it?._raw?.resource_phone,
    it?._raw?.resource?.phone_number
  );

  const out = [];
  if (resourceCode) {
    out.push({
      key: 'resource_code',
      label: props.rtl ? 'د منبع کود' : 'Resource Code',
      value: resourceCode,
      icon: 'mdi-barcode',
    });
  }
  if (resourcePhones) {
    out.push({
      key: 'resource_phones',
      label: props.rtl ? 'د منبع د تلیفون شمیره' : 'Resource Phone',
      value: resourcePhones,
      icon: 'mdi-phone',
    });
  }
  return out;
});

/* ---------------- ROUTING ---------------- */
/*
  IMPORTANT:
  We no longer hardcode route maps here.
  Drawer uses backend-provided route metadata:
  item._listPath
  item._relationQueryKey
*/
const typeRoute = computed(() => {
  const path = props.item?._listPath;
  const relationKey = props.item?._relationQueryKey;
  const id = props.item?.id;

  if (!path || !relationKey || !id) return null;

  return {
    path,
    query: {
      [relationKey]: id,
      source: 'related',
      open: 'peek',
    },
  };
});

function goTypeRoute() {
  console.log('goTypeRoute:', typeRoute.value, props.item);

  if (!typeRoute.value) return;
  model.value = false;
  router.push(typeRoute.value);
}
/* ---------------- Copy / PDF ---------------- */
function copyHeaderOnly() {
  const title = props.item?.title || props.item?.typeLabel || (props.rtl ? 'جزییات' : 'Details');
  const lines = [
    `${title}`,
    `${props.item?.typeLabel || ''}`.trim(),
    ...headerMeta.value.map((m) => `${m.label}: ${m.value}`),
  ].filter(Boolean);

  navigator.clipboard.writeText(lines.join('\n'));
}

function escapeHtml(s) {
  return String(s ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function exportPdfHeaderOnly() {
  const title = props.item?.title || props.item?.typeLabel || 'Details';
  const dir = props.rtl ? 'rtl' : 'ltr';

  const rowsHtml = headerMeta.value
    .map(
      (m) => `
      <tr>
        <td class="k">${escapeHtml(m.label)}</td>
        <td class="v">${escapeHtml(m.value)}</td>
      </tr>
    `
    )
    .join('');

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
    th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; text-align: ${
      dir === 'rtl' ? 'right' : 'left'
    }; }
    th { background: #f5f5f5; }
    td.k { width: 35%; font-weight: 700; }
    td.v { width: 65%; }
    .footer { margin-top: 14px; font-size: 11px; opacity: .7; }
  </style>
</head>
<body>
  <h2>${escapeHtml(title)}</h2>
  <div class="meta">${escapeHtml(props.item?.typeLabel || '')}</div>

  <table>
    <thead>
      <tr>
        <th>${props.rtl ? 'سرلیک' : 'Label'}</th>
        <th>${props.rtl ? 'معلومات' : 'Value'}</th>
      </tr>
    </thead>
    <tbody>
      ${rowsHtml || `<tr><td class="k">-</td><td class="v">-</td></tr>`}
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
.details-card {
  max-height: 70vh;
}

.title-line {
  max-width: 380px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.type-link {
  cursor: pointer;
  user-select: none;
}
.type-link.disabled {
  cursor: default;
  opacity: 0.7;
}

.header-meta-line {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  padding-bottom: 2px;
}

.meta-chip {
  white-space: nowrap;
  border-radius: 999px;
}

.meta-label {
  font-weight: 700;
  opacity: 0.85;
  margin-inline-end: 4px;
}

.meta-value {
  font-weight: 600;
}
</style>