<template>
  <!-- no UI; markers are rendered on the map -->
  <div style="display: none" />
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
  } from '@mdi/js';

  const props = defineProps({
    map: { type: Object, default: null }, // maplibre map instance
    items: { type: Array, default: () => [] }, // [{type, coordinates, ...}]
    rtl: { type: Boolean, default: true },
    labelZoom: { type: Number, default: 11 }, // show label from this zoom
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
    saeen_fasad: { label: 'ساعین بالفساد', path: mdiAccountRemove },
    fasad: { label: 'فساد', path: mdiAccountMinus },
    highways: { label: 'څلور لارې او درې لارې', path: mdiHighway },
    sarayanos: { label: 'سرایونه', path: mdiWarehouse },
    sarafyano: { label: 'صرافیانې', path: mdiBankTransfer },
    badmashanos: { label: 'زورواکان او ولکه داران', path: mdiAccountMultipleRemove },
    car_sales_companies: { label: 'موټر پلورونځی', path: mdiCarBack },
    transport_companies: { label: 'ترانسپورتي شرکتونه', path: mdiTrainCar },
    mashkok_homes: { label: 'مشکوک کورونه', path: mdiHomeGroupRemove },

    // NOTE: you didn’t provide an explicit table for "د مسولینو کورونه"
    // I mapped it to old_jahadis as “important persons/old commanders” group.
    // If you want it for another table, tell me and I will switch.
    old_soldiers_homes: { label: 'د پخواني چارواکو کورونه', path: mdiHomePlusOutline },

    old_jahadis: { label: 'پخوانی جهادی قومندانان', path: mdiAccountStar },
    mashkok_people: { label: 'مشکوک افراد', path: mdiAccountBoxMinusOutline },

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
    mulkano: { label: 'د کوڅو نماینده کان', path: mdiAccountDetails },

    universities: { label: 'پوهنتونونو ، دلوړو زده کړو موسسو او انسټیټیوټونو', path: mdiSchool },
    heist: { label: 'سرقت کوونکي', path: mdiAccountQuestion },
    masjids: { label: 'مساجد، خانقاوی', path: mdiMosque },
    companies: { label: 'فابریکې او شرکتونه', path: mdiFactory },
    damage_places: { label: 'آسیب پذیره ساحې', path: mdiFireplace },
    damage_rasan_places: { label: 'آسیب رسانه ساحې', path: mdiFireplaceOff },
    clubs: { label: 'ورزشي کلبونه', path: mdiGymnastics },
    done_lost_places: { label: 'هغه ساحې چې اختطاف پکې ترسره شوی', path: mdiSelectPlace },
    mahajerat: { label: 'نوې راغلي کورونه', path: mdiHomeGroup },
    soldier: { label: 'پخواني چارواکي', path: mdiPoliceBadge },

    // "څلور لاری" mapping: I mapped it to complexes (you can change)
    complexes: { label: 'داخلي موسسې او بنسټونه', path: mdiOfficeBuildingPlus },

    captives: { label: 'راخوشي شوي بندیان', path: mdiAccountLock },

    // "مهم تاسیسات" mapping: I mapped it to econemy_projects (you can change)
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
    office_employee: { label: 'اطلاعات', path: mdiAccountPlus },

  };

  /* ============================================================
  Helpers
============================================================ */
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
    box-shadow: 0 0 0 2px #ffffff, 0 1px 6px rgba(0, 0, 0, 0.3);
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
