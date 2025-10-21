// src/stores/EmployeeRepository.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';
// === ZONE <-> HAWZA mapping (from your picture) ===
const ZONE_HAWZA_MAP = {
  'لومړی زون': ['حوزه ۳', 'حوزه ۷', 'حوزه ۶', 'حوزه ۸', 'حوزه ۱۸'],
  'دوهم زون': ['حوزه ۸', 'بګرامی ولسوالی', 'حوزه ۱۲', 'حوزه ۱۹', 'حوزه ۲۱'],
  'دریم زون': ['حوزه ۱', 'حوزه ۲', 'حوزه ۱۶', 'حوزه ۹', 'حوزه ۱۰'],
  'څلورم زون': ['حوزه ۵', 'حوزه ۱۱', 'حوزه ۱۵', 'حوزه ۴', 'حوزه ۱۷'],
};
// === Jahadi Mawqaf options ===
const JAHADI_MAWQAFA_OPTIONS = ['فرد', 'سرګروف', 'ډلګی مشر', 'محاظ مشر'];
// === Halat Madani (Marital Status) options ===
const HALAT_MADANI_OPTIONS = ['مجرد', 'متاهل'];

// Build reverse map: Hawza -> Zone
const HAWZA_ZONE_MAP = Object.entries(ZONE_HAWZA_MAP).reduce((acc, [zone, list]) => {
  (list || []).forEach((hz) => {
    acc[hz] = zone;
  });
  return acc;
}, {});

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

export let useEmployeOfficeRepository = defineStore('EmployeOfficeRepository', {
  state() {
    return {
      router: useRouter(),
      page: 1,
      itemsPerPage: 10,

      Owner: reactive([]),
      Owners: reactive([]),

      resedints: reactive([]),
      officeemployees: reactive([]),

      Employee: reactive([]),
      Employees: reactive([]),

      Resedint: reactive([]),
      Resedints: reactive([]),

      ZoneEmployee: reactive([]),
      ZoneEmployees: reactive([]),

      Customer: reactive([]),
      Customers: reactive([]), // fixed

      Users: reactive([]),
      User: reactive([]),
      // === Jahadi Mawqaf options ===

      createDailog: false,
      updateDailog: false,
      createDailogEmployee: false,
      updateDailogEmployee: false,
      createDailogUsers: false,
      updateDailogUser: false,

      isLoading: false,
      error: null,
      loading: false,

      selectedItems: [],
      totalItems: 0,
      itemKey: 'id',
      Search: '',
      clearSearch: '',
      rail: false,
      drawerVisible: true,
    };
  },

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
    /* ====== Jahadi Mawqaf (dropdown) ====== */
    getJahadiMawqafOptions() {
      return JAHADI_MAWQAFA_OPTIONS;
    },
    /* ====== Halat Madani (dropdown) ====== */
    getHalatMadaniOptions() {
      return HALAT_MADANI_OPTIONS;
    },

    toggleRail() {
      this.rail = !this.rail;
    },
    toggleDrawerVisible() {
      this.drawerVisible = !this.drawerVisible;
    },
    setDrawerVisible(v) {
      this.drawerVisible = !!v;
    },
    // ---------------------- COMMON ----------------------
    getCurrentDate() {
      const today = new Date();
      const year = today.getFullYear();
      const month = (today.getMonth() + 1).toString().padStart(2, '0');
      const day = today.getDate().toString().padStart(2, '0');
      return `${year}-${month}-${day}`;
    },

    // ---------------------- CURRENCIES ----------------------
    async GetCurrencies() {
      this.loading = true;
      try {
        setContentType('application/json');
        const response = await axios.get(`/currencies`);
        this.currency = response.data.data;
      } catch (e) {
        console.error(e);
        this.error = e;
      } finally {
        this.loading = false;
      }
    },

    // ---------------------- Resedints (for dropdowns) ----------------------
    async GetResedints() {
      this.loading = true;
      try {
        setContentType('application/json');
        const response = await axios.get(`/resedints`);
        this.resedints = response.data?.data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
      } finally {
        this.loading = false;
      }
    },

    // ---------------------- OfficeEmployee (for dropdowns) ----------------------
    async GetOfficeEmployee() {
      this.loading = true;
      try {
        setContentType('application/json');
        const response = await axios.get(`/office-employees`);
        this.officeemployees = response.data?.data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
      } finally {
        this.loading = false;
      }
    },

    // ====================== OFFICE EMPLOYEES ======================
    async FetchEmployeesData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const response = await axios.get(
          `office-employees?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = response.data?.data ?? response.data ?? [];
        this.Employees = Array.isArray(items) ? items : [];
        this.totalItems =
          response.data?.meta?.total ?? response.data?.total ?? this.Employees.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Employees = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async DeleteEmployee(id) {
      try {
        await axios.delete(`/office-employees/${id}`);
        toast.success('د دفتر کارمند بریالیتوب سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchEmployeesData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'د دفتر د کارمند حذف کول ناکامه وو'));
      }
    },

    async FetchEmployeeData(id) {
      try {
        setContentType('application/json');
        const response = await axios.get(`/office-employees/${id}`);
        this.Employee = response.data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Employee = {};
      }
    },

    async CreateEmployee(formData) {
      try {
        delete axios.defaults.headers.common['Content-Type'];
        const res = await axios.post('/office-employees', formData);
        const saved = res.data?.data ?? res.data;
        toast.success('د دفتر د کارمند اضافه کول بریالی وو', {
          autoClose: 1200,
        });
        // you can also refresh the list if you like:
        // await this.FetchEmployeesData({ page: this.page, itemsPerPage: this.itemsPerPage });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'د دفتر د کارمند اضافه کول بریالی نه وو'));
        throw e;
      }
    },

    async UpdateEmployee(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          delete axios.defaults.headers.common['Content-Type'];
          res = await axios.post(`/office-employees/${id}`, data);
        } else {
          axios.defaults.headers.common['Content-Type'] = 'application/json';
          res = await axios.put(`/office-employees/${id}`, data);
        }
        toast.success('د دفتر کامند تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'د دفتر کارمند تغییر نشو'));
        throw e;
      }
    },
    async DownloadOfficeEmployeeZip(id, filename) {
      const res = await axios.get(`/office-employees/${id}/documents/download-all`, {
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

    // Optional helpers if you want sub-routes like Events (not required if you embed docs in the resource)
    async FetchOfficeEmployeeDocuments(employeeId) {
      const { data } = await axios.get(`/office-employees/${employeeId}/documents`);
      return data.data ?? data;
    },
    async UploadOfficeEmployeeDocuments(employeeId, files) {
      const fd = new FormData();
      [...files].forEach((f) => fd.append('documents[]', f));
      delete axios.defaults.headers.common['Content-Type'];
      const { data } = await axios.post(`/office-employees/${employeeId}/documents`, fd);
      return data.data ?? data;
    },
    async DeleteOfficeEmployeeDocument(employeeId, docId) {
      await axios.delete(`/office-employees/${employeeId}/documents/${docId}`);
    },

    // ====================== RESEDINTS ======================
    async FetchResedintsData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `resedints?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.Resedints = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.Resedints.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Resedints = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async DeleteResedintEmployee(id) {
      try {
        await axios.delete(`/resedints/${id}`);
        toast.success('بریالیتوب سره رزیډنټ حذف شو', {
          autoClose: 1200,
        });
        this.FetchResedintsData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ناکام شو حذف د رزیډنټ'));
      }
    },

    async FetchResedintData(id) {
      try {
        setContentType('application/json');
        const response = await axios.get(`/resedints/${id}`);
        this.Resedint = response.data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Resedint = {};
      }
    },

    async CreateResedint(formData) {
      try {
        delete axios.defaults.headers.common['Content-Type'];
        const res = await axios.post('/resedints', formData);
        const saved = res.data?.data ?? res.data;
        toast.success('رزیډنټ بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رزبډنټ اضافه نشو'));
        throw e;
      }
    },

    async UpdateResedint(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          delete axios.defaults.headers.common['Content-Type'];
          res = await axios.post(`/resedints/${id}`, data);
        } else {
          axios.defaults.headers.common['Content-Type'] = 'application/json';
          res = await axios.put(`/resedints/${id}`, data);
        }
        toast.success('رزیډنټ بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رزیډنټ تغییر نشو'));
        throw e;
      }
    },
    // ===== Resedint Documents =====
    async FetchResedintDocuments(resedintId) {
      const { data } = await axios.get(`/resedints/${resedintId}/documents`);
      return data.data ?? data;
    },
    async UploadResedintDocuments(resedintId, files) {
      const fd = new FormData();
      [...files].forEach((f) => fd.append('files[]', f));
      delete axios.defaults.headers.common['Content-Type'];
      const { data } = await axios.post(`/resedints/${resedintId}/documents`, fd);
      toast.success('اسناد رزیدانت اپلود شد', { autoClose: 1100 });
      return data.data ?? data;
    },
    async DeleteResedintDocument(resedintId, docId) {
      await axios.delete(`/resedints/${resedintId}/documents/${docId}`);
      toast.success('سند رزیدانت حذف شد', { autoClose: 1100 });
    },
    async DownloadResedintZip(id, filename) {
      const res = await axios.get(`/resedints/${id}/documents/zip`, {
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

    // ====================== ZONE EMPLOYEES ======================
    async FetchZoneEmployeesData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data: res } = await axios.get(
          `zone-employees?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = res?.data ?? res ?? [];
        this.ZoneEmployees = Array.isArray(items) ? items : [];
        this.totalItems = res?.meta?.total ?? res?.total ?? this.ZoneEmployees.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.ZoneEmployees = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async DeleteZoneEmployees(id) {
      try {
        await axios.delete(`/zone-employees/${id}`);
        toast.success('تدابیری کارمند موفقیت سره حذف شو', {
          autoClose: 1200,
        });
        this.FetchZoneEmployeesData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'تدابیری کارمند خذف نشو'));
      }
    },

    async FetchZoneEmployeeData(id) {
      try {
        setContentType('application/json');
        const response = await axios.get(`/zone-employees/${id}`);
        this.ZoneEmployee = response.data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.ZoneEmployee = {};
      }
    },

    async CreateZoneEmployee(formData) {
      try {
        delete axios.defaults.headers.common['Content-Type'];
        const res = await axios.post('/zone-employees', formData);
        const saved = res.data?.data ?? res.data;
        toast.success('Zone employee successfully created', {
          autoClose: 1200,
        });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'تدابیری کامند نشو اخیستی'));
        throw e;
      }
    },

    async UpdateZoneEmployee(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          delete axios.defaults.headers.common['Content-Type'];
          res = await axios.post(`/zone-employees/${id}`, data);
        } else {
          axios.defaults.headers.common['Content-Type'] = 'application/json';
          res = await axios.put(`/zone-employees/${id}`, data);
        }
        toast.success('تدابیری کارمند بریالیتوب سره تغییر شو', {
          autoClose: 1200,
        });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'تدابیری کارمند تغییر نشو'));
        throw e;
      }
    },
    async DownloadZoneEmployeeZip(id, filename) {
      const res = await axios.get(`/zone-employees/${id}/documents/download-all`, {
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

    // Optional subroutes (only if you want them; not required if docs are embedded in resource)
    async FetchZoneEmployeeDocuments(zoneEmployeeId) {
      const { data } = await axios.get(`/zone-employees/${zoneEmployeeId}/documents`);
      return data.data ?? data;
    },
    async UploadZoneEmployeeDocuments(zoneEmployeeId, files) {
      const fd = new FormData();
      [...files].forEach((f) => fd.append('documents[]', f));
      delete axios.defaults.headers.common['Content-Type'];
      const { data } = await axios.post(`/zone-employees/${zoneEmployeeId}/documents`, fd);
      return data.data ?? data;
    },
    async DeleteZoneEmployeeDocument(zoneEmployeeId, docId) {
      await axios.delete(`/zone-employees/${zoneEmployeeId}/documents/${docId}`);
    },

    // ====================== USERS ======================
    async FetchUsersData({ page, itemsPerPage }) {
      this.loading = true;
      try {
        setContentType('application/json');
        const response = await axios.get(
          `users?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
            this.Search || ''
          )}`
        );
        const items = response.data?.data ?? response.data ?? [];
        this.Users = Array.isArray(items) ? items : [];
        this.totalItems = response.data?.meta?.total ?? response.data?.total ?? this.Users.length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Users = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async CreateUser(formData) {
      try {
        setContentType(null);
        await axios.post('/users', formData);
        toast.success('استفاده کوونکی بریالیتوب سره اضافه شو', {
          autoClose: 1200,
        });
        this.createDailog = false;
        this.FetchUsersData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'استفاده کوونکی اضافه نشو'));
      }
    },

    async DeleteUser(id) {
      try {
        await axios.delete(`/users/${id}`);
        toast.success('استفاده کوونکی حذف شو', { autoClose: 1200 });
        this.FetchUsersData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'استفاده کوونکی خذف نشو'));
      }
    },

    async FetchUserData(id) {
      try {
        setContentType('application/json');
        const response = await axios.get(`/users/${id}`);
        this.User = response.data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.User = {};
      }
    },

    // FIXED: this function was previously colliding with UpdateEmployee
    async UpdateUser(id, data) {
      try {
        if (data instanceof FormData) {
          setContentType(null);
        } else {
          setContentType('application/json');
        }
        await axios.put(`/users/${id}`, data);
        toast.success('استفاده کوونکی تغییر شو', { autoClose: 1200 });
        this.updateDailog = false;
        this.FetchUsersData({
          page: this.page,
          itemsPerPage: this.itemsPerPage,
        });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'استفاده کوونکی تغییر نشو'));
      }
    },
  },
});
