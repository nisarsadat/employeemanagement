<template>
  <v-dialog
    v-model="internalOpen"
    max-width="720"
    class="form-wrapper"
    transition="dialog-top-transition"
    @update:modelValue="onModel"
  >
    <v-card :dir="isRtl ? 'rtl' : 'ltr'" rounded="xl">
      <v-card-title class="text-h6">
        {{ editMode ? t('د عاید سمون') : t('نوی عاید ثبتول') }}
      </v-card-title>
      <v-divider />
      <v-card-text>
        <v-form ref="formRef" @submit.prevent="emitSave">
          <v-row dense>
            <!-- Account -->
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
                :return-object="false"
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

            <!-- Date -->
            <v-col cols="12" md="6">
              <DateInput v-model="localDraft.date" :label="t('نیټه')" />
            </v-col>

            <!-- Amount -->
            <v-col cols="12" md="12">
              <v-text-field
                v-model.number="localDraft.amount"
                :label="t('مبلغ')"
                type="number"
                variant="outlined"
                density="compact"
                :rules="[rules.required, rules.positive]"
                required
              />
            </v-col>

            <!-- Note -->
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
      default: () => ({ id: null, account_id: null, amount: 0, date: null, note: '' }),
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
    amount: 0,
    date: null,
    note: '',
  });

  function prime() {
    Object.assign(localDraft, {
      id: props.value?.id ?? null,
      account_id: props.value?.account_id ?? null,
      amount: props.value?.amount ?? 0,
      date: props.value?.date ?? null,
      note: props.value?.note ?? '',
    });
  }

  // Accounts list (shows current balance as subtitle)
  const accountItems = ref([]);
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
    loadingAccounts.value = false;
  }

  // Validation rules
  const rules = {
    required: (v) => !!v || v === 0 || t('اړین دی'),
    positive: (v) => Number(v) > 0 || t('شمېره باید له صفر څخه لوړه وي'),
  };

  // Normalize date to YYYY-MM-DD if DateInput returns an object
  function normalizeDate(val) {
    if (val == null) return null;
    if (typeof val === 'string') return val; // assumed ISO
    if (typeof val === 'object' && val.year && val.month && val.day) {
      const mm = String(val.month).padStart(2, '0');
      const dd = String(val.day).padStart(2, '0');
      return `${val.year}-${mm}-${dd}`;
    }
    return String(val);
  }

  // Emit payload (send both date & received_at for backend compatibility)
  async function emitSave() {
    const ok = await formRef.value?.validate();
    if (!ok?.valid) return;

    const accountIdNum = Number(localDraft.account_id);
    const isoDate = normalizeDate(localDraft.date);

    emit('save', {
      id: localDraft.id,
      account_id: accountIdNum,
      amount: Number(localDraft.amount ?? 0),
      date: isoDate, // keep 'date' for forward/backward compatibility
      received_at: isoDate, // satisfies backends still expecting received_at
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
