<template>
  <!-- STEP 1: Bottom-Center (slightly raised) -->
  <v-snackbar
    v-model="snack1"
    location="bottom center"
    :timeout="-1"
    rounded="lg"
    class="confirm-snack confirm-snack--step1"
    @update:model-value="(v) => onSnackClosed(v, 1)"
  >
    <div class="d-flex align-start">
      <v-icon icon="mdi-delete-outline" class="mr-3 mt-0.5" color="amber-darken-3" />
      <div class="flex-grow-1">
        <div class="text-sm font-weight-bold">
          {{ t('تأیید حذف') }}
        </div>
        <div class="text-xs opacity-90 mt-1">
          {{ t('آیا مطمئن هستید که می‌خواهید این مورد را حذف کنید؟') }}
        </div>

        <div v-if="target" class="mt-2 text-xs">
          <span class="font-weight-bold">#{{ displayId }}</span>
          <span class="mx-2">•</span>
          <span class="text-truncate d-inline-block" style="max-width: 220px">
            {{ displayName || '-' }}
          </span>
        </div>
      </div>
    </div>

    <template #actions>
      <v-btn variant="text" class="text-grey-darken-2" @click="cancelAll">
        {{ t('لغو') }}
      </v-btn>
      <v-btn color="amber-darken-3" variant="flat" class="font-weight-bold" @click="next(2)">
        {{ t('ادامه') }}
      </v-btn>
    </template>
  </v-snackbar>

  <!-- STEP 2: Top-Right (slightly inset) -->
  <v-snackbar
    v-model="snack2"
    location="top end"
    :timeout="-1"
    rounded="lg"
    class="confirm-snack confirm-snack--step2"
    @update:model-value="(v) => onSnackClosed(v, 2)"
  >
    <div class="d-flex align-start">
      <v-icon icon="mdi-alert" class="mr-3 mt-0.5" color="deep-orange-darken-2" />
      <div class="flex-grow-1">
        <div class="text-sm font-weight-bold">
          {{ t('هشدار') }}
        </div>
        <div class="text-xs opacity-90 mt-1">
          {{ t('این اقدام ممکن است اطلاعات مهم را حذف کند. آیا همچنان ادامه می‌دهید؟') }}
        </div>

        <div v-if="target" class="mt-2 text-xs">
          <span class="font-weight-bold">#{{ displayId }}</span>
          <span class="mx-2">•</span>
          <span class="text-truncate d-inline-block" style="max-width: 220px">
            {{ displayName || '-' }}
          </span>
        </div>
      </div>
    </div>

    <template #actions>
      <v-btn variant="text" class="text-grey-darken-2" @click="cancelAll">
        {{ t('لغو') }}
      </v-btn>
      <v-btn color="deep-orange-darken-2" variant="flat" class="font-weight-bold" @click="next(3)">
        {{ t('ادامه') }}
      </v-btn>
    </template>
  </v-snackbar>

  <!-- STEP 3: Center (Final) -->
  <v-snackbar
    v-model="snack3"
    location="center"
    :timeout="-1"
    rounded="lg"
    class="confirm-snack confirm-snack--step3"
    @update:model-value="(v) => onSnackClosed(v, 3)"
  >
    <div class="d-flex align-start">
      <v-icon icon="mdi-alert-octagon-outline" class="mr-3 mt-0.5" color="red-darken-3" />
      <div class="flex-grow-1">
        <div class="text-sm font-weight-bold">
          {{ t('حذف نهایی') }}
        </div>
        <div class="text-xs opacity-90 mt-1">
          {{ t('این عمل غیرقابل بازگشت است. برای حذف نهایی تأیید کنید.') }}
        </div>

        <div v-if="target" class="mt-2 text-xs">
          <span class="font-weight-bold">#{{ displayId }}</span>
          <span class="mx-2">•</span>
          <span class="text-truncate d-inline-block" style="max-width: 220px">
            {{ displayName || '-' }}
          </span>
        </div>
      </div>
    </div>

    <template #actions>
      <v-btn variant="text" class="text-grey-darken-2" @click="cancelAll">
        {{ t('لغو') }}
      </v-btn>
      <v-btn color="error" variant="flat" class="font-weight-bold" @click="finalConfirm">
        {{ t('حذف') }}
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  target: { type: Object, default: null },
  isRtl: { type: Boolean, default: false },
  idKey: { type: String, default: 'id' },
  nameKey: { type: String, default: 'name' },
})

const emit = defineEmits(['update:modelValue', 'confirm'])
const { t } = useI18n()

const displayId = computed(() => props.target?.[props.idKey] ?? '')
const displayName = computed(() => props.target?.[props.nameKey] ?? '')

const step = ref(0)
const snack1 = ref(false)
const snack2 = ref(false)
const snack3 = ref(false)

watch(
  () => props.modelValue,
  (v) => {
    if (v) start()
    else closeAll()
  }
)

function start() {
  step.value = 1
  snack1.value = true
  snack2.value = false
  snack3.value = false
}

function closeAll() {
  snack1.value = false
  snack2.value = false
  snack3.value = false
  step.value = 0
}

function cancelAll() {
  closeAll()
  emit('update:modelValue', false)
}

function next(toStep) {
  step.value = toStep
  snack1.value = toStep === 1
  snack2.value = toStep === 2
  snack3.value = toStep === 3
}

function finalConfirm() {
  emit('confirm', props.target)
  cancelAll()
}

// If user closes snackbar manually -> cancel flow
function onSnackClosed(val, whichStep) {
  if (!val && step.value === whichStep && props.modelValue) {
    cancelAll()
  }
}
</script>

<style scoped>
/* =========================
   BASE SNACKBAR STYLE
========================= */
.confirm-snack :deep(.v-snackbar__wrapper) {
  border-radius: 14px;
  padding: 12px 14px;
}

.confirm-snack :deep(.v-snackbar__content) {
  direction: inherit;
  color: #1f2937; /* neutral text */
}

/* Titles */
.confirm-snack .text-sm.font-weight-bold {
  color: #111827;
}

/* Body text */
.confirm-snack .text-xs {
  color: #374151;
}

/* =========================
   STEP 1 – Soft warning
========================= */
.confirm-snack--step1 :deep(.v-snackbar__wrapper) {
  background: #ffffff;
  border: 1px solid rgba(245, 158, 11, 0.35);
  margin-bottom: 24px; /* lift from bottom */
}

/* =========================
   STEP 2 – Serious warning
========================= */
.confirm-snack--step2 :deep(.v-snackbar__wrapper) {
  background: #fff7f3;
  border: 1px solid rgba(234, 88, 12, 0.45);
  margin-top: 20px;
  margin-right: 20px;
}

/* =========================
   STEP 3 – Final danger
========================= */
.confirm-snack--step3 :deep(.v-snackbar__wrapper) {
  background: #fff3f3;
  border: 1px solid rgba(220, 38, 38, 0.55);
  box-shadow: 0 20px 50px rgba(220, 38, 38, 0.25);
}

/* Final step text emphasis */
.confirm-snack--step3 .text-sm.font-weight-bold {
  color: #b91c1c;
}

.confirm-snack--step3 .text-xs {
  color: #7f1d1d;
}
</style>
