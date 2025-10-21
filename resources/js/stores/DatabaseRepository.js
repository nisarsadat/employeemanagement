// src/stores/DatabaseRepository.js
import { defineStore } from "pinia";
import { reactive } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { toast } from "vue3-toastify";

// Accept JSON by default; for FormData we’ll clear it dynamically.
axios.defaults.headers.common["Accept"] = "application/json";

function setContentType(type) {
    if (!type) {
        delete axios.defaults.headers.common["Content-Type"];
    } else {
        axios.defaults.headers.common["Content-Type"] = type;
    }
}

export const useDatabaseRepository = defineStore("DatabaseRepository", {
    state: () => ({
        router: useRouter(),

        // paging / search
        page: 1,
        itemsPerPage: 10,
        totalItems: 0,
        Search: "",
        clearSearch: "",

        // flags
        isLoading: false,
        loading: false,
        error: null,

        // ==== DATA BUCKETS ====
        // Events
        Events: reactive([]),
        Event: reactive({}),
        // options
        eventTypeOptions: ["جنایی", "سیاسی", "ترافیکی", "طبیعی", "داسی نور"],
        janiTalafatOptions: ["وژل شوی", "ټپي شوی", "نور"],
        maliTalafatOptions: ["هو", "نه", "نور"],

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
        DeleviryCaptives: reactive([]),
        DeleviryCaptive: reactive({}),
        DeleviryCaptives: reactive([]),
        DeleviryCaptive: reactive({}),

        // options
        activityOptions: ["فعال", "غیر فعال"],
        halatMadaniOptions: ["مجرد", "متاهل"],
        seyasiOptions: ["ساعین بالفساد", "شر و فساد", "ضد جاسوسی" , "جینایی"],
        jenayeeOptions: ["جینایی"],
        infoCategoryOptions: ["الف", "ب", "ج"],

        // selection
        selectedItems: [],
        itemKey: "id",
    }),

    actions: {
        // ---------------------- COMMON ----------------------
        getCurrentDate() {
            const d = new Date();
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, "0");
            const day = String(d.getDate()).padStart(2, "0");
            return `${y}-${m}-${day}`;
        },

        // ---------------------- Resedints ----------------------
        async GetResedints() {
            this.loading = true;
            try {
                setContentType("application/json");
                const { data } = await axios.get(`/resedints`);
                this.resedints = data.data ?? data;
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("د رزیډنټانو احیستل ناکام شو");
            } finally {
                this.loading = false;
            }
        },

        // ---------------------- OfficeEmployee ----------------------
        async GetOfficeEmployee() {
            this.loading = true;
            try {
                setContentType("application/json");
                const { data } = await axios.get(`/office-employees`);
                this.officeemployees = data.data ?? data;
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("د دفتر کارمند اخیستل نا ممکن دی");
            } finally {
                this.loading = false;
            }
        },
        async GetOfficeEmployeeCode() {
            this.loading = true;
            try {
                setContentType("application/json");
                const { data } = await axios.get(`/office-employees`);
                this.officeemployeescode = data.data ?? data;
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("د دفتر کارمند اخیستل نا ممکن دی");
            } finally {
                this.loading = false;
            }
        },

        // ---------------------- Resources ----------------------
        async GetResource() {
            this.loading = true;
            try {
                setContentType("application/json");
                const { data } = await axios.get(`/resources`);
                this.resource = data.data ?? data;
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("د منابع د کود اخیستل ناممکن دی");
            } finally {
                this.loading = false;
            }
        },
        async GetResourceCode() {
            this.loading = true;
            try {
                setContentType("application/json");
                const { data } = await axios.get(`/resources`);
                this.resourcecode = data.data ?? data;
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("د منابع د کود اخیستل ناممکن دی");
            } finally {
                this.loading = false;
            }
        },

        async FetchResourcesData({ page, itemsPerPage }) {
            this.loading = true;
            try {
                setContentType("application/json");
                const { data: res } = await axios.get(
                    `resources?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
                        this.Search || ""
                    )}`
                );
                this.Resources = res.data ?? res;
                this.totalItems =
                    res?.meta?.total ?? res?.total ?? this.Resources.length;
            } catch (e) {
                console.error(e);
                this.error = e;
                this.Resources = [];
                this.totalItems = 0;
                toast.error("نشو کولای اوس منابع لود وشیو");
            } finally {
                this.loading = false;
            }
        },

        async FetchResourceData(id) {
            try {
                setContentType("application/json");
                const { data } = await axios.get(`/resources/${id}`);
                this.Resource = data.data ?? data;
            } catch (e) {
                console.error(e);
                this.error = e;
                this.Resource = {};
                toast.error("نشو کولای اوس منابع لود وشیو");
            }
        },

        async CreateResource(formData) {
            try {
                setContentType(null); // let browser set multipart boundary
                const { data: res } = await axios.post(`/resources`, formData);
                toast.success("منبع اضافه شوی بریالیتوب سره", {
                    autoClose: 1000,
                });
                await this.FetchResourcesData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
                return res?.data ?? res;
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("منبع اضافه نشو بیا کوشش وکی");
                throw e;
            }
        },

        async UpdateResource(id, data) {
            try {
                let res;
                if (data instanceof FormData) {
                    data.append("_method", "PUT");
                    setContentType(null);
                    ({ data: res } = await axios.post(
                        `/resources/${id}`,
                        data
                    ));
                } else {
                    setContentType("application/json");
                    ({ data: res } = await axios.put(`/resources/${id}`, data));
                }
                toast.success("منبع بریالیتوب سره تغییر شو", {
                    autoClose: 1000,
                });
                await this.FetchResourcesData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
                return res?.data ?? res;
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("د منبع تغییر ناکام شو بیا کوشش وکی");
                throw e;
            }
        },

        async DeleteResource(id) {
            try {
                await axios.delete(`/resources/${id}`);
                toast.success("منبع برالیتوب سره حذف شو", {
                    autoClose: 1000,
                });
                await this.FetchResourcesData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("منبع حذف کول ناکام شو بیا کوشش وکی");
            }
        },

        // ---------------------- Zone Employees ----------------------
        async GetZoneEmployees() {
            this.loading = true;
            try {
                const { data } = await axios.get(`/zone-employees`);
                this.ZoneEmployees = data.data ?? data;
            } finally {
                this.loading = false;
            }
        },
        async GetZoneEmployeesCode() {
            this.loading = true;
            try {
                const { data } = await axios.get(`/zone-employees`);
                this.ZoneEmployeesCode = data.data ?? data;
            } finally {
                this.loading = false;
            }
        },
        // alias to match your component that calls FetchZoneEmployeesData
        async FetchZoneEmployeesData({ page = 1, itemsPerPage = 100 } = {}) {
            return this.GetZoneEmployees(page, itemsPerPage);
        },

        // ====================== EVENTS  ======================
        async FetchEventsData({ page, itemsPerPage }) {
            this.loading = true;
            try {
                setContentType("application/json");
                const { data } = await axios.get(
                    `events?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
                        this.Search || ""
                    )}`
                );

                // ✅ normalize like Informations
                const list = Array.isArray(data)
                    ? data
                    : data.data ?? data.items ?? [];
                this.Events = list;
                this.totalItems =
                    data?.meta?.total ?? data?.total ?? list.length;
            } catch (e) {
                console.error(e);
                this.error = e;
                this.Events = [];
                this.totalItems = 0;
                toast.error("د پیشی اخیستل ناکامه وو");
            } finally {
                this.loading = false;
            }
        },

        async FetchEventData(id) {
            try {
                setContentType("application/json");
                const { data } = await axios.get(`/events/${id}`);
                this.Event = data.data ?? data;
            } catch (e) {
                console.error(e);
                this.error = e;
                this.Event = {};
                toast.error("و پیشی احیستل نامامه وو");
            }
        },

        async CreateEvent(formData) {
            try {
                setContentType(null); // ✅ same as Informations
                const { data } = await axios.post(`/events`, formData);
                toast.success("پیښه بریالیتوب سره اضافه شوه ", {
                    autoClose: 1000,
                });
                await this.FetchEventsData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
                return data?.data ?? data;
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("د پیښی ثبت ناکامه وو");
                throw e;
            }
        },

        async UpdateEvent(id, data) {
            try {
                let res;
                if (data instanceof FormData) {
                    data.append("_method", "PUT");
                    setContentType(null); // ✅ same as Informations
                    ({ data: res } = await axios.post(`/events/${id}`, data));
                } else {
                    setContentType("application/json");
                    ({ data: res } = await axios.put(`/events/${id}`, data));
                }
                toast.success("پیښه برالیتوب سره تغییر شوه", {
                    autoClose: 1000,
                });
                await this.FetchEventsData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
                return res?.data ?? res;
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("پیښه تغییر نشوه");
                throw e;
            }
        },

        async DeleteEvent(id) {
            try {
                await axios.delete(`/events/${id}`);
                toast.success("پیښه برالیتوب سره حذف شو", {
                    autoClose: 1100,
                });
                await this.FetchEventsData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("پیښه حذف نشوه");
            }
        },

        // ===== Event Documents (parallel to Information docs) =====
        async FetchEventDocuments(eventId) {
            const { data } = await axios.get(`/events/${eventId}/documents`);
            return data.data ?? data;
        },

        async UploadEventDocuments(eventId, files) {
            const fd = new FormData();
            [...files].forEach((f) => fd.append("files[]", f));
            setContentType(null);
            const { data } = await axios.post(
                `/events/${eventId}/documents`,
                fd
            );
            toast.success("اسناد اپلوډ شوه", { autoClose: 1100 });
            return data.data ?? data;
        },

        async DeleteEventDocument(eventId, docId) {
            await axios.delete(`/events/${eventId}/documents/${docId}`);
            toast.success("اسناد حذف شوه", { autoClose: 1100 });
        },

        async DownloadEventZip(id, filename) {
            const res = await axios.get(`/events/${id}/documents/zip`, {
                responseType: "blob",
            });
            const blob = new Blob([res.data], { type: "application/zip" });
            const url = window.URL.createObjectURL(blob);
            const safe = (s) =>
                String(s || "")
                    .toLowerCase()
                    .replace(/\s+/g, "_")
                    .replace(/[^a-z0-9._-]/g, "");
            const a = document.createElement("a");
            a.href = url;
            a.download = safe(filename || `documents_${id}.zip`);
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        },

        // ====================== INFORMATIONS ======================
        async FetchInformationsData({ page, itemsPerPage }) {
            this.loading = true;
            try {
                setContentType("application/json");
                const { data } = await axios.get(
                    `informations?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
                        this.Search || ""
                    )}`
                );

                // Accept both paginated and plain array shapes
                const list = Array.isArray(data)
                    ? data
                    : data.data ?? data.items ?? [];
                this.Informations = list;
                this.totalItems =
                    data?.meta?.total ?? data?.total ?? list.length;
            } catch (e) {
                console.error(e);
                this.error = e;
                this.Informations = [];
                this.totalItems = 0;
                toast.error("د اطلاعاتو اخیستل ناکامه شو");
            } finally {
                this.loading = false;
            }
        },

        async CreateInformation(formData, onProgress) {
            try {
                // Let the browser set multipart boundary
                delete axios.defaults.headers.common["Content-Type"];

                const { data } = await axios.post(`/informations`, formData, {
                    onUploadProgress: (e) => {
                        if (!e.total) return;
                        const pct = Math.round((e.loaded * 100) / e.total);
                        if (typeof onProgress === "function") onProgress(pct);
                    },
                });

                toast.success("اطلاعات اضافه شو", { autoClose: 1000 });
                await this.FetchInformationsData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
                return data?.data ?? data;
            } catch (e) {
                toast.error("اطلاعات اضافه نشو");
                throw e;
            }
        },

        // --- UPDATE with progress ---
        async UpdateInformation(id, formDataOrJson, onProgress) {
            try {
                let res;

                if (formDataOrJson instanceof FormData) {
                    formDataOrJson.append("_method", "PUT");
                    delete axios.defaults.headers.common["Content-Type"];

                    ({ data: res } = await axios.post(
                        `/informations/${id}`,
                        formDataOrJson,
                        {
                            onUploadProgress: (e) => {
                                if (!e.total) return;
                                const pct = Math.round(
                                    (e.loaded * 100) / e.total
                                );
                                if (typeof onProgress === "function")
                                    onProgress(pct);
                            },
                        }
                    ));
                } else {
                    axios.defaults.headers.common["Content-Type"] =
                        "application/json";
                    ({ data: res } = await axios.put(
                        `/informations/${id}`,
                        formDataOrJson
                    ));
                }

                toast.success("اطلاعات تغییر شو", { autoClose: 1000 });
                await this.FetchInformationsData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
                return res?.data ?? res;
            } catch (e) {
                toast.error("اطلاعات اضافه نشړ");
                throw e;
            }
        },

        async DeleteInformation(id) {
            try {
                await axios.delete(`/informations/${id}`);
                toast.success("Information deleted", { autoClose: 1100 });
                await this.FetchInformationsData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("Delete information failed");
            }
        },

        // Documents sub-routes
        async FetchInformationDocuments(infoId) {
            const { data } = await axios.get(
                `/informations/${infoId}/documents`
            );
            return data.data ?? data;
        },

        async UploadInformationDocuments(infoId, files) {
            const fd = new FormData();
            [...files].forEach((f) => fd.append("files[]", f));
            setContentType(null);
            const { data } = await axios.post(
                `/informations/${infoId}/documents`,
                fd
            );
            toast.success("اسناد اضافه شو", { autoClose: 1100 });
            return data.data ?? data;
        },

        async DeleteInformationDocument(infoId, docId) {
            await axios.delete(`/informations/${infoId}/documents/${docId}`);
            toast.success("اسناد حذف شو", { autoClose: 1100 });
        },

        // ZIP download (the button calls this)
        async DownloadInformationZip(id, filename) {
            const res = await axios.get(`/informations/${id}/documents/zip`, {
                responseType: "blob",
            });

            const blob = new Blob([res.data], { type: "application/zip" });
            const url = window.URL.createObjectURL(blob);

            const safe = (s) =>
                String(s || "")
                    .toLowerCase()
                    .replace(/\s+/g, "_")
                    .replace(/[^a-z0-9._-]/g, "");

            const a = document.createElement("a");
            a.href = url;
            a.download = safe(filename || `documents_${id}.zip`);
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        },
        // ====================== CAPTIVE DELIVERIES ======================
        // --- Captive Delivery list fetch (you already have this) ---
        async FetchCaptiveDeliveriesData({ page, itemsPerPage }) {
            this.loading = true;
            try {
                const { data } = await axios.get(
                    `captive-deliveries?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(
                        this.Search || ""
                    )}`
                );
                const list = Array.isArray(data)
                    ? data
                    : data.data ?? data.items ?? [];
                this.CaptiveDeliveries = list;
                this.totalItems =
                    data?.meta?.total ?? data?.total ?? list.length;
            } finally {
                this.loading = false;
            }
        },
        async DeleteCaptiveDelivery (id) {
            try {
                await axios.delete(`/captive-deliveries/${id}`);
                toast.success("Delevery Captive deleted", { autoClose: 1100 });
                await this.FetchInformationsData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
            } catch (e) {
                console.error(e);
                this.error = e;
                toast.error("Delete Deleviry Captive failed");
            }
        },

        // --- CREATE with progress ---
        async CreateCaptiveDelivery(formData, onProgress) {
            try {
                // Let the browser set multipart boundary
                delete axios.defaults.headers.common["Content-Type"];

                const { data } = await axios.post(
                    `/captive-deliveries`,
                    formData,
                    {
                        onUploadProgress: (e) => {
                            if (!e.total) return;
                            const pct = Math.round((e.loaded * 100) / e.total);
                            if (typeof onProgress === "function")
                                onProgress(pct);
                        },
                    }
                );

                toast.success("تسلیم شوی بندي اضافه شوه", { autoClose: 1000 });
                await this.FetchCaptiveDeliveriesData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
                return data?.data ?? data;
            } catch (e) {
                toast.error("تسلیم شوی بندي اضافه نشوه");
                throw e;
            }
        },

        // --- UPDATE with progress ---
        async UpdateCaptiveDelivery(id, formDataOrJson, onProgress) {
            try {
                let res;

                if (formDataOrJson instanceof FormData) {
                    formDataOrJson.append("_method", "PUT");
                    delete axios.defaults.headers.common["Content-Type"];

                    ({ data: res } = await axios.post(
                        `/captive-deliveries/${id}`,
                        formDataOrJson,
                        {
                            onUploadProgress: (e) => {
                                if (!e.total) return;
                                const pct = Math.round(
                                    (e.loaded * 100) / e.total
                                );
                                if (typeof onProgress === "function")
                                    onProgress(pct);
                            },
                        }
                    ));
                } else {
                    axios.defaults.headers.common["Content-Type"] =
                        "application/json";
                    ({ data: res } = await axios.put(
                        `/captive-deliveries/${id}`,
                        formDataOrJson
                    ));
                }

                toast.success("تسلیم شوی بندي تغییر شو", { autoClose: 1000 });
                await this.FetchCaptiveDeliveriesData({
                    page: this.page,
                    itemsPerPage: this.itemsPerPage,
                });
                return res?.data ?? res;
            } catch (e) {
                toast.error("تسلیم شوی بتدي تعییر نشو");
                throw e;
            }
        },

        // --- ZIP download (already done for other modules; reuse for Captive) ---
        async DownloadCaptiveDeliveryZip(id, filename) {
            const res = await axios.get(
                `/captive-deliveries/${id}/documents/zip`,
                {
                    responseType: "blob",
                }
            );
            const blob = new Blob([res.data], { type: "application/zip" });
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = String(filename || `documents_${id}.zip`)
                .toLowerCase()
                .replace(/\s+/g, "_")
                .replace(/[^a-z0-9._-]/g, "");
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        },
    },
});
