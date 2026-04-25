<!-- src/components/ResourceFiftySixFormatDialog.vue -->
<template>
  <v-dialog v-model="localModel" max-width="620" :dir="isRtl ? 'rtl' : 'ltr'">
    <v-card rounded="xl" class="overflow-hidden">
      <!-- Header -->
      <v-toolbar color="primary" density="comfortable" class="text-white">
        <v-toolbar-title class="font-weight-bold">
          {{ t('۵۶ فارمټونه') }}
        </v-toolbar-title>

        <v-spacer />

        <v-btn icon variant="text" color="white" @click="close">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <!-- Owner Info -->
      <v-card-text class="pb-2">
        <div class="d-flex align-center justify-space-between mb-3">
          <div>
            <div class="text-subtitle-1 font-weight-bold">
              {{ t('مربوط منبع') }}
            </div>

            <div class="text-caption text-medium-emphasis">
              {{ t('کود') }}:
              <strong>{{ ownerCodeValue || ownerIdValue || '-' }}</strong>
            </div>
          </div>

          <v-chip color="pink" variant="tonal" size="large">
            <v-icon start size="18">mdi-file-document-multiple-outline</v-icon>
            {{ totalCount }}
          </v-chip>
        </div>

        <v-divider />
      </v-card-text>

      <!-- Loading -->
      <v-card-text v-if="loading" class="py-8 text-center">
        <v-progress-circular indeterminate color="primary" size="42" />
        <div class="text-body-2 text-medium-emphasis mt-3">
          {{ t('در حال دریافت معلومات...') }}
        </div>
      </v-card-text>

      <!-- Error -->
      <v-card-text v-else-if="error" class="py-6">
        <v-alert type="error" variant="tonal" rounded="lg">
          {{ error }}
        </v-alert>
      </v-card-text>

      <!-- Empty -->
      <v-card-text v-else-if="!items.length" class="py-8 text-center">
        <v-icon size="48" color="grey">mdi-file-document-outline</v-icon>

        <div class="text-body-1 font-weight-medium mt-3">
          {{ t('هیچ ۵۶ فارمټی برای این منبع ثبت نشده است') }}
        </div>
      </v-card-text>

      <!-- List -->
      <v-card-text v-else class="pt-1">
        <v-list class="rounded-xl pa-0" density="comfortable">
          <v-list-item
            v-for="item in items"
            :key="itemKey(item)"
            class="format-row mb-2"
            rounded="lg"
            @click="goToFormat(item)"
          >
            <template #prepend>
              <v-avatar color="pink" variant="tonal" size="42">
                <v-icon color="pink">mdi-file-document-multiple-outline</v-icon>
              </v-avatar>
            </template>

            <v-list-item-title class="font-weight-bold">
              {{ getItemName(item) }}
            </v-list-item-title>

            <v-list-item-subtitle>
              {{ getTargetPath(item) || '-' }}
            </v-list-item-subtitle>

            <template #append>
              <div class="d-flex align-center ga-2">
                <v-chip color="primary" variant="flat" size="small">
                  {{ Number(item.count) || 0 }}
                </v-chip>

                <v-icon color="grey-darken-1">
                  {{ isRtl ? 'mdi-chevron-left' : 'mdi-chevron-right' }}
                </v-icon>
              </div>
            </template>
          </v-list-item>
        </v-list>
      </v-card-text>

      <v-card-actions class="justify-end px-4 pb-4">
        <v-btn color="primary" variant="flat" rounded="lg" @click="close">
          {{ t('بستن') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { computed, ref, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import { useDatabaseRepository } from '@/stores/DatabaseRepository';

  const props = defineProps({
    modelValue: {
      type: Boolean,
      default: false,
    },

    /**
     * ✅ New names used in Resources.vue
     */
    ownerId: {
      type: [Number, String, null],
      default: null,
    },
    ownerCode: {
      type: [Number, String, null],
      default: null,
    },

    /**
     * ✅ Old names also supported, so component works everywhere
     */
    resourceId: {
      type: [Number, String, null],
      default: null,
    },
    resourceCode: {
      type: [Number, String, null],
      default: null,
    },

    /**
     * For resource related 56-format filtering.
     * Example API should become:
     * /api/fasads?page=1&perPage=10&search=&search_field=resource_id&search_value=202
     */
    filterKey: {
      type: String,
      default: 'resource_id',
    },

    isRtl: {
      type: Boolean,
      default: true,
    },
  });

  const emit = defineEmits(['update:modelValue']);

  const router = useRouter();
  const repo = useDatabaseRepository();
  const { t } = useI18n();

  const loading = ref(false);
  const error = ref('');
  const items = ref([]);
  const totalCount = ref(0);

  const localModel = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
  });

  const ownerIdValue = computed(() => props.ownerId || props.resourceId || null);
  const ownerCodeValue = computed(() => props.ownerCode || props.resourceCode || null);

  function close() {
    localModel.value = false;
  }

  function itemKey(item) {
    return item?.type || item?.table || item?.front_path || item?.list_path || item?.path;
  }

  function getItemName(item) {
    return item?.name || item?.typeLabel || item?.label || item?.type || '-';
  }

  function getTargetPath(item) {
    return item?.front_path || item?.list_path || item?.path || null;
  }

  function cleanQuery(query) {
    const cleaned = {};

    Object.entries(query || {}).forEach(([key, value]) => {
      if (value === null || value === undefined) return;
      cleaned[key] = value;
    });

    return cleaned;
  }

  function buildTargetQuery() {
    return cleanQuery({
      search: '',
      search_field: props.filterKey || 'resource_id',
      search_value: ownerIdValue.value,
    });
  }

  async function fetchCounts() {
    const id = ownerIdValue.value;

    if (!id) {
      items.value = [];
      totalCount.value = 0;
      return;
    }

    loading.value = true;
    error.value = '';
    items.value = [];
    totalCount.value = 0;

    try {
      const res = await repo.FetchResourceFiftySixFormatCounts(id);
      const data = res?.data?.data || res?.data || res || {};

      items.value = data.formats || data.items || [];
      totalCount.value = Number(data.total_count || data.total || 0);
    } catch (e) {
      console.error('FetchResourceFiftySixFormatCounts error:', e);
      error.value = t('خطا در دریافت معلومات ۵۶ فارمټ');
    } finally {
      loading.value = false;
    }
  }

  function goToFormat(item) {
    const targetPath = getTargetPath(item);

    if (!targetPath || !ownerIdValue.value) return;

    close();

    router.push({
      path: targetPath,
      query: buildTargetQuery(),
    });
  }

  watch(
    () => props.modelValue,
    async (open) => {
      if (open) {
        await fetchCounts();
      }
    }
  );

  watch(
    () => ownerIdValue.value,
    async (id) => {
      if (localModel.value && id) {
        await fetchCounts();
      }
    }
  );
</script>

<style scoped>
  .ga-2 {
    gap: 8px;
  }

  .format-row {
    border: 1px solid rgba(148, 163, 184, 0.25);
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .format-row:hover {
    transform: translateY(-2px);
    background: #fdf2f8;
    border-color: rgba(236, 72, 153, 0.35);
  }
</style>