<template>
  <AppBar title="نقشه">

  </AppBar>

  <div class="map-wrap" :dir="isRtl ? 'rtl' : 'ltr'">
    <div ref="mapEl" class="map"></div>

    <!-- Search Component -->
    <PmtilesSearchMap class="search-panel" @flyTo="flyTo" />

    <!-- Dialog to save person/place at clicked coordinates -->
    <AddMapPointDialog
      v-model="dialogOpen"
      :coordinates="selectedCoordinates"
      @saved="handlePointSaved"
    />

    <!-- ✅ Separate details menu drawer -->
    <MapDetailsDrawer
      v-model="detailsOpen"
      :rtl="isRtl"
      :loading="detailsLoading"
      :item="selectedItem"
      :data="detailsData"
      @flyToItem="flyToItem"
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
import AddMapPointDialog from './AddMapPointDialog.vue';
import MapDetailsDrawer from './MapDetailsDrawer.vue';

import { useMapPointsRepository } from '../../stores/MapPointsRepository';
import { useMapBusStore } from '../../stores/MapBusStore';
import AppBar from '@/components/AppBar.vue';

const mapPointsStore = useMapPointsRepository();
const mapBus = useMapBusStore();

const { locale } = useI18n();
const isRtl = computed(() => ['fa', 'prs', 'ps', 'ar'].includes(String(locale.value || '').toLowerCase()));

/* ---------- PNG icons ---------- */
import provinceIcon from './province.png';
import districtIcon from './districts.png';
import zoneIcon from './zone.png';
import roadIcon from './road.png';
import streetIcon from './street.png';
import schoolIcon from './school.png';
import locationIcon from './location.png'; // default fallback

/* ---------- props (optional) ---------- */
const props = defineProps({
  dbPoints: { type: Array, default: () => [] }, // optional external points
});
const emit = defineEmits(['selectCoordinates', 'markerClick']);

const mapEl = ref(null);
let map, protocol;
const pmtilesUrl = '/pmtiles-range/afghanistan.pmtiles';

// highlighted marker from search
let highlightMarker = null;

// click marker (when user clicks somewhere to choose coordinates)
let clickMarker = null;

// static icon markers from basemap (provinces / districts / zones)
let provinceMarkers = [];
let districtMarkers = [];
let zoneMarkers = [];

// saved map-points markers (from map-points table)
let savedPointMarkers = [];

// unified markers (all module tables)
let unifiedMarkers = [];

/* ---------- dialog + local saved points ---------- */
const dialogOpen = ref(false);
const selectedCoordinates = ref('');
const dbPointsLocal = ref([]);

/* ---------- separate details drawer state ---------- */
const detailsOpen = ref(false);
const detailsLoading = ref(false);
const selectedItem = ref(null);
const detailsData = ref(null);

const loadingAll = ref(false);

/* ============================================================
   ✅ MODULE SOURCES (ALL ROUTES YOU SHARED)
   Each entry:
   - type: internal type key
   - label: shown in drawer header
   - list: index endpoint
   - show: show endpoint
   - title/subtitle builder: simple fallback (works with most)
============================================================ */
const MAP_SOURCES = [
  // Employee Management core
  { type: 'office_employee', label: 'کارمند', list: '/api/office-employees', show: (id) => `/api/office-employees/${id}` },
  { type: 'resource', label: 'منبع', list: '/api/resources', show: (id) => `/api/resources/${id}` },
  { type: 'information', label: 'اطلاعات', list: '/api/informations', show: (id) => `/api/informations/${id}` },
  { type: 'captive_delivery', label: 'تحویل اسیر', list: '/api/captive-deliveries', show: (id) => `/api/captive-deliveries/${id}` },
  { type: 'event', label: 'رویداد', list: '/api/events', show: (id) => `/api/events/${id}` },

  // 56 Formats (as per your routes)
  { type: 'fasad', label: 'فساد', list: '/api/fasads', show: (id) => `/api/fasads/${id}` },
  { type: 'saeen_fasad', label: 'ساین فساد', list: '/api/saeen-fasads', show: (id) => `/api/saeen-fasads/${id}` },
  { type: 'heist', label: 'سرقت', list: '/api/heists', show: (id) => `/api/heists/${id}` },
  { type: 'captive', label: 'اسیر', list: '/api/captives', show: (id) => `/api/captives/${id}` },
  { type: 'mulkano', label: 'ملکانو', list: '/api/mulkanos', show: (id) => `/api/mulkanos/${id}` },
  { type: 'entry_exit', label: 'ورود/خروج', list: '/api/entry-exits', show: (id) => `/api/entry-exits/${id}` },
  { type: 'sarafyano', label: 'صرافیانو', list: '/api/sarafyanos', show: (id) => `/api/sarafyanos/${id}` },
  { type: 'clubs', label: 'کلب', list: '/api/clubs', show: (id) => `/api/clubs/${id}` },
  { type: 'mahajerat', label: 'مهاجرت', list: '/api/mahajerats', show: (id) => `/api/mahajerats/${id}` },
  { type: 'soldier', label: 'سرباز', list: '/api/soldiers', show: (id) => `/api/soldiers/${id}` },

  // Fifthy format one
  { type: 'mawad', label: 'مواد', list: '/api/mawads', show: (id) => `/api/mawads/${id}` },
  { type: 'lost_mujremeen', label: 'گم مجرمین', list: '/api/lost-mujremeens', show: (id) => `/api/lost-mujremeens/${id}` },
  { type: 'old_soldiers_homes', label: 'خانه‌های سربازان قدیم', list: '/api/old-soldiers-homes', show: (id) => `/api/old-soldiers-homes/${id}` },
  { type: 'robbers', label: 'دزدان', list: '/api/robbers', show: (id) => `/api/robbers/${id}` },
  { type: 'non_responsable_people', label: 'اشخاص غیرمسؤول', list: '/api/non-responsable-people', show: (id) => `/api/non-responsable-people/${id}` },
  { type: 'mashkok_homes', label: 'خانه‌های مشکوک', list: '/api/mashkok-homes', show: (id) => `/api/mashkok-homes/${id}` },
  { type: 'damage_places', label: 'محلات خساره', list: '/api/damage-places', show: (id) => `/api/damage-places/${id}` },
  { type: 'damage_rasan_places', label: 'محلات خساره رسان', list: '/api/damage-rasan-places', show: (id) => `/api/damage-rasan-places/${id}` },
  { type: 'mashkok_shops', label: 'دکان‌های مشکوک', list: '/api/mashkok-shops', show: (id) => `/api/mashkok-shops/${id}` },
  { type: 'luchakanos', label: 'لوچکانو', list: '/api/luchakanos', show: (id) => `/api/luchakanos/${id}` },

  // Fifthy format two
  { type: 'schools', label: 'مکتب', list: '/api/schools', show: (id) => `/api/schools/${id}` },
  { type: 'hado_bus_stops', label: 'تم‌گاه بس', list: '/api/hado-bus-stops', show: (id) => `/api/hado-bus-stops/${id}` },
  { type: 'transport_companies', label: 'شرکت ترانسپورت', list: '/api/transport-companies', show: (id) => `/api/transport-companies/${id}` },
  { type: 'car_sales_companies', label: 'موتر فروشی', list: '/api/car-sales-companies', show: (id) => `/api/car-sales-companies/${id}` },
  { type: 'parkings', label: 'پارکینگ', list: '/api/parkings', show: (id) => `/api/parkings/${id}` },
  { type: 'banks', label: 'بانک', list: '/api/banks', show: (id) => `/api/banks/${id}` },
  { type: 'apartments', label: 'اپارتمان', list: '/api/apartments', show: (id) => `/api/apartments/${id}` },
  { type: 'rahnoma_mamlats', label: 'رهنما', list: '/api/rahnoma-mamlats', show: (id) => `/api/rahnoma-mamlats/${id}` },
  { type: 'hosppitals', label: 'شفاخانه', list: '/api/hosppitals', show: (id) => `/api/hosppitals/${id}` },
  { type: 'work_shops', label: 'ورکشاپ', list: '/api/work-shops', show: (id) => `/api/work-shops/${id}` },

  // Fifthy format three
  { type: 'badmashanos', label: 'بدماشانو', list: '/api/badmashanos', show: (id) => `/api/badmashanos/${id}` },
  { type: 'emarat_offices', label: 'ادارات امارت', list: '/api/emarat-offices', show: (id) => `/api/emarat-offices/${id}` },
  { type: 'hostels', label: 'لیلیه', list: '/api/hostels', show: (id) => `/api/hostels/${id}` },
  { type: 'hotels', label: 'هوتل', list: '/api/hotels', show: (id) => `/api/hotels/${id}` },
  { type: 'madrasas', label: 'مدرسه', list: '/api/madrasas', show: (id) => `/api/madrasas/${id}` },
  { type: 'mashkok_people', label: 'اشخاص مشکوک', list: '/api/mashkok-people', show: (id) => `/api/mashkok-people/${id}` },
  { type: 'mujahedinos', label: 'مجاهدین', list: '/api/mujahedinos', show: (id) => `/api/mujahedinos/${id}` },
  { type: 'old_jahadis', label: 'جهادی‌های قدیم', list: '/api/old-jahadis', show: (id) => `/api/old-jahadis/${id}` },
  { type: 'sarayanos', label: 'سرایانو', list: '/api/sarayanos', show: (id) => `/api/sarayanos/${id}` },
  { type: 'tujarans', label: 'تجاران', list: '/api/tujarans', show: (id) => `/api/tujarans/${id}` },

  // Fifthy format four
  { type: 'checkpoints', label: 'چک‌پاینت', list: '/api/checkpoints', show: (id) => `/api/checkpoints/${id}` },
  { type: 'masjids', label: 'مسجد', list: '/api/masjids', show: (id) => `/api/masjids/${id}` },
  { type: 'highways', label: 'شاهراه', list: '/api/highways', show: (id) => `/api/highways/${id}` },
  { type: 'companies', label: 'شرکت', list: '/api/companies', show: (id) => `/api/companies/${id}` },
  { type: 'done_lost_places', label: 'محل گمشده', list: '/api/done-lost-places', show: (id) => `/api/done-lost-places/${id}` },
  { type: 'econemy_projects', label: 'پروژه اقتصادی', list: '/api/econemy-projects', show: (id) => `/api/econemy-projects/${id}` },
  { type: 'criminal_capture_places', label: 'محل دستگیری', list: '/api/criminal-capture-places', show: (id) => `/api/criminal-capture-places/${id}` },
  { type: 'pumps', label: 'پمپ', list: '/api/pumps', show: (id) => `/api/pumps/${id}` },
  { type: 'shoras', label: 'شورا', list: '/api/shoras', show: (id) => `/api/shoras/${id}` },
  { type: 'governmentplaces', label: 'اداره دولتی', list: '/api/governmentplaces', show: (id) => `/api/governmentplaces/${id}` },
  { type: 'supermarkets', label: 'سوپرمارکیت', list: '/api/supermarkets', show: (id) => `/api/supermarkets/${id}` },
  { type: 'parks', label: 'پارک', list: '/api/parks', show: (id) => `/api/parks/${id}` },
  { type: 'complexes', label: 'کمپلکس', list: '/api/complexes', show: (id) => `/api/complexes/${id}` },
  { type: 'deplomaticplaces', label: 'محل دیپلوماتیک', list: '/api/deplomaticplaces', show: (id) => `/api/deplomaticplaces/${id}` },
  { type: 'universities', label: 'پوهنتون', list: '/api/universities', show: (id) => `/api/universities/${id}` },
  { type: 'wakilanos', label: 'وکیلانو', list: '/api/wakilanos', show: (id) => `/api/wakilanos/${id}` },
];

/* ---------- session token helper ---------- */
function getToken() {
  // you told me: session storage
  const t = sessionStorage.getItem('token');
  return t ? String(t) : '';
}
function authHeaders() {
  const token = getToken();
  return token
    ? { Accept: 'application/json', Authorization: `Bearer ${token}` }
    : { Accept: 'application/json' };
}

/* ---------- load saved map points ---------- */
async function loadSavedMapPoints() {
  try {
    dbPointsLocal.value = await mapPointsStore.FetchMapPoints();
    updateSavedPointMarkers();
  } catch (e) {
    console.error('Failed to load map points', e);
  }
}

/* ---------- Bilingual labels (unchanged) ---------- */
const LOCAL_NAME_ORDER = [
  'name:fa','name:prs','name:ps','name:ar','name:nonlatin','name','name:en','name_en','name:latin','name_int',
];
const LOCAL_NAME_EXPR = ['coalesce', ...LOCAL_NAME_ORDER.map((f) => ['get', f])];
const FONT_STACK_Z_LT6 = ['Noto Naskh Arabic Regular', 'Noto Sans Regular'];
const FONT_STACK_Z_GE6 = ['Noto Naskh Arabic Bold', 'Noto Sans Bold'];

function applyBilingualLabels(style) {
  if (!style?.layers) return style;
  for (const l of style.layers) {
    if (l.type === 'symbol') {
      l.layout ||= {};
      l.layout['text-field'] = LOCAL_NAME_EXPR;
      l.layout['text-font'] = [
        'step', ['zoom'],
        ['literal', FONT_STACK_Z_LT6],
        6,
        ['literal', FONT_STACK_Z_GE6],
      ];
      l.paint ||= {};
      l.paint['text-halo-color'] = '#fff';
      l.paint['text-halo-width'] = 1.1;
    }
  }
  return style;
}

/* ---------- icon chooser for search highlight ---------- */
function getIconForFeature(feature) {
  const t = String(
    feature?.type ||
      feature?.kind ||
      feature?.properties?.kind ||
      feature?.properties?.type ||
      feature?.properties?.class ||
      ''
  ).toLowerCase();

  if (['province', 'wilaya', 'velayat', 'state', 'region'].includes(t)) return provinceIcon;
  if (['district', 'woleswali', 'county', 'city', 'town'].includes(t)) return districtIcon;
  if (['zone','nahia','hawza','village','hamlet','suburb','neighbourhood','neighborhood'].includes(t)) return zoneIcon;
  if (t === 'road' || t === 'highway') return roadIcon;
  if (['street', 'residential', 'primary', 'secondary'].includes(t)) return streetIcon;
  if (['school', 'university', 'college'].includes(t)) return schoolIcon;

  return locationIcon;
}

/* ---------- HTML bubble for selected place (search result / flyTo) ---------- */
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

  const img = document.createElement('img');
  img.src = getIconForFeature(feature);
  img.alt = 'selected place';
  img.style.width = '14px';
  img.style.height = '14px';
  img.style.objectFit = 'contain';
  img.style.pointerEvents = 'none';

  el.appendChild(img);
  return el;
}

/* ---------- small round icon for static province/district/zone markers ---------- */
function createStaticIconElement(kind) {
  const el = document.createElement('div');
  el.style.width = '18px';
  el.style.height = '18px';
  el.style.borderRadius = '50%';
  el.style.background = '#ffffff';
  el.style.boxShadow = '0 0 0 1px rgba(0,0,0,0.25)';
  el.style.display = 'flex';
  el.style.alignItems = 'center';
  el.style.justifyContent = 'center';

  const img = document.createElement('img');
  img.src = kind === 'province' ? provinceIcon : kind === 'district' ? districtIcon : zoneIcon;
  img.style.width = '12px';
  img.style.height = '12px';
  img.style.objectFit = 'contain';
  img.style.pointerEvents = 'none';

  el.appendChild(img);
  return el;
}

/* ---------- marker element with optional photo ---------- */
function createMarkerElementFromItem(item) {
  const el = document.createElement('div');

  el.style.width = '20px';
  el.style.height = '19px';
  el.style.borderRadius = '50%';
  el.style.overflow = 'hidden';
  el.style.background = '#ffffff';
  el.style.boxShadow = '0 0 0 2px #ffffff, 0 1px 4px rgba(0,0,0,0.35)';
  el.style.cursor = 'pointer';
  el.style.display = 'flex';
  el.style.alignItems = 'center';
  el.style.justifyContent = 'center';

  const img = document.createElement('img');
  img.alt = item?.title || item?.typeLabel || 'marker';
  img.style.width = '100%';
  img.style.height = '100%';
  img.style.objectFit = 'cover';
  img.style.pointerEvents = 'none';

  const origin = window.location.origin;
  const raw = item?.picture || null;

  let pictureUrl = null;
  if (raw) {
    const pic = String(raw).trim();
    if (pic.startsWith('http://') || pic.startsWith('https://')) {
      pictureUrl = pic;
    } else {
      const clean = pic.replace(/^\/+/, '');
      pictureUrl = `${origin}/${clean}`;
    }
  }

  img.src = pictureUrl || locationIcon;

  el.appendChild(img);
  return el;
}

/* ---------- helpers ---------- */
function clearMarkerList(list) {
  list.forEach((m) => m.remove());
  list.length = 0;
}

function parseCoordinates(str) {
  if (!str) return null;
  const m = String(str).trim().match(/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/);
  if (!m) return null;
  const lng = parseFloat(m[1]);
  const lat = parseFloat(m[2]);
  if (!isFinite(lng) || !isFinite(lat)) return null;
  return [lng, lat];
}

/* ============================================================
   ✅ 1) SAVED MAP-POINTS markers (from map-points store)
============================================================ */
function updateSavedPointMarkers() {
  if (!map) return;

  clearMarkerList(savedPointMarkers);

  // include external dbPoints too, if any
  const allPoints = [...(props.dbPoints || []), ...(dbPointsLocal.value || [])];

  allPoints.forEach((p) => {
    const coords = parseCoordinates(p.coordinates || p.coordinate || p.coords);
    if (!coords) return;

    // use your photo marker builder
    const el = createMarkerElementFromItem({
      title: p.name || 'بدون نام',
      picture:
        p.picture ||
        p.picture_url ||
        p.image_url ||
        p.photo_url ||
        p.photo ||
        null,
    });

    const marker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
      .setLngLat(coords)
      .addTo(map);

    // click => open drawer with local point info (no api needed)
    el.addEventListener('click', (ev) => {
      ev.stopPropagation();
      openDetailsFromLocalPoint(p);
    });

    savedPointMarkers.push(marker);
  });
}

/* local saved point => drawer */
function openDetailsFromLocalPoint(p) {
  selectedItem.value = {
    type: 'map_point',
    typeLabel: 'نقطه ذخیره شده',
    id: p.id ?? null,
    coordinates: p.coordinates,
    title: p.name || 'بدون نام',
    subtitle: p.note || '',
    picture: p.picture || null,
    _showUrl: null,
    _raw: p,
  };

  detailsOpen.value = true;
  detailsLoading.value = false;

  // show “all details”
  detailsData.value = p;
}

/* ============================================================
   ✅ 2) UNIFIED MODULE markers (ALL TABLES)
============================================================ */
const allMapItems = ref([]); // unified points from all tables

function buildTitle(row, src) {
  return (
    row?.name ||
    row?.title ||
    row?.block_name ||
    row?.park_name ||
    row?.responsible_name ||
    row?.code_number ||
    row?.id ||
    src.label
  );
}

function buildSubtitle(row) {
  const parts = [];
  if (row?.code_number) parts.push(`کد: ${row.code_number}`);
  if (row?.phone_number) parts.push(`تماس: ${row.phone_number}`);
  if (row?.phone) parts.push(`تماس: ${row.phone}`);
  if (row?.responsible_phone) parts.push(`تماس: ${row.responsible_phone}`);
  if (row?.note) parts.push(String(row.note).slice(0, 40));
  return parts.join(' • ');
}

function pickPicture(row) {
  return (
    row?.picture ||
    row?.picture_url ||
    row?.image_url ||
    row?.photo_url ||
    row?.photo ||
    null
  );
}

async function loadAllModulePoints() {
  loadingAll.value = true;
  try {
    const requests = MAP_SOURCES.map(async (src) => {
      const url = `${src.list}?perPage=all`;

      const res = await fetch(url, { headers: authHeaders() }).then((r) => r.json());

      // handle laravel resource pagination or plain arrays
      const rows = Array.isArray(res?.data) ? res.data : Array.isArray(res) ? res : [];

      return rows
        .map((row) => ({
          type: src.type,
          typeLabel: src.label,
          id: row.id,
          coordinates: row.coordinates,
          title: buildTitle(row, src),
          subtitle: buildSubtitle(row),
          picture: pickPicture(row),
          _showUrl: src.show(row.id),
          _raw: row,
        }))
        .filter((x) => parseCoordinates(x.coordinates));
    });

    const results = await Promise.all(requests);
    allMapItems.value = results.flat();

    updateUnifiedMarkers();
  } catch (e) {
    console.error('loadAllModulePoints failed', e);
  } finally {
    loadingAll.value = false;
  }
}

function updateUnifiedMarkers() {
  if (!map) return;

  clearMarkerList(unifiedMarkers);

  allMapItems.value.forEach((item) => {
    const coords = parseCoordinates(item.coordinates);
    if (!coords) return;

    const el = createMarkerElementFromItem(item);

    const marker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
      .setLngLat(coords)
      .addTo(map);

    el.addEventListener('click', (ev) => {
      ev.stopPropagation();
      openDetailsMenu(item);
    });

    unifiedMarkers.push(marker);
  });

  console.log('Unified markers:', unifiedMarkers.length);
}

/* open drawer + fetch full record from its API show endpoint */
async function openDetailsMenu(item) {
  selectedItem.value = item;
  detailsOpen.value = true;
  detailsLoading.value = true;
  detailsData.value = null;

  try {
    const res = await fetch(item._showUrl, { headers: authHeaders() }).then((r) => r.json());
    detailsData.value = res?.data ?? res; // laravel resources return {data:{...}}
  } catch (e) {
    console.error('details load failed', e);
    detailsData.value = null;
  } finally {
    detailsLoading.value = false;
  }
}

/* Drawer asks to fly to item */
function flyToItem(item) {
  const coords = parseCoordinates(item?.coordinates);
  if (!coords || !map) return;

  const [lng, lat] = coords;

  map.flyTo({
    center: [lng, lat],
    zoom: 16,
    essential: true,
    speed: 1.3,
    curve: 1.3,
  });
}

/* ---------- admin icons (provinces / districts / zones) ---------- */
function updateAdminIcons() {
  if (!map) return;

  const zoom = map.getZoom();

  clearMarkerList(provinceMarkers);
  clearMarkerList(districtMarkers);
  clearMarkerList(zoneMarkers);

  if (zoom >= 4 && map.getLayer('place-state')) {
    const features = map.queryRenderedFeatures({ layers: ['place-state'] });
    const seen = new Set();
    features.forEach((f) => {
      const props = f.properties || {};
      const key = props['name:fa'] || props['name'] || props['name:latin'] || f.id;
      if (!key || seen.has(key)) return;
      seen.add(key);

      const geom = f.geometry;
      if (!geom || geom.type !== 'Point') return;
      const [lng, lat] = geom.coordinates;

      const el = createStaticIconElement('province');
      const marker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
        .setLngLat([lng, lat])
        .addTo(map);
      provinceMarkers.push(marker);
    });
  }

  if (zoom >= 6) {
    const districtLayers = ['place-city', 'place-town'].filter((id) => map.getLayer(id));
    if (districtLayers.length) {
      const features = map.queryRenderedFeatures({ layers: districtLayers });
      const seen = new Set();
      features.forEach((f) => {
        const props = f.properties || {};
        const key = props['name:fa'] || props['name'] || props['name:latin'] || f.id;
        if (!key || seen.has(key)) return;
        seen.add(key);

        const geom = f.geometry;
        if (!geom || geom.type !== 'Point') return;
        const [lng, lat] = geom.coordinates;

        const el = createStaticIconElement('district');
        const marker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
          .setLngLat([lng, lat])
          .addTo(map);
        districtMarkers.push(marker);
      });
    }
  }

  if (zoom >= 8) {
    const zoneLayers = ['place-village', 'place-other'].filter((id) => map.getLayer(id));
    if (zoneLayers.length) {
      const features = map.queryRenderedFeatures({ layers: zoneLayers });
      const seen = new Set();
      features.forEach((f) => {
        const props = f.properties || {};
        const key = props['name:fa'] || props['name'] || props['name:latin'] || f.id;
        if (!key || seen.has(key)) return;
        seen.add(key);

        const geom = f.geometry;
        if (!geom || geom.type !== 'Point') return;
        const [lng, lat] = geom.coordinates;

        const el = createStaticIconElement('zone');
        const marker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
          .setLngLat([lng, lat])
          .addTo(map);
        zoneMarkers.push(marker);
      });
    }
  }
}

/* ---------- Initialize map ---------- */
onMounted(async () => {
  const ORIGIN = window.location.origin;
  const STYLE_JSON = `${ORIGIN}/styles/bright-complete.json`;
  const SPRITE_URL = `${ORIGIN}/sprites/sprite`;
  const GLYPHS_URL = `${ORIGIN}/fonts/{fontstack}/{range}.pbf`;
  const RTL_PLUGIN_URL = `${ORIGIN}/vendor/maplibre/mapbox-gl-rtl-text.js`;

  protocol = new Protocol();

  try {
    maplibregl.addProtocol('pmtiles', protocol.tile);
  } catch {
    // ignore if already added
  }

  const pm = new PMTiles(`${ORIGIN}${pmtilesUrl}`);
  protocol.add(pm);

  try {
    maplibregl.setRTLTextPlugin(RTL_PLUGIN_URL, null, true);
  } catch {
    // ignore plugin error
  }

  let style = await fetch(STYLE_JSON).then((r) => r.json());

  for (const [, src] of Object.entries(style.sources)) {
    if (src.type === 'vector') {
      src.url = `pmtiles://${ORIGIN}${pmtilesUrl}`;
      delete src.tiles;
    }
  }

  style.sprite = SPRITE_URL;
  style.glyphs = GLYPHS_URL;
  style = applyBilingualLabels(style);

  map = new maplibregl.Map({
    container: mapEl.value,
    style,
    center: [69.2, 34.5],
    zoom: 7,
    minZoom: 3,
    maxZoom: 18,
  });

  map.addControl(new maplibregl.NavigationControl(), 'top-right');
  map.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

  map.on('load', async () => {
    updateAdminIcons();

    // ✅ load saved points + all module points
    await loadSavedMapPoints();
    await loadAllModulePoints();

    // ✅ apply flyTarget if any
    applyFlyTarget(mapBus.flyTarget);
  });

  map.on('moveend', () => updateAdminIcons());

  /* CLICK ON MAP → pick coordinates + open dialog */
  map.on('click', (e) => {
    const lng = e.lngLat.lng.toFixed(6);
    const lat = e.lngLat.lat.toFixed(6);
    const value = `${lng},${lat}`;

    selectedCoordinates.value = value;
    dialogOpen.value = true;

    emit('selectCoordinates', value);

    if (clickMarker) clickMarker.remove();
    clickMarker = new maplibregl.Marker({ color: '#2563eb' }).setLngLat(e.lngLat).addTo(map);

    // keep your small popup only for clicked coords (not record details)
    new maplibregl.Popup()
      .setLngLat(e.lngLat)
      .setHTML(`<b>Lng:</b> ${lng}<br/><b>Lat:</b> ${lat}`)
      .addTo(map);
  });
});

/* ---------- Fly from search + highlight ---------- */
function flyTo(payload) {
  if (!map) return;

  const { lng, lat, zoom, feature } = payload;

  map.flyTo({
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
  highlightMarker = new maplibregl.Marker({
    element: el,
    anchor: 'bottom',
  })
    .setLngLat([lng, lat])
    .addTo(map);
}

/* ---------- apply flyTarget from MapBusStore ---------- */
function applyFlyTarget(val) {
  if (!val || !val.coords || !map) return;

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

/* ---------- handle saved point from dialog ---------- */
function handlePointSaved(point) {
  dbPointsLocal.value.unshift(point);
  updateSavedPointMarkers();
}

/* ---------- watch external dbPoints (if any parent uses) ---------- */
watch(
  () => props.dbPoints,
  () => updateSavedPointMarkers(),
  { deep: true }
);

/* If you want map to reload markers after save/delete in any module */
watch(
  () => mapBus.reloadTs,
  async (ts) => {
    if (!ts || !map) return;
    await loadSavedMapPoints();
    await loadAllModulePoints();
  }
);

/* ===== React to global flyTo requests ===== */
watch(
  () => mapBus.flyTarget,
  (val) => {
    if (!val || !val.coords || !map) return;

    const coords = typeof val.coords === 'string' ? parseCoordinates(val.coords) : val.coords;
    if (!coords) return;

    const [lng, lat] = coords;
    const meta = val.meta || {};

    map.flyTo({
      center: [lng, lat],
      zoom: meta.zoom || 16,
      essential: true,
      speed: 1.3,
      curve: 1.3,
    });

    // For office employees we usually already show a marker; just clear highlight if needed
    const skipHighlight = meta.type === 'office_employee' || meta.disableHighlight === true;

    if (skipHighlight) {
      if (highlightMarker) {
        highlightMarker.remove();
        highlightMarker = null;
      }
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
  },
  { deep: true }
);

async function reloadAll() {
  if (!map) return;
  await loadSavedMapPoints();
  await loadAllModulePoints();
}

onBeforeUnmount(() => {
  if (highlightMarker) {
    highlightMarker.remove();
    highlightMarker = null;
  }
  if (clickMarker) {
    clickMarker.remove();
    clickMarker = null;
  }

  clearMarkerList(provinceMarkers);
  clearMarkerList(districtMarkers);
  clearMarkerList(zoneMarkers);

  clearMarkerList(savedPointMarkers);
  clearMarkerList(unifiedMarkers);

  map?.remove();
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

.search-panel {
  position: absolute;
  top: 10px;
  left: 10px;
  right: 10px;
  max-width: 420px;
}

.spin {
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0); }
  to { transform: rotate(360deg); }
}
</style>
