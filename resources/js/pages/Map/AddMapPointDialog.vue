<template>
  <div v-if="modelValue" class="ap-overlay">
    <div class="ap-dialog">
      <div class="ap-header">
        <h3>ثبت شخص / موقعیت جدید</h3>
        <button class="ap-close" @click="close">×</button>
      </div>

      <form @submit.prevent="onSubmit" class="ap-form">
        <!-- TWO-COLUMN WRAPPER -->
        <div class="two-col-wrapper">
          <!-- LEFT COLUMN: IMAGE -->
          <div class="img-column">
            <label class="img-label">عکس</label>

            <div class="img-box" @click="pickImage">
              <img v-if="previewUrl" :src="previewUrl" class="img-preview" />
              <div v-else class="img-placeholder">
                <span>+</span>
              </div>
            </div>

            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              class="hidden-input"
              @change="onFileChange"
            />
          </div>

          <!-- RIGHT COLUMN: FORM FIELDS -->
          <div class="form-column">
            <div class="ap-field">
              <label>نام *</label>
              <input v-model="form.name" type="text" required />
            </div>

            <div class="ap-field">
              <label>نام پدر</label>
              <input v-model="form.father_name" type="text" />
            </div>

            <div class="ap-field">
              <label>کد نمبر</label>
              <input v-model="form.code_number" type="text" />
            </div>
          </div>
        </div>
        <div class="ap-field">
          <label>شماره تماس</label>
          <input v-model="form.phone" type="text" />
        </div>
        <!-- FULL-WIDTH FIELDS -->
        <div class="ap-field">
          <label>کواردینات *</label>
          <input v-model="form.coordinates" type="text" required />
          <small class="ap-help">به طور خودکار از نقشه گرفته شده است</small>
        </div>

        <div class="ap-field">
          <label>یادداشت</label>
          <textarea v-model="form.note" rows="3"></textarea>
        </div>

        <!-- Actions -->
        <div class="ap-actions">
          <button type="button" class="btn-secondary" @click="close">لغو</button>
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'در حال ثبت...' : 'ثبت' }}
          </button>
        </div>

        <p v-if="error" class="ap-error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
  import { ref, watch } from 'vue';
  import { useMapPointsRepository } from '../../stores/MapPointsRepository';

  const mapPointsStore = useMapPointsRepository();

  const props = defineProps({
    modelValue: Boolean,
    coordinates: String,
  });

  const emit = defineEmits(['update:modelValue', 'saved']);

  const form = ref({
    name: '',
    father_name: '',
    code_number: '',
    coordinates: '',
    phone: '',
    note: '',
  });

  const file = ref(null);
  const fileInput = ref(null);
  const previewUrl = ref(null);

  const loading = ref(false);
  const error = ref('');

  watch(
    () => props.coordinates,
    (v) => {
      if (props.modelValue) form.value.coordinates = v || '';
    }
  );

  watch(
    () => props.modelValue,
    (open) => {
      if (open) {
        form.value.coordinates = props.coordinates || '';
        error.value = '';
      }
    }
  );

  function pickImage() {
    fileInput.value?.click();
  }

  function onFileChange(e) {
    const f = e.target.files[0];
    file.value = f;

    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);

    if (f) previewUrl.value = URL.createObjectURL(f);
  }

  function close() {
    emit('update:modelValue', false);
  }

  async function onSubmit() {
    if (!form.value.name || !form.value.coordinates) {
      error.value = 'نام و کواردینات ضروری هستند.';
      return;
    }

    loading.value = true;
    error.value = '';

    try {
      const fd = new FormData();

      Object.entries(form.value).forEach(([k, v]) => fd.append(k, v || ''));
      if (file.value) fd.append('picture', file.value);

      const created = await mapPointsStore.CreateMapPoint(fd);

      emit('saved', created);
      close();
    } catch (e) {
      error.value = 'خطایی رخ داد.';
    } finally {
      loading.value = false;
    }
  }
</script>

<style scoped>
  /* --- Overlay --- */
  .ap-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
  }

  /* --- Dialog Box --- */
  .ap-dialog {
    background: white;
    border-radius: 12px;
    width: 480px;
    padding: 14px 16px;
    direction: rtl;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
  }

  /* --- Header --- */
  .ap-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .ap-header h3 {
    font-size: 15px;
    font-weight: 600;
  }
  .ap-close {
    border: none;
    background: none;
    font-size: 20px;
    cursor: pointer;
  }

  /* --- Form Layout --- */
  .ap-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  /* TWO COLUMN LAYOUT */
  .two-col-wrapper {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 12px;
    align-items: flex-start;
  }

  /* --- IMAGE COLUMN --- */
  .img-column {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .img-label {
    font-size: 12px;
    color: #374151;
  }

  .img-box {
    width: 130px;
    height: 130px;
    border: 1px solid #d1d5db;
    background: #f3f4f6;
    border-radius: 8px;
    cursor: pointer;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .img-placeholder span {
    font-size: 28px;
    color: #6b7280;
  }

  .img-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .hidden-input {
    display: none;
  }

  /* --- Fields --- */
  .ap-field {
    display: flex;
    flex-direction: column;
  }
  .ap-field label {
    font-size: 12px;
    margin-bottom: 2px;
  }
  .ap-field input,
  .ap-field textarea {
    padding: 7px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    font-size: 13px;
  }

  /* --- Actions --- */
  .ap-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
  }

  .btn-primary,
  .btn-secondary {
    padding: 5px 14px;
    border-radius: 999px;
    font-size: 13px;
    cursor: pointer;
  }

  /* errors */
  .ap-error {
    color: #b91c1c;
    font-size: 12px;
  }
</style>
