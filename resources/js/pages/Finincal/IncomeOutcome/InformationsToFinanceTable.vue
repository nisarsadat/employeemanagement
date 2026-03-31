<template>
  <div class="pa-4">
    <v-card rounded="xl" class="pa-4">
      <div class="d-flex align-center justify-space-between mb-3">
        <div class="text-h6 font-weight-bold">د اطلاعاتو اړوند مالي حساب</div>

        <v-btn
          variant="tonal"
          prepend-icon="mdi-refresh"
          density="comfortable"
          class="rounded-lg"
          color="primary"
          :loading="dbRepo.loading"
          @click="refreshPage"
        />
      </div>

      <v-data-table-server
        v-model:page="page"
        v-model:items-per-page="itemsPerPage"
        :headers="headers"
        :items="informationsToFinance"
        :items-length="totalItems"
        :loading="dbRepo.loading"
        class="rounded-xl"
        density="compact"
        item-value="id"
        @update:options="onOptionsChange"
      >
        <template #item.message_to_finance="{ item }">
          <div class="text-body-2" style="max-width: 520px; white-space: pre-wrap">
            {{ item.message_to_finance }}
          </div>
        </template>

        <template #item.status="{ item }">
          <v-chip
            size="small"
            class="rounded-lg"
            variant="tonal"
            :color="isPaid(item) ? 'success' : 'warning'"
          >
            {{ isPaid(item) ? 'پرداخت شوی' : 'د انتظار حالت' }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            color="error"
            variant="tonal"
            density="compact"
            class="rounded-lg"
            prepend-icon="mdi-check"
            :disabled="isPaid(item)"
            @click="openFinance(item)"
          >
            انتخاب
          </v-btn>
        </template>

        <template #bottom>
          <div class="d-flex justify-end pa-3">
            <v-pagination
              v-model="page"
              :length="pageCount"
              rounded="circle"
              density="comfortable"
              @update:model-value="fetch"
            />
          </div>

          <div class="d-flex justify-end px-3 pb-3">
            <v-select
              v-model="itemsPerPage"
              :items="itemsPerPageOptions"
              label="تعداد"
              variant="outlined"
              density="compact"
              class="items-per-page-select"
              style="max-width: 120px"
              @update:model-value="onPerPageChange"
            />
          </div>
        </template>
      </v-data-table-server>
    </v-card>

    <FinanceFormDialog v-model="financeDialog" @saved="onFinanceSaved" />
  </div>
</template>

<script setup>
  import { computed, ref } from 'vue';
  import { useDatabaseRepository } from '@/stores/DatabaseRepository';
  import FinanceFormDialog from './FinancesFormDialog.vue';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  const dbRepo = useDatabaseRepository();
  const financeDialog = ref(false);

  const page = ref(Number(dbRepo.page || 1));
  const itemsPerPage = ref(Number(dbRepo.itemsPerPage || 10));
  const totalItems = ref(Number(dbRepo.totalItems || 0));

  const itemsPerPageOptions = [5, 10, 20, 50, 100];

  const headers = [
    { title: 'ID', key: 'id', sortable: true },
    { title: ' د منبع کوډ', key: 'resource_code_number', sortable: true, align: 'center' },
    { title: 'د کارمند کوډ', key: 'office_employee_code_number', sortable: true, align: 'center' },
    { title: ' مالی پیغام', key: 'message_to_finance', sortable: false, align: 'center' },
    { title: 'د پرداخت حالت', key: 'status', sortable: false, align: 'center' },
    { title: 'کړنې', key: 'actions', sortable: false, align: 'center' },
  ];

  const informationsToFinance = computed(() => {
    const list = Array.isArray(dbRepo.Informations) ? dbRepo.Informations : [];
    return list.filter((x) => String(x?.message_to_finance ?? '').trim().length > 0);
  });

  const pageCount = computed(() => {
    const total = Number(totalItems.value || 0);
    const per = Number(itemsPerPage.value || 10);
    return Math.max(1, Math.ceil(total / per));
  });

  function isPaid(item) {
    if (!item) return false;

    const status = String(item.finance_status ?? item.payment_status ?? '')
      .toLowerCase()
      .trim();

    if (['paid', 'done', 'completed', 'success', 'closed'].includes(status)) return true;

    const paidAt = item.finance_paid_at ?? item.paid_at ?? null;
    if (paidAt) return true;

    const flag = item.is_finance_paid ?? item.finance_is_paid ?? item.is_paid ?? item.paid ?? false;

    return flag === true || String(flag) === '1';
  }

  async function fetch() {
    dbRepo.page = page.value;
    dbRepo.itemsPerPage = itemsPerPage.value;

    const res = await dbRepo.FetchInformationsData({
      page: page.value,
      perPage: itemsPerPage.value,
    });

    totalItems.value =
      Number(
        res?.meta?.total ??
          res?.total ??
          dbRepo.totalItems ??
          dbRepo.meta?.total ??
          0
      ) || 0;
  }

  function onOptionsChange(options) {
    const nextPage = Number(options.page || 1);
    const nextPerPage = Number(options.itemsPerPage || 10);

    const pageChanged = page.value !== nextPage;
    const perPageChanged = itemsPerPage.value !== nextPerPage;

    page.value = nextPage;
    itemsPerPage.value = nextPerPage;

    if (pageChanged || perPageChanged) {
      fetch();
    }
  }

  function onPerPageChange() {
    page.value = 1;
    fetch();
  }

  function refreshPage() {
    fetch();
  }

  function openFinance(infoRow) {
    dbRepo.setFinancePrefill({
      information_id: infoRow.id,
      resource_id: infoRow.resource_id ?? infoRow.resource?.id ?? null,
      office_employee_id: infoRow.office_employee_id ?? infoRow.office_employee?.id ?? null,
      account_id:
        infoRow.account_id ??
        infoRow.account?.id ??
        infoRow.resource?.account_id ??
        infoRow.resource?.account?.id ??
        null,
      message_to_finance: infoRow.message_to_finance ?? '',
      money_amount_words: infoRow.money_amount_words ?? '',
    });

    financeDialog.value = true;
  }

  async function onFinanceSaved(payload) {
    const infoId = payload?.information_id;

    if (infoId && Array.isArray(dbRepo.Informations)) {
      const row = dbRepo.Informations.find((x) => String(x?.id) === String(infoId));
      if (row) {
        row.finance_status = 'done';
        row.finance_paid_at = new Date().toISOString();
      }
    }

    await fetch();
  }

  usePageRefresh('/app/finincalindex', fetch);

  fetch();
</script>

<style scoped>
  .items-per-page-select :deep(.v-field) {
    border-radius: 12px;
  }
</style>