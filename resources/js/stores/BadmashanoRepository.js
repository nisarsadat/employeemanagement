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

export const useBadmashanoRepository = defineStore('BadmashanoRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      // collections
      Badmashanos: reactive([]),
      Badmashano: reactive({}),

      Sarayanos: reactive([]),
      Sarayano: reactive({}),

      Hotels: reactive([]),
      Hotel: reactive({}),

      Hostels: reactive([]),
      Hostel: reactive({}),

      Tujarans: reactive([]),
      Tujaran: reactive({}),

      Mujahedinos: reactive([]),
      Mujahedino: reactive({}),

      EmaratOffices: reactive([]),
      EmaratOffice: reactive({}),

      OldJahadis: reactive([]),
      OldJahadi: reactive({}),

      MashkokPeoples: reactive([]),
      MashkokPeople: reactive({}),

      Madrasas: reactive([]),
      Madrasa: reactive([]),

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

    /* ============================ Badmashanos ============================ */
    async FetchBadmashanosData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `badmashanos?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Badmashanos = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Badmashanos.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Badmashanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchBadmashanoData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/badmashanos/${id}`);
        this.Badmashanos = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Badmashanos = {};
      }
    },

    async CreateBadmashano(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/badmashanos`, formData);
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

    async UpdateBadmashano(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/badmashanos/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/badmashanos/${id}`, data);
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

    async DeleteBadmashano(id) {
      try {
        await axios.delete(`/badmashanos/${id}`);
        toast.success('ریکارډ فساد بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ فساد حذف نشو'));
      }
    },
    /* ============================ Sarayanos ============================ */
    async FetchSarayanosData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `sarayanos?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Sarayanos = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Sarayanos.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Sarayanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchSarayanoData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/sarayanos/${id}`);
        this.Sarayano = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Sarayano = {};
      }
    },

    async CreateSarayano(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/sarayanos`, formData);
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

    async UpdateSarayano(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/sarayanos/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/sarayanos/${id}`, data);
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

    async DeleteSarayano(id) {
      try {
        await axios.delete(`/sarayanos/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Hotels ============================ */
    async FetchHotelsData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `hotels?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Hotels = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Hotels.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hotels = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchHotelData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/hotels/${id}`);
        this.Hotels = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hotels = {};
      }
    },

    async CreateHotel(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/hotels`, formData);
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

    async UpdateHotel(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/hotels/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/hotels/${id}`, data);
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

    async DeleteHotel(id) {
      try {
        await axios.delete(`/hotels/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Hostels ============================ */
    async FetchHostelsData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `hostels?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Hostels = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Hostels.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hostels = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchHostelData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/hostels/${id}`);
        this.Hostel = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hostel = {};
      }
    },

    async CreateHostel(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/hostels`, formData);
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

    async UpdateHostel(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/hostels/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/hostels/${id}`, data);
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

    async DeleteHostel(id) {
      try {
        await axios.delete(`/hostels/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Tujarans ============================ */
    async FetchTujaransData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `tujarans?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Tujarans = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Tujarans.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Tujarans = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchTujaranData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/tujarans/${id}`);
        this.Tujaran = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Tujaran = {};
      }
    },

    async CreateTujaran(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/tujarans`, formData);
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

    async UpdateTujaran(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/tujarans/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/tujarans/${id}`, data);
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

    async DeleteTujaran(id) {
      try {
        await axios.delete(`/tujarans/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Mujahedinos ============================ */
    async FetchMujahedinosData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `mujahedinos?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Mujahedinos = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Mujahedinos.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Mujahedinos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMujahedino(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mujahedinos/${id}`);
        this.Mujahedino = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Mujahedino = {};
      }
    },

    async CreateMujahedino(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mujahedinos`, formData);
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

    async UpdateMujahedino(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mujahedinos/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mujahedinos/${id}`, data);
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

    async DeleteMujahedino(id) {
      try {
        await axios.delete(`/mujahedinos/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ EmaratOffices ============================ */
    async FetchEmaratOfficesData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `emarat-offices?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.EmaratOffices = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.EmaratOffices.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EmaratOffices = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchEmaratOfficeData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/emarat-offices/${id}`);
        this.EmaratOffice = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EmaratOffice = {};
      }
    },

    async CreateEmaratOffice(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/emarat-offices`, formData);
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

    async UpdateEmaratOffice(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/emarat-offices/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/emarat-offices/${id}`, data);
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

    async DeleteEmaratOffice(id) {
      try {
        await axios.delete(`/emarat-offices/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ OldJahadis ============================ */
    async FetchOldJahadisData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `old-jahadis?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.OldJahadis = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.OldJahadis.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.OldJahadis = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchOldJahadiData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/old-jahadis/${id}`);
        this.OldJahadi = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.OldJahadi = {};
      }
    },

    async CreateOldJahadi(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/old-jahadis`, formData);
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

    async UpdateOldJahadi(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/old-jahadis/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/old-jahadis/${id}`, data);
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

    async DeleteOldJahadi(id) {
      try {
        await axios.delete(`/old-jahadis/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ MashkokPeoples ============================ */
    async FetchMashkokPeoplesData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `mashkok-people?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.MashkokPeoples = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.MashkokPeoples.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokPeoples = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMashkokPeopleData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mashkok-people/${id}`);
        this.MashkokPeople = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokPeople = {};
      }
    },

    async CreateMashkokPeople(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mashkok-people`, formData);
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

    async UpdateMashkokPeople(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mashkok-people/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mashkok-people/${id}`, data);
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

    async DeleteMashkokPeople(id) {
      try {
        await axios.delete(`/mashkok-people/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    /* ============================ Madrasas ============================ */
    async FetchMadrasasData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `madrasas?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Madrasas = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Madrasas.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Madrasas = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMadrasaData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/madrasas/${id}`);
        this.Madrasas = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Madrasas = {};
      }
    },

    async CreateMadrasa(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/madrasas`, formData);
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

    async UpdateMadrasa(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/madrasas/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/madrasas/${id}`, data);
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

    async DeleteMadrasa(id) {
      try {
        await axios.delete(`/madrasas/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
  },
});
