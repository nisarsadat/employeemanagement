<template>
  <AppBar title="نقشه"></AppBar>

  <div class="map-wrap" :dir="isRtl ? 'rtl' : 'ltr'">
    <div ref="mapEl" class="map"></div>

    <div class="map-top-tools" @click.stop>
      <PmtilesSearchMap class="search-panel" @flyTo="flyTo" />

      <v-card class="type-filter-card" rounded="xl" elevation="3">
        <div class="type-filter-row">
          <v-select
            v-model="pendingSelectedTypes"
            :items="typeOptions"
            item-title="label"
            item-value="value"
            label="د نقشې فیلټر"
            multiple
            chips
            closable-chips
            clearable
            hide-details
            density="compact"
            variant="outlined"
            class="type-filter-select"
          />

          <v-btn
            color="primary"
            variant="elevated"
            density="comfortable"
            rounded="lg"
            class="filter-btn"
            @click="applyTypeFilter"
          >
            فیلتر
          </v-btn>

          <v-btn
            variant="text"
            density="comfortable"
            rounded="lg"
            class="clear-filter-btn"
            @click="clearTypeFilter"
          >
            پاک
          </v-btn>
        </div>
      </v-card>

      <v-menu v-model="pointsMenuOpen" :close-on-content-click="false" location="bottom">
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            class="points-btn"
            variant="elevated"
            density="comfortable"
            rounded="xl"
            prepend-icon="mdi-format-list-bulleted"
          >
            نقطې ({{ allMapItems.length }})
          </v-btn>
        </template>

        <v-card class="points-menu-card" rounded="xl">
          <div class="points-menu-head">
            <div class="points-title">په نقشه کې نقطې</div>
            <div class="points-sub">
              ټول: {{ filteredMapItems.length }} / {{ allMapItems.length }}
            </div>
          </div>

          <v-divider />

          <v-card-text class="points-menu-body">
            <v-text-field
              v-model="pointsQ"
              density="compact"
              variant="outlined"
              hide-details
              clearable
              prepend-inner-icon="mdi-magnify"
              label="لټون"
              class="mb-3"
            />

            <div v-for="g in groupedPoints" :key="g.type" class="type-block">
              <div class="type-head">
                <div class="type-name">
                  <span class="type-dot"></span>
                  {{ g.label }}
                </div>
                <div class="type-count">{{ g.count }}</div>
              </div>

              <v-list density="compact" class="type-list" nav>
                <v-list-item
                  v-for="it in g.items"
                  :key="`${it.type}:${it.id}`"
                  @click="
                    () => {
                      pointsMenuOpen = false;
                      flyToItem(it);
                      openDetailsMenu(it);
                    }
                  "
                >
                  <template #prepend>
                    <v-icon
                      :color="
                        it.type === 'resource'
                          ? it.is_active
                            ? 'green-darken-2'
                            : 'red-darken-2'
                          : 'primary'
                      "
                    >
                      {{ safeMdiFor(it) }}
                    </v-icon>
                  </template>

                  <v-list-item-title class="mini-title">
                    {{ it.title || it.id }}
                  </v-list-item-title>

                  <v-list-item-subtitle class="mini-sub">
                    {{ it.subtitle }}
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </div>

            <div v-if="!groupedPoints.length" class="empty-points">هېڅ نقطه ونه موندل شوه.</div>
          </v-card-text>

          <v-divider />

          <div class="points-menu-actions">
            <v-btn variant="text" rounded="lg" @click="() => (pointsMenuOpen = false)"> تړل </v-btn>
          </div>
        </v-card>
      </v-menu>
    </div>

    <MapDetailsDrawer
      v-model="detailsOpen"
      :rtl="isRtl"
      :loading="detailsLoading"
      :item="selectedItem"
      :data="detailsData"
      @flyToItem="flyToItem"
      @openRelated="goToRelatedPage"
    />
  </div>
</template>

<script setup>
  import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import maplibregl from 'maplibre-gl';
  import 'maplibre-gl/dist/maplibre-gl.css';
  import { PMTiles, Protocol } from 'pmtiles';
  import PmtilesSearchMap from './PmtilesSearchMap.vue';
  import MapDetailsDrawer from './MapDetailsDrawer.vue';
  import { ICON_MAP } from '../../../constants/moduleIcons';
  import { useMapBusStore } from '../../stores/MapBusStore';
  import AppBar from '@/components/AppBar.vue';
  import { useRouter } from 'vue-router';
  const router = useRouter();
  // MDI paths
  import {
    mdiSilverwareForkKnife,
    mdiBed,
    mdiAccountMultiplePlus,
    mdiAccountCheck,
    mdiAccountRemove,
    mdiCarBack,
    mdiBankTransfer,
    mdiAccountMinus,
    mdiWarehouse,
    mdiHighway,
    mdiAccountMultipleRemove,
    mdiTrainCar,
    mdiHomeGroupRemove,
    mdiHomePlusOutline,
    mdiAccountStar,
    mdiAccountQuestion,
    mdiTownHall,
    mdiBookOpenBlankVariantOutline,
    mdiCarMultiple,
    mdiBusMultiple,
    mdiCarHatchback,
    mdiCarBrakeParking,
    mdiBank,
    mdiCity,
    mdiStoreSearchOutline,
    mdiHospitalBox,
    mdiCarWrench,
    mdiAccountEdit,
    mdiAccountDetails,
    mdiSchool,
    mdiAccountBoxMinusOutline,
    mdiMosque,
    mdiFactory,
    mdiGymnastics,
    mdiSelectPlace,
    mdiFireplaceOff,
    mdiFireplace,
    mdiHomeGroup,
    mdiPoliceBadge,
    mdiOfficeBuildingPlus,
    mdiAccountLock,
    mdiGreenhouse,
    mdiAccountAlert,
    mdiGasStation,
    mdiRobber,
    mdiAccountSwitch,
    mdiTreeOutline,
    mdiPistol,
    mdiHomeAnalytics,
    mdiCart,
    mdiBriefcase,
    mdiBriefcaseVariant,
    mdiShieldLock,
    mdiInformationOutline,
    mdiAccountPlus,
    mdiCircle,
    mdiClosedCaption,
  } from '@mdi/js';

  const mapBus = useMapBusStore();

  const { locale } = useI18n();
  const isRtl = computed(() =>
    ['fa', 'prs', 'ps', 'ar'].includes(String(locale.value || '').toLowerCase())
  );

  const mapEl = ref(null);
  const map = ref(null);

  const pointsQ = ref('');
  let protocol;
  const pmtilesUrl = '/pmtiles-range/afghanistan.pmtiles';

  let highlightMarker = null;

  const detailsOpen = ref(false);
  const detailsLoading = ref(false);
  const selectedItem = ref(null);
  const detailsData = ref(null);

  const loadingAll = ref(false);
  const pointsMenuOpen = ref(false);
  const allMapItems = ref([]);
  const typeOptions = ref([]);
  const selectedTypes = ref([]);
  const pendingSelectedTypes = ref([]);

  const MAP_POINTS_SOURCE_ID = 'module-points';
  const MAP_POINTS_CLUSTER_LAYER_ID = 'module-points-clusters';
  const MAP_POINTS_CLUSTER_COUNT_LAYER_ID = 'module-points-cluster-count';
  const MAP_POINTS_UNCLUSTERED_LAYER_ID = 'module-points-unclustered';
  const MAP_POINTS_LABEL_LAYER_ID = 'module-points-label';

  // ========================================================================
  // FAST ICON SYSTEM FOR MAP POINTS
  // ========================================================================

  const MAP_RESOURCE_ICON = 'mdi-circle';

  const POINT_TYPE_ICONS = {
    hotels: { label: 'مسافر خانی او میلمستون', path: mdiSilverwareForkKnife },
    hostels: { label: 'لیلی او کرایی اطاقونه', path: mdiBed },

    tujarans: { label: 'علماء او متنفذین', path: mdiAccountMultiplePlus },
    mujahedinos: { label: 'مجاهدین', path: mdiAccountCheck },
    saeen_fasad: { label: 'ساعین بالفساد', path: mdiAccountRemove },
    fasad: { label: 'فساد', path: mdiAccountMinus },
    highways: { label: 'څلور لارې او درې لارې', path: mdiHighway },
    sarayanos: { label: 'سرایونه', path: mdiWarehouse },
    sarafyano: { label: 'صرافیانې', path: mdiBankTransfer },
    badmashanos: { label: 'زورواکان او ولکه داران', path: mdiAccountMultipleRemove },
    car_sales_companies: { label: 'موټر پلورونځی', path: mdiCarBack },
    transport_companies: { label: 'ترانسپورتي شرکتونه', path: mdiTrainCar },
    mashkok_homes: { label: 'مشکوک کورونه', path: mdiHomeGroupRemove },
    old_soldiers_homes: { label: 'د پخواني چارواکو کورونه', path: mdiHomePlusOutline },
    old_jahadis: { label: 'پخوانی جهادی قومندانان', path: mdiAccountStar },
    mashkok_people: { label: 'مشکوک افراد', path: mdiAccountBoxMinusOutline },

    madrasas: { label: 'دینی مدرسی', path: mdiTownHall },
    schools: { label: 'ښوونځی او روزنیز مرکزونه', path: mdiBookOpenBlankVariantOutline },

    hado_bus_stops: { label: 'د هډو په اړه', path: mdiCarMultiple },
    parkings: { label: 'د پارکینونو په اړه', path: mdiCarBrakeParking },

    banks: { label: 'بانکونه او نماینده ګیانی', path: mdiBank },
    apartments: { label: 'رهایشی بلاکونه', path: mdiCity },

    rahnoma_mamlats: { label: 'رهنماء معاملات', path: mdiStoreSearchOutline },
    hosppitals: { label: 'روغتونونه', path: mdiHospitalBox },
    work_shops: { label: 'مستری خانی', path: mdiCarWrench },

    wakilanos: { label: 'وکیلان او ملکان', path: mdiAccountEdit },
    mulkano: { label: 'د کوڅو نماینده کان', path: mdiAccountDetails },

    universities: { label: 'پوهنتونونو ، دلوړو زده کړو موسسو او انسټیټیوټونو', path: mdiSchool },
    heist: { label: 'سرقت کوونکي', path: mdiAccountQuestion },
    masjids: { label: 'مساجد، خانقاوی', path: mdiMosque },
    companies: { label: 'فابریکې او شرکتونه', path: mdiFactory },
    clubs: { label: 'ورزشي کلبونه', path: mdiGymnastics },
    done_lost_places: { label: 'هغه ساحې چې اختطاف پکې ترسره شوی', path: mdiSelectPlace },
    damage_places: { label: 'آسیب پذیره ساحې', path: mdiFireplace },
    damage_rasan_places: { label: 'آسیب رسانه ساحې', path: mdiFireplaceOff },

    mahajerat: { label: 'نوې راغلي کورونه', path: mdiHomeGroup },
    soldier: { label: 'پخواني چارواکي', path: mdiPoliceBadge },
    complexes: { label: 'داخلي موسسې او بنسټونه', path: mdiOfficeBuildingPlus },

    // backend type is captive, old/frontend type may be captives
    captive: { label: 'راخوشي شوي بندیان', path: mdiAccountLock },
    captives: { label: 'راخوشي شوي بندیان', path: mdiAccountLock },

    econemy_projects: { label: 'مهم تاسیسات', path: mdiGreenhouse },
    criminal_capture_places: { label: 'هغه ساحی چې مجرمین پکې نیول شوی', path: mdiAccountAlert },
    pumps: { label: 'پمپونه', path: mdiGasStation },
    robbers: { label: 'اختطاف ګران', path: mdiRobber },
    shoras: { label: 'محلی شورا ګانی', path: mdiAccountSwitch },
    parks: { label: 'پارکونه', path: mdiTreeOutline },
    non_responsable_people: { label: 'غیر مسوله وسله وال', path: mdiPistol },
    governmentplaces: { label: 'دولتی ځایونه', path: mdiHomeAnalytics },
    supermarkets: { label: 'تجارتي مراکز او سوپر مارکیټونه', path: mdiCart },
    emarat_offices: { label: 'داخلی مؤسسه او بنسټونه', path: mdiBriefcase },
    deplomaticplaces: {
      label: 'دیپلوماتیک ځایونه،خارجي موسسې،دبهرنیانو د اوسیدو او کار ځایونه',
      path: mdiBriefcaseVariant,
    },
    checkpoints: { label: 'پوستی او چیک پاینټونه', path: mdiShieldLock },
    information: { label: 'اطلاعات', path: mdiInformationOutline },
    office_employee: { label: 'کارمند', path: mdiAccountPlus },

    resource: { label: 'منابع', path: mdiCircle },
    captive_delivery: { label: 'راخوشي شوي بندیان', path: mdiClosedCaption },

    // ✅ missing backend types added
    event: { label: 'پیښه', path: mdiInformationOutline },
    entry_exit: { label: 'د ښار دخولي/خروجي دروازه', path: mdiHighway },
    mawad: { label: 'د نشه یی توکو پلورونکی', path: mdiAccountAlert },
    lost_mujremeens: { label: 'نادرکه شوي مجرم', path: mdiAccountQuestion },
    mashkok_shops: { label: 'مشکوک دوکان', path: mdiStoreSearchOutline },
    luchakanos: { label: 'لوچک او بی بند و باره فرد', path: mdiAccountRemove },
    jawasis: { label: 'جاسوس', path: mdiAccountLock },
  };

  const ICON_ALIASES = {
    entry_exit: 'entry_exits',
    sarafyano: 'sarafyano',
    sarayanos: 'sarayanos',
    mahajerat: 'mahajerat',
    soldier: 'soldier',
    mawad: 'mawads',
    mulkano: 'mulkano',
  };

  function iconKeyOfType(type) {
    return ICON_ALIASES[type] || type;
  }

  function safeLabelFor(item) {
    const k = iconKeyOfType(item?.type);
    return POINT_TYPE_ICONS?.[k]?.label || ICON_MAP?.[k]?.label || item?.typeLabel || k || '';
  }

  function safeMdiFor(item) {
    const t = item?.type;
    if (t === 'resource') return MAP_RESOURCE_ICON;

    const k = iconKeyOfType(t);
    return ICON_MAP?.[k]?.mdi || 'mdi-map-marker';
  }

  function getPointIconPath(type) {
    const k = iconKeyOfType(type);
    return POINT_TYPE_ICONS?.[k]?.path || mdiCircle;
  }

  function pointImageName(type, isActive = null) {
    if (type === 'resource') {
      return isActive === 1 || isActive === true
        ? 'point-icon-resource-active'
        : 'point-icon-resource-inactive';
    }
    return `point-icon-${iconKeyOfType(type)}`;
  }

  function svgToImage(svgString) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.onload = () => resolve(img);
      img.onerror = reject;
      img.src = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svgString)}`;
    });
  }

  function buildIconSvg({
    pathD,
    size = 56,
    iconSize = 44,
    bg = '#ffffff',
    fg = '#1f2937',
    ring = '#2563eb',
    ringWidth = 2,
  }) {
    const center = size / 2;
    const r = center - 1.5;
    const viewBoxSize = 24;
    const iconX = (size - iconSize) / 2;
    const iconY = (size - iconSize) / 2;

    return `
    <svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
      <circle cx="${center}" cy="${center}" r="${r}" fill="${bg}" stroke="${ring}" stroke-width="${ringWidth}" />
      <g transform="translate(${iconX}, ${iconY}) scale(${iconSize / viewBoxSize})">
        <path d="${pathD}" fill="${fg}" />
      </g>
    </svg>
  `.trim();
  }

  async function addMapImage(name, svgString) {
    if (!map.value) return;
    if (map.value.hasImage(name)) return;

    const img = await svgToImage(svgString);
    map.value.addImage(name, img, { pixelRatio: 2 });
  }

  async function registerPointImages() {
    if (!map.value) return;

    const usedTypes = new Set(
      (Array.isArray(allMapItems.value) ? allMapItems.value : [])
        .map((it) => it?.type)
        .filter(Boolean)
    );

    usedTypes.add('resource');

    const tasks = [];

    for (const rawType of usedTypes) {
      const type = iconKeyOfType(rawType);

      if (type === 'resource') {
        tasks.push(
          addMapImage(
            'point-icon-resource-active',
            buildIconSvg({
              pathD: mdiCircle,
              bg: '#ffffff',
              fg: '#16a34a',
              ring: '#16a34a',
            })
          )
        );

        tasks.push(
          addMapImage(
            'point-icon-resource-inactive',
            buildIconSvg({
              pathD: mdiCircle,
              bg: '#ffffff',
              fg: '#dc2626',
              ring: '#dc2626',
            })
          )
        );

        continue;
      }

      tasks.push(
        addMapImage(
          `point-icon-${type}`,
          buildIconSvg({
            pathD: getPointIconPath(type),
            bg: '#ffffff',
            fg: '#1f2937',
            ring: '#2563eb',
          })
        )
      );
    }

    await Promise.all(tasks);
  }

  function getToken() {
    const t = sessionStorage.getItem('token');
    return t ? String(t) : '';
  }

  function authHeaders() {
    const token = getToken();
    return token
      ? { Accept: 'application/json', Authorization: `Bearer ${token}` }
      : { Accept: 'application/json' };
  }

  function parseCoordinates(str) {
    if (!str) return null;

    const m = String(str)
      .trim()
      .match(/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/);

    if (!m) return null;

    const lng = parseFloat(m[1]);
    const lat = parseFloat(m[2]);

    if (!isFinite(lng) || !isFinite(lat)) return null;

    return [lng, lat];
  }

  function debounce(fn, wait = 250) {
    let t = null;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), wait);
    };
  }

  function getMapBbox() {
    if (!map.value) return null;

    const b = map.value.getBounds();
    return [b.getWest(), b.getSouth(), b.getEast(), b.getNorth()].join(',');
  }
  function buildTypesQuery() {
    if (!selectedTypes.value.length) return '';

    return selectedTypes.value.map((type) => `types[]=${encodeURIComponent(type)}`).join('&');
  }

  function applyTypeFilter() {
    selectedTypes.value = [...pendingSelectedTypes.value];
    loadAllModulePoints();
  }

  function clearTypeFilter() {
    pendingSelectedTypes.value = [];
    selectedTypes.value = [];
    loadAllModulePoints();
  }
  async function loadAllModulePoints() {
    if (!map.value) return;

    loadingAll.value = true;

    try {
      const bbox = getMapBbox();
      const params = [`bbox=${encodeURIComponent(bbox)}`];

      const typesQuery = buildTypesQuery();
      if (typesQuery) {
        params.push(typesQuery);
      }

      const url = `/api/map/points?${params.join('&')}`;

      const res = await fetch(url, { headers: authHeaders() }).then(async (r) => {
        if (!r.ok) {
          throw new Error(`Map points request failed: ${r.status}`);
        }
        return r.json();
      });

      allMapItems.value = Array.isArray(res?.data) ? res.data : [];
      typeOptions.value = Array.isArray(res?.meta?.available_types) ? res.meta.available_types : [];

      await registerPointImages();
      refreshGeoJsonSource();
      console.log('Loaded map points:', allMapItems.value.length);
    } catch (e) {
      console.error('loadAllModulePoints failed', e);
      allMapItems.value = [];
      refreshGeoJsonSource();
    } finally {
      loadingAll.value = false;
    }
  }

  async function openDetailsMenu(item) {
    selectedItem.value = item;
    detailsOpen.value = true;
    detailsLoading.value = true;
    detailsData.value = null;

    try {
      const res = await fetch(item._showUrl, { headers: authHeaders() }).then((r) => r.json());
      detailsData.value = res?.data ?? res;
    } catch (e) {
      console.error('details load failed', e);
      detailsData.value = null;
    } finally {
      detailsLoading.value = false;
    }
  }

  function flyToItem(item) {
    const coords = parseCoordinates(item?.coordinates);
    if (!coords || !map.value) return;

    const [lng, lat] = coords;

    map.value.flyTo({
      center: [lng, lat],
      zoom: 16,
      essential: true,
      speed: 1.3,
      curve: 1.3,
    });
  }

  const LOCAL_NAME_EXPR = [
    'coalesce',
    ['get', 'name:fa'],
    ['get', 'name:prs'],
    ['get', 'name:ps'],
    ['get', 'name:ar'],
    ['get', 'name'],
    ['get', 'name:en'],
  ];

  const FONT_STACK_Z_LT6 = ['NotoNaskhArabic-Regular', 'NotoSans-Regular'];
  const FONT_STACK_Z_GE6 = ['NotoNaskhArabic-Bold', 'NotoSans-Bold'];

  function applyBilingualLabels(style) {
    if (!style?.layers) return style;

    for (const l of style.layers) {
      if (l.type === 'symbol') {
        l.layout ||= {};
        l.paint ||= {};

        l.layout['text-field'] = LOCAL_NAME_EXPR;
        l.layout['text-font'] = [
          'step',
          ['zoom'],
          ['literal', FONT_STACK_Z_LT6],
          6,
          ['literal', FONT_STACK_Z_GE6],
        ];

        l.layout['text-anchor'] = l.layout['text-anchor'] || 'center';
        l.layout['text-justify'] = 'auto';

        l.paint['text-halo-color'] = '#fff';
        l.paint['text-halo-width'] = 1.1;
      }
    }

    return style;
  }

  function asText(v) {
    if (v === null || v === undefined) return '';
    if (typeof v === 'string') return v;
    try {
      return JSON.stringify(v);
    } catch {
      return String(v);
    }
  }

  const filteredMapItems = computed(() => {
    const q = String(pointsQ.value || '')
      .trim()
      .toLowerCase();

    const arr = Array.isArray(allMapItems.value) ? allMapItems.value : [];
    if (!q) return arr;

    return arr.filter((it) => {
      return (
        asText(it.title).toLowerCase().includes(q) ||
        asText(it.subtitle).toLowerCase().includes(q) ||
        asText(it.typeLabel).toLowerCase().includes(q) ||
        asText(it.id).toLowerCase().includes(q)
      );
    });
  });

  const groupedPoints = computed(() => {
    const arr = filteredMapItems.value;
    const mapG = new Map();

    for (const it of arr) {
      const key = it.type || 'unknown';

      if (!mapG.has(key)) {
        mapG.set(key, {
          type: key,
          label: safeLabelFor(it) || it.typeLabel || key,
          items: [],
          count: 0,
        });
      }

      const g = mapG.get(key);
      g.items.push(it);
      g.count++;
    }

    return Array.from(mapG.values()).sort((a, b) => {
      if (b.count !== a.count) return b.count - a.count;
      return String(a.label).localeCompare(String(b.label), 'fa');
    });
  });

  function createHighlightElement(feature) {
    const props = feature?.properties || {};
    const photoUrl = props.photoUrl || '';
    const el = document.createElement('div');

    if (photoUrl) {
      el.style.width = '24px';
      el.style.height = '24px';
      el.style.borderRadius = '50%';
      el.style.overflow = 'hidden';
      el.style.background = '#ffffff';
      el.style.boxShadow = '0 0 0 2px #ffffff, 0 1px 4px rgba(0,0,0,0.35)';
      el.style.cursor = 'pointer';
      el.style.display = 'flex';
      el.style.alignItems = 'center';
      el.style.justifyContent = 'center';
      el.style.transform = 'translateY(-4px)';

      const img = document.createElement('img');
      img.src = photoUrl;
      img.alt = props.title || 'person';
      img.style.width = '100%';
      img.style.height = '100%';
      img.style.objectFit = 'cover';
      img.style.pointerEvents = 'none';
      el.appendChild(img);
      return el;
    }

    el.style.width = '20px';
    el.style.height = '20px';
    el.style.borderRadius = '50%';
    el.style.background = '#ffffff';
    el.style.boxShadow = '0 0 0 1px #2563eb, 0 1px 4px rgba(0,0,0,0.35)';
    el.style.display = 'flex';
    el.style.alignItems = 'center';
    el.style.justifyContent = 'center';
    el.style.cursor = 'pointer';
    el.style.transform = 'translateY(-4px)';

    const dot = document.createElement('div');
    dot.style.width = '10px';
    dot.style.height = '10px';
    dot.style.borderRadius = '999px';
    dot.style.background = '#2563eb';
    el.appendChild(dot);

    return el;
  }

  function flyTo(payload) {
    if (!map.value) return;

    const { lng, lat, zoom, feature } = payload;

    map.value.flyTo({
      center: [lng, lat],
      zoom: zoom || 14,
      essential: true,
      speed: 1,
      curve: 1.3,
    });

    if (highlightMarker) {
      highlightMarker.remove();
      highlightMarker = null;
    }

    const el = createHighlightElement(feature || {});
    highlightMarker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
      .setLngLat([lng, lat])
      .addTo(map.value);
  }

  function applyFlyTarget(val) {
    if (!val || !val.coords || !map.value) return;

    const coords = typeof val.coords === 'string' ? parseCoordinates(val.coords) : val.coords;
    if (!coords) return;

    const [lng, lat] = coords;
    const meta = val.meta || {};

    flyTo({
      lng,
      lat,
      zoom: meta.zoom || 16,
      feature: {
        type: meta.type || 'generic',
        properties: {
          title: meta.title,
          subtitle: meta.subtitle,
          photoUrl: meta.photoUrl || '',
        },
      },
    });
  }

  function itemsToGeoJson(items = []) {
    return {
      type: 'FeatureCollection',
      features: items
        .map((item) => {
          const coords = parseCoordinates(item?.coordinates);
          if (!coords) return null;

          return {
            type: 'Feature',
            geometry: {
              type: 'Point',
              coordinates: coords,
            },
            properties: {
              id: item.id,
              type: item.type,
              typeLabel: item.typeLabel || '',
              subTypeLabel: item.subTypeLabel || '',
              title: item.title || '',
              subtitle: item.subtitle || '',
              picture: item.picture || '',
              coordinates: item.coordinates || '',
              is_active: item.is_active ? 1 : 0,
              _showUrl: item._showUrl || '',
              _listPath: item._listPath || '',
              _relationQueryKey: item._relationQueryKey || '',
              iconName: pointImageName(item.type, item.is_active ? 1 : 0),
            },
          };
        })
        .filter(Boolean),
    };
  }

  function ensureMapPointLayers() {
    if (!map.value) return;

    if (!map.value.getSource(MAP_POINTS_SOURCE_ID)) {
      map.value.addSource(MAP_POINTS_SOURCE_ID, {
        type: 'geojson',
        data: { type: 'FeatureCollection', features: [] },
      });
    }

    // if (!map.value.getLayer(MAP_POINTS_CLUSTER_LAYER_ID)) {
    //   map.value.addLayer({
    //     id: MAP_POINTS_CLUSTER_LAYER_ID,
    //     type: 'circle',
    //     source: MAP_POINTS_SOURCE_ID,
    //     filter: ['has', 'point_count'],
    //     paint: {
    //       'circle-color': '#2563eb',
    //       'circle-radius': ['step', ['get', 'point_count'], 18, 20, 22, 100, 28, 500, 34],
    //       'circle-stroke-color': '#ffffff',
    //       'circle-stroke-width': 2,
    //     },
    //   });
    // }

    // if (!map.value.getLayer(MAP_POINTS_CLUSTER_COUNT_LAYER_ID)) {
    //   map.value.addLayer({
    //     id: MAP_POINTS_CLUSTER_COUNT_LAYER_ID,
    //     type: 'symbol',
    //     source: MAP_POINTS_SOURCE_ID,
    //     filter: ['has', 'point_count'],
    //     layout: {
    //       'text-field': ['get', 'point_count_abbreviated'],
    //       'text-size': 12,
    //     },
    //     paint: {
    //       'text-color': '#ffffff',
    //     },
    //   });
    // }

    // IMPORTANT:
    // unclustered points are now SYMBOL icons instead of CIRCLE dots
    if (!map.value.getLayer(MAP_POINTS_UNCLUSTERED_LAYER_ID)) {
      map.value.addLayer({
        id: MAP_POINTS_UNCLUSTERED_LAYER_ID,
        type: 'symbol',
        source: MAP_POINTS_SOURCE_ID,
        filter: ['==', ['geometry-type'], 'Point'],
        layout: {
          'icon-image': ['coalesce', ['get', 'iconName'], 'point-icon-resource-active'],
          'icon-size': 1,
          'icon-allow-overlap': true,
          'icon-ignore-placement': true,
          'icon-anchor': 'center',
        },
      });
    }

    if (!map.value.getLayer(MAP_POINTS_LABEL_LAYER_ID)) {
      map.value.addLayer({
        id: MAP_POINTS_LABEL_LAYER_ID,
        type: 'symbol',
        source: MAP_POINTS_SOURCE_ID,
        filter: ['==', ['geometry-type'], 'Point'],
        minzoom: 11,
        layout: {
          'text-field': [
            'case',
            ['!=', ['coalesce', ['get', 'subTypeLabel'], ''], ''],
            [
              'format',
              ['coalesce', ['get', 'typeLabel'], ''],
              { 'font-scale': 1 },
              '\n',
              {},
              ['coalesce', ['get', 'subTypeLabel'], ''],
              { 'font-scale': 0.85 },
            ],
            ['coalesce', ['get', 'typeLabel'], ''],
          ],
          'text-size': 12,
          'text-offset': [0, 1.6],
          'text-line-height': 1.15,
          'text-anchor': 'top',
          'text-allow-overlap': false,
          'text-ignore-placement': false,
        },
        paint: {
          'text-color': '#111827',
          'text-halo-color': '#ffffff',
          'text-halo-width': 1.2,
        },
      });
    }
  }

  function refreshGeoJsonSource() {
    if (!map.value) return;

    const src = map.value.getSource(MAP_POINTS_SOURCE_ID);
    if (!src) return;

    src.setData(itemsToGeoJson(allMapItems.value));
  }

  function bindMapPointEvents() {
    if (!map.value) return;

    // map.value.on('click', MAP_POINTS_CLUSTER_LAYER_ID, (e) => {
    //   const features = map.value.queryRenderedFeatures(e.point, {
    //     layers: [MAP_POINTS_CLUSTER_LAYER_ID],
    //   });

    //   const feature = features?.[0];
    //   if (!feature) return;

    //   const clusterId = feature.properties?.cluster_id;
    //   const source = map.value.getSource(MAP_POINTS_SOURCE_ID);

    //   source.getClusterExpansionZoom(clusterId, (err, zoom) => {
    //     if (err) return;

    //     map.value.easeTo({
    //       center: feature.geometry.coordinates,
    //       zoom,
    //       duration: 300,
    //     });
    //   });
    // });

    map.value.on('click', MAP_POINTS_UNCLUSTERED_LAYER_ID, (e) => {
      const feature = e.features?.[0];
      if (!feature) return;

      const p = feature.properties || {};

      const item = {
        id: Number(p.id),
        type: p.type,
        typeLabel: p.typeLabel,
        title: p.title,
        subtitle: p.subtitle,
        picture: p.picture,
        coordinates: p.coordinates,
        is_active: Number(p.is_active) === 1,
        _showUrl: p._showUrl,
        _listPath: p._listPath,
        _relationQueryKey: p._relationQueryKey,
      };

      openDetailsMenu(item);
    });
    function goToRelatedPage(item) {
      const path = item?._listPath;
      const relationKey = item?._relationQueryKey;
      const id = item?.id;

      if (!path || !relationKey || !id) return;

      router.push({
        path,
        query: {
          [relationKey]: id,
          source: 'related',
          open: 'peek',
        },
      });
    }

    // map.value.on('mouseenter', MAP_POINTS_CLUSTER_LAYER_ID, () => {
    //   map.value.getCanvas().style.cursor = 'pointer';
    // });

    // map.value.on('mouseleave', MAP_POINTS_CLUSTER_LAYER_ID, () => {
    //   map.value.getCanvas().style.cursor = '';
    // });

    map.value.on('mouseenter', MAP_POINTS_UNCLUSTERED_LAYER_ID, () => {
      map.value.getCanvas().style.cursor = 'pointer';
    });

    map.value.on('mouseleave', MAP_POINTS_UNCLUSTERED_LAYER_ID, () => {
      map.value.getCanvas().style.cursor = '';
    });
  }

  const debouncedReloadPoints = debounce(async () => {
    await loadAllModulePoints();
  }, 250);

  onMounted(async () => {
    const ORIGIN = window.location.origin;
    const STYLE_JSON = `${ORIGIN}/styles/bright-complete.json`;
    const SPRITE_URL = `${ORIGIN}/sprites/sprite`;
    const GLYPHS_URL = `${ORIGIN}/fonts/{fontstack}/{range}.pbf`;
    const RTL_PLUGIN_URL = `${ORIGIN}/vendor/maplibre/mapbox-gl-rtl-text.js`;

    try {
      protocol = new Protocol();

      try {
        maplibregl.addProtocol('pmtiles', protocol.tile);
      } catch (e) {
        console.warn('addProtocol warning:', e);
      }

      const archiveUrl = `${window.location.origin}/pmtiles-range/afghanistan.pmtiles`;
      const pm = new PMTiles(archiveUrl);
      protocol.add(pm);

      try {
        maplibregl.setRTLTextPlugin(RTL_PLUGIN_URL, false);
        console.log('RTL plugin requested:', RTL_PLUGIN_URL);
      } catch (e) {
        console.warn('RTL plugin warning:', e);
      }

      let styleJson = await fetch(STYLE_JSON).then((r) => r.json());

      const layerSourceNames = [
        ...new Set((styleJson.layers || []).filter((l) => l.source).map((l) => l.source)),
      ];

      if (!layerSourceNames.length) {
        throw new Error('No layer sources found in bright-complete.json');
      }

      const vectorSourceKey = layerSourceNames[0];

      styleJson.sources[vectorSourceKey] = {
        type: 'vector',
        url: `pmtiles://${pmtilesUrl}`,
      };

      styleJson.sprite = SPRITE_URL;
      styleJson.glyphs = GLYPHS_URL;
      styleJson = applyBilingualLabels(styleJson);

      console.log('STYLE_JSON', STYLE_JSON);
      console.log('SPRITE_URL', SPRITE_URL);
      console.log('GLYPHS_URL', GLYPHS_URL);
      console.log('styleJson before map init', styleJson);
      map.value = new maplibregl.Map({
        container: mapEl.value,
        style: styleJson,
        minZoom: 3,
        maxZoom: 18,
      });

      window.mapDebug = map.value;

      map.value.addControl(new maplibregl.NavigationControl(), 'top-right');
      map.value.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

      map.value.on('load', async () => {
        console.log('Map load event fired');

        const afghanistanBounds = [
          [60.5, 29.0], // southwest [lng, lat]
          [75.5, 38.8], // northeast [lng, lat]
        ];

        map.value.fitBounds(afghanistanBounds, {
          padding: 20,
          duration: 0,
        });

        ensureMapPointLayers();
        bindMapPointEvents();

        await loadAllModulePoints();

        if (mapBus.flyTarget?.coords) {
          applyFlyTarget(mapBus.flyTarget);
          mapBus.flyTarget = null;
        }

        map.value.on('moveend', debouncedReloadPoints);
      });

      map.value.on('error', (e) => {
        console.error('MAPLIBRE ERROR object:', e);
        console.error('MAPLIBRE ERROR details:', e?.error);
      });
    } catch (err) {
      console.error('MAP INIT FAILED:', err);
    }
  });

  watch(
    () => mapBus.reloadTs,
    async (ts) => {
      if (!ts || !map.value) return;
      await loadAllModulePoints();
    }
  );

  watch(
    () => mapBus.flyTarget,
    (val) => {
      if (!val || !val.coords || !map.value) return;

      const coords = typeof val.coords === 'string' ? parseCoordinates(val.coords) : val.coords;
      if (!coords) return;

      const [lng, lat] = coords;
      const meta = val.meta || {};

      map.value.flyTo({
        center: [lng, lat],
        zoom: meta.zoom || 16,
        essential: true,
        speed: 1.3,
        curve: 1.3,
      });

      const skipHighlight = meta.type === 'office_employee' || meta.disableHighlight === true;
      if (skipHighlight) {
        if (highlightMarker) {
          highlightMarker.remove();
          highlightMarker = null;
        }
        mapBus.flyTarget = null;
        return;
      }

      flyTo({
        lng,
        lat,
        zoom: meta.zoom || 16,
        feature: {
          type: meta.type || 'generic',
          properties: {
            title: meta.title,
            subtitle: meta.subtitle,
            photoUrl: meta.photoUrl || '',
          },
        },
      });

      mapBus.flyTarget = null;
    },
    { deep: true }
  );

  onBeforeUnmount(() => {
    if (highlightMarker) {
      highlightMarker.remove();
      highlightMarker = null;
    }

    const m = map.value;
    if (m) {
      m.off('moveend', debouncedReloadPoints);
      m.remove();
    }
  });
</script>

<style scoped>
  .map-wrap {
    position: relative;
  }

  .map {
    width: 100%;
    height: 100vh;
    background: #eef3f7;
  }

  .map-top-tools {
    position: absolute;
    top: calc(var(--v-layout-top, 12px) - 52px);
    left: -42px !important;
    z-index: 1;
    width: min(980px, calc(100vw - 24px));
    margin-right: 34px;
    max-width: calc(100vw - 12px);
    display: flex;
    align-items: flex-start;
    gap: 10px;
    flex-wrap: wrap;
  }

  :dir(rtl) .map-top-tools {
    left: auto;
    right: 12px;
  }

  .points-btn {
    height: 40px;
    min-width: 140px;
    font-weight: 800;
  }

  .points-menu-card {
    width: min(560px, calc(100vw - 24px));
    max-height: 72vh;
    overflow: hidden;
  }

  .points-menu-head {
    padding: 12px 14px;
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
  }

  .points-title {
    font-weight: 900;
    font-size: 14px;
  }

  .points-sub {
    font-size: 12px;
    opacity: 0.75;
    font-weight: 700;
  }

  .points-menu-body {
    padding: 12px 12px 2px 12px;
    overflow: auto;
    max-height: 58vh;
  }

  .type-block {
    border: 1px solid rgba(17, 24, 39, 0.08);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 10px;
    background: #fff;
  }

  .type-head {
    padding: 10px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(37, 99, 235, 0.04);
  }
  .type-filter-card {
    padding: 8px 10px;
    min-width: 360px;
    max-width: 460px;
  }

  .type-filter-row {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .type-filter-select {
    min-width: 220px;
    flex: 1;
  }

  .filter-btn,
  .clear-filter-btn {
    height: 40px;
    white-space: nowrap;
  }
  .type-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 900;
    font-size: 13px;
  }

  .type-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
  }

  .type-count {
    font-size: 12px;
    font-weight: 900;
    opacity: 0.85;
  }

  .type-list {
    padding: 0;
  }

  .mini-title {
    font-weight: 800;
    font-size: 12.5px;
  }

  .mini-sub {
    font-size: 11px;
    opacity: 0.8;
  }

  .empty-points {
    padding: 14px;
    font-weight: 800;
    opacity: 0.7;
    text-align: center;
  }

  .points-menu-actions {
    padding: 10px 12px;
    display: flex;
    justify-content: flex-end;
  }
</style>
