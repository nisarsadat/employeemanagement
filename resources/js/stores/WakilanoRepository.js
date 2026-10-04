// src/stores/WakilanoRepository.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { buildListParams, applyPaginatedResponse } from '@/utils/listHelpers';

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
function errText(e, fallback = 'یو څه ناسم شول') {
  return e?.response?.data?.message || e?.message || (typeof e === 'string' ? e : fallback);
}

export const useWakilanoRepository = defineStore('WakilanoRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      // ===== collections (reactive like your format) =====
      //
      Wakilanos: reactive([]),
      Wakilano: reactive({}),

      //
      Universities: reactive([]),
      University: reactive({}),

      //
      Masjids: reactive([]),
      Masjid: reactive({}),

      //
      Companies: reactive([]),
      Company: reactive({}),

      //
      Highways: reactive([]),
      Highway: reactive({}),

      //
      DoneLostPlaces: reactive([]),
      DoneLostPlace: reactive({}),

      //
      EconemyProjects: reactive([]),
      EconemyProject: reactive({}),

      //
      CriminalCapturePlaces: reactive([]),
      CriminalCapturePlace: reactive({}),

      //
      Pumps: reactive([]),
      Pump: reactive({}),

      //
      Shoras: reactive([]),
      Shora: reactive({}),

      //
      DeplomaticPlaces: reactive([]),
      DeplomaticPlace: reactive({}),

      //
      Checkpoints: reactive([]),
      Checkpoint: reactive({}),

      //
      Parks: reactive([]),
      Park: reactive({}),

      //
      GovernmentPlaces: reactive([]),
      GovernmentPlace: reactive({}),

      //
      SuperMarkets: reactive([]),
      SuperMarket: reactive({}),

      //
      Complexes: reactive([]),
      Complex: reactive({}),

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

    /* ============================ Wakilanos ============================ */
    async FetchWakilanosData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`wakilanos`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Wakilanos');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Wakilanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchWakilanoFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/wakilanos/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchWakilanoData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/wakilanos/${id}`);
        this.Wakilano = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Wakilano = {};
      }
    },
    async CreateWakilano(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/wakilanos`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenWakilanosPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'wakilanos-print',
      };

      const res = await axios.get('/wakilanos/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },

    async UpdateWakilano(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/wakilanos/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/wakilanos/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteWakilano(id) {
      try {
        await axios.delete(`/wakilanos/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ Wakilano Excel Download ============================ */
    async DownloadWakilanoExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/wakilanos/export/excel', {
          params: { search, search_field, search_value, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'wakilanos.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadWakilanoExcel error:', error); throw error; }
    },

    /* ============================ Universities ============================ */
    async FetchUniversitiesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`universities`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Universities');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Universities = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchUniversityFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/universities/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchUniversityData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/universities/${id}`);
        this.University = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.University = {};
      }
    },
    async CreateUniversity(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/universities`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenUniversitiesPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'universities-print',
      };

      const res = await axios.get('/universities/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateUniversity(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/universities/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/universities/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteUniversity(id) {
      try {
        await axios.delete(`/universities/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ University Excel Download ============================ */
    async DownloadUniversityExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/universities/export/excel', {
          params: { search, search_field, search_value, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'universities.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadUniversityExcel error:', error); throw error; }
    },

    /* ============================ Masjids ============================ */
    async FetchMasjidsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`masjids`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Masjids');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Masjids = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchMasjidFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/masjids/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchMasjidData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/masjids/${id}`);
        this.Masjid = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Masjid = {};
      }
    },
    async CreateMasjid(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/masjids`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenMasjidsPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'masjids-print',
      };

      const res = await axios.get('/masjids/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateMasjid(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/masjids/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/masjids/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteMasjid(id) {
      try {
        await axios.delete(`/masjids/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== MASJID EXCEL EXPORT ==================== */
    async DownloadMasjidExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/masjids/export/excel', { params: { search, search_field, search_value, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'masjids.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadMasjidExcel error:', error); throw error; }
    },

    /* ============================ Companies ============================ */
    async FetchCompaniesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`companies`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Companies');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Companies = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchCompanyFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/companies/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchCompanyData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/companies/${id}`);
        this.Company = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Company = {};
      }
    },
    async CreateCompany(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/companies`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenCompaniesPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'companies-print',
      };

      const res = await axios.get('/companies/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateCompany(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/companies/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/companies/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteCompany(id) {
      try {
        await axios.delete(`/companies/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== COMPANY EXCEL EXPORT ==================== */
    async DownloadCompanyExcel(payload = {}) {
      try {
        const {
          search = '',
          search_field = null,
          search_value = null,
          card_filter = 'all',
          resource_id = null,
          onDownloadProgress = null,
        } = payload;

        const response = await axios.get('/companies/export/excel', {
          params: { search, search_field, search_value, card_filter, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        const disposition = response.headers['content-disposition'];
        let fileName = 'companies.xlsx';
        if (disposition) {
          const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
          if (match?.[1]) fileName = match[1].replace(/['"]/g, '');
        }

        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        return response;
      } catch (error) {
        console.error('DownloadCompanyExcel error:', error);
        throw error;
      }
    },

    /* ============================ Highways ============================ */
    async FetchHighwaysData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`highways`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Highways');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Highways = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchHighwayFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/highways/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchHighwayData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/highways/${id}`);
        this.Highway = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Highway = {};
      }
    },
    async CreateHighway(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/highways`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenHighwaysPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'highways-print',
      };

      const res = await axios.get('/highways/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateHighway(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/highways/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/highways/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteHighway(id) {
      try {
        await axios.delete(`/highways/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== HIGHWAY EXCEL EXPORT ==================== */
    async DownloadHighwayExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/highways/export/excel', { params: { search, search_field, search_value, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'highways.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadHighwayExcel error:', error); throw error; }
    },

    /* ============================ DoneLostPlaces ============================ */
    async FetchDoneLostPlacesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`done-lost-places`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'DoneLostPlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DoneLostPlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchDoneLostPlaceFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/done-lost-places/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchDoneLostPlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/done-lost-places/${id}`);
        this.DoneLostPlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DoneLostPlace = {};
      }
    },
    async CreateDoneLostPlace(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/done-lost-places`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenDoneLostPlacesPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'done-lost-places-print',
      };

      const res = await axios.get('/done-lost-places/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateDoneLostPlace(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/done-lost-places/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/done-lost-places/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteDoneLostPlace(id) {
      try {
        await axios.delete(`/done-lost-places/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== DONE LOST PLACES EXCEL EXPORT ==================== */
    async DownloadDoneLostPlaceExcel(payload = {}) {
      try {
        const {
          search = '',
          search_field = null,
          search_value = null,
          card_filter = 'all',
          resource_id = null,
          onDownloadProgress = null,
        } = payload;

        const response = await axios.get('/done-lost-places/export/excel', {
          params: { search, search_field, search_value, card_filter, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        const disposition = response.headers['content-disposition'];
        let fileName = 'done_lost_places.xlsx';
        if (disposition) {
          const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
          if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); }
        }

        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        return response;
      } catch (error) {
        console.error('DownloadDoneLostPlaceExcel error:', error);
        throw error;
      }
    },

    /* ============================ EconemyProjects ============================ */
    async FetchEconemyProjectsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`econemy-projects`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'EconemyProjects');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EconemyProjects = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchEconemyProjectFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/econemy-projects/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchEconemyProjectData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/econemy-projects/${id}`);
        this.EconemyProject = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EconemyProject = {};
      }
    },
    async CreateEconemyProject(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/econemy-projects`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenEconemyProjectsPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'econemy-projects-print',
      };

      const res = await axios.get('/econemy-projects/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateEconemyProject(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/econemy-projects/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/econemy-projects/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteEconemyProject(id) {
      try {
        await axios.delete(`/econemy-projects/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== ECONEMY PROJECT EXCEL EXPORT ==================== */
    async DownloadEconemyProjectExcel(payload = {}) {
      try {
        const {
          search = '',
          search_field = null,
          search_value = null,
          card_filter = 'all',
          resource_id = null,
          onDownloadProgress = null,
        } = payload;

        const response = await axios.get('/econemy-projects/export/excel', {
          params: { search, search_field, search_value, card_filter, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        const disposition = response.headers['content-disposition'];
        let fileName = 'econemy_projects.xlsx';
        if (disposition) {
          const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
          if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); }
        }

        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        return response;
      } catch (error) {
        console.error('DownloadEconemyProjectExcel error:', error);
        throw error;
      }
    },

    /* ============================ CriminalCapturePlaces ============================ */
    async FetchCriminalCapturePlacesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`criminal-capture-places`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'CriminalCapturePlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.CriminalCapturePlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchCriminalCapturePlaceFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/criminal-capture-places/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchCriminalCapturePlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/criminal-capture-places/${id}`);
        this.CriminalCapturePlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.CriminalCapturePlace = {};
      }
    },
    async CreateCriminalCapturePlace(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/criminal-capture-places`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenCriminalCapturePlacesPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'criminal-capture-places-print',
      };

      const res = await axios.get('/criminal-capture-places/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateCriminalCapturePlace(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/criminal-capture-places/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/criminal-capture-places/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteCriminalCapturePlace(id) {
      try {
        await axios.delete(`/criminal-capture-places/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== CRIMINAL CAPTURE PLACE EXCEL EXPORT ==================== */
    async DownloadCriminalCapturePlaceExcel(payload = {}) {
      try {
        const {
          search = '',
          search_field = null,
          search_value = null,
          card_filter = 'all',
          resource_id = null,
          onDownloadProgress = null,
        } = payload;

        const response = await axios.get('/criminal-capture-places/export/excel', {
          params: { search, search_field, search_value, card_filter, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        const disposition = response.headers['content-disposition'];
        let fileName = 'criminal_capture_places.xlsx';
        if (disposition) {
          const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
          if (match?.[1]) fileName = match[1].replace(/['"]/g, '');
        }

        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        return response;
      } catch (error) {
        console.error('DownloadCriminalCapturePlaceExcel error:', error);
        throw error;
      }
    },

    /* ============================ Pumps ============================ */
    async FetchPumpsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`pumps`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Pumps');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Pumps = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchPumpFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/pumps/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchPumpData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/pumps/${id}`);
        this.Pump = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Pump = {};
      }
    },
    async CreatePump(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/pumps`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenPumpsPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'pumps-print',
      };

      const res = await axios.get('/pumps/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdatePump(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/pumps/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/pumps/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeletePump(id) {
      try {
        await axios.delete(`/pumps/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== PUMP EXCEL EXPORT ==================== */
    async DownloadPumpExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/pumps/export/excel', { params: { search, search_field, search_value, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'pumps.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadPumpExcel error:', error); throw error; }
    },

    /* ============================ Shoras ============================ */
    async FetchShorasData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`shoras`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Shoras');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Shoras = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchShoraFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/shoras/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchShoraData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/shoras/${id}`);
        this.Shora = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Shora = {};
      }
    },
    async CreateShora(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/shoras`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenShorasPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'shoras-print',
      };

      const res = await axios.get('/shoras/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateShora(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/shoras/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/shoras/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteShora(id) {
      try {
        await axios.delete(`/shoras/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ Shora Excel Download ============================ */
    async DownloadShoraExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/shoras/export/excel', {
          params: { search, search_field, search_value, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'shoras.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadShoraExcel error:', error); throw error; }
    },

    /* ============================ DeplomaticPlaces ============================ */
    async FetchDeplomaticPlacesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`deplomaticplaces`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'DeplomaticPlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DeplomaticPlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchDeplomaticPlaceFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/deplomaticplaces/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchDeplomaticPlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/deplomaticplaces/${id}`);
        this.DeplomaticPlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DeplomaticPlace = {};
      }
    },
    async CreateDeplomaticPlace(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/deplomaticplaces`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenDeplomaticPlacesPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'deplomaticplaces-print',
      };

      const res = await axios.get('/deplomaticplaces/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },

    async UpdateDeplomaticPlace(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/deplomaticplaces/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/deplomaticplaces/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteDeplomaticPlace(id) {
      try {
        await axios.delete(`/deplomaticplaces/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== DEPLOMATIC PLACE EXCEL EXPORT ==================== */
    async DownloadDeplomaticPlaceExcel(payload = {}) {
      try {
        const {
          search = '',
          search_field = null,
          search_value = null,
          card_filter = 'all',
          resource_id = null,
          onDownloadProgress = null,
        } = payload;

        const response = await axios.get('/deplomaticplaces/export/excel', {
          params: { search, search_field, search_value, card_filter, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        const disposition = response.headers['content-disposition'];
        let fileName = 'deplomatic_places.xlsx';
        if (disposition) {
          const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
          if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); }
        }

        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        return response;
      } catch (error) {
        console.error('DownloadDeplomaticPlaceExcel error:', error);
        throw error;
      }
    },

    /* ============================ Checkpoints ============================ */
    async FetchCheckpointsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`checkpoints`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Checkpoints');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Checkpoints = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchCheckpointFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/checkpoints/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchCheckpointData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/checkpoints/${id}`);
        this.Checkpoint = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Checkpoint = {};
      }
    },
    async CreateCheckpoint(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/checkpoints`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenCheckpointsPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'checkpoints-print',
      };

      const res = await axios.get('/checkpoints/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateCheckpoint(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/checkpoints/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/checkpoints/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteCheckpoint(id) {
      try {
        await axios.delete(`/checkpoints/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== CHECKPOINT EXCEL EXPORT/IMPORT ==================== */
    async DownloadCheckpointExcel(payload = {}) {
      try {
        const {
          search = '',
          search_field = null,
          search_value = null,
          card_filter = 'all',
          resource_id = null,
          onDownloadProgress = null,
        } = payload;

        const response = await axios.get('/checkpoints/export/excel', {
          params: {
            search,
            search_field,
            search_value,
            card_filter,
            resource_id,
          },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        const disposition = response.headers['content-disposition'];
        let fileName = 'checkpoints.xlsx';
        if (disposition) {
          const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
          if (match?.[1]) {
            fileName = match[1].replace(/['"]/g, '');
          }
        }

        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        return response;
      } catch (error) {
        console.error('DownloadCheckpointExcel error:', error);
        throw error;
      }
    },


    /* ============================ Parks ============================ */
    async FetchParksData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`parks`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Parks');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Parks = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchParkFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/parks/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchParkData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/parks/${id}`);
        this.Park = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Park = {};
      }
    },
    async CreatePark(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/parks`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenParksPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'parks-print',
      };

      const res = await axios.get('/parks/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdatePark(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/parks/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/parks/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeletePark(id) {
      try {
        await axios.delete(`/parks/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== PARK EXCEL EXPORT ==================== */
    async DownloadParkExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/parks/export/excel', { params: { search, search_field, search_value, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'parks.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadParkExcel error:', error); throw error; }
    },

    /* ============================ GovernmentPlaces ============================ */
    async FetchGovernmentPlacesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`governmentplaces`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'GovernmentPlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.GovernmentPlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchGovernmentPlaceFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/governmentplaces/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchGovernmentPlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/governmentplaces/${id}`);
        this.GovernmentPlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.GovernmentPlace = {};
      }
    },
    async CreateGovernmentPlace(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/governmentplaces`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenGovernmentPlacesPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'government-places-print',
      };

      const res = await axios.get('/government-places/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateGovernmentPlace(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/governmentplaces/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/governmentplaces/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteGovernmentPlace(id) {
      try {
        await axios.delete(`/governmentplaces/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== GOVERNMENT PLACE EXCEL EXPORT ==================== */
    async DownloadGovernmentPlaceExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, card_filter = 'all', resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/government-places/export/excel', { params: { search, search_field, search_value, card_filter, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'government_places.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadGovernmentPlaceExcel error:', error); throw error; }
    },

    /* ============================ SuperMarkets ============================ */
    async FetchSuperMarketsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`supermarkets`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'SuperMarkets');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.SuperMarkets = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchSuperMarketFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/supermarkets/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchSuperMarketData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/supermarkets/${id}`);
        this.SuperMarket = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.SuperMarket = {};
      }
    },
    async CreateSuperMarket(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/supermarkets`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenSuperMarketsPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'super-markets-print',
      };

      const res = await axios.get('/super-markets/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateSuperMarket(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/supermarkets/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/supermarkets/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteSuperMarket(id) {
      try {
        await axios.delete(`/supermarkets/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ SuperMarket Excel Download ============================ */
    async DownloadSuperMarketExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/super-markets/export/excel', {
          params: { search, search_field, search_value, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'super_markets.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadSuperMarketExcel error:', error); throw error; }
    },

    /* ============================ Complexes ============================ */
    async FetchComplexesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`complexes`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Complexes');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Complexes = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchComplexFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/complexes/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async FetchComplexData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/complexes/${id}`);
        this.Complex = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Complex = {};
      }
    },
    async CreateComplex(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/complexes`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenComplexesPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'complexes-print',
      };

      const res = await axios.get('/complexes/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async UpdateComplex(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/complexes/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/complexes/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },
    async DeleteComplex(id) {
      try {
        await axios.delete(`/complexes/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ==================== COMPLEX EXCEL EXPORT ==================== */
    async DownloadComplexExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, card_filter = 'all', resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/complexes/export/excel', {
          params: { search, search_field, search_value, card_filter, resource_id },
          responseType: 'blob', onDownloadProgress,
        });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'complexes.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) fileName = match[1].replace(/['"]/g, ''); }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadComplexExcel error:', error); throw error; }
    },

    // for pic
    /* ============================ Delete Pictures ============================ */

    async DeleteWakilanoPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/wakilanos/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteUniversityPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/universities/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteMasjidPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/masjids/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteCompanyPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/companies/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteHighwayPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/highways/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteDoneLostPlacePicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/done-lost-places/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteEconemyProjectPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/econemy-projects/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteCriminalCapturePlacePicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/criminal-capture-places/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeletePumpPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/pumps/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteShoraPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/shoras/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteDeplomaticPlacePicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/deplomaticplaces/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteCheckpointPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/checkpoints/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteParkPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/parks/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteGovernmentPlacePicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/governmentplaces/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteSuperMarketPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/supermarkets/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteComplexPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/complexes/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },
  },
});
