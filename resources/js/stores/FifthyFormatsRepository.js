// src/stores/FifthyFormatsRepository.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';

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

export const useFifthyFormatsRepository = defineStore('FifthyFormatsRepository', {
  state: () => ({
    router: useRouter(),

    // commons
    page: 1,
    itemsPerPage: 10,
    Search: '',
    isLoading: false,
    error: null,
    totalItems: 0,

    // drawers (optional UI helpers)
    rail: false,
    drawerVisible: true,

    // ===== entity state (list + single) =====
    fasads: reactive([]),
    fasad: reactive({}),

    saeenFasads: reactive([]),
    saeenFasad: reactive({}),

    heists: reactive([]),
    heist: reactive({}),

    captives: reactive([]),
    captive: reactive({}),

    mulkanos: reactive([]),
    mulkano: reactive({}),

    entryExits: reactive([]),
    entryExit: reactive({}),

    sarafyanos: reactive([]),
    sarafyano: reactive({}),

    clubs: reactive([]),
    club: reactive({}),

    mahajerats: reactive([]),
    mahajerat: reactive({}),

    soldiers: reactive([]),
    soldier: reactive({}),

    resourcecode: reactive([]),
  }),

  actions: {
    // ---------- tiny UI helpers ----------
    toggleRail() {
      this.rail = !this.rail;
    },
    toggleDrawerVisible() {
      this.drawerVisible = !this.drawerVisible;
    },
    setDrawerVisible(v) {
      this.drawerVisible = !!v;
    },
    getCurrentDate() {
      const d = new Date();
      return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(
        d.getDate()
      ).padStart(2, '0')}`;
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
    async apiPath(path) {
      // If baseURL already ends with /api, don't prefix another /api
      // Accepts path like "upload" or "/upload"
      const p = path.startsWith('/') ? path.slice(1) : path;
      const base = axios.defaults.baseURL || '';
      const endsWithApi = /\/api\/?$/.test(base);
      return (endsWithApi ? '' : '/api') + '/' + p;
    },
    async UploadImage(file) {
      const fd = new FormData();
      fd.append('file', file);

      // Post to /api/upload unless baseURL already has /api
      const { data } = await axios.post(apiPath('upload'), fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      // Expect { path: "..."} or { url: "..." }
      return data;
    },

    // =========================================================
    // =============== INTERNAL GENERIC HELPERS ================
    // =========================================================
    async _fetchList({ path, listKey, page, itemsPerPage, search, extra = {} }) {
      this.isLoading = true;
      try {
        setContentType('application/json');
        const params = new URLSearchParams({
          page: page ?? this.page,
          per_page: itemsPerPage ?? this.itemsPerPage,
          search: search ?? this.Search ?? '',
          ...extra,
        }).toString();

        const { data } = await axios.get(`/${path}?${params}`);
        const items = data?.data ?? data ?? [];
        this[listKey] = Array.isArray(items) ? items : [];
        this.totalItems = data?.meta?.total ?? data?.total ?? this[listKey].length;
      } catch (e) {
        console.error(e);
        this.error = e;
        this[listKey] = [];
        this.totalItems = 0;
        toast.error(errText(e, 'لیست ترلاسه نشو'));
      } finally {
        this.isLoading = false;
      }
    },

    async _fetchOne({ path, id, itemKey }) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/${path}/${id}`);
        this[itemKey] = data?.data ?? data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this[itemKey] = {};
        toast.error(errText(e, 'معلومات ترلاسه نشول'));
      }
    },

    async _create({ path, payload, successMsg = 'ثبت شو' }) {
      try {
        // allow FormData or JSON
        if (payload instanceof FormData) {
          setContentType(null);
        } else {
          setContentType('application/json');
        }
        const { data } = await axios.post(`/${path}`, payload);
        toast.success(successMsg, { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'ثبت ناکام شو'));
        throw e;
      }
    },

    async _update({ path, id, payload, successMsg = 'تغییر شو' }) {
      try {
        let res;
        if (payload instanceof FormData) {
          payload.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/${path}/${id}`, payload);
        } else {
          setContentType('application/json');
          res = await axios.put(`/${path}/${id}`, payload);
        }
        toast.success(successMsg, { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'تغییر ناکام شو'));
        throw e;
      }
    },

    async _delete({ path, id, successMsg = 'حذف شو' }) {
      try {
        await axios.delete(`/${path}/${id}`);
        toast.success(successMsg, { autoClose: 1200 });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'حذف ناکام شو'));
        throw e;
      }
    },

    // =========================================================
    // ================== FASAD ==================
    async FetchFasadsData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'fasads',
        listKey: 'fasads',
        page,
        itemsPerPage,
      });
    },
    async FetchFasadData(id) {
      return this._fetchOne({ path: 'fasads', id, itemKey: 'fasad' });
    },
    async CreateFasad(payload) {
      return this._create({
        path: 'fasads',
        payload,
        successMsg: 'فساد ثبت شو',
      });
    },
    async UpdateFasad(id, payload) {
      return this._update({
        path: 'fasads',
        id,
        payload,
        successMsg: 'فساد تغییر شو',
      });
    },
    async DeleteFasad(id) {
      await this._delete({ path: 'fasads', id, successMsg: 'فساد حذف شو' });
      await this.FetchFasadsData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // ================== SAEEN FASAD ==================
    async FetchSaeenFasadsData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'saeen-fasads',
        listKey: 'saeenFasads',
        page,
        itemsPerPage,
      });
    },
    async FetchSaeenFasadData(id) {
      return this._fetchOne({
        path: 'saeen-fasads',
        id,
        itemKey: 'saeenFasad',
      });
    },
    async CreateSaeenFasad(payload) {
      return this._create({
        path: 'saeen-fasads',
        payload,
        successMsg: 'سعین فساد ثبت شو',
      });
    },
    async UpdateSaeenFasad(id, payload) {
      return this._update({
        path: 'saeen-fasads',
        id,
        payload,
        successMsg: 'سعین فساد تغییر شو',
      });
    },
    async DeleteSaeenFasad(id) {
      await this._delete({
        path: 'saeen-fasads',
        id,
        successMsg: 'سعین فساد حذف شو',
      });
      await this.FetchSaeenFasadsData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // ================== HEIST ==================
    async FetchHeistsData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'heists',
        listKey: 'heists',
        page,
        itemsPerPage,
      });
    },
    async FetchHeistData(id) {
      return this._fetchOne({ path: 'heists', id, itemKey: 'heist' });
    },
    async CreateHeist(payload) {
      return this._create({
        path: 'heists',
        payload,
        successMsg: 'هیست ثبت شو',
      });
    },
    async UpdateHeist(id, payload) {
      return this._update({
        path: 'heists',
        id,
        payload,
        successMsg: 'هیست تغییر شو',
      });
    },
    async DeleteHeist(id) {
      await this._delete({ path: 'heists', id, successMsg: 'هیست حذف شو' });
      await this.FetchHeistsData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // ================== CAPTIVE ==================
    async FetchCaptivesData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'captives',
        listKey: 'captives',
        page,
        itemsPerPage,
      });
    },
    async FetchCaptiveData(id) {
      return this._fetchOne({ path: 'captives', id, itemKey: 'captive' });
    },
    async CreateCaptive(payload) {
      return this._create({
        path: 'captives',
        payload,
        successMsg: 'بندی ثبت شو',
      });
    },
    async UpdateCaptive(id, payload) {
      return this._update({
        path: 'captives',
        id,
        payload,
        successMsg: 'بندی تغییر شو',
      });
    },
    async DeleteCaptive(id) {
      await this._delete({ path: 'captives', id, successMsg: 'بندی حذف شو' });
      await this.FetchCaptivesData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // ================== MULKANO ==================
    async FetchMulkanosData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'mulkanos',
        listKey: 'mulkanos',
        page,
        itemsPerPage,
      });
    },
    async FetchMulkanoData(id) {
      return this._fetchOne({ path: 'mulkanos', id, itemKey: 'mulkano' });
    },
    async CreateMulkano(payload) {
      return this._create({
        path: 'mulkanos',
        payload,
        successMsg: 'ملکانو ثبت شو',
      });
    },
    async UpdateMulkano(id, payload) {
      return this._update({
        path: 'mulkanos',
        id,
        payload,
        successMsg: 'ملکانو تغییر شو',
      });
    },
    async DeleteMulkano(id) {
      await this._delete({
        path: 'mulkanos',
        id,
        successMsg: 'ملکانو حذف شو',
      });
      await this.FetchMulkanosData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // ================== ENTRY/EXIT ==================
    async FetchEntryExitsData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'entry-exits',
        listKey: 'entryExits',
        page,
        itemsPerPage,
      });
    },
    async FetchEntryExitData(id) {
      return this._fetchOne({
        path: 'entry-exits',
        id,
        itemKey: 'entryExit',
      });
    },
    async CreateEntryExit(payload) {
      return this._create({
        path: 'entry-exits',
        payload,
        successMsg: 'د داخل/خارج ریکارډ ثبت شو',
      });
    },
    async UpdateEntryExit(id, payload) {
      return this._update({
        path: 'entry-exits',
        id,
        payload,
        successMsg: 'داخل/خارج تغییر شو',
      });
    },
    async DeleteEntryExit(id) {
      await this._delete({
        path: 'entry-exits',
        id,
        successMsg: 'داخل/خارج حذف شو',
      });
      await this.FetchEntryExitsData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // ================== SARAFYANO ==================
    async FetchSarafyanosData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'sarafyanos',
        listKey: 'sarafyanos',
        page,
        itemsPerPage,
      });
    },
    async FetchSarafyanoData(id) {
      return this._fetchOne({ path: 'sarafyanos', id, itemKey: 'sarafyano' });
    },
    async CreateSarafyano(payload) {
      return this._create({
        path: 'sarafyanos',
        payload,
        successMsg: 'صراف ثبت شو',
      });
    },
    async UpdateSarafyano(id, payload) {
      return this._update({
        path: 'sarafyanos',
        id,
        payload,
        successMsg: 'صراف تغییر شو',
      });
    },
    async DeleteSarafyano(id) {
      await this._delete({
        path: 'sarafyanos',
        id,
        successMsg: 'صراف حذف شو',
      });
      await this.FetchSarafyanosData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // ================== CLUBS ==================
    async FetchClubsData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'clubs',
        listKey: 'clubs',
        page,
        itemsPerPage,
      });
    },
    async FetchClubData(id) {
      return this._fetchOne({ path: 'clubs', id, itemKey: 'club' });
    },
    async CreateClub(payload) {
      return this._create({
        path: 'clubs',
        payload,
        successMsg: 'کلپ ثبت شو',
      });
    },
    async UpdateClub(id, payload) {
      return this._update({
        path: 'clubs',
        id,
        payload,
        successMsg: 'کلپ تغییر شو',
      });
    },
    async DeleteClub(id) {
      await this._delete({ path: 'clubs', id, successMsg: 'کلپ حذف شو' });
      await this.FetchClubsData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // ================== MAHAJERAT ==================
    async FetchMahajeratsData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'mahajerats',
        listKey: 'mahajerats',
        page,
        itemsPerPage,
      });
    },
    async FetchMahajeratData(id) {
      return this._fetchOne({ path: 'mahajerats', id, itemKey: 'mahajerat' });
    },
    async CreateMahajerat(payload) {
      return this._create({
        path: 'mahajerats',
        payload,
        successMsg: 'مهاجرت ثبت شو',
      });
    },
    async UpdateMahajerat(id, payload) {
      return this._update({
        path: 'mahajerats',
        id,
        payload,
        successMsg: 'مهاجرت تغییر شو',
      });
    },
    async DeleteMahajerat(id) {
      await this._delete({
        path: 'mahajerats',
        id,
        successMsg: 'مهاجرت حذف شو',
      });
      await this.FetchMahajeratsData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },

    // ================== SOLDIER ==================
    async FetchSoldiersData({ page, itemsPerPage } = {}) {
      return this._fetchList({
        path: 'soldiers',
        listKey: 'soldiers',
        page,
        itemsPerPage,
      });
    },
    async FetchSoldierData(id) {
      return this._fetchOne({ path: 'soldiers', id, itemKey: 'soldier' });
    },
    async CreateSoldier(payload) {
      return this._create({
        path: 'soldiers',
        payload,
        successMsg: 'سولجر ثبت شو',
      });
    },
    async UpdateSoldier(id, payload) {
      return this._update({
        path: 'soldiers',
        id,
        payload,
        successMsg: 'سولجر تغییر شو',
      });
    },
    async DeleteSoldier(id) {
      await this._delete({
        path: 'soldiers',
        id,
        successMsg: 'سولجر حذف شو',
      });
      await this.FetchSoldiersData({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
      });
    },
  },
});
