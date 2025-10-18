<template>
  <v-menu
    v-model="menu"
    :close-on-content-click="false"
    transition="fade-transition"
    location="top start"
    :max-width="320"
  >
    <template #activator="{ props }">
      <v-text-field
        v-bind="props"
        :model-value="display"
        :label="label"
        variant="outlined"
        density="compact"
        rounded="lg"
        class="date-cell"
        :class="rtl ? 'date-cell-rtl' : 'date-cell-ltr'"
        :rules="rules"
        hide-details="auto"
        readonly
        clearable
        :dir="'ltr'"
        :prepend-inner-icon="rtl ? undefined : 'mdi-calendar-month-outline'"
        :append-inner-icon="rtl ? 'mdi-calendar-month-outline' : undefined"
        @click:clear="clearDate"
      />
    </template>

    <v-card elevation="2">
      <v-date-picker
        v-model="inner"
        color="primary"
        hide-actions
        @update:model-value="onPick"
      />
      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="menu = false">{{ closeText }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCalendar } from '../../composables/useCalendar'
import dayjs from 'dayjs'

const props = defineProps({
  modelValue: { type: String, default: null },
  label: { type: String, default: '' },
  rules: { type: Array, default: () => [] },
})
const emit = defineEmits(['update:modelValue'])

const { t, locale } = useI18n()
const { formatDisplay, toIso } = useCalendar()

const menu = ref(false)
const inner = ref(props.modelValue)
const rtl = computed(() => locale.value === 'fa')

/* -------- AUTO-FILL TODAY DATE -------- */
onMounted(() => {
  if (!props.modelValue) {
    const today = dayjs().format('YYYY-MM-DD')
    inner.value = today
    emit('update:modelValue', today)
  }
})
/* -------------------------------------- */

watch(() => props.modelValue, v => { inner.value = v })

const display = computed(() => formatDisplay(props.modelValue, locale.value === 'fa' ? 'fa' : 'en'))
const closeText = computed(() => t('بستن'))

function onPick(val) {
  const iso = toIso(val)
  inner.value = iso
  emit('update:modelValue', iso)
  menu.value = false
}

function clearDate() {
  emit('update:modelValue', null)
}
</script>

<style scoped>
.date-cell {
  min-width: 160px;
  max-width: 190px;
}
.date-cell-ltr :deep(.v-field__input),
.date-cell-rtl :deep(.v-field__input) {
  padding-left: 8px;
  padding-right: 8px;
}
:deep(.v-field__input) {
  line-height: 1.4;
}
</style>
