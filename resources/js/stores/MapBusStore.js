// resources/js/stores/MapBusStore.js
import { defineStore } from 'pinia';

export const useMapBusStore = defineStore('mapBus', {
  state: () => ({
    flyTarget: null,      // { coords: 'lng,lat' or [lng,lat], meta: {...}, ts }
    reloadTs: 0,          // bump this when you want map to reload markers
  }),
  actions: {
    flyTo(coords, meta = {}) {
      this.flyTarget = {
        coords,
        meta,
        ts: Date.now(),
      };
    },
    requestReload() {
      this.reloadTs = Date.now();
    },
  },
});
