<template>
  <v-autocomplete
    v-model="modelProxy"
    :items="items"
    item-title="display_name"
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
import { useSystemProfileRepository } from '@/stores/SystemProfileRepository'

/* ================= PROPS ================= */
const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'نوع سند' },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

/* ================= STORE ================= */
const lookups = useLookupsRepository()
const systemProfile = useSystemProfileRepository()

function brandLabel(text) {
  const name = systemProfile.name || ''
  return String(text ?? '').replace(/۰۳۰|030|۳۳۳|333/g, name)
}

/* ================= V-MODEL ================= */
const modelProxy = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v ?? ''),
})

/* ================= ITEMS ================= */
const items = computed(() =>
  (lookups.DocsTypes ?? []).map((item) => ({
    ...item,
    display_name: brandLabel(item?.name),
  }))
)

/* ================= INIT ================= */
onMounted(() => {
  systemProfile.ensureLoaded()
  if (!lookups.DocsTypes?.length) {
    lookups.GetDocsTypes()
  }
})
</script>
