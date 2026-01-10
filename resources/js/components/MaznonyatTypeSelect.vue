<!-- CombinedInfoTypeSelect.vue -->
<template>
  <v-row>
    <v-col :cols="cols" :md="md">
      <v-autocomplete
        v-model="modelProxy"
        :items="combinedItems"
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
      >
        <!-- show which group it came from -->
        <template #item="{ props: itemProps, item }">
          <v-list-item v-bind="itemProps">
            <v-list-item-subtitle class="text-medium-emphasis">
              {{ item.raw.typeLabel }}
            </v-list-item-subtitle>
          </v-list-item>
        </template>

        <template #selection="{ item }">
          <span>{{ item.raw.name }}</span>
          <span class="text-medium-emphasis" style="margin-inline-start: 6px">
            ({{ item.raw.typeLabel }})
          </span>
        </template>
      </v-autocomplete>
    </v-col>
  </v-row>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useLookupsRepository } from '@/stores/LookupsRepository'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'نوع (سیاسی / جنایی)' },
  disabled: { type: Boolean, default: false },

  cols: { type: [Number, String], default: 12 },
  md: { type: [Number, String], default: 4 },
})

const emit = defineEmits(['update:modelValue'])

const lookups = useLookupsRepository()

const modelProxy = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v ?? ''),
})

const combinedItems = computed(() => {
  const seyasee = (lookups.SeyaseeTypes ?? []).map((x) => ({
    ...x,
    type: 'seyasee',
    typeLabel: 'سیاسی',
  }))

  const jenayee = (lookups.JenayeeTypes ?? []).map((x) => ({
    ...x,
    type: 'jenayee',
    typeLabel: 'جنایی',
  }))

  // ✅ merge both lists
  return [...seyasee, ...jenayee]
})

onMounted(() => {
  if (!lookups.SeyaseeTypes?.length) lookups.GetSeyaseeTypes()
  if (!lookups.JenayeeTypes?.length) lookups.GetJenayeeTypes()
})
</script>
