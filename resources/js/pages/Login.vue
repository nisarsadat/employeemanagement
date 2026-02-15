<template>
  <v-container fluid class="d-flex justify-center align-center" style="height: 100vh">
    <v-card width="420" class="pa-6" rounded="xl" elevation="6">
      <div class="text-center mb-6">
        <v-avatar size="70" class="mb-3" color="primary">
          <v-icon size="40" color="white">mdi-account-key</v-icon>
        </v-avatar>
        <h2 class="text-h6 font-weight-bold">
          {{ t('login.title') || 'Login to your account' }}
        </h2>
      </div>

      <!-- ✅ Language selector FIRST -->
      <v-select
        v-model="selectedLang"
        :items="langItems"
        item-title="title"
        item-value="lang"
        prepend-inner-icon="mdi-web"
        density="comfortable"
        variant="outlined"
        rounded="lg"
        class="mb-3"
        :label="t('language') || 'Language'"
        @update:modelValue="changeLanguage"
      />

      <!-- use submit, not click -->
      <v-form ref="formRef" v-model="validForm" lazy-validation @submit.prevent="login">
        <v-text-field
          v-model.trim="form.username"
          :label="t('username') || 'Username'"
          prepend-inner-icon="mdi-account"
          density="comfortable"
          variant="outlined"
          rounded="lg"
          :rules="[rules.required]"
        />

        <v-text-field
          v-model="form.password"
          :label="t('password') || 'Password'"
          :type="showPassword ? 'text' : 'password'"
          prepend-inner-icon="mdi-lock"
          :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
          @click:append-inner="showPassword = !showPassword"
          density="comfortable"
          variant="outlined"
          rounded="lg"
          :rules="[rules.required, rules.min6]"
        />

        <v-btn
          type="submit"
          block
          color="primary"
          class="mt-4"
          rounded="lg"
          size="large"
          :loading="LoginRepository.loading"
          :disabled="LoginRepository.loading"
        >
          {{ t('login_button') || 'login_button' }}
        </v-btn>
      </v-form>

      <v-alert
        v-if="LoginRepository.error"
        type="error"
        class="mt-4"
        rounded="lg"
        variant="tonal"
      >
        {{ LoginRepository.error }}
      </v-alert>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useLoginRepository } from "@/stores/LoginRepository";

const LoginRepository = useLoginRepository();
const { t, locale } = useI18n();

const form = ref({ username: "", password: "" });
const formRef = ref(null);
const validForm = ref(false);
const showPassword = ref(false);

const rules = {
  required: (v) => !!v || "Required",
  min6: (v) => (v && v.length >= 6) || "Min 6 characters",
};

// ✅ language dropdown data
const langItems = computed(() => [
  { title: t("english") || "English", lang: "en" },
  { title: t("dari") || "Dari", lang: "fa" },
  { title: t("pashto") || "Pashto", lang: "ps" },
]);

const applyGlobalDir = (lang) => {
  const rtl = lang === "fa" || lang === "ps";
  document.documentElement.setAttribute("dir", rtl ? "rtl" : "ltr");
};

const selectedLang = ref("ps"); // ✅ default Pashto

const changeLanguage = (lang) => {
  selectedLang.value = lang;
  locale.value = lang;
  localStorage.setItem("apartment-lang", lang);
  applyGlobalDir(lang);
};

// ✅ load language BEFORE user logs in
onMounted(() => {
  const saved = localStorage.getItem("apartment-lang") || "ps"; // ✅ default ps
  changeLanguage(saved);
});

// keep selectedLang synced if something else changes locale
watch(locale, (l) => {
  selectedLang.value = l;
});

const login = async () => {
  const { valid } = await formRef.value.validate();
  if (!valid || LoginRepository.loading) return;
  await LoginRepository.Login(form.value);
};
</script>

<style scoped>
.v-card {
  transition: all 0.25s ease;
}
.v-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
}
</style>
