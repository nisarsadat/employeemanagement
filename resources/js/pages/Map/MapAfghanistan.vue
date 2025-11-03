<template>
  <div ref="mapEl" class="map"></div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import maplibregl from 'maplibre-gl'
import { PMTiles, Protocol } from 'pmtiles'

const mapEl = ref(null)
let map

// Helpers
function setDariLabels(style) {
  if (!style?.layers) return style
  const order = ['name:fa','name:prs','name:ps','name:ar','name','name:en']
  const expr = ['coalesce', ...order.map(f => ['get', f])]
  for (const layer of style.layers) {
    if (layer.type === 'symbol') {
      layer.layout ||= {}
      layer.layout['text-field'] = expr
      layer.layout['text-font'] = ['Noto Naskh Arabic Regular']
      layer.paint ||= {}
      layer.paint['text-halo-color'] = '#fff'
      layer.paint['text-halo-width'] = 1
    }
  }
  return style
}
function addIfSourceLayer({ id, def, layerIdSet }) {
  const sl = def['source-layer']
  if (!sl || layerIdSet.has(sl)) {
    try { map.addLayer({ id, ...def }) } catch {}
  }
}
function addBoundaryLine({ id, admin, color, width, dash, srcName, layerIdSet }) {
  addIfSourceLayer({
    id, layerIdSet,
    def: {
      type: 'line',
      source: srcName,
      'source-layer': 'boundary',
      filter: ['all', ['==', ['get','admin_level'], String(admin)], ['==', ['get','maritime'], 0]],
      paint: { 'line-color': color, 'line-width': width, ...(dash ? {'line-dasharray': dash} : {}) }
    }
  })
}
function addBoundaryLabel({ id, admin, color, srcName, layerIdSet }) {
  addIfSourceLayer({
    id, layerIdSet,
    def: {
      type: 'symbol',
      source: srcName,
      'source-layer': 'boundary',
      minzoom: 8,
      filter: ['all', ['==', ['get','admin_level'], String(admin)], ['==', ['get','maritime'], 0]],
      layout: {
        'symbol-placement': 'line',
        'text-field': ['coalesce',['get','name:fa'],['get','name:prs'],['get','name:ps'],['get','name:ar'],['get','name'],['get','name:en']],
        'text-font': ['Noto Naskh Arabic Regular'],
        'text-size': ['interpolate', ['linear'], ['zoom'], 8, 11, 12, 13]
      },
      paint: { 'text-color': color, 'text-halo-color': '#fff', 'text-halo-width': 1.2 }
    }
  })
}
function safePaint(id, prop, val) {
  try { if (map.getLayer(id)) map.setPaintProperty(id, prop, val) } catch {}
}

onMounted(async () => {
  const ORIGIN = window.location.origin
  const PMTILES_URL    = `${ORIGIN}/pmtiles-range/afghanistan.pmtiles`
  const STYLE_JSON     = `${ORIGIN}/styles/bright-fa.json`                   // << colorful style
  const SPRITE_URL     = `${ORIGIN}/sprites/sprite`
  const GLYPHS_URL     = `${ORIGIN}/fonts/{fontstack}/{range}.pbf`
  const RTL_PLUGIN_URL = `${ORIGIN}/vendor/maplibre/mapbox-gl-rtl-text.js`

  // pmtiles protocol
  const protocol = new Protocol()
  try { maplibregl.addProtocol('pmtiles', protocol.tile) } catch {}
  const pm = new PMTiles(PMTILES_URL)
  protocol.add(pm)

  // Discover available source-layers
  let meta = {}
  try { await pm.getHeader(); meta = await pm.getMetadata() } catch (e) {
    console.error('❌ PMTiles metadata failed (HTTP Range?).', e)
    return
  }
  const vectorLayers = Array.isArray(meta?.vector_layers) ? meta.vector_layers : []
  const layerIdSet = new Set(vectorLayers.map(l => l.id))
  console.log('🧩 PMTiles vector layers:', [...layerIdSet])

  try { maplibregl.setRTLTextPlugin(RTL_PLUGIN_URL, null, true) } catch {}

  // load colorful style
  let style = await fetch(STYLE_JSON).then(r => r.json())
  style.sprite = SPRITE_URL
  style.glyphs = GLYPHS_URL
  for (const [, src] of Object.entries(style.sources || {})) {
    if (src.type === 'vector') { src.url = `pmtiles://${PMTILES_URL}`; delete src.tiles }
  }
  style = setDariLabels(style)

  // init
  map = new maplibregl.Map({
    container: mapEl.value,
    style,
    center: [69.2, 34.5],   // Kabul
    zoom: 11,
    minZoom: 3,
    maxZoom: 18,
    pitch: 45,              // 3D perspective
    bearing: 0,
    attributionControl: true
  })
  map.addControl(new maplibregl.NavigationControl({ visualizePitch: true }), 'top-right')
  map.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left')

  map.on('load', async () => {
    const srcName = Object.keys(style.sources)[0]

    // slight color nudges if those layer ids exist in the style
    safePaint('water', 'fill-color', '#bcd8f2')
    safePaint('waterway', 'line-color', '#9cc7ea')
    safePaint('park', 'fill-color', '#d9efd3')
    safePaint('landuse', 'fill-color', '#e8f1e6')

    // boundaries (lines + labels)
    if (layerIdSet.has('boundary')) {
      addBoundaryLine({ id:'bnd-country',  admin:2, color:'#2e3a46', width:1.6, dash:null,  srcName, layerIdSet })
      addBoundaryLine({ id:'bnd-province', admin:4, color:'#3f6d45', width:1.2, dash:[2,2], srcName, layerIdSet })
      addBoundaryLine({ id:'bnd-district', admin:6, color:'#7a3a6b', width:0.9, dash:[1,2], srcName, layerIdSet })
      addBoundaryLine({ id:'bnd-nahia7',   admin:7, color:'#8c5a2b', width:0.8, dash:[1,2], srcName, layerIdSet })
      addBoundaryLine({ id:'bnd-nahia8',   admin:8, color:'#8c5a2b', width:0.8, dash:[1,2], srcName, layerIdSet })
      addBoundaryLine({ id:'bnd-nahia9',   admin:9, color:'#8c5a2b', width:0.8, dash:[1,2], srcName, layerIdSet })

      addBoundaryLabel({ id:'lbl-province', admin:4, color:'#2f7d36', srcName, layerIdSet })
      addBoundaryLabel({ id:'lbl-district', admin:6, color:'#7a3a6b', srcName, layerIdSet })
      addBoundaryLabel({ id:'lbl-nahia7',   admin:7, color:'#8c5a2b', srcName, layerIdSet })
      addBoundaryLabel({ id:'lbl-nahia8',   admin:8, color:'#8c5a2b', srcName, layerIdSet })
      addBoundaryLabel({ id:'lbl-nahia9',   admin:9, color:'#8c5a2b', srcName, layerIdSet })
    }

    // roads (lines)
    if (layerIdSet.has('transportation')) {
      addIfSourceLayer({
        id: 'road-casing',
        layerIdSet,
        def: {
          type: 'line',
          source: srcName,
          'source-layer': 'transportation',
          filter: ['in', ['get','class'], ['literal', ['motorway','trunk','primary','secondary','tertiary','residential']]],
          paint: { 'line-color': '#b8b8b8', 'line-width': ['interpolate',['linear'],['zoom'], 10, 0.8, 16, 3] }
        }
      })
      addIfSourceLayer({
        id: 'road',
        layerIdSet,
        def: {
          type: 'line',
          source: srcName,
          'source-layer': 'transportation',
          filter: ['in', ['get','class'], ['literal', ['motorway','trunk','primary','secondary','tertiary','residential']]],
          paint: { 'line-color': '#ffffff', 'line-width': ['interpolate',['linear'],['zoom'], 10, 0.6, 16, 2.6] }
        }
      })
    }

    // road labels (names/refs) – needs transportation_name
    if (layerIdSet.has('transportation_name')) {
      addIfSourceLayer({
        id: 'road-labels',
        layerIdSet,
        def: {
          type: 'symbol',
          source: srcName,
          'source-layer': 'transportation_name',
          minzoom: 12,
          layout: {
            'text-field': ['coalesce',
              ['get','ref'],
              ['get','name:fa'],['get','name:prs'],['get','name:ps'],
              ['get','name:ar'],['get','name'],['get','name:en']
            ],
            'symbol-placement': 'line',
            'text-font': ['Noto Naskh Arabic Regular'],
            'text-size': ['interpolate', ['linear'], ['zoom'], 12, 11, 16, 14],
            'text-max-angle': 30
          },
          paint: { 'text-color': '#385a9a', 'text-halo-color': '#fff', 'text-halo-width': 1.5 }
        }
      })
    } else {
      console.warn('No transportation_name layer → road names/numbers cannot be shown.')
    }

    // buildings (2D + 3D)
    if (layerIdSet.has('building')) {
      addIfSourceLayer({
        id: 'building-2d',
        layerIdSet,
        def: {
          type: 'fill',
          source: srcName,
          'source-layer': 'building',
          minzoom: 12,
          paint: { 'fill-color': '#d9d6cf', 'fill-opacity': 0.6 }
        }
      })
      addIfSourceLayer({
        id: 'building-3d',
        layerIdSet,
        def: {
          type: 'fill-extrusion',
          source: srcName,
          'source-layer': 'building',
          minzoom: 14,
          paint: {
            'fill-extrusion-color': '#e2e0dc',
            'fill-extrusion-height': [
              'case',
              ['has','render_height'], ['get','render_height'],
              ['has','height'], ['to-number', ['get','height']],
              ['has','levels'], ['*', ['to-number', ['get','levels']], 3],
              5
            ],
            'fill-extrusion-base': [
              'case',
              ['has','render_min_height'], ['get','render_min_height'],
              ['has','min_height'], ['to-number', ['get','min_height']],
              0
            ],
            'fill-extrusion-opacity': 0.9
          }
        }
      })
    }

    // house numbers
    if (layerIdSet.has('housenumber')) {
      addIfSourceLayer({
        id: 'house-numbers',
        layerIdSet,
        def: {
          type: 'symbol',
          source: srcName,
          'source-layer': 'housenumber',
          minzoom: 17,
          layout: { 'text-field': ['get','housenumber'], 'text-font': ['Noto Naskh Arabic Regular'], 'text-size': 12 },
          paint: { 'text-color': '#2f4858', 'text-halo-color': '#fff', 'text-halo-width': 1 }
        }
      })
    }

    // POIs (mosques/universities/schools)
    if (layerIdSet.has('poi')) {
      const addPoi = (id, subclasses, minzoom) => addIfSourceLayer({
        id, layerIdSet,
        def: {
          type: 'symbol',
          source: srcName,
          'source-layer': 'poi',
          minzoom,
          filter: ['in', ['get','subclass'], ['literal', subclasses]],
          layout: {
            'icon-image': ['concat', ['get','subclass'], '-11'], // adjust if your sprite uses other names
            'icon-size': ['interpolate', ['linear'], ['zoom'], 10, 0.8, 16, 1.1],
            'text-field': ['coalesce',
              ['get','name:fa'],['get','name:prs'],['get','name:ps'],
              ['get','name:ar'],['get','name'],['get','name:en']
            ],
            'text-font': ['Noto Naskh Arabic Regular'],
            'text-size': ['interpolate', ['linear'], ['zoom'], 12, 10, 16, 14],
            'text-anchor': 'top',
            'text-offset': [0, 0.8],
            'icon-allow-overlap': false,
            'text-optional': true
          },
          paint: { 'text-halo-color': '#fff', 'text-halo-width': 1 }
        }
      })
      addPoi('poi-mosque',     ['place_of_worship','mosque'], 12)
      addPoi('poi-university', ['university','college'],      11)
      addPoi('poi-school',     ['school'],                    12)
    }

    // optional terrain (safe)
    try {
      const resp = await fetch(DEM_PM_URL, { method: 'HEAD' })
      if (resp.ok) {
        const demPM = new PMTiles(DEM_PM_URL)
        protocol.add(demPM)
        await demPM.getHeader()
        map.addSource('terrain-dem', { type: 'raster-dem', url: `pmtiles://${DEM_PM_URL}`, tileSize: 512 })
        map.setTerrain({ source: 'terrain-dem', exaggeration: 1.25 })
      } else {
        console.info('No DEM found, terrain disabled.')
      }
    } catch { console.info('No DEM found, terrain disabled.') }
  })
})

onBeforeUnmount(() => { if (map) map.remove() })
</script>

<style scoped>
.map { width: 100%; height: 110vh; background: #eef3f7; }
</style>
