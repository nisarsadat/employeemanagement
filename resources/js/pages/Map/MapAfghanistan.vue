<template>
  <div class="map-wrap">
    <div ref="mapEl" class="map"></div>

    <!-- Search Component -->
    <PmtilesSearchMap class="search-panel" @flyTo="flyTo" />

    <!-- Dialog to save person/place at clicked coordinates -->
    <AddMapPointDialog
      v-model="dialogOpen"
      :coordinates="selectedCoordinates"
      @saved="handlePointSaved"
    />
  </div>
</template>

<script setup>
  import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
  import maplibregl from 'maplibre-gl';
  import 'maplibre-gl/dist/maplibre-gl.css';
  import { PMTiles, Protocol } from 'pmtiles';
  import PmtilesSearchMap from './PmtilesSearchMap.vue';
  import AddMapPointDialog from './AddMapPointDialog.vue';
  import { useMapPointsRepository } from '../../stores/MapPointsRepository';
  import { useMapBusStore } from '../../stores/MapBusStore';

  const mapPointsStore = useMapPointsRepository();
  const mapBus = useMapBusStore();

  /* ---------- PNG icons ---------- */
  import provinceIcon from './province.png';
  import districtIcon from './districts.png';
  import zoneIcon from './zone.png';
  import roadIcon from './road.png';
  import streetIcon from './street.png';
  import schoolIcon from './school.png';
  import locationIcon from './location.png'; // default for selected marker / DB markers

  /* ---------- props & emits for optional external integration ---------- */
  const props = defineProps({
    dbPoints: {
      type: Array,
      default: () => [],
    },
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

  // DB markers (backend points)
  let dbMarkers = [];

  // ---------- dialog + local DB points ----------
  const dialogOpen = ref(false);
  const selectedCoordinates = ref('');
  const dbPointsLocal = ref([]);

  async function loadPoints() {
    try {
      dbPointsLocal.value = await mapPointsStore.FetchMapPoints();
      updateDbMarkers();
    } catch (e) {
      console.error('Failed to load map points', e);
    }
  }

  /* ---------- Bilingual labels (unchanged) ---------- */
  const LOCAL_NAME_ORDER = [
    'name:fa',
    'name:prs',
    'name:ps',
    'name:ar',
    'name:nonlatin',
    'name',
    'name:en',
    'name_en',
    'name:latin',
    'name_int',
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
          'step',
          ['zoom'],
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

    if (['province', 'wilaya', 'velayat', 'state', 'region'].includes(t)) {
      return provinceIcon;
    }
    if (['district', 'woleswali', 'county', 'city', 'town'].includes(t)) {
      return districtIcon;
    }
    if (
      [
        'zone',
        'nahia',
        'hawza',
        'village',
        'hamlet',
        'suburb',
        'neighbourhood',
        'neighborhood',
      ].includes(t)
    ) {
      return zoneIcon;
    }
    if (t === 'road' || t === 'highway') return roadIcon;
    if (['street', 'residential', 'primary', 'secondary'].includes(t)) {
      return streetIcon;
    }
    if (['school', 'university', 'college'].includes(t)) return schoolIcon;

    return locationIcon;
  }

  /* ---------- HTML bubble for selected place (search result / flyTo) ---------- */
  function createHighlightElement(feature) {
    const props = feature?.properties || {};
    const photoUrl = props.photoUrl || ''; // 👈 from MapBus

    const el = document.createElement('div');

    // If we have a photo → show circular avatar marker
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

    // Otherwise: fallback to your old icon bubble
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

  function createDbMarkerElement(point) {
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
    img.alt = point.name || 'person';
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'cover';
    img.style.pointerEvents = 'none';

    const origin = window.location.origin;

    // ---- try all possible keys ----
    let raw =
      point.picture ||
      point.picture_url ||
      point.image_url ||
      point.photo_url ||
      point.photo ||
      null;

    let pictureUrl = null;

    if (raw) {
      const pic = String(raw).trim();

      if (pic.startsWith('http://') || pic.startsWith('https://')) {
        pictureUrl = pic; // already full URL
      } else {
        const clean = pic.replace(/^\/+/, ''); // remove leading '/'
        pictureUrl = `${origin}/${clean}`;
      }
    }

    console.log('DB marker photo', {
      raw,
      pictureUrl,
      point,
    });

    img.src = pictureUrl || locationIcon;

    el.appendChild(img);
    return el;
  }

  /* ---------- popup content for DB points (no image) ---------- */
  function buildPointPopupHtml(p) {
    return `
    <div style="font-size:12px; line-height:1.5; direction:rtl; max-width:220px;">
      <strong>${p.name || '-'}</strong><br/>
      ${p.father_name ? `<b>نام پدر:</b> ${p.father_name}<br/>` : ''}
      ${p.code_number ? `<b>کد نمبر:</b> ${p.code_number}<br/>` : ''}
      ${p.phone ? `<b>شماره تماس:</b> ${p.phone}<br/>` : ''}
      ${p.note ? `<b>یادداشت:</b> ${p.note}<br/>` : ''}
      ${p.coordinates ? `<b>کواردینات:</b> ${p.coordinates}` : ''}
    </div>
  `;
  }

  /* ---------- helpers ---------- */
  function clearMarkerList(list) {
    list.forEach((m) => m.remove());
    list.length = 0;
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

  /* ---------- DB markers ---------- */
  function updateDbMarkers() {
    if (!map) return;

    clearMarkerList(dbMarkers);

    const allPoints = [...(props.dbPoints || []), ...(dbPointsLocal.value || [])];

    allPoints.forEach((p) => {
      const coords = parseCoordinates(p.coordinates || p.coordinate || p.coords);
      if (!coords) return;

      const el = createDbMarkerElement(p);

      const marker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
        .setLngLat(coords)
        .setPopup(new maplibregl.Popup({ offset: 30 }).setHTML(buildPointPopupHtml(p)))
        .addTo(map);

      el.addEventListener('click', (ev) => {
        ev.stopPropagation();

        const popupHtml = `
    <div style="font-family: sans-serif; direction: rtl;">
      <strong>${p.name || 'بدون نام'}</strong><br>
      ${p.father_name ? 'پدر: ' + p.father_name + '<br>' : ''}
      ${p.code_number ? 'کد نمبر: ' + p.code_number + '<br>' : ''}
      ${p.phone ? 'تماس: ' + p.phone + '<br>' : ''}
      <small style="color: #555">کواردینات: ${p.coordinates}</small>
      <hr style="margin:6px 0;">
      <button id="delete-map-point-${p.id}"
              style="padding:6px 10px; font-size:12px;
                     background:#dc2626; color:#fff;
                     border:none; border-radius:4px; cursor:pointer;">
        حذف
      </button>
    </div>
  `;

        const popup = new maplibregl.Popup({ closeOnClick: true })
          .setLngLat(coords)
          .setHTML(popupHtml)
          .addTo(map);

        // Attach click handler AFTER popup renders
        setTimeout(() => {
          const btn = document.getElementById(`delete-map-point-${p.id}`);
          if (btn) {
            btn.addEventListener('click', async () => {
              if (confirm('آیا مطمئن هستید حذف شود؟')) {
                await mapPointsStore.DeleteMapPoint(p.id);
                updateDbMarkers();
                popup.remove();
              }
            });
          }
        }, 50);
      });

      dbMarkers.push(marker);
    });

    console.log('DB markers:', dbMarkers.length);
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

    map.on('load', () => {
      updateAdminIcons();
      loadPoints(); // load DB markers after map ready

      // ✅ if a flyTarget was set before opening the map, apply it now
      applyFlyTarget(mapBus.flyTarget);
    });

    map.on('moveend', () => {
      updateAdminIcons();
    });

    /* CLICK ON MAP → pick coordinates + popup + open dialog */
    map.on('click', (e) => {
      const lng = e.lngLat.lng.toFixed(6);
      const lat = e.lngLat.lat.toFixed(6);
      const value = `${lng},${lat}`;

      selectedCoordinates.value = value;
      dialogOpen.value = true;

      emit('selectCoordinates', value);

      if (clickMarker) clickMarker.remove();
      clickMarker = new maplibregl.Marker({ color: '#2563eb' }).setLngLat(e.lngLat).addTo(map);

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
          photoUrl: meta.photoUrl || '', // 👈 add this
        },
      },
    });
  }

  /* ---------- handle saved point from dialog ---------- */
  function handlePointSaved(point) {
    dbPointsLocal.value.unshift(point);
    updateDbMarkers();
  }

  /* ---------- watch external dbPoints (if any parent uses) ---------- */
  watch(
    () => props.dbPoints,
    () => {
      updateDbMarkers();
    },
    { deep: true }
  );

  watch(
    () => dbPointsLocal.value,
    () => {
      updateDbMarkers();
    },
    { deep: true }
  );

  // ===== React to global flyTo requests (from any module) =====
  watch(
    () => mapBus.flyTarget,
    (val) => {
      if (!val || !val.coords || !map) return;

      const coords = typeof val.coords === 'string' ? parseCoordinates(val.coords) : val.coords;
      if (!coords) return;

      const [lng, lat] = coords;
      const meta = val.meta || {};

      // Always move the camera
      map.flyTo({
        center: [lng, lat],
        zoom: meta.zoom || 16,
        essential: true,
        speed: 1.3,
        curve: 1.3,
      });

      // ✅ NEW: persist this routed person/place as a DB point,
      // so it always appears on the map (for this session)
      const coordStr =
        typeof val.coords === 'string'
          ? val.coords
          : `${lng.toFixed(6)},${lat.toFixed(6)}`;

      // build a simple key so we don't duplicate the same coord+title
      const mapKey = `${coordStr}::${meta.title || meta.name || ''}`;

      const existing = (dbPointsLocal.value || []).find(
        (p) => p._mapKey === mapKey
      );

      const basePoint = {
        _mapKey: mapKey, // internal only
        id: meta.id ?? null,
        name: meta.name || meta.title || '',
        father_name: meta.father_name || '',
        code_number: meta.code_number || '',
        phone: meta.phone || '',
        note: meta.note || meta.subtitle || '',
        picture: meta.photoUrl || meta.picture || '',
        coordinates: coordStr,
      };

      if (existing) {
        Object.assign(existing, basePoint);
      } else {
        dbPointsLocal.value.push(basePoint);
      }

      // For office employees we already have a DB marker with photo.
      // → remove any old highlight marker and DO NOT create a new one.
      const skipHighlight = meta.type === 'office_employee' || meta.disableHighlight === true;

      if (skipHighlight) {
        if (highlightMarker) {
          highlightMarker.remove();
          highlightMarker = null;
        }
        return;
      }

      // For everything else, show the highlight marker
      const feature = {
        type: meta.type || 'generic',
        properties: {
          title: meta.title,
          subtitle: meta.subtitle,
          photoUrl: meta.photoUrl || '',
        },
      };

      flyTo({
        lng,
        lat,
        zoom: meta.zoom || 16,
        feature,
      });
    },
    { deep: true }
  );

  // If you want map to reload markers after save/delete in any module
  watch(
    () => mapBus.reloadTs,
    (ts) => {
      if (!ts || !map) return;
      loadPoints();
    }
  );

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
    clearMarkerList(dbMarkers);
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
</style>
