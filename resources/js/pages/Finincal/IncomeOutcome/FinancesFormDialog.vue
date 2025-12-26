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
                {{ isEdit ? 'ویرایش مصرف' : 'ثبت مصرف جدید' }}
              </div>
              <div class="text-caption opacity-90">
                {{ isEdit ? 'اطلاعات مصرف را ویرایش کنید' : 'اطلاعات مصرف جدید را وارد کنید' }}
              </div>
            </div>
          </div>

          <v-btn
            icon="mdi-close"
            variant="text"
            size="small"
            class="rounded-lg"
            @click="close"
          />
        </div>
      </div>

      <v-divider />

      <v-card-text class="pa-5">
        <v-form ref="formRef" @submit.prevent="onSubmit">
          <!-- Section: Main -->
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-information-outline" size="18" class="me-2" />
              اطلاعات اصلی
            </div>

            <div class="grid-2">
              <!-- Account -->
              <v-autocomplete
                v-model="draft.account_id"
                :items="accountOptions"
                item-title="name"
                item-value="id"
                label="حساب *"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-bank-outline"
                :loading="repo.loading"
                :rules="[rules.required]"
                :error="hasError('account_id')"
                :error-messages="getErrorMessages('account_id')"
                class="field"
              />

              <!-- Date -->
              <DateInput
                v-model="draft.date"
                label="تاریخ *"
                density="compact"
                :rules="[rules.required]"
                :error="hasError('date')"
                :error-messages="getErrorMessages('date')"
                class="field"
              />

              <!-- Amount Number -->
              <v-text-field
                v-model="draft.money_amount_number"
                label="مبلغ (عدد) *"
                density="compact"
                variant="outlined"
                type="number"
                prepend-inner-icon="mdi-currency-usd"
                :rules="[rules.requiredNumber]"
                :error="hasError('money_amount_number')"
                :error-messages="getErrorMessages('money_amount_number')"
                class="field"
              />

              <!-- Amount Words -->
              <v-text-field
                v-model="draft.money_amount_words"
                label="مبلغ (حروف)"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-text-box-outline"
                :error="hasError('money_amount_words')"
                :error-messages="getErrorMessages('money_amount_words')"
                class="field"
              />
            </div>
          </div>

          <v-divider class="my-4" />

          <!-- Section: People & Result -->
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-account-group-outline" size="18" class="me-2" />
              معلومات تکمیلی
            </div>

            <div class="grid-2">
              <!-- Employee Phone -->
              <v-text-field
                v-model="draft.employee_phone"
                label="شماره تلفن کارمند"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-phone-outline"
                :error="hasError('employee_phone')"
                :error-messages="getErrorMessages('employee_phone')"
                class="field"
              />

              <!-- Expense Result -->
              <v-text-field
                v-model="draft.expense_result"
                label="نتیجه مصرف"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-clipboard-check-outline"
                :error="hasError('expense_result')"
                :error-messages="getErrorMessages('expense_result')"
                class="field"
              />

              <!-- Payer -->
              <v-text-field
                v-model="draft.payer"
                label="پرداخت کننده"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-account-arrow-right-outline"
                :error="hasError('payer')"
                :error-messages="getErrorMessages('payer')"
                class="field"
              />

              <!-- Receiver -->
              <v-text-field
                v-model="draft.receiver"
                label="دریافت کننده"
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

          <!-- Section: Link to employee/resource -->
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-link-variant" size="18" class="me-2" />
              ارتباطات (حداقل یکی ضروری)
            </div>

            <div class="grid-2">
              <!-- Office Employee dropdown -->
              <v-autocomplete
                v-model="draft.office_employee_id"
                :items="officeEmployeeOptions"
                item-title="name"
                item-value="id"
                :label="draft.resource_id ? 'کارمند' : 'کارمند *'"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-account-outline"
                clearable
                :loading="repo.officeEmployeesLoading"
                :rules="[conditionalEmployeeRequired]"
                :error="hasError('office_employee_id')"
                :error-messages="getErrorMessages('office_employee_id')"
                class="field"
              />

              <!-- Resource dropdown -->
              <v-autocomplete
                v-model="draft.resource_id"
                :items="resourceOptions"
                item-title="name"
                item-value="id"
                :label="draft.office_employee_id ? 'منبع' : 'منبع *'"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-link-box-outline"
                clearable
                :loading="repo.resourcesLoading"
                :rules="[conditionalResourceRequired]"
                :error="hasError('resource_id')"
                :error-messages="getErrorMessages('resource_id')"
                class="field"
              />
            </div>
          </div>

          <v-divider class="my-4" />

          <!-- Notes -->
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-note-text-outline" size="18" class="me-2" />
              یادداشت
            </div>

            <v-textarea
              v-model="draft.notes"
              label="یادداشت"
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

          <!-- General errors -->
          <div v-if="backendErrors.general?.length" class="mt-4">
            <v-alert type="error" variant="tonal" density="compact" class="rounded-lg">
              <div v-for="(m, i) in backendErrors.general" :key="i" class="text-caption">
                {{ m }}
              </div>
            </v-alert>
          </div>

          <!-- Actions -->
          <div class="actions mt-6">
            <v-btn variant="text" class="rounded-lg px-4" color="grey-darken-1" @click="close">
              انصراف
            </v-btn>

            <v-btn
              color="error"
              :loading="saving"
              type="submit"
              class="rounded-lg px-6"
              prepend-icon="mdi-check"
              elevation="1"
            >
              {{ isEdit ? 'ذخیره تغییرات' : 'ثبت مصرف' }}
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { computed, reactive, ref, watch } from 'vue';
  import { useFinanceRepository } from '@/stores/financeRepository';
  import DateInput from '@/components/DateInput.vue';

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
  });

  const backendErrors = reactive({});

  const rules = {
    required: (v) => !!v || v === 0 || 'این فیلد ضروری است',
    requiredNumber: (v) => {
      if (v === null || v === undefined || v === '') return 'این فیلد ضروری است';
      const n = Number(v);
      return Number.isFinite(n) ? true : 'عدد معتبر وارد کنید';
    },
  };

  const conditionalEmployeeRequired = (v) => {
    if (!draft.resource_id && !v) return 'حداقل یکی از فیلدهای "کارمند" یا "منبع" باید پر شود';
    return true;
  };

  const conditionalResourceRequired = (v) => {
    if (!draft.office_employee_id && !v) return 'حداقل یکی از فیلدهای "کارمند" یا "منبع" باید پر شود';
    return true;
  };

  const accountOptions = computed(() => repo.accounts || []);
  const officeEmployeeOptions = computed(() => repo.officeEmployees || []);
  const resourceOptions = computed(() => repo.resources || []);

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

  async function loadForEdit() {
    if (!props.editId) return;

    clearErrors();
    const item = await repo.FetchFinanceData(props.editId);

    draft.account_id = item?.account_id ?? null;
    draft.money_amount_number = item?.money_amount_number ?? '';
    draft.money_amount_words = item?.money_amount_words ?? '';
    draft.date = item?.date ?? '';

    draft.employee_phone = item?.employee_phone ?? '';
    draft.expense_result = item?.expense_result ?? '';
    draft.money_distributer = item?.money_distributer ?? '';
    draft.payer = item?.payer ?? '';
    draft.receiver = item?.receiver ?? '';
    draft.account_type = item?.account_type ?? '';
    draft.notes = item?.notes ?? '';

    draft.office_employee_id = item?.office_employee_id ?? null;
    draft.resource_id = item?.resource_id ?? null;
  }

  watch(
    () => model.value,
    async (open) => {
      if (!open) return;

      resetDraft();
      clearErrors();

      await ensureAccountsLoaded();
      await ensureOfficeEmployeesLoaded();
      await ensureResourcesLoaded();

      if (props.editId) await loadForEdit();
    }
  );

  watch(
    () => [draft.office_employee_id, draft.resource_id],
    () => {
      if (formRef.value) formRef.value.validate();
    }
  );

  function close() {
    model.value = false;
  }

  async function onSubmit() {
    clearErrors();

    const res = await formRef.value?.validate();
    if (!res?.valid) return;

    if (!draft.office_employee_id && !draft.resource_id) {
      backendErrors.general = ['حداقل یکی از فیلدهای "کارمند" یا "منبع" باید پر شود'];
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
      };

      if (isEdit.value) {
        await repo.UpdateFinance(props.editId, payload);
      } else {
        await repo.CreateFinance(payload);
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
  /* ... keep previous styles until the input section ... */

  /* ================================
     MODERN INPUT DESIGN
     ================================ */

  /* Base field surface */
  .field :deep(.v-field) {
    border-radius: 12px;
    background: #ffffff;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
    overflow: hidden;
  }

  /* Remove default Vuetify outlines since we're using custom borders */
  .field :deep(.v-field__outline) {
    display: none;
  }

  /* Custom border container */
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

  /* Hover state */
  .field :deep(.v-field):hover::before {
    border-color: #bdbdbd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }

  /* Focus state */
  .field :deep(.v-field--focused) {
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.15);
  }
  
  .field :deep(.v-field--focused)::before {
    border-color: #d32f2f;
    border-width: 2px;
    box-shadow: 0 4px 12px rgba(211, 47, 47, 0.1);
  }

  /* Error state */
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

  /* Disabled state */
  .field :deep(.v-field--disabled) {
    background: #f9fafb;
  }
  
  .field :deep(.v-field--disabled)::before {
    border-color: #e5e7eb;
    background: #f9fafb;
  }

  /* Label styling */
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

  /* Input text */
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

  /* Placeholder */
  .field :deep(::placeholder) {
    color: #9ca3af;
    font-weight: 400;
  }

  /* Icons */
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

  /* Hint and error messages */
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
  
  .field :deep(.v-messages__message:first-child) {
    margin-top: 2px;
  }

  /* Autocomplete specific */
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

  /* Chips in autocomplete */
  .field :deep(.v-chip) {
    border-radius: 8px;
    font-weight: 500;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
  }

  /* Clear button */
  .field :deep(.v-field__clearable) .v-icon {
    color: #9ca3af;
  }
  
  .field :deep(.v-field__clearable) .v-icon:hover {
    color: #6b7280;
  }

  /* Textarea specific */
  .field :deep(.v-textarea .v-field) {
    padding-top: 12px;
  }
  
  .field :deep(.v-textarea .v-field__input) {
    min-height: 80px;
  }

  /* DateInput component compatibility */
  .field :deep(.date-input .v-field)::before {
    display: none;
  }
  
  .field :deep(.date-input .v-field) {
    border: 1.5px solid #e0e0e0;
  }
  
  .field :deep(.date-input .v-field):hover {
    border-color: #bdbdbd;
  }
  
  .field :deep(.date-input .v-field--focused) {
    border-color: #d32f2f;
    box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.15);
  }

  /* Responsive adjustments */
  @media (max-width: 600px) {
    .field :deep(.v-field) {
      border-radius: 10px;
    }
    
    .field :deep(.v-field)::before {
      border-radius: 10px;
    }
    
    .field :deep(input),
    .field :deep(textarea) {
      font-size: 0.875rem;
    }
  }

  /* Keep other existing styles */
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

  .grid-1 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
  }
  .grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }
  .grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
  }
  .grid-4 {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
  }

  @media (max-width: 960px) {
    .grid-2,
    .grid-3,
    .grid-4 {
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