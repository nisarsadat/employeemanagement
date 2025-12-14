<!-- src/components/chat/ChatSearchInline.vue -->
<template>
  <!-- Inline search bar (place it inside chat-right-header, next to username) -->
  <div class="search-inline" :dir="dir">
    <v-text-field
      ref="inputRef"
      v-model="q"
      density="compact"
      variant="outlined"
      hide-details
      placeholder="Search…"
      prepend-inner-icon="mdi-magnify"
      class="search-input"
      clearable
      @click:clear="clear"
      @keydown.enter.prevent="doSearch"
      @input="onInput"
    />

    <!-- dropdown results -->
    <v-card
      v-if="showDropdown"
      class="results-card thin-scroll"
      rounded="lg"
      elevation="6"
    >
      <div v-if="loading" class="state">Searching…</div>
      <div v-else-if="searched && !results.length" class="state">No results</div>

      <v-list v-else density="compact" class="py-0">
        <v-list-item
          v-for="m in results"
          :key="m.id"
          rounded="lg"
          class="result-item"
          @click="select(m)"
        >
          <template #prepend>
            <v-icon color="primary" size="18">mdi-message-text</v-icon>
          </template>

          <div class="content">
            <div class="line">
              <span class="body">{{ highlight(m.body || '') }}</span>
            </div>
            <div class="meta">
              <span class="sender">{{ m.sender?.name || 'User' }}</span>
              <span class="dot">•</span>
              <span class="time">{{ formatTime(m.created_at) }}</span>
            </div>
          </div>
        </v-list-item>
      </v-list>
    </v-card>
  </div>
</template>

<script setup>
import { computed, ref, defineExpose } from 'vue';
import dayjs from 'dayjs';

const props = defineProps({
  dir: { type: String, default: 'ltr' },
  onSearch: { type: Function, required: true }, // async (q) => results[]
  minChars: { type: Number, default: 2 },
  debounceMs: { type: Number, default: 250 },
});
const emit = defineEmits(['jump']);

const inputRef = ref(null);
const q = ref('');
const loading = ref(false);
const results = ref([]);
const searched = ref(false);

let tId = null;

const focus = () => inputRef.value?.focus?.();
defineExpose({ focus });

const showDropdown = computed(() => {
  const hasQuery = q.value.trim().length >= props.minChars;
  return hasQuery && (loading.value || searched.value);
});

const doSearch = async () => {
  const term = q.value.trim();
  if (term.length < props.minChars) {
    results.value = [];
    searched.value = false;
    return;
  }

  loading.value = true;
  try {
    const res = await props.onSearch(term);
    results.value = Array.isArray(res) ? res : [];
    searched.value = true;
  } finally {
    loading.value = false;
  }
};

const onInput = () => {
  clearTimeout(tId);
  const term = q.value.trim();

  if (term.length < props.minChars) {
    results.value = [];
    searched.value = false;
    return;
  }

  tId = setTimeout(() => {
    doSearch();
  }, props.debounceMs);
};

const select = (m) => {
  emit('jump', m.id);
  // keep query (like WhatsApp) OR clear after jump — your choice:
  // q.value = '';
};

const clear = () => {
  q.value = '';
  results.value = [];
  searched.value = false;
};

const formatTime = (iso) => (iso ? dayjs(iso).format('YYYY-MM-DD HH:mm') : '');

const highlight = (text) => {
  const term = q.value.trim();
  if (!term) return text;

  const re = new RegExp(term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'ig');
  return String(text).replace(re, (m) => `«${m}»`);
};
</script>

<style scoped>
.search-inline {
  position: relative;
  width: 320px;
  max-width: 45vw;
}
.search-input :deep(.v-field) {
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.results-card {
  position: absolute;
  top: calc(100% + 8px);
  inset-inline-start: 0;
  width: 100%;
  max-height: 320px;
  overflow: auto;
  z-index: 50;
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.state {
  padding: 14px;
  text-align: center;
  color: #64748b;
  font-weight: 700;
}
.result-item {
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}
.content {
  min-width: 0;
}
.line {
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.meta {
  margin-top: 2px;
  font-size: 11px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
}
.dot {
  opacity: 0.6;
}
.thin-scroll::-webkit-scrollbar {
  width: 6px;
}
.thin-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
</style>
