<!-- src/pages/Dashboard.vue -->
<template>
  <div class="dashboard-container" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- App Bar -->
    <AppBar :title="t('داشبورد')" :subtitle="t('خلاصه عملکرد سیستم')" />

    <!-- Error Alert -->
    <transition name="slide-down">
      <v-alert
        v-if="repo.error"
        type="error"
        variant="tonal"
        rounded="lg"
        class="error-alert mx-4 mt-4"
        :text="readableError"
        icon="mdi-alert"
        border="start"
        closable
        @click:close="repo.error = null"
      />
    </transition>

    <!-- Stats Cards Grid (2 rows / 3 cards each on lg+) -->
    <div class="stats-grid px-4 py-6">
      <v-row dense>
        <!-- Employees -->
        <v-col cols="12" sm="6" md="4" lg="4">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="$router.push({ name: 'employees' })"
            :disabled="repo.loading"
          >
            <div class="stat-accent primary"></div>

            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between mb-4">
                <div class="card-icon-wrapper primary">
                  <v-icon color="primary" size="22">mdi-account-group</v-icon>
                </div>

                <v-chip
                  v-if="Number(employeesGrowth) > 0"
                  size="x-small"
                  variant="tonal"
                  color="success"
                >
                  <v-icon start size="14">mdi-trending-up</v-icon>
                  +{{ employeesGrowth }}%
                </v-chip>
              </div>

              <div class="text-caption text-medium-emphasis mb-2">{{ t('کارمندان') }}</div>

              <div class="d-flex align-baseline justify-space-between">
                <div class="text-h3 font-weight-black">{{ employeesTotal.toLocaleString() }}</div>
                <div class="mini-meta">
                  <v-icon size="16" color="primary">mdi-account-multiple</v-icon>
                  <span class="text-caption">{{ t('کل') }}</span>
                </div>
              </div>

              <div class="mt-4 d-flex align-center justify-space-between">
                <div class="d-flex align-center gap-2">
                  <div class="dot success"></div>
                  <span class="text-caption">{{ t('فعال') }}</span>
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ t('رتبه‌بندی در جدول') }}
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Resources (Active/Inactive clickable) -->
        <v-col cols="12" sm="6" md="4" lg="4">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            :disabled="repo.loading"
          >
            <div class="stat-accent teal"></div>

            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between mb-4">
                <div class="card-icon-wrapper teal">
                  <v-icon color="teal" size="22">mdi-database</v-icon>
                </div>

                <v-chip size="x-small" variant="tonal" color="teal">
                  {{ t('منابع') }}
                </v-chip>
              </div>

              <div class="text-caption text-medium-emphasis mb-2">{{ t('منابع') }}</div>

              <div class="d-flex align-baseline justify-space-between mb-3">
                <div class="text-h3 font-weight-black">{{ resourcesTotal.toLocaleString() }}</div>
                <div class="mini-meta">
                  <v-icon size="16" color="teal">mdi-chart-donut</v-icon>
                  <span class="text-caption">{{ resourcesActivePercentage }}%</span>
                </div>
              </div>

              <v-progress-linear
                :model-value="resourcesActivePercentage"
                height="7"
                rounded
                color="teal"
                bg-color="rgba(var(--v-theme-teal), 0.12)"
                class="mb-4"
              />

              <!-- Active / Inactive chips (clickable routes) -->
              <div class="d-flex flex-wrap gap-2">
                <v-chip
                  size="small"
                  variant="tonal"
                  color="success"
                  class="clickable-chip"
                  :disabled="repo.loading"
                  @click.stop="goResources('active')"
                >
                  <v-icon start size="16">mdi-check-circle</v-icon>
                  {{ t('فعال') }}: {{ num(resourcesActive) }}
                </v-chip>

                <v-chip
                  size="small"
                  variant="tonal"
                  color="error"
                  class="clickable-chip"
                  :disabled="repo.loading"
                  @click.stop="goResources('inactive')"
                >
                  <v-icon start size="16">mdi-close-circle</v-icon>
                  {{ t('غیرفعال') }}: {{ num(resourcesInactive) }}
                </v-chip>

                <v-spacer />

                <v-btn
                  size="small"
                  variant="text"
                  color="teal"
                  class="px-1"
                  :disabled="repo.loading"
                  @click.stop="goResources()"
                >
                  {{ t('مشاهده') }}
                  <v-icon end size="16">mdi-arrow-left</v-icon>
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Informations -->
        <v-col cols="12" sm="6" md="4" lg="4">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="$router.push({ name: 'informations' })"
            :disabled="repo.loading"
          >
            <div class="stat-accent indigo"></div>

            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between mb-4">
                <div class="card-icon-wrapper indigo">
                  <v-icon color="indigo" size="22">mdi-file-document-multiple</v-icon>
                </div>

                <v-chip
                  size="x-small"
                  variant="tonal"
                  :color="Number(informationsGrowth) >= 0 ? 'success' : 'error'"
                >
                  <v-icon start size="14">
                    {{ Number(informationsGrowth) >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}
                  </v-icon>
                  {{ Number(informationsGrowth) >= 0 ? '+' : '' }}{{ informationsGrowth }}%
                </v-chip>
              </div>

              <div class="text-caption text-medium-emphasis mb-2">{{ t('اطلاعات') }}</div>

              <div class="d-flex align-baseline justify-space-between">
                <div class="text-h3 font-weight-black">
                  {{ informationsTotal.toLocaleString() }}
                </div>
                <div class="mini-meta">
                  <v-icon size="16" color="indigo">mdi-file-document</v-icon>
                  <span class="text-caption">{{ t('رکورد') }}</span>
                </div>
              </div>

              <div class="mt-4 d-flex align-center justify-space-between">
                <div class="d-flex align-center gap-2">
                  <div
                    class="dot"
                    :class="Number(informationsGrowth) >= 0 ? 'success' : 'error'"
                  ></div>
                  <span class="text-caption">
                    {{ Number(informationsGrowth) >= 0 ? t('رو به رشد') : t('کاهش') }}
                  </span>
                </div>
                <div class="text-caption text-medium-emphasis">{{ t('گزارش کامل') }}</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Events -->
        <v-col cols="12" sm="6" md="4" lg="4">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="$router.push({ name: 'events' })"
            :disabled="repo.loading"
          >
            <div class="stat-accent cyan"></div>

            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between mb-4">
                <div class="card-icon-wrapper cyan">
                  <v-icon color="teal-darken-1" size="22">mdi-calendar-multiple</v-icon>
                </div>

                <div class="activity-dots">
                  <div class="activity-dot active"></div>
                  <div class="activity-dot"></div>
                  <div class="activity-dot active"></div>
                </div>
              </div>

              <div class="text-caption text-medium-emphasis mb-2">{{ t('رویدادها') }}</div>

              <div class="d-flex align-baseline justify-space-between">
                <div class="text-h3 font-weight-black">{{ eventsTotal.toLocaleString() }}</div>
                <div class="mini-meta">
                  <v-icon size="16" color="teal-darken-1">mdi-calendar</v-icon>
                  <span class="text-caption">{{ t('کل') }}</span>
                </div>
              </div>

              <div class="mt-4 d-flex align-center justify-space-between">
                <div class="d-flex align-center gap-2">
                  <div class="dot success"></div>
                  <span class="text-caption">{{ t('فعال') }}</span>
                </div>
                <div class="text-caption text-medium-emphasis">{{ t('جاری') }}</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Money Balance -->
        <v-col cols="12" sm="6" md="4" lg="4">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="$router.push({ name: 'finance' })"
            :disabled="repo.loading"
          >
            <div class="stat-accent success"></div>

            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between mb-4">
                <div class="card-icon-wrapper success">
                  <v-icon color="success" size="22">mdi-cash-multiple</v-icon>
                </div>

                <v-chip
                  size="x-small"
                  variant="tonal"
                  :color="moneyBalance >= 0 ? 'success' : 'error'"
                >
                  <v-icon start size="14">{{
                    moneyBalance >= 0 ? 'mdi-plus' : 'mdi-minus'
                  }}</v-icon>
                  {{ num(moneyBalance) }}
                </v-chip>
              </div>

              <div class="text-caption text-medium-emphasis mb-2">{{ t('بیلانس مالی') }}</div>

              <div class="d-flex align-baseline justify-space-between mb-3">
                <div class="text-h3 font-weight-black">{{ num(moneyBalance) }}</div>
                <div class="mini-meta">
                  <v-icon size="16" color="success">mdi-currency-usd</v-icon>
                  <span class="text-caption">{{ t('افغانی') }}</span>
                </div>
              </div>

              <div class="d-flex justify-space-between mt-2">
                <div class="d-flex align-center gap-1">
                  <v-icon size="14" color="success">mdi-arrow-down</v-icon>
                  <span class="text-caption">{{ num(moneyIncomeTotal) }}</span>
                </div>
                <div class="d-flex align-center gap-1">
                  <v-icon size="14" color="error">mdi-arrow-up</v-icon>
                  <span class="text-caption">{{ num(moneyExpenseTotal) }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Oil Balance -->
        <v-col cols="12" sm="6" md="4" lg="4">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="$router.push({ name: 'oil' })"
            :disabled="repo.loading"
          >
            <div class="stat-accent warning"></div>

            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between mb-4">
                <div class="card-icon-wrapper warning">
                  <v-icon color="warning" size="22">mdi-fuel</v-icon>
                </div>

                <v-chip
                  size="x-small"
                  variant="tonal"
                  :color="oilBalance >= 0 ? 'warning' : 'error'"
                >
                  <v-icon start size="14">{{ oilBalance >= 0 ? 'mdi-plus' : 'mdi-minus' }}</v-icon>
                  {{ num(oilBalance) }}
                </v-chip>
              </div>

              <div class="text-caption text-medium-emphasis mb-2">{{ t('بیلانس تیل') }}</div>

              <div class="d-flex align-baseline justify-space-between mb-3">
                <div class="text-h3 font-weight-black">{{ num(oilBalance) }}</div>
                <div class="mini-meta">
                  <v-icon size="16" color="warning">mdi-water</v-icon>
                  <span class="text-caption">{{ t('لیتر') }}</span>
                </div>
              </div>

              <div class="d-flex justify-space-between mt-2">
                <div class="d-flex align-center gap-1">
                  <v-icon size="14" color="success">mdi-arrow-down</v-icon>
                  <span class="text-caption">{{ num(oilIncomeTotal) }}</span>
                </div>
                <div class="d-flex align-center gap-1">
                  <v-icon size="14" color="error">mdi-arrow-up</v-icon>
                  <span class="text-caption">{{ num(oilExpenseTotal) }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Main Content -->
    <div class="main-content px-4 pb-10">
      <v-row>
        <!-- Left Column - Employee Ranking -->
        <v-col cols="12" lg="8">
          <v-card rounded="xl" class="mb-4 fancy-card">
            <!-- Header -->
            <div class="table-header pa-4">
              <div class="d-flex align-center justify-space-between flex-wrap gap-3">
                <div>
                  <div class="d-flex align-center gap-2">
                    <v-avatar size="34" class="bg-soft-primary">
                      <v-icon color="primary">mdi-trophy</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-h6 font-weight-black">{{ t('درجه‌بندی کارمندان') }}</div>
                      <div class="text-caption text-medium-emphasis">
                        {{ t('بر اساس امتیاز (رویداد + منابع + اطلاعات)') }}
                      </div>
                    </div>
                  </div>

                  <div class="d-flex align-center gap-4 mt-3">
                    <v-chip size="small" variant="tonal" color="primary">
                      <v-icon start size="16">mdi-account-multiple</v-icon>
                      {{ filteredEmployees.length }} {{ t('کارمند') }}
                    </v-chip>

                    <v-chip size="small" variant="tonal" color="amber">
                      <v-icon start size="16">mdi-crown</v-icon>
                      {{ t('بهترین') }}: {{ getTopEmployeeName }}
                    </v-chip>
                  </div>
                </div>

                <div class="d-flex align-center flex-wrap gap-2">
                  <v-text-field
                    v-model.trim="search"
                    density="compact"
                    variant="outlined"
                    rounded="xl"
                    hide-details
                    :placeholder="t('جستجوی کارمند...')"
                    clearable
                    class="search-field"
                  >
                    <template #prepend-inner>
                      <v-icon size="20" color="primary">mdi-magnify</v-icon>
                    </template>
                  </v-text-field>

                  <v-select
                    v-model="sortKey"
                    density="compact"
                    variant="outlined"
                    rounded="xl"
                    hide-details
                    class="sort-field"
                    :items="sortOptions"
                    item-title="title"
                    item-value="value"
                    :label="t('مرتب‌سازی')"
                  />

                  <v-btn
                    :loading="repo.loading"
                    variant="tonal"
                    color="primary"
                    rounded="xl"
                    @click="refreshData"
                    :disabled="repo.loading"
                    class="refresh-btn"
                  >
                    <v-icon>mdi-refresh</v-icon>
                  </v-btn>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="table-shell">
              <v-data-table
                :items="pagedEmployees"
                :headers="employeeHeaders"
                :loading="repo.loading"
                class="employees-table cool-table"
                item-key="id"
                fixed-header
                height="520"
                :items-per-page="itemsPerPage"
                hide-default-footer
              >
                <!-- Rank -->
                <template #item.rank="{ item }">
                  <div class="rank-cell">
                    <div class="rank-badge" :class="`rank-${item.rank}`">
                      <v-icon v-if="item.rank === 1" size="16">mdi-trophy</v-icon>
                      <v-icon v-else-if="item.rank === 2" size="16">mdi-medal</v-icon>
                      <v-icon v-else-if="item.rank === 3" size="16">mdi-medal-outline</v-icon>
                      <span v-else>{{ item.rank }}</span>
                    </div>
                  </div>
                </template>

                <!-- Employee -->
                <template #item.name="{ item }">
                  <div class="employee-cell" @click="viewEmployee(item)">
                    <v-avatar size="40" class="avatar-glow mr-3">
                      <span class="text-white text-body-2 font-weight-bold">
                        {{ getInitials(item.name) }}
                      </span>
                    </v-avatar>

                    <div class="min-w-0">
                      <div class="employee-name text-body-2 font-weight-bold text-truncate">
                        {{ item.name || '—' }}
                      </div>

                      <div class="d-flex align-center gap-2 mt-1">
                        <v-chip size="x-small" variant="tonal" color="primary">
                          {{ t('کود') }}: {{ item.code_number || '—' }}
                        </v-chip>

                        <v-tooltip location="top">
                          <template #activator="{ props }">
                            <v-btn
                              v-bind="props"
                              icon
                              size="x-small"
                              variant="text"
                              color="primary"
                              @click.stop="copyText(String(item.code_number || ''))"
                            >
                              <v-icon size="16">mdi-content-copy</v-icon>
                            </v-btn>
                          </template>
                          <span>{{ t('کپی کود') }}</span>
                        </v-tooltip>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Metrics -->
                <template #item.resources_count="{ item }">
                  <div class="metric-chip">
                    <v-icon size="16" color="primary">mdi-folder</v-icon>
                    <span class="font-weight-bold">{{ num(item.resources_count) }}</span>
                  </div>
                </template>

                <template #item.informations_count="{ item }">
                  <div class="metric-chip">
                    <v-icon size="16" color="indigo">mdi-file-document</v-icon>
                    <span class="font-weight-bold">{{ num(item.informations_count) }}</span>
                  </div>
                </template>

                <template #item.events_count="{ item }">
                  <div class="metric-chip">
                    <v-icon size="16" color="teal">mdi-calendar</v-icon>
                    <span class="font-weight-bold">{{ num(item.events_count) }}</span>
                  </div>
                </template>

                <!-- Score -->
                <template #item.score="{ item }">
                  <div class="score-cell">
                    <v-progress-linear
                      :model-value="getScorePercentage(item.score)"
                      height="10"
                      rounded
                      :color="getScoreColor(item.score)"
                      bg-color="rgba(0,0,0,0.06)"
                      class="score-bar"
                    />
                    <div class="score-meta">
                      <div class="score-number">{{ num(item.score) }}</div>
                      <div class="score-label text-caption text-medium-emphasis">
                        {{ getScoreLabel(item.score) }}
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Actions -->
                <template #item.actions="{ item }">
                  <div class="d-flex align-center justify-center gap-1">
                    <v-btn
                      icon
                      size="small"
                      variant="text"
                      color="primary"
                      @click="viewEmployee(item)"
                    >
                      <v-icon size="20">mdi-eye</v-icon>
                    </v-btn>
                  </div>
                </template>

                <!-- Empty -->
                <template #no-data>
                  <div class="empty-table pa-10 text-center">
                    <v-icon size="72" color="grey-lighten-2" class="mb-4"
                      >mdi-account-search</v-icon
                    >
                    <div class="text-h6 text-medium-emphasis mb-2">{{ t('کارمندی یافت نشد') }}</div>
                    <div class="text-caption text-medium-emphasis">
                      {{ t('هیچ کارمندی مطابق با جستجوی شما وجود ندارد') }}
                    </div>
                  </div>
                </template>

                <!-- Loading -->
                <template #loading>
                  <div class="loading-table pa-10 text-center">
                    <v-progress-circular indeterminate color="primary" size="54" class="mb-4" />
                    <div class="text-h6 text-medium-emphasis">{{ t('در حال بارگذاری...') }}</div>
                  </div>
                </template>
              </v-data-table>
            </div>

            <!-- Footer -->
            <div class="table-footer pa-3">
              <div class="d-flex align-center justify-space-between flex-wrap gap-2">
                <div class="text-caption text-medium-emphasis">
                  {{ t('نمایش') }} {{ pageFrom }}-{{ pageTo }} {{ t('از') }}
                  {{ filteredEmployees.length }}
                </div>

                <v-pagination
                  v-model="currentPage"
                  :length="pageCount"
                  total-visible="6"
                  rounded="circle"
                  density="compact"
                />
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- Right Column -->
        <v-col cols="12" lg="4">
          <FormatsPieChart
            class="mb-4"
            :title="t('توزیع فرم‌ها')"
            :subtitle="t('بر اساس مناطق مختلف')"
            :total="formatsTotal"
            :total-label="t('کل فرم‌ها')"
            :empty-text="t('داده‌ای موجود نیست')"
            :data="formatsPieData"
            :height="280"
            donut
            :show-legend="true"
            :loading="repo.loading"
            :interactive="true"
            @refresh="refreshData"
            @zone-click="onZoneClick"
          />

          <v-card rounded="xl" class="fancy-card">
            <v-card-text class="pa-0">
              <div class="zones-header pa-4">
                <div class="d-flex align-center justify-space-between mb-2">
                  <div class="d-flex align-center gap-2">
                    <v-avatar size="34" class="bg-soft-primary">
                      <v-icon color="primary">mdi-map-marker</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-h6 font-weight-black">{{ t('آمار مناطق') }}</div>
                      <div class="text-caption text-medium-emphasis">
                        {{ t('بر اساس تعداد فرم') }}
                      </div>
                    </div>
                  </div>

                  <v-chip size="small" variant="tonal" color="primary">
                    {{ zones.length }} {{ t('منطقه') }}
                  </v-chip>
                </div>
              </div>

              <div class="zones-list pa-2">
                <div
                  v-for="(zone, index) in zonesTop"
                  :key="`${zone.zone}-${index}`"
                  class="zone-item pa-3"
                  @click="viewZoneDetails(zone)"
                >
                  <div class="d-flex align-center gap-3">
                    <div class="zone-rank">
                      <div class="rank-number" :class="`rank-${index + 1}`">{{ index + 1 }}</div>
                    </div>

                    <div class="zone-info flex-grow-1">
                      <div class="zone-name text-body-2 font-weight-bold mb-2">
                        {{ zone.zone || t('نامشخص') }}
                      </div>

                      <v-progress-linear
                        :model-value="getZonePercentage(zone.total)"
                        height="7"
                        rounded
                        color="primary"
                        bg-color="rgba(var(--v-theme-primary), 0.12)"
                      />
                    </div>

                    <div class="zone-total text-body-2 font-weight-black">
                      {{ num(zone.total) }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="zones-footer pa-3">
                <div class="d-flex align-center justify-space-between">
                  <div class="text-caption text-medium-emphasis">
                    {{ t('نمایش') }} {{ zonesTop.length }} {{ t('از') }} {{ zones.length }}
                  </div>

                  <v-btn
                    size="small"
                    variant="text"
                    color="primary"
                    :to="{ name: 'yearlyfoulreports' }"
                  >
                    {{ t('مشاهده همه') }}
                    <v-icon end size="16">mdi-arrow-left</v-icon>
                  </v-btn>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Refresh FAB -->
    <v-fab location="bottom end" class="fab-refresh" :style="{ bottom: mobile ? '80px' : '24px' }">
      <v-btn fab color="primary" size="small" :loading="repo.loading" @click="refreshData">
        <v-icon>mdi-refresh</v-icon>
      </v-btn>
    </v-fab>
  </div>
</template>

<script setup>
  import { computed, onMounted, ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useDisplay } from 'vuetify';
  import { useRouter } from 'vue-router';
  import AppBar from '@/components/AppBar.vue';
  import FormatsPieChart from '@/components/charts/FormatZonePieChart.vue';
  import { useDashboardRepository } from '@/stores/DashboardRepository';

  const { t, locale } = useI18n();
  const { mobile } = useDisplay();
  const router = useRouter();

  const isRtl = computed(() => String(locale.value).toLowerCase().startsWith('fa'));

  const repo = useDashboardRepository();

  const search = ref('');
  const currentPage = ref(1);
  const itemsPerPage = 10;

  const sortKey = ref('score_desc');
  const sortOptions = computed(() => [
    { title: t('امتیاز (زیاد → کم)'), value: 'score_desc' },
    { title: t('امتیاز (کم → زیاد)'), value: 'score_asc' },
    { title: t('رویدادها (زیاد → کم)'), value: 'events_desc' },
    { title: t('منابع (زیاد → کم)'), value: 'resources_desc' },
    { title: t('اطلاعات (زیاد → کم)'), value: 'informations_desc' },
    { title: t('نام (A → Z)'), value: 'name_asc' },
  ]);

  onMounted(async () => {
    if (!repo.dashboard) await repo.fetchDashboard();
  });

  async function refreshData() {
    await repo.fetchDashboard();
  }

  function viewEmployee(employee) {
    router.push({ name: 'employee-details', params: { id: employee.id } });
  }

  /**
   * Resources routing
   * - Default: resources list
   * - Active/Inactive: resources list with query filter
   * NOTE: if your app uses a different query key, just change "status" here.
   */
  function goResources(status = null) {
    const query = status ? { status } : {};
    router.push({ name: 'resources', query });
  }

  function onZoneClick(zone) {
    router.push({
      name: 'zone-reports',
      query: { zone: zone.zone },
    });
  }

  function viewZoneDetails(zone) {
    router.push({
      name: 'zone-details',
      query: { zone: zone.zone },
    });
  }

  const readableError = computed(() => {
    const e = repo.error;
    if (!e) return '';
    return e?.response?.data?.message || e?.message || String(e);
  });

  /* ===========================
Helpers
=========================== */
  function num(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n.toLocaleString() : '0';
  }

  function getInitials(name) {
    if (!name) return '—';
    return String(name)
      .split(' ')
      .filter(Boolean)
      .map((w) => w[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  }

  async function copyText(text) {
    try {
      if (!text) return;
      await navigator.clipboard.writeText(text);
    } catch (_) {
      // ignore
    }
  }

  function getScoreLabel(score) {
    const percentage = getScorePercentage(score);
    if (percentage >= 80) return t('عالی');
    if (percentage >= 60) return t('خوب');
    if (percentage >= 40) return t('متوسط');
    return t('نیازمند بهبود');
  }

  /* ===========================
Growth
=========================== */
  const employeesGrowth = computed(() => {
    const growth = repo.dashboard?.growth?.employees;
    if (growth !== undefined) return growth;

    const trend = repo.employees?.trend;
    if (trend?.current && trend?.previous) {
      const prev = Number(trend.previous) || 0;
      if (!prev) return 0;
      return (((Number(trend.current) - prev) / prev) * 100).toFixed(1);
    }
    return 0;
  });

  const informationsGrowth = computed(() => {
    const growth = repo.dashboard?.growth?.informations;
    if (growth !== undefined) return growth;

    const trend = repo.informations?.trend;
    if (trend?.current && trend?.previous) {
      const prev = Number(trend.previous) || 0;
      if (!prev) return 0;
      return (((Number(trend.current) - prev) / prev) * 100).toFixed(1);
    }
    return 0;
  });

  /* ===========================
Stats
=========================== */
  const employeesTotal = computed(() => repo.employees?.total ?? repo.employeesFlat?.length ?? 0);

  const resourcesTotal = computed(() => Number(repo.resources?.total ?? 0));
  const resourcesActive = computed(() => Number(repo.resources?.active ?? 0));
  const resourcesInactive = computed(() => Number(repo.resources?.inactive ?? 0));

  const informationsTotal = computed(() => Number(repo.informations?.total ?? 0));
  const eventsTotal = computed(() => Number(repo.events?.total ?? 0));

  const resourcesActivePercentage = computed(() => {
    if (resourcesTotal.value === 0) return 0;
    return Math.round((resourcesActive.value / resourcesTotal.value) * 100);
  });

  const moneyIncomeTotal = computed(() => Number(repo.money?.income_total ?? 0));
  const moneyExpenseTotal = computed(() => Number(repo.money?.expensed_total ?? 0));
  const moneyBalance = computed(() =>
    Number(repo.money?.balance ?? moneyIncomeTotal.value - moneyExpenseTotal.value)
  );

  const oilIncomeTotal = computed(() => Number(repo.oil?.income_total ?? 0));
  const oilExpenseTotal = computed(() => Number(repo.oil?.expensed_total ?? 0));
  const oilBalance = computed(() =>
    Number(repo.oil?.balance ?? oilIncomeTotal.value - oilExpenseTotal.value)
  );

  /* ===========================
Employees Logic
=========================== */
  function employeeScoreNumber(item) {
    if (item?.score != null && Number.isFinite(Number(item.score))) return Number(item.score);
    const events = Number(item?.events_count) || 0;
    const resources = Number(item?.resources_count) || 0;
    const informations = Number(item?.informations_count) || 0;
    return events + resources + informations;
  }

  const baseEmployees = computed(() => {
    const list = Array.isArray(repo.employees?.flat_sorted)
      ? repo.employees.flat_sorted
      : Array.isArray(repo.dashboard?.employees_flat)
      ? repo.dashboard.employees_flat
      : [];

    return [...list].map((x) => ({
      ...x,
      score: employeeScoreNumber(x),
    }));
  });

  const filteredEmployees = computed(() => {
    const q = search.value?.trim()?.toLowerCase();
    const arr = baseEmployees.value;

    const filtered = !q
      ? arr
      : arr.filter((e) => {
          const name = String(e?.name || '').toLowerCase();
          const code = String(e?.code_number || '').toLowerCase();
          return name.includes(q) || code.includes(q);
        });

    // sort
    const sorted = [...filtered].sort((a, b) => {
      const ak = sortKey.value;
      if (ak === 'score_desc') return employeeScoreNumber(b) - employeeScoreNumber(a);
      if (ak === 'score_asc') return employeeScoreNumber(a) - employeeScoreNumber(b);

      if (ak === 'events_desc')
        return (Number(b?.events_count) || 0) - (Number(a?.events_count) || 0);
      if (ak === 'resources_desc')
        return (Number(b?.resources_count) || 0) - (Number(a?.resources_count) || 0);
      if (ak === 'informations_desc')
        return (Number(b?.informations_count) || 0) - (Number(a?.informations_count) || 0);

      if (ak === 'name_asc') return String(a?.name || '').localeCompare(String(b?.name || ''));
      return 0;
    });

    // rank after sorting
    return sorted.map((x, i) => ({
      ...x,
      rank: i + 1,
      score: employeeScoreNumber(x),
    }));
  });

  const maxEmployeeScore = computed(() => {
    const arr = filteredEmployees.value;
    if (!arr.length) return 100;
    const m = Math.max(...arr.map((e) => employeeScoreNumber(e)));
    return Math.max(m, 100);
  });

  function getScorePercentage(score) {
    const s = Number(score) || 0;
    const maxScore = maxEmployeeScore.value || 100;
    return Math.min((s / maxScore) * 100, 100);
  }

  function getScoreColor(score) {
    const percentage = getScorePercentage(score);
    if (percentage >= 80) return 'success';
    if (percentage >= 60) return 'primary';
    if (percentage >= 40) return 'warning';
    return 'error';
  }

  const getTopEmployeeName = computed(() => filteredEmployees.value[0]?.name || '—');

  const employeeHeaders = computed(() => [
    { title: t('رتبه'), key: 'rank', width: 90, sortable: false, align: 'center' },
    { title: t('کارمند'), key: 'name', sortable: false },
    { title: t('منابع'), key: 'resources_count', width: 110, sortable: false, align: 'center' },
    {
      title: t('اطلاعات'),
      key: 'informations_count',
      width: 110,
      sortable: false,
      align: 'center',
    },
    { title: t('رویدادها'), key: 'events_count', width: 110, sortable: false, align: 'center' },
    { title: t('امتیاز'), key: 'score', width: 220, sortable: false, align: 'center' },
    { title: t('عملیات'), key: 'actions', width: 70, sortable: false, align: 'center' },
  ]);

  // Pagination for table
  const pageCount = computed(() =>
    Math.max(1, Math.ceil(filteredEmployees.value.length / itemsPerPage))
  );

  const pagedEmployees = computed(() => {
    const page = Math.min(Math.max(1, currentPage.value), pageCount.value);
    const start = (page - 1) * itemsPerPage;
    return filteredEmployees.value.slice(start, start + itemsPerPage);
  });

  const pageFrom = computed(() => {
    if (!filteredEmployees.value.length) return 0;
    return (Math.min(currentPage.value, pageCount.value) - 1) * itemsPerPage + 1;
  });
  const pageTo = computed(() =>
    Math.min(pageFrom.value + itemsPerPage - 1, filteredEmployees.value.length)
  );

  /* ===========================
Formats By Zone Logic
=========================== */
  const zones = computed(() => {
    const z = repo.formats_by_zone?.zones;
    return Array.isArray(z) ? z : [];
  });

  const formatsTotal = computed(() =>
    zones.value.reduce((sum, z) => sum + Number(z?.total || 0), 0)
  );

  const zonesTop = computed(() =>
    zones.value.slice(0, mobile.value ? 5 : 8).map((zone) => ({
      ...zone,
      percentage: formatsTotal.value > 0 ? (Number(zone.total) / formatsTotal.value) * 100 : 0,
    }))
  );

  function getZonePercentage(total) {
    if (formatsTotal.value === 0) return 0;
    return (Number(total) / formatsTotal.value) * 100;
  }

  const formatsPieData = computed(() => {
    const list = zones.value
      .map((z) => ({
        value: Number(z?.total) || 0,
        name: z?.zone || t('نامشخص'),
        itemStyle: { color: getZoneColor(z?.zone) },
      }))
      .filter((x) => x.value > 0)
      .sort((a, b) => b.value - a.value);

    if (list.length <= 10) return list;

    const top = list.slice(0, 9);
    const restSum = list.slice(9).reduce((s, x) => s + x.value, 0);
    if (restSum > 0) top.push({ name: t('سایر'), value: restSum, itemStyle: { color: '#9E9E9E' } });
    return top;
  });

  function getZoneColor(zoneName) {
    const colors = [
      '#2196F3',
      '#4CAF50',
      '#FF9800',
      '#9C27B0',
      '#00BCD4',
      '#8BC34A',
      '#FF5722',
      '#673AB7',
      '#009688',
      '#E91E63',
    ];
    const s = String(zoneName || '');
    let hash = 0;
    for (let i = 0; i < s.length; i++) hash += s.charCodeAt(i);
    return colors[hash % colors.length];
  }
</script>

<style scoped>
  .dashboard-container {
    min-height: 100vh;
    background: radial-gradient(
        1200px 600px at 0% 0%,
        rgba(var(--v-theme-primary), 0.06),
        transparent 60%
      ),
      radial-gradient(900px 500px at 100% 10%, rgba(var(--v-theme-teal), 0.06), transparent 55%),
      #f8f9fa;
  }

  /* Error Alert */
  .error-alert {
    position: sticky;
    top: 16px;
    z-index: 100;
  }

  .slide-down-enter-active,
  .slide-down-leave-active {
    transition: all 0.25s ease;
  }
  .slide-down-enter-from,
  .slide-down-leave-to {
    opacity: 0;
    transform: translateY(-16px);
  }

  /* Cards */
  .stat-card {
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.08);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
  }

  .stat-accent {
    position: absolute;
    inset: 0 auto auto 0;
    width: 100%;
    height: 4px;
    opacity: 0.9;
  }
  .stat-accent.primary {
    background: linear-gradient(
      90deg,
      rgba(var(--v-theme-primary), 1),
      rgba(var(--v-theme-secondary), 1)
    );
  }
  .stat-accent.teal {
    background: linear-gradient(90deg, rgba(var(--v-theme-teal), 1), rgba(var(--v-theme-cyan), 1));
  }
  .stat-accent.indigo {
    background: linear-gradient(
      90deg,
      rgba(var(--v-theme-indigo), 1),
      rgba(var(--v-theme-primary), 1)
    );
  }
  .stat-accent.cyan {
    background: linear-gradient(
      90deg,
      rgba(var(--v-theme-teal), 1),
      rgba(var(--v-theme-primary), 1)
    );
  }
  .stat-accent.success {
    background: linear-gradient(
      90deg,
      rgba(var(--v-theme-success), 1),
      rgba(var(--v-theme-primary), 1)
    );
  }
  .stat-accent.warning {
    background: linear-gradient(
      90deg,
      rgba(var(--v-theme-warning), 1),
      rgba(var(--v-theme-error), 0.9)
    );
  }

  .stat-card-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.12) !important;
    border-color: rgba(var(--v-theme-primary), 0.22);
  }

  .card-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--v-theme-primary), 0.08);
  }
  .card-icon-wrapper.primary {
    background: rgba(var(--v-theme-primary), 0.1);
  }
  .card-icon-wrapper.teal {
    background: rgba(var(--v-theme-teal), 0.1);
  }
  .card-icon-wrapper.indigo {
    background: rgba(var(--v-theme-indigo), 0.1);
  }
  .card-icon-wrapper.cyan {
    background: rgba(var(--v-theme-teal), 0.1);
  }
  .card-icon-wrapper.success {
    background: rgba(var(--v-theme-success), 0.1);
  }
  .card-icon-wrapper.warning {
    background: rgba(var(--v-theme-warning), 0.12);
  }

  .mini-meta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(0, 0, 0, 0.04);
  }

  .dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.25);
  }
  .dot.success {
    background: rgba(var(--v-theme-success), 1);
  }
  .dot.error {
    background: rgba(var(--v-theme-error), 1);
  }

  .activity-dots {
    display: flex;
    gap: 4px;
  }
  .activity-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: rgba(var(--v-theme-primary), 0.22);
  }
  .activity-dot.active {
    background: rgba(var(--v-theme-primary), 1);
  }

  .clickable-chip {
    cursor: pointer;
  }

  /* Fancy cards */
  .fancy-card {
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.06);
    overflow: hidden;
  }
  .bg-soft-primary {
    background: rgba(var(--v-theme-primary), 0.1);
  }

  /* Table */
  .table-header {
    background: linear-gradient(
      90deg,
      rgba(var(--v-theme-primary), 0.06),
      rgba(var(--v-theme-teal), 0.04)
    );
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  }

  .table-shell {
    background: white;
  }

  .search-field {
    width: 260px;
  }
  .sort-field {
    width: 210px;
  }
  .refresh-btn {
    min-width: 42px;
    width: 42px;
    height: 42px;
  }

  .cool-table :deep(.v-data-table__wrapper) {
    border-radius: 0;
  }

  .cool-table :deep(thead th) {
    position: sticky;
    top: 0;
    z-index: 2;
    background: white !important;
    font-weight: 800 !important;
    letter-spacing: 0.2px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
  }

  .cool-table :deep(.v-data-table__tr) {
    transition: background-color 0.16s ease, transform 0.16s ease;
  }

  .cool-table :deep(.v-data-table__tr:hover) {
    background: rgba(var(--v-theme-primary), 0.03) !important;
  }

  .cool-table :deep(.v-data-table__td) {
    padding: 14px 16px !important;
    border-bottom: 1px dashed rgba(0, 0, 0, 0.08);
  }

  .rank-cell {
    display: flex;
    justify-content: center;
  }
  .rank-badge {
    width: 40px;
    height: 40px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    background: rgba(var(--v-theme-primary), 0.1);
    color: rgba(var(--v-theme-primary), 0.95);
  }

  .rank-1 {
    background: linear-gradient(135deg, #ffd700, #ffa500);
    color: white;
  }
  .rank-2 {
    background: linear-gradient(135deg, #c0c0c0, #808080);
    color: white;
  }
  .rank-3 {
    background: linear-gradient(135deg, #cd7f32, #a0522d);
    color: white;
  }

  .employee-cell {
    display: flex;
    align-items: center;
    cursor: pointer;
    min-width: 0;
  }

  .avatar-glow {
    background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 1),
      rgba(var(--v-theme-secondary), 1)
    );
    box-shadow: 0 10px 22px rgba(var(--v-theme-primary), 0.22);
  }

  .employee-name {
    transition: color 0.16s ease;
  }
  .employee-cell:hover .employee-name {
    color: rgba(var(--v-theme-primary), 1);
  }

  .metric-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 999px;
    background: rgba(0, 0, 0, 0.04);
    min-width: 86px;
  }

  .score-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: center;
  }
  .score-bar {
    width: 120px;
  }
  .score-meta {
    min-width: 78px;
    text-align: start;
  }
  .score-number {
    font-weight: 900;
    font-size: 1rem;
  }

  .empty-table,
  .loading-table {
    min-height: 380px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }

  .table-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    background: rgba(var(--v-theme-primary), 0.02);
  }

  /* Zones */
  .zones-header {
    background: linear-gradient(
      90deg,
      rgba(var(--v-theme-primary), 0.06),
      rgba(var(--v-theme-teal), 0.04)
    );
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  }

  .zones-list {
    max-height: 340px;
    overflow-y: auto;
  }

  .zone-item {
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.16s ease;
    border: 1px solid transparent;
  }

  .zone-item:hover {
    background: rgba(var(--v-theme-primary), 0.04);
    border-color: rgba(var(--v-theme-primary), 0.12);
  }

  .rank-number {
    width: 38px;
    height: 38px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    background: rgba(var(--v-theme-primary), 0.1);
    color: rgba(var(--v-theme-primary), 0.95);
  }

  .zones-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    background: rgba(var(--v-theme-primary), 0.02);
  }

  /* FAB */
  .fab-refresh {
    position: fixed;
    z-index: 100;
  }

  /* Responsive */
  @media (max-width: 960px) {
    .search-field {
      width: 220px;
    }
    .sort-field {
      width: 190px;
    }
  }

  @media (max-width: 600px) {
    .stats-grid .v-col {
      padding: 6px !important;
    }
    .search-field,
    .sort-field {
      width: 100%;
    }
    .refresh-btn {
      display: none;
    }
    .score-bar {
      width: 90px;
    }
  }

  /* Dark Mode */
  :deep(.v-theme--dark) .dashboard-container {
    background: radial-gradient(
        1200px 600px at 0% 0%,
        rgba(var(--v-theme-primary), 0.18),
        transparent 60%
      ),
      radial-gradient(900px 500px at 100% 10%, rgba(var(--v-theme-teal), 0.14), transparent 55%),
      #121212;
  }

  :deep(.v-theme--dark) .stat-card {
    background: #1e1e1e;
    border-color: rgba(255, 255, 255, 0.08);
  }

  :deep(.v-theme--dark) .fancy-card {
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.35);
  }

  :deep(.v-theme--dark) .table-header,
  :deep(.v-theme--dark) .table-footer,
  :deep(.v-theme--dark) .zones-header,
  :deep(.v-theme--dark) .zones-footer {
    background: rgba(255, 255, 255, 0.03);
    border-color: rgba(255, 255, 255, 0.08);
  }

  :deep(.v-theme--dark) .cool-table :deep(thead th) {
    background: #1e1e1e !important;
  }

  :deep(.v-theme--dark) .metric-chip,
  :deep(.v-theme--dark) .mini-meta {
    background: rgba(255, 255, 255, 0.06);
  }

  :deep(.v-theme--dark) .zone-item:hover {
    background: rgba(255, 255, 255, 0.05);
  }
  /* ✅ Make stat cards shorter without touching logic */
  .stat-card {
    height: 100% !important; /* keep equal height in grid */
  }

  .stat-card :deep(.v-card-text) {
    padding: 14px 16px !important; /* was pa-5 => too tall */
  }

  .stat-card .text-h3 {
    font-size: 1.75rem !important; /* smaller number */
    line-height: 1.2 !important;
    margin: 0 !important;
  }

  .stat-card .mb-4 {
    margin-bottom: 10px !important;
  }
  .stat-card .mt-4 {
    margin-top: 10px !important;
  }

  /* make progress bars + chips slightly tighter */
  .stat-card :deep(.v-progress-linear) {
    margin-bottom: 10px !important;
  }
  .stat-card :deep(.v-chip) {
    height: 24px !important;
  }
  /* 🔽 EXTRA compact stat cards */
  .stat-card :deep(.v-card-text) {
    padding: 10px 12px !important;
  }

  /* numbers smaller */
  .stat-card .text-h3 {
    font-size: 1.45rem !important;
    line-height: 1.15 !important;
  }

  /* remove extra vertical spacing */
  .stat-card .mb-4 {
    margin-bottom: 6px !important;
  }
  .stat-card .mb-3 {
    margin-bottom: 6px !important;
  }
  .stat-card .mt-4 {
    margin-top: 6px !important;
  }
  .stat-card .mt-3 {
    margin-top: 6px !important;
  }

  /* icons tighter */
  .card-icon-wrapper {
    width: 38px !important;
    height: 38px !important;
    border-radius: 10px !important;
  }

  /* chips & meta compact */
  .stat-card :deep(.v-chip) {
    height: 22px !important;
    padding: 0 8px !important;
    font-size: 0.7rem !important;
  }

  /* progress bars slimmer */
  .stat-card :deep(.v-progress-linear) {
    height: 5px !important;
    margin-bottom: 6px !important;
  }

  /* mini meta pill */
  .mini-meta {
    padding: 4px 8px !important;
    font-size: 0.7rem !important;
  }
  /* =========================================================
   🔒 FORCE GLOBAL FONT — NO EXCEPTIONS
   ========================================================= */

  /* Root + browser */
  html,
  body,
  #app {
    font-family: var(--app-font) !important;
  }

  /* Vuetify root */
  .v-application,
  .v-application * {
    font-family: var(--app-font) !important;
  }

  /* Inputs, buttons, labels */
  input,
  textarea,
  select,
  button,
  .v-btn,
  .v-field,
  .v-field__input,
  .v-field-label,
  .v-label {
    font-family: var(--app-font) !important;
  }

  /* Tables */
  .v-data-table,
  .v-data-table * {
    font-family: var(--app-font) !important;
  }

  /* Chips, badges, alerts */
  .v-chip,
  .v-badge,
  .v-alert,
  .v-snackbar,
  .v-tooltip,
  .v-menu,
  .v-list,
  .v-list-item {
    font-family: var(--app-font) !important;
  }

  /* Dialogs, cards, overlays */
  .v-dialog,
  .v-card,
  .v-sheet,
  .v-overlay,
  .v-navigation-drawer,
  .v-toolbar,
  .v-app-bar {
    font-family: var(--app-font) !important;
  }

  /* Pagination, tabs */
  .v-pagination,
  .v-tab,
  .v-tabs,
  .v-tabs-bar {
    font-family: var(--app-font) !important;
  }

  /* Icons labels / helper text */
  .v-icon,
  .v-messages,
  .v-messages__message,
  .v-hint {
    font-family: var(--app-font) !important;
  }

  /* Tailwind utility text (just in case) */
  [class*='text-'],
  [class*='font-'] {
    font-family: var(--app-font) !important;
  }
  /* ===========================
   ✅ EMPLOYEE TABLE HEADER (columns)
   =========================== */

/* Light mode */
.cool-table :deep(.v-data-table__thead th),
.cool-table :deep(.v-table thead th) {
  background-color: #f8fafc !important; /* very light, clean */
  color: rgba(0, 0, 0, 0.82) !important;
  font-weight: 800 !important;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
}

/* remove primary accent shadow */
.cool-table :deep(.v-data-table__thead tr),
.cool-table :deep(.v-table thead tr) {
  box-shadow: none !important;
}

/* Dark mode */
:deep(.v-theme--dark) .cool-table :deep(.v-data-table__thead th),
:deep(.v-theme--dark) .cool-table :deep(.v-table thead th) {
  background-color: rgba(255, 255, 255, 0.06) !important;
  color: rgba(255, 255, 255, 0.88) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
}

</style>
