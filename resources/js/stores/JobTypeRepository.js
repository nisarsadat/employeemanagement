import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { buildListParams, applyPaginatedResponse } from '@/utils/listHelpers';

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

export const useJobTypeRepository = defineStore('JobTypeRepository', {
  state() {
    return {
      router: useRouter(),

      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      JobTypes: reactive([]),
      JobType: reactive({}),

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

    async FetchJobTypesData() {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, {
          includeSearch: true,
          includePagination: true,
        });

        const res = await axios.get('/job-types', {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'JobTypes');
        return res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.JobTypes = [];
        this.totalItems = 0;
        toast.error(errText(e, 'د وظیفې ډولونو معلومات رانغلو'));
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchJobTypeData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/job-types/${id}`);
        this.JobType = data?.data ?? data ?? {};
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.JobType = {};
        toast.error(errText(e, 'د وظیفې ډول معلومات رانغلو'));
        throw e;
      }
    },

    async CreateJobType(payload) {
      try {
        setContentType('application/json');
        const res = await axios.post(`/job-types`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('نوع وظیفه بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع وظیفه اضافه نشو'));
        throw e;
      }
    },

    async UpdateJobType(id, payload) {
      try {
        setContentType('application/json');
        const res = await axios.put(`/job-types/${id}`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('نوع وظیفه بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع وظیفه تغییر نشو'));
        throw e;
      }
    },

    async DeleteJobType(id) {
      try {
        await axios.delete(`/job-types/${id}`);
        toast.success('نوع وظیفه بریالیتوب سره حذف شو', { autoClose: 1200 });

        await this.FetchJobTypesData();
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'نوع وظیفه حذف نشو'));
        throw e;
      }
    },
  },
});