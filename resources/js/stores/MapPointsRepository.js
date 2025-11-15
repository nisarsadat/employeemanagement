import { defineStore } from "pinia";
import { reactive } from "vue";
// import { toast } from "vue3-toastify";

// If you use your own axios plugin, use this instead:
// import { axios, setContentType } from "@/plugins/axiosInstance";
import axios from "axios";
import setContentType from "axios";

export const useMapPointsRepository = defineStore("MapPointsRepository", {
    state() {
        return {
            MapPoint: reactive({}),     // single record
            MapPoints: reactive([]),    // list for markers
            loading: false,
            error: null,
        };
    },

    actions: {
        // 🔹 Get all points (for markers)
        async FetchMapPoints() {
            this.loading = true;
            this.error = null;
            try {
                setContentType("application/json");
                const response = await axios.get(`/map-points`);
                // backend returns a plain array
                this.MapPoints = response.data;
                return this.MapPoints;
            } catch (err) {
                console.error("FetchMapPoints error:", err);
                this.error = err;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // 🔹 Create new point (name, father_name, picture, coordinates, ...)
        async CreateMapPoint(formData) {
            this.loading = true;
            this.error = null;

            try {
                // JSON + multipart like you do for Employees/Users
                setContentType("application/json");
                setContentType("multipart/form-data");

                const config = {
                    method: "POST",
                    url: "/map-points",
                    data: formData,
                };

                const response = await axios(config);
                const created = response.data;

                // keep store in sync
                this.MapPoints.unshift(created);
                this.MapPoint = created;

                // toast.success("نقطه موقعیت موفقانه ثبت شد", { autoClose: 1000 });

                return created;
            } catch (err) {
                console.error("CreateMapPoint error:", err);
                this.error = err;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // 🔹 Get a single point (if later you want detail view)
        async FetchMapPoint(id) {
            this.loading = true;
            this.error = null;
            try {
                setContentType("application/json");
                const response = await axios.get(`/map-points/${id}`);
                this.MapPoint = response.data;
                return this.MapPoint;
            } catch (err) {
                console.error("FetchMapPoint error:", err);
                this.error = err;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // 🔹 Delete point (optional, useful later)
        async DeleteMapPoint(id) {
            this.loading = true;
            this.error = null;
            try {
                const config = {
                    method: "DELETE",
                    url: `/map-points/${id}`,
                };
                await axios(config);

                // toast.success("نقطه موفقانه حذف شد", { autoClose: 1000 });

                this.MapPoints = this.MapPoints.filter((p) => p.id !== id);
            } catch (err) {
                console.error("DeleteMapPoint error:", err);
                this.error = err;
                throw err;
            } finally {
                this.loading = false;
            }
        },
    },
});
