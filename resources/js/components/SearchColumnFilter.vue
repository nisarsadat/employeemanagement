<template>
  <v-row class="align-center">
    <!-- Column select -->
    <v-col cols="12" sm="4" md="3" lg="3">
      <v-select
        :model-value="modelColumn"
        :items="columnItems"
        item-title="title"
        item-value="value"
        density="comfortable"
        variant="outlined"
        rounded="lg"
        hide-details
        clearable
        class="bg-white"
        :label="columnLabel"
        @update:model-value="handleColumnChange"
      />
    </v-col>

   

    <!-- Selected-column value autocomplete -->
    <v-col cols="12" sm="4" md="5" lg="5">
      <v-autocomplete
        :model-value="modelSelectedValue"
        :items="valueItems"
        item-title="title"
        item-value="value"
        density="comfortable"
        variant="outlined"
        rounded="lg"
        hide-details
        clearable
        class="bg-white"
        :label="valueLabelComputed"
        :loading="loadingValues"
        :disabled="!modelColumn"
        :no-data-text="modelColumn ? t('دیتا یافت نشد') : t('اول ستون را انتخاب کنید')"
        @update:model-value="$emit('update:modelSelectedValue', $event)"
        @update:search="handleValueSearch"
        @click:clear="$emit('clearSelectedValue')"
      />
    </v-col>
     <!-- Free text search -->
    <v-col cols="12" sm="4" md="4" lg="4">
      <v-text-field
        :model-value="modelSearch"
        :label="searchLabel"
        density="comfortable"
        prepend-inner-icon="mdi-magnify"
        variant="outlined"
        color="primary"
        rounded="lg"
        hide-details
        clearable
        class="bg-white search-field"
        @update:model-value="$emit('update:modelSearch', $event)"
        @keyup.enter="$emit('search')"
        @click:clear="$emit('clearSearch')"
      />
    </v-col>
  </v-row>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  modelSearch: {
    type: String,
    default: '',
  },
  modelColumn: {
    type: String,
    default: null,
  },
  modelSelectedValue: {
    type: [String, Number],
    default: null,
  },
  headers: {
    type: Array,
    default: () => [],
  },
  searchLabel: {
    type: String,
    default: '',
  },
  columnLabel: {
    type: String,
    default: '',
  },
  allColumnsLabel: {
    type: String,
    default: '',
  },
  valueLabel: {
    type: String,
    default: '',
  },
  excludeKeys: {
    type: Array,
    default: () => ['picture', 'map', 'actions'],
  },
  fetchColumnValues: {
    type: Function,
    default: null,
  },
});

const emit = defineEmits([
  'update:modelSearch',
  'update:modelColumn',
  'update:modelSelectedValue',
  'search',
  'clearSearch',
  'clearSelectedValue',
]);

const { t } = useI18n();

const loadingValues = ref(false);
const valueItems = ref([]);
const valueSearch = ref('');

const columnItems = computed(() => {
  const items = (props.headers || [])
    .filter((h) => h?.key && !props.excludeKeys.includes(h.key))
    .map((h) => ({
      title: typeof h.title === 'string' ? h.title : h.key,
      value: h.key,
    }));

  return [
    {
      title: props.allColumnsLabel || t('همه ستون‌ها'),
      value: null,
    },
    ...items,
  ];
});

const valueLabelComputed = computed(() => {
  if (props.valueLabel) return props.valueLabel;
  return props.modelColumn
    ? t('ارزش ستون انتخاب‌شده')
    : t('ارزش ستون');
});

async function loadColumnValues(searchText = '') {
  if (!props.modelColumn || typeof props.fetchColumnValues !== 'function') {
    valueItems.value = [];
    return;
  }

  loadingValues.value = true;
  try {
    const rows = await props.fetchColumnValues(props.modelColumn, searchText);

    valueItems.value = (rows || []).map((item) => {
      if (typeof item === 'object' && item !== null) {
        return {
          title: item.title ?? item.value ?? '',
          value: item.value ?? item.title ?? '',
        };
      }

      return {
        title: String(item ?? ''),
        value: item,
      };
    });
  } catch (e) {
    console.error('loadColumnValues error:', e);
    valueItems.value = [];
  } finally {
    loadingValues.value = false;
  }
}

async function handleColumnChange(value) {
  emit('update:modelColumn', value);
  emit('update:modelSelectedValue', null);
  valueSearch.value = '';

  if (value) {
    await loadColumnValues('');
  } else {
    valueItems.value = [];
  }
}

async function handleValueSearch(value) {
  valueSearch.value = value || '';
  await loadColumnValues(valueSearch.value);
}

watch(
  () => props.modelColumn,
  async (newVal) => {
    if (!newVal) {
      valueItems.value = [];
      return;
    }
    await loadColumnValues('');
  }
);
</script>

<style scoped>

</style>