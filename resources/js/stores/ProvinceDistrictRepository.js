// src/stores/ProvinceDistrictRepository.js
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

export const useProvinceDistrictRepository = defineStore('ProvinceDistrictRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      // collections
      Provinces: reactive([]),
      Province: reactive({}),

      Districts: reactive([]),
      District: reactive({}),

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

    /* ============================ PROVINCES ============================ */
    async FetchProvincesData({ page, itemsPerPage } = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const p = page ?? this.page ?? 1;
        const per = itemsPerPage ?? this.itemsPerPage ?? 10;

        const { data: res } = await axios.get(
          `provinces?page=${p}&perPage=${per}&search=${encodeURIComponent(this.Search || '')}`
        );

        const items = res?.data ?? res ?? [];
        this.Provinces = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Provinces.length;

        return res;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Provinces = [];
        this.totalItems = 0;
        toast.error(errText(e, 'د ولایتونو معلومات رانغلو'));
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchProvinceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/provinces/${id}`);
        this.Province = data?.data ?? data ?? {};
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Province = {};
        toast.error(errText(e, 'د ولایت معلومات رانغلو'));
        throw e;
      }
    },

    async CreateProvince(payload) {
      try {
        setContentType('application/json');
        const res = await axios.post(`/provinces`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('ولایت بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ولایت اضافه نشو'));
        throw e;
      }
    },

    async UpdateProvince(id, payload) {
      try {
        setContentType('application/json');
        const res = await axios.put(`/provinces/${id}`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('ولایت بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ولایت تغییر نشو'));
        throw e;
      }
    },

    async DeleteProvince(id) {
      try {
        await axios.delete(`/provinces/${id}`);
        toast.success('ولایت بریالیتوب سره حذف شو', { autoClose: 1200 });

        // refresh list
        await this.FetchProvincesData({ page: this.page, itemsPerPage: this.itemsPerPage });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ولایت حذف نشو'));
        throw e;
      }
    },

    /* ============================ DISTRICTS ============================ */
    async FetchDistrictsData({ page, itemsPerPage, province_id = null } = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const p = page ?? this.page ?? 1;
        const per = itemsPerPage ?? this.itemsPerPage ?? 10;

        const provinceQuery =
          province_id !== null && province_id !== undefined && province_id !== ''
            ? `&province_id=${encodeURIComponent(province_id)}`
            : '';

        const { data: res } = await axios.get(
          `districts?page=${p}&perPage=${per}&search=${encodeURIComponent(this.Search || '')}${provinceQuery}`
        );

        const items = res?.data ?? res ?? [];
        this.Districts = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Districts.length;

        return res;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Districts = [];
        this.totalItems = 0;
        toast.error(errText(e, 'د ولسوالیو معلومات رانغلو'));
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchDistrictData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/districts/${id}`);
        this.District = data?.data ?? data ?? {};
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.District = {};
        toast.error(errText(e, 'د ولسوالۍ معلومات رانغلو'));
        throw e;
      }
    },

    async CreateDistrict(payload) {
      try {
        setContentType('application/json');
        const res = await axios.post(`/districts`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('ولسوالی بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ولسوالی اضافه نشو'));
        throw e;
      }
    },

    async UpdateDistrict(id, payload) {
      try {
        setContentType('application/json');
        const res = await axios.put(`/districts/${id}`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('ولسوالی بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ولسوالی تغییر نشو'));
        throw e;
      }
    },

    async DeleteDistrict(id) {
      try {
        await axios.delete(`/districts/${id}`);
        toast.success('ولسوالی بریالیتوب سره حذف شو', { autoClose: 1200 });

        // refresh list
        await this.FetchDistrictsData({ page: this.page, itemsPerPage: this.itemsPerPage });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ولسوالی حذف نشو'));
        throw e;
      }
    },
  },
});
