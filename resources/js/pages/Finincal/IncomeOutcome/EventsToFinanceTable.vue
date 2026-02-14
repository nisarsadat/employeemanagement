<template>
  <div class="pa-4">
    <v-card rounded="xl" class="pa-4">
      <div class="d-flex align-center justify-space-between mb-3">
        <div class="text-h6 font-weight-bold">د پیښو اړوند مالي حساب</div>

        <v-btn
          variant="outlined"
          prepend-icon="mdi-refresh"
          density="comfortable"
          class="rounded-lg bg-blue"
          :loading="repo.loading"
          @click="fetch"
        >
          
        </v-btn>
      </div>

      <v-data-table
        :headers="headers"
        :items="eventsToFinance"
        :loading="repo.loading"
        :items-per-page="10"
        class="rounded-xl"
        density="compact"
      >
        <!-- message column -->
        <template #item.message_to_finance="{ item }">
          <div class="text-body-2" style="max-width: 520px; white-space: pre-wrap">
            {{ item.message_to_finance }}
          </div>
        </template>

        <!-- ✅ status column (Pashto) -->
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

        <!-- action -->
        <template #item.actions="{ item }">
          <v-btn
            color="error"
            variant="elevated"
            density="compact"
            class="rounded-lg"
            prepend-icon="mdi-check"
            :disabled="isPaid(item)"
            @click="openFinance(item)"
          >
            انتخاب
          </v-btn>
        </template>
      </v-data-table>
    </v-card>

    <FinanceFormDialog v-model="financeDialog" @saved="onFinanceSaved" />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useDatabaseRepository } from '@/stores/DatabaseRepository';
import FinanceFormDialog from './FinancesFormDialog.vue';

const repo = useDatabaseRepository();
const financeDialog = ref(false);

const headers = [
  { title: 'ID', key: 'id', sortable: true },
  { title: ' د منبع کوډ', key: 'resource_code_number', sortable: true , align: 'center'},
  { title: 'د کارمند کوډ', key: 'office_employee_code_number', sortable: true , align: 'center' },
  { title: ' مالی پیغام', key: 'message_to_finance', sortable: false , align: 'center'},
  { title: 'د پرداخت حالت', key: 'status', sortable: false , align: 'center'},
  { title: 'کړنې', key: 'actions', sortable: false , align: 'center'},
];

const eventsToFinance = computed(() => {
  // ✅ some repos use Events, some events — support both
  const list = Array.isArray(repo.Events) ? repo.Events : (Array.isArray(repo.events) ? repo.events : []);
  return list.filter((e) => String(e?.message_to_finance ?? '').trim().length > 0);
});

function isPaid(item) {
  if (!item) return false;

  const status = String(item.finance_status ?? item.payment_status ?? '').toLowerCase().trim();
  if (['paid', 'done', 'completed', 'success', 'closed'].includes(status)) return true;

  const paidAt = item.finance_paid_at ?? item.paid_at ?? null;
  if (paidAt) return true;

  const flag =
    item.is_finance_paid ??
    item.finance_is_paid ??
    item.is_paid ??
    item.paid ??
    false;

  return flag === true || String(flag) === '1';
}

async function fetch() {
  await repo.FetchEventsData({ page: repo.page, itemsPerPage: repo.itemsPerPage });
}

function openFinance(eventRow) {
  repo.setFinancePrefill({ event_id: eventRow.id });
  financeDialog.value = true;
}

async function onFinanceSaved() {
  await fetch();
}

fetch();
</script>
