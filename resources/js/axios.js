// resources/js/axios.js
import axios from "axios";

// ✅ Base URL for all API requests
axios.defaults.baseURL = "/api";

// ✅ Required so Sanctum can send & receive cookies
axios.defaults.withCredentials = true;

// ✅ Optional: Add default headers (like JSON)
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

export default axios;
