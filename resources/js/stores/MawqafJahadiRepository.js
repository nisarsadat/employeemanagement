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

export const useMawqafJahadiRepository = defineStore('MawqafJahadiRepository', {
  state() {
    return {
      router: useRouter(),

      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      MawqafJahadis: reactive([]),
      MawqafJahadi: reactive({}),

      isLoading: false,
      loading: false,
      error: null,

      Search: '',
      rail: false,
      drawerVisible: true,
    };
  },

  actions: {
    async FetchMawqafJahadisData({ page, perPage, search } = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const p = page ?? this.page ?? 1;
        const per = perPage ?? this.itemsPerPage ?? 10;

        const s = search ?? this.Search ?? '';
        this.Search = s;

        const { data: res } = await axios.get(
          `mawqaf-jahadis?page=${p}&perPage=${per}&search=${encodeURIComponent(s || '')}`
        );

        const items = res?.data ?? res ?? [];
        this.MawqafJahadis = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.MawqafJahadis.length;

        return res;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MawqafJahadis = [];
        this.totalItems = 0;
        toast.error(errText(e, 'د موقف جهادی معلومات رانغلو'));
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchMawqafJahadiData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mawqaf-jahadis/${id}`);
        this.MawqafJahadi = data?.data ?? data ?? {};
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MawqafJahadi = {};
        toast.error(errText(e, 'د موقف جهادی معلومات رانغلو'));
        throw e;
      }
    },

    async CreateMawqafJahadi(payload) {
      try {
        setContentType('application/json');
        const res = await axios.post(`/mawqaf-jahadis`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('موقف جهادی بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'موقف جهادی اضافه نشو'));
        throw e;
      }
    },

    async UpdateMawqafJahadi(id, payload) {
      try {
        setContentType('application/json');
        const res = await axios.put(`/mawqaf-jahadis/${id}`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('موقف جهادی بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'موقف جهادی تغییر نشو'));
        throw e;
      }
    },

    async DeleteMawqafJahadi(id) {
      try {
        await axios.delete(`/mawqaf-jahadis/${id}`);
        toast.success('موقف جهادی بریالیتوب سره حذف شو', { autoClose: 1200 });

        await this.FetchMawqafJahadisData({
          page: this.page,
          perPage: this.itemsPerPage,
          search: this.Search,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'موقف جهادی حذف نشو'));
        throw e;
      }
    },
  },
});
