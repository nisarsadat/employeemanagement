<template>
  <AppBar :title="t('دشبورد')" @toggle-drawer="drawer = !drawer">
    <template #actions>
      <v-btn icon />
    </template>
  </AppBar>

  <v-container fluid class="bg-bgproject py-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Main Tabs -->
    <v-row class="align-start">
      <v-col cols="12">
        <v-tabs
          v-model="mainTab"
          fixed-tabs
          class="mb-3 rounded-xl"
          align-tabs="center"
          density="comfortable"
          color="primary"
        >
          <v-tab value="accounts">{{ t('افراد') }}</v-tab>
          <v-tab value="cashflow">{{ t('اطلاعات و واقعات') }}</v-tab>
          <v-tab value="oil">{{ t('۵۶ فارمت') }}</v-tab>
          <v-tab value="report">{{ t('بخش مالی') }}</v-tab>
        </v-tabs>

        <v-window v-model="mainTab">
          <!-- ========== Accounts Window ========== -->
          <v-window-item value="accounts">
            <v-card elevation="2" class="pa-2 rounded-xl">
              <v-tabs
                v-model="accountsTab"
                grow
                class="mb-2 rounded-lg"
                color="primary"
                density="comfortable"
              >
                <v-tab value="list">{{ t('کارمندان') }}</v-tab>
                <v-tab value="types">{{ t('منابع') }}</v-tab>
                <v-tab value="type">{{ t('زندانی های تسلیم شده') }}</v-tab>
              </v-tabs>

              <v-window v-model="accountsTab" v-if="auth.can('PeopleDashboard.view')">
                <v-window-item value="list">
                  <officeemployeedashboard />
                </v-window-item>
                <v-window-item value="types">
                  <ResourceDashboard />
                </v-window-item>
                <v-window-item value="type">
                  <CaptiveDeleviryDashboard />
                </v-window-item>
              </v-window>
            </v-card>
          </v-window-item>

          <!-- ========== Cashflow (Income/Expense) Window ========== -->
          <v-window-item value="cashflow">
            <v-card elevation="2" class="pa-2 rounded-xl">
              <v-tabs
                v-model="cashflowTab"
                grow
                class="mb-2 rounded-lg"
                color="primary"
                density="comfortable"
              >
                <v-tab value="income">{{ t('اطلاعات') }}</v-tab>
                <v-tab value="expense">{{ t('واقعات') }}</v-tab>
              </v-tabs>

              <v-window v-model="cashflowTab" v-if="auth.can('InfoEnevtDashboard.view')">
                <v-window-item value="income">
                  <InfoDashboard />
                </v-window-item>
                <v-window-item value="expense">
                  <EventDashboard />
                </v-window-item>
              </v-window>
            </v-card>
          </v-window-item>

          <!-- ========== Oil Window ========== -->
          <v-window-item value="oil">
            <v-card elevation="2" class="pa-2 rounded-xl">
              <v-tabs
                v-model="oilTab"
                grow
                class="mb-2 rounded-lg"
                color="primary"
                density="comfortable"
              >
                <v-tab value="income">{{ t('۵۶ فارمت ') }}</v-tab>
                <v-tab value="expense">{{ t('۵۶ فارمت') }}</v-tab>
                <v-tab value="accountssss">{{ t('۵۶ فارمت') }}</v-tab>
                <v-tab value="accountsss">{{ t('۵۶ فارمت') }}</v-tab>
                <v-tab value="accountss">{{ t('۵۶ فارمت') }}</v-tab>
              </v-tabs>

              <v-window v-model="oilTab" v-if="auth.can('FifthyFormatDashboard.view')">
                <v-window-item value="income">
                  <FirstTen />
                </v-window-item>
                <v-window-item value="expense">
                  <SecondTen />
                </v-window-item>
                <v-window-item value="accountssss">
                  <ThirdTen />
                </v-window-item>
                <v-window-item value="accountsss">
                  <FourthTen />
                </v-window-item>
                <v-window-item value="accountss">
                  <FifthTen />
                </v-window-item>
              </v-window>
            </v-card>
          </v-window-item>

          <!-- ========== Reports Window ========== -->
          <v-window-item value="report">
            <v-card elevation="2" class="pa-2 rounded-xl">
              <v-tabs
                v-model="reportTab"
                grow
                class="mb-2 rounded-lg"
                color="primary"
                density="comfortable"
              >
                <v-tab value="oilreport">{{ t('بخش تیل') }}</v-tab>
                <v-tab value="moneyreport">{{ t('بخش پول') }}</v-tab>
              </v-tabs>

              <!-- bind to reportTab (fixed) -->
              <v-window v-model="reportTab" v-if="auth.can('FininaceDashboard.view')">
                <v-window-item value="oilreport">
                  <OilReport />
                </v-window-item>
                <v-window-item value="moneyreport">
                  <MoneyReport />
                </v-window-item>
              </v-window>
            </v-card>
          </v-window-item>
        </v-window>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
  import { ref, computed } from 'vue';
  import { useI18n } from 'vue-i18n';

  import AppBar from '../../components/AppBar.vue';
  import officeemployeedashboard from './PeopleDashboard/Officeemployeedashboard.vue';
  import CaptiveDeleviryDashboard from './PeopleDashboard/CaptiveDeliveryDashboard.vue';
  import ResourceDashboard from './PeopleDashboard/ResourceDashboard.vue';

  import EventDashboard from './EIDashboard/EventDashboard.vue';
  import InfoDashboard from './EIDashboard/InfoDashboard.vue';
  import MoneyReport from './OIlMoney/MoneyReport.vue';
  import OilReport from './OIlMoney/OilReport.vue';
  import FirstTen from './FifthyFormat/Firstten.vue';
  import SecondTen from './FifthyFormat/SecondTen.vue';
  import ThirdTen from './FifthyFormat/ThirdTen.vue';
  import FourthTen from './FifthyFormat/FourthTen.vue';
  import FifthTen from './FifthyFormat/FifthTen.vue';

  import { useLoginRepository } from '../../stores/LoginRepository';
  const auth = useLoginRepository();
  const { t, locale } = useI18n();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  const drawer = ref(false);

  // Main tabs: accounts | cashflow | oil | report
  const mainTab = ref('accounts');

  // Sub-tabs
  const accountsTab = ref('list'); // list | types
  const cashflowTab = ref('income'); // income | expense
  const oilTab = ref('income'); // income | expense | account
  const reportTab = ref('oilreport'); // oilreport | moneyreport
</script>

<style scoped>
  :deep(.v-tabs) {
    backdrop-filter: saturate(120%) blur(2px);
  }

  :deep(.v-tab) {
    font-weight: 600;
    letter-spacing: 0.2px;
  }

  :deep(.v-card) {
    border-radius: 16px;
  }

  :deep(.v-window-item) {
    padding: 8px;
  }

  /* Better spacing for RTL/LTR */
  .v-container[dir='rtl'] :deep(.v-tab__slider) {
    inset-inline-start: 0 !important;
  }
</style>
