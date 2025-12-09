<template>
  <!-- DIALOG 1 – FIRST CONFIRM -->
  <v-dialog
    :model-value="flowActive && currentStep === 1"
    max-width="400"
    persistent
    @update:model-value="onDialogModelUpdate"
  >
    <v-card class="rounded-lg overflow-hidden" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- Header -->
      <v-card-title class="bg-gradient-to-r bg-primary to-pink-500 px-4 py-3">
        <v-row no-gutters align="center" class="ma-0">
          <v-col cols="auto" class="pr-2">
            <v-icon color="white" size="20" icon="mdi-delete-outline" />
          </v-col>
          <v-col>
            <h3 class="text-base font-weight-bold text-white mb-0">
              {{ t('تأیید حذف - مرحله ۱ از ۳') }}
            </h3>
          </v-col>
        </v-row>
      </v-card-title>

      <!-- Content -->
      <v-card-text class="px-4 py-4">
        <p class="text-grey-darken-3 text-sm mb-3">
          {{ t('آیا مطمئن هستید که می‌خواهید این مورد را حذف کنید؟') }}
        </p>

        <v-alert
          type="warning"
          variant="tonal"
          border="start"
          color="amber-darken-3"
          density="compact"
          class="mb-3"
          :icon="false"
        >
          <div class="flex items-start">
            <v-icon
              color="amber-darken-3"
              icon="mdi-alert-outline"
              size="18"
              class="mt-0.5 mr-2"
            />
            <div>
              <div class="text-amber-darken-3 text-sm font-bold mb-0.5">
                {{ t('گام اول تأیید حذف') }}
              </div>
              <div class="text-amber-darken-3 text-xs">
                {{ t('اگر ادامه دهید، دو مرحله‌ی بعدی برای تأیید نهایی نمایش داده می‌شوند.') }}
              </div>
            </div>
          </div>
        </v-alert>

        <!-- Target info -->
        <v-card v-if="target" variant="outlined" class="bg-grey-lighten-5 mb-1">
          <v-card-text class="pa-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <div class="text-xs text-grey-darken-1 font-medium mb-0.5">
                  {{ t('آی دی') }}
                </div>
                <div class="text-sm font-bold text-grey-darken-4">
                  #{{ displayId }}
                </div>
              </div>
              <div>
                <div class="text-xs text-grey-darken-1 font-medium mb-0.5">
                  {{ t('نام') }}
                </div>
                <div class="text-sm font-semibold text-grey-darken-4 truncate">
                  {{ displayName || '-' }}
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-card-text>

      <!-- Actions -->
      <v-card-actions class="px-4 pb-4 pt-1">
        <div class="flex w-full gap-2">
          <v-btn
            variant="outlined"
            class="flex-1 text-grey-darken-2 border-grey-lighten-1 text-sm bg-info"
            @click="onCancelAll"
          >
            {{ t('لغو حذف') }}
          </v-btn>
          <v-btn
            color="error"
            variant="flat"
            class="flex-1 bg-gradient-to-r from-rose-darken-1 to-rose-darken-2 text-white text-sm font-bold"
            @click="goToStep(2)"
          >
            {{ t('ادامه حذف') }}
          </v-btn>
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- DIALOG 2 – SECOND CONFIRM -->
  <v-dialog
    :model-value="flowActive && currentStep === 2"
    max-width="400"
    persistent
    @update:model-value="onDialogModelUpdate"
  >
    <v-card class="rounded-lg overflow-hidden" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- Header -->
      <v-card-title class="bg-gradient-to-r from-amber-600 to-red-500 px-4 py-3">
        <v-row no-gutters align="center" class="ma-0">
          <v-col cols="auto" class="pr-2">
            <v-icon color="white" size="20" icon="mdi-alert" />
          </v-col>
          <v-col>
            <h3 class="text-base font-weight-bold text-white mb-0">
              {{ t('تأیید حذف - مرحله ۲ از ۳') }}
            </h3>
          </v-col>
        </v-row>
      </v-card-title>

      <!-- Content -->
      <v-card-text class="px-4 py-4">
        <p class="text-grey-darken-3 text-sm mb-3">
          {{ t('این اقدام ممکن است اطلاعات مهم را حذف کند. آیا همچنان می‌خواهید ادامه دهید؟') }}
        </p>

        <v-alert
          type="error"
          variant="tonal"
          border="start"
          color="deep-orange-darken-2"
          density="compact"
          class="mb-3"
          :icon="false"
        >
          <div class="flex items-start">
            <v-icon
              color="deep-orange-darken-2"
              icon="mdi-alert-outline"
              size="18"
              class="mt-0.5 mr-2"
            />
            <div>
              <div class="text-deep-orange-darken-2 text-sm font-bold mb-0.5">
                {{ t('هشدار جدی') }}
              </div>
              <div class="text-deep-orange-darken-2 text-xs">
                {{ t('بعد از ادامه، فقط یک مرحله تا حذف نهایی باقی می‌ماند.') }}
              </div>
            </div>
          </div>
        </v-alert>

        <!-- Target info -->
        <v-card v-if="target" variant="outlined" class="bg-grey-lighten-5 mb-1">
          <v-card-text class="pa-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <div class="text-xs text-grey-darken-1 font-medium mb-0.5">
                  {{ t('آی دی') }}
                </div>
                <div class="text-sm font-bold text-grey-darken-4">
                  #{{ displayId }}
                </div>
              </div>
              <div>
                <div class="text-xs text-grey-darken-1 font-medium mb-0.5">
                  {{ t('نام') }}
                </div>
                <div class="text-sm font-semibold text-grey-darken-4 truncate">
                  {{ displayName || '-' }}
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-card-text>

      <!-- Actions -->
      <v-card-actions class="px-4 pb-4 pt-1">
        <div class="flex w-full gap-2">
          <v-btn
            variant="outlined"
            class="flex-1 text-grey-darken-2 border-grey-lighten-1 text-sm bg-info"
            @click="onCancelAll"
          >
            {{ t('لغو حذف') }}
          </v-btn>
          <v-btn
            color="error"
            variant="flat"
            class="flex-1 bg-gradient-to-r from-red-darken-2 to-deep-orange-darken-2 text-white text-sm font-bold"
            @click="goToStep(3)"
          >
            {{ t('ادامه حذف') }}
          </v-btn>
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- DIALOG 3 – FINAL CONFIRM -->
  <v-dialog
    :model-value="flowActive && currentStep === 3"
    max-width="400"
    persistent
    @update:model-value="onDialogModelUpdate"
  >
    <v-card class="rounded-lg overflow-hidden" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- Header -->
      <v-card-title class="bg-gradient-to-r from-red-700 to-red-900 px-4 py-3">
        <v-row no-gutters align="center" class="ma-0">
          <v-col cols="auto" class="pr-2">
            <v-icon color="white" size="20" icon="mdi-trash-can-outline" />
          </v-col>
          <v-col>
            <h3 class="text-base font-weight-bold text-white mb-0">
              {{ t('تأیید نهایی حذف - مرحله ۳ از ۳') }}
            </h3>
          </v-col>
        </v-row>
      </v-card-title>

      <!-- Content -->
      <v-card-text class="px-4 py-4">
        <v-alert
          type="error"
          variant="tonal"
          border="start"
          color="red-darken-3"
          density="compact"
          class="mb-3"
          :icon="false"
        >
          <div class="flex items-start">
            <v-icon
              color="red-darken-3"
              icon="mdi-alert-octagon-outline"
              size="18"
              class="mt-0.5 mr-2"
            />
            <div>
              <div class="text-red-darken-3 text-sm font-bold mb-0.5">
                {{ t('این عمل غیرقابل بازگشت است') }}
              </div>
              <div class="text-red-darken-3 text-xs">
                {{ t('با تأیید این مرحله، مورد انتخاب‌شده برای همیشه حذف می‌شود.') }}
              </div>
            </div>
          </div>
        </v-alert>

        <p class="text-grey-darken-3 text-sm mb-2">
          {{ t('برای تأیید نهایی حذف، روی دکمه‌ی حذف کلیک کنید.') }}
        </p>

        <!-- Target info -->
        <v-card v-if="target" variant="outlined" class="bg-grey-lighten-5 mb-1">
          <v-card-text class="pa-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <div class="text-xs text-grey-darken-1 font-medium mb-0.5">
                  {{ t('آی دی') }}
                </div>
                <div class="text-sm font-bold text-grey-darken-4">
                  #{{ displayId }}
                </div>
              </div>
              <div>
                <div class="text-xs text-grey-darken-1 font-medium mb-0.5">
                  {{ t('نام') }}
                </div>
                <div class="text-sm font-semibold text-grey-darken-4 truncate">
                  {{ displayName || '-' }}
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-card-text>

      <!-- Actions -->
      <v-card-actions class="px-4 pb-4 pt-1">
        <div class="flex w-full gap-2">
          <v-btn
            variant="outlined"
            class="flex-1 text-grey-darken-2 border-grey-lighten-1 text-sm bg-info"
            @click="onCancelAll"
          >
            {{ t('لغو حذف') }}
          </v-btn>
          <v-btn
            color="error"
            variant="flat"
            class="flex-1 bg-gradient-to-r from-red-darken-3 to-red-darken-4 text-white text-sm font-bold"
            @click="onFinalConfirm"
          >
            {{ t('حذف نهایی') }}
          </v-btn>
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  target: {
    type: Object,
    default: null,
  },
  isRtl: {
    type: Boolean,
    default: false,
  },
  idKey: {
    type: String,
    default: 'id',
  },
  nameKey: {
    type: String,
    default: 'name',
  },
});

const emit = defineEmits(['update:modelValue', 'confirm']);
const { t } = useI18n();

// internal flow state
const flowActive = ref(false);
const currentStep = ref(1);

const displayId = computed(() => props.target?.[props.idKey] ?? '');
const displayName = computed(() => props.target?.[props.nameKey] ?? '');

// when parent opens/closes
watch(
  () => props.modelValue,
  (val) => {
    if (val) {
      flowActive.value = true;
      currentStep.value = 1;
    } else {
      flowActive.value = false;
      currentStep.value = 1;
    }
  }
);

// handle dialog's internal close events (e.g. ESC if ever allowed)
function onDialogModelUpdate(val) {
  // we keep them `persistent`, but just in case:
  if (!val) {
    onCancelAll();
  }
}

function goToStep(step) {
  currentStep.value = step;
}

function onCancelAll() {
  flowActive.value = false;
  currentStep.value = 1;
  emit('update:modelValue', false);
}

function onFinalConfirm() {
  emit('confirm', props.target);
  onCancelAll();
}
</script>

<style scoped>
/* Optional: dialog-specific tweaks here */
</style>
