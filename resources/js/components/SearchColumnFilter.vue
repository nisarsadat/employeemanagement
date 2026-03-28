<template>
  <v-row class="align-center">
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
        @update:model-value="$emit('update:modelColumn', $event)"
      />
    </v-col>

    <v-col cols="12" sm="8" md="9" lg="9">
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
        @click:clear="$emit('clear')"
      />
    </v-col>
  </v-row>
</template>

<script setup>
  import { computed } from 'vue';
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
    excludeKeys: {
      type: Array,
      default: () => ['picture', 'map', 'actions'],
    },
  });

  defineEmits(['update:modelSearch', 'update:modelColumn', 'search', 'clear']);

  const { t } = useI18n();

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
</script>

<style scoped>

</style>
