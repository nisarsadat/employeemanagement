<template>
  <div class="map-wrap">
    <div ref="mapEl" class="map"></div>

    <!-- Search Component -->
    <PmtilesSearchMap class="search-panel" @flyTo="flyTo" />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import { PMTiles, Protocol } from 'pmtiles';
import PmtilesSearchMap from './PmtilesSearchMap.vue';

/* ---------- PNG icons ---------- */
import provinceIcon from './province.png';
import districtIcon from './districts.png';
import zoneIcon from './zone.png';
import roadIcon from './road.png';
import streetIcon from './street.png';
import schoolIcon from './school.png';
import locationIcon from './location.png'; // default for selected marker

const mapEl = ref(null);
let map, protocol;
const pmtilesUrl = '/pmtiles-range/afghanistan.pmtiles';

// highlighted marker from search
let highlightMarker = null;

// static icon markers
let provinceMarkers = [];
let districtMarkers = [];
let zoneMarkers = [];

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
  'name_int'
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
        ['literal', FONT_STACK_Z_GE6]
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
      'neighborhood'
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

/* ---------- HTML bubble for selected place ---------- */
function createHighlightElement(feature) {
  const el = document.createElement('div');

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
  img.src =
    kind === 'province'
      ? provinceIcon
      : kind === 'district'
      ? districtIcon
      : zoneIcon;
  img.style.width = '12px';
  img.style.height = '12px';
  img.style.objectFit = 'contain';
  img.style.pointerEvents = 'none';

  el.appendChild(img);
  return el;
}

function clearMarkerList(list) {
  list.forEach((m) => m.remove());
  list.length = 0;
}

/* ---------- create icons on top of place-* labels using HTML markers ---------- */
function updateAdminIcons() {
  if (!map) return;

  const zoom = map.getZoom();

  // remove old markers
  clearMarkerList(provinceMarkers);
  clearMarkerList(districtMarkers);
  clearMarkerList(zoneMarkers);

  // 1) Provinces – use place-state layer (shows ولایت names)
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
    console.log('province markers:', provinceMarkers.length);
  }

  // 2) Districts – use place-city + place-town; only from zoom >= 6
  if (zoom >= 6) {
    const districtLayers = ['place-city', 'place-town'].filter((id) =>
      map.getLayer(id)
    );
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
      console.log('district markers:', districtMarkers.length);
    }
  }

  // 3) Zones / Nahia – use place-village + place-other; zoom >= 8
  if (zoom >= 8) {
    const zoneLayers = ['place-village', 'place-other'].filter((id) =>
      map.getLayer(id)
    );
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
      console.log('zone markers:', zoneMarkers.length);
    }
  }
}

/* ---------- Initialize map (your same logic) ---------- */
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

  // convert vector sources to pmtiles protocol
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
    maxZoom: 18
  });

  map.addControl(new maplibregl.NavigationControl(), 'top-right');
  map.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

  map.on('load', () => {
    // initial drop of icons
    updateAdminIcons();
  });

  // whenever you move/zoom, update the HTML markers
  map.on('moveend', () => {
    updateAdminIcons();
  });
});

/* ---------- Fly from search + highlight (same as before) ---------- */
function flyTo(payload) {
  if (!map) return;

  const { lng, lat, zoom, feature } = payload;

  map.flyTo({
    center: [lng, lat],
    zoom: zoom || 14,
    essential: true,
    speed: 1,
    curve: 1.3
  });

  if (highlightMarker) {
    highlightMarker.remove();
    highlightMarker = null;
  }

  const el = createHighlightElement(feature || {});
  highlightMarker = new maplibregl.Marker({
    element: el,
    anchor: 'bottom'
  })
    .setLngLat([lng, lat])
    .addTo(map);
}

onBeforeUnmount(() => {
  if (highlightMarker) {
    highlightMarker.remove();
    highlightMarker = null;
  }
  clearMarkerList(provinceMarkers);
  clearMarkerList(districtMarkers);
  clearMarkerList(zoneMarkers);
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
