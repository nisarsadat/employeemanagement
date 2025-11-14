<template>
  <v-dialog
    transition="dialog-top-transition"
    v-model="UsersRepository.createDialog"
    width="700px"
    class="form-wrapper"
  >
    <template #default="{ isActive }">
      <v-card class="px-3" rounded="xl">
        <v-card-title class="px-6 py-4 d-flex justify-space-between">
          <h2 class="text-h5 font-weight-bold">{{ t('د کارونکي جوړول') }}</h2>
          <v-btn
            variant="text"
            @click="isActive.value = false"
            icon="mdi-close"
            density="comfortable"
          />
        </v-card-title>

        <v-divider />

        <v-card-text class="pa-6">
          <v-form ref="formRef" class="w-full gap-4">
            <!-- Avatar -->
            <div class="d-flex justify-center mb-6">
              <div
                class="image-upload-container"
                @click="openImagePicker"
                @mouseover="hoverImage = true"
                @mouseleave="hoverImage = false"
              >
                <v-avatar size="120" class="relative">
                  <v-img v-if="formData.imagePreview" :src="formData.imagePreview" cover />
                  <v-icon v-else size="48" color="grey-lighten-1">mdi-camera</v-icon>
                  <div
                    class="image-overlay d-flex align-center justify-center"
                    :class="{ 'show-overlay': hoverImage || !!formData.imagePreview }"
                  >
                    <div v-if="formData.imagePreview" class="d-flex gap-2">
                      <v-btn
                        variant="flat"
                        color="white"
                        icon="mdi-pencil"
                        size="x-small"
                        @click.stop="openImagePicker"
                        class="action-btn"
                      />
                      <v-btn
                        variant="flat"
                        color="white"
                        icon="mdi-delete"
                        size="x-small"
                        @click.stop="removeImage"
                        class="action-btn"
                      />
                    </div>
                    <v-icon v-else size="32" color="white">mdi-plus</v-icon>
                  </div>
                </v-avatar>
                <input
                  type="file"
                  ref="imageInput"
                  accept="image/*"
                  class="d-none"
                  @change="handleImageUpload"
                />
              </div>
            </div>

            <!-- Name / Username -->
            <div class="d-flex gap-4" style="width: 100%">
              <v-text-field
                v-model="formData.name"
                variant="outlined"
                density="compact"
                :label="t('نوم')"
                :rules="[rules.required]"
                single-line
                bg-color="grey-lighten-5"
                rounded="lg"
              />
              <v-text-field
                v-model="formData.username"
                variant="outlined"
                density="compact"
                :label="t('یوزر نېم')"
                :rules="[rules.required]"
                single-line
                bg-color="grey-lighten-5"
                rounded="lg"
              />
            </div>

            <!-- Password -->
            <v-text-field
              v-model="formData.password"
              variant="outlined"
              density="compact"
              :label="t('پاسورډ')"
              :rules="[rules.required, rules.password]"
              single-line
              bg-color="grey-lighten-5"
              rounded="lg"
              :type="showPassword ? 'text' : 'password'"
              :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPassword = !showPassword"
            />

            <!-- Role -->
            <v-select
              v-model="formData.role"
              :items="UsersRepository.rolesAll"
              item-title="name"
              item-value="name"
              :return-object="false"
              :label="t('Role')"
              variant="outlined"
              density="compact"
              clearable
              bg-color="grey-lighten-5"
              rounded="lg"
            />
          </v-form>
        </v-card-text>

        <div class="justify-start pl-6 pb-6">
          <v-btn
            color="primary"
            variant="flat"
            :disabled="!canCreate"
            @click="createuser"
            rounded="lg"
            size="large"
            :loading="UsersRepository.loading"
          >
            {{ t('ثبت') }}
          </v-btn>
        </div>
      </v-card>
    </template>
  </v-dialog>
</template>

<script setup>
  import { reactive, ref, onMounted, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useUsersRepository } from '../../stores/UsersRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';

  const { t } = useI18n();
  const UsersRepository = useUsersRepository();
  const auth = useLoginRepository();

  const formRef = ref(null);
  const imageInput = ref(null);
  const showPassword = ref(false);
  const hoverImage = ref(false);
  const selectedImageFile = ref(null);

  const formData = reactive({
    name: '',
    username: '',
    password: '',
    imagePreview: null,
    role: '',
  });

  onMounted(() => {
    UsersRepository.FetchRolesAll();
  });

  const openImagePicker = () => imageInput.value?.click();
  const removeImage = () => {
    formData.imagePreview = null;
    selectedImageFile.value = null;
    if (imageInput.value) imageInput.value.value = '';
  };
  const handleImageUpload = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) return alert('یوازې انځور انتخاب کړه!');
    selectedImageFile.value = file;
    const r = new FileReader();
    r.onload = (ev) => (formData.imagePreview = ev.target?.result);
    r.readAsDataURL(file);
  };

  const resetForm = () => {
    formData.name = '';
    formData.username = '';
    formData.password = '';
    formData.role = '';
    removeImage();
    formRef.value?.resetValidation?.();
  };

  const createuser = async () => {
    const { valid } = await formRef.value.validate();
    if (!valid) return;
    const fd = new FormData();
    fd.append('name', formData.name);
    fd.append('username', formData.username);
    fd.append('password', formData.password);
    if (formData.role) fd.append('role', formData.role);
    if (selectedImageFile.value) fd.append('image', selectedImageFile.value);
    await UsersRepository.CreateUser(fd);
    resetForm();
  };

  const rules = {
    required: (v) => !!v || 'دا فیلډ ضروري دی',
    password: (v) => (v?.length ?? 0) >= 6 || 'کم از کم ۶ حروف',
  };
  const canCreate = computed(() => auth.can('users.create'));
</script>

<style scoped>
  .image-upload-container {
    position: relative;
    cursor: pointer;
    border: 2px dashed rgba(0, 0, 0, 0.2);
    border-radius: 50%;
    padding: 8px;
    transition: all 0.3s;
  }
  .image-upload-container:hover {
    border-color: rgba(var(--v-theme-primary), 0.8);
    background-color: rgba(var(--v-theme-primary), 0.05);
    transform: scale(1.02);
  }
  .image-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.3s;
  }
  .show-overlay {
    opacity: 1;
  }
  .action-btn {
    background: rgba(255, 255, 255, 0.2) !important;
    backdrop-filter: blur(2px);
  }
</style>
