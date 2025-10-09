import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
// import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export const useLoginRepository = defineStore("LoginRepository", () => {
    const router = useRouter();
    const loading = ref(false);
    const user = ref(JSON.parse(sessionStorage.getItem("user")) || null);
    const isLoggedIn = ref(!!sessionStorage.getItem("token"));
    const error = ref(null);
    const rail = ref(false);

    const Login = async (formData) => {
        try {
            loading.value = true;
            error.value = null;

            const response = await axios.post("login", formData);

            // Store token and user data
            sessionStorage.setItem("token", response.data.token);
            if (response.data.user) {
                sessionStorage.setItem(
                    "user",
                    JSON.stringify(response.data.user)
                );
                user.value = response.data.user;
            }

            isLoggedIn.value = true;

            toast.success("Login successful!", {
                position: "top-right",
                autoClose: 3000,
                hideProgressBar: false,
                closeOnClick: true,
                pauseOnHover: true,
                draggable: true,
                progress: undefined,
            });

            // Redirect to dashboard
            await router.push("/Dashboard");
        } catch (err) {
            console.error("Login error:", err);
            error.value =
                err.response?.data?.message ||
                "Login failed! Please check your credentials.";

            toast.error(error.value, {
                position: "top-right",
                autoClose: 10000,
                hideProgressBar: false,
                closeOnClick: true,
                pauseOnHover: true,
                draggable: true,
                progress: undefined,
            });

            throw err;
        } finally {
            loading.value = false;
        }
    };

    const Logout = async () => {
        try {
            const token = sessionStorage.getItem("token"); // 👈 get saved token

            await axios.post(
                "auth/logout",
                {},
                {
                    headers: {
                        Authorization: `Bearer ${token}`, // 👈 include token here
                    },
                }
            );

            sessionStorage.removeItem("token");
            sessionStorage.removeItem("user");
            isLoggedIn.value = false;
            user.value = null;

            toast.success("Logout successful!", {
                position: "top-right",
                autoClose: 3000,
                hideProgressBar: false,
                closeOnClick: true,
                pauseOnHover: true,
                draggable: true,
                progress: undefined,
            });

            await router.push("/");
        } catch (err) {
            toast.error("Logout failed! Please try again.", {
                position: "top-right",
                autoClose: 3000,
                hideProgressBar: false,
                closeOnClick: true,
                pauseOnHover: true,
                draggable: true,
                progress: undefined,
            });
            throw err;
        }
    };
    const toggleRail = async () => {
        rail.value = !rail.value;
    };
    return {
        loading,
        user,
        isLoggedIn,
        error,
        Login,
        Logout,
        toggleRail,
        rail
    };
});
