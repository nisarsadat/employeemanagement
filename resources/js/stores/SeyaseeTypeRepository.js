import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';

axios.defaults.headers.common['Accept'] = 'application/json';

function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
    return;
  }
  axios.defaults.headers.common['Content-Type'] = type;
}

function errText(e, fallback = 'بغضی شیی ') {
  return e?.response?.data?.message || e?.message || (typeof e === 'string' ? e : fallback);
}

export const useSeyaseeTypeRepository = defineStore('SeyaseeTypeRepository', {
  state() {
    return {
      router: useRouter(),

      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      SeyaseeTypes: reactive([]),
      SeyaseeType: reactive({}),

      isLoading: false,
      loading: false,
      error: null,

      Search: '',
      rail: false,
      drawerVisible: true,
    };
  },

  actions: {
    toggleRail() {
      this.rail = !this.rail;
    },
    toggleDrawerVisible() {
      this.drawerVisible = !this.drawerVisible;
    },
    setDrawerVisible(v) {
      this.drawerVisible = !!v;
    },

    /* ============================ SEYASEE TYPES ============================ */
    async FetchSeyaseeTypesData({ page, perPage, search } = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        this.page = page ?? this.page ?? 1;
        this.itemsPerPage = perPage ?? this.itemsPerPage ?? 10;
        this.Search = search ?? this.Search ?? '';

        const { data: res } = await axios.get(
          `seyasee-types?page=${this.page}&perPage=${this.itemsPerPage}&search=${encodeURIComponent(this.Search || '')}`
        );

        const items = res?.data ?? res ?? [];
        this.SeyaseeTypes = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.SeyaseeTypes.length;

        return res;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.SeyaseeTypes = [];
        this.totalItems = 0;
        toast.error(errText(e, 'د سیاسي نوعونو معلومات رانغلو'));
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchSeyaseeTypeData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/seyasee-types/${id}`);
        this.SeyaseeType = data?.data ?? data ?? {};
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.SeyaseeType = {};
        toast.error(errText(e, 'د سیاسي نوع معلومات رانغلو'));
        throw e;
      }
    },

    async CreateSeyaseeType(payload) {
      try {
        setContentType('application/json');
        const res = await axios.post(`/seyasee-types`, payload);
        const saved = res.data?.data ?? res.data;

        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        throw e;
      }
    },

    async UpdateSeyaseeType(id, payload) {
      try {
        setContentType('application/json');
        const res = await axios.put(`/seyasee-types/${id}`, payload);
        const saved = res.data?.data ?? res.data;

        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع سیاسي تغییر نشو'));
        throw e;
      }
    },

    async DeleteSeyaseeType(id) {
      try {
        await axios.delete(`/seyasee-types/${id}`);

        await this.FetchSeyaseeTypesData({
          page: this.page,
          perPage: this.itemsPerPage,
          search: this.Search,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع سیاسي حذف نشو'));
        throw e;
      }
    },
  },
});