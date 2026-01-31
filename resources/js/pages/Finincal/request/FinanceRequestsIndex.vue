<template>
  <v-container fluid>
    <AppBar :title="t('درخواست‌های مالیه')" :subtitle="t('پیام‌های رسیده از واقعه و اطلاعات')" />

    <v-card rounded="xl" class="pa-4">
      <div class="d-flex gap-2 flex-wrap mb-3">
        <v-text-field
          v-model="search"
          :label="t('جستجو')"
          density="compact"
          variant="outlined"
          prepend-inner-icon="mdi-magnify"
          style="max-width: 320px"
          @keyup.enter="fetchAll"
        />
        <v-btn color="primary" variant="elevated" @click="fetchAll" :loading="repo.loading">
          {{ t('تازه سازی') }}
        </v-btn>
      </div>

      <v-data-table
        :headers="headers"
        :items="repo.rows"
        :loading="repo.loading"
        item-key="id"
        class="elevation-0"
      >
        <template #item.source_type="{ item }">
          <v-chip size="small" variant="flat">
            {{ item.source_type === 'event' ? t('واقعه') : t('اطلاعات') }}
          </v-chip>
        </template>

        <template #item.status="{ item }">
          <v-chip size="small" :color="item.status === 'pending' ? 'warning' : item.status === 'paid' ? 'success' : 'error'">
            {{ item.status }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex gap-2">
            <v-btn size="small" color="error" variant="outlined" @click="reject(item)">
              {{ t('رد') }}
            </v-btn>

            <v-btn size="small" color="primary" variant="elevated" @click="openPay(item)">
              {{ t('پرداخت') }}
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Your existing finance dialog -->
    <FinanceDialog
      v-model="financeOpen"
      :edit-id="null"
      :prefill="financePrefill"
      @saved="onFinanceSaved"
    />
  </v-container>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppBar from '@/components/AppBar.vue';
import FinanceDialog from '@/components/FinanceDialog.vue'; // adjust if needed
import { useFinanceRequestsRepository } from '@/stores/financeRequestsRepository';

const { t } = useI18n();
const repo = useFinanceRequestsRepository();

const search = ref('');

const financeOpen = ref(false);
const financePrefill = ref(null);
const activeRequest = ref(null);

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'نوع', key: 'source_type' },
  { title: 'Source ID', key: 'source_id' },
  { title: 'پیام', key: 'message' },
  { title: 'حالت', key: 'status' },
  { title: 'عملیات', key: 'actions', sortable: false },
];

async function fetchAll() {
  await repo.FetchFinanceRequests({ search: search.value, status: 'pending', perPage: 200 });
}

function openPay(item) {
  activeRequest.value = item;

  financePrefill.value = {
    // ✅ link request for pay callback
    finance_request_id: item.id,

    // ✅ set event_id or information_id
    event_id: item.source_type === 'event' ? item.source_id : null,
    information_id: item.source_type === 'information' ? item.source_id : null,

    // optional helpful prefill
    resource_id: item.resource_id || null,
    office_employee_id: item.office_employee_id || null,

    // put message into notes
    notes: item.message || '',
  };

  financeOpen.value = true;
}

async function onFinanceSaved(createdFinanceId) {
  // If your FinanceDialog only emits 'saved' without id, tell me — we can adjust.
  // Here we expect it emits finance id.
  if (activeRequest.value?.id && createdFinanceId) {
    await repo.PayFinanceRequest(activeRequest.value.id, createdFinanceId);
  }
  financeOpen.value = false;
  financePrefill.value = null;
  activeRequest.value = null;
  await fetchAll();
}

async function reject(item) {
  await repo.RejectFinanceRequest(item.id);
  await fetchAll();
}

// init
fetchAll();
</script>
