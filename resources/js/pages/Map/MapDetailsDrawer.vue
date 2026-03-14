<template>
  <!-- ✅ Small popup instead of big drawer -->
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
        <!-- Loading -->
        <v-skeleton-loader v-if="loading" type="article, actions" class="mb-2" />

        <!-- Empty -->
        <v-alert v-else-if="!data" type="info" variant="tonal" class="mb-0" :icon="false">
          <div class="d-flex align-center">
            <v-icon color="info" class="mr-3">mdi-information-outline</v-icon>
            <span>{{ rtl ? 'هیڅ معلومات نشته' : 'No data available' }}</span>
          </div>
        </v-alert>

        <!-- Header Chips -->
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

      <!-- FOOTER (small) -->
      <v-divider />
      <v-card-actions class="justify-end px-4 py-3" style="gap: 8px">
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

  /* ---------------- ✅ Header meta rules ----------------
   ✅ No ID anywhere
   ✅ Resource shows: nick_name + resource code + phones
   ✅ Office employee shows: nick_name + office employee code + phone
-------------------------------------------------------- */
  const headerMeta = computed(() => {
    const d = props.data && typeof props.data === 'object' ? props.data : {};
    const it = props.item || {};
    const t = normalizeType(it?.type || it?.typeLabel);

    // ========= OFFICE EMPLOYEE (Nick Name + Code + Phone) =========
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
      if (nickName)
        out.push({
          key: 'nick_name',
          label: props.rtl ? 'مستعار نوم' : 'Nick Name',
          value: nickName,
          icon: 'mdi-account-outline',
        });
      if (code)
        out.push({
          key: 'code',
          label: props.rtl ? 'د کارمند کود' : 'Office Employee Code',
          value: code,
          icon: 'mdi-account-badge-outline',
        });
      if (phone)
        out.push({
          key: 'phone',
          label: props.rtl ? 'د کارمند د تلیفون شمیره' : 'Phone',
          value: phone,
          icon: 'mdi-phone',
        });
      return out;
    }

    // ========= RESOURCE (Nick Name + Code + Phones) =========
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

      if (nickName)
        out.push({
          key: 'nick_name',
          label: props.rtl ? 'مستعار نوم' : 'Nick Name',
          value: nickName,
          icon: 'mdi-account-outline',
        });

      if (code)
        out.push({
          key: 'code',
          label: props.rtl ? 'د منبع کود' : 'Resource Code',
          value: code,
          icon: 'mdi-barcode',
        });

      if (phones)
        out.push({
          key: 'phones',
          label: props.rtl ? 'د منبع د تلیفون شمیرې' : 'Phones',
          value: phones,
          icon: 'mdi-phone-multiple',
        });

      return out;
    }

    // ========= ALL OTHER FORMATS =========
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
    if (resourceCode)
      out.push({
        key: 'resource_code',
        label: props.rtl ? 'د منبع کود' : 'Resource Code',
        value: resourceCode,
        icon: 'mdi-barcode',
      });
    if (resourcePhones)
      out.push({
        key: 'resource_phones',
        label: props.rtl ? 'د منبع د تلیفون شمیره' : 'Resource Phone',
        value: resourcePhones,
        icon: 'mdi-phone',
      });
    return out;
  });

  /* ---------------- ROUTING ---------------- */
  const typeRoute = computed(() => {
    const t = normalizeType(props.item?.type || props.item?.typeLabel);
    const id = props.item?.id;

    if (!id) return null;

    const routeMap = {
      officeemployee: '/app/officeemployee',
      resource: '/app/resources',
      information: '/app/informations',
      captivedelivery: '/app/delevirycaptive',
      event: '/app/events',

      fasad: '/app/fasads',
      saeenfasad: '/app/saeenfasad',
      heist: '/app/heist',
      captive: '/app/captives',
      mulkano: '/app/mulkanos',
      entryexit: '/app/entry-exits',
      sarafyano: '/app/sarafyanos',
      clubs: '/app/clubs',
      mahajerat: '/app/mahajerats',
      soldier: '/app/soldiers',

      mawad: '/app/mawads',
      lostmujremeen: '/app/lostmujremeens',
      oldsoldiershomes: '/app/oldsoldiershomes',
      robbers: '/app/robbers',
      nonresponsablepeople: '/app/nonresponsablepeople',
      mashkokhomes: '/app/mashkokhomes',
      damageplaces: '/app/damageplaces',
      damagerasanplaces: '/app/damagerasanplaces',
      mashkokshops: '/app/mashkokshops',
      luchakanos: '/app/luchakanos',

      schools: '/app/schools',
      hadobusstops: '/app/hadobusstops',
      transportcompanies: '/app/transportcompanies',
      carsalescompanies: '/app/carsalescompanies',
      parkings: '/app/parkings',
      banks: '/app/banks',
      apartments: '/app/apartments',
      rahnomamamlats: '/app/rahnomamamlats',
      hosppitals: '/app/hosppitals',
      workshops: '/app/workshops',

      badmashanos: '/app/badmashanos',
      emaratoffices: '/app/emaratoffices',
      hostels: '/app/hostels',
      hotels: '/app/hotels',
      madrasas: '/app/madrasas',
      mashkokpeople: '/app/mashkokpeoples',
      mujahedinos: '/app/mujahedinos',
      oldjahadis: '/app/oldjahadis',
      sarayanos: '/app/sarayanos',
      tujarans: '/app/tujarans',

      checkpoints: '/app/checkpoints',
      masjids: '/app/masjids',
      highways: '/app/highways',
      companies: '/app/companies',
      donelostplaces: '/app/donelostplaces',
      econemyprojects: '/app/econemyprojects',
      criminalcaptureplaces: '/app/criminalcaptureplaces',
      pumps: '/app/pumps',
      shoras: '/app/shoras',
      governmentplaces: '/app/governmentplaces',
      supermarkets: '/app/supermarkets',
      parks: '/app/parks',
      complexes: '/app/complexes',
      deplomaticplaces: '/app/deplomaticplaces',
      universities: '/app/universities',
      wakilanos: '/app/wakilanos',

      jawasis: '/app/jawasis',
    };

    const aliases = {
      officeemployees: 'officeemployee',
      employees: 'officeemployee',

      resources: 'resource',
      informations: 'information',
      captivedeliveries: 'captivedelivery',
      events: 'event',

      saeenbalfasad: 'saeenfasad',
      saeenfasads: 'saeenfasad',
      captives: 'captive',
      mulkanos: 'mulkano',
      entryexits: 'entryexit',
      sarafyanos: 'sarafyano',
      club: 'clubs',
      clubs: 'clubs',
      mahajerats: 'mahajerat',
      soldiers: 'soldier',

      mawads: 'mawad',
      lostmujremeens: 'lostmujremeen',
      oldsoldierhomes: 'oldsoldiershomes',
      nonresponsiblepeople: 'nonresponsablepeople',
      nonresponsableperson: 'nonresponsablepeople',
      mashkokhome: 'mashkokhomes',
      damageplace: 'damageplaces',
      damagerasanplace: 'damagerasanplaces',
      mashkokshop: 'mashkokshops',
      luchakano: 'luchakanos',

      school: 'schools',
      hadobusstop: 'hadobusstops',
      hadobusstops: 'hadobusstops',
      transportcompany: 'transportcompanies',
      carsalescompany: 'carsalescompanies',
      parking: 'parkings',
      bank: 'banks',
      apartment: 'apartments',
      rahnomamamlat: 'rahnomamamlats',
      hosppital: 'hosppitals',
      hospital: 'hosppitals',
      hospitals: 'hosppitals',
      workshop: 'workshops',
      workshops: 'workshops',

      badmashano: 'badmashanos',
      emaratoffice: 'emaratoffices',
      hostel: 'hostels',
      hotel: 'hotels',
      madrasa: 'madrasas',
      mashkokpeople: 'mashkokpeople',
      mashkokperson: 'mashkokpeople',
      mujahedino: 'mujahedinos',
      oldjahadi: 'oldjahadis',
      sarayano: 'sarayanos',
      tujaran: 'tujarans',

      checkpoint: 'checkpoints',
      masjid: 'masjids',
      highway: 'highways',
      company: 'companies',
      donelostplace: 'donelostplaces',
      econemyproject: 'econemyprojects',
      economyproject: 'econemyprojects',
      criminalcaptureplace: 'criminalcaptureplaces',
      pump: 'pumps',
      shora: 'shoras',
      governmentplace: 'governmentplaces',
      supermarket: 'supermarkets',
      park: 'parks',
      complex: 'complexes',
      deplomaticplace: 'deplomaticplaces',
      diplomaticplace: 'deplomaticplaces',
      university: 'universities',
      wakilano: 'wakilanos',

      jawasi: 'jawasis',
    };

    const normalized = aliases[t] || t;
    const path = routeMap[normalized];

    if (!path) return null;

    return {
      path,
      query: { id },
    };
  });

  function goTypeRoute() {
    if (!typeRoute.value) return;
    model.value = false;
    router.push(typeRoute.value);
  }

  /* ---------------- Copy / PDF (Header meta only) ---------------- */
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
