<template>
  <div ref="anchor" class="global-search" :class="{ 'is-compact': compact }">
    <v-btn
      v-if="compact"
      icon
      size="small"
      variant="tonal"
      class="search-icon-btn"
      :aria-label="t('جستجو در سیستم و معلومات')"
      @click="openCompact"
    >
      <v-icon size="18" color="primary">mdi-magnify</v-icon>
    </v-btn>

    <v-text-field
      v-else
      ref="fieldRef"
      v-model="query"
      class="search-field"
      density="compact"
      variant="solo"
      flat
      hide-details
      rounded
      clearable
      autocomplete="off"
      prepend-inner-icon="mdi-magnify"
      :placeholder="t('جستجو در سیستم و معلومات')"
      :aria-label="t('جستجو در سیستم و معلومات')"
      @focus="openPanel"
      @keydown="onKeydown"
      @click:clear="query = ''"
    >
      <template #append-inner>
        <span v-if="!query" class="search-kbd">{{ shortcutLabel }}</span>
      </template>
    </v-text-field>

    <teleport to="body">
      <div
        v-if="open"
        ref="panel"
        class="global-search-panel"
        :dir="isRtl ? 'rtl' : 'ltr'"
        :style="panelStyle"
      >
        <v-text-field
          v-if="compact"
          ref="compactFieldRef"
          v-model="query"
          class="search-field compact-field"
          density="compact"
          variant="solo"
          flat
          hide-details
          rounded
          clearable
          autocomplete="off"
          prepend-inner-icon="mdi-magnify"
          :placeholder="t('جستجو در سیستم و معلومات')"
          @keydown="onKeydown"
        />

        <div class="panel-scroll">
          <div v-if="loading && String(query || '').trim().length >= 2" class="panel-status">
            <v-progress-circular indeterminate size="16" width="2" color="primary" />
            <span>{{ t('در حال جستجو...') }}</span>
          </div>

          <template v-for="(item, index) in rows" :key="item.key">
            <div v-if="showSection(index)" class="section-label">
              {{ sectionLabel(item.section) }}
            </div>

            <button
              type="button"
              class="result-row"
              :class="{ 'is-active': index === activeIndex }"
              @mouseenter="activeIndex = index"
              @click="go(item)"
            >
              <span class="result-icon">
                <v-icon size="18" :icon="item.icon || 'mdi-magnify'" />
              </span>
              <span class="result-copy">
                <span class="result-title">{{ item.title }}</span>
                <span v-if="item.subtitle" class="result-subtitle">{{ item.subtitle }}</span>
              </span>
              <span class="result-badge">{{ t(item.label || 'سیستم') }}</span>
            </button>
          </template>

          <div v-if="showEmpty" class="panel-empty">
            <v-icon size="28" color="primary">mdi-magnify-remove-outline</v-icon>
            <div>{{ t('هیچ نتیجه‌ای پیدا نشد') }}</div>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDisplay } from 'vuetify';
import { useRouter } from 'vue-router';
import axios from '@/axios';
import { useLoginRepository } from '@/stores/LoginRepository';
import { matchSystemPages, suggestedSystemPages } from '@/search/systemCatalog';

const RECENT_KEY = 'global-search-recent';

const { t, locale } = useI18n();
const display = useDisplay();
const router = useRouter();
const login = useLoginRepository();

const query = ref('');
const open = ref(false);
const loading = ref(false);
const dataHits = ref([]);
const activeIndex = ref(0);
const anchor = ref(null);
const panel = ref(null);
const fieldRef = ref(null);
const compactFieldRef = ref(null);
const panelStyle = ref({});
const recent = ref(readRecent());

const compact = computed(() => display.smAndDown.value);
const isRtl = computed(() => ['fa', 'ps'].includes(locale.value));
const shortcutLabel = computed(() =>
  /mac/i.test(navigator.platform || '') ? '⌘K' : 'Ctrl K'
);

const can = (perm) => {
  if (!perm) return true;
  if (login.hasRole?.('admin') || login.can?.('*')) return true;
  return !!login.can?.(perm);
};

const systemHits = computed(() => matchSystemPages(query.value, can, t));
const suggestions = computed(() => suggestedSystemPages(can, t));

const rows = computed(() => {
  const term = String(query.value || '').trim();
  if (!term) {
    return [
      ...recent.value.map((item, index) => ({
        ...item,
        key: `recent-${index}-${item.path}`,
        section: 'recent',
      })),
      ...suggestions.value,
    ];
  }

  return [
    ...systemHits.value,
    ...dataHits.value.map((item) => ({
      ...item,
      key: item.id || `${item.path}-${item.title}`,
      section: 'records',
      subtitle: item.subtitle || '',
    })),
  ];
});

const showEmpty = computed(
  () => String(query.value || '').trim().length >= 2 && !loading.value && rows.value.length === 0
);

let timer = null;
let request = null;

watch(query, (value) => {
  activeIndex.value = 0;
  const term = String(value || '').trim();
  clearTimeout(timer);
  request?.abort();

  if (term.length < 2) {
    dataHits.value = [];
    loading.value = false;
    return;
  }

  loading.value = true;
  timer = setTimeout(() => fetchData(term), 280);
});

watch(open, async (isOpen) => {
  if (!isOpen) return;
  await nextTick();
  placePanel();
});

watch(compact, () => {
  if (open.value) nextTick(placePanel);
});

function showSection(index) {
  return index === 0 || rows.value[index].section !== rows.value[index - 1].section;
}

function sectionLabel(section) {
  if (section === 'recent') return t('جستجوهای اخیر');
  if (section === 'pages') return t('صفحات');
  if (section === 'records') return t('ریکاردها');
  return t('سیستم');
}

function readRecent() {
  try {
    const parsed = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');
    return Array.isArray(parsed) ? parsed.slice(0, 5) : [];
  } catch {
    return [];
  }
}

function remember(item) {
  const next = [
    {
      title: item.title,
      subtitle: item.subtitle || '',
      icon: item.icon,
      path: item.path,
      label: item.label || 'سیستم',
    },
    ...recent.value.filter((saved) => saved.path !== item.path || saved.title !== item.title),
  ].slice(0, 5);
  recent.value = next;
  localStorage.setItem(RECENT_KEY, JSON.stringify(next));
}

async function fetchData(term) {
  request?.abort();
  const current = new AbortController();
  request = current;
  loading.value = true;

  try {
    const { data } = await axios.get('/global-search', {
      params: { q: term },
      signal: current.signal,
    });
    if (request !== current) return;
    dataHits.value = Array.isArray(data?.results) ? data.results : [];
  } catch (error) {
    if (error?.name === 'CanceledError' || error?.code === 'ERR_CANCELED') return;
    dataHits.value = [];
  } finally {
    if (request === current) loading.value = false;
  }
}

function placePanel() {
  const el = anchor.value;
  if (!el) return;
  const rect = el.getBoundingClientRect();
  const width = compact.value ? Math.min(window.innerWidth - 16, 420) : Math.max(rect.width, 380);
  let left = compact.value ? (isRtl.value ? rect.right - width : rect.left) : rect.left;
  left = Math.max(8, Math.min(left, window.innerWidth - width - 8));
  panelStyle.value = {
    top: `${Math.min(rect.bottom + 8, window.innerHeight - 16)}px`,
    left: `${left}px`,
    width: `${width}px`,
  };
}

function openPanel() {
  open.value = true;
  nextTick(placePanel);
}

async function openCompact() {
  open.value = true;
  await nextTick();
  placePanel();
  compactFieldRef.value?.focus?.();
}

function focusField() {
  if (compact.value) openCompact();
  else fieldRef.value?.focus?.();
}

function onKeydown(event) {
  if (event.key === 'ArrowDown') {
    event.preventDefault();
    if (!rows.value.length) return;
    activeIndex.value = (activeIndex.value + 1) % rows.value.length;
  } else if (event.key === 'ArrowUp') {
    event.preventDefault();
    if (!rows.value.length) return;
    activeIndex.value = (activeIndex.value - 1 + rows.value.length) % rows.value.length;
  } else if (event.key === 'Enter') {
    event.preventDefault();
    const item = rows.value[activeIndex.value] || rows.value[0];
    if (item) go(item);
  } else if (event.key === 'Escape') {
    open.value = false;
  }
}

async function go(item) {
  if (!item?.path) return;
  remember(item);
  open.value = false;
  query.value = '';
  dataHits.value = [];
  await router.push(item.path);
}

function onDocumentPointer(event) {
  if (!open.value) return;
  const target = event.target;
  if (anchor.value?.contains(target) || panel.value?.contains(target)) return;
  open.value = false;
}

function onGlobalKey(event) {
  if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault();
    open.value = true;
    nextTick(() => {
      placePanel();
      focusField();
    });
  }
}

function onResize() {
  if (open.value) placePanel();
}

onMounted(() => {
  document.addEventListener('pointerdown', onDocumentPointer);
  document.addEventListener('keydown', onGlobalKey);
  window.addEventListener('resize', onResize);
  window.addEventListener('scroll', onResize, true);
});

onBeforeUnmount(() => {
  clearTimeout(timer);
  request?.abort();
  document.removeEventListener('pointerdown', onDocumentPointer);
  document.removeEventListener('keydown', onGlobalKey);
  window.removeEventListener('resize', onResize);
  window.removeEventListener('scroll', onResize, true);
});
</script>

<style scoped>
.global-search {
  flex: 1 1 340px;
  max-width: 460px;
  min-width: 180px;
  margin-inline: 16px;
}

.global-search.is-compact {
  flex: 0 0 auto;
  max-width: none;
  min-width: 0;
}

.search-field :deep(.v-field) {
  border-radius: 999px !important;
  background: rgba(var(--v-theme-surface), 0.72) !important;
  border: 1px solid rgba(var(--v-theme-outline), 0.16);
  box-shadow: none !important;
  min-height: 40px;
}

.search-field :deep(.v-field__input) {
  min-height: 40px;
  padding-top: 0;
  padding-bottom: 0;
  font-size: 0.9rem;
}

.search-field :deep(.v-field__prepend-inner .v-icon) {
  color: rgb(var(--v-theme-primary));
  opacity: 1;
}

.search-kbd {
  font-size: 0.68rem;
  letter-spacing: 0.02em;
  color: rgba(var(--v-theme-on-surface), 0.55);
  border: 1px solid rgba(var(--v-theme-outline), 0.25);
  border-radius: 6px;
  padding: 1px 6px;
  line-height: 1.4;
}

.search-icon-btn {
  width: 40px !important;
  height: 40px !important;
  min-width: 40px !important;
  border-radius: 50% !important;
  background: rgba(var(--v-theme-surface), 0.7) !important;
  border: 1px solid rgba(var(--v-theme-outline), 0.15) !important;
}

.compact-field {
  padding: 10px 10px 0;
}
</style>

<style>
.global-search-panel {
  position: fixed;
  z-index: 2500;
  max-height: min(70vh, 480px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: 16px;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-theme-outline), 0.14);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.16);
}

.global-search-panel .panel-scroll {
  overflow: auto;
  padding: 6px;
}

.global-search-panel .section-label {
  padding: 8px 10px 4px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.global-search-panel .result-row {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  text-align: inherit;
  border: 0;
  background: transparent;
  border-radius: 12px;
  padding: 8px 10px;
  cursor: pointer;
  color: inherit;
}

.global-search-panel .result-row.is-active,
.global-search-panel .result-row:hover {
  background: rgba(var(--v-theme-primary), 0.1);
}

.global-search-panel .result-icon {
  width: 32px;
  height: 32px;
  flex: 0 0 32px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
}

.global-search-panel .result-copy {
  min-width: 0;
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
}

.global-search-panel .result-title,
.global-search-panel .result-subtitle {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.global-search-panel .result-title {
  font-size: 0.9rem;
  font-weight: 600;
}

.global-search-panel .result-subtitle {
  font-size: 0.75rem;
  color: rgba(var(--v-theme-on-surface), 0.62);
}

.global-search-panel .result-badge {
  flex: 0 0 auto;
  max-width: 110px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.68rem;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
}

.global-search-panel .panel-status,
.global-search-panel .panel-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 10px;
  color: rgba(var(--v-theme-on-surface), 0.7);
  font-size: 0.85rem;
}

.global-search-panel .panel-empty {
  flex-direction: column;
  padding: 22px 10px;
}
</style>
