<!-- resources/js/components/Map/MapAfgPage.vue -->
<template>
  <div class="map-page">
    <MapAfg
      :dbPoints="dbPoints"
      @selectCoordinates="handleSelectCoordinates"
      @markerClick="handleMarkerClick"
    />

    <AddMapPointDialog
      v-model="dialogOpen"
      :coordinates="selectedCoordinates"
      @saved="handleSavedPoint"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import MapAfg from './MapAfghanistan.vue'
import AddMapPointDialog from './AddMapPointDialog.vue'
import { useMapPointsRepository } from '../../repositories/useMapPointsRepository'

const { FetchMapPoints } = useMapPointsRepository()

const dbPoints = ref([])
const selectedCoordinates = ref('')
const dialogOpen = ref(false)

async function loadPoints() {
  dbPoints.value = await FetchMapPoints()
}

function handleSelectCoordinates(coordStr) {
  selectedCoordinates.value = coordStr
  dialogOpen.value = true
}

function handleSavedPoint(point) {
  dbPoints.value.unshift(point)
}

function handleMarkerClick(payload) {
  console.log('Marker clicked:', payload)
}

onMounted(() => {
  loadPoints()
})
</script>

<style scoped>
.map-page {
  position: relative;
  width: 100%;
  height: 100vh;
}
</style>
