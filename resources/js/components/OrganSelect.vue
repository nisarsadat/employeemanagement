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
  label: { type: String, default: 'ارگان' },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const lookups = useLookupsRepository()

/* ================= V-MODEL ================= */
const modelProxy = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v ?? ''),
})

/* ================= ITEMS ================= */
const items = computed(() => lookups.Organs ?? [])

/* ================= INIT ================= */
onMounted(() => {
  if (!lookups.Organs?.length) {
    lookups.GetOrgans()
  }
})
</script>
