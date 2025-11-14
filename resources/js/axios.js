// resources/js/axios.js
import axios from "axios";

// ✅ Base URL (token mode doesn't need Sanctum cookies/CSRF)
axios.defaults.baseURL = "http://127.0.0.1:8000/api";

// ❌ Do NOT use cookies for token mode
axios.defaults.withCredentials = false;

// ✅ JSON defaults
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// ✅ Load token from sessionStorage on boot
const saved = sessionStorage.getItem("token");
if (saved) {
  axios.defaults.headers.common["Authorization"] = `Bearer ${saved}`;
}

// ✅ Attach token automatically on every request (if present)
axios.interceptors.request.use((config) => {
  const t = sessionStorage.getItem("token");
  if (t) config.headers.Authorization = `Bearer ${t}`;
  return config;
});

// ✅ Handle 401/419: drop token and bounce to login
axios.interceptors.response.use(
  (r) => r,
  (err) => {
    const status = err?.response?.status;
    if (status === 401 || status === 419) {
      sessionStorage.removeItem("token");
      sessionStorage.removeItem("user");
      // If you have vue-router available here, you can import it and push.
      // As a safe fallback:
      if (window.location.pathname !== "/") {
        window.location.href = "/";
      }
    }
    return Promise.reject(err);
  }
);

export default axios;
