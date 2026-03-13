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

export const useResourceActivityTypeRepository = defineStore('ResourceActivityTypeRepository', {
  state() {
    return {
      router: useRouter(),

      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      ResourceActivityTypes: reactive([]),
      ResourceActivityType: reactive({}),

      isLoading: false,
      loading: false,
      error: null,

      Search: '',
      rail: false,
      drawerVisible: true,
    };
  },

  actions: {
    async FetchResourceActivityTypesData({ page, perPage, search } = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        this.page = page ?? this.page ?? 1;
        this.itemsPerPage = perPage ?? this.itemsPerPage ?? 10;

        const s = search ?? this.Search ?? '';
        this.Search = s;

        const { data: res } = await axios.get(
          `resource-activity-types?page=${this.page}&perPage=${
            this.itemsPerPage
          }&search=${encodeURIComponent(s || '')}`
        );

        const items = res?.data ?? res ?? [];
        this.ResourceActivityTypes = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.ResourceActivityTypes.length;

        return res;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.ResourceActivityTypes = [];
        this.totalItems = 0;
        toast.error(errText(e, 'د ریسورس فعالیت ډولونو معلومات رانغلو'));
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchResourceActivityTypeData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/resource-activity-types/${id}`);
        this.ResourceActivityType = data?.data ?? data ?? {};
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.ResourceActivityType = {};
        toast.error(errText(e, 'د ریسورس فعالیت ډول معلومات رانغلو'));
        throw e;
      }
    },

    async CreateResourceActivityType(payload) {
      try {
        setContentType('application/json');
        const res = await axios.post(`/resource-activity-types`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('نوع فعالیت ریسورس بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع فعالیت ریسورس اضافه نشو'));
        throw e;
      }
    },

    async UpdateResourceActivityType(id, payload) {
      try {
        setContentType('application/json');
        const res = await axios.put(`/resource-activity-types/${id}`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('نوع فعالیت ریسورس بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع فعالیت ریسورس تغییر نشو'));
        throw e;
      }
    },

    async DeleteResourceActivityType(id) {
      try {
        await axios.delete(`/resource-activity-types/${id}`);
        toast.success('نوع فعالیت ریسورس بریالیتوب سره حذف شو', { autoClose: 1200 });

        await this.FetchResourceActivityTypesData({
          page: this.page,
          perPage: this.itemsPerPage,
          search: this.Search,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع فعالیت ریسورس حذف نشو'));
        throw e;
      }
    },
  },
});
