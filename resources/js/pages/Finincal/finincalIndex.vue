<template>
  <AppBar :title="t('حساب مالي')" @toggle-drawer="drawer = !drawer">
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
          <v-tab value="accounts">{{ t('حسابونه') }}</v-tab>
          <v-tab value="cashflow">{{ t('عاید او مصرف') }}</v-tab>
          <v-tab value="oil">{{ t('تیل') }}</v-tab>
          <v-tab value="report">{{ t('راپورونه') }}</v-tab>
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
                <v-tab value="list">{{ t('حسابونه') }}</v-tab>
                <v-tab value="types">{{ t('د حساب ډولونه') }}</v-tab>
              </v-tabs>

              <v-window v-model="accountsTab">
                <v-window-item value="list">
                  <AccountsIndex />
                </v-window-item>
                <v-window-item value="types">
                  <AccountTypesIndex />
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
                <v-tab value="income">{{ t('عایدات') }}</v-tab>
                <v-tab value="expense">{{ t('مصارف') }}</v-tab>
              </v-tabs>

              <v-window v-model="cashflowTab">
                <v-window-item value="income">
                  <IncomesIndex />
                </v-window-item>
                <v-window-item value="expense">
                  <ExpensesIndex />
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
                <v-tab value="income">{{ t('د تیلو داخل') }}</v-tab>
                <v-tab value="expense">{{ t('د تیلو مصرف') }}</v-tab>
                <v-tab value="account">{{ t('د تیلو حسابونه') }}</v-tab>
              </v-tabs>

              <v-window v-model="oilTab">
                <v-window-item value="income">
                  <OilIncomeIndex />
                </v-window-item>
                <v-window-item value="expense">
                  <OilExpenseIndex />
                </v-window-item>
                <v-window-item value="account">
                  <OilIndex />
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
                <v-tab value="oilreport">{{ t('د تیلو راپور') }}</v-tab>
                <v-tab value="moneyreport">{{ t('د پیسو راپور') }}</v-tab>
              </v-tabs>

              <!-- bind to reportTab (fixed) -->
              <v-window v-model="reportTab">
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

  // Accounts area
  import AccountsIndex from './Accounts/AccountsIndex.vue';
  import AccountTypesIndex from './Accounts/AccountTypesIndex.vue';

  // Cashflow area
  import IncomesIndex from './IncomeOutcome/IncomesIndex.vue';
  import ExpensesIndex from './IncomeOutcome/ExpensesIndex.vue';

  // Oil area (normalized PascalCase)
  import OilIndex from './oil/oilindex.vue';
  import OilIncomeIndex from './oil/oilIncomeIndex.vue';
  import OilExpenseIndex from './oil/expenseOil.vue';

  // Reports
  import OilReport from './Report/OilReport.vue';
  import MoneyReport from './Report/MoneyReport.vue';

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
