<template>
  <v-dialog
    v-model="internalOpen"
    max-width="880"
    class="form-wrapper"
    transition="dialog-top-transition"
    @update:modelValue="onModel"
  >
    <v-card :dir="isRtl ? 'rtl' : 'ltr'" rounded="xl">
      <v-card-title class="text-h6">
        {{ editMode ? t('د مصرف سمون') : t('نوی مصرف ثبتول') }}
      </v-card-title>
      <v-divider />
      <v-card-text>
        <v-form ref="formRef" @submit.prevent="emitSave">
          <v-row dense>
            <v-col cols="12" md="6">
              <v-select
                v-model="localDraft.account_id"
                :items="accountItems"
                item-title="title"
                item-value="id"
                :label="t('حساب')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :loading="loadingAccounts"
                required
              >
                <template #item="{ props, item }">
                  <v-list-item
                    v-bind="props"
                    :title="item.raw.title"
                    :subtitle="item.raw.subtitle"
                  />
                </template>
              </v-select>
            </v-col>

            <v-col cols="12" md="6">
              <DateInput v-model="localDraft.paid_at" :label="t('نیټه')" />
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="localDraft.payee_type"
                :items="payeeTypeItems"
                item-title="label"
                item-value="value"
                :label="t('چا ته پیسې ورکول کېږي؟')"
                variant="outlined"
                density="compact"
                clearable
              />
            </v-col>

            <v-col cols="12" md="6" v-if="localDraft.payee_type">
              <v-select
                v-model="localDraft.payee_id"
                :items="payeeOptions"
                item-title="title"
                item-value="id"
                :label="
                  localDraft.payee_type === 'office_employee'
                    ? t('د دفتر کارکوونکی')
                    : t('سرچینه / ریسورس')
                "
                variant="outlined"
                density="compact"
                :loading="loadingPayees"
                clearable
              >
                <template #item="{ props, item }">
                  <v-list-item
                    v-bind="props"
                    :title="item.raw.title"
                    :subtitle="item.raw.subtitle"
                  />
                </template>
              </v-select>
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model.number="localDraft.amount_price"
                :label="t('مبلغ')"
                type="number"
                variant="outlined"
                density="compact"
                :rules="[rules.required, rules.positive, rules.notExceedBalance]"
                required
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-text-field
                v-model.trim="localDraft.phone_number"
                :label="t('د تلیفون شمېره')"
                variant="outlined"
                density="compact"
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model.trim="localDraft.reason_expense"
                :label="t('د مصرف دلیل')"
                variant="outlined"
                density="compact"
              />
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model.trim="localDraft.note"
                :label="t('یادونه')"
                rows="3"
                variant="outlined"
                density="compact"
                auto-grow
              />
            </v-col>

            <v-col cols="12">
              <v-alert v-if="balanceWarning" type="warning" variant="tonal" class="mt-2">
                {{ t('خبرداری: د حساب موجودي له مبلغ څخه کمه ده.') }}
              </v-alert>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
      <v-divider />
      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="close">{{ t('لغوه') }}</v-btn>
        <v-btn color="primary" :loading="busy" @click="emitSave">
          {{ editMode ? t('اوسمهالول') : t('ثبتول') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { reactive, ref, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import axios from 'axios';
  import DateInput from '@/components/DateInput.vue';

  const props = defineProps({
    modelValue: { type: Boolean, default: false },
    editMode: { type: Boolean, default: false },
    value: {
      type: Object,
      default: () => ({
        id: null,
        account_id: null,
        paid_at: null,
        payee_type: null,
        payee_id: null,
        amount_price: 0,
        phone_number: '',
        reason_expense: '',
        note: '',
      }),
    },
    busy: { type: Boolean, default: false },
  });

  const emit = defineEmits(['update:modelValue', 'save']);

  const { t, locale } = useI18n();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  const internalOpen = ref(props.modelValue);
  watch(
    () => props.modelValue,
    (v) => {
      internalOpen.value = v;
      if (v) prime();
    }
  );
  function onModel(v) {
    emit('update:modelValue', v);
  }
  function close() {
    internalOpen.value = false;
    emit('update:modelValue', false);
  }

  const formRef = ref(null);
  const localDraft = reactive({
    id: null,
    account_id: null,
    paid_at: null,
    payee_type: null,
    payee_id: null,
    amount_price: 0,
    phone_number: '',
    reason_expense: '',
    note: '',
  });

  function prime() {
    Object.assign(localDraft, {
      id: props.value?.id ?? null,
      account_id: props.value?.account_id ?? null,
      paid_at: props.value?.paid_at ?? null,
      payee_type: props.value?.payee_type ?? null,
      payee_id: props.value?.payee_id ?? null,
      amount_price: props.value?.amount_price ?? 0,
      phone_number: props.value?.phone_number ?? '',
      reason_expense: props.value?.reason_expense ?? '',
      note: props.value?.note ?? '',
    });
    // preload payees if needed
    if (localDraft.payee_type) loadPayees(localDraft.payee_type);
  }

  // accounts with current balance
  const accountItems = ref([]);
  const accountMap = ref(new Map()); // id -> price
  const loadingAccounts = ref(false);
  async function loadAccounts() {
    loadingAccounts.value = true;
    const resp = await axios.get(`/accounts?perPage=1000`);
    const arr = resp?.data?.data ?? [];
    accountItems.value = arr.map((a) => ({
      id: a.id,
      title: a.name,
      subtitle: `${t('بیلانس')}: ${Number(a.price ?? 0).toLocaleString()}`,
    }));
    accountMap.value = new Map(arr.map((a) => [a.id, Number(a.price ?? 0)]));
    loadingAccounts.value = false;
  }

  // payee type + options
  const payeeTypeItems = [
    { label: 'د دفتر کارکوونکی', value: 'office_employee' },
    { label: 'سرچینه / ریسورس', value: 'resource' },
  ];

  const payeeOptions = ref([]);
  const loadingPayees = ref(false);
  async function loadPayees(type) {
    loadingPayees.value = true;
    const url =
      type === 'office_employee' ? '/office-employees?perPage=1000' : '/resources?perPage=1000';
    const resp = await axios.get(url);
    const list = resp?.data?.data ?? [];
    payeeOptions.value = list.map((p) => ({
      id: p.id,
      title: p.name ?? p.full_name ?? '',
      subtitle: p.code ?? p.code_number ? `${t('کوډ')}: ${p.code ?? p.code_number}` : undefined,
    }));
    loadingPayees.value = false;
  }
  watch(
    () => localDraft.payee_type,
    (v) => {
      localDraft.payee_id = null;
      if (v) loadPayees(v);
    }
  );

  // validation
  const rules = {
    required: (v) => !!v || v === 0 || t('اړین دی'),
    positive: (v) => Number(v) > 0 || t('شمېره باید له صفر څخه لوړه وي'),
    notExceedBalance: (v) => {
      const amount = Number(v ?? 0);
      const bal = accountMap.value.get(localDraft.account_id) ?? 0;
      return amount <= bal || t('مبلغ باید د حساب له بیلانس څخه زیات نه وي');
    },
  };

  const balanceWarning = computed(() => {
    const amount = Number(localDraft.amount_price ?? 0);
    const bal = accountMap.value.get(localDraft.account_id) ?? 0;
    return !!localDraft.account_id && amount > bal;
  });

  async function emitSave() {
    const ok = await formRef.value?.validate();
    if (!ok?.valid) return;

    emit('save', {
      id: localDraft.id,
      account_id: localDraft.account_id,
      paid_at: localDraft.paid_at,
      payee_type: localDraft.payee_type || null,
      payee_id: localDraft.payee_id || null,
      amount_price: Number(localDraft.amount_price ?? 0),
      phone_number: localDraft.phone_number || '',
      reason_expense: localDraft.reason_expense || '',
      note: localDraft.note || '',
    });
  }

  watch(internalOpen, (v) => {
    if (v) loadAccounts();
  });
</script>
<style scoped>
  .form-wrapper :deep(.v-field__outline) {
    --v-field-border-opacity: 0.08;
  }

  .form-wrapper :deep(.v-input__control) {
    min-height: 44px !important;
  }
</style>
