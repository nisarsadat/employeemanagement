<template>
  <v-dialog
    v-model="internalOpen"
    max-width="820"
    transition="dialog-top-transition"
    @update:modelValue="onModel"
  >
    <v-card :dir="isRtl ? 'rtl' : 'ltr'" rounded="xl">
      <v-card-title class="text-h6">{{
        editMode ? t('د تيلو مصرف سمون') : t('نوی د تيلو مصرف')
      }}</v-card-title>

      <v-divider />

      <v-card-text>
        <v-form ref="formRef" @submit.prevent="emitSave">
          <v-row dense>
            <!-- ✅ Oil Account -->
            <v-col cols="12" md="6">
              <v-select
                v-model="localDraft.oil_account_id"
                :items="accountItems"
                item-title="title"
                item-value="id"
                :label="t('د تيلو نوعیت')"
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

            <!-- ✅ Employee -->
            <v-col cols="12" md="6">
              <v-autocomplete
                v-model="localDraft.employee_id"
                :items="employeeItems"
                item-title="title"
                item-value="id"
                :label="t('کارکوونکی')"
                variant="outlined"
                density="compact"
                clearable
                :loading="loadingEmployees"
              >
                <template #item="{ props, item }">
                  <v-list-item
                    v-bind="props"
                    :title="item.raw.title"
                    :subtitle="item.raw.subtitle"
                  />
                </template>
              </v-autocomplete>
            </v-col>

            <!-- ✅ Name -->
            <v-col cols="12" md="6">
              <v-text-field
                v-model.trim="localDraft.name"
                :label="t('نوم / عنوان')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                required
              />
            </v-col>

            <!-- ✅ Father Name -->
            <v-col cols="12" md="6">
              <v-text-field
                v-model.trim="localDraft.father_name"
                :label="t('د پلار نوم')"
                variant="outlined"
                density="compact"
              />
            </v-col>

            <!-- ✅ Payer -->
            <v-col cols="12" md="6">
              <v-text-field
                v-model.trim="localDraft.payer"
                :label="t('ورکوونکی')"
                variant="outlined"
                density="compact"
              />
            </v-col>

            <!-- ✅ Date -->
            <v-col cols="12" md="6">
              <DateInput v-model="localDraft.date" :label="t('نیټه')" />
            </v-col>

            <!-- ✅ Liter in number (must not exceed account quantity) -->
            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="localDraft.liter_in_number"
                :label="t('لیټر (عدد)')"
                type="number"
                variant="outlined"
                density="compact"
                :hint="selectedAccountHint"
                persistent-hint
                :rules="[rules.required, rules.positive, rules.maxByAccount]"
                required
              />
            </v-col>

            <!-- ✅ Liter in words -->
            <v-col cols="12" md="6">
              <v-text-field
                v-model.trim="localDraft.liter_in_words"
                :label="t('لیټر (په تورو)')"
                variant="outlined"
                density="compact"
              />
            </v-col>

            <!-- ✅ Tafseel -->
            <v-col cols="12">
              <v-textarea
                v-model.trim="localDraft.tafseel"
                :label="t('تفصیل')"
                rows="2"
                variant="outlined"
                density="compact"
                auto-grow
              />
            </v-col>

            <!-- ✅ Note -->
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
        <v-btn color="primary" :loading="busy" @click="emitSave">{{
          editMode ? t('اوسمهالول') : t('ثبتول')
        }}</v-btn>
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
      oil_account_id: null,
      employee_id: null,
      name: '',
      father_name: '',
      date: null,
      liter_in_number: 0,
      liter_in_words: '',
      tafseel: '',
      payer: '',
      note: '',
    }),
  },
  busy: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'save']);

const { t, locale } = useI18n();
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

const internalOpen = ref(props.modelValue);
const formRef = ref(null);

const localDraft = reactive({
  id: null,
  oil_account_id: null,
  employee_id: null,

  name: '',
  father_name: '',
  date: null,

  liter_in_number: 0,
  liter_in_words: '',

  tafseel: '',
  payer: '',
  note: '',
});

// -------- dialog open/close ----------
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

function prime() {
  Object.assign(localDraft, {
    id: props.value?.id ?? null,
    oil_account_id: props.value?.oil_account_id ?? null,
    employee_id: props.value?.employee_id ?? null,

    name: props.value?.name ?? '',
    father_name: props.value?.father_name ?? '',
    date: props.value?.date ?? null,

    liter_in_number: props.value?.liter_in_number ?? 0,
    liter_in_words: props.value?.liter_in_words ?? '',

    tafseel: props.value?.tafseel ?? '',
    payer: props.value?.payer ?? '',
    note: props.value?.note ?? '',
  });
}

function normalizeDate(val) {
  if (val == null) return null;
  if (typeof val === 'string') return val;
  if (typeof val === 'object' && val.year && val.month && val.day) {
    const mm = String(val.month).padStart(2, '0');
    const dd = String(val.day).padStart(2, '0');
    return `${val.year}-${mm}-${dd}`;
  }
  return String(val);
}

// -------- accounts list (with quantity for validation) ----------
const accountItems = ref([]);
const loadingAccounts = ref(false);

// keep a map: accountId -> quantity
const accountQtyById = ref({});

async function loadAccounts() {
  loadingAccounts.value = true;

  const resp = await axios.get(`/oil-accounts?perPage=1000`);
  const arr = resp?.data?.data ?? [];

  const qtyMap = {};
  accountItems.value = arr.map((a) => {
    const q = Number(a.quantity ?? 0);
    qtyMap[a.id] = q;

    return {
      id: a.id,
      title: a.name,
      subtitle: `${t('موجوده مقدار')}: ${q.toLocaleString(undefined, {
        maximumFractionDigits: 3,
      })} L`,
    };
  });

  accountQtyById.value = qtyMap;
  loadingAccounts.value = false;
}

// hint for currently selected account
const selectedAccountQty = computed(() => {
  const id = Number(localDraft.oil_account_id || 0);
  return Number(accountQtyById.value?.[id] ?? 0);
});

const selectedAccountHint = computed(() => {
  const q = selectedAccountQty.value;
  if (!localDraft.oil_account_id) return '';
  return `${t('اعظمي مقدار')}: ${q.toLocaleString(undefined, {
    maximumFractionDigits: 3,
  })} L`;
});

// -------- employees list ----------
const employeeItems = ref([]);
const loadingEmployees = ref(false);

async function loadEmployees() {
  loadingEmployees.value = true;
  const resp = await axios.get(`/office-employees?perPage=1000`);
  const arr = resp?.data?.data ?? [];

  employeeItems.value = arr.map((e) => ({
    id: e.id,
    title: e.name,
    subtitle: `${e.code_number ?? '—'} • ${e.phone_number ?? '—'}`,
  }));

  loadingEmployees.value = false;
}

// -------- validation rules ----------
const rules = {
  required: (v) => !!v || v === 0 || t('اړین دی'),
  positive: (v) => Number(v) > 0 || t('شمېره باید له صفر څخه لوړه وي'),

  // ✅ IMPORTANT: liters must not exceed selected account quantity
  maxByAccount: (v) => {
    const qty = selectedAccountQty.value;
    const n = Number(v ?? 0);

    // if no account selected yet, don't block typing
    if (!localDraft.oil_account_id) return true;

    return n <= qty || `${t('مقدار باید له موجوده مقدار څخه زیات نه وي')} (${qty} L)`;
  },
};

// if account changes and current liters is too high, clamp it
watch(
  () => localDraft.oil_account_id,
  () => {
    const qty = selectedAccountQty.value;
    if (Number(localDraft.liter_in_number ?? 0) > qty) {
      localDraft.liter_in_number = qty;
    }
  }
);

// -------- save ----------
async function emitSave() {
  const ok = await formRef.value?.validate();
  if (!ok?.valid) return;

  emit('save', {
    id: localDraft.id,

    oil_account_id: Number(localDraft.oil_account_id),
    employee_id: localDraft.employee_id ? Number(localDraft.employee_id) : null,

    name: localDraft.name || '',
    father_name: localDraft.father_name || '',
    payer: localDraft.payer || '',

    liter_in_number: Number(localDraft.liter_in_number ?? 0),
    liter_in_words: localDraft.liter_in_words || '',

    date: normalizeDate(localDraft.date),

    tafseel: localDraft.tafseel || '',
    note: localDraft.note || '',
  });
}

// load dropdowns when dialog opens
watch(internalOpen, (v) => {
  if (v) {
    loadAccounts();
    loadEmployees();
  }
});
</script>
