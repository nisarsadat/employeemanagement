<template>
  <v-autocomplete
    v-model="modelProxy"
    :items="items"
    item-title="information_category"
    item-value="information_category"
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

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const lookups = useLookupsRepository()

const modelProxy = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v ?? ''),
})

const items = computed(() => lookups.InformationCategories ?? [])

onMounted(() => {
  if (!lookups.InformationCategories?.length) {
    lookups.GetInformationCategories()
  }
})
</script>