<template>
  <v-dialog v-model="model" max-width="800" dir="rtl">
    <v-card rounded="xl" class="overflow-hidden">
      <!-- Header with Gradient -->
      <div class="bg-gradient-success py-5 px-4">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-avatar color="white" size="44" class="ms-3">
              <v-icon icon="mdi-cash-plus" color="success" size="24"></v-icon>
            </v-avatar>
            <div>
              <h2 class="text-h5 font-weight-bold text-white mb-1">
                {{ editMode ? t('ویرایش عاید') : t('ثبت عاید جدید') }}
              </h2>
              <p class="text-white text-caption opacity-85 mb-0">
                {{ editMode ? t('اطلاعات عاید را ویرایش کنید') : t('عاید جدید را ثبت کنید') }}
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
          <!-- Main Information Section -->
          <div class="mb-6">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-success ms-3"></div>
              <h3 class="text-h6 font-weight-bold text-success">
                <v-icon icon="mdi-information" class="ms-2" size="20"></v-icon>
                {{ t('اطلاعات اصلی عاید') }}
              </h3>
            </div>
            
            <v-row>
              <!-- Account Selection -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100 elevation-1">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block text-right">
                    <v-icon icon="mdi-bank" size="16" class="ms-1"></v-icon>
                    {{ t('حساب') }} *
                  </label>
                  <v-autocomplete
                    v-model="editor.account_id"
                    :items="accountsOptions"
                    item-title="name"
                    item-value="id"
                    :placeholder="t('حساب را انتخاب کنید')"
                    variant="outlined"
                    :error="hasError('account_id')"
                    :error-messages="getErrorMessages('account_id')"
                    hide-details
                    :rules="[v => !!v || t('حساب الزامی است')]"
                    class="mt-1"
                    density="comfortable"
                    dir="rtl"
                    prepend-inner-icon="mdi-chevron-down"
                  >
                    <template #item="{ props, item: it }">
                      <v-list-item v-bind="props" dir="rtl">
                        <template #prepend>
                          <v-avatar color="success-lighten-4" size="36" class="ms-3">
                            <v-icon icon="mdi-bank" color="success"></v-icon>
                          </v-avatar>
                        </template>
                        <v-list-item-title class="text-body-2 font-weight-medium text-right">
                          {{ it.raw.name }}
                        </v-list-item-title>
                      </v-list-item>
                    </template>
                  </v-autocomplete>
                </v-card>
              </v-col>

              <!-- Date -->
              <v-col cols="12" md="6">
                <v-card variant="flat" class="border rounded-lg pa-4 h-100 elevation-1">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block text-right">
                    <v-icon icon="mdi-calendar-check" size="16" class="ms-1"></v-icon>
                    {{ t('تاریخ دریافت') }} *
                  </label>
                  <DateInput
                    v-model="editor.received_at"
                    :placeholder="t('تاریخ را انتخاب کنید')"
                    :error="hasError('received_at')"
                    :error-messages="getErrorMessages('received_at')"
                    :rules="[v => !!v || t('تاریخ دریافت الزامی است')]"
                    hide-details
                    variant="outlined"
                    class="mt-1"
                    density="comfortable"
                    dir="rtl"
                  />
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Amount Section -->
          <div class="mb-6">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-amber ms-3"></div>
              <h3 class="text-h6 font-weight-bold text-amber-darken-3">
                <v-icon icon="mdi-cash" class="ms-2" size="20"></v-icon>
                {{ t('مبلغ عاید') }}
              </h3>
            </div>
            
            <v-row>
              <!-- Amount Input -->
              <v-col cols="12">
                <v-card variant="flat" class="border rounded-lg pa-4 elevation-1">
                  <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block text-right">
                    <v-icon icon="mdi-currency-usd" size="16" class="ms-1"></v-icon>
                    {{ t('مبلغ') }} *
                  </label>
                  <div class="d-flex align-center gap-3">
                    <v-text-field
                      v-model="editor.amount"
                      type="number"
                      :placeholder="t('مبلغ را وارد کنید')"
                      variant="outlined"
                      :error="hasError('amount')"
                      :error-messages="getErrorMessages('amount')"
                      hide-details
                      :rules="[
                        v => !!v || t('مبلغ الزامی است'),
                        v => v > 0 || t('مبلغ باید بیشتر از صفر باشد')
                      ]"
                      class="flex-grow-1"
                      density="comfortable"
                      dir="ltr"
                    >
                      <template #append>
                        <span class="text-caption text-grey">افغانی</span>
                      </template>
                    </v-text-field>
                    
                    <!-- Amount Display Card -->
                    <v-card 
                      variant="flat" 
                      color="amber-lighten-5" 
                      class="pa-3 rounded-lg border d-flex align-center"
                      style="min-width: 140px"
                    >
                      <div class="text-center">
                        <div class="text-caption text-grey mb-1">{{ t('مبلغ') }}</div>
                        <div class="text-h6 font-weight-bold text-amber-darken-3">
                          {{ formatNumber(editor.amount) }}
                        </div>
                        <div class="text-caption text-grey">افغانی</div>
                      </div>
                    </v-card>
                  </div>
                  <div class="text-caption text-grey mt-3 text-right">
                    {{ t('مبلغ به صورت عددی وارد شود') }}
                  </div>
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Notes Section -->
          <div class="mb-2">
            <div class="d-flex align-center mb-4">
              <div class="section-title-line bg-blue-grey ms-3"></div>
              <h3 class="text-h6 font-weight-bold text-blue-grey">
                <v-icon icon="mdi-note-text" class="ms-2" size="20"></v-icon>
                {{ t('توضیحات و یادداشت') }}
              </h3>
            </div>
            
            <v-card variant="flat" class="border rounded-lg pa-4 elevation-1">
              <label class="text-subtitle-2 text-grey-darken-2 mb-2 d-block text-right">
                <v-icon icon="mdi-pencil" size="16" class="ms-1"></v-icon>
                {{ t('یادداشت') }}
              </label>
              <v-textarea
                v-model="editor.note"
                :placeholder="t('توضیحات مربوط به این عاید را وارد کنید...')"
                variant="outlined"
                :error="hasError('note')"
                :error-messages="getErrorMessages('note')"
                hide-details
                rows="3"
                auto-grow
                class="mt-1"
                dir="rtl"
                counter
                :maxlength="500"
              />
              <div class="text-caption text-grey mt-2 text-right">
                {{ t('توضیحاتی مانند منبع عاید، نحوه دریافت و سایر اطلاعات مرتبط') }}
              </div>
            </v-card>
          </div>

          <!-- Summary Card -->
          <v-card variant="flat" color="success-lighten-5" class="border rounded-lg pa-4 mt-6" dir="rtl">
            <div class="d-flex align-center justify-space-between">
              <div>
                <div class="text-subtitle-2 text-grey-darken-2 mb-1">{{ t('خلاصه اطلاعات') }}</div>
                <div class="text-caption text-grey">
                  {{ t('اطلاعات وارد شده را بررسی کنید') }}
                </div>
              </div>
              <v-icon icon="mdi-clipboard-check" color="success" size="24"></v-icon>
            </div>
            
            <v-divider class="my-3" />
            
            <v-row>
              <v-col cols="12" md="6">
                <div class="d-flex align-center mb-3">
                  <v-icon icon="mdi-bank" size="18" class="ms-2 text-grey"></v-icon>
                  <div>
                    <div class="text-caption text-grey">{{ t('حساب') }}</div>
                    <div class="text-body-2 font-weight-medium">
                      {{ getAccountName(editor.account_id) || t('انتخاب نشده') }}
                    </div>
                  </div>
                </div>
              </v-col>
              
              <v-col cols="12" md="6">
                <div class="d-flex align-center mb-3">
                  <v-icon icon="mdi-calendar" size="18" class="ms-2 text-grey"></v-icon>
                  <div>
                    <div class="text-caption text-grey">{{ t('تاریخ') }}</div>
                    <div class="text-body-2 font-weight-medium">
                      {{ editor.received_at || t('تعیین نشده') }}
                    </div>
                  </div>
                </div>
              </v-col>
              
              <v-col cols="12" md="6">
                <div class="d-flex align-center">
                  <v-icon icon="mdi-cash" size="18" class="ms-2 text-grey"></v-icon>
                  <div>
                    <div class="text-caption text-grey">{{ t('مبلغ') }}</div>
                    <div class="text-body-2 font-weight-medium text-success">
                      {{ formatNumber(editor.amount) }} افغانی
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
          {{ t('پاک کردن') }}
        </v-btn>

        <v-btn
          color="success"
          variant="flat"
          @click="submit"
          :loading="busy"
          prepend-icon="mdi-content-save-check"
          size="large"
          class="px-6"
          elevation="2"
        >
          {{ editMode ? t('ذخیره تغییرات') : t('ثبت عاید') }}
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
import DateInput from "@/components/DateInput.vue";

const { t } = useI18n();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editMode: { type: Boolean, default: false },
  value: { type: Object, default: () => ({}) },
  busy: { type: Boolean, default: false },
  accountsOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:modelValue", "save"]);

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit("update:modelValue", v),
});

const formRef = ref(null);
const itemErrors = reactive({});
const snack = reactive({ open: false, text: "", color: "primary", icon: "mdi-information" });

const blankRow = () => ({
  id: null,
  account_id: null,
  amount: 0,
  received_at: null,
  note: "",
});

const editor = reactive(blankRow());

// Helper functions
function formatNumber(num) {
  return new Intl.NumberFormat('fa-AF').format(num || 0);
}

function getAccountName(accountId) {
  const account = props.accountsOptions.find(acc => acc.id === accountId);
  return account ? account.name : null;
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
    amount: v.amount ?? 0,
    received_at: v.received_at ?? null,
    note: v.note ?? "",
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
  if (!editor.account_id) {
    showSnack(t("انتخاب حساب الزامی است"), "error", "mdi-alert-circle");
    return;
  }

  if (!editor.received_at) {
    showSnack(t("تاریخ دریافت الزامی است"), "error", "mdi-alert-circle");
    return;
  }

  if (!editor.amount || editor.amount <= 0) {
    showSnack(t("مبلغ باید بیشتر از صفر باشد"), "error", "mdi-alert-circle");
    return;
  }

  const payload = {
    id: editor.id,
    account_id: editor.account_id,
    amount: editor.amount,
    received_at: editor.received_at,
    note: editor.note,
  };

  emit("save", payload);
  showSnack(
    editMode ? t("عاید با موفقیت ویرایش شد") : t("عاید جدید با موفقیت ثبت شد"),
    "success",
    "mdi-check-circle"
  );
}
</script>

<style scoped>
/* Gradient background for header */
.bg-gradient-success {
  background: linear-gradient(135deg, #10B981 0%, #059669 100%);
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
  background: linear-gradient(135deg, #10B981 0%, #059669 100%);
  border-radius: 4px;
}

.v-dialog::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

/* Smooth hover effect for cards */
.v-card[class*="border"] {
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.12);
}

.v-card[class*="border"]:hover {
  border-color: var(--v-success-base);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
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

/* Success color variations */
.bg-success-lighten-4 {
  background-color: rgba(16, 185, 129, 0.08);
}

/* Amount display styling */
.amount-display {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  letter-spacing: 0.5px;
}

/* Focus effects */
.v-input--is-focused .v-field {
  border-color: var(--v-success-base) !important;
}

/* Button hover effects */
.v-btn:hover {
  transform: translateY(-1px);
  transition: transform 0.2s ease;
}
</style>