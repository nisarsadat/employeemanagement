// src/stores/FasadRepository.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';

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

export const useMawadRepository = defineStore('MawadRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      // collections
      Mawads: reactive([]),
      Mawad: reactive({}),

      LostMujremeens: reactive([]),
      LostMujremeen: reactive({}),

      OldSoldiersHomes: reactive([]),
      OldSoldiersHome: reactive({}),

      Robbers: reactive([]),
      Robber: reactive({}),

      NonResponsablePeoples: reactive([]),
      NonResponsablePeople: reactive({}),

      MashkokHomes: reactive([]),
      MashkokHome: reactive({}),

      DamagePLaces: reactive([]),
      DamagePlace: reactive({}),

      DamageRasanPlaces: reactive([]),
      DamageRasanPlace: reactive({}),

      MashkokShops: reactive([]),
      MashkokShop: reactive({}),

      Luchakanos: reactive([]),
      Luchakano: reactive({}),

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

    /* ============================ Mawads ============================ */
    async FetchMawadsData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `mawads?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Mawads = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Mawads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Fasads = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMawadData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mawads/${id}`);
        this.Mawad = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Mawad = {};
      }
    },

    async CreateMawad(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mawads`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ فساد بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ فساد اضافه نشو'));
        throw e;
      }
    },

    async UpdateMawad(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mawads/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mawads/${id}`, data);
        }
        toast.success('ریکارډ فساد بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ فساد تغییر نشو'));
        throw e;
      }
    },

    async DeleteMawad(id) {
      try {
        await axios.delete(`/mawads/${id}`);
        toast.success('ریکارډ فساد بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchMawadsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ فساد حذف نشو'));
      }
    },
    /* ============================ Lost Mujremeens ============================ */
    async FetchLostMujremeensData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `lost-mujremeens?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.LostMujremeens = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Fasads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.LostMujremeens = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchLostMujremeenData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/lost-mujremeens/${id}`);
        this.LostMujremeen = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.LostMujremeen = {};
      }
    },

    async CreateLostMujremeen(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/lost-mujremeens`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateLostMujremeen(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/lost-mujremeens/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/lost-mujremeens/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteLostMujremeen(id) {
      try {
        await axios.delete(`/lost-mujremeens/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Old Soldiers Homes ============================ */
    async FetchOldSoldiersHomesData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `old-soldiers-homes?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.OldSoldiersHomes = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Fasads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.OldSoldiersHomes = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchOldSoldiersHomeData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/old-soldiers-homes/${id}`);
        this.OldSoldiersHome = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.OldSoldiersHome = {};
      }
    },

    async CreateOldSoldiersHome(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/old-soldiers-homes/`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateOldSoldiersHome(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/old-soldiers-homes/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/old-soldiers-homes/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteOldSoldiersHome(id) {
      try {
        await axios.delete(`/old-soldiers-homes/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchHeistsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Robbers ============================ */
    async FetchRobbersData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `robbers?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Robbers = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Fasads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Robbers = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchRobberData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/robbers/${id}`);
        this.Robber = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Captive = {};
      }
    },

    async CreateRobber(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/robbers`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateRobber(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/robbers/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/robbers/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteRobbers(id) {
      try {
        await axios.delete(`/robbers/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchRobbersData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ NonResponsablePeoples ============================ */
    async FetchNonResponsablePeoplesData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `non-responsable-people?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.NonResponsablePeoples = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Fasads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.NonResponsablePeoples = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchNonResponsablePeopleData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/non-responsable-people/${id}`);
        this.NonResponsablePeople = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.NonResponsablePeople = {};
      }
    },

    async CreateNonResponsablePeople(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/non-responsable-people`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateNonResponsablePeoples(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/non-responsable-people/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/non-responsable-people/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteNonResponsablePeople(id) {
      try {
        await axios.delete(`/non-responsable-people/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchNonResponsablePeoplesData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Mashkok Homes ============================ */
    async FetchMashkokHomesData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `mashkok-homes?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.MashkokHomes = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Fasads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokHomes = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMashkokHomeData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mashkok-homes/${id}`);
        this.MashkokHome = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokHome = {};
      }
    },

    async CreateMashkokHome(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mashkok-homes`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateMashkokHome(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mashkok-homes/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mashkok-homes/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteMashkokHome(id) {
      try {
        await axios.delete(`/mashkok-homes/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchEntryExitsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ DamagePlacses ============================ */
    async FetchDamagePlacesData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `damage-places?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.DamagePlaces = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Fasads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DamagePlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchDamagePlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/damage-places/${id}`);
        this.DamagePlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DamagePlace = {};
      }
    },

    async CreateDamagePlace(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/damage-places`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateDamagePlacse(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/damage-places/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/damage-places/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteDamagePlacse(id) {
      try {
        await axios.delete(`/damage-places/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchDamagePlacesData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Damage Rasan PLaces ============================ */
    async FetchDamageRasanPlacesData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `damage-rasan-places?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.DamageRasanPlaces = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Fasads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DamageRasanPlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchDamageRasanPlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/damage-rasan-places/${id}`);
        this.DamageRasanPlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DamageRasanPlace = {};
      }
    },

    async CreateDamageRasanPlaces(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/damage-rasan-places`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateDamageRasanPlaces(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/damage-rasan-places/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/damage-rasan-places/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteDamageRasanPlaces(id) {
      try {
        await axios.delete(`/damage-rasan-places/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Mashkok Shops ============================ */
    async FetchMashkokShopsData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `mashkok-shops?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.MashkokShops = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Fasads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokShops = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMashkokShopData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mashkok-shops/${id}`);
        this.MashkokShop = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokShop = {};
      }
    },

    async CreateMashkokShop(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mashkok-shops`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateMashkokShop(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mashkok-shops/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mashkok-shops/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteMashkokShop(id) {
      try {
        await axios.delete(`/mashkok-shops/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Luchakano ============================ */
    async FetchLuchakanosData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `luchakanos?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Luchakanos = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Fasads.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Luchakanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchLuchakanoData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/luchakanos/${id}`);
        this.Luchakano = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Luchakano = {};
      }
    },

    async CreateLuchakano(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/luchakanos`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateLuchakano(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/luchakanos/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/luchakanos/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteLuchakano(id) {
      try {
        await axios.delete(`/luchakanos/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchLuchakanosData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
  },
});
