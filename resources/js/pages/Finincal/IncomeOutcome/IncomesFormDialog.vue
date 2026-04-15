<template>
  <v-dialog v-model="model" max-width="820" scrollable>
    <v-card rounded="xl" class="overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-success py-6 px-4">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-avatar color="white" size="48" class="me-3">
              <v-icon icon="mdi-cash-plus" color="success" size="28" />
            </v-avatar>
            <div>
              <h2 class="text-h5 font-weight-bold text-white mb-1">
                {{ isEdit ? t('incomes.dialog.editTitle') : t('incomes.dialog.createTitle') }}
              </h2>
              <p class="text-white text-caption opacity-75 mb-0">
                {{ isEdit ? t('incomes.dialog.editSubtitle') : t('incomes.dialog.createSubtitle') }}
              </p>
            </div>
          </div>

          <v-btn icon="mdi-close" variant="text" color="white" size="small" @click="close" />
        </div>
      </div>

      <v-divider />

      <v-card-text class="pa-4">
        <v-form ref="formRef" @submit.prevent="onSubmit">
          <div class="d-grid gap-3">
            <!-- Account -->
            <v-autocomplete
              v-model="draft.account_id"
              :items="accountOptions"
              item-title="name"
              item-value="id"
              :label="t('incomes.form.account')"
              density="compact"
              variant="outlined"
              prepend-inner-icon="mdi-bank"
              :rules="[rules.required]"
              :error="hasError('account_id')"
              :error-messages="getErrorMessages('account_id')"
            />

            <!-- Amount -->
            <v-text-field
              v-model="draft.amount"
              :label="t('incomes.form.amount')"
              density="compact"
              variant="outlined"
              prepend-inner-icon="mdi-currency-usd"
              :rules="[rules.requiredNumber]"
              :error="hasError('amount')"
              :error-messages="getErrorMessages('amount')"
            />
            <v-text-field
              v-model="draft.amount_by_word"
              :label="t('incomes.form.amount_by_word')"
              density="compact"
              variant="outlined"
              prepend-inner-icon="mdi-currency-usd"
              :rules="[rules.requiredNumber]"
            />

            <!-- Received at with DateInput -->
            <DateInput
              v-model="draft.received_at"
              :label="t('incomes.form.receivedAt')"
              density="compact"
              :rules="[rules.required]"
              :error="hasError('received_at')"
              :error-messages="getErrorMessages('received_at')"
            />

            <!-- Note -->
            <v-textarea
              v-model="draft.note"
              :label="t('incomes.form.note')"
              density="compact"
              variant="outlined"
              rows="3"
              auto-grow
              prepend-inner-icon="mdi-note-text"
              :error="hasError('note')"
              :error-messages="getErrorMessages('note')"
            />
          </div>

          <div v-if="backendErrors.general?.length" class="mt-3">
            <v-alert type="error" variant="tonal" density="compact">
              <div v-for="(m, i) in backendErrors.general" :key="i">{{ m }}</div>
            </v-alert>
          </div>

          <div class="d-flex justify-end gap-2 mt-4">
            <v-btn variant="text" @click="close">
              {{ t('common.cancel') }}
            </v-btn>

            <v-btn color="success" :loading="saving" type="submit">
              {{ isEdit ? t('common.saveChanges') : t('incomes.actions.create') }}
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { computed, reactive, ref, watch } from 'vue';
  import { useFinanceRepository } from '@/stores/FinanceRepository';
  import DateInput from '@/components/DateInput.vue'; // Import DateInput component
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const rtl = ref(true);
  const props = defineProps({
    modelValue: { type: Boolean, default: false },
    editId: { type: [Number, String, null], default: null },
  });

  const emit = defineEmits(['update:modelValue', 'saved']);

  const repo = useFinanceRepository();

  const model = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
  });

  const isEdit = computed(() => !!props.editId);

  const formRef = ref(null);
  const saving = ref(false);

  const draft = reactive({
    account_id: null,
    amount: '',
    amount_by_word: '',
    received_at: '',
    note: '',
  });

  const backendErrors = reactive({});

  const rules = {
    required: (v) => !!v || v === 0 || 'ضروري',
    requiredNumber: (v) => {
      if (v === null || v === undefined || v === '') return 'ضروري';
      const n = Number(v);
      return Number.isFinite(n) ? true : 'عدد معتبر وارد کنید';
    },
  };

  const accountOptions = computed(() => repo.accounts || []);

  function clearErrors() {
    Object.keys(backendErrors).forEach((k) => delete backendErrors[k]);
  }

  function hasError(key) {
    return Array.isArray(backendErrors[key]) && backendErrors[key].length > 0;
  }
  function getErrorMessages(key) {
    return backendErrors[key] || [];
  }

  function resetDraft() {
    draft.account_id = null;
    draft.amount = '';
    draft.amount_by_word = '';
    draft.received_at = '';
    draft.note = '';
  }

  async function ensureAccountsLoaded() {
    // Load accounts once if empty (for autocomplete)
    if (Array.isArray(repo.accounts) && repo.accounts.length) return;

    await repo.FetchAccountsData({
      page: 1,
      perPage: 2000, // enough for dropdown
      search: '',
    });
  }

  async function loadForEdit() {
    if (!props.editId) return;
    clearErrors();
    const item = await repo.FetchIncomeData(props.editId);

    draft.account_id = item?.account_id ?? null;
    draft.amount = item?.amount ?? '';
    draft.amount_by_word = item?.amount_by_word ?? '';
    // DateInput expects date string, no need to slice
    draft.received_at = item?.received_at ?? '';
    draft.note = item?.note ?? '';
  }

  watch(
    () => model.value,
    async (open) => {
      if (!open) return;

      resetDraft();
      clearErrors();
      await ensureAccountsLoaded();

      if (props.editId) await loadForEdit();
    }
  );

  function close() {
    model.value = false;
  }

  async function onSubmit() {
    clearErrors();

    const res = await formRef.value?.validate();
    if (!res?.valid) return;

    saving.value = true;
    try {
      const payload = {
        account_id: draft.account_id,
        amount: draft.amount,
        amount_by_word: draft.amount_by_word,
        received_at: draft.received_at,
        note: draft.note,
      };

      if (isEdit.value) {
        await repo.UpdateIncome(props.editId, payload);
      } else {
        await repo.CreateIncome(payload);
      }

      emit('saved');
      close();
    } catch (e) {
      const errors = e?.response?.data?.errors;
      if (errors && typeof errors === 'object') {
        Object.assign(backendErrors, errors);
      } else {
        backendErrors.general = ['خطا رخ داد. دوباره تلاش کنید.'];
      }
    } finally {
      saving.value = false;
    }
  }
</script>

<style scoped>
  .bg-gradient-success {
    background: linear-gradient(135deg, rgba(46, 125, 50, 1), rgba(76, 175, 80, 0.9));
  }
  .d-grid {
    display: grid;
  }
  .gap-3 {
    gap: 12px;
  }
  .gap-2 {
    gap: 8px;
  }
</style>
