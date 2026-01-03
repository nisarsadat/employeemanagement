<!-- src/pages/Dashboard/ZoneFormatsDialog.vue -->
<template>
  <v-dialog v-model="model" max-width="980" scrollable :scrim="true">
    <v-card rounded="xl" class="zone-details-card" :dir="rtl ? 'rtl' : 'ltr'">
      <!-- Header -->
      <div class="px-5 pt-5 pb-3 d-flex align-center justify-space-between">
        <div class="d-flex align-center" style="gap: 12px">
          <v-avatar size="46" class="avatar-glow">
            <v-icon color="white" size="22">mdi-map-marker</v-icon>
          </v-avatar>

          <div class="min-w-0">
            <div class="text-h6 font-weight-black text-truncate">
              {{ zoneName }}
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ t('تعداد فورمت‌ها') }}:
              <span class="font-weight-bold">{{ num(totalCount) }}</span>
            </div>
          </div>
        </div>

        <div class="d-flex align-center" style="gap: 8px">
          <!-- ✅ PDF -->
          <v-tooltip :text="t('دانلود PDF')" location="bottom">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                color="red-darken-1"
                variant="tonal"
                size="default"
                rounded="lg"
                prepend-icon="mdi-file-pdf-box"
                :disabled="!zone || loading"
                @click="downloadPdf"
              >
                PDF
              </v-btn>
            </template>
          </v-tooltip>

          <!-- ✅ Word -->
          <v-tooltip :text="t('دانلود Word')" location="bottom">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                color="blue-darken-1"
                variant="tonal"
                size="default"
                rounded="lg"
                prepend-icon="mdi-file-word-box"
                :disabled="!zone || loading"
                @click="downloadWord"
              >
                Word
              </v-btn>
            </template>
          </v-tooltip>

          <v-progress-circular v-if="loading" indeterminate size="22" width="2" color="primary" />
          <v-btn icon variant="text" @click="close">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
      </div>

      <v-divider />

      <!-- Body -->
      <v-card-text class="pa-5">
        <!-- Empty / No data -->
        <div v-if="!zone" class="pa-6 text-center">
          <v-icon size="64" color="grey-lighten-2" class="mb-3">mdi-database-off</v-icon>
          <div class="text-h6 text-medium-emphasis">{{ t('داده‌ای موجود نیست') }}</div>
        </div>

        <div v-else>
          <!-- Cards grid -->
          <v-row dense>
            <v-col
              v-for="(c, idx) in cards"
              :key="`${c.key}-${idx}`"
              cols="12"
              sm="6"
              md="4"
              lg="4"
            >
              <v-card
                class="mini-stat clickable"
                rounded="xl"
                variant="flat"
                border
                ripple
                :disabled="c.key === 'total'"
                @click="onCardClick(c)"
              >
                <div class="mini-accent"></div>

                <v-card-text class="pa-4">
                  <div class="d-flex align-center justify-space-between mb-3">
                    <div class="d-flex align-center" style="gap: 10px">
                      <v-avatar size="34" class="bg-soft-primary">
                        <v-icon color="primary" size="18">{{ c.icon }}</v-icon>
                      </v-avatar>

                      <div class="min-w-0">
                        <div class="text-body-2 font-weight-bold text-truncate">
                          {{ c.label }}
                        </div>
                        <div class="text-caption text-medium-emphasis">
                          {{ t('ثبت شده') }}
                        </div>
                      </div>
                    </div>

                    <v-chip size="small" variant="tonal" color="primary">
                      {{ num(c.value) }}
                    </v-chip>
                  </div>

                  <v-progress-linear
                    :model-value="percent(c.value)"
                    height="7"
                    rounded
                    color="primary"
                    bg-color="rgba(var(--v-theme-primary), 0.10)"
                  />
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- Fallback note -->
          <div
            v-if="cards.length === 1 && cards[0].key === 'total'"
            class="mt-4 text-caption text-medium-emphasis"
          >
            {{ t('جزییات نوع فورمت‌ها در API موجود نیست؛ فقط مجموع نمایش داده شد.') }}
          </div>

          <div v-else class="mt-4 text-caption text-medium-emphasis">
            {{ t('برای رفتن به صفحه فورمت، روی کارت کلیک کنید.') }}
          </div>
        </div>
      </v-card-text>

      <v-divider />

      <!-- Footer -->
      <div class="px-5 py-4 d-flex align-center justify-space-between">
        <div class="text-caption text-medium-emphasis">
          {{ t('منطقه') }}: <span class="font-weight-bold">{{ zoneName }}</span>
        </div>

        <v-btn color="primary" variant="tonal" rounded="xl" @click="close">
          {{ t('بستن') }}
          <v-icon end>mdi-close</v-icon>
        </v-btn>
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
    zone: { type: Object, default: null },
    loading: { type: Boolean, default: false },
  });

  const emit = defineEmits(['update:modelValue', 'close']);

  const { t, locale } = useI18n();
  const rtl = computed(() => String(locale.value).toLowerCase().startsWith('fa'));
  const router = useRouter();

  const model = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
  });

  function close() {
    model.value = false;
    emit('close');
  }

  const zoneName = computed(() => props.zone?.zone || t('نامشخص'));
  const totalCount = computed(() => Number(props.zone?.total ?? 0));

  function num(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n.toLocaleString() : '0';
  }

  function percent(v) {
    const total = totalCount.value || 0;
    if (!total) return 0;
    return Math.max(0, Math.min(100, (Number(v) / total) * 100));
  }

  /**
   * ✅ Route mapping:
   * key (DB table) -> route path in your app
   * Adjust paths ONLY if your router uses different ones.
   */
  const formatRouteMap = {
    fasads: '/app/fasads',
    saeen_fasads: '/app/saeenfasad',
    heists: '/app/heist',
    captives: '/app/captives',
    mulkanos: '/app/mulkanos',

    entry_exits: '/app/entry-exits',
    sarafyanos: '/app/sarafyanos',
    clubs: '/app/clubs',
    mahajerats: '/app/mahajerats',
    soldiers: '/app/soldiers',

    mawads: '/app/mawads',
    lost_mujremeens: '/app/lostmujremeens',
    old_soldiers_homes: '/app/oldsoldiershomes',
    robbers: '/app/robbers',
    non_responsable_people: '/app/nonresponsablepeople',
    mashkok_homes: '/app/mashkokhomes',
    damage_places: '/app/damageplaces',
    damage_rasan_places: '/app/damagerasanplaces',
    mashkok_shops: '/app/mashkokshops',
    luchakanos: '/app/luchakanos',

    schools: '/app/schools',
    hado_bus_stops: '/app/hadobusstops',
    transport_companies: '/app/transportcompanies',
    car_sales_companies: '/app/carsalescompanies',
    parkings: '/app/parkings',
    banks: '/app/banks',
    apartments: '/app/apartments',
    rahnoma_mamlats: '/app/rahnomamamlats',
    hosppitals: '/app/hosppitals',
    work_shops: '/app/workshops',

    badmashanos: '/app/badmashanos',
    emarat_offices: '/app/emaratoffices',
    hostels: '/app/hostels',
    hotels: '/app/hotels',
    madrasas: '/app/madrasas',

    mashkok_people: '/app/mashkokpeople',
    mujahedinos: '/app/mujahedinos',
    old_jahadis: '/app/oldjahadis',
    sarayanos: '/app/sarayanos',
    tujarans: '/app/tujarans',

    checkpoints: '/app/checkpoints',
    highways: '/app/highways',
    donelostplaces: '/app/donelostplaces',
    econemy_projects: '/app/econemyprojects',
    criminal_capture_places: '/app/criminalcaptureplaces',
    pumps: '/app/pumps',
    parks: '/app/parks',
    complexes: '/app/complexes',
    universities: '/app/universities',
    wakilanos: '/app/wakilanos',
  };

  function toIcon(key) {
    const map = {
      fasads: 'mdi-file-document',
      saeen_fasads: 'mdi-file-star',
      heists: 'mdi-handcuffs',
      captives: 'mdi-account-lock',
      mulkanos: 'mdi-home-city',
      schools: 'mdi-school',
      banks: 'mdi-bank',
      hotels: 'mdi-bed',
      hostels: 'mdi-bed-outline',
      pumps: 'mdi-fuel',
      parks: 'mdi-pine-tree',
      universities: 'mdi-school-outline',
      wakilanos: 'mdi-account-tie',
      total: 'mdi-counter',
    };
    return map[key] || 'mdi-file-document-outline';
  }

  /**
   * ✅ Use your new backend structure:
   * zone.breakdown_items: [{ key, name, count }]
   * fallback to total if missing.
   */
  const cards = computed(() => {
    const z = props.zone || null;
    if (!z) return [];

    if (Array.isArray(z.breakdown_items) && z.breakdown_items.length) {
      return z.breakdown_items
        .map((x) => ({
          key: x.key,
          label: x.name || x.key,
          value: Number(x.count ?? 0),
          icon: toIcon(x.key),
        }))
        .filter((x) => x.value > 0)
        .sort((a, b) => b.value - a.value);
    }

    if (z.breakdown && typeof z.breakdown === 'object') {
      return Object.entries(z.breakdown)
        .map(([k, v]) => ({
          key: k,
          label: String(k).replaceAll('_', ' '),
          value: Number(v ?? 0),
          icon: toIcon(k),
        }))
        .filter((x) => x.value > 0)
        .sort((a, b) => b.value - a.value);
    }

    return [
      {
        key: 'total',
        label: t('مجموع'),
        value: totalCount.value,
        icon: toIcon('total'),
      },
    ];
  });

  /**
   * ✅ When user clicks a format card:
   * - route to that format page
   * - include zone in query, so that page can filter by zone if it supports it
   */
  function onCardClick(card) {
    if (!card || card.key === 'total') return;

    const path = formatRouteMap[card.key];
    if (!path) return;

    close();

    router.push({
      path,
      query: {
        zone: props.zone?.zone || '',
      },
    });
  }

  /* ------------------------------------------------------------------ */
  /* ✅ EXPORTS (TABLE)                                                  */
  /* ------------------------------------------------------------------ */

  function fileSafeName(s) {
    return String(s || 'zone')
      .trim()
      .replace(/[\\/:*?"<>|]+/g, '-')
      .replace(/\s+/g, '-')
      .slice(0, 80);
  }

  function exportRows() {
    // Only meaningful rows (if only total exists, still export it)
    const rows = (cards.value || []).map((c) => ({
      label: c.label,
      count: Number(c.value ?? 0),
      percent: percent(c.value),
    }));

    // if cards is empty but zone exists, export total
    if (!rows.length && props.zone) {
      rows.push({
        label: t('مجموع'),
        count: totalCount.value,
        percent: 100,
      });
    }

    return rows;
  }

  function buildExportHtml() {
    const rows = exportRows();
    const dir = rtl.value ? 'rtl' : 'ltr';

    const now = new Date();
    const dateStr = now.toLocaleString();

    const head = `
<!doctype html>
<html lang="${rtl.value ? 'fa' : 'en'}" dir="${dir}">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>${zoneName.value}</title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; padding: 24px; }
  h2 { margin: 0 0 8px 0; }
  .meta { margin: 0 0 16px 0; font-size: 12px; opacity: .8; }
  table { width: 100%; border-collapse: collapse; margin-top: 12px; }
  th, td { border: 1px solid #ddd; padding: 10px; font-size: 13px; }
  th { background: #f6f6f6; text-align: ${rtl.value ? 'right' : 'left'}; }
  td.num { text-align: ${rtl.value ? 'left' : 'right'}; white-space: nowrap; }
</style>
</head>
<body>
  <h2>${t('جزئیات فورمت‌ها')} — ${escapeHtml(zoneName.value)}</h2>
  <div class="meta">
    ${t('تعداد فورمت‌ها')}: <b>${num(totalCount.value)}</b>
    <span style="margin: 0 8px;">•</span>
    ${escapeHtml(dateStr)}
  </div>
`;

    const table = `
  <table>
    <thead>
      <tr>
        <th style="width: 55%;">${t('فورمت')}</th>
        <th style="width: 25%;">${t('تعداد')}</th>
        <th style="width: 20%;">${t('فیصدی')}</th>
      </tr>
    </thead>
    <tbody>
      ${rows
        .map(
          (r) => `
        <tr>
          <td>${escapeHtml(r.label)}</td>
          <td class="num">${num(r.count)}</td>
          <td class="num">${Number(r.percent || 0).toFixed(1)}%</td>
        </tr>`
        )
        .join('')}
    </tbody>
  </table>
</body>
</html>`;

    return head + table;
  }

  function escapeHtml(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function downloadPdf() {
    const html = buildExportHtml();

    const w = window.open('', '_blank');
    if (!w) return;

    w.document.open();
    w.document.write(html);
    w.document.close();

    // Give the browser a tick then print
    w.focus();
    setTimeout(() => {
      w.print();
    }, 150);
  }

  function downloadWord() {
    const html = buildExportHtml();

    const blob = new Blob([html], { type: 'application/msword;charset=utf-8' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `${fileSafeName(zoneName.value)}-formats.doc`;
    document.body.appendChild(a);
    a.click();
    a.remove();

    setTimeout(() => URL.revokeObjectURL(url), 800);
  }
</script>

<style scoped>
  .zone-details-card {
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

  .bg-soft-primary {
    background: rgba(var(--v-theme-primary), 0.1);
  }

  .mini-stat {
    position: relative;
    border: 1px solid rgba(0, 0, 0, 0.08);
    overflow: hidden;
  }

  .mini-accent {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(
      90deg,
      rgba(var(--v-theme-primary), 1),
      rgba(var(--v-theme-teal), 1)
    );
    opacity: 0.9;
  }

  .clickable {
    cursor: pointer;
    transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
  }

  .clickable:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 26px rgba(0, 0, 0, 0.1);
    border-color: rgba(var(--v-theme-primary), 0.22);
  }

  /* disabled (total) card */
  :deep(.v-card--disabled) {
    opacity: 0.75;
    cursor: default;
  }

  /* Dark mode */
  :deep(.v-theme--dark) .zone-details-card,
  :deep(.v-theme--dark) .mini-stat {
    border-color: rgba(255, 255, 255, 0.1);
  }
</style>
