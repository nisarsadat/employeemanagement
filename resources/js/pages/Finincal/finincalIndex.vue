<template>
  <AppBar :title="t('حساب مالي')" @toggle-drawer="drawer = !drawer">
    <template #actions>
      <v-btn icon />
    </template>
  </AppBar>

  <v-container fluid class="bg-bgproject" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Main Tabs -->
    <v-row class="align-start">
      <v-col cols="12">
        <v-tabs v-model="mainTab" fixed-tabs class="mb-2">
          <v-tab color="primary" value="accounts">{{ t('حسابونه') }}</v-tab>
          <v-tab color="primary" value="cashflow">{{ t('عاید او مصرف') }}</v-tab>
          <v-tab color="primary" value="oil">{{ t(' تیل ') }}</v-tab>
        </v-tabs>

        <v-window v-model="mainTab">
          <!-- ========== Accounts Window ========== -->
          <v-window-item value="accounts">
            <v-card elevation="2" class="pa-2">
              <!-- Sub Tabs for Accounts -->
              <v-tabs v-model="accountsTab" grow class="mb-2">
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
            <v-card elevation="2" class="pa-2">
              <!-- Sub Tabs for Income/Expense -->
              <v-tabs v-model="cashflowTab" grow class="mb-2">
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
          <v-window-item value="oil">
            <v-card elevation="2" class="pa-2">
              <!-- Sub Tabs for Income/Expense -->
              <v-tabs v-model="cashflowTab" grow class="mb-2">
                <v-tab value="income">{{ t('آمد تیل') }}</v-tab>
                <v-tab value="expense">{{ t('رفت تیل') }}</v-tab>
                <v-tab value="account">{{ t('حساب تیل') }}</v-tab>
              </v-tabs>

              <v-window v-model="cashflowTab">
                <v-window-item value="income">
                  <oilincomeIndex />
                </v-window-item>
                <v-window-item value="expense">
                  <oilExpenseIndex />
                </v-window-item>
                <v-window-item value="account">
                  <oilIndex />
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

  import oilIndex from './oil/oilindex.vue';
  import oilincomeIndex from './oil/oilIncomeIndex.vue';
  import oilExpenseIndex from './oil/expenseOil.vue';
  const { t, locale } = useI18n();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  const drawer = ref(false);

  // Main tabs: accounts | cashflow
  const mainTab = ref('accounts');

  // Sub-tabs for Accounts window
  const accountsTab = ref('list'); // list | types

  // Sub-tabs for Cashflow window
  const cashflowTab = ref('income'); // income | expense
</script>

<style scoped>
  /* Optional: subtle card padding and spacing already added via classes */
</style>
