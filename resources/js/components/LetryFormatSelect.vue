<!-- src/components/lookups/LetryFormatSelect.vue -->
<template>
  <v-autocomplete
    v-model="modelProxy"
    :items="items"
    item-title="letry_format_name"
    item-value="letry_format_name"
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
import { computed, onMounted } from 'vue';
import { useLookupsRepository } from '@/stores/LookupsRepository';

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'فورمت لتری' },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const lookups = useLookupsRepository();

const modelProxy = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v ?? ''),
});

const items = computed(() => lookups.LetryFormats ?? []);

onMounted(() => {
  if (!lookups.LetryFormats?.length) {
    lookups.GetLetryFormats();
  }
});
</script>
