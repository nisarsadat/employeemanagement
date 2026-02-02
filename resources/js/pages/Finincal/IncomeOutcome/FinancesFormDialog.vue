<!-- src/pages/Finances/FinancesFormDialog.vue -->
<template>
  <v-dialog v-model="model" max-width="980" scrollable>
    <v-card rounded="xl" class="dialog-card overflow-hidden">
      <!-- Header -->
      <div class="dialog-header">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center gap-3">
            <div class="icon-badge">
              <v-icon icon="mdi-cash-minus" size="22" />
            </div>

            <div>
              <div class="text-h6 font-weight-bold mb-0">
                {{ isEdit ? t('finances.dialog.titleEdit') : t('finances.dialog.titleCreate') }}
              </div>
              <div class="text-caption opacity-90">
                {{
                  isEdit ? t('finances.dialog.subtitleEdit') : t('finances.dialog.subtitleCreate')
                }}
              </div>
            </div>
          </div>

          <v-btn icon="mdi-close" variant="text" size="small" class="rounded-lg" @click="close" />
        </div>
      </div>

      <v-divider />

      <v-card-text class="pa-5">
        <v-form ref="formRef" @submit.prevent="onSubmit">
          <!-- Section: Main -->
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-information-outline" size="18" class="me-2" />
              {{ t('finances.form.sectionMain') }}
            </div>

            <div class="grid-2">
              <!-- Account -->
              <v-autocomplete
                v-model="draft.account_id"
                :items="accountOptions"
                :item-title="accountTitle"
                item-value="id"
                :label="t('finances.form.accountRequired')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-bank-outline"
                :loading="repo.loading"
                :rules="[rules.required]"
                :error="hasError('account_id')"
                :error-messages="getErrorMessages('account_id')"
                :hint="selectedAccountBalanceHint"
                persistent-hint
                @update:model-value="onAccountChange"
                class="field"
              />

              <!-- Date -->
              <DateInput
                v-model="draft.date"
                :label="t('finances.form.dateRequired')"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('date')"
                :error-messages="getErrorMessages('date')"
                class="field"
              />

              <!-- Amount Number -->
              <v-text-field
                v-model="draft.money_amount_number"
                :label="t('finances.form.amountNumberRequired')"
                density="compact"
                variant="outlined"
                type="number"
                prepend-inner-icon="mdi-currency-usd"
                :rules="[rules.requiredNumber, rules.positiveAmount, rules.maxAmount]"
                :error="hasError('money_amount_number')"
                :error-messages="getErrorMessages('money_amount_number')"
                :hint="amountValidationHint"
                persistent-hint
                @blur="validateAmount"
                class="field"
                :disabled="amountLocked"
                :readonly="amountLocked"
              />

              <!-- Amount Words -->
              <v-text-field
                v-model="draft.money_amount_words"
                :label="t('finances.form.amountWords')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-text-box-outline"
                :error="hasError('money_amount_words')"
                :error-messages="getErrorMessages('money_amount_words')"
                class="field"
                :disabled="wordsLocked"
                :readonly="wordsLocked"
              />
            </div>
          </div>

          <v-divider class="my-4" />

          <!-- Section: People & Result -->
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-account-group-outline" size="18" class="me-2" />
              {{ t('finances.form.sectionExtra') }}
            </div>

            <div class="grid-2">
              <v-text-field
                v-model="draft.employee_phone"
                :label="t('finances.form.employeePhone')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-phone-outline"
                :error="hasError('employee_phone')"
                :error-messages="getErrorMessages('employee_phone')"
                class="field"
                @update:model-value="onEmployeePhoneManualChange"
              />

              <v-text-field
                v-model="draft.expense_result"
                :label="t('finances.form.expenseResult')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-clipboard-check-outline"
                :error="hasError('expense_result')"
                :error-messages="getErrorMessages('expense_result')"
                class="field"
              />

              <v-text-field
                v-model="draft.payer"
                :label="t('finances.form.payer')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-account-arrow-right-outline"
                :error="hasError('payer')"
                :error-messages="getErrorMessages('payer')"
                class="field"
              />

              <v-text-field
                v-model="draft.receiver"
                :label="t('finances.form.receiver')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-account-arrow-left-outline"
                :error="hasError('receiver')"
                :error-messages="getErrorMessages('receiver')"
                class="field"
              />
            </div>
          </div>

          <v-divider class="my-4" />

          <!-- Section: Links -->
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-link-variant" size="18" class="me-2" />
              {{ t('finances.form.sectionLinks') }}
            </div>

            <div class="grid-2">
              <v-autocomplete
                v-model="draft.office_employee_id"
                :items="officeEmployeeOptions"
                :item-title="officeEmployeeTitle"
                item-value="id"
                :label="t('finances.form.employee')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-account-outline"
                clearable
                :loading="repo.officeEmployeesLoading"
                :error="hasError('office_employee_id')"
                :error-messages="getErrorMessages('office_employee_id')"
                class="field"
                @update:model-value="onEmployeeManualChange"
              />

              <v-autocomplete
                v-model="draft.resource_id"
                :items="resourceOptions"
                :item-title="resourceTitle"
                item-value="id"
                :label="t('finances.form.resource')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-link-box-outline"
                clearable
                :loading="repo.resourcesLoading"
                :error="hasError('resource_id')"
                :error-messages="getErrorMessages('resource_id')"
                class="field"
              />

              <v-autocomplete
                v-model="draft.event_id"
                :items="eventOptions"
                :item-title="eventTitle"
                item-value="id"
                :label="t('واقعات')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-calendar-alert"
                clearable
                :loading="eventsLoadingLocal || repo.eventsLoading"
                :error="hasError('event_id')"
                :error-messages="getErrorMessages('event_id')"
                class="field"
                @update:search="onEventSearch"
              />

              <v-autocomplete
                v-model="draft.information_id"
                :items="informationOptions"
                :item-title="informationTitle"
                item-value="id"
                :label="t('اطلاعات')"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-information"
                clearable
                :loading="informationsLoadingLocal || repo.informationsLoading"
                :error="hasError('information_id')"
                :error-messages="getErrorMessages('information_id')"
                class="field"
                @update:search="onInformationSearch"
              />
            </div>

            <!-- tiny hint when locked -->
            <div v-if="amountLocked || wordsLocked" class="mt-2 text-caption opacity-75">
              {{ t('finances.hints.amountLockedFromMessage') }}
            </div>
          </div>

          <v-divider class="my-4" />

          <!-- Notes -->
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-note-text-outline" size="18" class="me-2" />
              {{ t('finances.form.sectionNotes') }}
            </div>

            <v-textarea
              v-model="draft.notes"
              :label="t('finances.form.notes')"
              density="compact"
              variant="outlined"
              rows="3"
              auto-grow
              prepend-inner-icon="mdi-note-text-outline"
              :error="hasError('notes')"
              :error-messages="getErrorMessages('notes')"
              class="field"
            />
          </div>

          <!-- Actions -->
          <div class="actions mt-6">
            <v-btn variant="text" class="rounded-lg px-4" color="grey-darken-1" @click="close">
              {{ t('common.cancel') }}
            </v-btn>

            <v-btn
              color="error"
              :loading="saving"
              :disabled="disableSubmit"
              type="submit"
              class="rounded-lg px-6"
              prepend-icon="mdi-check"
              elevation="1"
            >
              {{ isEdit ? t('common.saveChanges') : t('finances.actions.createExpense') }}
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { computed, reactive, ref, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useFinanceRepository } from '@/stores/financeRepository';
  import DateInput from '@/components/DateInput.vue';
  import { useDatabaseRepository } from '@/stores/DatabaseRepository';

  const dbRepo = useDatabaseRepository();
  const { t } = useI18n();

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

  const originalAccountId = ref(null);
  const originalAmount = ref(0);

  // ✅ Lock flags (freeze/disabled)
  const amountLocked = ref(false);
  const wordsLocked = ref(false);

  const draft = reactive({
    account_id: null,
    money_amount_number: '',
    money_amount_words: '',
    date: '',
    employee_phone: '',
    expense_result: '',
    money_distributer: '',
    payer: '',
    receiver: '',
    account_type: '',
    notes: '',

    office_employee_id: null,
    resource_id: null,
    event_id: null,
    information_id: null,
  });

  const backendErrors = reactive({});

  function clearErrors() {
    Object.keys(backendErrors).forEach((k) => delete backendErrors[k]);
  }
  function hasError(key) {
    return Array.isArray(backendErrors[key]) && backendErrors[key].length > 0;
  }
  function getErrorMessages(key) {
    return backendErrors[key] || [];
  }

  function toNumberSafe(v) {
    if (v === null || v === undefined || v === '') return NaN;
    const n = Number(v);
    return Number.isFinite(n) ? n : NaN;
  }

  function formatAmount(amount) {
    const n = Number(amount);
    if (!Number.isFinite(n)) return '0';
    return new Intl.NumberFormat('fa-IR').format(n);
  }

  // ✅ parse number from message_to_finance safely
  function parseAmountFromMessage(msg) {
    const s = String(msg ?? '').trim();
    if (!s) return '';
    // find first chunk like 12,000 or 12000 or 12.5
    const m = s.match(/(\d[\d,]*\.?\d*)/);
    if (!m) return '';
    const cleaned = String(m[1]).replace(/,/g, '');
    const n = Number(cleaned);
    if (!Number.isFinite(n)) return '';
    return String(n);
  }

  function applyLockedAmountsFromLinkedRow(row) {
    if (!row) return;

    // ✅ amount number from message_to_finance
    const amountFromMsg = parseAmountFromMessage(row.message_to_finance);
    if (amountFromMsg !== '') {
      draft.money_amount_number = amountFromMsg;
      amountLocked.value = true;
    }

    // ✅ words from money_amount_words
    const words = String(row.money_amount_words ?? '').trim();
    if (words) {
      draft.money_amount_words = words;
      wordsLocked.value = true;
    }
  }

  // ✅ beautify expense_result ONLY when it's empty (doesn't overwrite manual)
  function setExpenseResultLabelFromLink() {
    const eid = draft.event_id;
    const iid = draft.information_id;

    if (String(draft.expense_result ?? '').trim() !== '') return;

    if (eid) {
      draft.expense_result = `واقعه ${eid}`;
      return;
    }
    if (iid) {
      draft.expense_result = `اطلاع ${iid}`;
      return;
    }
  }

  const rules = {
    required: (v) => !!v || v === 0 || t('validation.required'),
    requiredNumber: (v) => {
      if (v === null || v === undefined || v === '') return t('validation.required');
      const n = toNumberSafe(v);
      return Number.isFinite(n) ? true : t('validation.number');
    },
    positiveAmount: (v) => {
      if (v === null || v === undefined || v === '') return true;
      const n = toNumberSafe(v);
      if (!Number.isFinite(n)) return t('validation.number');
      if (n <= 0) return t('finances.validation.amountMustBeGreaterThanZero');
      return true;
    },
    maxAmount: (v) => {
      if (v === null || v === undefined || v === '') return true;
      const amount = toNumberSafe(v);
      const available = effectiveAvailableBalance.value;
      if (!Number.isFinite(amount)) return true;
      if (available !== null && amount > available) {
        return t('finances.validation.amountCannotBeGreaterThan', {
          balance: formatAmount(available),
        });
      }
      return true;
    },
  };

  const accountOptions = computed(() => repo.accounts || []);
  const officeEmployeeOptions = computed(() => repo.officeEmployees || []);
  const resourceOptions = computed(() => repo.resources || []);

  const selectedEventsById = reactive({});
  const selectedInfosById = reactive({});

  const eventOptions = computed(() => {
    const base = Array.isArray(repo.events) ? [...repo.events] : [];
    const id = draft.event_id;
    if (id !== null && id !== undefined && id !== '') {
      const exists = base.some((x) => String(x?.id) === String(id));
      if (!exists && selectedEventsById[String(id)]) base.unshift(selectedEventsById[String(id)]);
    }
    return base;
  });

  const informationOptions = computed(() => {
    const base = Array.isArray(repo.informations) ? [...repo.informations] : [];
    const id = draft.information_id;
    if (id !== null && id !== undefined && id !== '') {
      const exists = base.some((x) => String(x?.id) === String(id));
      if (!exists && selectedInfosById[String(id)]) base.unshift(selectedInfosById[String(id)]);
    }
    return base;
  });

  const accountTitle = (item) => {
    if (!item) return '';
    const raw = item.price ?? item.balance ?? item.amount ?? 0;
    return `${item.name} — ${formatAmount(Number(raw))}`;
  };

  const officeEmployeeTitle = (item) => {
    if (!item) return '';
    const code = item.code_number ?? item.code ?? item.codeNumber ?? '';
    return code ? `${code} — ${item.name}` : item.name;
  };

  const resourceTitle = (item) => {
    if (!item) return '';
    const code = item.code_number ?? item.code ?? item.codeNumber ?? '';
    return code ? `${code} — ${item.name}` : item.name;
  };

  const eventTitle = (item) => {
    if (!item) return '';
    const type = item.event_type ?? '';
    const spec = item.event_specific_type ?? '';
    const res = [type, spec].filter(Boolean).join(' / ');
    return res ? `${res} — #${item.id}` : `#${item.id}`;
  };

  const informationTitle = (item) => {
    if (!item) return '';
    const nm = item.name ?? item.nick_name ?? '';
    return nm ? `${nm} — #${item.id}` : `#${item.id}`;
  };

  const selectedAccount = computed(() =>
    accountOptions.value.find((acc) => acc.id === draft.account_id)
  );

  const selectedAccountBalance = computed(() => {
    if (!selectedAccount.value) return null;
    const raw =
      selectedAccount.value.price ??
      selectedAccount.value.balance ??
      selectedAccount.value.amount ??
      0;
    const n = Number(raw);
    return Number.isFinite(n) ? n : 0;
  });

  const selectedAccountBalanceHint = computed(() => {
    if (!selectedAccount.value) return t('finances.hints.selectAccount');
    return t('finances.hints.accountBalance', {
      balance: formatAmount(selectedAccountBalance.value),
    });
  });

  const effectiveAvailableBalance = computed(() => {
    const bal = selectedAccountBalance.value;
    if (bal === null) return null;

    if (!isEdit.value) return bal;

    if (draft.account_id && draft.account_id === originalAccountId.value) {
      return bal + (Number(originalAmount.value) || 0);
    }
    return bal;
  });

  const showBalanceWarning = computed(() => {
    const amount = toNumberSafe(draft.money_amount_number);
    if (!Number.isFinite(amount)) return false;

    const available = effectiveAvailableBalance.value;
    if (available === null) return false;

    return amount > available;
  });

  const showInvalidAmount = computed(() => {
    const amount = toNumberSafe(draft.money_amount_number);
    if (!Number.isFinite(amount)) return false;
    return amount <= 0;
  });

  const amountValidationHint = computed(() => {
    const amount = toNumberSafe(draft.money_amount_number);
    if (!Number.isFinite(amount)) return '';

    const available = effectiveAvailableBalance.value;
    if (available === null) return '';

    if (amount <= 0) return t('finances.hints.amountGreaterThanZero');
    if (amount > available)
      return t('finances.hints.amountMoreThanBalance', { balance: formatAmount(available) });

    const remaining = available - amount;
    return t('finances.hints.payableRemaining', { remaining: formatAmount(remaining) });
  });

  const disableSubmit = computed(
    () => saving.value || showBalanceWarning.value || showInvalidAmount.value
  );

  function resetDraft() {
    draft.account_id = null;
    draft.money_amount_number = '';
    draft.money_amount_words = '';
    draft.date = '';
    draft.employee_phone = '';
    draft.expense_result = '';
    draft.money_distributer = '';
    draft.payer = '';
    draft.receiver = '';
    draft.account_type = '';
    draft.notes = '';

    draft.office_employee_id = null;
    draft.resource_id = null;
    draft.event_id = null;
    draft.information_id = null;

    originalAccountId.value = null;
    originalAmount.value = 0;

    // ✅ reset locks every open
    amountLocked.value = false;
    wordsLocked.value = false;
  }

  async function ensureAccountsLoaded() {
    if (Array.isArray(repo.accounts) && repo.accounts.length) return;
    await repo.FetchAccountsData({ page: 1, perPage: 2000, search: '' });
  }
  async function ensureOfficeEmployeesLoaded() {
    if (Array.isArray(repo.officeEmployees) && repo.officeEmployees.length) return;
    await repo.FetchOfficeEmployeesOptions({ search: '' });
  }
  async function ensureResourcesLoaded() {
    if (Array.isArray(repo.resources) && repo.resources.length) return;
    await repo.FetchResourcesOptions({ search: '' });
  }

  async function ensureEventsLoaded() {
    if (Array.isArray(repo.events) && repo.events.length) return;

    const fns = ['FetchEventsOptions', 'FetchEventsData', 'FetchEventOptions', 'FetchEventData'];
    for (const fn of fns) {
      if (typeof repo[fn] === 'function') {
        try {
          await repo[fn]({ page: 1, perPage: 2000, search: '' });
        } catch {
          try {
            await repo[fn]({ search: '' });
          } catch {}
        }
        break;
      }
    }
  }

  async function ensureInformationsLoaded() {
    if (Array.isArray(repo.informations) && repo.informations.length) return;

    const fns = [
      'FetchInformationsOptions',
      'FetchInformationsData',
      'FetchInformationOptions',
      'FetchInformationData',
    ];
    for (const fn of fns) {
      if (typeof repo[fn] === 'function') {
        try {
          await repo[fn]({ page: 1, perPage: 2000, search: '' });
        } catch {
          try {
            await repo[fn]({ search: '' });
          } catch {}
        }
        break;
      }
    }
  }

  function onAccountChange() {
    formRef.value?.validate();
  }
  function validateAmount() {
    formRef.value?.validate();
  }
  function close() {
    model.value = false;
  }

  const eventsLoadingLocal = ref(false);
  const informationsLoadingLocal = ref(false);

  let eventSearchT = null;
  let infoSearchT = null;

  async function fetchEventsBySearch(search) {
    const s = String(search ?? '').trim();
    const fns = ['FetchEventsOptions', 'FetchEventsData', 'FetchEventOptions', 'FetchEventData'];
    for (const fn of fns) {
      if (typeof repo[fn] === 'function') {
        try {
          await repo[fn]({ page: 1, perPage: 2000, search: s });
        } catch {
          try {
            await repo[fn]({ search: s });
          } catch {}
        }
        break;
      }
    }
  }

  async function fetchInformationsBySearch(search) {
    const s = String(search ?? '').trim();
    const fns = [
      'FetchInformationsOptions',
      'FetchInformationsData',
      'FetchInformationOptions',
      'FetchInformationData',
    ];
    for (const fn of fns) {
      if (typeof repo[fn] === 'function') {
        try {
          await repo[fn]({ page: 1, perPage: 2000, search: s });
        } catch {
          try {
            await repo[fn]({ search: s });
          } catch {}
        }
        break;
      }
    }
  }

  function onEventSearch(v) {
    if (eventSearchT) clearTimeout(eventSearchT);
    eventSearchT = setTimeout(async () => {
      eventsLoadingLocal.value = true;
      try {
        await fetchEventsBySearch(v);
      } finally {
        eventsLoadingLocal.value = false;
      }
    }, 250);
  }

  function onInformationSearch(v) {
    if (infoSearchT) clearTimeout(infoSearchT);
    infoSearchT = setTimeout(async () => {
      informationsLoadingLocal.value = true;
      try {
        await fetchInformationsBySearch(v);
      } finally {
        informationsLoadingLocal.value = false;
      }
    }, 250);
  }

  const employeePhoneManuallyChanged = ref(false);
  function onEmployeeManualChange() {}
  function onEmployeePhoneManualChange() {
    employeePhoneManuallyChanged.value = true;
  }

  function sameId(a, b) {
    if (a === null || a === undefined || a === '') return false;
    if (b === null || b === undefined || b === '') return false;
    return String(a) === String(b);
  }
  function normalizeSelectId(options, id) {
    if (id === null || id === undefined || id === '') return null;
    const sample = options?.[0]?.id;
    if (sample === null || sample === undefined) return id;
    if (typeof sample === 'number') return Number(id);
    return String(id);
  }
  function setEmployeeAndPhone(empId) {
    if (!empId) return;
    draft.office_employee_id = normalizeSelectId(officeEmployeeOptions.value, empId);

    if (!employeePhoneManuallyChanged.value) {
      const emp = officeEmployeeOptions.value.find((x) => sameId(x.id, empId));
      const phone = emp?.phone_number ?? emp?.phone ?? emp?.mobile ?? '';
      draft.employee_phone = phone || '';
    }
  }

  watch(
    () => draft.office_employee_id,
    (eid) => {
      if (!eid) return;
      if (employeePhoneManuallyChanged.value) return;

      const emp = officeEmployeeOptions.value.find((x) => sameId(x.id, eid));
      const phone = emp?.phone_number ?? emp?.phone ?? emp?.mobile ?? '';
      draft.employee_phone = phone || '';
    }
  );

  // ✅ When selecting event manually -> auto fill + freeze amount/words
  // ✅ ALSO: event/info are OPTIONAL and mutually exclusive (event picked => clear info)
  watch(
    () => draft.event_id,
    (eid) => {
      if (!eid) {
        // unlock ONLY if no information selected too
        if (!draft.information_id) {
          amountLocked.value = false;
          wordsLocked.value = false;
        }
        return;
      }

      // ✅ event picked => clear information (optional)
      if (draft.information_id) draft.information_id = null;

      const ev = eventOptions.value.find((x) => sameId(x.id, eid));
      if (ev) selectedEventsById[String(eid)] = ev;

      const rid = ev?.resource_id ?? ev?.resource?.id ?? null;
      const empId = ev?.office_employee_id ?? ev?.office_employee?.id ?? null;

      if (rid) draft.resource_id = rid;
      setEmployeeAndPhone(empId);

      // ✅ autofill from Event (freeze)
      if (ev) applyLockedAmountsFromLinkedRow(ev);

      // ✅ optional label (doesn't overwrite manual)
      setExpenseResultLabelFromLink();
    }
  );

  // ✅ When selecting information manually -> auto fill + freeze amount/words
  // ✅ ALSO: event/info are OPTIONAL and mutually exclusive (info picked => clear event)
  watch(
    () => draft.information_id,
    (iid) => {
      if (!iid) {
        if (!draft.event_id) {
          amountLocked.value = false;
          wordsLocked.value = false;
        }
        return;
      }

      // ✅ info picked => clear event (optional)
      if (draft.event_id) draft.event_id = null;

      const inf = informationOptions.value.find((x) => sameId(x.id, iid));
      if (inf) selectedInfosById[String(iid)] = inf;

      const rid = inf?.resource_id ?? inf?.resource?.id ?? null;
      const empId = inf?.office_employee_id ?? inf?.office_employee?.id ?? null;

      if (rid) draft.resource_id = rid;
      setEmployeeAndPhone(empId);

      // ✅ autofill from Information (freeze)
      if (inf) applyLockedAmountsFromLinkedRow(inf);

      // ✅ optional label (doesn't overwrite manual)
      setExpenseResultLabelFromLink();
    }
  );

  watch(
    () => draft.resource_id,
    (rid) => {
      if (!rid) return;
      const r = resourceOptions.value.find((x) => sameId(x.id, rid));
      if (!r) return;

      const empId = r.office_employee_id ?? r.office_employee?.id ?? r.officeEmployee?.id ?? null;
      setEmployeeAndPhone(empId);
    }
  );

  // ✅ helper: find event/info row from dbRepo lists if repo options are missing
  function findFromDbRepo(type, id) {
    const sid = String(id);
    if (type === 'event') {
      const list = Array.isArray(dbRepo.Events)
        ? dbRepo.Events
        : Array.isArray(dbRepo.events)
        ? dbRepo.events
        : [];
      return list.find((x) => String(x?.id) === sid) || null;
    }
    if (type === 'info') {
      const list = Array.isArray(dbRepo.Informations)
        ? dbRepo.Informations
        : Array.isArray(dbRepo.informations)
        ? dbRepo.informations
        : [];
      return list.find((x) => String(x?.id) === sid) || null;
    }
    return null;
  }

  async function fetchSingleIfPossible(type, id) {
    // best effort (no breaking if missing)
    const fns =
      type === 'event'
        ? ['FetchEventData', 'FetchEvent', 'FetchEventById', 'GetEvent']
        : ['FetchInformationData', 'FetchInformation', 'FetchInformationById', 'GetInformation'];

    for (const fn of fns) {
      if (typeof repo[fn] === 'function') {
        try {
          const item = await repo[fn](id);
          if (item) return item;
        } catch {}
      }
      if (typeof dbRepo[fn] === 'function') {
        try {
          const item = await dbRepo[fn](id);
          if (item) return item;
        } catch {}
      }
    }
    return null;
  }

  // ✅ on open: read prefill, set links, then auto fill & freeze amounts
  watch(
    () => model.value,
    async (open) => {
      if (!open) return;

      resetDraft();
      clearErrors();
      employeePhoneManuallyChanged.value = false;

      await ensureAccountsLoaded();
      await ensureOfficeEmployeesLoaded();
      await ensureResourcesLoaded();
      await ensureEventsLoaded();
      await ensureInformationsLoaded();

      const pre = dbRepo.financePrefill;

      // ✅ link priority: event > info
      if (pre?.event_id) {
        draft.event_id = pre.event_id;
        draft.information_id = null;
      } else if (pre?.information_id) {
        draft.information_id = pre.information_id;
        draft.event_id = null;
      }

      // ✅ direct links
      if (pre?.resource_id) draft.resource_id = pre.resource_id;
      if (pre?.office_employee_id) setEmployeeAndPhone(pre.office_employee_id);

      // ✅ IMPORTANT: amount/words autofill from linked row + freeze (from prefill)
      if (pre?.event_id) {
        let ev =
          eventOptions.value.find((x) => sameId(x.id, pre.event_id)) ||
          findFromDbRepo('event', pre.event_id);

        if (!ev) ev = await fetchSingleIfPossible('event', pre.event_id);

        if (ev) {
          selectedEventsById[String(pre.event_id)] = ev;
          applyLockedAmountsFromLinkedRow(ev);
          setExpenseResultLabelFromLink();
        }
      } else if (pre?.information_id) {
        let inf =
          informationOptions.value.find((x) => sameId(x.id, pre.information_id)) ||
          findFromDbRepo('info', pre.information_id);

        if (!inf) inf = await fetchSingleIfPossible('info', pre.information_id);

        if (inf) {
          selectedInfosById[String(pre.information_id)] = inf;
          applyLockedAmountsFromLinkedRow(inf);
          setExpenseResultLabelFromLink();
        }
      }

      dbRepo.clearFinancePrefill?.();

      // ✅ edit mode (do NOT lock; edit should remain editable unless you want otherwise)
      if (props.editId) {
        clearErrors();
        const item = await repo.FetchFinanceData(props.editId);

        originalAccountId.value = item?.account_id ?? null;
        originalAmount.value = Number(item?.money_amount_number ?? 0) || 0;

        Object.assign(draft, {
          account_id: item?.account_id ?? null,
          money_amount_number: item?.money_amount_number ?? '',
          money_amount_words: item?.money_amount_words ?? '',
          date: item?.date ?? '',
          employee_phone: item?.employee_phone ?? '',
          expense_result: item?.expense_result ?? '',
          money_distributer: item?.money_distributer ?? '',
          payer: item?.payer ?? '',
          receiver: item?.receiver ?? '',
          account_type: item?.account_type ?? '',
          notes: item?.notes ?? '',
          office_employee_id: item?.office_employee_id ?? null,
          resource_id: item?.resource_id ?? null,
          event_id: item?.event_id ?? null,
          information_id: item?.information_id ?? null,
        });

        amountLocked.value = false;
        wordsLocked.value = false;
      }
    }
  );

  watch(
    () => draft.money_amount_number,
    () => {
      if (draft.money_amount_number !== '') validateAmount();
    }
  );
  watch(
    () => draft.account_id,
    () => {
      if (draft.money_amount_number !== '') validateAmount();
    }
  );

  async function onSubmit() {
    clearErrors();

    const res = await formRef.value?.validate();
    if (!res?.valid) return;

    const amount = toNumberSafe(draft.money_amount_number);
    if (Number.isFinite(amount) && amount <= 0) {
      backendErrors.money_amount_number = [t('finances.validation.amountMustBeGreaterThanZero')];
      return;
    }

    const available = effectiveAvailableBalance.value;
    if (Number.isFinite(amount) && available !== null && amount > available) {
      backendErrors.money_amount_number = [
        t('finances.validation.amountCannotBeGreaterThan', { balance: formatAmount(available) }),
      ];
      return;
    }

    saving.value = true;
    try {
      const payload = {
        account_id: draft.account_id,
        money_amount_number: draft.money_amount_number,
        money_amount_words: draft.money_amount_words,
        date: draft.date,
        employee_phone: draft.employee_phone,
        expense_result: draft.expense_result,
        money_distributer: draft.money_distributer,
        payer: draft.payer,
        receiver: draft.receiver,
        account_type: draft.account_type,
        notes: draft.notes,

        office_employee_id: draft.office_employee_id || null,
        resource_id: draft.resource_id || null,
        event_id: draft.event_id || null,
        information_id: draft.information_id || null,
      };

      if (isEdit.value) await repo.UpdateFinance(props.editId, payload);
      else await repo.CreateFinance(payload);

      emit('saved', { event_id: draft.event_id, information_id: draft.information_id });

      close();
    } catch (e) {
      const errors = e?.response?.data?.errors;
      if (errors && typeof errors === 'object') Object.assign(backendErrors, errors);
      else backendErrors.general = [t('common.errorTryAgain')];
    } finally {
      saving.value = false;
    }
  }
</script>

<style scoped>
  /* keep your unchanged styles exactly (same as your file) */
  .field :deep(.v-field) {
    border-radius: 12px;
    background: #ffffff;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
    overflow: hidden;
  }
  .field :deep(.v-field__outline) {
    display: none;
  }
  .field :deep(.v-field)::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 12px;
    border: 1.5px solid #e0e0e0;
    pointer-events: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .field :deep(.v-field):hover::before {
    border-color: #bdbdbd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }
  .field :deep(.v-field--focused) {
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.15);
  }
  .field :deep(.v-field--focused)::before {
    border-color: #d32f2f;
    border-width: 2px;
    box-shadow: 0 4px 12px rgba(211, 47, 47, 0.1);
  }
  .field :deep(.v-field--error) {
    background: #fff5f5;
  }
  .field :deep(.v-field--error)::before {
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.02);
  }
  .field :deep(.v-field--error.v-field--focused) {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
  }
  .field :deep(.v-field--disabled) {
    background: #f9fafb;
  }
  .field :deep(.v-field--disabled)::before {
    border-color: #e5e7eb;
    background: #f9fafb;
  }
  .field :deep(.v-label) {
    font-weight: 500;
    color: #6b7280;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    padding-left: 2px;
  }
  .field :deep(.v-field--focused .v-label),
  .field :deep(.v-field--persistent .v-label) {
    color: #d32f2f;
    font-weight: 600;
  }
  .field :deep(input),
  .field :deep(textarea),
  .field :deep(.v-select__selection-text) {
    font-weight: 500;
    color: #111827;
    font-size: 0.9375rem;
    padding-top: 2px;
  }
  .field :deep(.v-textarea textarea) {
    line-height: 1.5;
  }
  .field :deep(::placeholder) {
    color: #9ca3af;
    font-weight: 400;
  }
  .field :deep(.v-field__prepend-inner),
  .field :deep(.v-field__append-inner) {
    padding: 0 12px;
  }
  .field :deep(.v-field__prepend-inner .v-icon),
  .field :deep(.v-field__append-inner .v-icon) {
    color: #6b7280;
    transition: all 0.2s ease;
  }
  .field :deep(.v-field--focused .v-field__prepend-inner .v-icon),
  .field :deep(.v-field--focused .v-field__append-inner .v-icon) {
    color: #d32f2f;
  }
  .field :deep(.v-messages) {
    margin-top: 4px;
    padding: 0 4px;
  }
  .field :deep(.v-messages__message) {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    line-height: 1.2;
  }
  .field.v-input--error :deep(.v-messages__message),
  .field :deep(.v-field--error) :deep(.v-messages__message),
  .field :deep(.v-messages__message--error) {
    color: #ef4444 !important;
  }
  .field :deep(.v-autocomplete__menu) {
    border-radius: 12px;
    margin-top: 4px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  }
  .field :deep(.v-list-item) {
    border-radius: 8px;
    margin: 2px 8px;
    min-height: 40px;
  }
  .field :deep(.v-list-item--active) {
    background: #fef2f2;
  }
  .field :deep(.v-chip) {
    border-radius: 8px;
    font-weight: 500;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
  }
  .field :deep(.v-field__clearable) .v-icon {
    color: #9ca3af;
  }
  .field :deep(.v-field__clearable) .v-icon:hover {
    color: #6b7280;
  }
  .field :deep(.v-textarea .v-field) {
    padding-top: 12px;
  }
  .field :deep(.v-textarea .v-field__input) {
    min-height: 80px;
  }
  .dialog-card {
    border: 1px solid rgba(0, 0, 0, 0.06);
    background: #fff;
  }
  .dialog-header {
    padding: 18px 18px;
    background: linear-gradient(135deg, rgba(211, 47, 47, 0.12), rgba(244, 67, 54, 0.06));
  }
  .icon-badge {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(211, 47, 47, 0.14);
    color: #d32f2f;
    border: 1px solid rgba(211, 47, 47, 0.18);
  }
  .section {
    background: #fafafa;
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: 16px;
    padding: 14px;
  }
  .section-title {
    display: flex;
    align-items: center;
    font-weight: 800;
    font-size: 13px;
    margin-bottom: 10px;
    color: rgba(0, 0, 0, 0.7);
  }
  .grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }
  @media (max-width: 960px) {
    .grid-2 {
      grid-template-columns: 1fr;
    }
  }
  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }
  .v-btn {
    text-transform: none;
    font-weight: 700;
    letter-spacing: normal;
  }
</style>
