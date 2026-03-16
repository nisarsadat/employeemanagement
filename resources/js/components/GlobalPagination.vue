<template>
  <div class="pagination-wrap bg-projectbg mt-4">
    <v-card rounded="xl" variant="flat" flat class="pagination-card px-2">
      <div class="d-flex align-center justify-space-between flex-wrap ga-4">
        <!-- Total + info -->
        <div class="d-flex align-center ga-3 flex-wrap min-w-0">
          <div class="total-chip">
            <span class="total-label">{{ totalLabel }}</span>
            <strong class="total-value">{{ resolvedTotal }}</strong>
          </div>

          <div class="text-caption text-medium-emphasis pagination-hint">
            {{ pageInfoText }}
          </div>
        </div>

        <!-- Controls -->
        <div class="d-flex align-center ga-3 flex-wrap pagination-actions">
          <div class="pagination-box">
            <v-pagination
              :model-value="page"
              :length="pageCount"
              density="comfortable"
              total-visible="7"
              prev-icon="mdi-chevron-left-circle-outline"
              next-icon="mdi-chevron-right-circle-outline"
              @update:model-value="$emit('update:page', $event)"
            />
          </div>

          <v-select
            :model-value="itemsPerPage"
            :items="normalizedPerPageItems"
            :label="itemsPerPageLabel"
            density="compact"
            hide-details
            chips
            variant="underlined"
            class="per-page-select mt-2"
            menu-icon="mdi-chevron-down"
            @update:model-value="onPerPageChange"
          />
        </div>
      </div>
    </v-card>
  </div>
</template>

<script setup>
  import { computed } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const props = defineProps({
    page: { type: Number, required: true },
    itemsPerPage: { type: Number, required: true },
    total: { type: Number, default: undefined },
    totalItems: { type: Number, default: undefined },
    perPageItems: { type: Array, default: () => [10, 20, 50, 100] },
  });

  const emit = defineEmits(['update:page', 'update:itemsPerPage']);

  const normalizedPerPageItems = computed(() =>
    (props.perPageItems || []).map((v) => ({
      title: String(v),
      value: Number(v),
    }))
  );

  const resolvedTotal = computed(() => Number(props.total ?? props.totalItems ?? 0));

  const pageCount = computed(() => {
    const per = Number(props.itemsPerPage || 10);
    const total = Number(resolvedTotal.value || 0);
    return Math.max(1, Math.ceil(total / per));
  });

  const currentFrom = computed(() => {
    if (!resolvedTotal.value) return 0;
    return (props.page - 1) * props.itemsPerPage + 1;
  });

  const currentTo = computed(() => {
    if (!resolvedTotal.value) return 0;
    return Math.min(props.page * props.itemsPerPage, resolvedTotal.value);
  });

  const totalLabel = computed(() => t('pagination.total'));
  const itemsPerPageLabel = computed(() => t('pagination.itemsPerPage'));

  const pageInfoText = computed(() =>
    t('pagination.pageInfo', {
      from: currentFrom.value,
      to: currentTo.value,
      total: resolvedTotal.value,
    })
  );

  function onPerPageChange(value) {
    emit('update:itemsPerPage', Number(value));
  }
</script>

<style scoped>
  .pagination-wrap {
    width: 100%;
    margin-bottom: 60px !important;
  }

  .pagination-card {
    background: transparent !important;
    box-shadow: none !important;
    border: 0 !important;
  }

  .total-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 36px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(var(--v-theme-primary), 0.06);
    white-space: nowrap;
  }

  .total-label {
    font-size: 0.82rem;
    color: rgba(var(--v-theme-on-surface), 0.7);
  }

  .total-value {
    font-size: 0.95rem;
    color: rgb(var(--v-theme-primary));
  }

  .pagination-hint {
    white-space: nowrap;
  }

  .pagination-actions {
    margin-inline-start: auto;
  }

  .per-page-select {
    min-width: 120px;
    max-width: 140px;
  }

  .pagination-box {
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
    border-radius: 12px;
    overflow: hidden;
    background: rgb(var(--v-theme-surface));
  }

  .min-w-0 {
    min-width: 0;
  }

  :deep(.v-input),
  :deep(.v-input__control),
  :deep(.v-field) {
    background: transparent !important;
    box-shadow: none !important;
  }

  :deep(.v-field--variant-underlined .v-field__outline) {
    display: block !important;
  }

  :deep(.v-field--variant-underlined .v-field__outline__start),
  :deep(.v-field--variant-underlined .v-field__outline__end) {
    display: none !important;
  }

  :deep(.v-field__overlay) {
    background: transparent !important;
  }

  :deep(.v-field__field) {
    background: transparent !important;
  }

  :deep(.v-select .v-field__input) {
    min-height: 34px !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    display: flex;
    align-items: center;
  }

  :deep(.v-pagination) {
    gap: 0 !important;
  }

  :deep(.v-pagination__list) {
    display: flex;
    flex-wrap: nowrap;
    gap: 0 !important;
  }

  :deep(.v-pagination__item),
  :deep(.v-pagination__prev),
  :deep(.v-pagination__next),
  :deep(.v-pagination__first),
  :deep(.v-pagination__last),
  :deep(.v-pagination__more) {
    margin: 0 !important;
  }

  :deep(.v-pagination__item .v-btn),
  :deep(.v-pagination__prev .v-btn),
  :deep(.v-pagination__next .v-btn),
  :deep(.v-pagination__first .v-btn),
  :deep(.v-pagination__last .v-btn),
  :deep(.v-pagination__more .v-btn) {
    min-width: 48px !important;
    height: 40px !important;
    margin: 0 !important;
    border-radius: 0 !important;
    text-transform: none !important;
    letter-spacing: 0 !important;
    font-weight: 600 !important;
    color: rgba(var(--v-theme-on-surface), 0.78) !important;
    border-inline-end: 1px solid rgba(var(--v-theme-on-surface), 0.14);
    box-shadow: none !important;
    background: transparent !important;
  }

  :deep(.v-pagination__item--is-active .v-btn) {
    background: rgba(var(--v-theme-primary), 0.08) !important;
    color: rgb(var(--v-theme-primary)) !important;
    font-weight: 700 !important;
  }

  :deep(.v-pagination__prev .v-btn),
  :deep(.v-pagination__next .v-btn) {
    min-width: 110px !important;
    padding-inline: 14px !important;
  }

  :deep(.v-pagination__prev .v-btn__content) {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  :deep(.v-pagination__next .v-btn__content) {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  :deep(.v-pagination__prev .v-icon),
  :deep(.v-pagination__next .v-icon) {
    font-size: 24px !important;
    color: rgb(var(--v-theme-primary)) !important;
  }

  :deep(.v-pagination__list > *:last-child .v-btn) {
    border-inline-end: 0 !important;
  }

  :deep(.v-btn--disabled) {
    opacity: 0.55 !important;
  }

  @media (max-width: 960px) {
    .pagination-actions {
      width: 100%;
      justify-content: space-between;
      margin-inline-start: 0;
    }

    .pagination-box {
      flex: 1 1 auto;
      min-width: 0;
    }

    :deep(.v-pagination) {
      width: 100%;
    }
  }

  @media (max-width: 700px) {
    .pagination-actions {
      flex-direction: column;
      align-items: stretch !important;
    }

    .per-page-select {
      min-width: 100%;
      max-width: 100%;
    }

    .pagination-box {
      width: 100%;
      overflow-x: auto;
    }

    .pagination-hint {
      white-space: normal;
    }

    .total-chip {
      width: 100%;
      justify-content: center;
    }
  }

  @media (max-width: 600px) {
    .pagination-card {
      padding-inline: 12px !important;
    }

    :deep(.v-pagination__prev .v-btn),
    :deep(.v-pagination__next .v-btn) {
      min-width: 88px !important;
      padding-inline: 10px !important;
    }

    :deep(.v-pagination__item .v-btn),
    :deep(.v-pagination__prev .v-btn),
    :deep(.v-pagination__next .v-btn),
    :deep(.v-pagination__more .v-btn) {
      height: 38px !important;
      font-size: 0.88rem;
    }
  }
</style>