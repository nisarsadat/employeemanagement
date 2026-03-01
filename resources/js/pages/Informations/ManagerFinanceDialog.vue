<template>
  <v-dialog v-model="model" max-width="780" scrollable transition="dialog-bottom-transition">
    <v-card rounded="xl" elevation="5" :dir="isRtl ? 'rtl' : 'ltr'" class="finance-dialog">
      <!-- Header with gradient -->
      <v-card-title class="d-flex align-center justify-space-between pa-4 bg-gradient">
        <div class="d-flex align-center ga-3">
          <v-avatar color="indigo-darken-3" size="40" variant="flat" class="elevation-2">
            <v-icon color="white" size="24">mdi-cash-edit</v-icon>
          </v-avatar>
          <div>
            <span class="text-h6 font-weight-bold text-white"
              >{{ t('معلومات مالی') }} {{ t('او') }} {{ t('نظر مدیر') }}</span
            >
            <div class="text-caption text-white text-opacity-80" v-if="id">ID: {{ id }}</div>
          </div>
        </div>

        <v-btn icon variant="text" @click="close" class="btn-close" color="white">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-divider class="border-opacity-25" />

      <!-- Body with improved spacing and animations -->
      <v-card-text class="pa-6" style="max-height: 70vh">
        <v-row>
          <v-col cols="12" md="6">
            <v-autocomplete
              v-model="local.account_id"
              :items="accountItems"
              item-title="display"
              item-value="id"
              :label="t('حساب')"
              density="comfortable"
              variant="outlined"
              clearable
              :disabled="loading"
              prepend-inner-icon="mdi-bank"
              bg-color="surface"
              class="rounded-lg"
              hide-details="auto"
            />
          </v-col>

          <v-col cols="12" md="6">
            <v-text-field
              v-model="local.message_to_finance"
              :label="t('مسج مالی بخش ته')"
              density="comfortable"
              type="number"
              variant="outlined"
              :disabled="loading"
              prepend-inner-icon="mdi-cash"
              bg-color="surface"
              class="rounded-lg"
              hide-details="auto"
            />
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="local.money_amount_words"
              :label="t('مقدار په حروف')"
              density="comfortable"
              variant="outlined"
              :disabled="loading"
              prepend-inner-icon="mdi-text"
              bg-color="surface"
              class="rounded-lg"
              hide-details="auto"
            />
          </v-col>

          <v-col cols="12">
            <v-textarea
              v-model="local.manager_comment"
              :label="t('نظر مدیر')"
              rows="4"
              auto-grow
              density="comfortable"
              variant="outlined"
              :disabled="loading"
              prepend-inner-icon="mdi-comment-text"
              bg-color="surface"
              class="rounded-lg"
              hide-details="auto"
            />
          </v-col>
        </v-row>
      </v-card-text>

      <v-divider class="border-opacity-25" />

      <!-- Footer with enhanced buttons -->
      <v-card-actions class="pa-4 d-flex justify-end ga-3 bg-grey-lighten-4">
        <v-btn
          variant="outlined"
          @click="close"
          :disabled="loading"
          prepend-icon="mdi-close"
          class="text-none px-6"
          rounded="pill"
        >
          {{ t('بستن') }}
        </v-btn>

        <v-btn
          color="indigo-darken-3"
          variant="flat"
          :loading="loading"
          @click="save"
          prepend-icon="mdi-content-save"
          class="text-none px-8 elevation-2"
          rounded="pill"
          v-if="auth.can('direction.create')"
          size="large"
        >
          {{ t('ثبت') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { computed, reactive, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useLoginRepository } from '../../stores/LoginRepository';
  const auth = useLoginRepository();

  const { t } = useI18n();

  const props = defineProps({
    modelValue: { type: Boolean, default: false },
    id: { type: [Number, String], default: null },
    account_id: { type: [Number, String, null], default: null },
    message_to_finance: { type: [Number, String], default: '' },
    money_amount_words: { type: String, default: '' },
    manager_comment: { type: String, default: '' },
    accountItems: { type: Array, default: () => [] },
    isRtl: { type: Boolean, default: true },
    loading: { type: Boolean, default: false },
  });

  const emit = defineEmits(['update:modelValue', 'save']);

  const model = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
  });

  const local = reactive({
    id: null,
    account_id: null,
    message_to_finance: '',
    money_amount_words: '',
    manager_comment: '',
  });

  function hydrateFromProps() {
    local.id = props.id ?? null;
    local.account_id = props.account_id ?? null;
    local.message_to_finance = props.message_to_finance ?? '';
    local.money_amount_words = props.money_amount_words ?? '';
    local.manager_comment = props.manager_comment ?? '';
  }

  watch(
    () => model.value,
    (open) => {
      if (open) hydrateFromProps();
    }
  );

  function close() {
    model.value = false;
  }

  function save() {
    emit('save', {
      id: local.id,
      account_id: local.account_id,
      message_to_finance: local.message_to_finance,
      money_amount_words: local.money_amount_words,
      manager_comment: local.manager_comment,
    });
  }
</script>

<style scoped>
  .bg-gradient {
    background: linear-gradient(135deg, #3949ab 0%, #5c6bc0 100%);
  }

  .btn-close {
    transition: transform 0.2s ease;
  }

  .btn-close:hover {
    transform: rotate(90deg);
    background-color: rgba(255, 255, 255, 0.1);
  }

  :deep(.v-field--variant-outlined) {
    transition: all 0.2s ease;
  }

  :deep(.v-field--variant-outlined:hover) {
    border-color: #5c6bc0;
  }

  :deep(.v-field--focused) {
    border-color: #3949ab !important;
  }

  .dialog-bottom-transition-enter-active,
  .dialog-bottom-transition-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
  }

  .dialog-bottom-transition-enter-from,
  .dialog-bottom-transition-leave-to {
    transform: translateY(20px);
    opacity: 0;
  }
</style>
