<template>
  <div class="debug-panel">
    <div class="debug-controls">
      <button @click="togglePanel" class="debug-btn">
        🐛 {{ isOpen ? 'Hide Debug' : 'Show Debug' }}
      </button>
      
      <div v-if="isOpen" class="debug-content">
        <h3>Map Debugger</h3>
        
        <div class="debug-section">
          <button @click="checkSourceLayers" class="action-btn">
            Check Source Layers
          </button>
          <button @click="checkRoadFeatures" class="action-btn">
            Check Road Names
          </button>
          <button @click="enableClickInspect" class="action-btn">
            {{ clickInspect ? 'Disable' : 'Enable' }} Click Inspect
          </button>
        </div>

        <div class="debug-output">
          <h4>Debug Output:</h4>
          <pre>{{ debugLog }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  map: Object,
  layerIdSet: Set,
  pm: Object
})

const isOpen = ref(false)
const clickInspect = ref(false)
const debugLog = ref('')

const togglePanel = () => {
  isOpen.value = !isOpen.value
}

const log = (message) => {
  debugLog.value = `${new Date().toLocaleTimeString()}: ${message}\n${debugLog.value}`
}

const checkSourceLayers = () => {
  log(`📊 Available source layers: ${[...props.layerIdSet].join(', ')}`)
  
  // Check what layers are currently on the map
  const style = props.map.getStyle()
  const styleLayers = style.layers.map(l => l.id)
  log(`🗺️ Style layers on map: ${styleLayers.length} layers`)
  
  // Show road-related layers
  const roadLayers = styleLayers.filter(id => 
    id.includes('road') || id.includes('highway') || id.includes('transportation')
  )
  log(`🛣️ Road-related layers: ${roadLayers.join(', ')}`)
}

const checkRoadFeatures = async () => {
  log('🔍 Checking road features in current view...')
  
  const bounds = props.map.getBounds()
  const zoom = props.map.getZoom()
  log(`📍 Current view: zoom ${zoom}, bounds: ${JSON.stringify(bounds.toArray())}`)
  
  // Get features in current view
  const features = props.map.queryRenderedFeatures({
    layers: props.map.getStyle().layers
      .filter(l => l.id.includes('road') || l.id.includes('highway') || l.id.includes('label'))
      .map(l => l.id)
  })
  
  const roadNameFeatures = features.filter(f => 
    f.sourceLayer === 'transportation_name' && 
    (f.properties.name || f.properties['name:fa'] || f.properties.ref)
  )
  
  log(`🛣️ Found ${roadNameFeatures.length} road name features in view`)
  
  roadNameFeatures.slice(0, 10).forEach(feature => {
    log(`   - ${feature.properties.name || feature.properties['name:fa'] || feature.properties.ref} (class: ${feature.properties.class})`)
  })
  
  if (roadNameFeatures.length === 0) {
    log('❌ No road names found in current view!')
    
    // Check if we have any transportation features at all
    const allTransportFeatures = features.filter(f => 
      f.sourceLayer === 'transportation_name'
    )
    log(`📦 Total transportation_name features: ${allTransportFeatures.length}`)
    
    if (allTransportFeatures.length > 0) {
      log('ℹ️ Transportation features exist but have no names:')
      allTransportFeatures.slice(0, 5).forEach(f => {
        log(`   - Class: ${f.properties.class}, Props: ${JSON.stringify(f.properties)}`)
      })
    }
  }
}

const enableClickInspect = () => {
  clickInspect.value = !clickInspect.value
  
  if (clickInspect.value) {
    props.map.on('click', handleMapClick)
    log('🔍 Click inspection ENABLED - click anywhere on map')
  } else {
    props.map.off('click', handleMapClick)
    log('🔍 Click inspection DISABLED')
  }
}

const handleMapClick = (e) => {
  const features = props.map.queryRenderedFeatures(e.point)
  log(`📍 Click at [${e.lngLat.lng.toFixed(4)}, ${e.lngLat.lat.toFixed(4)}]`)
  log(`   Found ${features.length} features`)
  
  // Group by source layer
  const byLayer = {}
  features.forEach(f => {
    const layer = f.sourceLayer || 'unknown'
    if (!byLayer[layer]) byLayer[layer] = []
    byLayer[layer].push(f)
  })
  
  Object.keys(byLayer).forEach(layer => {
    log(`   ${layer}: ${byLayer[layer].length} features`)
    
    // Show details for transportation_name features
    if (layer === 'transportation_name') {
      byLayer[layer].forEach(f => {
        log(`     🛣️ Road: ${f.properties.name || f.properties['name:fa'] || 'NO NAME'} (class: ${f.properties.class})`)
        log(`        All props: ${JSON.stringify(f.properties)}`)
      })
    }
  })
}

onBeforeUnmount(() => {
  if (props.map && clickInspect.value) {
    props.map.off('click', handleMapClick)
  }
})
</script>

<style scoped>
.debug-panel {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 1000;
  background: white;
  border: 2px solid #ccc;
  border-radius: 8px;
  max-width: 500px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.debug-controls {
  padding: 10px;
}

.debug-btn {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.debug-btn:hover {
  background: #45a049;
}

.debug-content {
  margin-top: 10px;
  border-top: 1px solid #eee;
  padding-top: 10px;
}

.debug-section {
  margin-bottom: 15px;
}

.action-btn {
  background: #2196F3;
  color: white;
  border: none;
  padding: 6px 12px;
  margin: 2px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.action-btn:hover {
  background: #0b7dda;
}

.debug-output {
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 10px;
  max-height: 300px;
  overflow-y: auto;
  font-family: monospace;
  font-size: 12px;
}

.debug-output pre {
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
}

h3, h4 {
  margin: 0 0 10px 0;
  color: #333;
}
</style>