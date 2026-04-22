<template>
  <v-autocomplete
    v-model="localValue"
    v-model:search="search"
    :items="items"
    :item-title="resolvedItemTitle"
    item-value="id"
    :label="label"
    :density="density"
    :variant="variant"
    :rounded="rounded"
    :clearable="clearable"
    :loading="lookup.resourcesLoading"
    :disabled="disabled"
    :rules="rules"
    :error="error"
    :error-messages="errorMessages"
    no-filter
    hide-no-data="false"
    @update:model-value="onModelUpdate"
    @keydown.enter="forceSearch"
  />
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useLookupRepository } from '@/stores/LookupRepository';

const props = defineProps({
  modelValue: {
    type: [Number, String, null],
    default: null,
  },
  label: {
    type: String,
    default: 'Resource',
  },
  density: {
    type: String,
    default: 'compact',
  },
  variant: {
    type: String,
    default: 'outlined',
  },
  rounded: {
    type: [String, Number],
    default: 'lg',
  },
  clearable: {
    type: Boolean,
    default: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  rules: {
    type: Array,
    default: () => [],
  },
  error: {
    type: Boolean,
    default: false,
  },
  errorMessages: {
    type: Array,
    default: () => [],
  },
  initialFetch: {
    type: Boolean,
    default: true,
  },
  perPage: {
    type: Number,
    default: 20,
  },
  itemTitle: {
    type: [String, Function],
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'selected', 'cleared']);

const lookup = useLookupRepository();

const localValue = ref(props.modelValue);
const search = ref('');
let timer = null;

watch(
  () => props.modelValue,
  (v) => {
    localValue.value = v;
  }
);

watch(search, (q) => {
  clearTimeout(timer);
  timer = setTimeout(() => {
    lookup.FetchResourcesLookup({
      search: q?.trim?.() || '',
      perPage: props.perPage,
    }).catch(() => {});
  }, 300);
});

const items = computed(() => lookup.resources || []);

const defaultTitle = (item) => {
  if (!item) return '';
  const id = item.id ?? '';
  const code = item.code_number ?? '';
  const name = item.name ?? '';
  const nick = item.nick_name ?? '';
  return [id, code, name, nick].filter(Boolean).join(' — ');
};

const resolvedItemTitle = computed(() => {
  if (typeof props.itemTitle === 'function') return props.itemTitle;
  if (typeof props.itemTitle === 'string' && props.itemTitle.trim()) return props.itemTitle;
  return defaultTitle;
});

function onModelUpdate(value) {
  emit('update:modelValue', value);

  if (!value) {
    emit('cleared');
    return;
  }

  const selected = lookup.getResourceById(value);
  if (selected) emit('selected', selected);
}

function forceSearch() {
  lookup.FetchResourcesLookup({
    search: search.value?.trim?.() || '',
    perPage: props.perPage,
  }).catch(() => {});
}

onMounted(() => {
  if (props.initialFetch) {
    lookup.FetchResourcesLookup({ search: '', perPage: props.perPage }).catch(() => {});
  }
});
</script> 