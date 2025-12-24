<template>
  <v-dialog v-model="model" max-width="800" dir="rtl">
    <v-card rounded="xl" class="overflow-hidden">
      <!-- Header with Gradient -->
      <div class="bg-gradient-info py-5 px-4">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-avatar color="white" size="44" class="ms-3">
              <v-icon icon="mdi-bank" color="info" size="24"></v-icon>
            </v-avatar>
            <div>
              <h2 class="text-h5 font-weight-bold text-white mb-1">
                {{ editMode ? t('ویرایش حساب') : t('ثبت حساب جدید') }}
              </h2>
              <p class="text-white text-caption opacity-85 mb-0">
                {{ editMode ? t('اطلاعات حساب را ویرایش کنید') : t('حساب بانکی جدید را ثبت کنید') }}
              </p>
            </div>
          </div>
          <v-btn 
            icon="mdi-close" 
            variant="text" 
            color="white"
            size="small"
            @click="model = false"
          />
        </div>
      </div>

      <v-card-text class="pa-5">
        <v-form ref="formRef" @submit.prevent="submit" lazy-validation>
          <!-- Basic Information Section -->
          <div class="mb-6">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-info ms-3"></div>
              <h3 class="text-h6 font-weight-bold text-info">
                <v-icon icon="mdi-card-account-details" class="ms-2" size="20"></v-icon>
                {{ t('اطلاعات پایه حساب') }}
              </h3>
            </div>
            
            <v-row>
              <!-- Account Name -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100 elevation-1">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block text-right">
                    <v-icon icon="mdi-account" size="16" class="ms-1"></v-icon>
                    {{ t('نام حساب') }} *
                  </label>
                  <v-text-field
                    v-model="editor.name"
                    :placeholder="t('نام حساب را وارد کنید')"
                    variant="outlined"
                    :error="hasError('name')"
                    :error-messages="getErrorMessages('name')"
                    hide-details
                    :rules="[v => !!v || t('نام حساب الزامی است'), v => v.length >= 2 || t('نام حساب باید حداقل ۲ حرف باشد')]"
                    class="mt-1"
                    density="comfortable"
                    dir="rtl"
                  >
                    <template #prepend-inner>
                      <v-icon icon="mdi-pencil" size="18" class="ms-2 text-grey"></v-icon>
                    </template>
                  </v-text-field>
                  <div class="text-caption text-grey mt-2 text-right">
                    {{ t('مثال: حساب جاری شرکت، حساب پس‌انداز شخصی، ...') }}
                  </div>
                </v-card>
              </v-col>

              <!-- Account Type -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100 elevation-1">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block text-right">
                    <v-icon icon="mdi-format-list-bulleted-type" size="16" class="ms-1"></v-icon>
                    {{ t('نوع حساب') }} *
                  </label>
                  <v-select
                    v-model="editor.account_type_id"
                    :items="accountTypeOptions"
                    item-title="text"
                    item-value="value"
                    :placeholder="t('نوع حساب را انتخاب کنید')"
                    variant="outlined"
                    :error="hasError('account_type_id')"
                    :error-messages="getErrorMessages('account_type_id')"
                    hide-details
                    :rules="[v => !!v || t('نوع حساب الزامی است')]"
                    class="mt-1"
                    density="comfortable"
                    dir="rtl"
                  >
                    <template #prepend-inner>
                      <v-icon icon="mdi-tag" size="18" class="ms-2 text-grey"></v-icon>
                    </template>
                    <template #item="{ props, item: it }">
                      <v-list-item v-bind="props" dir="rtl">
                        <template #prepend>
                          <v-avatar :color="getAccountTypeColor(it.value)" size="32" class="ms-3">
                            <v-icon :icon="getAccountTypeIcon(it.value)" color="white" size="18"></v-icon>
                          </v-avatar>
                        </template>
                        <v-list-item-title class="text-body-2 font-weight-medium text-right">
                          {{ it.title }}
                        </v-list-item-title>
                      </v-list-item>
                    </template>
                  </v-select>
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Balance Information Section -->
          <div class="mb-6">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-amber ms-3"></div>
              <h3 class="text-h6 font-weight-bold text-amber-darken-3">
                <v-icon icon="mdi-cash" class="ms-2" size="20"></v-icon>
                {{ t('اطلاعات موجودی') }}
              </h3>
            </div>
            
            <v-row>
              <!-- Balance Input -->
              <v-col cols="12">
                <v-card variant="flat" class="border rounded-lg pa-4 elevation-1">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block text-right">
                    <v-icon icon="mdi-wallet" size="16" class="ms-1"></v-icon>
                    {{ t('موجودی اولیه') }}
                  </label>
                  <div class="d-flex align-center gap-3">
                    <v-text-field
                      v-model="editor.price"
                      type="number"
                      :placeholder="t('موجودی اولیه را وارد کنید')"
                      variant="outlined"
                      :error="hasError('price')"
                      :error-messages="getErrorMessages('price')"
                      hide-details
                      :rules="[
                        v => v >= 0 || t('موجودی نمی‌تواند منفی باشد')
                      ]"
                      class="flex-grow-1"
                      density="comfortable"
                      dir="ltr"
                    >
                      <template #prepend-inner>
                        <v-icon icon="mdi-currency-usd" size="18" class="ms-2 text-grey"></v-icon>
                      </template>
                      <template #append>
                        <span class="text-caption text-grey">افغانی</span>
                      </template>
                    </v-text-field>
                    
                    <!-- Balance Display Card -->
                    <v-card 
                      variant="flat" 
                      :color="editor.price >= 0 ? 'amber-lighten-5' : 'red-lighten-5'" 
                      class="pa-3 rounded-lg border d-flex align-center"
                      style="min-width: 140px"
                    >
                      <div class="text-center">
                        <div class="text-caption text-grey mb-1">{{ t('موجودی') }}</div>
                        <div :class="[
                          'text-h6 font-weight-bold',
                          editor.price >= 0 ? 'text-amber-darken-3' : 'text-red-darken-2'
                        ]">
                          {{ formatNumber(editor.price) }}
                        </div>
                        <div class="text-caption text-grey">افغانی</div>
                      </div>
                    </v-card>
                  </div>
                  <div class="text-caption text-grey mt-2 text-right">
                    <v-icon icon="mdi-information" size="14" class="ms-1"></v-icon>
                    {{ t('برای حساب‌های جدید، موجودی اولیه را وارد کنید') }}
                  </div>
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Details Section -->
          <div class="mb-2">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-blue-grey ms-3"></div>
              <h3 class="text-h6 font-weight-bold text-blue-grey">
                <v-icon icon="mdi-text-box" class="ms-2" size="20"></v-icon>
                {{ t('جزئیات و توضیحات') }}
              </h3>
            </div>
            
            <v-card variant="flat" class="border rounded-lg pa-4 elevation-1">
              <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block text-right">
                <v-icon icon="mdi-information-variant" size="16" class="ms-1"></v-icon>
                {{ t('جزئیات حساب') }}
              </label>
              <v-textarea
                v-model="editor.details"
                :placeholder="t('توضیحات مربوط به این حساب را وارد کنید...')"
                variant="outlined"
                :error="hasError('details')"
                :error-messages="getErrorMessages('details')"
                hide-details
                rows="3"
                auto-grow
                class="mt-1"
                dir="rtl"
                counter
                :maxlength="1000"
              >
                <template #prepend-inner>
                  <v-icon icon="mdi-pencil-box" size="18" class="ms-2 text-grey"></v-icon>
                </template>
              </v-textarea>
              <div class="text-caption text-grey mt-2 text-right">
                {{ t('مثال: شماره حساب، نام شعبه، مشخصات صاحب حساب، محدودیت‌ها، ...') }}
              </div>
            </v-card>
          </div>

          <!-- Summary Card -->
          <v-card variant="flat" color="info-lighten-5" class="border rounded-lg pa-4 mt-6" dir="rtl">
            <div class="d-flex align-center justify-space-between">
              <div>
                <div class="text-subtitle-2 text-grey-darken-2 mb-1">{{ t('خلاصه اطلاعات حساب') }}</div>
                <div class="text-caption text-grey">
                  {{ t('اطلاعات وارد شده را بررسی کنید') }}
                </div>
              </div>
              <v-icon icon="mdi-bank-check" color="info" size="24"></v-icon>
            </div>
            
            <v-divider class="my-3" />
            
            <v-row>
              <v-col cols="12" md="6">
                <div class="d-flex align-center mb-3">
                  <v-avatar :color="getAccountTypeColor(editor.account_type_id)" size="36" class="ms-3">
                    <v-icon :icon="getAccountTypeIcon(editor.account_type_id)" color="white" size="18"></v-icon>
                  </v-avatar>
                  <div>
                    <div class="text-caption text-grey">{{ t('نام حساب') }}</div>
                    <div class="text-body-2 font-weight-medium">
                      {{ editor.name || t('تعیین نشده') }}
                    </div>
                  </div>
                </div>
              </v-col>
              
              <v-col cols="12" md="6">
                <div class="d-flex align-center mb-3">
                  <v-avatar color="amber-lighten-4" size="36" class="ms-3">
                    <v-icon icon="mdi-cash" color="amber-darken-3"></v-icon>
                  </v-avatar>
                  <div>
                    <div class="text-caption text-grey">{{ t('موجودی') }}</div>
                    <div :class="[
                      'text-body-2 font-weight-medium',
                      editor.price >= 0 ? 'text-amber-darken-3' : 'text-red-darken-2'
                    ]">
                      {{ formatNumber(editor.price) }} افغانی
                    </div>
                  </div>
                </div>
              </v-col>
              
              <v-col cols="12" md="6">
                <div class="d-flex align-center">
                  <v-avatar color="blue-grey-lighten-4" size="36" class="ms-3">
                    <v-icon icon="mdi-tag" color="blue-grey"></v-icon>
                  </v-avatar>
                  <div>
                    <div class="text-caption text-grey">{{ t('نوع حساب') }}</div>
                    <div class="text-body-2 font-weight-medium text-info">
                      {{ getAccountTypeName(editor.account_type_id) || t('انتخاب نشده') }}
                    </div>
                  </div>
                </div>
              </v-col>
              
              <v-col cols="12" md="6">
                <div class="d-flex align-center">
                  <v-avatar color="green-lighten-4" size="36" class="ms-3">
                    <v-icon icon="mdi-check-circle" color="green"></v-icon>
                  </v-avatar>
                  <div>
                    <div class="text-caption text-grey">{{ t('وضعیت') }}</div>
                    <div class="text-body-2 font-weight-medium text-success">
                      {{ editor.id ? t('حساب موجود') : t('حساب جدید') }}
                    </div>
                  </div>
                </div>
              </v-col>
            </v-row>
          </v-card>
        </v-form>
      </v-card-text>

      <!-- Footer Actions -->
      <v-divider />

      <v-card-actions class="pa-4 d-flex justify-end gap-3 bg-grey-lighten-5" dir="rtl">
        <v-btn
          variant="outlined"
          color="grey-darken-2"
          @click="clearLocal"
          :disabled="busy"
          prepend-icon="mdi-delete-sweep"
          size="large"
          class="px-5"
        >
          {{ t('پاک کردن فرم') }}
        </v-btn>

        <v-btn
          color="info"
          variant="flat"
          @click="submit"
          :loading="busy"
          prepend-icon="mdi-content-save-check"
          size="large"
          class="px-6"
          elevation="2"
        >
          {{ editMode ? t('ذخیره تغییرات') : t('ثبت حساب') }}
        </v-btn>
      </v-card-actions>
    </v-card>

    <!-- Notification Toast -->
    <v-snackbar v-model="snack.open" :color="snack.color" timeout="3000" location="top" dir="rtl">
      <div class="d-flex align-center">
        <v-icon :icon="snack.icon" class="ms-2"></v-icon>
        <span>{{ snack.text }}</span>
      </div>
      <template #actions>
        <v-btn icon="mdi-close" variant="text" @click="snack.open = false"></v-btn>
      </template>
    </v-snackbar>
  </v-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editMode: { type: Boolean, default: false },
  value: { type: Object, default: () => ({}) },
  busy: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue", "save"]);

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit("update:modelValue", v),
});

const formRef = ref(null);
const itemErrors = reactive({});
const snack = reactive({ open: false, text: "", color: "primary", icon: "mdi-information" });

// Account type options
const accountTypeOptions = computed(() => [
  { value: 1, text: t('حساب جاری') },
  { value: 2, text: t('حساب پس‌انداز') },
  { value: 3, text: t('حساب سرمایه‌گذاری') },
  { value: 4, text: t('حساب اعتباری') },
  { value: 5, text: t('حساب قرض الحسنه') },
  { value: 6, text: t('حساب ارزی') },
  { value: 7, text: t('سایر') },
]);

const blankRow = () => ({
  id: null,
  name: "",
  price: 0,
  account_type_id: null,
  details: "",
});

const editor = reactive(blankRow());

// Helper functions
function formatNumber(num) {
  return new Intl.NumberFormat('fa-AF').format(num || 0);
}

function getAccountTypeColor(typeId) {
  const colors = {
    1: 'blue',      // جاری
    2: 'green',     // پس‌انداز
    3: 'amber',     // سرمایه‌گذاری
    4: 'orange',    // اعتباری
    5: 'purple',    // قرض الحسنه
    6: 'cyan',      // ارزی
    7: 'grey',      // سایر
  };
  return colors[typeId] || 'grey';
}

function getAccountTypeIcon(typeId) {
  const icons = {
    1: 'mdi-bank-transfer',    // جاری
    2: 'mdi-piggy-bank',       // پس‌انداز
    3: 'mdi-chart-line',       // سرمایه‌گذاری
    4: 'mdi-credit-card',      // اعتباری
    5: 'mdi-hand-heart',       // قرض الحسنه
    6: 'mdi-currency-usd',     // ارزی
    7: 'mdi-dots-horizontal',  // سایر
  };
  return icons[typeId] || 'mdi-bank';
}

function getAccountTypeName(typeId) {
  const type = accountTypeOptions.value.find(opt => opt.value === typeId);
  return type ? type.text : null;
}

function showSnack(text, color = "primary", icon = "mdi-information") {
  snack.text = text;
  snack.color = color;
  snack.icon = icon;
  snack.open = true;
}

function clearErrors() {
  Object.keys(itemErrors).forEach((k) => delete itemErrors[k]);
}

function hasError(field) {
  return !!itemErrors[field]?.length;
}

function getErrorMessages(field) {
  return itemErrors[field] || [];
}

function applyFromProps() {
  const v = props.value || {};
  Object.assign(editor, blankRow(), {
    id: v.id ?? null,
    name: v.name ?? "",
    price: v.price ?? 0,
    account_type_id: v.account_type_id ?? null,
    details: v.details ?? "",
  });
  clearErrors();
}

watch(() => props.value, applyFromProps, { deep: true, immediate: true });
watch(() => props.modelValue, (open) => { if (open) applyFromProps(); });

function clearLocal() {
  Object.assign(editor, blankRow());
  clearErrors();
  showSnack(t("همه فیلدها پاک شد"), "info", "mdi-check-circle");
}

async function submit() {
  clearErrors();

  const valid = await formRef.value?.validate?.();
  if (valid === false) {
    showSnack(t("لطفاً فیلدهای الزامی را تکمیل کنید"), "error", "mdi-alert-circle");
    return;
  }

  // Additional validation
  if (!editor.name || editor.name.trim().length < 2) {
    showSnack(t("نام حساب باید حداقل ۲ حرف باشد"), "error", "mdi-alert-circle");
    return;
  }

  if (!editor.account_type_id) {
    showSnack(t("انتخاب نوع حساب الزامی است"), "error", "mdi-alert-circle");
    return;
  }

  if (editor.price < 0) {
    showSnack(t("موجودی نمی‌تواند منفی باشد"), "error", "mdi-alert-circle");
    return;
  }

  const payload = {
    id: editor.id,
    name: editor.name.trim(),
    price: parseFloat(editor.price) || 0,
    account_type_id: editor.account_type_id,
    details: editor.details.trim(),
  };

  emit("save", payload);
  showSnack(
    editMode ? t("حساب با موفقیت ویرایش شد") : t("حساب جدید با موفقیت ثبت شد"),
    "success",
    "mdi-check-circle"
  );
}
</script>

<style scoped>
/* Gradient background for header */
.bg-gradient-info {
  background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%);
}

/* Custom section title line */
.section-title-line {
  width: 4px;
  height: 24px;
  border-radius: 2px;
}

/* Custom scrollbar */
.v-dialog::-webkit-scrollbar {
  width: 8px;
}

.v-dialog::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.v-dialog::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%);
  border-radius: 4px;
}

.v-dialog::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
}

/* Smooth hover effect for cards */
.v-card[class*="border"] {
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.12);
}

.v-card[class*="border"]:hover {
  border-color: var(--v-info-base);
  box-shadow: 0 4px 12px rgba(14, 165, 233, 0.1);
}

/* RTL specific adjustments */
[dir="rtl"] .v-input__prepend-inner {
  margin-left: 8px;
  margin-right: 0;
}

[dir="rtl"] .v-input__append-inner {
  margin-right: 8px;
  margin-left: 0;
}

/* Text alignment for RTL */
.text-right {
  text-align: right;
}

/* Height utilities */
.h-100 {
  height: 100%;
}

/* Info color variations */
.bg-info-lighten-5 {
  background-color: rgba(14, 165, 233, 0.05);
}

/* Amount display styling */
.amount-display {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  letter-spacing: 0.5px;
}

/* Focus effects */
.v-input--is-focused .v-field {
  border-color: var(--v-info-base) !important;
}

/* Button hover effects */
.v-btn:hover {
  transform: translateY(-1px);
  transition: transform 0.2s ease;
}

/* Avatar icon colors */
:deep(.v-avatar) {
  transition: transform 0.2s ease;
}

:deep(.v-avatar:hover) {
  transform: scale(1.05);
}
</style>