<!-- src/pages/Dashboard.vue -->
<template>
  <EmployeeDetailsDialog
    v-model="employeeDialog"
    :employee="selectedEmployee"
    :loading="employeeDialogLoading"
    @close="closeEmployeeDialog"
  />

  <ZoneFormatsDialog
    v-model="zoneDialog"
    :zone="selectedZone"
    :loading="zoneDialogLoading"
    @close="closeZoneDialog"
  />

  <div class="dashboard-container" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- App Bar -->
    <AppBar
      :title="t('داشبورد')"
      :subtitle="t('خلاصه عملکرد سیستم')"
      @calendar-change="onCalendarChange"
    >
      <template #actions>
        <div class="d-flex align-center gap-2 flex-wrap">
          <!-- Finance menu -->
          <v-menu location="bottom" transition="scale-transition" :close-on-content-click="false">
            <template #activator="{ props }">
              <v-tooltip :text="t('فیلتر دوره مالی (تیل + مالی + جدول کارمندان)')">
                <template #activator="{ props: tipProps }">
                  <v-btn
                    v-bind="{ ...props, ...tipProps }"
                    variant="tonal"
                    color="primary"
                    rounded="lg"
                    size="small"
                    prepend-icon="mdi-finance"
                    class="dash-filter-btn"
                  >
                    {{ t('دوره مالی') }}
                    <v-icon end size="16">mdi-chevron-down</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
            </template>

            <v-card rounded="xl" class="dash-filter-menu">
              <v-card-title class="d-flex align-center justify-space-between py-3">
                <div class="d-flex align-center gap-2">
                  <v-icon color="primary">mdi-finance</v-icon>
                  <span class="text-subtitle-2 font-weight-bold">
                    {{ t('انتخاب دوره مالی') }}
                  </span>
                </div>

                <v-chip size="x-small" variant="tonal" color="primary">
                  {{ financePeriod }}
                </v-chip>
              </v-card-title>

              <v-divider />

              <v-card-text class="py-3">
                <v-btn-toggle
                  v-model="financePeriod"
                  density="compact"
                  mandatory
                  rounded="lg"
                  class="finance-period-toggle"
                  @update:modelValue="onFinancePeriodChanged"
                >
                  <v-btn value="daily" size="small">{{ t('روزانه') }}</v-btn>
                  <v-btn value="weekly" size="small">{{ t('هفته‌وار') }}</v-btn>
                  <v-btn value="monthly" size="small">{{ t('ماهانه') }}</v-btn>
                  <v-btn value="yearly" size="small">{{ t('سالانه') }}</v-btn>
                  <v-btn value="all" size="small">{{ t('همه') }}</v-btn>
                </v-btn-toggle>
              </v-card-text>
            </v-card>
          </v-menu>

          <!-- General menu -->
          <v-menu location="bottom" transition="scale-transition" :close-on-content-click="false">
            <template #activator="{ props }">
              <v-tooltip :text="t('فیلتر عمومی (کارت‌ها + فرم‌ها)')">
                <template #activator="{ props: tipProps }">
                  <v-btn
                    v-bind="{ ...props, ...tipProps }"
                    variant="tonal"
                    color="teal"
                    rounded="lg"
                    size="small"
                    prepend-icon="mdi-filter-variant"
                    class="dash-filter-btn"
                  >
                    {{ t('دوره عمومی') }}
                    <v-icon end size="16">mdi-chevron-down</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
            </template>

            <v-card rounded="xl" class="dash-filter-menu">
              <v-card-title class="d-flex align-center justify-space-between py-3">
                <div class="d-flex align-center gap-2">
                  <v-icon color="teal">mdi-filter-variant</v-icon>
                  <span class="text-subtitle-2 font-weight-bold">
                    {{ t('انتخاب دوره عمومی') }}
                  </span>
                </div>

                <v-chip size="x-small" variant="tonal" color="teal">
                  {{ period }}
                </v-chip>
              </v-card-title>

              <v-divider />

              <v-card-text class="py-3">
                <v-btn-toggle
                  v-model="period"
                  density="compact"
                  mandatory
                  rounded="lg"
                  class="table-mode-toggle"
                  @update:modelValue="onPeriodChanged"
                >
                  <v-btn value="daily" size="small">{{ t('روزانه') }}</v-btn>
                  <v-btn value="weekly" size="small">{{ t('هفته‌وار') }}</v-btn>
                  <v-btn value="monthly" size="small">{{ t('ماهانه') }}</v-btn>
                  <v-btn value="yearly" size="small">{{ t('سالانه') }}</v-btn>
                  <v-btn value="all" size="small">{{ t('همه') }}</v-btn>
                </v-btn-toggle>
              </v-card-text>
            </v-card>
          </v-menu>
        </div>
      </template>
    </AppBar>

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

    <!-- Stats Cards Grid -->
    <div class="stats-grid px-4 py-4">
      <v-row dense class="stats-row">
        <!-- Employees Card -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="goEmployees"
            :disabled="repo.loading"
          >
            <div class="stat-accent primary"></div>

            <v-card-text class="stat-card-body">
              <div class="stat-top">
                <div class="card-icon-wrapper primary">
                  <v-icon color="primary" size="20">mdi-account-group</v-icon>
                </div>

                <v-chip
                  v-if="Number(employeesGrowth) > 0"
                  size="x-small"
                  variant="tonal"
                  color="success"
                >
                  <v-icon start size="12">mdi-trending-up</v-icon>
                  +{{ employeesGrowth }}%
                </v-chip>
              </div>

              <div class="stat-title">{{ t('کل کارمندان') }}</div>

              <div class="stat-main">
                <div class="stat-number">{{ employeesTotal.toLocaleString() }}</div>
              </div>

              <div class="stat-bottom">
                <div class="chips-grid">
                  <v-chip
                    v-for="chip in employeesCardChips"
                    :key="chip.key"
                    size="x-small"
                    variant="tonal"
                    :color="chip.color"
                    class="chip-compact"
                  >
                    {{ chip.label }}: {{ num(chip.value) }}
                  </v-chip>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Resources Card -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card rounded="xl" class="stat-card" :disabled="repo.loading">
            <div class="stat-accent teal"></div>

            <v-card-text class="stat-card-body">
              <div class="stat-top">
                <div class="card-icon-wrapper teal">
                  <v-icon color="teal" size="20">mdi-database</v-icon>
                </div>
                <v-chip size="x-small" variant="tonal" color="teal">{{ t('منابع') }}</v-chip>
              </div>

              <div class="stat-title">{{ t('منابع') }}</div>

              <div class="stat-main">
                <div class="stat-number">{{ resourcesTotal.toLocaleString() }}</div>
              </div>

              <v-progress-linear
                :model-value="resourcesActivePercentage"
                height="5"
                rounded
                color="teal"
                bg-color="rgba(var(--v-theme-teal), 0.12)"
                class="stat-progress"
              />

              <div class="stat-bottom">
                <div class="actions-row">
                  <v-chip
                    size="x-small"
                    variant="tonal"
                    color="success"
                    class="chip-compact"
                    @click.stop="goActiveResources"
                  >
                    <v-icon start size="14">mdi-check-circle</v-icon>
                    {{ t('فعال') }}: {{ num(resourcesActive) }}
                  </v-chip>

                  <v-chip
                    size="x-small"
                    variant="tonal"
                    color="error"
                    class="chip-compact"
                    @click.stop="goInactiveResources"
                  >
                    <v-icon start size="14">mdi-close-circle</v-icon>
                    {{ t('غیرفعال') }}: {{ num(resourcesInactive) }}
                  </v-chip>

                  <v-spacer />

                  <v-btn
                    size="x-small"
                    variant="text"
                    color="teal"
                    class="px-1"
                    :disabled="repo.loading"
                    @click.stop="goResources()"
                  >
                    {{ t('مشاهده') }}
                    <v-icon end size="14">mdi-arrow-left</v-icon>
                  </v-btn>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Informations Card -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="goInformations"
            :disabled="repo.loading"
          >
            <div class="stat-accent indigo"></div>

            <v-card-text class="stat-card-body">
              <div class="stat-top">
                <div class="card-icon-wrapper indigo">
                  <v-icon color="indigo" size="20">mdi-file-document-multiple</v-icon>
                </div>
                <v-chip size="x-small" variant="tonal" color="indigo">{{ t('اطلاعات') }}</v-chip>
              </div>

              <div class="stat-title">{{ t('اطلاعات') }}</div>

              <div class="stat-main">
                <div class="stat-number">{{ informationsTotal.toLocaleString() }}</div>
              </div>

              <div class="stat-bottom">
                <div class="chips-grid">
                  <v-chip size="x-small" variant="tonal" color="error" class="chip-compact">
                    {{ t('شر او فساد') }}: {{ num(infoCountSharOFasad) }}
                  </v-chip>
                  <v-chip size="x-small" variant="tonal" color="warning" class="chip-compact">
                    {{ t('ساعین بالفساد') }}: {{ num(infoCountSaenBilFasad) }}
                  </v-chip>
                  <v-chip size="x-small" variant="tonal" color="primary" class="chip-compact">
                    {{ t('جاسوسی') }}: {{ num(infoCountJasoosi) }}
                  </v-chip>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Events Card -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="goEvents"
            :disabled="repo.loading"
          >
            <div class="stat-accent cyan"></div>

            <v-card-text class="stat-card-body">
              <div class="stat-top">
                <div class="card-icon-wrapper cyan">
                  <v-icon color="teal-darken-1" size="20">mdi-calendar-multiple</v-icon>
                </div>

                <div class="activity-dots">
                  <div class="activity-dot active"></div>
                  <div class="activity-dot"></div>
                  <div class="activity-dot active"></div>
                </div>
              </div>

              <div class="stat-title">{{ t('رویدادها') }}</div>

              <div class="stat-main">
                <div class="stat-number">{{ eventsTotal.toLocaleString() }}</div>
              </div>

              <div class="stat-bottom">
                <div class="chips-grid">
                  <v-chip
                    v-for="eventType in eventsByType"
                    :key="eventType.type"
                    size="x-small"
                    variant="tonal"
                    :color="getEventTypeColor(eventType.type)"
                    class="chip-compact"
                  >
                    {{ eventType.type }}: {{ num(eventType.count) }}
                  </v-chip>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Money Balance Card -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="goFinance"
            :disabled="repo.loading"
          >
            <div class="stat-accent success"></div>

            <v-card-text class="stat-card-body">
              <!-- <div class="stat-top">
                <div class="card-icon-wrapper success">
                  <v-icon color="success" size="20">mdi-cash-multiple</v-icon>
                </div>

                <v-chip
                  size="x-small"
                  variant="tonal"
                  :color="moneyBalance >= 0 ? 'success' : 'warning'"
                  class="chip-compact font-weight-bold"
                >
                  <v-icon start size="12" :icon="moneyBalance >= 0 ? 'mdi-plus' : 'mdi-minus'" />
                  <span>{{ t('موجودی:') }}</span>
                  <span>{{ num(moneyBalance) }}</span>
                </v-chip>
              </div> -->

              <div class="stat-title">{{ t('ټولې پاتې پیسې') }}</div>

              <div class="stat-main">
                <div class="stat-number">{{ num(moneyBalance) }}</div>
              </div>

              <div class="stat-bottom">
                <div class="split-meta">
                  <div class="meta-item">
                    <v-icon size="12" color="success">mdi-arrow-down</v-icon>
                    <span class="text-caption">{{ t('ټولې راغلي پیسې:') }}</span>
                    <span class="text-caption font-weight-bold">{{ num(moneyIncomeTotal) }}</span>
                  </div>
                  <div class="meta-item">
                    <v-icon size="12" color="error">mdi-arrow-up</v-icon>
                    <span class="text-caption">{{ t('ټولې مصرف شوي پیسې:') }}</span>
                    <span class="text-caption font-weight-bold">{{ num(moneyExpenseTotal) }}</span>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Oil Balance Card -->
        <!-- Oil Balance Card (make it same like Money card) -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card
            rounded="xl"
            class="stat-card"
            :class="{ 'stat-card-hover': !repo.loading }"
            ripple
            @click="goOil"
            :disabled="repo.loading"
          >
            <div class="stat-accent warning"></div>

            <v-card-text class="stat-card-body">
              <div class="stat-title">{{ t('د تیلو  مجموعه') }}:</div>

              <div class="stat-main">
                <div class="stat-number">{{ num(oilBalance) }}</div>
              </div>

              <div class="stat-bottom">
                <div class="split-meta">
                  <div class="meta-item">
                    <v-icon size="12" color="success">mdi-arrow-down</v-icon>
                    <span class="text-caption">{{ t('ټول راغلي تیل:') }}</span>
                    <span class="text-caption font-weight-bold">{{ num(oilIncomeTotal) }}</span>
                  </div>

                  <div class="meta-item">
                    <v-icon size="12" color="error">mdi-arrow-up</v-icon>
                    <span class="text-caption">{{ t('ټول مصرف شوي تیل:') }}</span>
                    <span class="text-caption font-weight-bold">{{ num(oilExpenseTotal) }}</span>
                  </div>
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
                        {{ t('بر اساس امتیاز (رویداد + منابع + اطلاعات + گزارش‌های خیرات)') }}
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
                  <v-menu
                    location="bottom"
                    transition="scale-transition"
                    :close-on-content-click="false"
                  >
                    <template #activator="{ props }">
                      <v-btn
                        v-bind="props"
                        variant="outlined"
                        rounded="xl"
                        class="sort-field"
                        prepend-icon="mdi-sort"
                      >
                        {{ t('مرتب‌سازی') }}
                        <v-chip class="ml-2" size="x-small" variant="tonal" color="primary">
                          {{ sortKeys.length }}
                        </v-chip>
                        <v-icon end size="18">mdi-chevron-down</v-icon>
                      </v-btn>
                    </template>

                    <v-card rounded="xl" class="dash-filter-menu">
                      <v-card-title class="py-3 d-flex align-center justify-space-between">
                        <div class="d-flex align-center gap-2">
                          <v-icon color="primary">mdi-sort</v-icon>
                          <span class="text-subtitle-2 font-weight-bold">{{
                            t('مرتب‌سازی چندگانه')
                          }}</span>
                        </div>

                        <v-btn
                          size="small"
                          variant="text"
                          color="error"
                          @click="sortKeys = ['score_desc']"
                        >
                          {{ t('ریست') }}
                        </v-btn>
                      </v-card-title>

                      <v-divider />

                      <v-card-text class="py-2">
                        <v-list-item
                          v-for="opt in sortOptions"
                          :key="opt.value"
                          class="px-0"
                          @click="toggleSort(opt.value)"
                        >
                          <template #prepend>
                            <v-checkbox
                              :model-value="sortKeys.includes(opt.value)"
                              @click.stop
                              @update:modelValue="toggleSort(opt.value)"
                              density="compact"
                              hide-details
                            />
                          </template>

                          <v-list-item-title class="text-body-2">
                            {{ opt.title }}
                          </v-list-item-title>
                        </v-list-item>

                        <!-- show selected order -->
                        <div class="mt-3 d-flex flex-wrap ga-2">
                          <v-chip
                            v-for="k in sortKeys"
                            :key="k"
                            size="x-small"
                            variant="tonal"
                            color="primary"
                            closable
                            @click:close="removeSort(k)"
                          >
                            {{ sortOptions.find((x) => x.value === k)?.title || k }}
                          </v-chip>
                        </div>
                      </v-card-text>
                    </v-card>
                  </v-menu>

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
            <div class="table-shell table-scroll-wrap">
              <v-data-table
                :items="pagedEmployees"
                :headers="employeeHeaders"
                :loading="repo.loading"
                class="employees-table cool-table"
                item-key="id"
                fixed-header
                height="350"
                :items-per-page="itemsPerPage"
                hide-default-footer
              >
                <!-- Rank -->
                <template #item.rank="{ item }">
                  <div class="rank-cell">
                    <div class="rank-badge">
                      {{ item.rank }}
                    </div>
                  </div>
                </template>

                <!-- Employee -->
                <template #item.name="{ item }">
                  <div class="employee-cell" @click="openEmployeeDialog(item)">
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

                <!-- Khayrat Reports (only in new mode) -->
                <template #item.khayrat_reports_count="{ item }">
                  <div class="metric-chip">
                    <span class="font-weight-bold">{{ num(item.khayrat_reports_count || 0) }}</span>
                  </div>
                </template>

                <!-- Score -->
                <!-- ✅ Score Bar column -->
                <template #item.score_bar="{ item }">
                  <div class="score-bar-cell">
                    <v-progress-linear
                      :model-value="getScorePercentage(item.score)"
                      height="10"
                      rounded
                      :color="getScoreColor(item.score)"
                      bg-color="rgba(0,0,0,0.06)"
                      class="score-bar"
                    />
                  </div>
                </template>

                <!-- ✅ Score Value column -->
                <template #item.score_value="{ item }">
                  <div class="score-value-cell">
                    <div class="score-number">{{ num(item.score) }}</div>
                    <div class="score-label text-caption text-medium-emphasis">
                      {{ getScoreLabel(item.score) }}
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
                      @click.stop="openEmployeeDialog(item)"
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
            :height="326"
            donut
            :show-legend="true"
            :loading="repo.loading"
            :interactive="true"
            @refresh="refreshData"
            @zone-click="onZoneClick"
          />
        </v-col>
        <v-col cols="12" lg="12">
          <MonthlyAmountBarChart
            :loading="repo.loading"
            :height="320"
            :calendar="repo.calendar"
            :year="repo.calendar_meta?.year"
            :data="repo.resources_monthly_counts"
            @refresh="refreshData"
          />
        </v-col>
        <v-col cols="12" lg="12">
          <ResourcesByHawzaBarChart
            :loading="repo.loading"
            :data="repo.dashboard?.resources?.resources_by_hawza || []"
          />
        </v-col>
      </v-row>
    </div>

    <div class="fab-stack" :class="{ rtl: isRtl }" :style="{ bottom: mobile ? '80px' : '24px' }">
      <v-tooltip location="top">
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            fab
            color="primary"
            size="small"
            :loading="repo.loading"
            @click="refreshData"
          >
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </template>
        <span>{{ t('تازه‌سازی') }}</span>
      </v-tooltip>
    </div>
  </div>
</template>
<script setup>
  import { computed, onMounted, ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useDisplay } from 'vuetify';
  import { useRouter } from 'vue-router';
  import ResourcesByHawzaBarChart from '@/components/charts/ResourcesByHawzaBarChart.vue';

  import AppBar from '@/components/AppBar.vue';
  import FormatsPieChart from '@/components/charts/FormatZonePieChart.vue';
  import { useDashboardRepository } from '@/stores/DashboardRepository';
  import EmployeeDetailsDialog from './EmployeeDetailsDialog.vue';
  import ZoneFormatsDialog from './ZoneFormatsDialog.vue';
  import MonthlyAmountBarChart from '@/components/charts/MonthlyAmountBarChart.vue';

  const { t, locale } = useI18n();
  const { mobile } = useDisplay();
  const router = useRouter();

  const isRtl = computed(() => String(locale.value).toLowerCase().startsWith('fa'));
  const repo = useDashboardRepository();

  const search = ref('');
  const currentPage = ref(1);
  const itemsPerPage = 10;
  const sortKeys = ref(['score_desc']);

  const period = ref('all'); // main filter
  const financePeriod = ref('monthly'); // affects money + oil + employee table

  // ✅ calendar comes from AppBar (fallback to repo or localStorage)
  const calendar = ref(localStorage.getItem('calendar') || repo.calendar || 'miladi');
  // dialogs (add these)
  const employeeDialog = ref(false);
  const selectedEmployee = ref(null);
  const employeeDialogLoading = ref(false);

  function closeEmployeeDialog() {
    employeeDialog.value = false;
    employeeDialogLoading.value = false;
    // optional:
    // selectedEmployee.value = null
  }
  function onCalendarChange(newCal) {
    const c = String(newCal || '').toLowerCase();
    calendar.value = c || 'miladi';
    localStorage.setItem('calendar', calendar.value);
    refreshData();
  }

  function go(path, query = {}) {
    router.push({ path, query });
  }

  function goEmployees() {
    go('/app/officeemployee');
  }
  function goResources(status = null) {
    go('/app/resources', status ? { status } : {});
  }
  function goActiveResources() {
    go('/app/activeresource');
  }
  function goInactiveResources() {
    go('/app/inactiveresource');
  }
  function goInformations() {
    go('/app/informations');
  }
  function goEvents() {
    go('/app/events');
  }
  function goFinance() {
    go('/app/finincalindex');
  }
  function goOil() {
    go('/app/finincalindex', { tab: 'oil' });
  }

  function getEventTypeColor(type) {
    const colors = {
      سیاسي: 'blue',
      جنایي: 'red',
      الهی: 'green',
      ترافیکي: 'orange',
      ترافیکی: 'orange',
      الهي: 'green',
    };
    return colors[type] || 'grey';
  }

  onMounted(async () => {
    await repo.fetchDashboard({
      calendar: calendar.value,
      period: period.value,
      finance_period: financePeriod.value,
    });
  });

  async function refreshData() {
    await repo.fetchDashboard({
      calendar: calendar.value,
      period: period.value,
      finance_period: financePeriod.value,
    });
    currentPage.value = 1;
  }

  async function onPeriodChanged() {
    currentPage.value = 1;
    search.value = '';
    await refreshData();
  }

  async function onFinancePeriodChanged() {
    currentPage.value = 1;
    search.value = '';
    await refreshData();
  }

  /* ===========================
  Backend-driven counts
  =========================== */
  const employeesTotal = computed(() => repo.employees?.total ?? repo.employeesFlat?.length ?? 0);
  const employeesActive = computed(() => Number(repo.employees?.active ?? 0));
  const employeesInactive = computed(() => Number(repo.employees?.inactive ?? 0));
  const moneyMonthlyChartData = computed(() => {
    const labels = repo.money_monthly?.labels || [];
    const values = repo.money_monthly?.net || []; // net = income - expense
    // ECharts component supports {labels, values}
    return { labels, values };
  });

  // ✅ job types (اداري / اوپراتیفي) from backend repo.employees.job_types
  const employeesJobTypes = computed(() => {
    const types = repo.employees?.job_types || [];

    const result = [
      { type: 'فعال', count: Number(repo.employees?.active || 0) },
      { type: 'غیر فعال', count: Number(repo.employees?.inactive || 0) },
      { type: 'اداري', count: 0 },
      { type: 'اوپراتیفي', count: 0 },
    ];

    types.forEach((item) => {
      const t = String(item.type || '').trim();

      if (t.includes('اداري')) result[2].count += Number(item.count || 0);
      if (t.includes('اوپراتی')) result[3].count += Number(item.count || 0);
    });

    return result; // ✅ keep all chips even if 0
  });

  // ✅ Employee card chips: فعال / غیر فعال / اداري / اوپراتیفي
  const employeesCardChips = computed(() => {
    const jt = employeesJobTypes.value;
    const admin = jt.find((x) => x.type === 'اداري')?.count ?? 0;
    const oper = jt.find((x) => x.type === 'اوپراتیفي')?.count ?? 0;

    return [
      { key: 'active', label: t('فعال'), value: employeesActive.value, color: 'success' },
      { key: 'inactive', label: t('غیر فعال'), value: employeesInactive.value, color: 'error' },
      { key: 'admin', label: 'اداري', value: admin, color: 'primary' },
      { key: 'oper', label: 'اوپراتیفي', value: oper, color: 'teal' },
    ];
  });

  // ✅ Events by type (سیاسي/جنایي/الهی/ترافیکي) from backend repo.events.types
  const eventsByType = computed(() => {
    const types = repo.events?.types || [];

    const result = [
      { type: 'سیاسي', count: 0 },
      { type: 'جنایي', count: 0 },
      { type: 'الهی', count: 0 },
      { type: 'ترافیکي', count: 0 },
    ];

    types.forEach((item) => {
      const t = String(item.type || '').trim();
      const found = result.find((r) => t.includes(r.type));
      if (found) found.count += Number(item.count || 0);
    });

    return result;
  });

  /* ===========================
  Sort options / headers
  =========================== */
  const sortOptions = computed(() => [
    { title: t('امتیاز (زیاد → کم)'), value: 'score_desc' },
    { title: t('امتیاز (کم → زیاد)'), value: 'score_asc' },
    { title: t('پیښې (زیاد → کم)'), value: 'events_desc' },
    { title: t('منابع (زیاد → کم)'), value: 'resources_desc' },
    { title: t('اطلاعات (زیاد → کم)'), value: 'informations_desc' },
    { title: t('خیریت راپور (زیاد → کم)'), value: 'khayrat_desc' },
    { title: t('نام (A → Z)'), value: 'name_asc' },
  ]);

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
    {
      title: t('گزارش‌های خیرات'),
      key: 'khayrat_reports_count',
      width: 110,
      sortable: false,
      align: 'center',
    },
    { title: t('نمودار امتیاز'), key: 'score_bar', width: 150, sortable: false, align: 'center' },
    { title: t('امتیاز'), key: 'score_value', width: 120, sortable: false, align: 'center' },
    { title: t('د معلواتو لیدنه'), key: 'actions', width: 70, sortable: false, align: 'center' },
  ]);
  function toggleSort(key) {
    const arr = [...(sortKeys.value || [])];
    const i = arr.indexOf(key);

    if (i >= 0) arr.splice(i, 1);
    else arr.push(key);

    // ✅ ensure at least one sort
    if (!arr.length) arr.push('score_desc');

    sortKeys.value = arr;
    currentPage.value = 1;
  }

  function removeSort(key) {
    const arr = [...(sortKeys.value || [])].filter((x) => x !== key);
    if (!arr.length) arr.push('score_desc');
    sortKeys.value = arr;
    currentPage.value = 1;
  }

  /* ===========================
  Error
  =========================== */
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
    if (percentage >= 60) return t('ښه');
    if (percentage >= 40) return t('متوسط');
    return t('');
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

  /* ===========================
  Stats
  =========================== */
  const resourcesTotal = computed(() => Number(repo.resources?.total ?? 0));
  const resourcesActive = computed(() => Number(repo.resources?.active ?? 0));
  const resourcesInactive = computed(() => Number(repo.resources?.inactive ?? 0));

  const informationsTotal = computed(() => Number(repo.informations?.total ?? 0));
  const eventsTotal = computed(() => Number(repo.events?.total ?? 0));

  const infoJenayeeTypes = computed(() => {
    const arr = repo.informations?.jenayee_types;
    return Array.isArray(arr) ? arr : [];
  });

  const infoSyasiTypes = computed(() => {
    const arr = repo.informations?.syasi_types;
    return Array.isArray(arr) ? arr : [];
  });

  function findTypeCount(list, names) {
    if (!Array.isArray(list)) return 0;
    const normalizedNames = (Array.isArray(names) ? names : [names]).map((x) =>
      String(x || '').trim()
    );
    const row = list.find((x) => normalizedNames.includes(String(x?.type || '').trim()));
    return Number(row?.count || 0);
  }

  const infoCountSharOFasad = computed(() => findTypeCount(infoJenayeeTypes.value, ['شر او فساد']));
  const infoCountSaenBilFasad = computed(() =>
    findTypeCount(infoJenayeeTypes.value, ['ساعین بالفساد'])
  );
  const infoCountJasoosi = computed(() => findTypeCount(infoSyasiTypes.value, ['جاسوسی']));

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
  Employees Logic (table)
  =========================== */
  const baseEmployees = computed(() => {
    const list = Array.isArray(repo.employees_new?.flat_sorted)
      ? repo.employees_new.flat_sorted
      : [];
    return [...list].map((x) => ({ ...x, score: Number(x.score || x.total || 0) }));
  });

  function employeeScoreNumber(item) {
    if (item?.score != null && Number.isFinite(Number(item.score))) return Number(item.score);

    const events = Number(item?.events_count) || 0;
    const resources = Number(item?.resources_count) || 0;
    const informations = Number(item?.informations_count) || 0;
    const khayrat = Number(item?.khayrat_reports_count) || 0;

    return events + resources + informations + khayrat;
  }
  function cmpByKey(a, b, key) {
    if (key === 'score_desc') return employeeScoreNumber(b) - employeeScoreNumber(a);
    if (key === 'score_asc') return employeeScoreNumber(a) - employeeScoreNumber(b);

    if (key === 'events_desc')
      return (Number(b?.events_count) || 0) - (Number(a?.events_count) || 0);

    if (key === 'resources_desc')
      return (Number(b?.resources_count) || 0) - (Number(a?.resources_count) || 0);

    if (key === 'informations_desc')
      return (Number(b?.informations_count) || 0) - (Number(a?.informations_count) || 0);

    if (key === 'khayrat_desc')
      return (Number(b?.khayrat_reports_count) || 0) - (Number(a?.khayrat_reports_count) || 0);

    if (key === 'name_asc')
      return String(a?.name || '').localeCompare(String(b?.name || ''), undefined, {
        sensitivity: 'base',
        numeric: true,
      });

    return 0;
  }

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

    const keys =
      Array.isArray(sortKeys.value) && sortKeys.value.length ? sortKeys.value : ['score_desc'];

    const sorted = [...filtered].sort((a, b) => {
      for (const k of keys) {
        const d = cmpByKey(a, b, k);
        if (d !== 0) return d;
      }
      return 0;
    });

    return sorted.map((x, i) => ({ ...x, rank: i + 1, score: employeeScoreNumber(x) }));
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

  function onZoneClick(zone) {
    const zName = zone?.zone || zone?.name;
    const found = zones.value.find((x) => String(x.zone) === String(zName));
    viewZoneDetails(found || { zone: zName, total: zone?.total ?? zone?.value ?? 0 });
  }

  async function viewZoneDetails(zone) {
    selectedZone.value = zone;
    zoneDialog.value = true;

    if (typeof repo.fetchZoneDetails === 'function') {
      try {
        zoneDialogLoading.value = true;
        const full = await repo.fetchZoneDetails(zone.zone);
        selectedZone.value = full || zone;
      } catch (_) {
        selectedZone.value = zone;
      } finally {
        zoneDialogLoading.value = false;
      }
    }
  }

  function closeZoneDialog() {
    zoneDialog.value = false;
  }

  async function openEmployeeDialog(employee) {
    selectedEmployee.value = employee;
    employeeDialog.value = true;

    if (typeof repo.fetchEmployeeDetails === 'function') {
      try {
        employeeDialogLoading.value = true;
        const full = await repo.fetchEmployeeDetails(employee.id);
        selectedEmployee.value = full || employee;
      } catch (_) {
        selectedEmployee.value = employee;
      } finally {
        employeeDialogLoading.value = false;
      }
    }
  }
</script>
<style scoped>
  /* Add these styles for the new toggles */
  .finance-period-toggle {
    border: 1px solid rgba(var(--v-theme-primary), 0.2);
    background: rgba(var(--v-theme-primary), 0.05);
  }
  /* 
  .table-mode-toggle {
    border: 1px solid rgba(var(--v-theme-primary), 0.2);
    background: rgba(var(--v-theme-primary), 0.05);
  } */

  /* Rest of the styles remain the same as your original... */
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

  .fab-stack {
    position: fixed;
    right: 24px;
    display: flex;
    flex-direction: row;
    gap: 10px;
    z-index: 100;
  }

  /* Compact cards */
  /* =========================
   Cards (Beautiful + no fixed height)
   ========================= */

  .stats-row {
    row-gap: 12px;
  }

  .stat-card {
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.08);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    position: relative;
    overflow: hidden;
  }

  .stat-card-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.12) !important;
    border-color: rgba(var(--v-theme-primary), 0.22);
  }

  .stat-card-body {
    padding: 12px 12px !important;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  /* Accent line */
  .stat-accent {
    position: absolute;
    inset: 0 auto auto 0;
    width: 100%;
    height: 4px;
    opacity: 0.95;
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

  /* Top row inside card */
  .stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }

  /* Icon wrapper */
  .card-icon-wrapper {
    width: 40px;
    height: 40px;
    border-radius: 12px;
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

  /* Title + Number */
  .stat-title {
    font-size: 0.78rem;
    color: rgba(0, 0, 0, 0.55);
  }

  .stat-main {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
  }

  .stat-number {
    font-size: 1.45rem;
    line-height: 1.1;
    font-weight: 900;
    letter-spacing: 0.2px;
  }

  /* Bottom area */
  .stat-bottom {
    margin-top: 2px;
  }

  /* Chips grid that wraps nicely */
  .chips-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
  }

  /* Compact chip look */
  .chip-compact {
    height: 22px !important;
    padding: 0 8px !important;
    font-size: 0.72rem !important;
  }

  /* Progress spacing */
  .stat-progress {
    margin-top: 2px;
    margin-bottom: 2px;
  }

  /* Resources actions line */
  .actions-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
  }

  /* Money/Oil bottom split meta */
  .split-meta {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
  }

  .meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(0, 0, 0, 0.04);
  }

  /* Activity dots (events card) */
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

  /* Dark mode tweaks */
  :deep(.v-theme--dark) .stat-card {
    background: #1e1e1e;
    border-color: rgba(255, 255, 255, 0.08);
  }

  :deep(.v-theme--dark) .stat-title {
    color: rgba(255, 255, 255, 0.65);
  }

  :deep(.v-theme--dark) .meta-item {
    background: rgba(255, 255, 255, 0.06);
  }

  /* Scrollbar */
  .table-scroll-wrap {
    position: relative;
  }

  .table-scroll-wrap :deep(.v-data-table__wrapper),
  .table-scroll-wrap :deep(.v-table__wrapper) {
    overflow-y: auto !important;
    scrollbar-gutter: stable;
    padding-right: 2px;
    scrollbar-width: none;
    scrollbar-color: rgba(0, 0, 0, 0.28) transparent;
  }

  .table-scroll-wrap:hover :deep(.v-data-table__wrapper),
  .table-scroll-wrap:hover :deep(.v-table__wrapper) {
    scrollbar-width: thin;
  }

  .table-scroll-wrap :deep(.v-data-table__wrapper::-webkit-scrollbar),
  .table-scroll-wrap :deep(.v-table__wrapper::-webkit-scrollbar) {
    width: 0px;
    height: 0px;
  }

  .table-scroll-wrap:hover :deep(.v-data-table__wrapper::-webkit-scrollbar),
  .table-scroll-wrap:hover :deep(.v-table__wrapper::-webkit-scrollbar) {
    width: 6px;
    height: 6px;
  }

  .table-scroll-wrap :deep(.v-data-table__wrapper::-webkit-scrollbar-track),
  .table-scroll-wrap :deep(.v-table__wrapper::-webkit-scrollbar-track) {
    background: transparent;
  }

  .table-scroll-wrap :deep(.v-data-table__wrapper::-webkit-scrollbar-thumb),
  .table-scroll-wrap :deep(.v-table__wrapper::-webkit-scrollbar-thumb) {
    background: rgba(0, 0, 0, 0.22);
    border-radius: 999px;
    border: 2px solid transparent;
    background-clip: padding-box;
  }

  .table-scroll-wrap:hover :deep(.v-data-table__wrapper::-webkit-scrollbar-thumb),
  .table-scroll-wrap:hover :deep(.v-table__wrapper::-webkit-scrollbar-thumb) {
    background: rgba(0, 0, 0, 0.32);
  }

  /* Dark mode */
  :deep(.v-theme--dark) .table-scroll-wrap :deep(.v-data-table__wrapper::-webkit-scrollbar-thumb),
  :deep(.v-theme--dark) .table-scroll-wrap :deep(.v-table__wrapper::-webkit-scrollbar-thumb) {
    background: rgba(255, 255, 255, 0.22);
  }

  :deep(.v-theme--dark)
    .table-scroll-wrap:hover
    :deep(.v-data-table__wrapper::-webkit-scrollbar-thumb),
  :deep(.v-theme--dark) .table-scroll-wrap:hover :deep(.v-table__wrapper::-webkit-scrollbar-thumb) {
    background: rgba(255, 255, 255, 0.32);
  }

  :deep(.v-theme--dark) .dashboard-container {
    background: radial-gradient(
        1200px 600px at 0% 0%,
        rgba(var(--v-theme-primary), 0.18),
        transparent 60%
      ),
      radial-gradient(900px 500px at 100% 10%, rgba(var(--v-theme-teal), 0.14), transparent 55%),
      #121212;
  }
  .score-bar-cell {
    display: flex;
    justify-content: center;
  }

  .score-value-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    line-height: 1.1;
  }

  .score-value-cell .score-number {
    font-weight: 900;
    font-size: 1rem;
  }

  .score-value-cell .score-label {
    margin-top: 2px;
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
  .two-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    align-items: center;
  }

  .two-metrics > div {
    border-radius: 10px;
    padding: 4px 6px;
    background: rgba(0, 0, 0, 0.03);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }
  .dash-filter-btn {
    height: 40px;
    border: 1px solid rgba(var(--v-theme-outline), 0.14);
    margin-left: 12px;
  }

  .dash-filter-menu {
    min-width: 360px;
    border: 1px solid rgba(var(--v-theme-outline), 0.12);
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.12);
  }
</style>
