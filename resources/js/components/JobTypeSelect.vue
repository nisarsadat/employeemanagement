<template>
  <v-row>
    <v-col :cols="cols" :md="md">
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
        @update:modelValue="onPicked"
        auto-select-first
        :menu-props="{ maxHeight: 360 }"
      />
    </v-col>
  </v-row>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useLookupsRepository } from '@/stores/LookupsRepository'

/* ================= PROPS ================= */
const props = defineProps({
  modelValue: { type: String, default: '' }, // backend expects NAME
  label: { type: String, default: 'نوعیت وظیفه' },
  disabled: { type: Boolean, default: false },

  cols: { type: [Number, String], default: 12 },
  md: { type: [Number, String], default: 12 },
})

const emit = defineEmits(['update:modelValue', 'change'])
const lookups = useLookupsRepository()

/* ================= HELPERS ================= */
const clean = (v) => (v == null ? '' : String(v).trim())

const unwrap = (x) => {
  if (Array.isArray(x)) return x
  if (x && Array.isArray(x.data)) return x.data
  return []
}

const itemTitle = (x) => x?.name ?? x?.title ?? ''
const itemValue = (x) => itemTitle(x)

/* ================= ITEMS ================= */
const items = computed(() => unwrap(lookups.JobTypes))

/* ================= V-MODEL ================= */
const valueProxy = computed({
  get: () => clean(props.modelValue),
  set: (v) => emit('update:modelValue', clean(v)),
})

/* ================= EVENTS ================= */
function onPicked() {
  emit('change', valueProxy.value)
}

/* ================= INIT ================= */
onMounted(() => {
  if (!items.value.length) {
    lookups.GetJobTypes()
  }
})
</script>
