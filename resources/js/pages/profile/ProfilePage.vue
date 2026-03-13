<template>
  <AppBar
    :title="t('Profile')"
    :subtitle="t('Manage your account information and password')"
    class="app-bar-transparent"
  >
  </AppBar>
  <v-container fluid class="profile-container pa-0" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Subtle background pattern -->
    <div class="profile-bg-pattern" />

    <div class="profile-content pa-6">
      <!-- Welcome header with decorative element -->
      <div class="welcome-section mb-6 d-flex align-center">
        <v-avatar size="48" class="welcome-avatar mr-3" color="primary-lighten-1">
          <span class="text-h6 font-weight-medium text-primary">{{ initials }}</span>
        </v-avatar>
        <div>
          <div class="text-subtitle-2 text-medium-emphasis">{{ t('Welcome back') }}</div>
          <div class="text-h6 font-weight-bold">{{ profile.name || t('User') }}</div>
        </div>
      </div>

      <v-row class="profile-grid">
        <!-- Profile info section -->
        <v-col cols="12" lg="7" class="profile-col">
          <v-card
            rounded="xl"
            elevation="0"
            class="profile-card pa-6"
            :class="{ border: !isDarkMode }"
          >
            <!-- Card header with icon -->
            <div class="d-flex align-center mb-5">
              <div class="card-icon-wrapper mr-3">
                <v-icon icon="mdi-account-circle" color="primary" size="28" />
              </div>
              <div>
                <div class="text-h6 font-weight-bold">
                  {{ t('Profile Information') }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ t('Update your personal details') }}
                </div>
              </div>
            </div>

            <v-form ref="profileFormRef" @submit.prevent="submitProfile">
              <!-- Avatar section with elegant design -->
              <div class="avatar-section mb-6">
                <div class="d-flex flex-column align-center">
                  <div class="avatar-wrapper position-relative mb-2">
                    <v-avatar size="120" class="profile-avatar elevation-3">
                      <v-img
                        v-if="profilePreview"
                        :src="profilePreview"
                        cover
                        class="avatar-image"
                      />
                      <div v-else class="avatar-placeholder">
                        <span class="text-h3 font-weight-medium text-white">
                          {{ initials }}
                        </span>
                      </div>
                    </v-avatar>

                    <v-btn
                      icon="mdi-camera"
                      size="small"
                      color="primary"
                      class="avatar-edit-btn elevation-2"
                      @click="pickImage"
                    />
                  </div>

                  <div class="text-caption text-medium-emphasis">
                    {{ t('Click the camera icon to change photo') }}
                  </div>
                </div>
              </div>

              <input
                ref="imageInputRef"
                type="file"
                accept="image/*"
                class="d-none"
                @change="onImageChange"
              />

              <!-- Form fields with improved styling -->
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="profile.name"
                    :label="t('Full Name')"
                    variant="outlined"
                    density="comfortable"
                    :rules="[rules.required]"
                    prepend-inner-icon="mdi-account-outline"
                    class="custom-text-field"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="profile.username"
                    :label="t('Username')"
                    variant="outlined"
                    density="comfortable"
                    :rules="[rules.required]"
                    prepend-inner-icon="mdi-at"
                    class="custom-text-field"
                  />
                </v-col>

                <v-col cols="12">
                  <v-text-field
                    :model-value="roleText"
                    :label="t('Role')"
                    variant="outlined"
                    density="comfortable"
                    prepend-inner-icon="mdi-shield-account-outline"
                    class="custom-text-field"
                    readonly
                    bg-color="grey-lighten-4"
                  />
                </v-col>
              </v-row>

              <v-divider class="my-4" />

              <!-- Form actions -->
              <div class="d-flex justify-end">
                <v-btn
                  color="primary"
                  size="large"
                  rounded="pill"
                  :loading="repo.loading"
                  @click="submitProfile"
                  class="px-8 save-btn"
                  elevation="0"
                >
                  <v-icon start icon="mdi-content-save-outline" />
                  {{ t('Save Changes') }}
                </v-btn>
              </div>
            </v-form>
          </v-card>
        </v-col>

        <!-- Change password section -->
        <v-col cols="12" lg="5" class="profile-col">
          <v-card
            rounded="xl"
            elevation="0"
            class="profile-card pa-6"
            :class="{ border: !isDarkMode }"
          >
            <!-- Card header with icon -->
            <div class="d-flex align-center mb-5">
              <div class="card-icon-wrapper mr-3">
                <v-icon icon="mdi-shield-lock" color="primary" size="28" />
              </div>
              <div>
                <div class="text-h6 font-weight-bold">
                  {{ t('Security') }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ t('Change your password') }}
                </div>
              </div>
            </div>

            <v-form ref="passwordFormRef" @submit.prevent="submitPassword">
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="passwordForm.current_password"
                    :label="t('Current Password')"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    variant="outlined"
                    density="comfortable"
                    :rules="[rules.required]"
                    prepend-inner-icon="mdi-lock-outline"
                    :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                    class="custom-text-field"
                    @click:append-inner="showCurrentPassword = !showCurrentPassword"
                  />
                </v-col>

                <v-col cols="12">
                  <v-text-field
                    v-model="passwordForm.new_password"
                    :label="t('New Password')"
                    :type="showNewPassword ? 'text' : 'password'"
                    variant="outlined"
                    density="comfortable"
                    :rules="[rules.required, rules.min6]"
                    prepend-inner-icon="mdi-lock-plus-outline"
                    :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'"
                    class="custom-text-field"
                    :disabled="!passwordForm.current_password"
                    @click:append-inner="showNewPassword = !showNewPassword"
                  />
                </v-col>

                <v-col cols="12">
                  <v-text-field
                    v-model="passwordForm.new_password_confirmation"
                    :label="t('Confirm New Password')"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    variant="outlined"
                    density="comfortable"
                    :rules="[rules.required, rules.passwordMatch]"
                    prepend-inner-icon="mdi-lock-check-outline"
                    class="custom-text-field"
                    :color="confirmPasswordColor"
                    :base-color="confirmPasswordColor"
                    :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
                    :disabled="!passwordForm.current_password || !passwordForm.new_password"
                    @click:append-inner="showConfirmPassword = !showConfirmPassword"
                  >
                    <template v-if="passwordForm.new_password" #details>
                      <div
                        v-if="showPasswordMatch"
                        class="password-match-message success-message mt-1"
                      >
                        <v-icon icon="mdi-check-circle" size="16" color="success" />
                        <span class="text-caption ml-1">{{ t('Passwords match') }}</span>
                      </div>
                    </template>
                  </v-text-field>
                </v-col>
              </v-row>

              <v-divider class="my-4" />

              <!-- Form actions -->
              <div class="d-flex justify-end">
                <v-btn
                  color="error"
                  size="large"
                  rounded="pill"
                  :loading="repo.loading"
                  @click="submitPassword"
                  class="px-8 change-password-btn"
                  elevation="0"
                >
                  <v-icon start icon="mdi-lock-reset" />
                  {{ t('Update Password') }}
                </v-btn>
              </div>
            </v-form>
          </v-card>

          <!-- Additional security tip card -->
          <v-card
            rounded="xl"
            elevation="0"
            class="security-tip-card mt-4 pa-4"
            color="primary-lighten-5"
          >
            <div class="d-flex align-center">
              <v-icon icon="mdi-shield-check" color="primary" size="24" class="mr-3" />
              <div class="text-body-2 text-medium-emphasis">
                {{
                  t(
                    'Use a strong password with at least 8 characters including numbers and symbols'
                  )
                }}
              </div>
            </div>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>

<script setup>
  import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { toast } from 'vue3-toastify';
  import AppBar from '@/components/AppBar.vue';
  import { useLoginRepository } from '@/stores/LoginRepository';
  import { useTheme } from 'vuetify';

  const { t, locale } = useI18n();
  const repo = useLoginRepository();
  const theme = useTheme();

  const isRtl = computed(() => ['fa', 'ps'].includes(locale.value));
  const isDarkMode = computed(() => theme.global.name.value === 'dark');

  const profileFormRef = ref(null);
  const passwordFormRef = ref(null);
  const imageInputRef = ref(null);
  const showCurrentPassword = ref(false);
  const showNewPassword = ref(false);
  const showConfirmPassword = ref(false);
  const profile = reactive({
    name: '',
    username: '',
    image: null,
    image_url: null,
    roles: [],
  });

  const passwordForm = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
  });

  const previewUrl = ref(null);

  const rules = {
    required: (v) => !!v || t('This field is required'),
    min6: (v) => !v || String(v).length >= 6 || t('Minimum 6 characters required'),
    passwordMatch: (v) =>
      v === passwordForm.new_password || t('Password confirmation does not match'),
  };

  const roleText = computed(() => {
    return Array.isArray(profile.roles) && profile.roles.length ? profile.roles.join(', ') : '-';
  });

  const initials = computed(() => {
    const text = profile.name || repo.user?.name || 'U';
    return text
      .split(' ')
      .map((s) => s?.[0] || '')
      .join('')
      .slice(0, 2)
      .toUpperCase();
  });

  const profilePreview = computed(() => {
    return previewUrl.value || profile.image_url || null;
  });

  const showPasswordMismatch = computed(() => {
    return (
      !!passwordForm.new_password &&
      !!passwordForm.new_password_confirmation &&
      passwordForm.new_password_confirmation !== passwordForm.new_password
    );
  });

  const showPasswordMatch = computed(() => {
    return (
      !!passwordForm.new_password &&
      !!passwordForm.new_password_confirmation &&
      passwordForm.new_password_confirmation === passwordForm.new_password
    );
  });

  const confirmPasswordColor = computed(() => {
    if (!passwordForm.new_password_confirmation) return 'primary';
    return showPasswordMatch.value ? 'success' : 'error';
  });

  function revokePreview() {
    if (previewUrl.value) {
      URL.revokeObjectURL(previewUrl.value);
      previewUrl.value = null;
    }
  }

  const loadProfile = async () => {
    try {
      const payload = await repo.fetchProfile();

      revokePreview();

      profile.name = payload?.name ?? '';
      profile.username = payload?.username ?? '';
      profile.image = null;
      profile.image_url = payload?.image_url ?? null;
      profile.roles = payload?.roles ?? [];
    } catch (err) {
      toast.error(err?.response?.data?.message || t('Failed to load profile'));
    }
  };

  const pickImage = () => {
    imageInputRef.value?.click();
  };

  const onImageChange = (e) => {
    const file = e?.target?.files?.[0];
    if (!file) return;

    revokePreview();

    profile.image = file;
    previewUrl.value = URL.createObjectURL(file);
  };

  const submitProfile = async () => {
    const result = await profileFormRef.value?.validate?.();
    if (result && !result.valid) return;

    try {
      const formData = new FormData();
      formData.append('name', profile.name);
      formData.append('username', profile.username);

      if (profile.image instanceof File) {
        formData.append('image', profile.image);
      }

      await repo.updateProfile(formData);
      await loadProfile();
      toast.success(t('Profile updated successfully'));
    } catch (err) {
      const msg =
        err?.response?.data?.message ||
        Object.values(err?.response?.data?.errors || {})?.flat?.()?.[0] ||
        t('Failed to update profile');

      toast.error(msg);
    }
  };
  const resetPasswordForm = () => {
    passwordForm.current_password = '';
    passwordForm.new_password = '';
    passwordForm.new_password_confirmation = '';

    showCurrentPassword.value = false;
    showNewPassword.value = false;
    showConfirmPassword.value = false;

    passwordFormRef.value?.resetValidation?.();
  };

  const submitPassword = async () => {
    const result = await passwordFormRef.value?.validate?.();
    if (result && !result.valid) return;

    try {
      const response = await repo.changePassword({
        current_password: passwordForm.current_password,
        new_password: passwordForm.new_password,
        new_password_confirmation: passwordForm.new_password_confirmation,
      });

      resetPasswordForm();
      toast.success(t('Password changed successfully'));

      if (response?.force_logout) {
        await repo.Logout();
      }
    } catch (err) {
      const msg =
        err?.response?.data?.message ||
        Object.values(err?.response?.data?.errors || {})?.flat?.()?.[0] ||
        t('Failed to change password');

      toast.error(msg);
    }
  };

  onMounted(async () => {
    await loadProfile();
  });

  onBeforeUnmount(() => {
    revokePreview();
  });
</script>

<style scoped>
  .profile-container {
    min-height: 100vh;
    position: relative;
    background: rgb(var(--v-theme-background));
  }

  .profile-bg-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 200px;
    background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 0.05) 0%,
      rgba(var(--v-theme-primary), 0.02) 100%
    );
    pointer-events: none;
  }

  .app-bar-transparent {
    background: transparent !important;
    position: relative;
    z-index: 2;
  }

  .profile-content {
    position: relative;
    z-index: 2;
    max-width: 1400px;
    margin: 0 auto;
  }

  .welcome-section {
    background: rgba(var(--v-theme-primary), 0.03);
    padding: 12px 20px;
    border-radius: 50px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(var(--v-theme-primary), 0.1);
    display: inline-flex;
    width: auto;
  }

  .welcome-avatar {
    background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 0.1),
      rgba(var(--v-theme-primary), 0.05)
    );
    border: 2px solid rgba(var(--v-theme-primary), 0.2);
  }

  .profile-grid {
    margin-top: 8px;
  }

  .profile-col {
    transition: transform 0.3s ease;
  }

  .profile-card {
    background: rgb(var(--v-theme-surface));
    border-radius: 24px !important;
    transition: all 0.3s ease;
    height: 100%;
  }

  .profile-card.border {
    border: 1px solid rgba(var(--v-theme-primary), 0.1) !important;
  }

  .profile-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08) !important;
  }

  .card-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    background: rgba(var(--v-theme-primary), 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .avatar-section {
    position: relative;
  }

  .avatar-wrapper {
    position: relative;
    display: inline-block;
  }

  .profile-avatar {
    border: 4px solid rgb(var(--v-theme-surface));
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
    transition: transform 0.3s ease;
  }

  .profile-avatar:hover {
    transform: scale(1.02);
  }

  .avatar-image {
    transition: transform 0.3s ease;
  }

  .avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgb(var(--v-theme-primary)), rgb(var(--v-theme-secondary)));
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .avatar-edit-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    transform: translate(10%, 10%);
    transition: all 0.3s ease !important;
    border: 2px solid rgb(var(--v-theme-surface)) !important;
  }

  .avatar-edit-btn:hover {
    transform: translate(10%, 10%) scale(1.1);
    background: rgb(var(--v-theme-primary)) !important;
  }

  .avatar-edit-btn:hover :deep(.v-icon) {
    color: white !important;
  }

  .custom-text-field :deep(.v-field) {
    border-radius: 16px !important;
    transition: all 0.3s ease;
  }

  .custom-text-field :deep(.v-field:hover) {
    border-color: rgb(var(--v-theme-primary)) !important;
  }

  .custom-text-field :deep(.v-field--focused) {
    box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.15);
  }

  .save-btn {
    background: linear-gradient(135deg, rgb(var(--v-theme-primary)), rgb(var(--v-theme-secondary)));
    transition: all 0.3s ease !important;
  }

  .save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(var(--v-theme-primary), 0.3) !important;
  }

  .change-password-btn {
    transition: all 0.3s ease !important;
  }

  .change-password-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(var(--v-theme-error), 0.3) !important;
  }

  .password-match-message {
    display: flex;
    align-items: center;
    animation: slideIn 0.3s ease;
  }

  .security-tip-card {
    border-radius: 16px !important;
    border: 1px solid rgba(var(--v-theme-primary), 0.2) !important;
    animation: fadeInUp 0.5s ease;
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Responsive adjustments */
  @media (max-width: 960px) {
    .profile-content {
      padding: 16px !important;
    }

    .welcome-section {
      width: 100%;
      justify-content: center;
    }

    .profile-card {
      padding: 20px !important;
    }
  }

  @media (max-width: 600px) {
    .profile-card {
      padding: 16px !important;
    }

    .avatar-edit-btn {
      transform: scale(0.9);
    }

    .avatar-edit-btn:hover {
      transform: scale(1);
    }
  }

  /* RTL support */
  [dir='rtl'] .welcome-avatar {
    margin-right: 0 !important;
    margin-left: 12px !important;
  }

  [dir='rtl'] .card-icon-wrapper {
    margin-right: 0 !important;
    margin-left: 12px !important;
  }

  [dir='rtl'] .avatar-edit-btn {
    right: auto;
    left: 0;
    transform: translate(-10%, 10%);
  }

  [dir='rtl'] .avatar-edit-btn:hover {
    transform: translate(-10%, 10%) scale(1.1);
  }
</style>