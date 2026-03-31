<template>
  <v-dialog v-model="localValue" max-width="700" persistent>
    <v-card rounded="xl" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-card-title class="d-flex align-center justify-space-between px-4 py-3">
        <div class="d-flex align-center ga-2">
          <v-icon color="indigo">mdi-cash-edit</v-icon>
          <span class="font-weight-bold fontfamily">
            {{ t(' مالی برخه') }}
          </span>
        </div>

        <v-btn icon variant="text" @click="closeDialog">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-divider />

      <v-card-text class="pt-4">
        <v-row>
          <!-- account -->
          <v-col cols="12">
            <v-autocomplete
              v-model="form.account_id"
              :items="accountItems"
              item-title="display"
              item-value="id"
              :label="t('حساب')"
              density="compact"
              variant="outlined"
              clearable
            />
          </v-col>

          <!-- amount number -->
          <v-col cols="12" md="6">
            <v-text-field
              v-model="form.message_to_finance"
              :label="t('مقدار به عدد')"
              type="number"
              density="compact"
              variant="outlined"
            />
          </v-col>

          <!-- amount words -->
          <v-col cols="12" md="6">
            <v-text-field
              v-model="form.money_amount_words"
              :label="t('مقدار به حروف')"
              density="compact"
              variant="outlined"
            />
          </v-col>
        </v-row>
      </v-card-text>

      <v-divider />

      <v-card-actions class="px-4 py-3 d-flex justify-end ga-2">
        <v-btn variant="tonal" color="grey" class="text-none" @click="closeDialog">
          {{ t('بستن') }}
        </v-btn>

        <v-btn color="primary" density="compact" variant="tonal" :loading="loading" @click="submit">
          {{ t('ذخیره') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { computed, ref, watch } from 'vue';
  import { useI18n } from 'vue-i18n';

  const props = defineProps({
    modelValue: { type: Boolean, default: false },
    id: { type: [Number, String, null], default: null },
    account_id: { type: [Number, String, null], default: null },
    message_to_finance: { type: [String, Number], default: '' },
    money_amount_words: { type: String, default: '' },
    accountItems: { type: Array, default: () => [] },
    isRtl: { type: Boolean, default: true },
    loading: { type: Boolean, default: false },
  });

  const emit = defineEmits(['update:modelValue', 'save']);

  const { t } = useI18n();

  const localValue = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
  });

  const blankForm = () => ({
    id: props.id ?? null,
    account_id: props.account_id ?? null,
    message_to_finance: props.message_to_finance ?? '',
    money_amount_words: props.money_amount_words ?? '',
  });

  const form = ref(blankForm());

  watch(
    () => props.modelValue,
    (open) => {
      if (open) {
        form.value = blankForm();
      }
    }
  );

  watch(
    () => [props.id, props.account_id, props.message_to_finance, props.money_amount_words],
    () => {
      if (props.modelValue) {
        form.value = blankForm();
      }
    }
  );

  function closeDialog() {
    localValue.value = false;
  }

  function submit() {
    emit('save', {
      id: form.value.id,
      account_id: form.value.account_id,
      message_to_finance: form.value.message_to_finance,
      money_amount_words: form.value.money_amount_words,
    });
  }
</script>

<style scoped>
  .fontfamily {
    font-family: var(--app-font) !important;
  }

  :deep(.v-field-label),
  :deep(.v-label),
  :deep(input::placeholder) {
    font-size: 12px !important;
    line-height: 1.2 !important;
    color: #555 !important;
    font-family: var(--app-font) !important;
  }

  :deep(.v-field__input) {
    min-height: 30px !important;
    padding: 3px 8px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
    font-family: var(--app-font) !important;
  }
</style>
