<template>
  <AppBar :title="t('حساب مالي')" @toggle-drawer="drawer = !drawer">
    <template #actions>
      <v-btn icon />
    </template>
  </AppBar>
  <div class="pa-4">
    <v-card rounded="xl" class="pa-4">
      <div class="d-flex align-center justify-space-between mb-3">
        <div class="text-h6 font-weight-bold">د جلساتو اړوند مالي حساب</div>

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
        :items="meetingsToFinance"
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
              prev-icon="mdi-chevron-right"
              next-icon="mdi-chevron-left"
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
  import AppBar from '@/components/AppBar.vue';
  import { useI18n } from 'vue-i18n';

  const dbRepo = useDatabaseRepository();
  const financeDialog = ref(false);

  const page = ref(Number(dbRepo.page || 1));
  const itemsPerPage = ref(Number(dbRepo.itemsPerPage || 10));
  const totalItems = ref(Number(dbRepo.totalItems || 0));

  const itemsPerPageOptions = [5, 10, 20, 50, 100];
  const { t } = useI18n();

  const headers = [
    { title: 'ID', key: 'id', sortable: true },
    { title: 'عنوان جلسه', key: 'meeting_title', sortable: true, align: 'center' },
    { title: 'ارگان', key: 'organ', sortable: true, align: 'center' },
    { title: ' مالی پیغام', key: 'message_to_finance', sortable: false, align: 'center' },
    { title: 'د پرداخت حالت', key: 'status', sortable: false, align: 'center' },
    { title: 'کړنې', key: 'actions', sortable: false, align: 'center' },
  ];

  const meetingsToFinance = computed(() =>
    Array.isArray(dbRepo.Meetings) ? dbRepo.Meetings : []
  );

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

    const res = await dbRepo.FetchMeetingsData({
      page: page.value,
      perPage: itemsPerPage.value,
      only_finance_queue: 1,
    });

    totalItems.value =
      Number(
        res?.data?.meta?.total ?? res?.meta?.total ?? dbRepo.totalItems ?? dbRepo.meta?.total ?? 0
      ) || 0;

    page.value = Number(dbRepo.page || page.value || 1);
    itemsPerPage.value = Number(dbRepo.itemsPerPage || itemsPerPage.value || 10);
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

  function openFinance(meetingRow) {
    const employees = Array.isArray(meetingRow.office_employees) ? meetingRow.office_employees : [];
    const employeeId =
      meetingRow.office_employee_id ??
      employees[0]?.id ??
      (Array.isArray(meetingRow.office_employee_ids) ? meetingRow.office_employee_ids[0] : null) ??
      null;

    dbRepo.setFinancePrefill({
      meeting_id: meetingRow.id,
      office_employee_id: employeeId,
      account_id: meetingRow.account_id ?? meetingRow.account?.id ?? null,
      message_to_finance: meetingRow.message_to_finance ?? '',
      money_amount_words: meetingRow.money_amount_words ?? '',
    });

    financeDialog.value = true;
  }

  async function onFinanceSaved(payload) {
    const meetingId = payload?.meeting_id;

    if (meetingId && Array.isArray(dbRepo.Meetings)) {
      const row = dbRepo.Meetings.find((x) => String(x?.id) === String(meetingId));
      if (row) {
        row.finance_status = 'paid';
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
