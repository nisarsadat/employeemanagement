<template>
  <v-navigation-drawer
    v-model="model"
    location="right"
    width="450"
    temporary
    :dir="rtl ? 'rtl' : 'ltr'"
    elevation="6"
    class="saved-drawer"
  >
    <div class="drawer-content pa-5">
      <!-- Header with gradient -->
      <div class="header-gradient pa-4 rounded-lg mb-4">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center" style="gap: 12px">
            <v-avatar size="40" class="elevation-2 bg-white">
              <v-icon color="primary" size="24">mdi-map-marker-star</v-icon>
            </v-avatar>

            <div class="min-w-0">
              <div class="text-h6 font-weight-bold text-white one-line">
                {{ rtl ? 'نقاط ذخیره شده' : 'Saved Locations' }}
              </div>
              <div class="text-caption text-white text-opacity-85 one-line">
                {{ rtl ? 'مدیریت مکان‌های دلخواه' : 'Manage your favorite places' }}
              </div>
            </div>
          </div>

          <v-btn
            icon
            variant="tonal"
            size="small"
            color="white"
            class="elevation-1"
            @click="model = false"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
      </div>

      <!-- Reference Point Selector -->
      <div v-if="filteredPoints.length > 1" class="reference-selector pa-3 mb-4 rounded-lg bg-grey-lighten-4">
        <div class="d-flex align-center justify-space-between mb-2">
          <div class="text-caption font-weight-medium">
            <v-icon size="16" class="mr-1">mdi-map-marker-radius</v-icon>
            {{ rtl ? 'محاسبه فاصله از' : 'Calculate distance from' }}
          </div>
          <v-btn
            v-if="referencePoint"
            icon
            size="x-small"
            variant="text"
            @click="referencePoint = null"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
        
        <v-select
          v-model="referencePoint"
          :items="referencePointOptions"
          density="compact"
          variant="outlined"
          hide-details
          :label="rtl ? 'انتخاب نقطه مرجع' : 'Select reference point'"
          item-title="title"
          item-value="key"
          return-object
        >
          <template #selection="{ item }">
            <div class="d-flex align-center">
              <v-avatar size="24" class="mr-2">
                <v-img :src="item.raw.pictureResolved" v-if="item.raw.hasImage">
                  <template #placeholder>
                    <v-icon size="14">{{ item.raw.typeIcon }}</v-icon>
                  </template>
                </v-img>
                <v-icon v-else size="14">{{ item.raw.typeIcon }}</v-icon>
              </v-avatar>
              <span class="text-truncate">{{ item.title }}</span>
            </div>
          </template>
        </v-select>
      </div>

      <!-- Search with advanced filters -->
      <div class="search-section mb-4">
        <v-text-field
          v-model="q"
          density="compact"
          variant="outlined"
          hide-details
          clearable
          single-line
          prepend-inner-icon="mdi-magnify"
          :label="rtl ? 'جستجو در نقاط...' : 'Search points...'"
          class="search-field elevation-1"
        >
          <template #append-inner>
            <v-menu location="bottom">
              <template #activator="{ props: menuProps }">
                <v-btn
                  v-bind="menuProps"
                  icon
                  size="x-small"
                  variant="text"
                  class="ml-1"
                >
                  <v-badge
                    v-if="activeFiltersCount > 0"
                    :content="activeFiltersCount"
                    color="primary"
                    size="x-small"
                    inline
                  >
                    <v-icon>mdi-filter-variant</v-icon>
                  </v-badge>
                  <v-icon v-else>mdi-filter-variant</v-icon>
                </v-btn>
              </template>
              <v-list density="compact">
                <v-list-subheader>{{ rtl ? 'فیلتر بر اساس نوع' : 'Filter by Type' }}</v-list-subheader>
                <v-list-item @click="toggleFilterType('all')">
                  <template #prepend>
                    <v-icon :color="filterType === 'all' ? 'primary' : ''">
                      mdi-format-list-bulleted
                    </v-icon>
                  </template>
                  <v-list-item-title>
                    {{ rtl ? 'همه انواع' : 'All Types' }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item @click="toggleFilterType('resource')">
                  <template #prepend>
                    <v-icon :color="filterType === 'resource' ? 'primary' : ''">
                      mdi-cog
                    </v-icon>
                  </template>
                  <v-list-item-title>
                    {{ rtl ? 'منابع' : 'Resources' }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item @click="toggleFilterType('employee')">
                  <template #prepend>
                    <v-icon :color="filterType === 'employee' ? 'primary' : ''">
                      mdi-account
                    </v-icon>
                  </template>
                  <v-list-item-title>
                    {{ rtl ? 'کارمندان' : 'Employees' }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item @click="toggleFilterType('event')">
                  <template #prepend>
                    <v-icon :color="filterType === 'event' ? 'primary' : ''">
                      mdi-calendar
                    </v-icon>
                  </template>
                  <v-list-item-title>
                    {{ rtl ? 'رویدادها' : 'Events' }}
                  </v-list-item-title>
                </v-list-item>
                <v-divider />
                <v-list-subheader>{{ rtl ? 'مرتب سازی' : 'Sorting' }}</v-list-subheader>
                <v-list-item @click="toggleSort('name')">
                  <template #prepend>
                    <v-icon :color="sortBy === 'name' ? 'primary' : ''">
                      mdi-sort-alphabetical
                    </v-icon>
                  </template>
                  <v-list-item-title>
                    {{ rtl ? 'بر اساس نام' : 'By name' }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item @click="toggleSort('date')">
                  <template #prepend>
                    <v-icon :color="sortBy === 'date' ? 'primary' : ''">
                      mdi-sort-clock
                    </v-icon>
                  </template>
                  <v-list-item-title>
                    {{ rtl ? 'بر اساس تاریخ' : 'By date' }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item @click="toggleSort('distance')" v-if="referencePoint">
                  <template #prepend>
                    <v-icon :color="sortBy === 'distance' ? 'primary' : ''">
                      mdi-sort-numeric
                    </v-icon>
                  </template>
                  <v-list-item-title>
                    {{ rtl ? 'بر اساس فاصله' : 'By distance' }}
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
        </v-text-field>
      </div>

      <!-- Stats and Actions -->
      <div class="d-flex align-center justify-space-between mb-3">
        <div class="stats-chip d-flex align-center">
          <v-chip size="small" variant="outlined" color="primary" class="mr-2">
            <v-icon start size="16">mdi-counter</v-icon>
            {{ filteredPoints.length }}
          </v-chip>
          <v-chip v-if="referencePoint" size="small" variant="tonal" color="info" class="mr-2">
            <v-icon start size="16">mdi-ruler</v-icon>
            {{ totalDistance.toFixed(1) }} km
          </v-chip>
          <v-chip v-if="favoriteCount > 0" size="small" variant="tonal" color="amber" class="mr-2">
            <v-icon start size="16">mdi-star</v-icon>
            {{ favoriteCount }}
          </v-chip>
        </div>

        <div class="d-flex" style="gap: 6px">
          <v-btn
            icon
            size="small"
            variant="tonal"
            color="success"
            @click="exportToExcel"
            :title="rtl ? 'خروجی اکسل' : 'Export Excel'"
            :disabled="filteredPoints.length === 0"
          >
            <v-icon>mdi-file-excel</v-icon>
          </v-btn>

          <v-btn
            icon
            size="small"
            variant="tonal"
            color="info"
            @click="sharePoints"
            :title="rtl ? 'اشتراک گذاری' : 'Share'"
            :disabled="filteredPoints.length === 0"
          >
            <v-icon>mdi-share-variant</v-icon>
          </v-btn>

          <v-btn
            icon
            size="small"
            variant="tonal"
            color="warning"
            @click="showVisitedStats = !showVisitedStats"
            :title="rtl ? 'آمار بازدید' : 'Visit stats'"
          >
            <v-icon>mdi-chart-bar</v-icon>
          </v-btn>

          <v-btn
            icon
            size="small"
            variant="tonal"
            @click="refreshPoints"
            :title="rtl ? 'تازه‌سازی' : 'Refresh'"
          >
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </div>
      </div>

      <!-- Stats Overview -->
      <v-expand-transition>
        <div v-if="showVisitedStats && filteredPoints.length > 0" class="stats-overview pa-3 mb-3 rounded-lg bg-grey-lighten-4">
          <div class="text-caption font-weight-medium mb-2">{{ rtl ? 'آمار بازدید' : 'Visit Statistics' }}</div>
          <div class="d-flex justify-space-between">
            <div class="text-center">
              <div class="text-h6">{{ visitedCount }}</div>
              <div class="text-caption text-medium-emphasis">{{ rtl ? 'بازدید شده' : 'Visited' }}</div>
            </div>
            <div class="text-center">
              <div class="text-h6">{{ unvisitedCount }}</div>
              <div class="text-caption text-medium-emphasis">{{ rtl ? 'بازدید نشده' : 'Unvisited' }}</div>
            </div>
            <div class="text-center">
              <div class="text-h6">{{ favoriteCount }}</div>
              <div class="text-caption text-medium-emphasis">{{ rtl ? 'مورد علاقه' : 'Favorites' }}</div>
            </div>
            <div class="text-center">
              <div class="text-h6">{{ averageVisits.toFixed(1) }}</div>
              <div class="text-caption text-medium-emphasis">{{ rtl ? 'میانگین بازدید' : 'Avg Visits' }}</div>
            </div>
          </div>
        </div>
      </v-expand-transition>
      <!-- List with Enhanced Features -->
      <div v-if="filteredPoints.length" class="list nice-scroll">
        <v-expand-transition group>
          <div
            v-for="(it, index) in filteredPoints"
            :key="it.key"
            class="list-item pa-3 mb-2"
            :class="{ 
              'visited-item': it.visitCount > 0,
              'unvisited-item': it.visitCount === 0 
            }"
            role="button"
            tabindex="0"
            @click="select(it)"
            @keydown.enter="select(it)"
            @contextmenu.prevent="showContextMenu($event, it)"
          >
            <div class="d-flex align-center" style="gap: 12px">
              <!-- Badge with index -->
              <div class="index-badge d-flex align-center justify-center" :class="{ 'visited-badge': it.visitCount > 0 }">
                {{ index + 1 }}
              </div>

              <!-- Avatar with image or icon -->
              <v-avatar
                size="52"
                class="elevation-1"
                :class="{ 
                  'has-image': it.hasImage && !it.imageError,
                  'favorite-avatar': it.isFavorite,
                  'visited-avatar': it.visitCount > 0
                }"
              >
                <!-- Show actual image if available -->
                <v-img
                  v-if="it.hasImage && !it.imageError"
                  :src="it.pictureResolved"
                  cover
                  :alt="it.title || 'Location'"
                  @error="markImageError(it.key)"
                  class="cover-img"
                >
                  <!-- Favorite overlay -->
                  <template v-if="it.isFavorite" #default>
                    <div class="favorite-overlay">
                      <v-icon size="16" color="amber">mdi-star</v-icon>
                    </div>
                  </template>
                  <!-- Visited overlay -->
                  <template v-if="it.visitCount > 0" #default>
                    <div class="visited-overlay">
                      <v-icon size="14" color="white">mdi-check</v-icon>
                    </div>
                  </template>
                </v-img>
                
                <!-- Show icon if no image or image error -->
                <div v-else class="icon-placeholder" :style="{ backgroundColor: getTypeColor(it.type) }">
                  <v-icon :color="it.isFavorite ? 'amber' : 'white'" size="24">
                    {{ it.typeIcon }}
                  </v-icon>
                </div>
              </v-avatar>

              <!-- Main Content -->
              <div class="min-w-0 flex-grow-1">
                <div class="d-flex align-center mb-1">
                  <div class="text-body-1 font-weight-bold one-line">
                    {{ it.title || (rtl ? 'بدون نام' : 'Untitled') }}
                    <v-icon v-if="it.isFavorite" size="16" color="amber" class="ml-1">
                      mdi-star
                    </v-icon>
                  </div>
                  <v-spacer />
                  <div class="d-flex align-center" style="gap: 4px">
                    <v-chip
                      v-if="it.status"
                      size="x-small"
                      :color="getStatusColor(it.status)"
                      class="ml-2"
                    >
                      {{ it.status }}
                    </v-chip>
                    <v-icon
                      v-if="it.visitCount > 0"
                      size="16"
                      color="success"
                      :title="rtl ? `${it.visitCount} بار بازدید شده` : `Visited ${it.visitCount} times`"
                    >
                      mdi-check-circle
                    </v-icon>
                  </div>
                </div>

                <!-- Type and Subtitle -->
                <div class="text-caption text-medium-emphasis one-line mb-1">
                  <v-icon size="14" class="mr-1">{{ it.typeIcon }}</v-icon>
                  {{ it.typeLabel }}
                  <span v-if="it.subtitle"> • {{ it.subtitle }}</span>
                </div>

                <!-- Coordinates and Distance -->
                <div class="d-flex align-center justify-space-between">
                  <div class="text-caption coord-line">
                    <v-icon size="14" class="mr-1">mdi-crosshairs-gps</v-icon>
                    {{ formatCoords(it.coordinates) }}
                  </div>
                  <div v-if="it.distanceFromReference !== null" class="text-caption" :class="getDistanceColor(it.distanceFromReference)">
                    <v-icon size="12" class="mr-1">mdi-ruler</v-icon>
                    {{ it.distanceFromReference }} km
                  </div>
                </div>

                <!-- Metadata row -->
                <div class="d-flex align-center justify-space-between mt-1">
                  <div v-if="it.lastVisited" class="text-caption text-disabled">
                    <v-icon size="12" class="mr-1">mdi-clock-outline</v-icon>
                    {{ formatDate(it.lastVisited) }}
                  </div>
                  <div v-if="it.createdAt" class="text-caption text-disabled">
                    <v-icon size="12" class="mr-1">mdi-calendar-plus</v-icon>
                    {{ formatDate(it.createdAt, true) }}
                  </div>
                </div>
              </div>

              <!-- Quick Actions -->
              <div class="d-flex align-center" style="gap: 4px">
                <v-btn
                  icon
                  size="x-small"
                  variant="text"
                  @click.stop="copyCoords(it.coordinates)"
                  :title="rtl ? 'کپی مختصات' : 'Copy coordinates'"
                >
                  <v-icon size="18">mdi-content-copy</v-icon>
                </v-btn>

                <v-btn
                  icon
                  size="x-small"
                  variant="text"
                  :color="it.isFavorite ? 'amber' : ''"
                  @click.stop="toggleFavorite(it)"
                  :title="rtl ? 'نشان کردن' : 'Toggle favorite'"
                >
                  <v-icon size="18">
                    {{ it.isFavorite ? 'mdi-star' : 'mdi-star-outline' }}
                  </v-icon>
                </v-btn>

                <v-btn
                  icon
                  size="x-small"
                  variant="text"
                  :color="it.visitCount > 0 ? 'success' : ''"
                  @click.stop="markAsVisited(it)"
                  :title="rtl ? 'علامت‌گذاری بازدید' : 'Mark as visited'"
                >
                  <v-icon size="18">
                    {{ it.visitCount > 0 ? 'mdi-check-circle' : 'mdi-check-circle-outline' }}
                  </v-icon>
                </v-btn>

                <v-icon size="20" color="primary" class="opacity-70">
                  {{ rtl ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
                </v-icon>
              </div>
            </div>

            <!-- Progress bar for visited status -->
            <v-progress-linear
              v-if="it.visitCount > 0"
              :model-value="Math.min(it.visitCount * 20, 100)"
              height="4"
              color="success"
              class="mt-2"
            />
          </div>
        </v-expand-transition>
      </div>

      <!-- Empty State with Illustration -->
      <div v-else class="empty-state d-flex flex-column align-center justify-center pa-6">
        <v-avatar size="80" color="grey-lighten-3" class="mb-4">
          <v-icon size="40" color="grey">mdi-map-marker-off</v-icon>
        </v-avatar>
        <div class="text-h6 font-weight-medium mb-2 text-center">
          {{ rtl ? 'نقطه‌ای یافت نشد' : 'No points found' }}
        </div>
        <div class="text-body-2 text-medium-emphasis text-center mb-4">
          {{ rtl ? 'می‌توانید نقاط جدید اضافه کنید یا عبارت جستجو را تغییر دهید' : 'You can add new points or change your search term' }}
        </div>
        <v-btn
          variant="outlined"
          color="primary"
          prepend-icon="mdi-plus"
          @click="addNewPoint"
          class="mb-2"
        >
          {{ rtl ? 'افزودن نقطه جدید' : 'Add New Point' }}
        </v-btn>
        <v-btn
          v-if="q || filterType !== 'all'"
          variant="text"
          size="small"
          @click="clearFilters"
        >
          {{ rtl ? 'پاک کردن فیلترها' : 'Clear filters' }}
        </v-btn>
      </div>

      <!-- Bottom Actions -->
      <div v-if="filteredPoints.length > 0" class="bottom-actions mt-4 pt-3 border-t">
        <div class="d-flex justify-space-between align-center">
          <div class="text-caption text-medium-emphasis">
            {{ rtl ? 'حافظه مصرفی' : 'Memory usage' }}: {{ formatBytes(storageSize) }}
          </div>
          <div class="d-flex" style="gap: 8px">
            <v-btn
              variant="tonal"
              color="error"
              size="small"
              prepend-icon="mdi-delete"
              @click="clearAllPoints"
              :disabled="filteredPoints.length === 0"
            >
              {{ rtl ? 'پاک کردن' : 'Clear' }}
            </v-btn>
            <v-btn
              variant="flat"
              color="primary"
              size="small"
              prepend-icon="mdi-download"
              @click="downloadBackup"
              :disabled="filteredPoints.length === 0"
            >
              {{ rtl ? 'پشتیبان' : 'Backup' }}
            </v-btn>
          </div>
        </div>
      </div>

      <!-- Context Menu -->
      <v-menu
        v-model="contextMenu.show"
        :position-x="contextMenu.x"
        :position-y="contextMenu.y"
        absolute
        offset-y
      >
        <v-list density="compact">
          <v-list-item @click="navigateToPoint(contextMenu.item)">
            <template #prepend>
              <v-icon>mdi-navigation</v-icon>
            </template>
            <v-list-item-title>
              {{ rtl ? 'مسیریابی' : 'Navigate' }}
            </v-list-item-title>
          </v-list-item>
          <v-list-item @click="setAsReference(contextMenu.item)">
            <template #prepend>
              <v-icon>mdi-map-marker-radius</v-icon>
            </template>
            <v-list-item-title>
              {{ rtl ? 'تنظیم به عنوان مرجع' : 'Set as reference' }}
            </v-list-item-title>
          </v-list-item>
          <v-list-item @click="markAsVisited(contextMenu.item)">
            <template #prepend>
              <v-icon :color="contextMenu.item?.visitCount > 0 ? 'success' : ''">
                {{ contextMenu.item?.visitCount > 0 ? 'mdi-check-circle' : 'mdi-check-circle-outline' }}
              </v-icon>
            </template>
            <v-list-item-title>
              {{ rtl ? 
                (contextMenu.item?.visitCount > 0 ? 'بازدید نشده' : 'علامت بازدید') : 
                (contextMenu.item?.visitCount > 0 ? 'Mark unvisited' : 'Mark visited') 
              }}
            </v-list-item-title>
          </v-list-item>
          <v-divider />
          <v-list-item @click="editPoint(contextMenu.item)">
            <template #prepend>
              <v-icon>mdi-pencil</v-icon>
            </template>
            <v-list-item-title>
              {{ rtl ? 'ویرایش' : 'Edit' }}
            </v-list-item-title>
          </v-list-item>
          <v-list-item @click="duplicatePoint(contextMenu.item)">
            <template #prepend>
              <v-icon>mdi-content-duplicate</v-icon>
            </template>
            <v-list-item-title>
              {{ rtl ? 'تکثیر' : 'Duplicate' }}
            </v-list-item-title>
          </v-list-item>
          <v-list-item @click="deletePoint(contextMenu.item)" class="text-error">
            <template #prepend>
              <v-icon color="error">mdi-delete</v-icon>
            </template>
            <v-list-item-title>
              {{ rtl ? 'حذف' : 'Delete' }}
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>

      <!-- Snackbar for notifications -->
      <v-snackbar v-model="snackbar.show" :timeout="3000" :color="snackbar.color">
        {{ snackbar.message }}
        <template #actions>
          <v-btn variant="text" @click="snackbar.show = false">
            {{ rtl ? 'باشه' : 'OK' }}
          </v-btn>
        </template>
      </v-snackbar>
    </div>
  </v-navigation-drawer>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  rtl: Boolean,
  points: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'select', 'refresh', 'add', 'edit', 'delete']);

// State variables
const q = ref('');
const filterType = ref('all');
const sortBy = ref('name');
const sortOrder = ref('asc');
const imgErrorMap = ref({});
const favoritePoints = ref(new Set());
const visitedPoints = ref({}); // key -> visitCount
const referencePoint = ref(null);
const showVisitedStats = ref(false);
const contextMenu = ref({
  show: false,
  x: 0,
  y: 0,
  item: null,
});
const snackbar = ref({
  show: false,
  message: '',
  color: 'success',
});
const isOnline = ref(navigator.onLine);

// Computed model for drawer
const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
});

// Computed values
const activeFiltersCount = computed(() => {
  let count = 0;
  if (filterType.value !== 'all') count++;
  if (q.value) count++;
  if (referencePoint.value) count++;
  return count;
});

const favoriteCount = computed(() => {
  return filteredPoints.value.filter(p => p.isFavorite).length;
});

const visitedCount = computed(() => {
  return filteredPoints.value.filter(p => p.visitCount > 0).length;
});

const unvisitedCount = computed(() => {
  return filteredPoints.value.filter(p => p.visitCount === 0).length;
});

const averageVisits = computed(() => {
  const visitedPoints = filteredPoints.value.filter(p => p.visitCount > 0);
  if (visitedPoints.length === 0) return 0;
  const total = visitedPoints.reduce((sum, p) => sum + p.visitCount, 0);
  return total / visitedPoints.length;
});

const storageSize = computed(() => {
  // Estimate storage size
  const data = JSON.stringify(filteredPoints.value);
  return new Blob([data]).size;
});

// Watch for points changes
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    // Reset image errors when drawer opens
    imgErrorMap.value = {};
  }
}, { immediate: true });

// Online/offline handling
onMounted(() => {
  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);
  
  // Load favorites and visited data from localStorage
  loadPersistentData();
});

onUnmounted(() => {
  window.removeEventListener('online', handleOnline);
  window.removeEventListener('offline', handleOffline);
});

function handleOnline() {
  isOnline.value = true;
  showNotification(rtl ? 'آنلاین شدید' : 'You are online', 'success');
}

function handleOffline() {
  isOnline.value = false;
  showNotification(rtl ? 'آفلاین شدید' : 'You are offline', 'warning');
}

function loadPersistentData() {
  try {
    const savedFavorites = localStorage.getItem('savedPointsFavorites');
    if (savedFavorites) {
      favoritePoints.value = new Set(JSON.parse(savedFavorites));
    }
    
    const savedVisited = localStorage.getItem('savedPointsVisited');
    if (savedVisited) {
      visitedPoints.value = JSON.parse(savedVisited);
    }
    
    const savedReference = localStorage.getItem('savedPointsReference');
    if (savedReference) {
      referencePoint.value = JSON.parse(savedReference);
    }
  } catch (error) {
    console.error('Error loading persistent data:', error);
  }
}

function savePersistentData() {
  try {
    localStorage.setItem('savedPointsFavorites', JSON.stringify([...favoritePoints.value]));
    localStorage.setItem('savedPointsVisited', JSON.stringify(visitedPoints.value));
    if (referencePoint.value) {
      localStorage.setItem('savedPointsReference', JSON.stringify(referencePoint.value));
    }
  } catch (error) {
    console.error('Error saving persistent data:', error);
  }
}

// Helper functions
function normStr(s) {
  return String(s || '').toLowerCase().trim();
}

function pickFirst(...vals) {
  for (const v of vals) {
    if (v === null || v === undefined) continue;
    const s = String(v).trim();
    if (s && s !== '-' && s !== 'null' && s !== 'undefined') return s;
  }
  return '';
}

function detectType(p) {
  const explicit = pickFirst(p?.type, p?.kind, p?.category, p?.typeLabel, p?.type_label);
  if (explicit) return explicit.toLowerCase();
  
  if (p?.resource || p?.resource_id || p?.resourceId) return 'resource';
  if (p?.office_employee || p?.officeEmployee || p?.office_employee_id || p?.employee_id)
    return 'employee';
  if (p?.event_id || p?.eventId) return 'event';
  
  return 'point';
}

function labelForType(type) {
  const t = String(type || '').toLowerCase();
  
  if (t.includes('resource')) return props.rtl ? 'منبع' : 'Resource';
  if (t.includes('employee')) return props.rtl ? 'کارمند' : 'Employee';
  if (t.includes('event')) return props.rtl ? 'رویداد' : 'Event';
  
  return props.rtl ? 'نقطه' : 'Point';
}

function getIconForType(type) {
  const t = String(type || '').toLowerCase();
  
  if (t.includes('resource')) return 'mdi-cog';
  if (t.includes('employee')) return 'mdi-account';
  if (t.includes('event')) return 'mdi-calendar';
  
  return 'mdi-map-marker';
}

function getTypeColor(type) {
  const colors = {
    resource: '#4CAF50',
    employee: '#2196F3',
    event: '#FF9800',
    point: '#9C27B0'
  };
  return colors[type] || '#607D8B';
}

function resolvePicture(raw, imageError) {
  if (imageError || !raw) {
    return null; // Return null to show icon placeholder
  }
  
  const pic = String(raw).trim();
  if (!pic || pic === 'null' || pic === 'undefined') return null;
  
  // Handle relative URLs
  if (pic.startsWith('http://') || pic.startsWith('https://')) {
    return pic;
  }
  
  if (pic.startsWith('data:image')) {
    return pic; // Data URLs
  }
  
  if (pic.startsWith('/')) {
    return `${window.location.origin}${pic}`;
  }
  
  // Assume it's a relative path
  return `${window.location.origin}/${pic.replace(/^\//, '')}`;
}

// Normalize points with comprehensive image detection
const normalized = computed(() => {
  return (props.points || []).map((p) => {
    const key = String(p?.id ?? p?.coordinates ?? p?.name ?? p?.title ?? Math.random());
    
    const type = detectType(p);
    const typeLabel = pickFirst(p?.typeLabel, p?.type_label) || labelForType(type);
    const typeIcon = getIconForType(type);
    
    const title = pickFirst(p?.name, p?.title, props.rtl ? 'بدون نام' : 'Untitled');
    const subtitle = pickFirst(p?.note, p?.details, p?.description, p?.subtitle);
    const coordinates = pickFirst(p?.coordinates, p?.coordinate, p?.coords, p?.location);
    
    // Comprehensive image detection - check multiple possible field names
    const picture = pickFirst(
      p?.picture,
      p?.picture_url,
      p?.pictureUrl,
      p?.image_url,
      p?.imageUrl,
      p?.photo_url,
      p?.photoUrl,
      p?.photo,
      p?.image,
      p?.avatar,
      p?.avatar_url,
      p?.avatarUrl,
      p?.profile_picture,
      p?.profilePicture,
      p?.profile_picture_url,
      p?.profilePictureUrl,
      p?.thumbnail,
      p?.thumbnail_url,
      p?.thumbnailUrl
    );
    
    const hasImage = Boolean(picture && picture !== 'null' && picture !== 'undefined');
    const imageError = !!imgErrorMap.value[key];
    const pictureResolved = resolvePicture(picture, imageError);
    
    // Calculate distance from reference point if set
    let distanceFromReference = null;
    if (referencePoint.value && coordinates && referencePoint.value.coordinates) {
      distanceFromReference = calculateDistanceBetweenPoints(
        coordinates,
        referencePoint.value.coordinates
      );
    }
    
    // Get visit count from localStorage
    const visitCount = visitedPoints.value[key] || p?.visitCount || 0;
    
    return {
      key,
      id: p?.id ?? null,
      type,
      typeLabel,
      typeIcon,
      title,
      subtitle,
      coordinates,
      picture,
      hasImage: hasImage && pictureResolved !== null,
      imageError,
      pictureResolved,
      isFavorite: favoritePoints.value.has(key),
      status: p?.status || 'active',
      distanceFromReference,
      lastVisited: p?.lastVisited || p?.updated_at || p?.updatedAt || null,
      createdAt: p?.created_at || p?.createdAt || p?.date || null,
      visitCount,
      _raw: p,
    };
  });
});

// Reference point options
const referencePointOptions = computed(() => {
  return normalized.value.map(p => ({
    title: p.title,
    value: p.key,
    key: p.key,
    coordinates: p.coordinates,
    typeIcon: p.typeIcon,
    hasImage: p.hasImage,
    pictureResolved: p.pictureResolved,
  }));
});

// Filter and sort points
const filteredPoints = computed(() => {
  let filtered = normalized.value;
  
  // Filter by type
  if (filterType.value !== 'all') {
    filtered = filtered.filter(it => it.type === filterType.value);
  }
  
  // Filter by search query
  if (q.value) {
    const searchTerm = q.value.toLowerCase();
    filtered = filtered.filter(it => {
      const searchFields = [
        it.title,
        it.subtitle,
        it.typeLabel,
        it.coordinates,
        it.status,
      ];
      return searchFields.some(field => 
        field && field.toString().toLowerCase().includes(searchTerm)
      );
    });
  }
  
  // Sort
  filtered = [...filtered].sort((a, b) => {
    let compareResult = 0;
    
    switch (sortBy.value) {
      case 'name':
        compareResult = a.title.localeCompare(b.title);
        break;
      case 'date':
        const dateA = new Date(a.lastVisited || a.createdAt || 0);
        const dateB = new Date(b.lastVisited || b.createdAt || 0);
        compareResult = dateA - dateB;
        break;
      case 'distance':
        if (referencePoint.value) {
          const distA = a.distanceFromReference || Infinity;
          const distB = b.distanceFromReference || Infinity;
          compareResult = distA - distB;
        } else {
          compareResult = a.title.localeCompare(b.title);
        }
        break;
      default:
        compareResult = a.title.localeCompare(b.title);
    }
    
    return sortOrder.value === 'asc' ? compareResult : -compareResult;
  });
  
  return filtered;
});

// Calculate total distance from reference
const totalDistance = computed(() => {
  if (!referencePoint.value) return 0;
  return filteredPoints.value.reduce((sum, point) => {
    return sum + (point.distanceFromReference || 0);
  }, 0);
});

// Distance calculation function
function calculateDistanceBetweenPoints(coord1, coord2) {
  if (!coord1 || !coord2) return null;
  
  try {
    const [lat1, lng1] = parseCoordinates(coord1);
    const [lat2, lng2] = parseCoordinates(coord2);
    
    if (lat1 === null || lng1 === null || lat2 === null || lng2 === null) {
      return null;
    }
    
    // Haversine formula
    const R = 6371; // Earth's radius in km
    const dLat = toRad(lat2 - lat1);
    const dLng = toRad(lng2 - lng1);
    
    const a = 
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
      Math.sin(dLng / 2) * Math.sin(dLng / 2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;
    
    return parseFloat(distance.toFixed(1));
  } catch (error) {
    console.error('Distance calculation error:', error);
    return null;
  }
}

function parseCoordinates(coordString) {
  if (!coordString) return [null, null];
  
  try {
    const parts = coordString.split(/[,;]/).map(part => {
      const num = parseFloat(part.trim());
      return isNaN(num) ? null : num;
    });
    
    return parts.length >= 2 ? [parts[0], parts[1]] : [null, null];
  } catch {
    return [null, null];
  }
}

function toRad(degrees) {
  return degrees * Math.PI / 180;
}

// Image error handling
function markImageError(key) {
  imgErrorMap.value = { ...imgErrorMap.value, [key]: true };
}

// Toggle functions
function toggleFilterType(type) {
  filterType.value = filterType.value === type ? 'all' : type;
}

function toggleSort(field) {
  if (sortBy.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = field;
    sortOrder.value = 'asc';
  }
}

function clearFilters() {
  q.value = '';
  filterType.value = 'all';
  referencePoint.value = null;
}

// Actions
function select(item) {
  // Increment visit count
  const newCount = (visitedPoints.value[item.key] || 0) + 1;
  visitedPoints.value = { ...visitedPoints.value, [item.key]: newCount };
  savePersistentData();
  
  emit('select', item);
  showNotification(
    props.rtl ? `در حال انتقال به ${item.title}` : `Navigating to ${item.title}`,
    'info'
  );
}

function toggleFavorite(item) {
  if (favoritePoints.value.has(item.key)) {
    favoritePoints.value.delete(item.key);
    showNotification(
      props.rtl ? 'از نشان‌ها حذف شد' : 'Removed from favorites',
      'warning'
    );
  } else {
    favoritePoints.value.add(item.key);
    showNotification(
      props.rtl ? 'به نشان‌ها اضافه شد' : 'Added to favorites',
      'success'
    );
  }
  savePersistentData();
}

function markAsVisited(item) {
  const currentCount = visitedPoints.value[item.key] || 0;
  if (currentCount > 0) {
    // Reset to 0 (mark as unvisited)
    visitedPoints.value = { ...visitedPoints.value };
    delete visitedPoints.value[item.key];
    showNotification(
      props.rtl ? `بازدید ${item.title} بازنشانی شد` : `Reset visit count for ${item.title}`,
      'info'
    );
  } else {
    // Mark as visited
    visitedPoints.value = { ...visitedPoints.value, [item.key]: 1 };
    showNotification(
      props.rtl ? `${item.title} به عنوان بازدید شده علامت گذاری شد` : `Marked ${item.title} as visited`,
      'success'
    );
  }
  savePersistentData();
}

function setAsReference(item) {
  referencePoint.value = {
    key: item.key,
    title: item.title,
    coordinates: item.coordinates,
  };
  savePersistentData();
  showNotification(
    props.rtl ? `${item.title} به عنوان نقطه مرجع تنظیم شد` : `Set ${item.title} as reference point`,
    'success'
  );
  contextMenu.value.show = false;
}

function showContextMenu(e, item) {
  e.preventDefault();
  contextMenu.value = {
    show: true,
    x: e.clientX,
    y: e.clientY,
    item: item,
  };
}

async function copyCoords(coords) {
  try {
    await navigator.clipboard.writeText(String(coords || ''));
    showNotification(
      props.rtl ? 'مختصات کپی شد' : 'Coordinates copied',
      'success'
    );
  } catch (err) {
    // Fallback
    try {
      const textArea = document.createElement('textarea');
      textArea.value = String(coords || '');
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      showNotification(
        props.rtl ? 'مختصات کپی شد' : 'Coordinates copied',
        'info'
      );
    } catch (fallbackErr) {
      showNotification(
        props.rtl ? 'خطا در کپی کردن' : 'Copy failed',
        'error'
      );
    }
  }
}

// Export functions
function exportToExcel() {
  try {
    const data = filteredPoints.value.map(p => ({
      name: p.title,
      type: p.typeLabel,
      coordinates: p.coordinates,
      status: p.status,
      distance: p.distanceFromReference ? `${p.distanceFromReference} km` : '-',
      visits: p.visitCount,
      lastVisited: formatDate(p.lastVisited),
      created: formatDate(p.createdAt),
    }));
    
    // Create CSV
    const headers = Object.keys(data[0] || {}).join(',');
    const rows = data.map(row => 
      Object.values(row).map(val => `"${val || '-'}"`).join(',')
    ).join('\n');
    
    const csv = `${headers}\n${rows}`;
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `saved-points-${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    showNotification(
      props.rtl ? 'خروجی با موفقیت دانلود شد' : 'Export downloaded successfully',
      'success'
    );
  } catch (error) {
    console.error('Export error:', error);
    showNotification(
      props.rtl ? 'خطا در ایجاد خروجی' : 'Export failed',
      'error'
    );
  }
}

function sharePoints() {
  if (navigator.share) {
    const pointsText = filteredPoints.value
      .slice(0, 5)
      .map(p => `${p.title}: ${p.coordinates}`)
      .join('\n');
    
    navigator.share({
      title: props.rtl ? 'نقاط ذخیره شده' : 'Saved Points',
      text: `${filteredPoints.value.length} ${props.rtl ? 'نقطه ذخیره شده' : 'saved points'}\n\n${pointsText}`,
      url: window.location.href,
    }).catch(() => {
      showNotification(
        props.rtl ? 'اشتراک گذاری لغو شد' : 'Share cancelled',
        'info'
      );
    });
  } else {
    const pointsText = filteredPoints.value
      .map(p => `${p.title}: ${p.coordinates}`)
      .join('\n');
    copyCoords(pointsText);
  }
}

function downloadBackup() {
  try {
    const backup = {
      version: '1.1',
      timestamp: new Date().toISOString(),
      totalPoints: filteredPoints.value.length,
      points: filteredPoints.value.map(p => ({
        title: p.title,
        type: p.typeLabel,
        coordinates: p.coordinates,
        status: p.status,
        visitCount: p.visitCount,
        lastVisited: p.lastVisited,
        createdAt: p.createdAt,
        isFavorite: p.isFavorite,
      })),
      metadata: {
        referencePoint: referencePoint.value,
        totalVisits: visitedCount.value,
        totalFavorites: favoriteCount.value,
      }
    };
    
    const blob = new Blob([JSON.stringify(backup, null, 2)], {
      type: 'application/json',
    });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `points-backup-${new Date().toISOString().slice(0, 10)}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    showNotification(
      props.rtl ? 'پشتیبان گیری موفق' : 'Backup successful',
      'success'
    );
  } catch (error) {
    showNotification(
      props.rtl ? 'خطا در پشتیبان گیری' : 'Backup failed',
      'error'
    );
  }
}

function clearAllPoints() {
  if (confirm(props.rtl ? 'آیا از حذف همه نقاط اطمینان دارید؟' : 'Are you sure you want to delete all points?')) {
    // Clear all persistent data
    favoritePoints.value.clear();
    visitedPoints.value = {};
    referencePoint.value = null;
    savePersistentData();
    
    showNotification(
      props.rtl ? 'همه داده‌ها پاک شدند' : 'All data cleared',
      'warning'
    );
  }
}

function addNewPoint() {
  emit('add');
}

function refreshPoints() {
  emit('refresh');
  showNotification(
    props.rtl ? 'در حال تازه‌سازی...' : 'Refreshing...',
    'info'
  );
}

function editPoint(item) {
  emit('edit', item);
  contextMenu.value.show = false;
}

function duplicatePoint(item) {
  showNotification(
    props.rtl ? 'ویژگی تکثیر در حال توسعه' : 'Duplicate feature in development',
    'info'
  );
  contextMenu.value.show = false;
}

function deletePoint(item) {
  if (confirm(props.rtl ? 'آیا از حذف این نقطه اطمینان دارید؟' : 'Are you sure you want to delete this point?')) {
    emit('delete', item);
    showNotification(
      props.rtl ? `نقطه "${item.title}" حذف شد` : `Point "${item.title}" deleted`,
      'warning'
    );
    contextMenu.value.show = false;
  }
}

function navigateToPoint(item) {
  select(item);
  contextMenu.value.show = false;
}

// Helper functions
function formatCoords(coords) {
  if (!coords || coords === '-') return '-';
  
  // Try to parse coordinates
  const parts = coords.split(/[,;]/).map(part => part.trim());
  if (parts.length >= 2) {
    const lat = parseFloat(parts[0]).toFixed(6);
    const lng = parseFloat(parts[1]).toFixed(6);
    return `${lat}, ${lng}`;
  }
  
  return coords;
}

function formatDate(dateString, short = false) {
  if (!dateString) return props.rtl ? 'نامشخص' : 'Unknown';
  
  try {
    const date = new Date(dateString);
    if (short) {
      return new Intl.DateTimeFormat(props.rtl ? 'fa-IR' : 'en-US', {
        month: 'short',
        day: 'numeric',
      }).format(date);
    }
    return new Intl.DateTimeFormat(props.rtl ? 'fa-IR' : 'en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    }).format(date);
  } catch {
    return props.rtl ? 'نامشخص' : 'Unknown';
  }
}

function getStatusColor(status) {
  const statusMap = {
    active: 'success',
    inactive: 'warning',
    pending: 'info',
    completed: 'success',
    cancelled: 'error',
    available: 'success',
    busy: 'warning',
    offline: 'grey',
  };
  return statusMap[status?.toLowerCase()] || 'grey';
}

function getDistanceColor(distance) {
  if (distance < 1) return 'text-green';
  if (distance < 5) return 'text-light-green';
  if (distance < 20) return 'text-orange';
  return 'text-red';
}

function formatBytes(bytes) {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showNotification(message, color = 'success') {
  snackbar.value = {
    show: true,
    message,
    color,
  };
}
</script>

<style scoped>
.saved-drawer {
  border-radius: 20px 0 0 20px !important;
  overflow: hidden;
}

.drawer-content {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
}

.header-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.search-section {
  position: relative;
  z-index: 2;
}

.search-field :deep(.v-field) {
  border-radius: 12px;
  background: white;
}

.list {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  padding-right: 4px;
  padding-left: 4px;
}

.list-item {
  border-radius: 16px;
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
  background: white;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.list-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.list-item.visited-item {
  border-left: 4px solid #4CAF50;
}

.list-item.unvisited-item {
  border-left: 4px solid #FF9800;
}

.index-badge {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
  font-size: 12px;
  font-weight: bold;
}

.index-badge.visited-badge {
  background: rgba(76, 175, 80, 0.1);
  color: #4CAF50;
}

.v-avatar.has-image {
  border: 3px solid rgba(var(--v-theme-primary), 0.2);
  transition: border-color 0.3s ease;
}

.v-avatar.favorite-avatar {
  border-color: #ffb300;
  box-shadow: 0 0 0 2px rgba(255, 179, 0, 0.3);
}

.v-avatar.visited-avatar {
  border-color: #4CAF50;
}

.icon-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.favorite-overlay {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 20px;
  height: 20px;
  background: rgba(255, 179, 0, 0.9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
}

.visited-overlay {
  position: absolute;
  bottom: 4px;
  left: 4px;
  width: 20px;
  height: 20px;
  background: rgba(76, 175, 80, 0.9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
}

.empty-state {
  flex: 1;
  min-height: 300px;
}

.cover-img :deep(img) {
  object-fit: cover;
  transition: transform 0.3s ease;
}

.list-item:hover .cover-img :deep(img) {
  transform: scale(1.1);
}

/* Custom scrollbar */
.nice-scroll::-webkit-scrollbar {
  width: 8px;
}

.nice-scroll::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}

.nice-scroll::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-primary), 0.3);
  border-radius: 4px;
}

.nice-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(var(--v-theme-primary), 0.5);
}

/* RTL specific styles */
:dir(rtl) .list-item.visited-item {
  border-left: none;
  border-right: 4px solid #4CAF50;
}

:dir(rtl) .list-item.unvisited-item {
  border-left: none;
  border-right: 4px solid #FF9800;
}

:dir(rtl) .list-item:hover .cover-img :deep(img) {
  transform: scale(1.1) rotateY(180deg);
}

:dir(rtl) .index-badge {
  margin-left: 0;
  margin-right: 8px;
}

:dir(rtl) .favorite-overlay {
  right: auto;
  left: 4px;
}

:dir(rtl) .visited-overlay {
  left: auto;
  right: 4px;
}

/* Animation for new items */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.list-item {
  animation: slideIn 0.3s ease;
}

/* Stats overview */
.stats-overview {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .saved-drawer {
    width: 100% !important;
    border-radius: 0 !important;
  }
  
  .drawer-content {
    padding: 16px;
  }
  
  .list-item {
    padding: 12px;
  }
  
  .v-avatar {
    size: 44px;
  }
}
</style>