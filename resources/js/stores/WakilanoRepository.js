// src/stores/WakilanoRepository.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { buildListParams, applyPaginatedResponse } from '@/utils/listHelpers';

// ---- sane defaults
axios.defaults.headers.common['Accept'] = 'application/json';

// keep your function name; make it actually work
function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
    return;
  }
  axios.defaults.headers.common['Content-Type'] = type;
}

// small helper for error text
function errText(e, fallback = 'بغضی شیی ') {
  return e?.response?.data?.message || e?.message || (typeof e === 'string' ? e : fallback);
}

export const useWakilanoRepository = defineStore('WakilanoRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      // ===== collections (reactive like your format) =====
      // 
      Wakilanos: reactive([]),
      Wakilano: reactive({}),

      // 
      Universities: reactive([]),
      University: reactive({}),

      // 
      Masjids: reactive([]),
      Masjid: reactive({}),

      // 
      Companies: reactive([]),
      Company: reactive({}),

      // 
      Highways: reactive([]),
      Highway: reactive({}),

      // 
      DoneLostPlaces: reactive([]),
      DoneLostPlace: reactive({}),

      // 
      EconemyProjects: reactive([]),
      EconemyProject: reactive({}),

      // 
      CriminalCapturePlaces: reactive([]),
      CriminalCapturePlace: reactive({}),

      // 
      Pumps: reactive([]),
      Pump: reactive({}),

      // 
      Shoras: reactive([]),
      Shora: reactive({}),

      // 
      DeplomaticPlaces: reactive([]),
      DeplomaticPlace: reactive({}),

      // 
      Checkpoints: reactive([]),
      Checkpoint: reactive({}),
      
      
      // 
      Parks: reactive([]),
      Park: reactive({}),

      // 
      GovernmentPlaces: reactive([]),
      GovernmentPlace: reactive({}),

      // 
      SuperMarkets: reactive([]),
      SuperMarket: reactive({}),

      // 
      Complexes: reactive([]),
      Complex: reactive({}),

      // dropdown support (used by your UI)
      resourcecode: reactive([]),

      // ui state
      isLoading: false,
      loading: false,
      error: null,

      // misc
      Search: '',
      rail: false,
      drawerVisible: true,
    };
  },

  actions: {
    /* ---------------- UI toggles ---------------- */
    toggleRail() {
      this.rail = !this.rail;
    },
    toggleDrawerVisible() {
      this.drawerVisible = !this.drawerVisible;
    },
    setDrawerVisible(v) {
      this.drawerVisible = !!v;
    },

    /* ---------------- Utility ---------------- */
    getCurrentDate() {
      const today = new Date();
      const y = today.getFullYear();
      const m = String(today.getMonth() + 1).padStart(2, '0');
      const d = String(today.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    },

    /* ============ Resource codes for dropdown (code_number, phone_number) ============ */
    async GetResourceCode() {
      this.loading = true;
      try {
        setContentType('application/json');
        // adjust endpoint if yours is different:
        const { data } = await axios.get(`/resources`);
        this.resourcecode = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.resourcecode = [];
      } finally {
        this.loading = false;
      }
    },

    /* ============================ Wakilanos ============================ */
    async FetchWakilanosData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`wakilanos`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Wakilanos');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Wakilanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchWakilanoData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/wakilanos/${id}`);
        this.Wakilano = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Wakilano = {};
      }
    },
    async CreateWakilano(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/wakilanos`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نشو'));
        throw e;
      }
    },
    async UpdateWakilano(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/wakilanos/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/wakilanos/${id}`, data);
        }
        toast.success('ریکارډ بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteWakilano(id) {
      try {
        await axios.delete(`/wakilanos/${id}`);
        toast.success('ریکارډ بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نشو'));
      }
    },

    /* ============================ Universities ============================ */
    async FetchUniversitiesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`universities`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Universities');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Universities = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchUniversityData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/universities/${id}`);
        this.University = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.University = {};
      }
    },
    async CreateUniversity(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/universities`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateUniversity(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/universities/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/universities/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteUniversity(id) {
      try {
        await axios.delete(`/universities/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Masjids ============================ */
    async FetchMasjidsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`masjids`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Masjids');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Masjids = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchMasjidData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/masjids/${id}`);
        this.Masjid = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Masjid = {};
      }
    },
    async CreateMasjid(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/masjids`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateMasjid(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/masjids/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/masjids/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteMasjid(id) {
      try {
        await axios.delete(`/masjids/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Companies ============================ */
    async FetchCompaniesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`companies`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Companies');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Companies = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchCompanyData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/companies/${id}`);
        this.Company = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Company = {};
      }
    },
    async CreateCompany(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/companies`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateCompany(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/companies/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/companies/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteCompany(id) {
      try {
        await axios.delete(`/companies/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Highways ============================ */
    async FetchHighwaysData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`highways`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Highways');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Highways = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchHighwayData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/highways/${id}`);
        this.Highway = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Highway = {};
      }
    },
    async CreateHighway(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/highways`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateHighway(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/highways/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/highways/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteHighway(id) {
      try {
        await axios.delete(`/highways/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ DoneLostPlaces ============================ */
    async FetchDoneLostPlacesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`done-lost-places`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'DoneLostPlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DoneLostPlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchDoneLostPlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/done-lost-places/${id}`);
        this.DoneLostPlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DoneLostPlace = {};
      }
    },
    async CreateDoneLostPlace(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/done-lost-places`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateDoneLostPlace(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/done-lost-places/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/done-lost-places/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteDoneLostPlace(id) {
      try {
        await axios.delete(`/done-lost-places/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ EconemyProjects ============================ */
    async FetchEconemyProjectsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`econemy-projects`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'EconemyProjects');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EconemyProjects = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchEconemyProjectData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/econemy-projects/${id}`);
        this.EconemyProject = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EconemyProject = {};
      }
    },
    async CreateEconemyProject(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/econemy-projects`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateEconemyProject(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/econemy-projects/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/econemy-projects/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteEconemyProject(id) {
      try {
        await axios.delete(`/econemy-projects/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ CriminalCapturePlaces ============================ */
    async FetchCriminalCapturePlacesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`criminal-capture-places`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'CriminalCapturePlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.CriminalCapturePlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchCriminalCapturePlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/criminal-capture-places/${id}`);
        this.CriminalCapturePlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.CriminalCapturePlace = {};
      }
    },
    async CreateCriminalCapturePlace(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/criminal-capture-places`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateCriminalCapturePlace(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/criminal-capture-places/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/criminal-capture-places/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteCriminalCapturePlace(id) {
      try {
        await axios.delete(`/criminal-capture-places/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Pumps ============================ */
    async FetchPumpsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`pumps`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Pumps');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Pumps = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchPumpData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/pumps/${id}`);
        this.Pump = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Pump = {};
      }
    },
    async CreatePump(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/pumps`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdatePump(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/pumps/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/pumps/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeletePump(id) {
      try {
        await axios.delete(`/pumps/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Shoras ============================ */
    async FetchShorasData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`shoras`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Shoras');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Shoras = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchShoraData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/shoras/${id}`);
        this.Shora = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Shora = {};
      }
    },
    async CreateShora(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/shoras`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateShora(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/shoras/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/shoras/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteShora(id) {
      try {
        await axios.delete(`/shoras/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ DeplomaticPlaces ============================ */
    async FetchDeplomaticPlacesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`deplomaticplaces`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'DeplomaticPlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DeplomaticPlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchDeplomaticPlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/deplomaticplaces/${id}`);
        this.DeplomaticPlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DeplomaticPlace = {};
      }
    },
    async CreateDeplomaticPlace(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/deplomaticplaces`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateDeplomaticPlace(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/deplomaticplaces/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/deplomaticplaces/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteDeplomaticPlace(id) {
      try {
        await axios.delete(`/deplomaticplaces/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Checkpoints ============================ */
    async FetchCheckpointsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`checkpoints`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Checkpoints');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Checkpoints = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchCheckpointData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/checkpoints/${id}`);
        this.Checkpoint = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Checkpoint = {};
      }
    },
    async CreateCheckpoint(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/checkpoints`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateCheckpoint(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/checkpoints/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/checkpoints/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteCheckpoint(id) {
      try {
        await axios.delete(`/checkpoints/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Parks ============================ */
    async FetchParksData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`parks`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Parks');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Parks = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchParkData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/parks/${id}`);
        this.Park = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Park = {};
      }
    },
    async CreatePark(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/parks`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdatePark(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/parks/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/parks/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeletePark(id) {
      try {
        await axios.delete(`/parks/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ GovernmentPlaces ============================ */
    async FetchGovernmentPlacesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`governmentplaces`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'GovernmentPlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.GovernmentPlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchGovernmentPlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/governmentplaces/${id}`);
        this.GovernmentPlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.GovernmentPlace = {};
      }
    },
    async CreateGovernmentPlace(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/governmentplaces`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateGovernmentPlace(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/governmentplaces/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/governmentplaces/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteGovernmentPlace(id) {
      try {
        await axios.delete(`/governmentplaces/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ SuperMarkets ============================ */
    async FetchSuperMarketsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`supermarkets`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'SuperMarkets');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.SuperMarkets = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchSuperMarketData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/supermarkets/${id}`);
        this.SuperMarket = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.SuperMarket = {};
      }
    },
    async CreateSuperMarket(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/supermarkets`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateSuperMarket(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/supermarkets/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/supermarkets/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteSuperMarket(id) {
      try {
        await axios.delete(`/supermarkets/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Complexes ============================ */
    async FetchComplexesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');
        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });
        const { data: res } = await axios.get(`complexes`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Complexes');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Complexes = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchComplexData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/complexes/${id}`);
        this.Complex = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Complex = {};
      }
    },
    async CreateComplex(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/complexes`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async UpdateComplex(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/complexes/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/complexes/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async DeleteComplex(id) {
      try {
        await axios.delete(`/complexes/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
  },
});
