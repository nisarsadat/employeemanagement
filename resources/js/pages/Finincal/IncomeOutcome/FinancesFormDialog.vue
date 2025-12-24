<template>
  <v-dialog v-model="model" max-width="900" scrollable>
    <v-card rounded="xl" class="overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-primary py-6 px-4">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-avatar color="white" size="48" class="me-3">
              <v-icon icon="mdi-cash" color="primary" size="28"></v-icon>
            </v-avatar>
            <div>
              <h2 class="text-h5 font-weight-bold text-white mb-1">
                {{ editMode ? t('ویرایش مصرف') : t('ثبت مصرف جدید') }}
              </h2>
              <p class="text-white text-caption opacity-75 mb-0">
                {{ editMode ? t('اطلاعات مصرف را ویرایش کنید') : t('اطلاعات مصرف جدید را وارد کنید') }}
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

      <v-card-text class="pa-6">
        <v-form ref="formRef" @submit.prevent="submit" lazy-validation>
          <!-- Main Information Section -->
          <div class="mb-8">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-primary me-3"></div>
              <h3 class="text-h6 font-weight-bold text-primary">
                <v-icon icon="mdi-information" class="me-2" size="20"></v-icon>
                {{ t('اطلاعات اصلی') }}
              </h3>
            </div>
            
            <v-row>
              <!-- Account -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-bank" size="16" class="me-1"></v-icon>
                    {{ t('حساب') }} *
                  </label>
                  <v-autocomplete
                    v-model="editor.account_id"
                    :items="accountsOptions"
                    item-title="name"
                    item-value="id"
                    :placeholder="t('حساب را انتخاب کنید')"
                    variant="underlined"
                    :error="hasError('account_id')"
                    :error-messages="getErrorMessages('account_id')"
                    hide-details
                    :rules="[v => !!v || t('حساب الزامی است')]"
                    class="mt-1"
                  />
                </v-card>
              </v-col>

              <!-- Date -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-calendar" size="16" class="me-1"></v-icon>
                    {{ t('تاریخ') }} *
                  </label>
                  <DateInput
                    v-model="editor.date"
                    :placeholder="t('تاریخ را انتخاب کنید')"
                    :error="hasError('date')"
                    :error-messages="getErrorMessages('date')"
                    :rules="[v => !!v || t('تاریخ الزامی است')]"
                    hide-details
                    variant="underlined"
                    class="mt-1"
                  />
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Employee Information Section -->
          <div class="mb-8">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-blue me-3"></div>
              <h3 class="text-h6 font-weight-bold text-blue">
                <v-icon icon="mdi-account-tie" class="me-2" size="20"></v-icon>
                {{ t('اطلاعات کارمند') }}
              </h3>
            </div>
            
            <v-row>
              <!-- Employee Selection -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-account-search" size="16" class="me-1"></v-icon>
                    {{ t('کارمند') }}
                  </label>
                  <v-autocomplete
                    v-model="editor.office_employee_id"
                    :items="employeesOptions"
                    item-title="name"
                    item-value="id"
                    :placeholder="t('کارمند را انتخاب کنید')"
                    variant="underlined"
                    :error="hasError('office_employee_id')"
                    :error-messages="getErrorMessages('office_employee_id')"
                    hide-details
                    @update:model-value="onEmployeeSelected"
                    class="mt-1"
                  >
                    <template #item="{ props, item: it }">
                      <v-list-item v-bind="props">
                        <template #prepend>
                          <v-avatar color="blue" size="32">
                            <span class="text-white text-caption">{{ it.raw.name?.charAt(0) || '?' }}</span>
                          </v-avatar>
                        </template>
                        <v-list-item-title class="text-body-2 font-weight-medium">{{ it.raw.name }}</v-list-item-title>
                        <v-list-item-subtitle class="text-caption">
                          {{ t('کد') }}: {{ it.raw.code_number }}
                        </v-list-item-subtitle>
                      </v-list-item>
                    </template>
                  </v-autocomplete>
                </v-card>
              </v-col>

              <!-- Employee Phone (Auto-filled) -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-phone" size="16" class="me-1"></v-icon>
                    {{ t('شماره تماس کارمند') }}
                  </label>
                  <v-text-field
                    v-model="editor.employee_phone"
                    :placeholder="t('شماره تماس کارمند')"
                    variant="underlined"
                    :error="hasError('employee_phone')"
                    :error-messages="getErrorMessages('employee_phone')"
                    hide-details
                    readonly
                    class="mt-1"
                  >
                    <template #append>
                      <v-icon 
                        v-if="editor.employee_phone"
                        icon="mdi-content-copy"
                        size="small"
                        @click="copyToClipboard(editor.employee_phone)"
                        class="cursor-pointer"
                      ></v-icon>
                    </template>
                  </v-text-field>
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Resource Information Section -->
          <div class="mb-8">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-green me-3"></div>
              <h3 class="text-h6 font-weight-bold text-green">
                <v-icon icon="mdi-source" class="me-2" size="20"></v-icon>
                {{ t('اطلاعات منبع') }}
              </h3>
            </div>
            
            <v-row>
              <!-- Resource Selection -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-source-check" size="16" class="me-1"></v-icon>
                    {{ t('منبع') }}
                  </label>
                  <v-autocomplete
                    v-model="editor.resource_id"
                    :items="resourcesOptions"
                    item-title="name"
                    item-value="id"
                    :placeholder="t('منبع را انتخاب کنید')"
                    variant="underlined"
                    :error="hasError('resource_id')"
                    :error-messages="getErrorMessages('resource_id')"
                    hide-details
                    @update:model-value="onResourceSelected"
                    class="mt-1"
                  >
                    <template #item="{ props, item: it }">
                      <v-list-item v-bind="props">
                        <template #prepend>
                          <v-avatar color="green" size="32">
                            <v-icon icon="mdi-source" color="white" size="18"></v-icon>
                          </v-avatar>
                        </template>
                        <v-list-item-title class="text-body-2 font-weight-medium">{{ it.raw.name }}</v-list-item-title>
                        <v-list-item-subtitle class="text-caption">
                          {{ t('کد') }}: {{ it.raw.code_number }}
                        </v-list-item-subtitle>
                      </v-list-item>
                    </template>
                  </v-autocomplete>
                </v-card>
              </v-col>

              <!-- Resource Phone (Auto-filled) -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-phone" size="16" class="me-1"></v-icon>
                    {{ t('شماره تماس منبع') }}
                  </label>
                  <v-text-field
                    v-model="resourcePhone"
                    :placeholder="t('شماره تماس منبع')"
                    variant="underlined"
                    hide-details
                    readonly
                    class="mt-1"
                  >
                    <template #append>
                      <v-icon 
                        v-if="resourcePhone"
                        icon="mdi-content-copy"
                        size="small"
                        @click="copyToClipboard(resourcePhone)"
                        class="cursor-pointer"
                      ></v-icon>
                    </template>
                  </v-text-field>
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Amount Information Section -->
          <div class="mb-8">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-amber me-3"></div>
              <h3 class="text-h6 font-weight-bold text-amber-darken-3">
                <v-icon icon="mdi-cash-multiple" class="me-2" size="20"></v-icon>
                {{ t('اطلاعات مبلغ') }}
              </h3>
            </div>
            
            <v-row>
              <!-- Amount in Numbers -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-numeric" size="16" class="me-1"></v-icon>
                    {{ t('مبلغ (عدد)') }} *
                  </label>
                  <v-text-field
                    v-model="editor.money_amount_number"
                    type="number"
                    :placeholder="t('مبلغ را وارد کنید')"
                    variant="underlined"
                    :error="hasError('money_amount_number')"
                    :error-messages="getErrorMessages('money_amount_number')"
                    hide-details
                    :rules="[v => v > 0 || t('مبلغ باید بیشتر از صفر باشد')]"
                    class="mt-1"
                  >
                    <template #append>
                      <span class="text-caption text-grey">افغانی</span>
                    </template>
                  </v-text-field>
                </v-card>
              </v-col>

              <!-- Amount in Words -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-alphabetical" size="16" class="me-1"></v-icon>
                    {{ t('مبلغ (حروف)') }}
                  </label>
                  <v-text-field
                    v-model="editor.money_amount_words"
                    :placeholder="t('مبلغ به حروف')"
                    variant="underlined"
                    :error="hasError('money_amount_words')"
                    :error-messages="getErrorMessages('money_amount_words')"
                    hide-details
                    class="mt-1"
                  />
                </v-card>
              </v-col>

              <!-- Expense Purpose -->
              <v-col cols="12">
                <v-card variant="flat" class="border rounded-lg pa-4">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-text-box-check" size="16" class="me-1"></v-icon>
                    {{ t('جهت مصرف') }}
                  </label>
                  <v-textarea
                    v-model="editor.expense_result"
                    :placeholder="t('جهت مصرف را توضیح دهید')"
                    variant="underlined"
                    :error="hasError('expense_result')"
                    :error-messages="getErrorMessages('expense_result')"
                    hide-details
                    rows="2"
                    auto-grow
                    class="mt-1"
                  />
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Transaction Parties Section -->
          <div class="mb-8">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-purple me-3"></div>
              <h3 class="text-h6 font-weight-bold text-purple">
                <v-icon icon="mdi-account-switch" class="me-2" size="20"></v-icon>
                {{ t('طرفین معامله') }}
              </h3>
            </div>
            
            <v-row>
              <!-- Payer -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-account-arrow-right" size="16" class="me-1"></v-icon>
                    {{ t('پرداخت کننده') }}
                  </label>
                  <v-text-field
                    v-model="editor.payer"
                    :placeholder="t('نام پرداخت کننده')"
                    variant="underlined"
                    :error="hasError('payer')"
                    :error-messages="getErrorMessages('payer')"
                    hide-details
                    class="mt-1"
                  />
                </v-card>
              </v-col>

              <!-- Receiver -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                    <v-icon icon="mdi-account-arrow-left" size="16" class="me-1"></v-icon>
                    {{ t('دریافت کننده') }}
                  </label>
                  <v-text-field
                    v-model="editor.receiver"
                    :placeholder="t('نام دریافت کننده')"
                    variant="underlined"
                    :error="hasError('receiver')"
                    :error-messages="getErrorMessages('receiver')"
                    hide-details
                    class="mt-1"
                  />
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Notes Section -->
          <div class="mb-2">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-grey-darken-2 me-3"></div>
              <h3 class="text-h6 font-weight-bold text-grey-darken-2">
                <v-icon icon="mdi-note-text" class="me-2" size="20"></v-icon>
                {{ t('یادداشت‌ها') }}
              </h3>
            </div>
            
            <v-card variant="flat" class="border rounded-lg pa-4">
              <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block">
                <v-icon icon="mdi-pencil" size="16" class="me-1"></v-icon>
                {{ t('توضیحات اضافی') }}
              </label>
              <v-textarea
                v-model="editor.notes"
                :placeholder="t('هرگونه توضیح اضافی درباره این مصرف')"
                variant="underlined"
                hide-details
                rows="3"
                auto-grow
                class="mt-1"
              />
            </v-card>
          </div>
        </v-form>
      </v-card-text>

      <!-- Footer Actions -->
      <v-divider />

      <v-card-actions class="pa-4 d-flex justify-end gap-3 bg-grey-lighten-5">
        <v-btn
          variant="outlined"
          color="grey-darken-2"
          @click="clearLocal"
          :disabled="busy"
          prepend-icon="mdi-broom"
          size="large"
          class="px-5"
        >
          {{ t('پاک کردن فرم') }}
        </v-btn>

        <v-btn
          color="primary"
          variant="flat"
          @click="submit"
          :loading="busy"
          prepend-icon="mdi-content-save-check"
          size="large"
          class="px-6"
          elevation="2"
        >
          {{ editMode ? t('ذخیره تغییرات') : t('ثبت مصرف') }}
        </v-btn>
      </v-card-actions>
    </v-card>

    <!-- Notification Toast -->
    <v-snackbar v-model="snack.open" :color="snack.color" timeout="3000" location="top">
      <div class="d-flex align-center">
        <v-icon :icon="snack.icon" class="me-2"></v-icon>
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
import DateInput from "@/components/DateInput.vue";

const { t } = useI18n();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editMode: { type: Boolean, default: false },
  value: { type: Object, default: () => ({}) },
  busy: { type: Boolean, default: false },

  accountsOptions: { type: Array, default: () => [] },
  employeesOptions: { type: Array, default: () => [] },
  resourcesOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:modelValue", "save"]);

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit("update:modelValue", v),
});

const formRef = ref(null);
const itemErrors = reactive({});
const snack = reactive({ open: false, text: "", color: "primary", icon: "mdi-information" });
const resourcePhone = ref("");

const blankRow = () => ({
  id: null,
  account_id: null,
  office_employee_id: null,
  resource_id: null,
  money_amount_number: 0,
  money_amount_words: "",
  date: null,
  employee_phone: "",
  expense_result: "",
  payer: "",
  receiver: "",
  notes: "",
});

const editor = reactive(blankRow());

// Watch for employee selection changes
function onEmployeeSelected(employeeId) {
  if (employeeId) {
    const selectedEmployee = props.employeesOptions.find(emp => emp.id === employeeId);
    if (selectedEmployee) {
      editor.employee_phone = selectedEmployee.phone_number || "";
    }
  } else {
    editor.employee_phone = "";
  }
}

// Watch for resource selection changes
function onResourceSelected(resourceId) {
  if (resourceId) {
    const selectedResource = props.resourcesOptions.find(res => res.id === resourceId);
    if (selectedResource) {
      resourcePhone.value = selectedResource.phone_number || "";
    }
  } else {
    resourcePhone.value = "";
  }
}

function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(() => {
    showSnack(t("متن در کلیپ‌بورد کپی شد"), "success", "mdi-check");
  });
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
    account_id: v.account_id ?? null,
    office_employee_id: v.office_employee_id ?? null,
    resource_id: v.resource_id ?? null,
    money_amount_number: v.money_amount_number ?? 0,
    money_amount_words: v.money_amount_words ?? "",
    date: v.date ?? null,
    employee_phone: v.employee_phone ?? "",
    expense_result: v.expense_result ?? "",
    payer: v.payer ?? "",
    receiver: v.receiver ?? "",
    notes: v.notes ?? "",
  });
  
  // Also update resource phone if resource is selected
  if (v.resource_id) {
    const selectedResource = props.resourcesOptions.find(res => res.id === v.resource_id);
    if (selectedResource) {
      resourcePhone.value = selectedResource.phone_number || "";
    }
  }
  
  clearErrors();
}

watch(() => props.value, applyFromProps, { deep: true, immediate: true });
watch(() => props.modelValue, (open) => { if (open) applyFromProps(); });

function clearLocal() {
  Object.assign(editor, blankRow());
  resourcePhone.value = "";
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
  if (!editor.account_id) {
    showSnack(t("انتخاب حساب الزامی است"), "error", "mdi-alert-circle");
    return;
  }

  if (!editor.date) {
    showSnack(t("تاریخ الزامی است"), "error", "mdi-alert-circle");
    return;
  }

  if (!editor.money_amount_number || editor.money_amount_number <= 0) {
    showSnack(t("مبلغ باید بیشتر از صفر باشد"), "error", "mdi-alert-circle");
    return;
  }

  const payload = {
    id: editor.id,
    account_id: editor.account_id,
    office_employee_id: editor.office_employee_id,
    resource_id: editor.resource_id,
    money_amount_number: editor.money_amount_number,
    money_amount_words: editor.money_amount_words,
    date: editor.date,
    employee_phone: editor.employee_phone,
    expense_result: editor.expense_result,
    payer: editor.payer,
    receiver: editor.receiver,
    notes: editor.notes,
  };

  emit("save", payload);
  showSnack(
    editMode ? t("اطلاعات با موفقیت ویرایش شد") : t("مصرف جدید با موفقیت ثبت شد"),
    "success",
    "mdi-check-circle"
  );
}
</script>

<style scoped>
/* Gradient background for header */
.bg-gradient-primary {
  background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
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
  background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
  border-radius: 4px;
}

.v-dialog::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%);
}

/* Smooth hover effect for cards */
.v-card[class*="border"] {
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.12);
}

.v-card[class*="border"]:hover {
  border-color: var(--v-primary-base);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

/* Underlined input styling */
:deep(.v-input--underlined) .v-input__control:before {
  border-color: rgba(0, 0, 0, 0.12);
}

:deep(.v-input--underlined:hover) .v-input__control:before {
  border-color: var(--v-primary-base);
}

:deep(.v-input--underlined.v-input--is-focused) .v-input__control:before {
  border-color: var(--v-primary-base);
  border-width: 2px;
}

/* Copy icon hover effect */
.cursor-pointer {
  cursor: pointer;
  transition: color 0.2s ease;
}

.cursor-pointer:hover {
  color: var(--v-primary-base);
}

/* Height utilities */
.h-100 {
  height: 100%;
}
</style>