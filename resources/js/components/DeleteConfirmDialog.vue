<template>
  <v-dialog v-model="visible" max-width="400" persistent>
    <v-card class="rounded-lg overflow-hidden" :dir="isRtl ? 'rtl' : 'ltr'">
      <!-- Header -->
      <v-card-title class="bg-gradient-to-r bg-primary to-pink-500 px-4 py-3">
        <v-row no-gutters align="center" class="ma-0">
          <v-col cols="auto" class="pr-2">
            <v-icon color="white" size="20" icon="mdi-delete-outline" />
          </v-col>
          <v-col>
            <h3 class="text-base font-weight-bold text-white mb-0">
              {{ t('تأیید حذف در سه مرحله') }}
            </h3>
          </v-col>
        </v-row>
      </v-card-title>

      <!-- Content -->
      <v-card-text class="px-4 py-4">
        <!-- Progress indicator -->
        <div class="mb-4">
          <v-row no-gutters justify="space-between" class="mb-1">
            <v-col cols="auto">
              <span class="text-xs font-medium text-grey-darken-1">
                {{ t('مرحله') }} {{ step }}/3
              </span>
            </v-col>
            <v-col cols="auto">
              <span class="text-xs font-bold text-rose-darken-2">
                {{ Math.round((step / 3) * 100) }}%
              </span>
            </v-col>
          </v-row>

          <v-progress-linear
            :model-value="(step / 4) * 134"
            color="red"
            height="6"
            rounded
          />
        </div>

        <!-- Warning message -->
        <v-alert
          type="warning"
          variant="tonal"
          border="start"
          color="rose-darken-2"
          density="compact"
          class="mb-4"
          :icon="false"
        >
          <div class="flex items-start">
            <v-icon
              color="rose-darken-2"
              icon="mdi-alert-outline"
              size="18"
              class="mt-0.5 mr-2"
            />
            <div>
              <div class="text-rose-darken-2 text-sm font-bold mb-0.5">
                {{ t('این عمل غیرقابل بازگشت است') }}
              </div>
              <div class="text-rose-darken-1 text-xs">
                {{ t('برای حذف نهایی، سه مرحله تأیید لازم است.') }}
              </div>
            </div>
          </div>
        </v-alert>

        <!-- Confirmation text -->
        <p class="text-grey-darken-3 text-sm mb-3">
          {{ t('آیا مطمئن هستید که می‌خواهید این مورد را حذف کنید؟') }}
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
            @click="onCancel"
          >
            {{ t('لغو حذف') }}
          </v-btn>
          <v-btn
            color="error"
            variant="flat"
            class="flex-1 bg-gradient-to-r from-rose-darken-1 to-rose-darken-2 text-white text-sm font-bold"
            @click="onPrimaryClick"
          >
            {{ step < 3 ? t('ادامه حذف') : t('حذف') }}
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

const step = ref(0);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});

const displayId = computed(() => props.target?.[props.idKey] ?? '');
const displayName = computed(() => props.target?.[props.nameKey] ?? '');

watch(
  () => props.modelValue,
  (val) => {
    if (val) {
      step.value = 0;
    }
  }
);

function onCancel() {
  visible.value = false;
  step.value = 0;
}

function onPrimaryClick() {
  if (step.value < 3) {
    step.value += 1;
    return;
  }
  // Step 3 -> actually confirm, but keep dialog open;
  // parent will close it (same logic as your old version)
  emit('confirm', props.target);
}
</script>

<style scoped>
/* You can keep it empty or add dialog-specific tweaks here if needed. */
</style>
