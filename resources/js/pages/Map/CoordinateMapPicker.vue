<!-- CoordinateMapPicker.vue -->
<template>
  <div class="coord-layout">
    <div class="coord-form-side">
      <label class="coord-label">Coordinates (lng,lat)</label>
      <input
        v-model="localValue"
        type="text"
        class="coord-input"
        placeholder="مثال: 69.207500,34.555300"
      />
      <small class="coord-help">
        روی نقشه کلیک کن تا خودکار اینجا نوشته شود.
      </small>
    </div>

    <div class="coord-map-side">
      <PmtilesMap
        :person-points="points"
        @coordinatePicked="onCoordinatePicked"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import PmtilesMap from './MapAfghanistan.vue'

const props = defineProps({
  /* v-model="form.coordinates" */
  modelValue: {
    type: String,
    default: ''
  },
  /**
   * List of DB points to show on the map.
   * Example (merge employees + damage places in parent):
   * [
   *   { id: 1, name: 'Ahmad', type: 'employee', coordinates: '69.12345,34.56789' },
   *   { id: 5, name: 'Damage place 1', type: 'damage_place', coordinates: '69.20,34.56' }
   * ]
   */
  points: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const localValue = ref(props.modelValue)
const points = props.points

watch(
  () => props.modelValue,
  (val) => {
    if (val !== localValue.value) {
      localValue.value = val ?? ''
    }
  }
)

watch(localValue, (val) => {
  emit('update:modelValue', val ?? '')
})

function onCoordinatePicked(payload) {
  // payload = { lng, lat, coordinates: "69.207500,34.555300" }
  localValue.value = payload.coordinates
}
</script>

<style scoped>
.coord-layout {
  display: grid;
  grid-template-columns: minmax(220px, 280px) 1fr;
  gap: 12px;
  align-items: stretch;
}

.coord-form-side {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.coord-label {
  font-size: 13px;
  font-weight: 600;
}

.coord-input {
  width: 100%;
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  font-size: 13px;
  font-family: ui-monospace, monospace;
}

.coord-input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.2);
}

.coord-help {
  font-size: 11px;
  color: #6b7280;
}

.coord-map-side {
  min-height: 360px;
  border-radius: 8px;
  overflow: hidden;
}
</style>
