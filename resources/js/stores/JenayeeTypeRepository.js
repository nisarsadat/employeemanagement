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

export const useJenayeeTypeRepository = defineStore('JenayeeTypeRepository', {
  state() {
    return {
      router: useRouter(),

      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      JenayeeTypes: reactive([]),
      JenayeeType: reactive({}),

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

    /* ============================ JENAYEE TYPES ============================ */
    async FetchJenayeeTypesData({ page, perPage, search } = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const p = page ?? this.page ?? 1;
        const per = perPage ?? this.itemsPerPage ?? 10;

        const s = search ?? this.Search ?? '';
        this.Search = s;

        const { data: res } = await axios.get(
          `jenayee-types?page=${p}&perPage=${per}&search=${encodeURIComponent(s || '')}`
        );

        const items = res?.data ?? res ?? [];
        this.JenayeeTypes = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.JenayeeTypes.length;

        return res;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.JenayeeTypes = [];
        this.totalItems = 0;
        toast.error(errText(e, 'د جنایي نوعونو معلومات رانغلو'));
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchJenayeeTypeData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/jenayee-types/${id}`);
        this.JenayeeType = data?.data ?? data ?? {};
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.JenayeeType = {};
        toast.error(errText(e, 'د جنایي نوع معلومات رانغلو'));
        throw e;
      }
    },

    async CreateJenayeeType(payload) {
      try {
        setContentType('application/json');
        const res = await axios.post(`/jenayee-types`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('نوع جنایی بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع جنایی اضافه نشو'));
        throw e;
      }
    },

    async UpdateJenayeeType(id, payload) {
      try {
        setContentType('application/json');
        const res = await axios.put(`/jenayee-types/${id}`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('نوع جنایی بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع جنایی تغییر نشو'));
        throw e;
      }
    },

    async DeleteJenayeeType(id) {
      try {
        await axios.delete(`/jenayee-types/${id}`);
        toast.success('نوع جنایی بریالیتوب سره حذف شو', { autoClose: 1200 });

        await this.FetchJenayeeTypesData({ page: this.page, perPage: this.itemsPerPage, search: this.Search });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع جنایی حذف نشو'));
        throw e;
      }
    },
  },
});
