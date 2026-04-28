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
function errText(e, fallback = 'یو څه ناسم شول') {
  return e?.response?.data?.message || e?.message || (typeof e === 'string' ? e : fallback);
}

export const useSchoolRepository = defineStore('SchoolRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      // collections  // done
      Schools: reactive([]),
      School: reactive({}),

      // done
      HadoBusStops: reactive([]),
      HadoBusStop: reactive({}),

      // done
      TransportCompanies: reactive([]),
      TransportCompany: reactive({}),

      // done
      CarSalesCompanies: reactive([]),
      CarSalesCompany: reactive({}),

      // done
      Parkings: reactive([]),
      Parking: reactive({}),

      // done
      Banks: reactive([]),
      Bank: reactive({}),

      // done
      Apartments: reactive([]),
      Apartment: reactive({}),

      // done
      RahnomaMamlats: reactive([]),
      RahnomaMamlat: reactive({}),

      // done
      Hosppitals: reactive([]),
      Hosppital: reactive({}),

      // done
      WorkShops: reactive([]),
      WorkShop: reactive([]),

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

    /* ============================ Schools ============================ */
    async FetchSchoolsData(params = {}) {
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

        const { data: res } = await axios.get(`schools`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Schools');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Schools = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchSchoolFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/schools/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchSchoolData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/schools/${id}`);
        this.School = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.School = {};
      }
    },

    async CreateSchool(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/schools`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenSchoolsPrintView(payload = {}) {
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
        file_name: file_name || 'schools-print',
      };

      const res = await axios.get('/schools/print', {
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
    async UpdateSchool(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/schools/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/schools/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteSchool(id) {
      try {
        await axios.delete(`/schools/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ HadoBusStops ============================ */
    async FetchHadoBusStopsData(params = {}) {
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

        const { data: res } = await axios.get(`hado-bus-stops`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'HadoBusStops');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.HadoBusStops = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchHadoBusStopFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/hado-bus-stops/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchHadoBusStopData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/hado-bus-stops/${id}`);
        this.HadoBusStop = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.HadoBusStop = {};
      }
    },

    async CreateHadoBusStop(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/hado-bus-stops`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenHadoBusStopsPrintView(payload = {}) {
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
        file_name: file_name || 'hado-bus-stops-print',
      };

      const res = await axios.get('/hado-bus-stops/print', {
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
    async UpdateHadoBusStop(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/hado-bus-stops/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/hado-bus-stops/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteHadoBusStop(id) {
      try {
        await axios.delete(`/hado-bus-stops/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ TransportCompanies ============================ */
    async FetchTransportCompaniesData(params = {}) {
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

        const { data: res } = await axios.get(`transport-companies`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'TransportCompanies');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.TransportCompanies = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchTransportCompanyFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/transport-companies/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchTransportCompanyData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/transport-companies/${id}`);
        this.TransportCompany = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.TransportCompany = {};
      }
    },

    async CreateTransportCompany(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/transport-companies`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenTransportCompaniesPrintView(payload = {}) {
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
        file_name: file_name || 'transport-companies-print',
      };

      const res = await axios.get('/transport-companies/print', {
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
    async UpdateTransportCompany(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/transport-companies/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/transport-companies/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteTransportCompany(id) {
      try {
        await axios.delete(`/transport-companies/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ CarSalesCompanies ============================ */
    async FetchCarSalesCompaniesData(params = {}) {
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

        const { data: res } = await axios.get(`car-sales-companies`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'CarSalesCompanies');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.CarSalesCompanies = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchCarSalesCompanyFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/car-sales-companies/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchCarSalesCompanyData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/car-sales-companies/${id}`);
        this.CarSalesCompany = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.CarSalesCompany = {};
      }
    },

    async CreateCarSalesCompany(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/car-sales-companies`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenCarSalesCompaniesPrintView(payload = {}) {
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
        file_name: file_name || 'car-sales-companies-print',
      };

      const res = await axios.get('/car-sales-companies/print', {
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
    async UpdateCarSalesCompany(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/car-sales-companies/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/car-sales-companies/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteCarSalesCompany(id) {
      try {
        await axios.delete(`/car-sales-companies/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ Parkings ============================ */
    async FetchParkingsData(params = {}) {
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

        const { data: res } = await axios.get(`parkings`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Parkings');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Parkings = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchParkingFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/parkings/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchParkingData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/parkings/${id}`);
        this.Parking = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Parking = {};
      }
    },

    async CreateParking(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/parkings`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenParkingsPrintView(payload = {}) {
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
        file_name: file_name || 'parkings-print',
      };

      const res = await axios.get('/parkings/print', {
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
    async UpdateParking(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/parkings/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/parkings/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteParking(id) {
      try {
        await axios.delete(`/parkings/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ Banks ============================ */
    async FetchBanksData(params = {}) {
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

        const { data: res } = await axios.get(`banks`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Banks');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Banks = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchBankFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/banks/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchBankData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/banks/${id}`);
        this.Bank = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Bank = {};
      }
    },

    async CreateBank(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/banks`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenBanksPrintView(payload = {}) {
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
        file_name: file_name || 'banks-print',
      };

      const res = await axios.get('/banks/print', {
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
    async UpdateBank(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/banks/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/banks/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteBank(id) {
      try {
        await axios.delete(`/banks/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ Apartments ============================ */
    async FetchApartmentsData(params = {}) {
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

        const { data: res } = await axios.get(`apartments`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Apartments');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Apartments = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchApartmentFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/apartments/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchApartmentData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/apartments/${id}`);
        this.Apartment = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Apartment = {};
      }
    },

    async CreateApartment(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/apartments`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenApartmentsPrintView(payload = {}) {
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
        file_name: file_name || 'apartments-print',
      };

      const res = await axios.get('/apartments/print', {
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
    async UpdateApartment(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/apartments/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/apartments/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteApartment(id) {
      try {
        await axios.delete(`/apartments/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ RahnomaMamlats ============================ */
    async FetchRahnomaMamlatsData(params = {}) {
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

        const { data: res } = await axios.get(`rahnoma-mamlats`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'RahnomaMamlats');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.RahnomaMamlats = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchRahnomaMamlatFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/rahnoma-mamlats/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchRahnomaMamlatData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/rahnoma-mamlats/${id}`);
        this.RahnomaMamlat = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.RahnomaMamlat = {};
      }
    },

    async CreateRahnomaMamlat(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/rahnoma-mamlats`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenRahnomaMamlatsPrintView(payload = {}) {
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
        file_name: file_name || 'rahnoma-mamlats-print',
      };

      const res = await axios.get('/rahnoma-mamlats/print', {
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
    async UpdateRahnomaMamlat(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/rahnoma-mamlats/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/rahnoma-mamlats/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteRahnomaMamlat(id) {
      try {
        await axios.delete(`/rahnoma-mamlats/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ Hosppitals ============================ */
    async FetchHosppitalsData(params = {}) {
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

        const { data: res } = await axios.get(`hosppitals`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Hosppitals');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hosppitals = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchHosppitalFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/hosppitals/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchHosppitalData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/hosppitals/${id}`);
        this.Hosppital = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Hosppital = {};
      }
    },

    async CreateHosppital(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/hosppitals`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenHosppitalsPrintView(payload = {}) {
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
        file_name: file_name || 'hosppitals-print',
      };

      const res = await axios.get('/hosppitals/print', {
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
    async UpdateHosppital(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/hosppitals/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/hosppitals/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteHosppital(id) {
      try {
        await axios.delete(`/hosppitals/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },

    /* ============================ WorkShops ============================ */
    async FetchWorkShopsData(params = {}) {
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

        const { data: res } = await axios.get(`work-shops`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'WorkShops');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.WorkShops = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchWorkShopFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/work-shops/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchWorkShopData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/work-shops/${id}`);
        this.WorkShop = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.WorkShop = {};
      }
    },

    async CreateWorkShop(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/work-shops`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('ریکارډ په بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ اضافه نه شو'));
        throw e;
      }
    },
    async OpenWorkShopsPrintView(payload = {}) {
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
        file_name: file_name || 'work-shops-print',
      };

      const res = await axios.get('/work-shops/print', {
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
    async UpdateWorkShop(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/work-shops/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/work-shops/${id}`, data);
        }
        toast.success('ریکارډ په بریالیتوب سره بدل شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ بدل نه شو'));
        throw e;
      }
    },

    async DeleteWorkShop(id) {
      try {
        await axios.delete(`/work-shops/${id}`);
        toast.success('ریکارډ په بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ حذف نه شو'));
      }
    },
    /* ============================ Delete Pictures ============================ */

    async DeleteSchoolPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/schools/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteHadoBusStopPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/hado-bus-stops/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteTransportCompanyPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/transport-companies/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteCarSalesCompanyPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/car-sales-companies/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteParkingPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/parkings/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteBankPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/banks/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteApartmentPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/apartments/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteRahnomaMamlatPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/rahnoma-mamlats/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteHosppitalPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/hosppitals/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },

    async DeleteWorkShopPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/work-shops/${id}/picture`);
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
