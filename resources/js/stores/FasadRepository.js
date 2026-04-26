// src/stores/FasadRepository.js
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
function errText(e, fallback = 'بعضې ستونزه رامنځته شوه') {
  return e?.response?.data?.message || e?.message || (typeof e === 'string' ? e : fallback);
}

export const useFasadRepository = defineStore('FasadRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      // collections // done
      Fasads: reactive([]),
      Fasad: reactive({}),

      // done
      SaeenFasads: reactive([]),
      SaeenFasad: reactive({}),

      // done
      Heists: reactive([]),
      Heist: reactive({}),

      //  done
      Captives: reactive([]),
      Captive: reactive({}),

      // done
      Mulkanos: reactive([]),
      Mulkano: reactive({}),

      // done
      EntryExits: reactive([]),
      EntryExit: reactive({}),

      // done
      Sarafyanos: reactive([]),
      Sarafyano: reactive({}),

      // done
      Clubs: reactive([]),
      Club: reactive({}),

      // done
      Soldiers: reactive([]),
      Soldier: reactive({}),

      // done
      Mahajerats: reactive([]),
      Mahajerat: reactive([]),

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

    /* ============================ FASADS ============================ */
    async FetchFasadsData(params = {}) {
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

        const { data: res } = await axios.get(`fasads`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Fasads');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Fasads = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchFasadFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/fasads/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchFasadData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/fasads/${id}`);
        this.Fasad = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Fasad = {};
      }
    },

    async CreateFasad(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/fasads`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ فساد بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ فساد اضافه نشو'));
        throw e;
      }
    },
    async OpenFasadsPrintView(payload = {}) {
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
        file_name: file_name || 'fasads-print',
      };

      const res = await axios.get('/fasads/print', {
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
    async UpdateFasad(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/fasads/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/fasads/${id}`, data);
        }
        toast.success('ریکارډ فساد بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ فساد تغییر نشو'));
        throw e;
      }
    },

    async DeleteFasad(id) {
      try {
        await axios.delete(`/fasads/${id}`);
        toast.success('ریکارډ فساد بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchFasadsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ فساد حذف نشو'));
      }
    },

    /* ============================ SAeen FASADS ============================ */
    async FetchSaeenFasadsData(params = {}) {
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

        const { data: res } = await axios.get(`saeen-fasads`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'SaeenFasads');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.SaeenFasads = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async OpenSaeenFasadPrintView(payload = {}) {
      const params = {
        ...(payload.filters || {}),

        selected_columns: payload.selected_columns || [],
        column_labels: payload.column_labels || {},
        print_labels: payload.print_labels || {},
        locale: payload.locale || 'fa',
        title: payload.title || '',
        file_name: payload.file_name || 'saeen-fasad-print',
      };

      const res = await axios.get('/saeen-fasadas/print', {
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
    async FetchSaeenFasadFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/saeen-fasads/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchSaeenFasadData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/saeen-fasads/${id}`);
        this.SaeenFasad = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.SaeenFasad = {};
      }
    },

    async CreateSaeenFasad(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/saeen-fasads`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateSaeenFasad(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/saeen-fasads/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/saeen-fasads/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteSaeenFasad(id) {
      try {
        await axios.delete(`/saeen-fasads/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchSaeenFasadsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Heist ============================ */
    async FetchHeistsData(params = {}) {
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

        const { data: res } = await axios.get(`heists`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Heists');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Heists = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchHeistFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/heists/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchHeistData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/heists/${id}`);
        this.Heist = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Heist = {};
      }
    },

    async CreateHeist(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/heists`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenHeistsPrintView(payload = {}) {
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
        file_name: file_name || 'heists-print',
      };

      const res = await axios.get('/heists/print', {
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
    async UpdateHeist(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/heists/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/heists/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteHeist(id) {
      try {
        await axios.delete(`/heists/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchHeistsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Captive ============================ */
    async FetchCaptivesData(params = {}) {
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

        const { data: res } = await axios.get(`captives`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Captives');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Captives = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchCaptiveFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/captives/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchCaptiveData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/captives/${id}`);
        this.Captive = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Captive = {};
      }
    },

    async CreateCaptive(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/captives`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenCaptivesPrintView(payload = {}) {
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
        file_name: file_name || 'captives-print',
      };

      const res = await axios.get('/captives/print', {
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
    async UpdateCaptive(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/captives/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/captives/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteCaptive(id) {
      try {
        await axios.delete(`/captives/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchHeistsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    async DownloadCaptiveDocuments(id) {
      try {
        setContentType(null);

        const response = await axios.get(`/captives/${id}/documents/zip`, {
          responseType: 'blob',
          withCredentials: true,
        });

        const contentType = response.headers['content-type'] || '';
        if (!contentType.includes('zip')) {
          const text =
            (await response.data.text?.()) ??
            (await new Promise((resolve) => {
              const reader = new FileReader();
              reader.onload = () => resolve(reader.result);
              reader.readAsText(response.data);
            }));

          console.error('Download not a zip:', text);
          toast.error('سرور د ZIP فایل پر ځای بل ځواب راولېږه.');
          return;
        }

        const url = window.URL.createObjectURL(response.data);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'captive_documents.zip';
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'د اسنادو ډاونلوډ ناکام شو'));
      }
    },

    /* ============================ Mulkano ============================ */
    async FetchMulkanosData(params = {}) {
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

        const { data: res } = await axios.get(`mulkanos`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Mulkanos');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Mulkanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMulkanoFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/mulkanos/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchMulkanoData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mulkanos/${id}`);
        this.Mulkano = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Mulkano = {};
      }
    },

    async CreateMulkano(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mulkanos`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateMulkano(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mulkanos/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mulkanos/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteMulakno(id) {
      try {
        await axios.delete(`/mulkanos/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchHeistsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    async OpenMulkanoPrintView(payload = {}) {
      const {
        selected_columns = [],
        column_labels = {},
        print_labels = {},
        locale = 'fa',
        title = '',
        file_name = 'mulkanos-print',
        module,
        ...filters
      } = payload;

      const params = {
        ...filters,

        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
      };

      const res = await axios.get('/mulkano/print', {
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
    /* ============================ Entry Exit ============================ */
    async FetchEntryExitsData(params = {}) {
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

        const { data: res } = await axios.get(`entry-exits`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'EntryExits');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EntryExits = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchEntryExitFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/entry-exits/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchEntryExitData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/entry-exits/${id}`);
        this.EntryExit = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.EntryExit = {};
      }
    },

    async CreateEntryExit(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/entry-exits`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateEntryExit(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/entry-exits/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/entry-exits/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteEntryExit(id) {
      try {
        await axios.delete(`/entry-exits/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchEntryExitsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    async OpenEntryExitPrintView(payload = {}) {
      const {
        selected_columns = [],
        column_labels = {},
        print_labels = {},
        locale = 'fa',
        title = '',
        file_name = 'entry-exits-print',
        module,
        ...filters
      } = payload;

      const params = {
        ...filters,

        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
      };

      const res = await axios.get('/entry-exits/print', {
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
    /* ============================ Sarafyano ============================ */
    async FetchSarafyanosData(params = {}) {
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

        const { data: res } = await axios.get(`sarafyanos`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Sarafyanos');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Sarafyanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchSarafyanoFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/sarafyanos/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchSarafyanoData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/sarafyanos/${id}`);
        this.Sarafyano = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Sarafyano = {};
      }
    },

    async CreateSarafyano(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/sarafyanos`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenSarafyanoPrintView(payload = {}) {
      const {
        selected_columns = [],
        column_labels = {},
        print_labels = {},
        locale = 'fa',
        title = '',
        file_name = 'sarafyanos-print',
        module,
        ...filters
      } = payload;

      const params = {
        ...filters,

        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
      };

      const res = await axios.get('/sarafyanos/print', {
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
    async UpdateSarafyano(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/sarafyanos/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/sarafyanos/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteSarafyano(id) {
      try {
        await axios.delete(`/sarafyanos/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchSarafyanosData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Clubs ============================ */
    async FetchClubsData(params = {}) {
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

        const { data: res } = await axios.get(`clubs`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Clubs');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Clubs = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchClubFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/clubs/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchClubData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/clubs/${id}`);
        this.Club = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Club = {};
      }
    },

    async CreateClub(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/clubs`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenClubsPrintView(payload = {}) {
      const {
        selected_columns = [],
        column_labels = {},
        print_labels = {},
        locale = 'fa',
        title = '',
        file_name = 'clubs-print',
        module,
        ...filters
      } = payload;

      const params = {
        ...filters,

        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
      };

      const res = await axios.get('/clubs/print', {
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
    async UpdateClub(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/clubs/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/clubs/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteClub(id) {
      try {
        await axios.delete(`/clubs/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchClubsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Mahajerat ============================ */
    async FetchMahajeratsData(params = {}) {
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

        const { data: res } = await axios.get(`mahajerats`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Mahajerats');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Mahajerats = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMahajeratFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/mahajerats/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchMahajeratData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mahajerats/${id}`);
        this.Mahajerat = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Mahajerat = {};
      }
    },

    async CreateMahajerat(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mahajerats`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateMahajerat(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mahajerats/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mahajerats/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },
    async OpenMahajeratsPrintView(payload = {}) {
      const {
        selected_columns = [],
        column_labels = {},
        print_labels = {},
        locale = 'fa',
        title = '',
        file_name = 'mahajerats-print',
        module,
        ...filters
      } = payload;

      const params = {
        ...filters,

        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
      };

      const res = await axios.get('/mahajerats/print', {
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
    async DeleteMahajerat(id) {
      try {
        await axios.delete(`/mahajerats/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchMahajeratsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Soldiers ============================ */
    async FetchSoldiersData(params = {}) {
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

        const { data: res } = await axios.get(`soldiers`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Soldiers');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Soldiers = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchSoldierFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/soldiers/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchSoldierData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/soldiers/${id}`);
        this.Soldier = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Soldier = {};
      }
    },

    async CreateSoldier(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/soldiers`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenSoldiersPrintView(payload = {}) {
      const {
        selected_columns = [],
        column_labels = {},
        print_labels = {},
        locale = 'fa',
        title = '',
        file_name = 'soldiers-print',
        module,
        ...filters
      } = payload;

      const params = {
        ...filters,

        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
      };

      const res = await axios.get('/soldiers/print', {
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
    async UpdateSoldier(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/soldiers/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/soldiers/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ تغییر نشو'));
        throw e;
      }
    },

    async DeleteSoldier(id) {
      try {
        await axios.delete(`/soldiers/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchSoldiersData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    async DownloadFasadZip(id, name = `fasad_${id}`) {
      try {
        setContentType(null);

        const response = await axios.get(`/fasads/${id}/download-zip`, {
          responseType: 'blob',
          withCredentials: true,
        });

        const contentType = response.headers['content-type'] || '';
        if (!contentType.includes('zip')) {
          const text =
            (await response.data.text?.()) ??
            (await new Promise((resolve) => {
              const reader = new FileReader();
              reader.onload = () => resolve(reader.result);
              reader.readAsText(response.data);
            }));

          console.error('DownloadZip not zip:', text);
          toast.error('سرور د ZIP فایل پر ځای بل ځواب راولېږه.');
          return;
        }

        const blob = new Blob([response.data], { type: 'application/zip' });
        const url = window.URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `${name}.zip`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        window.URL.revokeObjectURL(url);
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'د اسنادو ډاونلوډ ناکام شو'));
        throw e;
      }
    },

    async DownloadSaeenFasadZip(id, name = `saeen_fasad_${id}`) {
      try {
        setContentType(null);

        const response = await axios.get(`/saeen-fasads/${id}/download-zip`, {
          responseType: 'blob',
          withCredentials: true,
        });

        const contentType = response.headers['content-type'] || '';
        if (!contentType.includes('zip')) {
          const text =
            (await response.data.text?.()) ??
            (await new Promise((resolve) => {
              const reader = new FileReader();
              reader.onload = () => resolve(reader.result);
              reader.readAsText(response.data);
            }));

          console.error('DownloadSaeenFasadZip not zip:', text);
          toast.error('سرور د ZIP فایل پر ځای بل ځواب راولېږه.');
          return;
        }

        const blob = new Blob([response.data], { type: 'application/zip' });
        const url = window.URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `${name}.zip`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        window.URL.revokeObjectURL(url);
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'د اسنادو ډاونلوډ ناکام شو'));
        throw e;
      }
    },

    async DownloadHeistZip(id, name = `heist_${id}`) {
      try {
        setContentType(null);

        const response = await axios.get(`/heists/${id}/documents-zip`, {
          responseType: 'blob',
          withCredentials: true,
        });

        const contentType = response.headers['content-type'] || '';
        if (!contentType.includes('zip')) {
          const text =
            (await response.data.text?.()) ??
            (await new Promise((resolve) => {
              const reader = new FileReader();
              reader.onload = () => resolve(reader.result);
              reader.readAsText(response.data);
            }));

          console.error('DownloadHeistZip not zip:', text);
          toast.error('سرور د ZIP فایل پر ځای بل ځواب راولېږه.');
          return;
        }

        const blob = new Blob([response.data], { type: 'application/zip' });
        const url = window.URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `${name}.zip`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        window.URL.revokeObjectURL(url);
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'د اسنادو ډاونلوډ ناکام شو'));
        throw e;
      }
    },
  },
});
