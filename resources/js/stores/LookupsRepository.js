import { defineStore } from 'pinia';
import { reactive } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';

// ---- sane defaults
axios.defaults.headers.common['Accept'] = 'application/json';

function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
    return;
  }
  axios.defaults.headers.common['Content-Type'] = type;
}

function errText(e, fallback = 'Lookup معلومات ترلاسه نه شول') {
  return e?.response?.data?.message || e?.message || fallback;
}

export const useLookupsRepository = defineStore('LookupsRepository', {
  state() {
    return {
      Provinces: reactive([]),
      Districts: reactive([]),
      Zones: reactive([]),
      Hawzas: reactive([]),

      EventTypes: reactive([]),
      EventTypeOptions: reactive([]),
      EventSpecificTypes: reactive([]),

      JenayeeTypes: reactive([]),
      SeyaseeTypes: reactive([]),
      Organs: reactive([]),
      JobTypes: reactive([]),
      ResourceActivityTypes: reactive([]),
      MawqafJahadis: reactive([]),
      FormatZones: reactive([]),
      DocsTypes: reactive([]),
      Marjas: reactive([]),
      loading: false,
      isLoading: false,
      error: null,
    };
  },

  actions: {
    /* ================= Provinces ================= */
    async GetProvinces() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/provinces');
        this.Provinces = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Provinces = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    /* ================= Districts ================= */
    async GetDistricts() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/districts');
        this.Districts = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Districts = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    /* ================= Zones ================= */
    async GetZones() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/zones');
        this.Zones = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Zones = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    /* ================= Hawzas ================= */
    async GetHawzas() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/hawzas');
        this.Hawzas = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hawzas = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    /* ================= Event Types ================= */
    async GetEventTypes() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/event-types');
        this.EventTypes = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EventTypes = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    async GetEventTypeOptions() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/event-types/options');
        this.EventTypeOptions = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EventTypeOptions = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    async GetEventSpecificTypes() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/event-specific-types');
        this.EventSpecificTypes = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EventSpecificTypes = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    /* ================= Other Lookups ================= */
    async GetJenayeeTypes() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/jenayee-types');
        this.JenayeeTypes = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.JenayeeTypes = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    async GetSeyaseeTypes() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/seyasee-types');
        this.SeyaseeTypes = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.SeyaseeTypes = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    async GetOrgans() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/organs');
        this.Organs = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Organs = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },
    async GetFormatZones() {
      this.loading = true;
      try {
        const { data } = await axios.get('/format-zones');
        this.FormatZones = data?.data ?? data ?? [];
      } finally {
        this.loading = false;
      }
    },

    async GetJobTypes() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/job-types');
        this.JobTypes = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.JobTypes = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    async GetResourceActivityTypes() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/resource-activity-types');
        this.ResourceActivityTypes = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.ResourceActivityTypes = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },

    async GetMawqafJahadis() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get('/mawqaf-jahadis');
        this.MawqafJahadis = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MawqafJahadis = [];
        toast.error(errText(e));
      } finally {
        this.loading = false;
      }
    },
    async GetDocsTypes() {
      this.loading = true;
      try {
        const { data } = await axios.get('/docs-types?perPage=10000');
        this.DocsTypes = data?.data ?? [];
      } finally {
        this.loading = false;
      }
    },
    async GetMarjas() {
      this.loading = true;
      try {
        const { data } = await axios.get('/marjas?perPage=10000');
        this.Marjas = data?.data ?? [];
      } finally {
        this.loading = false;
      }
    },
  },
});
