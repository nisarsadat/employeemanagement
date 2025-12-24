<!-- src/pages/Dashboard.vue -->
<template>
  <div class="dashboard-container" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('داشبورد')" :subtitle="t('خلاصه سیستم و درجه‌بندی کارمندان')" />

    <!-- Glassmorphic Header -->
    <div class="dashboard-header">
      <!-- Live Status Bar -->
      <div class="status-bar">
        <div class="status-items">
          <div class="status-item">
            <v-icon size="16" color="success">mdi-circle</v-icon>
            <span class="text-caption">{{ t('سیستم آنلاین') }}</span>
          </div>
          <v-divider vertical />
          <div class="status-item">
            <v-icon size="16" color="primary">mdi-update</v-icon>
            <span class="text-caption">{{ t('آخرین بروزرسانی') }}: {{ lastUpdateTime }}</span>
          </div>
          <v-divider vertical />
          <div class="status-item">
            <v-icon size="16" color="amber">mdi-database</v-icon>
            <span class="text-caption"
              >{{ t('کل رکوردها') }}: {{ totalRecords.toLocaleString() }}</span
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Error Alert -->
    <transition name="slide-fade">
      <v-alert
        v-if="repo.error"
        type="error"
        variant="tonal"
        rounded="xl"
        class="error-alert mx-4 mb-4"
        :text="readableError"
        icon="mdi-alert-octagon"
        border="start"
        closable
        @click:close="repo.error = null"
      />
    </transition>

    <!-- ✅ Stats Grid (FIXED: transition-group wrapper won’t break v-row/v-col) -->
    <div class="stats-grid px-4 mb-8">
      <v-row dense>
        <transition-group name="stats-grid" tag="div" class="stats-grid-group" appear>
          <!-- Employees Card -->
          <v-col cols="12" sm="6" md="3" lg="3" key="employees" class="stats-col">
            <v-card
              rounded="xl"
              class="stat-card glass-card"
              :class="{ pulse: employeesTotal > 0 }"
              ripple
              @click="$router.push({ name: 'employees' })"
            >
              <div class="card-glow" :style="glowStyle('primary')"></div>
              <v-card-text class="pa-5">
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-2 d-flex align-center gap-1">
                      <v-icon size="16" class="icon-shake">mdi-account-multiple</v-icon>
                      {{ t('کارمندان') }}
                    </div>

                    <div class="text-h3 font-weight-black text-gradient-primary">
                      {{ employeesTotal.toLocaleString() }}
                    </div>

                    <div class="trend-indicator">
                      <v-icon size="18" color="success">mdi-trending-up</v-icon>
                      <span class="text-caption">{{ t('فعال') }}</span>
                    </div>
                  </div>

                  <div class="floating-icon">
                    <v-avatar size="64" class="avatar-glow-primary">
                      <v-icon size="32" color="white">mdi-account-group</v-icon>
                    </v-avatar>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Resources Card -->
          <v-col cols="12" sm="6" md="3" lg="3" key="resources" class="stats-col">
            <v-card
              rounded="xl"
              class="stat-card glass-card"
              ripple
              @click="$router.push({ name: 'resources' })"
            >
              <div class="card-glow" :style="glowStyle('teal')"></div>
              <v-card-text class="pa-5">
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-2 d-flex align-center gap-1">
                      <v-icon size="16">mdi-folder-account</v-icon>
                      {{ t('منابع') }}
                    </div>

                    <div class="text-h3 font-weight-black text-gradient-teal">
                      {{ resourcesTotal.toLocaleString() }}
                    </div>

                    <div class="resource-stats">
                      <v-chip size="x-small" variant="flat" color="success" class="mr-2">
                        <v-icon start size="12">mdi-check</v-icon>
                        {{ resourcesActive }}
                      </v-chip>

                      <v-chip size="x-small" variant="flat" color="grey" class="mr-2">
                        <v-icon start size="12">mdi-close</v-icon>
                        {{ resourcesInactive }}
                      </v-chip>
                    </div>
                  </div>

                  <div class="floating-icon">
                    <v-avatar size="64" class="avatar-glow-teal">
                      <v-icon size="32" color="white">mdi-database</v-icon>
                    </v-avatar>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Informations Card -->
          <v-col cols="12" sm="6" md="3" lg="3" key="informations" class="stats-col">
            <v-card
              rounded="xl"
              class="stat-card glass-card"
              ripple
              @click="$router.push({ name: 'informations' })"
            >
              <div class="card-glow" :style="glowStyle('indigo')"></div>
              <v-card-text class="pa-5">
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-2 d-flex align-center gap-1">
                      <v-icon size="16">mdi-file-document-multiple</v-icon>
                      {{ t('اطلاعات') }}
                    </div>

                    <div class="text-h3 font-weight-black text-gradient-indigo">
                      {{ informationsTotal.toLocaleString() }}
                    </div>

                    <div class="growth-badge">
                      <v-icon size="16" color="indigo">mdi-chart-line</v-icon>
                      <span class="text-caption">{{ t('رشد') }} +12%</span>
                    </div>
                  </div>

                  <div class="floating-icon">
                    <v-avatar size="64" class="avatar-glow-indigo">
                      <v-icon size="32" color="white">mdi-chart-box</v-icon>
                    </v-avatar>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Events Card -->
          <v-col cols="12" sm="6" md="3" lg="3" key="events" class="stats-col">
            <v-card
              rounded="xl"
              class="stat-card glass-card"
              ripple
              @click="$router.push({ name: 'events' })"
            >
              <div class="card-glow" :style="glowStyle('teal-darken-1')"></div>
              <v-card-text class="pa-5">
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-2 d-flex align-center gap-1">
                      <v-icon size="16">mdi-calendar-check</v-icon>
                      {{ t('رویدادها') }}
                    </div>

                    <div class="text-h3 font-weight-black text-gradient-teal-darken">
                      {{ eventsTotal.toLocaleString() }}
                    </div>

                    <div class="activity-indicator">
                      <div class="activity-dots">
                        <div class="dot active"></div>
                        <div class="dot"></div>
                        <div class="dot active"></div>
                      </div>
                      <span class="text-caption">{{ t('فعال') }}</span>
                    </div>
                  </div>

                  <div class="floating-icon">
                    <v-avatar size="64" class="avatar-glow-teal-darken">
                      <v-icon size="32" color="white">mdi-calendar-star</v-icon>
                    </v-avatar>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </transition-group>
      </v-row>
    </div>

    <!-- Main Content with Floating Effect -->
    <div class="main-content px-4">
      <v-row dense>
        <!-- Employees Ranking Card -->
        <v-col cols="12" lg="7">
          <v-card rounded="xl" class="main-card floating-card">
            <div class="card-header-glow"></div>
            <v-card-text class="pa-4">
              <!-- Animated Header -->
              <div class="card-header animated-header">
                <div class="header-content">
                  <div class="title-group">
                    <div class="text-h5 font-weight-bold text-primary mb-1">
                      <v-icon color="primary" class="mr-2">mdi-trophy</v-icon>
                      {{ t('درجه‌بندی کارمندان') }}
                    </div>
                    <div class="text-caption text-medium-emphasis d-flex align-center gap-2">
                      <div class="spark-line"></div>
                      {{ t('به اساس تعداد رویداد، منبع و معلومات') }}
                    </div>
                  </div>

                  <!-- Interactive Controls -->
                  <div class="header-controls">
                    <v-text-field
                      v-model="search"
                      density="compact"
                      variant="outlined"
                      rounded="xl"
                      hide-details
                      :placeholder="t('جستجو: نام یا کود نمبر')"
                      clearable
                      @input="searchEmployees"
                    >
                      <template #prepend-inner>
                        <v-icon color="primary" class="px-6">mdi-magnify</v-icon>
                      </template>
                    </v-text-field>

                    <v-btn
                      :loading="repo.loading"
                      variant="flat"
                      color="primary"
                      rounded="xl"
                      @click="refreshWithAnimation"
                      class="refresh-btn"
                      :disabled="repo.loading"
                    >
                      <v-icon>{{ refreshIcon }}</v-icon>
                      {{ t('تازه‌سازی') }}
                    </v-btn>

                    <v-btn
                      icon
                      variant="tonal"
                      color="grey"
                      rounded="lg"
                      @click="toggleTableDensity"
                      class="density-toggle"
                    >
                      <v-icon>{{ densityIcon }}</v-icon>
                    </v-btn>
                  </div>
                </div>
              </div>

              <!-- Enhanced Employees Table -->
              <div class="table-wrapper">
                <v-data-table
                  :items="filteredEmployees"
                  :headers="employeeHeaders"
                  :loading="repo.loading"
                  :density="tableDensity"
                  class="employees-table elevation-0"
                  item-key="id"
                  fixed-header
                  height="520"
                  :items-per-page="10"
                  :loading-text="t('در حال بارگذاری...')"
                >
                  <!-- Rank Column with Animation -->
                  <template #item.rank="{ item }">
                    <div class="rank-cell" :class="`rank-${item.rank}`">
                      <div class="rank-badge" :style="rankBadgeStyle(item.rank)">
                        <span class="rank-number">{{ item.rank }}</span>
                        <div class="rank-crown" v-if="item.rank <= 3">
                          <v-icon size="14" color="amber">mdi-crown</v-icon>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Employee Info with Hover Effect -->
                  <template #item.name="{ item }">
                    <div class="employee-info hover-expand" @click="viewEmployee(item)">
                      <v-avatar size="40" class="avatar-gradient">
                        <span class="text-body-1 font-weight-bold text-white">
                          {{ getInitials(item.name) }}
                        </span>
                      </v-avatar>
                      <div class="employee-details">
                        <div class="employee-name">{{ item.name || '—' }}</div>
                        <div class="employee-code">
                          <v-icon size="12">mdi-identifier</v-icon>
                          {{ t('کود') }}: {{ item.code_number || '—' }}
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Resources Count with Progress -->
                  <template #item.resources_count="{ item }">
                    <div class="metric-cell">
                      <v-progress-circular
                        :model-value="(item.resources_count / maxResources) * 100"
                        size="36"
                        width="3"
                        color="primary"
                        class="mr-2"
                      >
                        <v-icon size="16" color="primary">mdi-folder</v-icon>
                      </v-progress-circular>
                      <span class="metric-value">{{ num(item.resources_count) }}</span>
                    </div>
                  </template>

                  <!-- Informations Count with Progress -->
                  <template #item.informations_count="{ item }">
                    <div class="metric-cell">
                      <v-progress-circular
                        :model-value="(item.informations_count / maxInformations) * 100"
                        size="36"
                        width="3"
                        color="indigo"
                        class="mr-2"
                      >
                        <v-icon size="16" color="indigo">mdi-file-document</v-icon>
                      </v-progress-circular>
                      <span class="metric-value">{{ num(item.informations_count) }}</span>
                    </div>
                  </template>

                  <!-- Events Count with Progress -->
                  <template #item.events_count="{ item }">
                    <div class="metric-cell">
                      <v-progress-circular
                        :model-value="(item.events_count / maxEvents) * 100"
                        size="36"
                        width="3"
                        color="teal"
                        class="mr-2"
                      >
                        <v-icon size="16" color="teal">mdi-calendar</v-icon>
                      </v-progress-circular>
                      <span class="metric-value">{{ num(item.events_count) }}</span>
                    </div>
                  </template>

                  <!-- Score with Animated Ring -->
                  <template #item.score="{ item }">
                    <div class="score-cell">
                      <div class="score-ring">
                        <svg width="48" height="48">
                          <circle
                            cx="24"
                            cy="24"
                            r="20"
                            fill="transparent"
                            stroke="rgba(var(--v-theme-primary), 0.1)"
                            stroke-width="4"
                          />
                          <circle
                            cx="24"
                            cy="24"
                            r="20"
                            fill="transparent"
                            :stroke="getScoreColor(item.score)"
                            stroke-width="4"
                            stroke-linecap="round"
                            :stroke-dasharray="`${getScorePercentage(item.score) * 1.25} 125`"
                            transform="rotate(-90 24 24)"
                            class="score-progress"
                          />
                        </svg>
                        <div class="score-value">
                          <span class="text-body-2 font-weight-bold">{{ num(item.score) }}</span>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Empty State Enhanced -->
                  <template #no-data>
                    <div class="empty-state">
                      <div class="empty-illustration">
                        <v-icon size="80" color="grey-lighten-2">mdi-account-search</v-icon>
                      </div>
                      <div class="empty-text">
                        <div class="text-h6 text-medium-emphasis mb-2">
                          {{ t('هیچ کارمند موجود نیست') }}
                        </div>
                        <div class="text-caption text-medium-emphasis">
                          {{ t('کارمند جدید اضافه کنید') }}
                        </div>
                      </div>
                      <v-btn
                        color="primary"
                        variant="flat"
                        rounded="xl"
                        class="mt-4"
                        @click="$router.push({ name: 'add-employee' })"
                      >
                        <v-icon start>mdi-plus</v-icon>
                        {{ t('افزودن کارمند') }}
                      </v-btn>
                    </div>
                  </template>

                  <!-- Loading State Enhanced -->
                  <template #loading>
                    <div class="loading-state">
                      <div class="loading-animation">
                        <v-progress-circular indeterminate color="primary" size="48" />
                        <div class="loading-text mt-4">
                          <div class="text-body-1">{{ t('در حال بارگذاری...') }}</div>
                          <div class="text-caption text-medium-emphasis">
                            {{ t('لطفاً صبر کنید') }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>
                </v-data-table>
              </div>

              <!-- Table Footer with Stats -->
              <div class="table-footer">
                <div class="footer-stats">
                  <div class="stat-item">
                    <v-icon size="16" color="primary">mdi-account-multiple</v-icon>
                    <span class="text-caption"
                      >{{ t('نمایش') }} {{ Math.min(filteredEmployees.length, 10) }} {{ t('از') }}
                      {{ filteredEmployees.length }}</span
                    >
                  </div>
                  <div class="stat-item">
                    <v-icon size="16" color="amber">mdi-trophy</v-icon>
                    <span class="text-caption">{{ t('بهترین') }}: {{ getTopEmployeeName }}</span>
                  </div>
                </div>
                <div class="footer-actions">
                  <v-pagination
                    v-model="currentPage"
                    :length="Math.ceil(filteredEmployees.length / 10)"
                    total-visible="4"
                    rounded="circle"
                    density="compact"
                  />
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Right Side: Charts and Zones -->
        <v-col cols="12" lg="5">
          <!-- Formats Pie Chart Enhanced -->
          <FormatsPieChart
            class="mb-4 floating-card"
            :title="t('توزیع فرم‌ها بر اساس زون')"
            :subtitle="t('تمام زون‌ها بر اساس تعداد فرم')"
            :total="formatsTotal"
            :total-label="t('مجموع فرم‌ها')"
            :empty-text="t('هیچ دیتا موجود نیست')"
            :data="formatsPieData"
            :height="340"
            donut
            :show-legend="true"
            :loading="repo.loading"
            :interactive="true"
            @refresh="refresh"
            @zone-click="onZoneClick"
          />

          <!-- Zones List Enhanced -->
          <v-card rounded="xl" class="floating-card zones-card">
            <div class="card-header-glow"></div>
            <v-card-text class="pa-4">
              <!-- Animated Header -->
              <div class="card-header">
                <div class="header-content">
                  <div class="title-group">
                    <div class="text-h5 font-weight-bold text-primary mb-1">
                      <v-icon color="primary" class="mr-2">mdi-map-marker</v-icon>
                      {{ t('آمار زون‌ها') }}
                    </div>
                    <div class="text-caption text-medium-emphasis d-flex align-center gap-2">
                      <div class="pulse-dot"></div>
                      {{ t('تفکیک بر اساس تعداد فرم') }}
                    </div>
                  </div>

                  <div class="header-badges">
                    <v-chip size="small" variant="flat" color="primary" class="px-3 glow-chip">
                      <v-icon start size="14">mdi-map</v-icon>
                      {{ t('زون') }}: {{ zones.length }}
                    </v-chip>
                    <v-chip size="small" variant="tonal" color="success" class="px-3">
                      <v-icon start size="14">mdi-chart-bar</v-icon>
                      {{ t('فعال') }}
                    </v-chip>
                  </div>
                </div>
              </div>

              <!-- Interactive Zones Table -->
              <div class="zones-container">
                <div class="zones-list">
                  <div
                    v-for="(zone, index) in zonesTop"
                    :key="zone.zone"
                    class="zone-item"
                    :class="{ 'zone-item-highlight': index < 3 }"
                    @click="viewZoneDetails(zone)"
                  >
                    <div class="zone-rank">
                      <div class="rank-circle" :class="`rank-${index + 1}`">
                        {{ index + 1 }}
                      </div>
                    </div>
                    <div class="zone-info">
                      <div class="zone-name">{{ zone.zone || t('نامعلوم') }}</div>
                      <div class="zone-stats">
                        <span class="text-caption text-medium-emphasis">
                          {{ getZonePercentage(zone.total) }}% {{ t('از کل') }}
                        </span>
                      </div>
                    </div>
                    <div class="zone-progress">
                      <div class="progress-bar">
                        <div
                          class="progress-fill"
                          :style="{ width: `${getZonePercentage(zone.total)}%` }"
                          :class="`progress-color-${(index % 4) + 1}`"
                        ></div>
                      </div>
                      <div class="zone-total">
                        <v-chip size="x-small" variant="flat" class="px-2">
                          {{ num(zone.total) }}
                        </v-chip>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Zones Footer -->
              <div class="zones-footer">
                <div class="zones-summary">
                  <div class="summary-item">
                    <v-icon size="16" color="primary">mdi-chart-pie</v-icon>
                    <span class="text-caption"
                      >{{ t('نمایش') }} {{ zonesTop.length }} {{ t('از') }} {{ zones.length }}</span
                    >
                  </div>
                  <div class="summary-item">
                    <v-icon size="16" color="teal">mdi-chart-timeline</v-icon>
                    <span class="text-caption"
                      >{{ t('تعداد کل') }}: {{ formatsTotal.toLocaleString() }}</span
                    >
                  </div>
                </div>
                <v-btn
                  size="small"
                  variant="flat"
                  color="primary"
                  :to="{ name: 'yearlyfoulreports' }"
                  class="details-btn"
                  rounded="xl"
                >
                  {{ t('مشاهده جزئیات') }}
                  <v-icon end size="16">mdi-arrow-left</v-icon>
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>
    <!-- Floating Action Button -->
    <v-fab
      v-model="showFab"
      location="bottom end"
      class="fab-container fab-transparent"
      :style="{ bottom: mobile ? '80px' : '40px' }"
    >
      <v-fab-group>
        <v-btn
          icon="mdi-refresh"
          variant="text"
          color="primary"
          @click="refreshWithAnimation"
          :loading="repo.loading"
          class="fab-icon-only"
        />
      </v-fab-group>
    </v-fab>

    <!-- Toast Notifications -->
    <v-snackbar v-model="showToast" :timeout="3000" color="primary" rounded="xl">
      <div class="toast-content">
        <v-icon class="mr-2">mdi-check-circle</v-icon>
        {{ toastMessage }}
      </div>
    </v-snackbar>
  </div>
</template>

<script setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useDisplay } from 'vuetify';
  import AppBar from '@/components/AppBar.vue';
  import FormatsPieChart from '@/components/charts/FormatZonePieChart.vue';
  import { useDashboardRepository } from '@/stores/DashboardRepository';

  const { t, locale } = useI18n();
  const { mobile } = useDisplay();
  const isRtl = computed(() => String(locale.value).toLowerCase().startsWith('fa'));

  const repo = useDashboardRepository();
  const search = ref('');
  const lastUpdateTime = ref(new Date().toLocaleTimeString());
  const tableDensity = ref('comfortable');
  const currentPage = ref(1);
  const showFab = ref(true);
  const showToast = ref(false);
  const toastMessage = ref('');
  const refreshIcon = ref('mdi-refresh');
  const isRefreshing = ref(false);

  onMounted(async () => {
    if (!repo.dashboard) {
      await repo.fetchDashboard();
    }
    startAutoRefresh();
  });

  function startAutoRefresh() {
    setInterval(() => {
      lastUpdateTime.value = new Date().toLocaleTimeString();
    }, 60000);
  }

  async function refreshWithAnimation() {
    isRefreshing.value = true;
    refreshIcon.value = 'mdi-loading mdi-spin';

    await repo.fetchDashboard();

    isRefreshing.value = false;
    refreshIcon.value = 'mdi-refresh';

    showToast.value = true;
    toastMessage.value = t('دیتا با موفقیت بروزرسانی شد');
    lastUpdateTime.value = new Date().toLocaleTimeString();
  }

  function searchEmployees() {
    // Debounce search if needed
  }

  function toggleTableDensity() {
    tableDensity.value = tableDensity.value === 'comfortable' ? 'compact' : 'comfortable';
  }

  function viewEmployee(employee) {
    // Navigate to employee details
    console.log('View employee:', employee);
  }

  function editEmployee(employee) {
    // Navigate to edit employee
    console.log('Edit employee:', employee);
  }

  function onZoneClick(zone) {
    // Handle zone click
    console.log('Zone clicked:', zone);
  }

  function viewZoneDetails(zone) {
    // Navigate to zone details
    console.log('View zone:', zone);
  }

  const readableError = computed(() => {
    const e = repo.error;
    if (!e) return '';
    return e?.response?.data?.message || e?.message || String(e);
  });

  /* ===========================
  Helper Functions
=========================== */
  function num(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n.toLocaleString() : '0';
  }

  function getInitials(name) {
    if (!name) return '—';
    return name
      .split(' ')
      .filter(Boolean)
      .map((word) => word[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  }

  function glowStyle(color) {
    return {
      background: `radial-gradient(circle at center, rgba(var(--v-theme-${color}), 0.3) 0%, transparent 70%)`,
    };
  }

  /* ===========================
  Stats Computed
=========================== */
  const employeesTotal = computed(() => repo.employees?.total ?? repo.employeesFlat?.length ?? 0);
  const resourcesTotal = computed(() => repo.resources?.total ?? 0);
  const resourcesActive = computed(() => repo.resources?.active ?? 0);
  const resourcesInactive = computed(() => repo.resources?.inactive ?? 0);
  const informationsTotal = computed(() => repo.informations?.total ?? 0);
  const eventsTotal = computed(() => repo.events?.total ?? 0);

  const totalRecords = computed(
    () => employeesTotal.value + resourcesTotal.value + informationsTotal.value + eventsTotal.value
  );

  /* ===========================
  Employees Logic (Unchanged)
=========================== */
  function employeeScoreNumber(item) {
    if (item?.score != null && Number.isFinite(Number(item.score))) return Number(item.score);
    const events = Number(item?.events_count) || 0;
    const resources = Number(item?.resources_count) || 0;
    const informations = Number(item?.informations_count) || 0;
    return events + resources + informations;
  }

  const employees = computed(() => {
    const list = Array.isArray(repo.employees?.flat_sorted)
      ? repo.employees.flat_sorted
      : Array.isArray(repo.dashboard?.employees_flat)
      ? repo.dashboard.employees_flat
      : [];

    const sorted = [...list].sort((a, b) => employeeScoreNumber(b) - employeeScoreNumber(a));

    return sorted.map((x, i) => ({
      ...x,
      rank: i + 1,
      score: employeeScoreNumber(x),
    }));
  });

  const maxEmployeeScore = computed(() => {
    const arr = employees.value;
    if (!arr.length) return 100;
    const m = Math.max(...arr.map((e) => employeeScoreNumber(e)));
    return Math.max(m, 100);
  });

  const maxResources = computed(() =>
    Math.max(...employees.value.map((e) => Number(e.resources_count) || 0))
  );
  const maxInformations = computed(() =>
    Math.max(...employees.value.map((e) => Number(e.informations_count) || 0))
  );
  const maxEvents = computed(() =>
    Math.max(...employees.value.map((e) => Number(e.events_count) || 0))
  );

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

  const filteredEmployees = computed(() => {
    const q = search.value?.trim()?.toLowerCase();
    if (!q) return employees.value;

    return employees.value.filter((e) => {
      const name = String(e?.name || '').toLowerCase();
      const code = String(e?.code_number || '').toLowerCase();
      return name.includes(q) || code.includes(q);
    });
  });

  const getTopEmployeeName = computed(() => filteredEmployees.value[0]?.name || '—');

  const employeeHeaders = computed(() => [
    { title: t('رتبه'), key: 'rank', width: 80, sortable: false, align: 'center' },
    { title: t('کارمند'), key: 'name', sortable: false },
    { title: t('منابع'), key: 'resources_count', width: 100, sortable: false, align: 'center' },
    {
      title: t('معلومات'),
      key: 'informations_count',
      width: 100,
      sortable: false,
      align: 'center',
    },
    { title: t('رویداد'), key: 'events_count', width: 100, sortable: false, align: 'center' },
    { title: t('امتیاز'), key: 'score', width: 100, sortable: false, align: 'center' },
  ]);

  function rankBadgeStyle(rank) {
    const colors = {
      1: {
        bg: 'linear-gradient(135deg, #FFD700 0%, #FFA500 100%)',
        shadow: '0 0 20px rgba(255, 215, 0, 0.5)',
      },
      2: {
        bg: 'linear-gradient(135deg, #C0C0C0 0%, #808080 100%)',
        shadow: '0 0 20px rgba(192, 192, 192, 0.5)',
      },
      3: {
        bg: 'linear-gradient(135deg, #CD7F32 0%, #A0522D 100%)',
        shadow: '0 0 20px rgba(205, 127, 50, 0.5)',
      },
    };

    return rank <= 3
      ? colors[rank]
      : {
          bg: 'linear-gradient(135deg, #2196F3 0%, #1976D2 100%)',
          shadow: '0 0 10px rgba(33, 150, 243, 0.3)',
        };
  }

  const densityIcon = computed(() =>
    tableDensity.value === 'comfortable' ? 'mdi-view-agenda' : 'mdi-view-stream'
  );

  /* ===========================
  Formats By Zone Logic (Unchanged)
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
    return formatsTotal.value > 0 ? ((Number(total) / formatsTotal.value) * 100).toFixed(1) : '0';
  }

  const formatsPieData = computed(() => {
    const list = zones.value
      .map((z) => ({
        value: Number(z?.total) || 0,
        name: z?.zone || t('نامعلوم'),
        itemStyle: { color: getZoneColor(z?.zone) },
      }))
      .filter((x) => x.value > 0)
      .sort((a, b) => b.value - a.value);

    if (list.length <= 10) return list;

    const top = list.slice(0, 9);
    const restSum = list.slice(9).reduce((s, x) => s + x.value, 0);
    if (restSum > 0) {
      top.push({ name: t('سایر'), value: restSum, itemStyle: { color: '#9E9E9E' } });
    }
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
      '#3F51B5',
      '#CDDC39',
    ];
    const s = String(zoneName || '');
    let hash = 0;
    for (let i = 0; i < s.length; i++) hash += s.charCodeAt(i);
    return colors[hash % colors.length];
  }
</script>

<style scoped>
  .dashboard-header {
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.05) 0%, transparent 100%);
    padding-bottom: 16px;
    position: relative;
    overflow: hidden;
  }

  .dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(var(--v-theme-primary), 0.3), transparent);
  }

  .status-bar {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(var(--v-border-color), 0.1);
    border-radius: 12px;
    margin: 0 16px;
    padding: 8px 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  }

  .status-items {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .status-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  /* Stats Grid */
  .stats-grid-wrapper {
    position: relative;
    margin-bottom: 24px;
  }

  .parallax-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 300px;
    background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 0.05) 0%,
      rgba(var(--v-theme-secondary), 0.05) 100%
    );
    border-radius: 0 0 40px 40px;
    z-index: 0;
  }

  .stats-col {
    z-index: 1;
  }

  .glass-card {
    background: rgba(255, 255, 255, 0.9) !important;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .glass-card:hover {
    transform: translateY(-8px) scale(1.02);
    border-color: rgba(var(--v-theme-primary), 0.3) !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1), 0 0 40px rgba(var(--v-theme-primary), 0.1) !important;
  }

  .card-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .glass-card:hover .card-glow {
    opacity: 1;
  }

  .floating-icon {
    position: relative;
    animation: float 3s ease-in-out infinite;
  }

  @keyframes float {
    0%,
    100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-10px);
    }
  }

  .avatar-glow-primary {
    background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%) !important;
    box-shadow: 0 0 30px rgba(33, 150, 243, 0.4);
  }

  .avatar-glow-teal {
    background: linear-gradient(135deg, #009688 0%, #00796b 100%) !important;
    box-shadow: 0 0 30px rgba(0, 150, 136, 0.4);
  }

  .avatar-glow-indigo {
    background: linear-gradient(135deg, #3f51b5 0%, #303f9f 100%) !important;
    box-shadow: 0 0 30px rgba(63, 81, 181, 0.4);
  }

  .avatar-glow-teal-darken {
    background: linear-gradient(135deg, #00796b 0%, #004d40 100%) !important;
    box-shadow: 0 0 30px rgba(0, 121, 107, 0.4);
  }

  .text-gradient-primary {
    background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .text-gradient-teal {
    background: linear-gradient(135deg, #009688 0%, #00796b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .text-gradient-indigo {
    background: linear-gradient(135deg, #3f51b5 0%, #303f9f 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .text-gradient-teal-darken {
    background: linear-gradient(135deg, #00796b 0%, #004d40 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .trend-indicator,
  .growth-badge,
  .activity-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
  }

  .activity-dots {
    display: flex;
    gap: 3px;
  }

  .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(var(--v-theme-primary), 0.3);
  }

  .dot.active {
    background: rgba(var(--v-theme-primary), 1);
  }

  /* Main Cards */
  .floating-card {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.6) !important;
    transition: all 0.3s ease;
  }

  .floating-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
  }

  .card-header-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(
      90deg,
      rgba(var(--v-theme-primary), 0.8),
      rgba(var(--v-theme-secondary), 0.8)
    );
    border-radius: 12px 12px 0 0;
    opacity: 0.8;
  }

  .animated-header {
    position: relative;
    padding-bottom: 16px;
    margin-bottom: 16px;
  }

  .animated-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(var(--v-theme-primary), 0.3), transparent);
  }

  .spark-line {
    width: 40px;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(var(--v-theme-primary), 0.8));
    border-radius: 1px;
  }

  .header-controls {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .refresh-btn {
    min-width: auto;
    transition: all 0.3s ease;
  }

  .refresh-btn:disabled {
    opacity: 0.6;
  }

  /* Table Styling */
  .table-wrapper {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(var(--v-border-color), 0.1);
    background: rgba(var(--v-theme-surface), 0.5);
  }

  .employees-table :deep(.v-data-table__tr) {
    transition: all 0.2s ease;
  }

  .employees-table :deep(.v-data-table__tr:hover) {
    background: rgba(var(--v-theme-primary), 0.03) !important;
    transform: translateX(4px);
  }

  .rank-cell {
    display: flex;
    justify-content: center;
  }

  .rank-badge {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    font-weight: bold;
    color: white;
    transition: all 0.3s ease;
  }

  .rank-badge:hover {
    transform: scale(1.1);
  }

  .rank-crown {
    position: absolute;
    top: -8px;
    right: -8px;
  }

  .employee-info {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 8px;
    border-radius: 8px;
  }

  .employee-info:hover {
    background: rgba(var(--v-theme-primary), 0.05);
    transform: translateX(4px);
  }

  .avatar-gradient {
    background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%) !important;
  }

  .employee-details {
    flex: 1;
  }

  .employee-name {
    font-weight: 600;
    margin-bottom: 2px;
  }

  .employee-code {
    font-size: 0.75rem;
    color: rgba(var(--v-theme-on-surface), 0.6);
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .metric-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .metric-value {
    font-weight: 600;
    min-width: 30px;
  }

  .score-cell {
    display: flex;
    justify-content: center;
  }

  .score-ring {
    position: relative;
  }

  .score-progress {
    transition: stroke-dasharray 0.8s ease;
  }

  .score-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  /* Empty & Loading States */
  .empty-state,
  .loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 0;
  }

  .empty-illustration {
    margin-bottom: 16px;
    opacity: 0.6;
  }

  .loading-animation {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  /* Table Footer */
  .table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid rgba(var(--v-border-color), 0.1);
  }

  .footer-stats {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  /* Zones Card */
  .zones-card {
    position: relative;
  }

  .zones-container {
    margin: 8px 0;
  }

  .zone-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(var(--v-theme-surface), 0.5);
  }

  .zone-item:hover {
    background: rgba(var(--v-theme-primary), 0.05);
    transform: translateX(4px);
  }

  .zone-item-highlight {
    border-left: 3px solid rgba(var(--v-theme-primary), 0.8);
  }

  .zone-rank {
    flex-shrink: 0;
  }

  .rank-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    font-size: 0.875rem;
  }

  .rank-1 {
    background: linear-gradient(135deg, #ffd700, #ffa500);
  }
  .rank-2 {
    background: linear-gradient(135deg, #c0c0c0, #808080);
  }
  .rank-3 {
    background: linear-gradient(135deg, #cd7f32, #a0522d);
  }
  .rank-4 {
    background: linear-gradient(135deg, #2196f3, #1976d2);
  }
  .rank-5 {
    background: linear-gradient(135deg, #4caf50, #388e3c);
  }

  .zone-info {
    flex: 1;
  }

  .zone-name {
    font-weight: 500;
    margin-bottom: 2px;
  }

  .zone-progress {
    width: 120px;
  }

  .progress-bar {
    height: 6px;
    background: rgba(var(--v-border-color), 0.2);
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 4px;
  }

  .progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.8s ease;
  }

  .progress-color-1 {
    background: linear-gradient(90deg, #2196f3, #64b5f6);
  }
  .progress-color-2 {
    background: linear-gradient(90deg, #4caf50, #81c784);
  }
  .progress-color-3 {
    background: linear-gradient(90deg, #ff9800, #ffb74d);
  }
  .progress-color-4 {
    background: linear-gradient(90deg, #9c27b0, #ba68c8);
  }

  .zones-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid rgba(var(--v-border-color), 0.1);
  }

  .zones-summary {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .summary-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .details-btn {
    min-width: auto;
  }

  /* FAB Container */
  .fab-container {
    position: fixed;
    z-index: 1000;
  }

  .fab-button {
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
  }

  .fab-button:hover {
    transform: scale(1.1);
  }

  /* Toast */
  .toast-content {
    display: flex;
    align-items: center;
  }

  /* Animations */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateX(-20px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  .slide-fade-enter-active,
  .slide-fade-leave-active {
    transition: all 0.3s ease;
  }

  .slide-fade-enter-from,
  .slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-20px);
  }

  .stats-grid-move {
    transition: transform 0.5s ease;
  }

  .stats-grid-enter-active {
    transition: all 0.5s ease;
    transition-delay: calc(var(--v-index) * 0.1s);
  }

  .stats-grid-leave-active {
    transition: all 0.3s ease;
  }

  .stats-grid-enter-from {
    opacity: 0;
    transform: scale(0.8) translateY(20px);
  }

  .stats-grid-leave-to {
    opacity: 0;
    transform: scale(0.8);
  }

  /* Pulse Animation */
  @keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0.4);
    }
    70% {
      box-shadow: 0 0 0 10px rgba(var(--v-theme-primary), 0);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0);
    }
  }

  .pulse {
    animation: pulse 2s infinite;
  }

  /* Icon Shake */
  @keyframes iconShake {
    0%,
    100% {
      transform: rotate(0deg);
    }
    25% {
      transform: rotate(-5deg);
    }
    75% {
      transform: rotate(5deg);
    }
  }

  .icon-shake {
    animation: iconShake 5s ease-in-out infinite;
  }

  /* Pulse Dot */
  .pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(var(--v-theme-primary), 0.8);
    animation: pulse 1.5s infinite;
  }

  /* Glow Chip */
  .glow-chip {
    box-shadow: 0 0 15px rgba(var(--v-theme-primary), 0.3) !important;
    animation: glow 2s infinite alternate;
  }

  @keyframes glow {
    from {
      box-shadow: 0 0 10px rgba(var(--v-theme-primary), 0.3);
    }
    to {
      box-shadow: 0 0 20px rgba(var(--v-theme-primary), 0.5);
    }
  }

  /* Responsive Design */
  @media (max-width: 960px) {
    .main-content .row {
      flex-direction: row-reverse;
    }

    .header-controls {
      flex-wrap: wrap;
    }

    .zone-progress {
      width: 80px;
    }
  }

  @media (max-width: 600px) {
    .status-items {
      flex-wrap: wrap;
      gap: 8px;
    }

    .text-h3 {
      font-size: 2rem;
    }

    .header-controls {
      gap: 8px;
    }

    .table-footer {
      flex-direction: row;
      gap: 12px;
      align-items: stretch;
    }

    .footer-actions {
      display: flex;
      justify-content: center;
    }
  }

  /* Scrollbar Styling */
  .table-wrapper::-webkit-scrollbar,
  .zones-container::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }

  .table-wrapper::-webkit-scrollbar-track,
  .zones-container::-webkit-scrollbar-track {
    background: rgba(var(--v-border-color), 0.1);
    border-radius: 3px;
  }

  .table-wrapper::-webkit-scrollbar-thumb,
  .zones-container::-webkit-scrollbar-thumb {
    background: rgba(var(--v-theme-primary), 0.3);
    border-radius: 3px;
  }

  .table-wrapper::-webkit-scrollbar-thumb:hover,
  .zones-container::-webkit-scrollbar-thumb:hover {
    background: rgba(var(--v-theme-primary), 0.5);
  }

  /* RTL Support */
  [dir='rtl'] .employee-info:hover,
  [dir='rtl'] .zone-item:hover {
    transform: translateX(-4px);
  }

  [dir='rtl'] .employees-table :deep(.v-data-table__tr:hover) {
    transform: translateX(-4px);
  }

  [dir='rtl'] .spark-line {
    background: linear-gradient(90deg, rgba(var(--v-theme-primary), 0.8), transparent);
  }

  [dir='rtl'] .animated-header::after {
    background: linear-gradient(90deg, transparent, rgba(var(--v-theme-primary), 0.3), transparent);
  }

  /* ✅ MUST ADD: keeps v-col behaving like direct children of v-row */
  .stats-grid-group {
    display: contents;
  }
  .dashboard-container {
    font-family: var(--app-font);
  }

  .dashboard-container :deep(*) {
    font-family: inherit;
  }
  /* Remove FAB background completely */
  .fab-transparent :deep(.v-fab__container),
  .fab-transparent :deep(.v-fab__underlay),
  .fab-transparent :deep(.v-fab__content) {
    background: transparent !important;
    box-shadow: none !important;
  }

  /* Icon-only button */
  .fab-icon-only {
    background: transparent !important;
    box-shadow: none !important;
  }

  /* Optional hover effect (nice UX) */
  .fab-icon-only:hover {
    background: rgba(var(--v-theme-primary), 0.08) !important;
    border-radius: 50%;
  }
</style>
