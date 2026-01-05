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
    auto-select-first
    :menu-props="{ maxHeight: 360 }"
  />
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useLookupsRepository } from '@/stores/LookupsRepository'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'نوع جنایی' },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const lookups = useLookupsRepository()

const modelProxy = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v ?? ''),
})

const items = computed(() => lookups.JenayeeTypes ?? [])

onMounted(() => {
  if (!lookups.JenayeeTypes?.length) {
    lookups.GetJenayeeTypes()
  }
})
</script>
