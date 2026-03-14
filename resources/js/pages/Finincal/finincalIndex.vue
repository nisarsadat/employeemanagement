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
          <v-tab value="messages">{{ t('پیغامونه') }}</v-tab>
          <v-tab value="cashflow">{{ t('بودیجه او مصرف') }}</v-tab>
          <v-tab value="oil">{{ t('تیل') }}</v-tab>
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
              </v-tabs>

              <v-window v-model="accountsTab" v-if="auth.can('finincalindex.view')">
                <v-window-item value="list">
                  <AccountsIndex />
                </v-window-item>
              </v-window>
            </v-card>
          </v-window-item>

          <!-- ========== Messages Window (Events + Informations) ========== -->
          <v-window-item value="messages">
            <v-card elevation="2" class="pa-2 rounded-xl">
              <v-tabs
                v-model="messagesTab"
                grow
                class="mb-2 rounded-lg"
                color="primary"
                density="comfortable"
              >
                <v-tab value="informations">{{ t('د اطلاعاتو پیغامونه') }}</v-tab>
                <v-tab value="events">{{ t('د پیښو پیغامونه') }}</v-tab>
              </v-tabs>

              <v-window v-model="messagesTab" v-if="auth.can('finincalindex.view')">
                <v-window-item value="events">
                  <EventsToFinanceTable />
                </v-window-item>

                <v-window-item value="informations">
                  <InformationsToFinanceTable />
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
                <v-tab value="expense">{{ t('مصارف') }}</v-tab>
                <v-tab value="income">{{ t('بودیجه') }}</v-tab>
              </v-tabs>

              <v-window v-model="cashflowTab" v-if="auth.can('finincalindex.view')">
                <v-window-item value="income">
                  <IncomesIndex />
                </v-window-item>
                <v-window-item value="expense">
                  <Finances />
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

              <v-window v-model="oilTab" v-if="auth.can('finincalindex.view')">
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

  // Messages area
  import EventsToFinanceTable from './IncomeOutcome/EventsToFinanceTable.vue';
  import InformationsToFinanceTable from './IncomeOutcome/InformationsToFinanceTable.vue';

  // Cashflow area
  import IncomesIndex from './IncomeOutcome/IncomesIndex.vue';
  import Finances from './IncomeOutcome/Finances.vue';

  // Oil area
  import OilIndex from './oil/oilIndex.vue';
  import OilIncomeIndex from './oil/oilIncomeIndex.vue';
  import OilExpenseIndex from './oil/expenseOil.vue';

  import { useLoginRepository } from '../../stores/LoginRepository';
  const auth = useLoginRepository();

  const { t, locale } = useI18n();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  const drawer = ref(false);

  // Main tabs: accounts | messages | cashflow | oil
  const mainTab = ref('accounts');

  // Sub-tabs
  const accountsTab = ref('list');
  const messagesTab = ref('events'); // ✅ events | informations
  const cashflowTab = ref('income');
  const oilTab = ref('income');
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
