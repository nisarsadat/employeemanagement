<template>
  <div class="finance-page">
    <AppBar :title="t('حساب مالي')" @toggle-drawer="drawer = !drawer">
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <v-container fluid class="bg-bgproject py-4 finance-container" :dir="isRtl ? 'rtl' : 'ltr'">
      <div class="finance-sections">
        <!-- 1. Money / Cash Finance -->
        <section class="section-block">
          <div class="section-title-wrap mb-4">
            <h2 class="section-title">{{ t('Money / Cash Finance') }}</h2>
            <p class="section-subtitle">{{ t('Accounts, budget / income, and expenses') }}</p>
          </div>

          <div class="cards-column">
            <!-- Accounts -->
            <v-card
              class="finance-card"
              elevation="2"
              hover
              @click="goTo('/app/finance/accounts')"
            >
              <v-card-text class="text-center py-6">
                <v-icon size="36" color="primary" class="mb-3">mdi-bank</v-icon>
                <div class="text-h6 font-weight-bold mb-2">{{ t('حسابونه') }}</div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ t('د حسابونو لیست او مدیریت') }}
                </div>
              </v-card-text>
            </v-card>

            <!-- Budget / Income -->
            <v-card
              class="finance-card"
              elevation="2"
              hover
              @click="goTo('/app/finance/cashflow/income')"
            >
              <v-card-text class="text-center py-6">
                <v-icon size="36" color="success" class="mb-3">mdi-cash-plus</v-icon>
                <div class="text-h6 font-weight-bold mb-2">{{ t('بودیجه') }}</div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ t('د بودیجې لیست او مدیریت') }}
                </div>
              </v-card-text>
            </v-card>

            <!-- Expenses -->
            <v-card
              class="finance-card"
              elevation="2"
              hover
              @click="goTo('/app/finance/cashflow/expense')"
            >
              <v-card-text class="text-center py-6">
                <v-icon size="36" color="error" class="mb-3">mdi-cash-minus</v-icon>
                <div class="text-h6 font-weight-bold mb-2">{{ t('مصارف') }}</div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ t('د مصارفو لیست او مدیریت') }}
                </div>
              </v-card-text>
            </v-card>
          </div>
        </section>

        <!-- 2. Oil Finance -->
        <section class="section-block">
          <div class="section-title-wrap mb-4">
            <h2 class="section-title">{{ t('Oil Finance') }}</h2>
            <p class="section-subtitle">{{ t('Oil accounts, oil income, and oil expenses') }}</p>
          </div>

          <div class="cards-column">
            <!-- Oil Accounts -->
            <v-card
              class="finance-card"
              elevation="2"
              hover
              @click="goTo('/app/finance/oil/accounts')"
            >
              <v-card-text class="text-center py-6">
                <v-icon size="36" color="primary" class="mb-3">mdi-oil</v-icon>
                <div class="text-h6 font-weight-bold mb-2">{{ t('د تیلو حسابونه') }}</div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ t('د تیلو حسابونو لیست او مدیریت') }}
                </div>
              </v-card-text>
            </v-card>

            <!-- Oil Income -->
            <v-card
              class="finance-card"
              elevation="2"
              hover
              @click="goTo('/app/finance/oil/income')"
            >
              <v-card-text class="text-center py-6">
                <v-icon size="36" color="success" class="mb-3">mdi-truck-delivery-outline</v-icon>
                <div class="text-h6 font-weight-bold mb-2">{{ t('د تیلو داخل') }}</div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ t('د تیلو داخل ثبت او مدیریت') }}
                </div>
              </v-card-text>
            </v-card>

            <!-- Oil Expenses -->
            <v-card
              class="finance-card"
              elevation="2"
              hover
              @click="goTo('/app/finance/oil/expense')"
            >
              <v-card-text class="text-center py-6">
                <v-icon size="36" color="warning" class="mb-3">mdi-fuel</v-icon>
                <div class="text-h6 font-weight-bold mb-2">{{ t('د تیلو مصرف') }}</div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ t('د تیلو مصرف لیست او مدیریت') }}
                </div>
              </v-card-text>
            </v-card>
          </div>
        </section>

        <!-- 3. Messages -->
        <section class="section-block">
          <div class="section-title-wrap mb-4">
            <h2 class="section-title">{{ t('Messages') }}</h2>
            <p class="section-subtitle">{{ t('Information and event messages') }}</p>
          </div>

          <div class="cards-column">
            <!-- Information Messages -->
            <v-card
              class="finance-card"
              elevation="2"
              hover
              @click="goTo('/app/finance/messages/informations')"
            >
              <v-card-text class="text-center py-6">
                <v-icon size="36" color="info" class="mb-3">mdi-information-outline</v-icon>
                <div class="text-h6 font-weight-bold mb-2">{{ t('د اطلاعاتو پیغامونه') }}</div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ t('د اطلاعاتو پیغامونو مشاهده او مدیریت') }}
                </div>
              </v-card-text>
            </v-card>

            <!-- Event Messages -->
            <v-card
              class="finance-card"
              elevation="2"
              hover
              @click="goTo('/app/finance/messages/events')"
            >
              <v-card-text class="text-center py-6">
                <v-icon size="36" color="warning" class="mb-3">mdi-calendar-alert</v-icon>
                <div class="text-h6 font-weight-bold mb-2">{{ t('د پیښو پیغامونه') }}</div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ t('د پیښو پیغامونو مشاهده او مدیریت') }}
                </div>
              </v-card-text>
            </v-card>
          </div>
        </section>
      </div>
    </v-container>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import AppBar from '../../components/AppBar.vue';

const router = useRouter();
const { t, locale } = useI18n();

const drawer = ref(false);
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));
function goTo(path) {
  router.push(path);
}
</script>

<style scoped>
.finance-page {
  min-height: 100vh;
}

.finance-container {
  max-width: 1400px;
  margin: 0 auto;
}

/* main layout */
.finance-sections {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px;
  align-items: start;
}

/* each section */
.section-block {
  background: rgba(255, 255, 255, 0.72);
  backdrop-filter: blur(6px);
  border-radius: 20px;
  padding: 18px;
  height: 100%;
  display: flex;
  flex-direction: column;
  text-align: center;
}

.section-title-wrap {
  border-inline-start: 5px solid rgb(var(--v-theme-primary));
  padding-inline-start: 12px;
}

.section-title {
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.4;
}

.section-subtitle {
  margin: 4px 0 0;
  color: rgba(0, 0, 0, 0.6);
  font-size: 0.92rem;
  line-height: 1.5;
}

/* cards inside each section => column */
.cards-column {
  display: flex;
  flex-direction: column;
  gap: 16px;
  flex: 1;
}

.finance-card {
  cursor: pointer;
  transition: all 0.25s ease;
  min-height: 150px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.finance-card:hover {
  transform: translateY(-4px);
}

/* responsive */
@media (max-width: 1264px) {
  .finance-sections {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 900px) {
  .finance-sections {
    grid-template-columns: 1fr;
  }

  .section-block {
    padding: 16px;
  }

  .finance-card {
    min-height: 135px;
  }

  .section-title {
    font-size: 1.05rem;
  }

  .section-subtitle {
    font-size: 0.88rem;
  }
}

:deep(.v-card) {
  border-radius: 16px;
}
</style>