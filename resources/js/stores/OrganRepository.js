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

export const useOrganRepository = defineStore('OrganRepository', {
  state() {
    return {
      router: useRouter(),

      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      Organs: reactive([]),
      Organ: reactive({}),

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

    async FetchOrgansData({ page, perPage, search } = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const p = page ?? this.page ?? 1;
        const per = perPage ?? this.itemsPerPage ?? 10;

        const s = search ?? this.Search ?? '';
        this.Search = s;

        const { data: res } = await axios.get(
          `organs?page=${p}&perPage=${per}&search=${encodeURIComponent(s || '')}`
        );

        const items = res?.data ?? res ?? [];
        this.Organs = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Organs.length;

        return res;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Organs = [];
        this.totalItems = 0;
        toast.error(errText(e, 'د ارګانونو معلومات رانغلو'));
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchOrganData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/organs/${id}`);
        this.Organ = data?.data ?? data ?? {};
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Organ = {};
        toast.error(errText(e, 'د ارګان معلومات رانغلو'));
        throw e;
      }
    },

    async CreateOrgan(payload) {
      try {
        setContentType('application/json');
        const res = await axios.post(`/organs`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('ارگان بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ارگان اضافه نشو'));
        throw e;
      }
    },

    async UpdateOrgan(id, payload) {
      try {
        setContentType('application/json');
        const res = await axios.put(`/organs/${id}`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('ارگان بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ارگان تغییر نشو'));
        throw e;
      }
    },

    async DeleteOrgan(id) {
      try {
        await axios.delete(`/organs/${id}`);
        toast.success('ارگان بریالیتوب سره حذف شو', { autoClose: 1200 });

        await this.FetchOrgansData({ page: this.page, perPage: this.itemsPerPage, search: this.Search });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ارگان حذف نشو'));
        throw e;
      }
    },
  },
});
