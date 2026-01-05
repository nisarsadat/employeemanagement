<template>
  <v-autocomplete
    v-model="valueProxy"
    :items="items"
    :item-title="itemTitle"
    :item-value="itemValue"
    :label="label"
    density="compact"
    variant="outlined"
    clearable
    hide-details="auto"
    :loading="lookups.loading"
    :disabled="disabled"
    :error="error"
    :error-messages="errorMessages"
  />
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useLookupsRepository } from '@/stores/LookupsRepository'

const props = defineProps({
  modelValue: { type: String, default: '' }, // backend expects NAME as string
  label: { type: String, default: 'نوع فعالیت منبع' },
  disabled: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
  errorMessages: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])
const lookups = useLookupsRepository()

const clean = (v) => (v == null ? '' : String(v).trim())

const unwrap = (x) => {
  if (Array.isArray(x)) return x
  if (x && Array.isArray(x.data)) return x.data
  return []
}

const items = computed(() => unwrap(lookups.ResourceActivityTypes))

// show name/title and return same string value
const itemTitle = (x) => x?.name ?? x?.title ?? ''
const itemValue = (x) => itemTitle(x)

const valueProxy = computed({
  get: () => clean(props.modelValue),
  set: (v) => emit('update:modelValue', clean(v)),
})

onMounted(() => {
  if (!items.value.length) {
    lookups.GetResourceActivityTypes()
  }
})
</script>
