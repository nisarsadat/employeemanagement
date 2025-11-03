<template>
  <div>
    <h2>Offline OpenStreetMap</h2>
    <OfflineMap
      ref="offlineMap"
      :center="mapCenter"
      :zoom="zoomLevel"
      @map-click="handleMapClick"
    />
    
    <div class="controls">
      <button @click="addSampleMarker">Add Marker</button>
      <button @click="clearMarkers">Clear Markers</button>
      <button @click="setToLondon">London</button>
      <button @click="setToNewYork">New York</button>
    </div>
  </div>
</template>

<script>
import OfflineMap from './MapAfghanistan.vue';

export default {
  name: 'App',
  components: {
    OfflineMap
  },
  data() {
    return {
      mapCenter: [51.505, -0.09],
      zoomLevel: 13
    };
  },
  methods: {
    handleMapClick(coords) {
      console.log('Map clicked at:', coords);
      this.$refs.offlineMap.addMarker(coords.lat, coords.lng, `Location: ${coords.lat.toFixed(4)}, ${coords.lng.toFixed(4)}`);
    },
    
    addSampleMarker() {
      this.$refs.offlineMap.addMarker(51.505, -0.09, 'This is a sample marker!');
    },
    
    clearMarkers() {
      this.$refs.offlineMap.clearMarkers();
    },
    
    setToLondon() {
      this.mapCenter = [51.505, -0.09];
      this.zoomLevel = 13;
    },
    
    setToNewYork() {
      this.mapCenter = [40.7128, -74.0060];
      this.zoomLevel = 12;
    }
  }
};
</script>

<style>
.controls {
  margin-top: 10px;
}

.controls button {
  margin: 0 5px;
  padding: 8px 16px;
  background: #007cbf;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.controls button:hover {
  background: #005a87;
}
</style>