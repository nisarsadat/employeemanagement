// resources/js/axios.js
import axios from "axios";

// ✅ Base URL for all API requests
axios.defaults.baseURL = "/api";

// ✅ Required so Sanctum can send & receive cookies
axios.defaults.withCredentials = true;

// ✅ Optional: Add default headers (like JSON)
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Attach bearer token from sessionStorage if present
axios.interceptors.request.use((config) => {
  const token = typeof window !== 'undefined' ? sessionStorage.getItem('token') : null;
  if (token) {
    config.headers = config.headers || {};
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Global 401 handler
axios.interceptors.response.use(
  (resp) => resp,
  (error) => {
    if (error?.response?.status === 401) {
      if (typeof window !== 'undefined') {
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('user');
        // best-effort redirect
        try { window.location.href = '/login'; } catch (_) {}
      }
    }
    return Promise.reject(error);
  }
);

export default axios;
