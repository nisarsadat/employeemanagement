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

export const useMawadRepository = defineStore('MawadRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      // collections // done
      Mawads: reactive([]),
      Mawad: reactive({}),

      // done
      LostMujremeens: reactive([]),
      LostMujremeen: reactive({}),

      // done
      OldSoldiersHomes: reactive([]),
      OldSoldiersHome: reactive({}),

      //  done
      Robbers: reactive([]),
      Robber: reactive({}),

      // done
      NonResponsablePeoples: reactive([]),
      NonResponsablePeople: reactive({}),

      // done
      MashkokHomes: reactive([]),
      MashkokHome: reactive({}),

      // done
      DamagePLaces: reactive([]),
      DamagePlace: reactive({}),

      // done
      DamageRasanPlaces: reactive([]),
      DamageRasanPlace: reactive({}),

      // done
      MashkokShops: reactive([]),
      MashkokShop: reactive({}),

      // done
      Luchakanos: reactive([]),
      Luchakano: reactive({}),

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

    /* ============================ Mawads ============================ */
    async FetchMawadsData(params = {}) {
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

        const { data: res } = await axios.get(`mawads`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Mawads');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Fasads = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMawadFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/mawads/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchMawadData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mawads/${id}`);
        this.Mawad = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Mawad = {};
      }
    },

    async CreateMawad(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mawads`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenMawadsPrintView(payload = {}) {
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
        file_name: file_name || 'mawads-print',
      };

      const res = await axios.get('/mawads/print', {
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
    async UpdateMawad(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mawads/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mawads/${id}`, data);
        }
        toast.success('ریکارډ  بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  تغییر نشو'));
        throw e;
      }
    },

    async DeleteMawad(id) {
      try {
        await axios.delete(`/mawads/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchMawadsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Lost Mujremeens ============================ */
    async FetchLostMujremeensData(params = {}) {
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

        const { data: res } = await axios.get(`lost-mujremeens`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'LostMujremeens');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.LostMujremeens = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchLostMujremeenFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/lost-mujremeens/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async OpenLostMujremeensPrintView(payload = {}) {
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
        file_name: file_name || 'lost-mujremeens-print',
      };

      const res = await axios.get('/lost-mujremeens/print', {
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
    async FetchLostMujremeenData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/lost-mujremeens/${id}`);
        this.LostMujremeen = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.LostMujremeen = {};
      }
    },

    async CreateLostMujremeen(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/lost-mujremeens`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateLostMujremeen(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/lost-mujremeens/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/lost-mujremeens/${id}`, data);
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

    async DeleteLostMujremeen(id) {
      try {
        await axios.delete(`/lost-mujremeens/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Old Soldiers Homes ============================ */
    async FetchOldSoldiersHomesData(params = {}) {
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

        const { data: res } = await axios.get(`old-soldiers-homes`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'OldSoldiersHomes');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.OldSoldiersHomes = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchOldSoldiersHomeFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/old-soldiers-homes/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchOldSoldiersHomeData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/old-soldiers-homes/${id}`);
        this.OldSoldiersHome = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.OldSoldiersHome = {};
      }
    },

    async CreateOldSoldiersHome(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/old-soldiers-homes/`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenOldSoldiersHomesPrintView(payload = {}) {
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
        file_name: file_name || 'old-soldiers-homes-print',
      };

      const res = await axios.get('/old-soldiers-homes/print', {
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
    async UpdateOldSoldiersHome(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/old-soldiers-homes/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/old-soldiers-homes/${id}`, data);
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

    async DeleteOldSoldiersHome(id) {
      try {
        await axios.delete(`/old-soldiers-homes/${id}`);
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

    /* ============================ Robbers ============================ */
    async FetchRobbersData(params = {}) {
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

        const { data: res } = await axios.get(`robbers`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Robbers');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Robbers = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchRobberFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/robbers/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchRobberData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/robbers/${id}`);
        this.Robber = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Robber = {};
      }
    },

    async CreateRobber(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/robbers`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateRobber(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/robbers/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/robbers/${id}`, data);
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

    async DeleteRobbers(id) {
      try {
        await axios.delete(`/robbers/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchRobbersData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
    async OpenRobbersPrintView(payload = {}) {
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
        file_name: file_name || 'robbers-print',
      };

      const res = await axios.get('/robbers/print', {
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
    /* ============================ NonResponsablePeoples ============================ */
    async FetchNonResponsablePeoplesData(params = {}) {
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

        const { data: res } = await axios.get(`non-responsable-people`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'NonResponsablePeoples');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.NonResponsablePeoples = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchNonResponsablePeopleFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/non-responsable-people/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchNonResponsablePeopleData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/non-responsable-people/${id}`);
        this.NonResponsablePeople = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.NonResponsablePeople = {};
      }
    },

    async CreateNonResponsablePeople(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/non-responsable-people`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenNonResponsablePeoplePrintView(payload = {}) {
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
        file_name: file_name || 'non-responsable-people-print',
      };

      const res = await axios.get('/non-responsable-people/print', {
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
    async UpdateNonResponsablePeoples(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/non-responsable-people/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/non-responsable-people/${id}`, data);
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

    async DeleteNonResponsablePeople(id) {
      try {
        await axios.delete(`/non-responsable-people/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchNonResponsablePeoplesData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Mashkok Homes ============================ */
    async FetchMashkokHomesData(params = {}) {
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

        const { data: res } = await axios.get(`mashkok-homes`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'MashkokHomes');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokHomes = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMashkokHomeFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/mashkok-homes/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },
    async OpenMashkokHomesPrintView(payload = {}) {
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
        file_name: file_name || 'mashkok-homes-print',
      };

      const res = await axios.get('/mashkok-homes/print', {
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
    async FetchMashkokHomeData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mashkok-homes/${id}`);
        this.MashkokHome = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokHome = {};
      }
    },

    async CreateMashkokHome(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mashkok-homes`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },

    async UpdateMashkokHome(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mashkok-homes/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mashkok-homes/${id}`, data);
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

    async DeleteMashkokHome(id) {
      try {
        await axios.delete(`/mashkok-homes/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchMashkokHomesData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ DamagePlacses ============================ */
    async FetchDamagePlacesData(params = {}) {
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

        const { data: res } = await axios.get(`damage-places`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'DamagePlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DamagePlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchDamagePlaceFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/damage-places/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchDamagePlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/damage-places/${id}`);
        this.DamagePlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DamagePlace = {};
      }
    },

    async CreateDamagePlace(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/damage-places`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenDamagePlacesPrintView(payload = {}) {
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
        file_name: file_name || 'damage-places-print',
      };

      const res = await axios.get('/damage-places/print', {
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
    async UpdateDamagePlace(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/damage-places/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/damage-places/${id}`, data);
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

    async DeleteDamagePlace(id) {
      try {
        await axios.delete(`/damage-places/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchDamagePlacesData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Damage Rasan PLaces ============================ */
    async FetchDamageRasanPlacesData(params = {}) {
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

        const { data: res } = await axios.get(`damage-rasan-places`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'DamageRasanPlaces');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DamageRasanPlaces = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchDamageRasanPlaceFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/damage-rasan-places/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchDamageRasanPlaceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/damage-rasan-places/${id}`);
        this.DamageRasanPlace = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.DamageRasanPlace = {};
      }
    },

    async CreateDamageRasanPlaces(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/damage-rasan-places`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenDamageRasanPlacesPrintView(payload = {}) {
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
        file_name: file_name || 'damage-rasan-places-print',
      };

      const res = await axios.get('/damage-rasan-places/print', {
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
    async UpdateDamageRasanPlaces(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/damage-rasan-places/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/damage-rasan-places/${id}`, data);
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

    async DeleteDamageRasanPlaces(id) {
      try {
        await axios.delete(`/damage-rasan-places/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Mashkok Shops ============================ */
    async FetchMashkokShopsData(params = {}) {
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

        const { data: res } = await axios.get(`mashkok-shops`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'MashkokShops');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokShops = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchMashkokShopFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/mashkok-shops/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchMashkokShopData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/mashkok-shops/${id}`);
        this.MashkokShop = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.MashkokShop = {};
      }
    },

    async CreateMashkokShop(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/mashkok-shops`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenMashkokShopsPrintView(payload = {}) {
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
        file_name: file_name || 'mashkok-shops-print',
      };

      const res = await axios.get('/mashkok-shops/print', {
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
    async UpdateMashkokShop(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/mashkok-shops/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/mashkok-shops/${id}`, data);
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

    async DeleteMashkokShop(id) {
      try {
        await axios.delete(`/mashkok-shops/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Luchakano ============================ */
    async FetchLuchakanosData(params = {}) {
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

        const { data: res } = await axios.get(`luchakanos`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Luchakanos');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Luchakanos = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchLuchakanoFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/luchakanos/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchLuchakanoData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/luchakanos/${id}`);
        this.Luchakano = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Luchakano = {};
      }
    },

    async CreateLuchakano(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/luchakanos`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ  بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        // optionally refresh list here
        // await this.FetchFasadsData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  اضافه نشو'));
        throw e;
      }
    },
    async OpenLuchakanosPrintView(payload = {}) {
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
        file_name: file_name || 'luchakanos-print',
      };

      const res = await axios.get('/luchakano/print', {
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
    async UpdateLuchakano(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/luchakanos/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/luchakanos/${id}`, data);
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

    async DeleteLuchakano(id) {
      try {
        await axios.delete(`/luchakanos/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchLuchakanosData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    // ⬇️ Download ALL documents of Mashkok Home as ZIP
    // async DownloadMashkokHomeDocumentsZip(id) {
    //   try {
    //     // IMPORTANT: responseType = blob
    //     const response = await axios.get(`/mashkok-homes/${id}/documents/zip`, {
    //       responseType: 'blob',
    //     });

    //     // Create a temporary download link
    //     const blob = new Blob([response.data], {
    //       type: 'application/zip',
    //     });

    //     const url = window.URL.createObjectURL(blob);
    //     const link = document.createElement('a');

    //     link.href = url;
    //     link.download = `mashkok_home_${id}_documents.zip`;

    //     document.body.appendChild(link);
    //     link.click();

    //     document.body.removeChild(link);
    //     window.URL.revokeObjectURL(url);

    //     toast.success('فایل ZIP با موفقیت دانلود شد', {
    //       autoClose: 1200,
    //     });
    //   } catch (e) {
    //     console.error(e);
    //     toast.error(e?.response?.data?.message || 'دانلود فایل‌ها ناموفق بود');
    //     throw e;
    //   }
    // },
    async DownloadMashkokHomeZip(id) {
      const res = await axios.get(`/mashkok-homes/${id}/documents/zip`, {
        responseType: 'blob',
        validateStatus: () => true,
      });

      const status = res.status;
      const ct = (res.headers?.['content-type'] || '').toLowerCase();

      // If not 200, it's an error body (often JSON)
      if (status !== 200) {
        const text = await res.data.text();
        console.error('ZIP request failed:', status, ct, text.slice(0, 500));
        throw new Error(`ZIP download failed: ${status}`);
      }

      // If backend returned HTML/JSON, log it and stop
      if (
        ct.includes('text/html') ||
        ct.includes('application/json') ||
        ct.includes('text/plain')
      ) {
        const text = await res.data.text();
        console.error('Returned NOT-ZIP:', ct, text.slice(0, 500));
        throw new Error(
          'ZIP endpoint returned non-zip (HTML/JSON). Fix route/auth or backend error.'
        );
      }

      // ✅ Strong check: ZIP files start with "PK"
      const head = new Uint8Array(await res.data.slice(0, 2).arrayBuffer());
      const isZip = head[0] === 0x50 && head[1] === 0x4b; // 'P''K'
      if (!isZip) {
        const text = await res.data.text();
        console.error('Missing ZIP signature (PK). Body:', text.slice(0, 500));
        throw new Error('Downloaded file is not a valid ZIP (no PK signature).');
      }

      const url = URL.createObjectURL(res.data);
      const a = document.createElement('a');
      a.href = url;
      a.download = `mashkok_home_${id}_documents.zip`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    },

    async DownloadLostMujremeenZip(id) {
      const res = await axios.get(`/lost-mujremeens/${id}/documents-zip`, {
        responseType: 'blob',
        validateStatus: () => true,
      });

      const ct = res.headers?.['content-type'] || '';

      // If it is HTML, you're still hitting Vite or redirecting
      if (ct.includes('text/html')) {
        const html = await res.data.text();
        console.error('Returned HTML (NOT ZIP):', html.slice(0, 300));
        throw new Error('ZIP endpoint returned HTML. Fix proxy/baseURL or route/auth.');
      }

      const blob = new Blob([res.data], { type: 'application/zip' });
      const url = URL.createObjectURL(blob);

      const a = document.createElement('a');
      a.href = url;
      a.download = `lost_mujremeen_${id}_documents.zip`;
      document.body.appendChild(a);
      a.click();
      a.remove();

      URL.revokeObjectURL(url);
    },
  },
});
