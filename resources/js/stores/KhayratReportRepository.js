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

export const useKhayratReportRepository = defineStore('KhayratReportRepository', {
  state() {
    return {
      router: useRouter(),

      page: 1,
      itemsPerPage: 10,
      perPage: 10,
      totalItems: 0,

      KhayratReports: [],
      KhayratReport: {},

      loading: false,
      error: null,
      Search: '',

      lookups: {
        loadingResources: false,
        loadingEmployees: false,
        resources: [],
        employees: [],
      },
    };
  },

  actions: {
    /* ==========================
     * CRUD
     * ========================== */

    async FetchKhayratReportsData({ page, perPage, search } = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        if (page !== undefined) this.page = page;
        if (perPage !== undefined) {
          this.perPage = perPage;
          this.itemsPerPage = perPage;
        }

        const s = search ?? this.Search ?? '';
        this.Search = s;

        const queryParams = buildListParams(this, {
          includeSearch: true,
          includePagination: true,
        });

        const { data: res } = await axios.get(`/khayrat-reports`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, { data: res.data }, 'KhayratReports');
        this.perPage = this.itemsPerPage;

        return res;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.KhayratReports = [];
        this.totalItems = 0;
        toast.error(errText(e, 'د راپورونو معلومات رانغلو'));
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchKhayratReportData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/khayrat-reports/${id}`);
        this.KhayratReport = data?.data ?? data ?? {};
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.KhayratReport = {};
        toast.error(errText(e, 'د راپور معلومات رانغلو'));
        throw e;
      }
    },

    async CreateKhayratReport(payload) {
      try {
        setContentType('application/json');
        const res = await axios.post(`/khayrat-reports`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('راپور بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'راپور اضافه نشو'));
        throw e;
      }
    },

    async UpdateKhayratReport(id, payload) {
      try {
        setContentType('application/json');
        const res = await axios.put(`/khayrat-reports/${id}`, payload);
        const saved = res.data?.data ?? res.data;

        toast.success('راپور بریالیتوب سره تغییر شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'راپور تغییر نشو'));
        throw e;
      }
    },

    async DeleteKhayratReport(id) {
      try {
        await axios.delete(`/khayrat-reports/${id}`);
        toast.success('راپور بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'راپور حذف نشو'));
        throw e;
      }
    },

    /* ==========================
     * LOOKUPS
     * ✅ no page/perPage
     * ========================== */

    async FetchResourcesLookup({ search } = {}) {
      this.lookups.loadingResources = true;
      try {
        setContentType('application/json');

        const s = (search ?? '').trim();
        const { data: res } = await axios.get(`/resources?search=${encodeURIComponent(s)}`);

        const items = res?.data ?? res ?? [];
        this.lookups.resources = Array.isArray(items) ? items : [];
        return this.lookups.resources;
      } catch (e) {
        console.error(e);
        toast.error(errText(e, 'Resources lookup failed'));
        this.lookups.resources = [];
        throw e;
      } finally {
        this.lookups.loadingResources = false;
      }
    },

    async FetchOfficeEmployeesLookup({ search } = {}) {
      this.lookups.loadingEmployees = true;
      try {
        setContentType('application/json');

        const s = (search ?? '').trim();
        const { data: res } = await axios.get(`/office-employees?search=${encodeURIComponent(s)}`);

        const items = res?.data ?? res ?? [];
        this.lookups.employees = Array.isArray(items) ? items : [];
        return this.lookups.employees;
      } catch (e) {
        console.error(e);
        toast.error(errText(e, 'Office employees lookup failed'));
        this.lookups.employees = [];
        throw e;
      } finally {
        this.lookups.loadingEmployees = false;
      }
    },
  },
});
