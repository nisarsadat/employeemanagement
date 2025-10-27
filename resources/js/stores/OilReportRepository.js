// src/stores/OilReportRepository.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useOilReportRepository = defineStore('OilReportRepository', {
  state: () => ({
    period: 'daily',
    labels: [],
    series: { income: [], expense: [], net: [] },
    totals: { income: 0, expense: 0, net: 0 },
    range: { from: null, to: null },
    loading: false,
  }),
  actions: {
    async fetch(period = this.period, extra = {}) {
      this.loading = true;
      const params = new URLSearchParams({ period, ...extra }).toString();
      const { data } = await axios.get(`/oil-reports?${params}`);
      this.period  = period;
      this.labels  = data.labels || [];
      this.series  = data.series || { income:[], expense:[], net:[] };
      this.totals  = data.totals || { income:0, expense:0, net:0 };
      this.range   = data.range  || { from:null, to:null };
      this.loading = false;
    }
  }
});
