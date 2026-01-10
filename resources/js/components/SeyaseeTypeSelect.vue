<template>
  <v-row>
    <v-col :cols="cols" :md="md">
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
    </v-col>
  </v-row>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useLookupsRepository } from '@/stores/LookupsRepository'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'نوع سیاسی' },
  disabled: { type: Boolean, default: false },

  // ✅ same style as Province component
  cols: { type: [Number, String], default: 12 },
  md: { type: [Number, String], default: 4 },
})

const emit = defineEmits(['update:modelValue'])

const lookups = useLookupsRepository()

const modelProxy = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v ?? ''),
})

const items = computed(() => lookups.SeyaseeTypes ?? [])

onMounted(() => {
  if (!lookups.SeyaseeTypes?.length) {
    lookups.GetSeyaseeTypes()
  }
})
</script>
