<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('مشخصات سیستم')" />

    <v-card class="mt-6 mx-2" rounded="xl" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-text>
        <v-form ref="formRef" @submit.prevent="save">
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model.trim="form.name"
                :label="t('system_name')"
                variant="outlined"
                density="comfortable"
                rounded="lg"
                :rules="[rules.required]"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="form.default_language"
                :items="langItems"
                item-title="title"
                item-value="lang"
                :label="t('زبان پیش‌فرض')"
                variant="outlined"
                density="comfortable"
                rounded="lg"
                :rules="[rules.required]"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-file-input
                v-model="logoFile"
                :label="t('لوگو')"
                accept="image/*"
                prepend-icon=""
                prepend-inner-icon="mdi-image"
                variant="outlined"
                density="comfortable"
                rounded="lg"
                show-size
                clearable
              />
              <v-img
                v-if="logoPreview"
                :src="logoPreview"
                width="72"
                height="72"
                class="mt-2 rounded-lg"
                cover
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model.trim="form.admin_email"
                :label="t('ایمیل مدیر')"
                :hint="t('اختیاری')"
                persistent-hint
                variant="outlined"
                density="comfortable"
                rounded="lg"
                :rules="[rules.email]"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model.trim="form.phone"
                :label="t('شماره تماس')"
                :hint="t('اختیاری')"
                persistent-hint
                variant="outlined"
                density="comfortable"
                rounded="lg"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model.trim="form.address"
                :label="t('آدرس')"
                :hint="t('اختیاری')"
                persistent-hint
                variant="outlined"
                density="comfortable"
                rounded="lg"
              />
            </v-col>

            <v-col cols="12" class="d-flex justify-end">
              <v-btn
                type="submit"
                color="primary"
                rounded="lg"
                size="large"
                class="text-none"
                :loading="repo.loading"
              >
                {{ t('ذخیره') }}
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>

    <v-snackbar v-model="saved" color="success" timeout="2500">
      {{ t('ذخیره شد') }}
    </v-snackbar>

    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import { useSystemProfileRepository } from '@/stores/SystemProfileRepository';

  const { t, locale } = useI18n();
  const isRtl = computed(() => locale.value === 'fa' || locale.value === 'ps');
  const repo = useSystemProfileRepository();

  const formRef = ref(null);
  const logoFile = ref(null);
  const saved = ref(false);
  const showError = ref(false);
  const errorMessage = ref('');

  const form = ref({
    name: '',
    default_language: 'ps',
    admin_email: '',
    phone: '',
    address: '',
  });

  const langItems = computed(() => [
    { title: t('english'), lang: 'en' },
    { title: t('dari'), lang: 'fa' },
    { title: t('pashto'), lang: 'ps' },
  ]);

  const rules = {
    required: (v) => !!String(v || '').trim() || t('Required'),
    email: (v) => !String(v || '').trim() || /.+@.+\..+/.test(v) || t('ایمیل مدیر'),
  };

  const pickedLogo = computed(() => {
    const value = logoFile.value;
    if (!value) return null;
    return Array.isArray(value) ? value[0] || null : value;
  });

  const localPreview = ref('');

  watch(pickedLogo, (file) => {
    if (localPreview.value) URL.revokeObjectURL(localPreview.value);
    localPreview.value = file ? URL.createObjectURL(file) : '';
  });

  const logoPreview = computed(() => localPreview.value || repo.logoUrl || '');

  function fillForm() {
    form.value = {
      name: repo.name,
      default_language: repo.defaultLanguage || 'ps',
      admin_email: repo.adminEmail,
      phone: repo.phone,
      address: repo.address,
    };
  }

  function applyLanguage(lang) {
    locale.value = lang;
    localStorage.setItem('apartment-lang', lang);
    const rtl = lang === 'fa' || lang === 'ps';
    document.documentElement.setAttribute('dir', rtl ? 'rtl' : 'ltr');
  }

  async function save() {
    const result = await formRef.value?.validate();
    if (!result?.valid) return;

    const body = new FormData();
    body.append('name', form.value.name.trim());
    body.append('default_language', form.value.default_language);
    body.append('admin_email', form.value.admin_email.trim());
    body.append('phone', form.value.phone.trim());
    body.append('address', form.value.address.trim());
    if (pickedLogo.value) body.append('logo', pickedLogo.value);

    try {
      await repo.save(body);
      applyLanguage(form.value.default_language);
      logoFile.value = null;
      fillForm();
      saved.value = true;
    } catch (error) {
      const errors = error?.response?.data?.errors;
      errorMessage.value = errors
        ? Object.values(errors).flat()[0]
        : error?.response?.data?.message || 'Error';
      showError.value = true;
    }
  }

  onMounted(async () => {
    await repo.ensureLoaded();
    fillForm();
  });
</script>
