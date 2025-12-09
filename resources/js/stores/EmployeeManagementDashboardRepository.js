// src/stores/EmployeeManagementDashboardRepository.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';

// === ZONE <-> HAWZA mapping (same pattern as DatabaseRepository) ===
const ZONE_HAWZA_MAP = {
  'لومړی زون': ['حوزه ۳', 'حوزه ۷', 'حوزه ۶', 'حوزه ۸', 'حوزه ۱۸'],
  'دوهم زون': ['حوزه ۸', 'بګرامی ولسوالی', 'حوزه ۱۲', 'حوزه ۱۹', 'حوزه ۲۱'],
  'دریم زون': ['حوزه ۱', 'حوزه ۲', 'حوزه ۱۶', 'حوزه ۹', 'حوزه ۱۰'],
  'څلورم زون': ['حوزه ۵', 'حوزه ۱۱', 'حوزه ۱۵', 'حوزه ۴', 'حوزه ۱۷'],
};

// Build reverse map: Hawza -> Zone
const HAWZA_ZONE_MAP = Object.entries(ZONE_HAWZA_MAP).reduce((acc, [zone, list]) => {
  (list || []).forEach((hz) => {
    acc[hz] = zone;
  });
  return acc;
}, {});

// Accept JSON by default
axios.defaults.headers.common['Accept'] = 'application/json';

function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
  } else {
    axios.defaults.headers.common['Content-Type'] = type;
  }
}

export const useEmployeeManagementDashboardRepository = defineStore(
  'EmployeeManagementDashboardRepository',
  {
    state: () => ({
      router: useRouter(),

      // ---- loading / error ----
      isLoadingCards: false,
      isLoadingTimeseries: false,
      isLoadingFormats: false,
      error: null,

      // ---- cards data (totals) ----
      cards: reactive({
        total_employees: 0,
        total_resources: 0,
        total_resources_active: 0,
        total_resources_inactive: 0,
        total_informations: 0,
        total_events: 0,
        total_captive_deliveries: 0,

        // money (placeholder, backend returns 0 until you wire it)
        total_money: 0,
        total_money_expense: 0,
        total_money_income: 0,
        total_money_balance: 0,

        // oil (placeholder, backend returns 0 until you wire it)
        total_oil: 0,
        total_oil_expense: 0,
        total_oil_income: 0,
        total_oil_balance: 0,
      }),

      // ---- timeseries data for graphs ----
      timeseriesFilter: reactive({
        from: null, // 'YYYY-MM-DD'
        to: null, // 'YYYY-MM-DD'
      }),
      timeseries: reactive({
        from: null,
        to: null,
        resources: [],
        informations: [],
        events: [],
      }),

      // ---- 56 formats by hawza ----
      formatsLoading: false,
      hawzaRows: reactive([]), // [{ hawza, fasads, ..., wakilanos, total }]
      totalsPerFormat: reactive({}), // { fasads: 12, heists: 5, ... }
      formatKeys: reactive([]), // ['fasads', 'saeen_fasads', ...]
    }),

    actions: {
      /* ========= Zone/Hawza helpers ========= */
      getZoneOptions() {
        return Object.keys(ZONE_HAWZA_MAP);
      },
      getAllHawzaOptions() {
        return Object.values(ZONE_HAWZA_MAP).flat();
      },
      getHawzaOptionsForZone(zone) {
        if (!zone) return this.getAllHawzaOptions();
        return ZONE_HAWZA_MAP[zone] ?? [];
      },
      getZoneForHawza(hawza) {
        if (!hawza) return undefined;
        return HAWZA_ZONE_MAP[hawza];
      },
      hawzaIsValidForZone(zone, hawza) {
        if (!zone || !hawza) return true;
        return (ZONE_HAWZA_MAP[zone] ?? []).includes(hawza);
      },
      normalizeAfterZoneChange(currentZone, currentHawza) {
        if (!currentZone) return { zone: '', hawza: '' };
        const allowed = new Set(ZONE_HAWZA_MAP[currentZone] || []);
        return {
          zone: currentZone,
          hawza: allowed.has(currentHawza || '') ? currentHawza : '',
        };
      },
      normalizeAfterHawzaChange(currentHawza, currentZone) {
        if (!currentHawza) return { zone: currentZone || '', hawza: '' };
        const z = HAWZA_ZONE_MAP[currentHawza];
        return { zone: z || currentZone || '', hawza: currentHawwa };
      },

      /* ========= Helpers ========= */
      getCurrentDate() {
        const d = new Date();
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
      },

      /* ========= CARDS (totals) ========= */
      async FetchDashboardCards() {
        this.isLoadingCards = true;
        this.error = null;
        try {
          setContentType('application/json');
          const { data } = await axios.get('/dashboard/employee-management/cards');

          // normalize into cards reactive object
          const src = data || {};
          this.cards.total_employees = src.total_employees ?? 0;
          this.cards.total_resources = src.total_resources ?? 0;
          this.cards.total_resources_active = src.total_resources_active ?? 0;
          this.cards.total_resources_inactive = src.total_resources_inactive ?? 0;
          this.cards.total_informations = src.total_informations ?? 0;
          this.cards.total_events = src.total_events ?? 0;
          this.cards.total_captive_deliveries = src.total_captive_deliveries ?? 0;

          this.cards.total_money = src.total_money ?? 0;
          this.cards.total_money_expense = src.total_money_expense ?? 0;
          this.cards.total_money_income = src.total_money_income ?? 0;
          this.cards.total_money_balance = src.total_money_balance ?? 0;

          this.cards.total_oil = src.total_oil ?? 0;
          this.cards.total_oil_expense = src.total_oil_expense ?? 0;
          this.cards.total_oil_income = src.total_oil_income ?? 0;
          this.cards.total_oil_balance = src.total_oil_balance ?? 0;
        } catch (e) {
          console.error(e);
          this.error = e;
          toast.error('د ډشبورډ معلوماتو کارتونو اخیستل ناکامه شول');
        } finally {
          this.isLoadingCards = false;
        }
      },

      /* ========= TIMESERIES (graphs) ========= */
      async FetchDashboardTimeseries(params = {}) {
        this.isLoadingTimeseries = true;
        this.error = null;
        try {
          setContentType('application/json');

          const q = new URLSearchParams();
          const from = params.from ?? this.timeseriesFilter.from;
          const to = params.to ?? this.timeseriesFilter.to;

          if (from) q.set('from', from);
          if (to) q.set('to', to);

          const qs = q.toString();
          const url = qs
            ? `/dashboard/employee-management/timeseries?${qs}`
            : `/dashboard/employee-management/timeseries`;

          const { data } = await axios.get(url);

          this.timeseries.from = data.from ?? null;
          this.timeseries.to = data.to ?? null;
          this.timeseries.resources = data.resources ?? [];
          this.timeseries.informations = data.informations ?? [];
          this.timeseries.events = data.events ?? [];
        } catch (e) {
          console.error(e);
          this.error = e;
          this.timeseries.resources = [];
          this.timeseries.informations = [];
          this.timeseries.events = [];
          toast.error('د ډشبورډ د ګراف معلومات اخیستل ناکامه شول');
        } finally {
          this.isLoadingTimeseries = false;
        }
      },

      /* ========= 56 FORMATS by HAWZA ========= */
      async FetchFormatsByHawza() {
        this.isLoadingFormats = true;
        this.error = null;
        try {
          setContentType('application/json');
          const { data } = await axios.get('/dashboard/employee-management/formats-by-hawza');

          const hawzas = data?.hawzas ?? [];
          const totalsPerFormat = data?.totals_per_format ?? {};
          const formatKeys = data?.format_keys ?? [];

          // reset arrays/objects
          this.hawzaRows.splice(0, this.hawzaRows.length, ...hawzas);
          this.formatKeys.splice(0, this.formatKeys.length, ...formatKeys);

          // clear and refill totalsPerFormat
          Object.keys(this.totalsPerFormat).forEach((k) => delete this.totalsPerFormat[k]);
          Object.entries(totalsPerFormat).forEach(([k, v]) => {
            this.totalsPerFormat[k] = v;
          });
        } catch (e) {
          console.error(e);
          this.error = e;
          this.hawzaRows = [];
          this.formatKeys = [];
          Object.keys(this.totalsPerFormat).forEach((k) => delete this.totalsPerFormat[k]);
          toast.error('د ۵۶ فارمت د حوزې له مخې معلومات اخیستل ناکامه شول');
        } finally {
          this.isLoadingFormats = false;
        }
      },

      /* ========= SMALL HELPERS FOR CHARTS ========= */
      getResourceSeriesForChart() {
        // return arrays: labels[], data[]
        const labels = this.timeseries.resources.map((r) => r.date);
        const data = this.timeseries.resources.map((r) => r.count);
        return { labels, data };
      },
      getInformationSeriesForChart() {
        const labels = this.timeseries.informations.map((r) => r.date);
        const data = this.timeseries.informations.map((r) => r.count);
        return { labels, data };
      },
      getEventSeriesForChart() {
        const labels = this.timeseries.events.map((r) => r.date);
        const data = this.timeseries.events.map((r) => r.count);
        return { labels, data };
      },
    },
  }
);
