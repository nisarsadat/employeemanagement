<template>
  <v-autocomplete
    v-model="modelProxy"
    :items="items"
    item-title="name"
    item-value="name"
    :label="label"
    density="compact"
    variant="outlined"
    clearable
    hide-details="auto"
    :loading="lookups.loading"
    :disabled="disabled"
  />
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useLookupsRepository } from '@/stores/LookupsRepository'

/* ================= PROPS ================= */
const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'فارمت آمریت' },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

/* ================= STORE ================= */
const lookups = useLookupsRepository()

/* ================= V-MODEL ================= */
const modelProxy = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v ?? ''),
})

/* ================= ITEMS ================= */
const items = computed(() => lookups.FormatZones ?? [])

/* ================= INIT ================= */
onMounted(() => {
  if (!lookups.FormatZones?.length) {
    lookups.GetFormatZones()
  }
})
</script>
