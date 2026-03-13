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
function errText(e, fallback = 'بغضی شیی ') {
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

    /* ============================ Schools ============================ */
    async FetchSchoolsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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

    async FetchSchoolData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/schools/${id}`);
        this.School = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Schools = {};
      }
    },

    async CreateSchool(formData) {
      try {
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/schools`, formData);
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

    async DeleteSchool(id) {
      try {
        await axios.delete(`/schools/${id}`);
        toast.success('ریکارډ فساد بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ فساد حذف نشو'));
      }
    },

    /* ============================ HadoBusStops ============================ */
    async FetchHadoBusStopsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/hado-bus-stops`, formData);
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

    async DeleteHadoBusStop(id) {
      try {
        await axios.delete(`/hado-bus-stops/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ TransportCompanies ============================ */
    async FetchTransportCompaniesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/transport-companies`, formData);
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

    async DeleteTransportCompany(id) {
      try {
        await axios.delete(`/transport-companies/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ CarSalesCompanies ============================ */
    async FetchCarSalesCompaniesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/car-sales-companies`, formData);
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

    async DeleteCarSalesCompany(id) {
      try {
        await axios.delete(`/car-sales-companies/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Parkings ============================ */
    async FetchParkingsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/parkings`, formData);
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

    async DeleteParking(id) {
      try {
        await axios.delete(`/parkings/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Banks ============================ */
    async FetchBanksData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/banks`, formData);
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

    async DeleteBank(id) {
      try {
        await axios.delete(`/banks/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Apartments ============================ */
    async FetchApartmentsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/apartments`, formData);
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

    async DeleteApartment(id) {
      try {
        await axios.delete(`/apartments/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ RahnomaMamlats ============================ */
    async FetchRahnomaMamlatsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/rahnoma-mamlats`, formData);
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

    async DeleteRahnomaMamlat(id) {
      try {
        await axios.delete(`/rahnoma-mamlats/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ Hosppitals ============================ */
    async FetchHosppitalsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/hosppitals`, formData);
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

    async DeleteHosppital(id) {
      try {
        await axios.delete(`/hosppitals/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },

    /* ============================ WorkShops ============================ */
    async FetchWorkShopsData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

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
        // let the browser set proper multipart boundary
        setContentType(null);
        const res = await axios.post(`/work-shops`, formData);
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

    async DeleteWorkShop(id) {
      try {
        await axios.delete(`/work-shops/${id}`);
        toast.success('ریکارډ  بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ریکارډ  حذف نشو'));
      }
    },
  },
});