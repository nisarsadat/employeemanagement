<template>
  <!-- no UI; markers are rendered on the map -->
  <div style="display:none;" />
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import maplibregl from 'maplibre-gl';

// MDI paths
import {
  mdiSilverwareForkKnife,
  mdiBed,
  mdiAccountMultiplePlus,
  mdiAccountCheck,
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
  mdiMosque,
  mdiFactory,
  mdiGymnastics,
  mdiHomeGroup,
  mdiPoliceBadge,
  mdiTrafficLightOutline,
  mdiAccountLock,
  mdiGreenhouse,
  mdiAccountAlert,
  mdiGasStation,
  mdiAccountSwitch,
  mdiTreeOutline,
  mdiHomeAnalytics,
  mdiCart,
  mdiBriefcase,
  mdiBriefcaseVariant,
  mdiShieldLock,
} from '@mdi/js';

const props = defineProps({
  map: { type: Object, default: null },          // maplibre map instance
  items: { type: Array, default: () => [] },     // [{type, coordinates, ...}]
  rtl: { type: Boolean, default: true },
  labelZoom: { type: Number, default: 11 },      // show label from this zoom
});

const emit = defineEmits(['clickItem']);

/* ============================================================
  ✅ ICON + PASHTO LABEL mapping (match by "type"/table name)
============================================================ */
const ICONS = {
  hotels: { label: 'مسافر خانی او میلمستون', path: mdiSilverwareForkKnife },
  hostels: { label: 'لیلی او کرایی اطاقونه', path: mdiBed },

  tujarans: { label: 'علماء او متنفذین', path: mdiAccountMultiplePlus },
  mujahedinos: { label: 'مجاهدین', path: mdiAccountCheck },

  // NOTE: you didn’t provide an explicit table for "د مسولینو کورونه"
  // I mapped it to old_jahadis as “important persons/old commanders” group.
  // If you want it for another table, tell me and I will switch.
  old_soldiers_homes: { label: 'د مسولینو کورونه', path: mdiHomePlusOutline },

  old_jahadis: { label: 'پخوانی جهادی قومندانان', path: mdiAccountStar },
  mashkok_people: { label: 'مشکوک افراد', path: mdiAccountQuestion },

  madrasas: { label: 'دینی مدرسی', path: mdiTownHall },
  schools: { label: 'ښوونځی او روزنیز مرکزونه', path: mdiBookOpenBlankVariantOutline },

  hado_bus_stops: { label: 'د هډو په اړه', path: mdiCarMultiple },
  transport_companies: { label: 'ترانسپورتی شرکتونه', path: mdiBusMultiple },
  car_sales_companies: { label: 'موټر پلورنځی', path: mdiCarHatchback },
  parkings: { label: 'د پارکینونو په اړه', path: mdiCarBrakeParking },

  banks: { label: 'بانکونه او نماینده ګیانی', path: mdiBank },
  apartments: { label: 'رهایشی بلاکونه', path: mdiCity },

  rahnoma_mamlats: { label: 'رهنماء معاملات', path: mdiStoreSearchOutline },
  hosppitals: { label: 'روغتونونه', path: mdiHospitalBox },
  work_shops: { label: 'مستری خانی', path: mdiCarWrench },

  wakilanos: { label: 'وکیلان او ملکان', path: mdiAccountEdit },
  sarayanos: { label: 'د کوڅو نماینده کان', path: mdiAccountDetails },

  universities: { label: 'پوهنتونونه', path: mdiSchool },
  masjids: { label: 'مساجد، خانقاوی', path: mdiMosque },
  companies: { label: 'شرکتونه', path: mdiFactory },
  companies: { label: 'ورزشي کلبونه', path: mdiGymnastics },
  mahajerat: { label: 'نوې راغلي کورونه', path: mdiHomeGroup },
  soldier: { label: 'پخواني چارواکي', path: mdiPoliceBadge },

  // "څلور لاری" mapping: I mapped it to complexes (you can change)
  complexes: { label: 'څلور لاری', path: mdiTrafficLightOutline },

  captives: { label: 'اختطاف شوی', path: mdiAccountLock },

  // "مهم تاسیسات" mapping: I mapped it to econemy_projects (you can change)
  econemy_projects: { label: 'مهم تاسیسات', path: mdiGreenhouse },

  criminal_capture_places: { label: 'هغه ساحی چې مجرمین پکې نیول شوی', path: mdiAccountAlert },

  pumps: { label: 'پمپونه', path: mdiGasStation },
  shoras: { label: 'محلی شورا ګانی', path: mdiAccountSwitch },
  parks: { label: 'پارکونه', path: mdiTreeOutline },
  governmentplaces: { label: 'دولتی ځایونه', path: mdiHomeAnalytics },
  supermarkets: { label: 'سوپر مارکیټونه', path: mdiCart },

  emarat_offices: { label: 'داخلی مؤسسه او بنسټونه', path: mdiBriefcase },
  deplomaticplaces: { label: 'دیپلوماتیک ځایونه', path: mdiBriefcaseVariant },
  checkpoints: { label: 'پوستی او چیک پاینټونه', path: mdiShieldLock },
};

/* ============================================================
  Helpers
============================================================ */
function parseCoordinates(str) {
  if (!str) return null;
  const m = String(str).trim().match(/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/);
  if (!m) return null;
  const lng = parseFloat(m[1]);
  const lat = parseFloat(m[2]);
  if (!isFinite(lng) || !isFinite(lat)) return null;
  return [lng, lat];
}

function safeLabelFor(item) {
  const m = ICONS[item?.type];
  return m?.label || item?.typeLabel || item?.type || '';
}

function safePathFor(item) {
  const m = ICONS[item?.type];
  return m?.path || null;
}

function createSvgIcon(pathD) {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('viewBox', '0 0 24 24');
  svg.setAttribute('width', '18');
  svg.setAttribute('height', '18');

  const p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
  p.setAttribute('d', pathD);
  svg.appendChild(p);

  return svg;
}

function createMarkerEl(item, showLabel) {
  const wrapper = document.createElement('div');
  wrapper.className = 'mmi-marker';
  wrapper.dir = props.rtl ? 'rtl' : 'ltr';

  // bubble
  const bubble = document.createElement('div');
  bubble.className = 'mmi-bubble';

  // icon circle
  const iconWrap = document.createElement('div');
  iconWrap.className = 'mmi-icon';

  const pathD = safePathFor(item);
  if (pathD) {
    const svg = createSvgIcon(pathD);
    svg.classList.add('mmi-svg');
    iconWrap.appendChild(svg);
  } else {
    // fallback dot if no icon
    const dot = document.createElement('div');
    dot.className = 'mmi-dot';
    iconWrap.appendChild(dot);
  }

  const label = document.createElement('div');
  label.className = 'mmi-label';
  label.textContent = safeLabelFor(item);
  if (!showLabel) label.style.display = 'none';

  bubble.appendChild(iconWrap);
  bubble.appendChild(label);

  wrapper.appendChild(bubble);
  return { wrapper, label };
}

/* ============================================================
  Marker lifecycle
============================================================ */
const markers = ref([]);
let zoomHandler = null;

function clearMarkers() {
  markers.value.forEach((m) => m.marker.remove());
  markers.value = [];
}

function rebuildMarkers() {
  const map = props.map;
  if (!map) return;

  clearMarkers();

  const showLabel = map.getZoom() >= props.labelZoom;

  (props.items || []).forEach((item) => {
    const coords = parseCoordinates(item?.coordinates);
    if (!coords) return;

    const { wrapper, label } = createMarkerEl(item, showLabel);

    const marker = new maplibregl.Marker({ element: wrapper, anchor: 'bottom' })
      .setLngLat(coords)
      .addTo(map);

    wrapper.addEventListener('click', (ev) => {
      ev.stopPropagation();
      emit('clickItem', item);
    });

    markers.value.push({ marker, label });
  });

  // attach zoom listener once
  if (!zoomHandler) {
    zoomHandler = () => {
      const visible = map.getZoom() >= props.labelZoom;
      markers.value.forEach((m) => {
        if (m?.label) m.label.style.display = visible ? 'block' : 'none';
      });
    };
    map.on('zoom', zoomHandler);
  }
}

watch(
  () => props.map,
  () => rebuildMarkers()
);

watch(
  () => props.items,
  () => rebuildMarkers(),
  { deep: true }
);

onBeforeUnmount(() => {
  const map = props.map;
  if (map && zoomHandler) {
    map.off('zoom', zoomHandler);
  }
  zoomHandler = null;
  clearMarkers();
});
</script>

<style scoped>
.mmi-marker {
  transform: translateY(-4px);
  cursor: pointer;
}

.mmi-bubble {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 8px;
  border-radius: 999px;
  background: #ffffff;
  box-shadow: 0 0 0 2px #ffffff, 0 1px 6px rgba(0, 0, 0, 0.30);
  white-space: nowrap;
}

.mmi-icon {
  width: 22px;
  height: 22px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(37, 99, 235, 0.08);
}

.mmi-svg {
  fill: #1f2937;
}

.mmi-dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: #2563eb;
}

.mmi-label {
  font-size: 12px;
  font-weight: 700;
  color: #111827;
  opacity: 0.95;
  line-height: 1;
}
</style>
