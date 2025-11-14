<template>
  <v-container fluid class="d-flex justify-center align-center" style="height:100vh;">
    <v-card width="420" class="pa-6" rounded="xl" elevation="6">
      <div class="text-center mb-6">
        <v-avatar size="70" class="mb-3" color="primary">
          <v-icon size="40" color="white">mdi-account-key</v-icon>
        </v-avatar>
        <h2 class="text-h6 font-weight-bold">Login to your account</h2>
      </div>

      <!-- use submit, not click -->
      <v-form ref="formRef" v-model="validForm" lazy-validation @submit.prevent="login">
        <v-text-field
          v-model.trim="form.username"
          label="Username"
          prepend-inner-icon="mdi-account"
          density="comfortable"
          variant="outlined"
          rounded="lg"
          :rules="[rules.required]"
        />

        <v-text-field
          v-model="form.password"
          label="Password"
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
          Login
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
import { ref } from "vue";
import { useLoginRepository } from "@/stores/LoginRepository";

const LoginRepository = useLoginRepository();

const form = ref({ username: "", password: "" });
const formRef = ref(null);
const validForm = ref(false);
const showPassword = ref(false);

const rules = {
  required: v => !!v || "Required",
  min6: v => (v && v.length >= 6) || "Min 6 characters",
};

const login = async () => {
  const { valid } = await formRef.value.validate();
  if (!valid || LoginRepository.loading) return;
  await LoginRepository.Login(form.value);
};
</script>

<style scoped>
.v-card { transition: all 0.25s ease; }
.v-card:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(0,0,0,.08); }
</style>
