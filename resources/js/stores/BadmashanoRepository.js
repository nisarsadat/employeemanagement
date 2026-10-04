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
function errText(e, fallback = 'یوه ستونزه رامنځته شوه') {
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

      // collections //
      Badmashanos: reactive([]),
      Badmashano: reactive({}),

      //
      Sarayanos: reactive([]),
      Sarayano: reactive({}),

      //
      Hotels: reactive([]),
      Hotel: reactive({}),

      //
      Hostels: reactive([]),
      Hostel: reactive({}),

      //
      Tujarans: reactive([]),
      Tujaran: reactive({}),

      //
      Mujahedinos: reactive([]),
      Mujahedino: reactive({}),

      //
      EmaratOffices: reactive([]),
      EmaratOffice: reactive({}),

      //
      OldJahadis: reactive([]),
      OldJahadi: reactive({}),

      //
      MashkokPeoples: reactive([]),
      MashkokPeople: reactive({}),

      //
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
    async FetchBadmashanosData(params = {}) {
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

        const { data: res } = await axios.get(`badmashanos`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Badmashanos');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Badmashanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchBadmashanoFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/badmashanos/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchBadmashanoData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/badmashanos/${id}`);
        this.Badmashano = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Badmashano = {};
      }
    },

    async CreateBadmashano(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/badmashanos`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
        throw e;
      }
    },
    async OpenBadmashanosPrintView(payload = {}) {
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
        file_name: file_name || 'badmashanos-print',
      };

      const res = await axios.get('/badmashanos/print', {
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteBadmashano(id) {
      try {
        await axios.delete(`/badmashanos/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ============================ Sarayanos ============================ */
    async FetchSarayanosData(params = {}) {
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

        const { data: res } = await axios.get(`sarayanos`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Sarayanos');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Sarayanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async OpenSarayanosPrintView(payload = {}) {
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
        file_name: file_name || 'sarayanos-print',
      };

      const res = await axios.get('/sarayanos/print', {
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
    async FetchSarayanoFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/sarayanos/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
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
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteSarayano(id) {
      try {
        await axios.delete(`/sarayanos/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ============================ Sarayano Excel Download ============================ */
    async DownloadSarayanoExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/sarayanos/export/excel', {
          params: { search, search_field, search_value, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'sarayanos.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadSarayanoExcel error:', error); throw error; }
    },

    /* ============================ Hotels ============================ */
    async FetchHotelsData(params = {}) {
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

        const { data: res } = await axios.get(`hotels`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Hotels');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hotels = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchHotelFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/hotels/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchHotelData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/hotels/${id}`);
        this.Hotel = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hotel = {};
      }
    },

    async CreateHotel(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/hotels`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
        throw e;
      }
    },
    async OpenHotelsPrintView(payload = {}) {
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
        file_name: file_name || 'hotels-print',
      };

      const res = await axios.get('/hotels/print', {
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
    async OpenHostelsPrintView(payload = {}) {
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
        file_name: file_name || 'hostels-print',
      };

      const res = await axios.get('/hostels/print', {
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteHotel(id) {
      try {
        await axios.delete(`/hotels/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ==================== HOTEL EXCEL EXPORT ==================== */
    async DownloadHotelExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, card_filter = 'all', resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/hotels/export/excel', { params: { search, search_field, search_value, card_filter, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'hotels.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadHotelExcel error:', error); throw error; }
    },

    /* ============================ Hostels ============================ */
    async FetchHostelsData(params = {}) {
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

        const { data: res } = await axios.get(`hostels`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Hostels');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hostels = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchHostelFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/hostels/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
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
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteHostel(id) {
      try {
        await axios.delete(`/hostels/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ==================== HOSTEL EXCEL EXPORT ==================== */
    async DownloadHostelExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, card_filter = 'all', resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/hostels/export/excel', { params: { search, search_field, search_value, card_filter, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'hostels.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadHostelExcel error:', error); throw error; }
    },

    /* ============================ Tujarans ============================ */
    async FetchTujaransData(params = {}) {
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

        const { data: res } = await axios.get(`tujarans`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Tujarans');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Tujarans = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchTujaranFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/tujarans/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
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
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
        throw e;
      }
    },
    async OpenTujaransPrintView(payload = {}) {
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
        file_name: file_name || 'tujarans-print',
      };

      const res = await axios.get('/tujarans/print', {
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteTujaran(id) {
      try {
        await axios.delete(`/tujarans/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ============================ Tujaran Excel Download ============================ */
    async DownloadTujaranExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/tujarans/export/excel', {
          params: { search, search_field, search_value, resource_id },
          responseType: 'blob',
          onDownloadProgress,
        });

        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'tujarans.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadTujaranExcel error:', error); throw error; }
    },

    /* ============================ Mujahedinos ============================ */
    async FetchMujahedinosData(params = {}) {
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

        const { data: res } = await axios.get(`mujahedinos`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Mujahedinos');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Mujahedinos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMujahedinoFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mujahedinos/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchMujahedinoData(id) {
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
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
        throw e;
      }
    },
    async OpenMujahedinosPrintView(payload = {}) {
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
        file_name: file_name || 'mujahedinos-print',
      };

      const res = await axios.get('/mujahedinos/print', {
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteMujahedino(id) {
      try {
        await axios.delete(`/mujahedinos/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ==================== MUJAHEDINO EXCEL EXPORT ==================== */
    async DownloadMujahedinoExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/mujahedinos/export/excel', { params: { search, search_field, search_value, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'mujahedinos.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadMujahedinoExcel error:', error); throw error; }
    },

    /* ============================ EmaratOffices ============================ */
    async FetchEmaratOfficesData(params = {}) {
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

        const { data: res } = await axios.get(`emarat-offices`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'EmaratOffices');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EmaratOffices = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchEmaratOfficeFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/emarat-offices/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
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
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
        throw e;
      }
    },
    async OpenEmaratOfficesPrintView(payload = {}) {
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
        file_name: file_name || 'emarat-offices-print',
      };

      const res = await axios.get('/emarat-offices/print', {
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteEmaratOffice(id) {
      try {
        await axios.delete(`/emarat-offices/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ==================== EMARAT OFFICE EXCEL EXPORT ==================== */
    async DownloadEmaratOfficeExcel(payload = {}) {
      try {
        const {
          search = '',
          search_field = null,
          search_value = null,
          card_filter = 'all',
          resource_id = null,
          onDownloadProgress = null,
        } = payload;

        const response = await axios.get('/emarat-offices/export/excel', {
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
        let fileName = 'emarat_offices.xlsx';
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
        console.error('DownloadEmaratOfficeExcel error:', error);
        throw error;
      }
    },

    /* ============================ OldJahadis ============================ */
    async FetchOldJahadisData(params = {}) {
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

        const { data: res } = await axios.get(`old-jahadis`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'OldJahadis');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.OldJahadis = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchOldJahadiFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/old-jahadis/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
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
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
        throw e;
      }
    },
    async OpenOldJahadisPrintView(payload = {}) {
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
        file_name: file_name || 'old-jahadis-print',
      };

      const res = await axios.get('/old-jahadis/print', {
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteOldJahadi(id) {
      try {
        await axios.delete(`/old-jahadis/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ==================== OLD JAHADI EXCEL EXPORT ==================== */
    async DownloadOldJahadiExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/old-jahadis/export/excel', { params: { search, search_field, search_value, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'old_jahadis.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadOldJahadiExcel error:', error); throw error; }
    },

    /* ============================ MashkokPeoples ============================ */
    async FetchMashkokPeoplesData(params = {}) {
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

        const { data: res } = await axios.get(`mashkok-people`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'MashkokPeoples');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokPeoples = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMashkokPeopleFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mashkok-people/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
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
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
        throw e;
      }
    },
    async OpenMashkokPeoplesPrintView(payload = {}) {
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
        file_name: file_name || 'mashkok-peoples-print',
      };

      const res = await axios.get('/mashkok-people/print', {
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteMashkokPeople(id) {
      try {
        await axios.delete(`/mashkok-people/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ==================== MASHKOK PEOPLE EXCEL EXPORT ==================== */
    async DownloadMashkokPeopleExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/mashkok-people/export/excel', { params: { search, search_field, search_value, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'mashkok_people.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadMashkokPeopleExcel error:', error); throw error; }
    },

    /* ============================ Madrasas ============================ */
    async FetchMadrasasData(params = {}) {
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

        const { data: res } = await axios.get(`madrasas`, {
          params: queryParams,
        });
        applyPaginatedResponse(this, res, 'Madrasas');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Madrasas = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMadrasaFilterOptions(params = {}) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/madrasas/filter-options`, {
          params,
        });
        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchMadrasaData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/madrasas/${id}`);
        this.Madrasa = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Madrasa = {};
      }
    },

    async CreateMadrasa(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/madrasas`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('رېکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
        throw e;
      }
    },
    async OpenMadrasasPrintView(payload = {}) {
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
        file_name: file_name || 'madrasas-print',
      };

      const res = await axios.get('/madrasas/print', {
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
        toast.success('رېکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteMadrasa(id) {
      try {
        await axios.delete(`/madrasas/${id}`);
        toast.success('رېکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ حذف نشو'));
      }
    },

    /* ==================== MADRASA EXCEL EXPORT ==================== */
    async DownloadMadrasaExcel(payload = {}) {
      try {
        const { search = '', search_field = null, search_value = null, resource_id = null, onDownloadProgress = null } = payload;
        const response = await axios.get('/madrasas/export/excel', { params: { search, search_field, search_value, resource_id }, responseType: 'blob', onDownloadProgress });
        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        const disposition = response.headers['content-disposition'];
        let fileName = 'madrasas.xlsx';
        if (disposition) { const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/); if (match?.[1]) { fileName = match[1].replace(/['"]/g, ''); } }
        link.setAttribute('download', fileName); document.body.appendChild(link); link.click(); link.remove(); window.URL.revokeObjectURL(url);
        return response;
      } catch (error) { console.error('DownloadMadrasaExcel error:', error); throw error; }
    },

    /* ============================ Delete Pictures ============================ */

    async DeleteBadmashanoPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/badmashanos/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteSarayanoPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/sarayanos/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteHotelPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/hotels/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteHostelPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/hostels/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteTujaranPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/tujarans/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteMujahedinoPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/mujahedinos/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteEmaratOfficePicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/emarat-offices/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteOldJahadiPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/old-jahadis/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteMashkokPeoplePicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/mashkok-people/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteMadrasaPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/madrasas/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    /* ==================== EXCEL EXPORT ==================== */

    async DownloadBadmashanoExcel(payload = {}) {
      try {
        const {
          search = '',
          search_field = null,
          search_value = null,
          card_filter = 'all',
          resource_id = null,
          fifty_six_format_id = null,
          onDownloadProgress = null,
        } = payload;

        const response = await axios.get('/badmashanos/export/excel', {
          params: {
            search,
            search_field,
            search_value,
            card_filter,
            resource_id,
            fifty_six_format_id,
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
        let fileName = 'badmashanos.xlsx';
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
        console.error('DownloadBadmashanoExcel error:', error);
        throw error;
      }
    },
  },
});
