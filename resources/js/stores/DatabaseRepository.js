// src/stores/DatabaseRepository.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { buildListParams, applyPaginatedResponse } from '@/utils/listHelpers';
// === ZONE <-> HAWZA mapping (from your picture) ===
const ZONE_HAWZA_MAP = {
  'لومړی زون': ['حوزه ۳', 'حوزه ۷', 'حوزه ۶', 'حوزه ۸', 'حوزه ۱۸'],
  'دوهم زون': ['حوزه ۸', 'بګرامی ولسوالی', 'حوزه ۱۲', 'حوزه ۱۹', 'حوزه ۲۱'],
  'دریم زون': ['حوزه ۱', 'حوزه ۲', 'حوزه ۱۶', 'حوزه ۹', 'حوزه ۱۰'],
  'څلورم زون': ['حوزه ۵', 'حوزه ۱۱', 'حوزه ۱۵', 'حوزه ۴', 'حوزه ۱۷'],
};
// Build reverse map: Hawza -> Zone
const HAWZA_ZONE_MAP = Object.entries(ZONE_HAWZA_MAP).reduce((acc, [zone, list]) => {
  (list || []).forEach((hz) => {
    acc[hz] = zone;
  });
  return acc;
}, {});
// Accept JSON by default; for FormData we’ll clear it dynamically.
axios.defaults.headers.common['Accept'] = 'application/json';

function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
  } else {
    axios.defaults.headers.common['Content-Type'] = type;
  }
}

export const useDatabaseRepository = defineStore('DatabaseRepository', {
  state: () => ({
    router: useRouter(),

    // paging / search
    page: 1,
    itemsPerPage: 10,
    totalItems: 0,
    Search: '',
    clearSearch: '',

    // flags
    isLoading: false,
    loading: false,
    error: null,

    // ==== DATA BUCKETS ====
    // Events
    Events: reactive([]),
    Event: reactive({}),
    // options
    eventTypeOptions: ['جنایی', 'سیاسی', 'ترافیکی', 'طبیعی', 'داسی نور'],
    janiTalafatOptions: ['وژل شوی', 'ټپي شوی', 'نور'],
    maliTalafatOptions: ['هو', 'نه', 'نور'],

    // Resources
    Resources: reactive([]),
    Resource: reactive({}),
    resource: reactive({}),

    // Informations
    Informations: reactive([]),
    Information: reactive({}),

    // Dropdowns
    resedints: reactive([]),
    officeemployees: reactive([]),
    ZoneEmployees: reactive([]),
    resedintscode: reactive([]),
    officeemployeescode: reactive([]),
    ZoneEmployeesCode: reactive([]),
    resourcecode: reactive({}),

    // Others you used elsewhere
    CaptiveDeliveries: reactive([]),
    CaptiveDelivery: reactive({}),

    // // inside state()
    financePrefill: null,

    // options
    activityOptions: ['فعال', 'غیر فعال'],
    halatMadaniOptions: ['مجرد', 'متاهل'],
    seyasiOptions: [
      'ساعین بالفساد',
      'شر و فساد',
      ' جاسوسی',
      'جعل او تزویر',
      'انټرنټی جرایم',
      'هدفی وژنه',
      'د اصلحو قاچاق',
      'د تورو پیسو سپینول چاپ او انتقال',
      'رشوت او اختلاص',
      'مشکوک',
      'سیاسی احزاب',
      'تبلیغاتی جګره',
      'عیسویت او یهودیت',
      'باجګیری',
      'ثبوتاج اقتصادی',
      'مشکوک حرکات ',
    ],
    jenayeeOptions: [
      'قتل',
      'اختطاف',
      'سرقت',
      'نشه یی توکی',
      'اخلاقی فساد',
      'جعل او تزویر',
      'اداری فساد',
      'غیر مسول مسلح',
      'غیر قانونی کرنه',
      'متفرقه',
    ],
    infoCategoryOptions: ['الف', 'ب', 'ج'],
    captiveResons: [
      // سیاسی
      'ساعین بالفساد',
      'شر و فساد',
      'جاسوسی',
      'جعل او تزویر',
      'انټرنټی جرایم',
      'هدفی وژنه',
      'د اصلحو قاچاق',
      'د تورو پیسو سپینول چاپ او انتقال',
      'رشوت او اختلاص',
      'مشکوک',
      'سیاسی احزاب',
      'تبلیغاتی جګره',
      'عیسویت او یهودیت',
      'باجګیری',
      'ثبوتاج اقتصادی',
      'مشکوک حرکات',

      // جنایی
      'قتل',
      'اختطاف',
      'سرقت',
      'نشه یی توکی',
      'اخلاقی فساد',
      'اداری فساد',
      'غیر مسول مسلح',
      'غیر قانونی کرنه',
      'متفرقه',
    ],

    // selection
    selectedItems: [],
    itemKey: 'id',
  }),

  actions: {
    /* ========= Zone/Hawza helpers (used by Vue) ========= */
    getZoneOptions() {
      return Object.keys(ZONE_HAWZA_MAP);
    },
    getAllHawzaOptions() {
      return Object.values(ZONE_HAWZA_MAP).flat();
    },
    getHawzaOptionsForZone(zone) {
      if (!zone) return this.getAllHawzaOptions(); // allow picking hawza first
      return ZONE_HAWZA_MAP[zone] ?? [];
    },
    getZoneForHawza(hawza) {
      if (!hawza) return undefined;
      return HAWZA_ZONE_MAP[hawza];
    },
    hawzaIsValidForZone(zone, hawza) {
      if (!zone || !hawza) return true; // treat empty as valid if optional
      return (ZONE_HAWZA_MAP[zone] ?? []).includes(hawza);
    },
    /** Normalize after user changes Zone:
     *  - if hawza doesn’t belong to new zone, clear it
     */
    normalizeAfterZoneChange(currentZone, currentHawza) {
      if (!currentZone) return { zone: '', hawza: '' };
      const allowed = new Set(ZONE_HAWZA_MAP[currentZone] || []);
      return { zone: currentZone, hawza: allowed.has(currentHawza || '') ? currentHawza : '' };
    },
    /** Normalize after user changes Hawza:
     *  - auto-select the correct zone for that hawza
     */
    normalizeAfterHawzaChange(currentHawza, currentZone) {
      if (!currentHawza) return { zone: currentZone || '', hawza: '' };
      const z = HAWZA_ZONE_MAP[currentHawza];
      return { zone: z || currentZone || '', hawza: currentHawza };
    },
    // ---------------------- COMMON ----------------------
    getCurrentDate() {
      const d = new Date();
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${y}-${m}-${day}`;
    },

    //
    setFinancePrefill(payload) {
      this.financePrefill = payload || null;
    },
    clearFinancePrefill() {
      this.financePrefill = null;
    },

    // ---------------------- OfficeEmployee ----------------------
    async GetOfficeEmployee() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/office-employees`);
        this.officeemployees = data.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('د دفتر کارمند اخیستل نا ممکن دی');
      } finally {
        this.loading = false;
      }
    },
    async GetOfficeEmployeeCode() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/office-employees`);
        this.officeemployeescode = data.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('د دفتر کارمند اخیستل نا ممکن دی');
      } finally {
        this.loading = false;
      }
    },

    // ---------------------- Resources ----------------------
    async GetResource() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/resources`);
        this.resource = data.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('د منابع د کود اخیستل ناممکن دی');
      } finally {
        this.loading = false;
      }
    },
    async GetResourceCode() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/resources`);
        this.resourcecode = data.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('د منابع د کود اخیستل ناممکن دی');
      } finally {
        this.loading = false;
      }
    },

    async FetchResourcesData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: true,
          defaultDateField: 'resource_saved_date',
        });

        const res = await axios.get('/resources', {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Resources');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Resources = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchResourceDateOptions(params = {}) {
      // params: { date_field: 'resource_saved_date' }
      return await axios.get('/resources/date-options', { params });
    },
    async FetchResourceData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/resources/${id}`);
        this.Resource = data.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Resource = {};
        toast.error('نشو کولای اوس منابع لود وشیو');
      }
    },

    async CreateResource(formData) {
      try {
        setContentType(null); // let browser set multipart boundary
        const { data: res } = await axios.post(`/resources`, formData);
        toast.success('منبع اضافه شوی بریالیتوب سره', {
          autoClose: 1000,
        });
        await this.FetchResourcesData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
        return res?.data ?? res;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('منبع اضافه نشو بیا کوشش وکی');
        throw e;
      }
    },

    async UpdateResource(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          ({ data: res } = await axios.post(`/resources/${id}`, data));
        } else {
          setContentType('application/json');
          ({ data: res } = await axios.put(`/resources/${id}`, data));
        }
        toast.success('منبع بریالیتوب سره تغییر شو', {
          autoClose: 1000,
        });
        await this.FetchResourcesData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
        return res?.data ?? res;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('د منبع تغییر ناکام شو بیا کوشش وکی');
        throw e;
      }
    },

    async DeleteResource(id) {
      try {
        await axios.delete(`/resources/${id}`);
        toast.success('منبع برالیتوب سره حذف شو', {
          autoClose: 1000,
        });
        await this.FetchResourcesData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('منبع حذف کول ناکام شو بیا کوشش وکی');
      }
    },

    // ====================== EVENTS  ======================
    async FetchEventDateOptions(params) {
      return axios.get('/events/date-options', { params });
    },

    async FetchEventsData(params = {}) {
      this.loading = true;

      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: true,
          defaultDateField: 'event_date',
        });

        const res = await axios.get('/events', {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Events');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Events = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchEventData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/events/${id}`);
        this.Event = data.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Event = {};
        toast.error('و پیشی احیستل نامامه وو');
      }
    },

    async CreateEvent(formData) {
      try {
        setContentType(null); // ✅ same as Informations
        const { data } = await axios.post(`/events`, formData);
        toast.success('پیښه بریالیتوب سره اضافه شوه ', {
          autoClose: 1000,
        });
        await this.FetchEventsData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('د پیښی ثبت ناکامه وو');
        throw e;
      }
    },

    async UpdateEvent(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null); // ✅ same as Informations
          ({ data: res } = await axios.post(`/events/${id}`, data));
        } else {
          setContentType('application/json');
          ({ data: res } = await axios.put(`/events/${id}`, data));
        }
        toast.success('پیښه برالیتوب سره تغییر شوه', {
          autoClose: 1000,
        });
        await this.FetchEventsData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
        return res?.data ?? res;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('پیښه تغییر نشوه');
        throw e;
      }
    },

    async DeleteEvent(id) {
      try {
        await axios.delete(`/events/${id}`);
        toast.success('پیښه برالیتوب سره حذف شو', {
          autoClose: 1100,
        });
        await this.FetchEventsData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('پیښه حذف نشوه');
      }
    },

    // ===== Event Documents (parallel to Information docs) =====
    async FetchEventDocuments(eventId) {
      const { data } = await axios.get(`/events/${eventId}/documents`);
      return data.data ?? data;
    },

    async UploadEventDocuments(eventId, files) {
      const fd = new FormData();
      [...files].forEach((f) => fd.append('files[]', f));
      setContentType(null);
      const { data } = await axios.post(`/events/${eventId}/documents`, fd);
      toast.success('اسناد اپلوډ شوه', { autoClose: 1100 });
      return data.data ?? data;
    },

    async DeleteEventDocument(eventId, docId) {
      await axios.delete(`/events/${eventId}/documents/${docId}`);
      toast.success('اسناد حذف شوه', { autoClose: 1100 });
    },

    async DownloadEventZip(id, filename) {
      const res = await axios.get(`/events/${id}/documents/zip`, {
        responseType: 'blob',
      });
      const blob = new Blob([res.data], { type: 'application/zip' });
      const url = window.URL.createObjectURL(blob);
      const safe = (s) =>
        String(s || '')
          .toLowerCase()
          .replace(/\s+/g, '_')
          .replace(/[^a-z0-9._-]/g, '');
      const a = document.createElement('a');
      a.href = url;
      a.download = safe(filename || `documents_${id}.zip`);
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    },

    // ====================== INFORMATIONS ======================
    // ✅ Informations date-options
    async FetchInformationDateOptions(params = {}) {
      return await axios.get('/informations/date-options', { params });
    },

    async FetchInformationsData(params = {}) {
      this.loading = true;

      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: true,
          defaultDateField: 'information_saved_date',
        });

        const res = await axios.get('/informations', {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Informations');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Informations = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchInformationData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/informations/${id}`);
        this.Information = data.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Information = {};
        toast.error('و دیتا احیستل نامامه وو');
      }
    },
    async CreateInformation(formData, onProgress) {
      try {
        // Let the browser set multipart boundary
        delete axios.defaults.headers.common['Content-Type'];

        const { data } = await axios.post(`/informations`, formData, {
          onUploadProgress: (e) => {
            if (!e.total) return;
            const pct = Math.round((e.loaded * 100) / e.total);
            if (typeof onProgress === 'function') onProgress(pct);
          },
        });

        toast.success('اطلاعات اضافه شو', { autoClose: 1000 });
        await this.FetchInformationsData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
        return data?.data ?? data;
      } catch (e) {
        toast.error('اطلاعات اضافه نشو');
        throw e;
      }
    },

    // --- UPDATE with progress ---
    async UpdateInformation(id, formDataOrJson, onProgress) {
      try {
        let res;

        if (formDataOrJson instanceof FormData) {
          formDataOrJson.append('_method', 'PUT');
          delete axios.defaults.headers.common['Content-Type'];

          ({ data: res } = await axios.post(`/informations/${id}`, formDataOrJson, {
            onUploadProgress: (e) => {
              if (!e.total) return;
              const pct = Math.round((e.loaded * 100) / e.total);
              if (typeof onProgress === 'function') onProgress(pct);
            },
          }));
        } else {
          axios.defaults.headers.common['Content-Type'] = 'application/json';
          ({ data: res } = await axios.put(`/informations/${id}`, formDataOrJson));
        }

        toast.success('اطلاعات تغییر شو', { autoClose: 1000 });
        await this.FetchInformationsData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
        return res?.data ?? res;
      } catch (e) {
        toast.error('اطلاعات اضافه نشړ');
        throw e;
      }
    },

    async DeleteInformation(id) {
      try {
        await axios.delete(`/informations/${id}`);
        toast.success('Information deleted', { autoClose: 1100 });
        await this.FetchInformationsData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('Delete information failed');
      }
    },

    // Documents sub-routes
    async FetchInformationDocuments(infoId) {
      const { data } = await axios.get(`/informations/${infoId}/documents`);
      return data.data ?? data;
    },

    async UploadInformationDocuments(infoId, files) {
      const fd = new FormData();
      [...files].forEach((f) => fd.append('files[]', f));
      setContentType(null);
      const { data } = await axios.post(`/informations/${infoId}/documents`, fd);
      toast.success('اسناد اضافه شو', { autoClose: 1100 });
      return data.data ?? data;
    },

    async DeleteInformationDocument(infoId, docId) {
      await axios.delete(`/informations/${infoId}/documents/${docId}`);
      toast.success('اسناد حذف شو', { autoClose: 1100 });
    },

    // ZIP download (the button calls this)
    async DownloadInformationZip(id, filename) {
      const res = await axios.get(`/informations/${id}/documents/zip`, {
        responseType: 'blob',
      });

      const blob = new Blob([res.data], { type: 'application/zip' });
      const url = window.URL.createObjectURL(blob);

      const safe = (s) =>
        String(s || '')
          .toLowerCase()
          .replace(/\s+/g, '_')
          .replace(/[^a-z0-9._-]/g, '');

      const a = document.createElement('a');
      a.href = url;
      a.download = safe(filename || `documents_${id}.zip`);
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    },
    // ====================== CAPTIVE DELIVERIES ======================
    // --- Captive Delivery list fetch (you already have this) ---
    async FetchCaptiveDeliveriesData(params = {}) {
      this.loading = true;

      try {
        setContentType('application/json');

        const queryParams = buildListParams(this, params, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

        const res = await axios.get('/captive-deliveries', {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'CaptiveDeliveries');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.CaptiveDeliveries = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async FetchCaptiveDeliveryData(id) {
      this.loading = true;
      try {
        const { data } = await axios.get(`captive-deliveries/${id}`);

        // 🔹 Support BOTH shapes: { data: {...} } OR {...}
        const record = data?.data ?? data;

        this.CaptiveDelivery = record; // 👈 store a SINGLE object
        this.totalItems = 1;
      } finally {
        this.loading = false;
      }
    },
    async DeleteCaptiveDelivery(id) {
      try {
        await axios.delete(`/captive-deliveries/${id}`);
        toast.success('Delevery Captive deleted', { autoClose: 1100 });
        await this.FetchCaptiveDeliveriesData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error('Delete Deleviry Captive failed');
      }
    },

    // --- CREATE with progress ---
    async CreateCaptiveDelivery(formData, onProgress) {
      try {
        // Let the browser set multipart boundary
        delete axios.defaults.headers.common['Content-Type'];

        const { data } = await axios.post(`/captive-deliveries`, formData, {
          onUploadProgress: (e) => {
            if (!e.total) return;
            const pct = Math.round((e.loaded * 100) / e.total);
            if (typeof onProgress === 'function') onProgress(pct);
          },
        });

        toast.success('تسلیم شوی بندي اضافه شوه', { autoClose: 1000 });
        await this.FetchCaptiveDeliveriesData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
        return data?.data ?? data;
      } catch (e) {
        toast.error('تسلیم شوی بندي اضافه نشوه');
        throw e;
      }
    },

    // --- UPDATE with progress ---
    async UpdateCaptiveDelivery(id, formDataOrJson, onProgress) {
      try {
        let res;

        if (formDataOrJson instanceof FormData) {
          formDataOrJson.append('_method', 'PUT');
          delete axios.defaults.headers.common['Content-Type'];

          ({ data: res } = await axios.post(`/captive-deliveries/${id}`, formDataOrJson, {
            onUploadProgress: (e) => {
              if (!e.total) return;
              const pct = Math.round((e.loaded * 100) / e.total);
              if (typeof onProgress === 'function') onProgress(pct);
            },
          }));
        } else {
          axios.defaults.headers.common['Content-Type'] = 'application/json';
          ({ data: res } = await axios.put(`/captive-deliveries/${id}`, formDataOrJson));
        }

        toast.success('تسلیم شوی بندي تغییر شو', { autoClose: 1000 });
        await this.FetchCaptiveDeliveriesData({
          page: this.page,
          perPage: this.itemsPerPage,
        });
        return res?.data ?? res;
      } catch (e) {
        toast.error('تسلیم شوی بتدي تعییر نشو');
        throw e;
      }
    },

    // --- ZIP download (already done for other modules; reuse for Captive) ---
    async DownloadCaptiveDeliveryZip(id, filename) {
      const res = await axios.get(`/captive-deliveries/${id}/documents/zip`, {
        responseType: 'blob',
      });
      const blob = new Blob([res.data], { type: 'application/zip' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = String(filename || `documents_${id}.zip`)
        .toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9._-]/g, '');
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    },
    // ✅ Add THESE actions inside: actions: { ... }  (no design changes — same HTML/CSS you provided)

    escapeHtml(v) {
      return String(v ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    },

    buildInformationFormHTML(data = {}) {
      // ✅ helper: safe text
      const t = (k) => this.escapeHtml(data?.[k] ?? '');
      // ✅ logo: you can pass data.logo_url OR keep LOGO_URL_HERE
      const logo = data?.logo_url ? String(data.logo_url) : 'LOGO_URL_HERE';

      return `<!DOCTYPE html>
<html lang="ps" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <title>د اطلاعات ثبت فورم</title>

    <style>
      @page {
        size: A4;
        margin: 18mm;
      }

      body {
        font-family: 'Tahoma', 'Arial', sans-serif;
        font-size: 12px;
        color: #000;
      }

      .page {
        padding: 10px;
      }

      /* ===== HEADER ===== */
      .header {
        display: grid;
        grid-template-columns: 1fr 120px 1fr;
        align-items: center;
        column-gap: 110px;
        margin-bottom: 8px;
      }

      .header-block {
        line-height: 1.6;
        font-weight: bold;
        max-width: 220px;
      }

      .header-block:first-child {
        text-align: center;
        justify-self: end;
      }

      .header-block:last-child {
        text-align: center;
        justify-self: start;
      }

      .header-center {
        text-align: center;
      }

      .header-center img {
        width: 95px;
        height: 95px;
        display: block;
        margin: 0 auto;
      }

      .form-title {
        text-align: right;
        font-weight: bold;
        margin: 8px 0;
      }

      /* ===== INFO ROW ===== */
      .info-row {
        border: 1px solid #000;
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        text-align: center;
        font-weight: bold;
      }

      .info-row div {
        border-left: 1px solid #000;
        padding: 4px;
      }

      /* ===== TABLE ===== */
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
      }

      th,
      td {
        border: 1px solid #000;
        padding: 4px;
        height: 26px;
        text-align: center;
        vertical-align: middle;
      }

      th {
        font-weight: bold;
      }

      /* ===== SECTION TITLE ===== */
      .section-title {
        margin-top: 8px;
        font-weight: bold;
      }

      /* ===== LINED AREA ===== */
      .lined-box {
        height: 240px;
        background-image: repeating-linear-gradient(
          to bottom,
          transparent,
          transparent 18px,
          #000 19px
        );
        border-bottom: 1px solid black;
      }
      .lined-box2 {
        height: 50px;
        background-image: repeating-linear-gradient(
          to bottom,
          transparent,
          transparent 18px,
          #000 19px
        );
        border-bottom: 1px solid black;
      }

      /* ===== FOOTER ===== */
      .footer {
        margin-top: 10px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
      }

      .footer-box {
        border: 1px solid #000;
        height: 40px;
        padding: 4px;
        font-weight: bold;
      }

      .note {
        margin-top: 12px;
        padding-top: 12px;
        padding-right: 6px;
        font-size: 11px;
        font-weight: bold;
        border-top: 4px solid black;
      }
      .borderall {
        border: 3px solid black;
        padding-left: 6px;
        padding-right: 6px;
      }

      /* ✅ Fillable text inside lines (no design change; just makes text show nicely) */
      .fill-text {
        font-weight: normal;
        margin-top: 6px;
        white-space: pre-wrap;
        line-height: 1.6;
      }
    </style>
  </head>

  <body>
    <div class="page">
      <!-- HEADER -->
      <div class="borderall">
        <div class="header">
          <div class="header-block">
            د ریاست عمومی استخبارات<br />
            د استخباراتو لوی ریاست<br />
            ریاست استخبارات ۶۹<br />
            مدیریت ۳۳۳
          </div>

          <div class="header-center">
            <img src="${logo}" alt="logo" />
            <div>۳۳۳</div>
          </div>

          <div class="header-block">
            د ریاست عمومی استخبارات<br />
            د استخباراتو لوی ریاست<br />
            د استخباراتو ۶۹ ریاست<br />
            مدیریت ۳۳۳
          </div>
        </div>

        <div class="form-title">د اطلاعات ثبت فورم:</div>
        <div class="form-title">شمیره: ( ${t('form_number')} )</div>

        <table>
          <tr>
            <th>د منبع کوډ</th>
            <th>د کارمند کود</th>
            <th>د اطلاع د ثبت نېټه</th>
            <th>د اطلاع د ثبت ساعت</th>
            <th>د موثقیت درجه</th>
            <th>د اعتماد میزان</th>
            <th>د اطلاع نوعیت</th>
            <th>همغژی</th>
          </tr>
          <tr>
            <td>${t('source_code')}</td>
            <td>${t('employee_code')}</td>
            <td>${t('info_date')}</td>
            <td>${t('info_time')}</td>
            <td>${t('credibility_degree')}</td>
            <td>${t('trust_level')}</td>
            <td>${t('info_type')}</td>
            <td>${t('hamghaji')}</td>
          </tr>

          <tr>
            <th colspan="8">د متهم شهرت</th>
          </tr>
          <tr>
            <th>نوم</th>
            <th>مستعار نوم</th>
            <th>د پلار نوم</th>
            <th>د نیکه نوم</th>
            <th>مدنی حالت</th>
            <th>دنده</th>
            <th colspan="2">عمر</th>
          </tr>
          <tr>
            <td>${t('name')}</td>
            <td>${t('alias_name')}</td>
            <td>${t('father_name')}</td>
            <td>${t('grandfather_name')}</td>
            <td>${t('civil_status')}</td>
            <td>${t('job')}</td>
            <td colspan="2">${t('age')}</td>
          </tr>

          <tr>
            <th colspan="4">فعلاً د هستوګنې ځای</th>
            <th colspan="4">اصلاً د هستوګنې ځای</th>
          </tr>
          <tr>
            <th>ولایت</th>
            <th>ولسوالی / ناحیه</th>
            <th>کلی / کوڅه</th>
            <th>ولایت</th>
            <th>ولسوالی / ناحیه</th>
            <th>کلی / کوڅه</th>
            <th colspan="2">مربوطه ولایت</th>
          </tr>
          <tr>
            <td>${t('current_province')}</td>
            <td>${t('current_district')}</td>
            <td>${t('current_village')}</td>
            <td>${t('original_province')}</td>
            <td>${t('original_district')}</td>
            <td>${t('original_village')}</td>
            <td colspan="2">${t('related_province')}</td>
          </tr>
        </table>

        <!-- ✅ Evidence (د اطلاع شواهد) -->
        <div class="section-title">
          د اطلاع شواهد
          <div class="lined-box2"></div>
          <div class="fill-text">${t('evidence_text')}</div>
        </div>

        <!-- ✅ Tafseel -->
        <div class="section-title">تفصیل:</div>
        <div class="lined-box"></div>
        <div class="fill-text">${t('tafseel_text')}</div>

        <!-- ✅ Manager comment -->
        <div class="section-title">
          د مدیر نظر
          <div class="lined-box2"></div>
          <div class="fill-text">${t('manager_note')}</div>
        </div>
      </div>

      <div class="note">پاملرنه: د دې سند افشا کول جرم بلل کېږي.</div>
    </div>
  </body>
</html>`;
    },

    /** ✅ PDF (via print dialog) — call this from Vue button */
    DownloadInformationFormPdf(data = {}) {
      const html = this.buildInformationFormHTML(data);
      const w = window.open('', '_blank', 'width=900,height=1200');
      if (!w) {
        toast.error('Popup بند دی، لطفاً اجازه ورکړئ');
        return;
      }
      w.document.open();
      w.document.write(html);
      w.document.close();

      // wait a bit for images/fonts
      setTimeout(() => {
        w.focus();
        w.print();
      }, 400);
    },

    /** ✅ Word download (.doc) — same HTML */
    DownloadInformationFormWord(data = {}, filename = 'information_form.doc') {
      const html = this.buildInformationFormHTML(data);
      const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
      const url = window.URL.createObjectURL(blob);

      const safe = (s) =>
        String(s || 'information_form.doc')
          .replace(/\s+/g, '_')
          .replace(/[^\w.\-]/g, '');

      const a = document.createElement('a');
      a.href = url;
      a.download = safe(filename);
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    },
    // this is for the event form download
    // ====================== EVENT EXPORT (PDF + WORD) ======================

    buildEventFormHTML(data = {}) {
      const esc = (v) =>
        String(v ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');

      // keep the same logo pattern you use
      const logo = data?.logo_url ? String(data.logo_url) : 'LOGO_URL_HERE';

      return `<!DOCTYPE html>
<html lang="ps" dir="rtl">
<head>
<meta charset="UTF-8" />
<title>د پیښی راپور</title>
<style>
  @page { size: A4; margin: 18mm; }
  body { font-family: Tahoma, Arial, sans-serif; font-size: 12px; color:#000; }

  .header{
    display:grid;
    grid-template-columns: 1fr 120px 1fr;
    align-items:center;
    column-gap: 110px;
    margin-bottom: 8px;
  }
  .header-block{ line-height:1.6; font-weight:bold; text-align:center; }
  .header-center{ text-align:center; }
  .header-center img{ width: 95px; height:95px; display:block; margin:0 auto; }

  .borderall{ border:3px solid #000; padding: 6px; }

  table{ width:100%; border-collapse:collapse; margin-top:6px; }
  th, td{ border:1px solid #000; padding:4px; height:26px; text-align:center; vertical-align:middle; }
  th{ font-weight:bold; }

  .section-title{ margin-top:10px; font-weight:bold; }
  .lined-box{
    height: 260px;
    background-image: repeating-linear-gradient(to bottom, transparent, transparent 18px, #000 19px);
    border-bottom: 1px solid #000;
  }
  .lined-box2{
    height: 60px;
    background-image: repeating-linear-gradient(to bottom, transparent, transparent 18px, #000 19px);
    border-bottom: 1px solid #000;
  }
  .fill-text{ font-weight:normal; margin-top:6px; white-space:pre-wrap; line-height:1.6; }
</style>
</head>
<body>
  <div class="borderall">
    <div class="header">
      <div class="header-block">
        د ریاست عمومی استخبارات<br/>
        د استخباراتو لوی ریاست<br/>
        ریاست استخبارات ۶۹<br/>
        مدیریت ۳۳۳
      </div>

      <div class="header-center">
        <img src="${logo}" alt="logo" />
        <div>۳۳۳</div>
      </div>

      <div class="header-block">
        د ریاست عمومی استخبارات<br/>
        د استخباراتو لوی ریاست<br/>
        د استخباراتو ۶۹ ریاست<br/>
        مدیریت ۳۳۳
      </div>
    </div>

    <div style="font-weight:bold; margin:8px 0;">د پیښی د ثبت فورم:</div>
    <div style="font-weight:bold; margin:8px 0;">شمیره: ( ${esc(data.id)} )</div>

    <table>
    <tr>
      <th colspan="2">د منبع کوډ</th>
      <th colspan="2">د کارمند کوډ</th>
      <th colspan="2">د پیښی نیټه</th>
      <th colspan="2">د پیښی ساعت</th>
      <th colspan="4">د پیښی نوعیت</th>
    </tr>

    <tr>
      <td colspan="2">${esc(data.source_code)}</td>
      <td colspan="2">${esc(data.employee_code)}</td>
      <td colspan="2">${esc(data.event_date)}</td>
      <td colspan="2">${esc(data.event_hour)}</td>
      <td colspan="4">${esc(data.event_specefic_type)}</td>
    </tr>

    <tr>
      <th colspan="4">هدف کی بود / مکان بود</th>
      <th colspan="4">کی جرم را انجام داده</th>
      <th colspan="4">ترسره شوی همغږی</th>
    </tr>

    <tr>
      <td colspan="4">${esc(data.target_person)}</td>
      <td colspan="4">${esc(data.criminal)}</td>
      <td colspan="4">${esc(data.hamghagi)}</td>
    </tr>
    <tr>
      <th colspan="6">د پیښی ځای</th>
      <th colspan="6">تلفات</th>
    </tr>

    <tr>
      <!-- Place sub headers (6 columns total) -->
      <th colspan="2">زون</th>
      <th colspan="2">حوزه</th>
      <th colspan="2">محل مشخص</th>

      <!-- Casualties sub headers (6 columns total) -->
      <th colspan="2">تلفات مالی</th>
      <th colspan="2">کشته</th>
      <th colspan="2">زخمی</th>
    </tr>

    <tr>
      <!-- Place values -->
      <td colspan="2">${esc(data.zone)}</td>
      <td colspan="2">${esc(data.hawza)}</td>
      <td colspan="2">${esc(data.specific_place)}</td>

      <!-- Casualties values -->
      <td colspan="2">${esc(data.talafat_mali)}</td>
      <td colspan="2">${esc(data.killed)}</td>
      <td colspan="2">${esc(data.injured)}</td>
    </tr>
    </table>

    <div class="section-title">اسناد / شواهد:</div>
    <div class="lined-box2"></div>
    <div class="fill-text">${esc(data.evidence_text)}</div>
    
    <div class="section-title">تفصیل:</div>
    <div class="lined-box"></div>
    <div class="fill-text">${esc(data.tafseel_text)}</div>

  </div>

  <div style="margin-top:12px; font-weight:bold; border-top:4px solid #000; padding-top:10px;">
    پاملرنه: د دې سند افشا کول جرم بلل کېږي.
  </div>
</body>
</html>`;
    },

    DownloadEventFormPdf(data = {}) {
      const html = this.buildEventFormHTML(data);
      const w = window.open('', '_blank', 'width=900,height=1200');
      if (!w) {
        toast.error('Popup بند دی، لطفاً اجازه ورکړئ');
        return;
      }
      w.document.open();
      w.document.write(html);
      w.document.close();
      setTimeout(() => {
        w.focus();
        w.print();
      }, 400);
    },

    DownloadEventFormWord(data = {}, filename = 'event_form.doc') {
      const html = this.buildEventFormHTML(data);
      const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
      const url = window.URL.createObjectURL(blob);

      const safe = (s) =>
        String(s || 'event_form.doc')
          .replace(/\s+/g, '_')
          .replace(/[^\w.\-]/g, '');

      const a = document.createElement('a');
      a.href = url;
      a.download = safe(filename);
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    },
  },
});
