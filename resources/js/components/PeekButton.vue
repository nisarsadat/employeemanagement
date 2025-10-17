<template>
  <v-expand-transition>
    <div v-if="expanded" class="inline-panel" :style="{ direction: dir }">
      <v-card class="simple-panel">
        <!-- ✅ Header (title + icon only) -->
        <div class="panel-header">
          <div class="header-content">
            <div class="title-section">
              <v-icon icon="mdi-information" class="mr-2 ml-12" size="20" />
              <strong>{{ titleText }}</strong>
            </div>
          </div>
        </div>

        <v-divider />

        <!-- ✅ Body -->
        <div class="panel-body">
          <template v-if="loading">
            <div class="loading-state">
              <v-progress-circular indeterminate size="24" width="2" color="primary" />
              <span>در حال بارگذاری...</span>
            </div>
          </template>

          <template v-else>
            <!-- ✅ First Row: Image (in body) + Fields -->
            <div class="bodyjadwal">
              <div class="picpart" :style="columnsStyle">
                <!-- Image (only in body) -->
                <div v-if="showBodyImage && hasImage" class="image-container">
                  <v-img
                    :src="getByPath(item, imageKey)"
                    height="100%"
                    width="100%"
                    cover
                    class="profile-image"
                  >
                    <template #placeholder>
                      <div class="image-placeholder">
                        <v-icon icon="mdi-account" size="32" />
                      </div>
                    </template>
                  </v-img>
                </div>
              </div>

              <!-- Fields -->
              <div class="fields-grid" :style="columnsStyle">
                <div v-for="(f, idx) in fields" :key="idx" class="field-item">
                  <div class="field-label">{{ f.title }}</div>
                  <div class="field-value">
                    <slot name="cell" :field="f" :value="getByPath(item, f.key)" :item="item">
                      {{ getByPath(item, f.key) ?? '—' }}
                    </slot>
                  </div>
                </div>
              </div>
            </div>

            <!-- Extra Slot -->
            <div v-if="$slots.extra" class="extra-content">
              <slot name="extra" />
            </div>
          </template>
        </div>

        <v-divider />
      </v-card>
    </div>
  </v-expand-transition>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  id: { type: [String, Number], required: true },
  item: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  modelValue: { type: Boolean, default: false },

  title: { type: String, default: '' },
  titleKey: { type: String, default: '' },

  imageKey: { type: String, default: '' },
  fields: { type: Array, default: () => [] },

  cols: { type: Number, default: 3 },
  dir: { type: String, default: 'rtl' },

  /* ✅ Control visibility */
  showBodyImage: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'fetch']);

const expanded = ref(props.modelValue);
watch(() => props.modelValue, (v) => (expanded.value = v));
watch(expanded, (v) => emit('update:modelValue', v));

function toggle() {
  const opening = !expanded.value;
  expanded.value = opening;
  if (opening) emit('fetch', props.id);
}

function getByPath(obj, path) {
  if (!obj || !path) return undefined;
  if (path.includes('.')) {
    return path.split('.').reduce((o, k) => (o == null ? undefined : o[k]), obj);
  }
  return obj[path];
}

const hasImage = computed(() => {
  if (!props.imageKey) return false;
  return !!getByPath(props.item, props.imageKey);
});

const titleText = computed(() => {
  if (props.title) return props.title;
  if (props.titleKey) {
    const t = getByPath(props.item, props.titleKey);
    if (t != null && t !== '') return String(t);
  }
  return 'جزییات';
});

const columnsStyle = computed(() => {
  const n = Math.max(1, Math.min(6, Number(props.cols) || 3));
  return { gridTemplateColumns: `repeat(${n}, minmax(0, 1fr))` };
});
</script>

<style scoped>
.simple-panel {
  border: 1px solid #5c4444;
  border-radius: 8px;
  overflow: hidden;
}

/* Header */
.panel-header {
  padding: 12px 16px;
  background: #98a4a8;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.title-section {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #333;
}

/* Body */
.panel-body {
  padding: 16px;
}

.loading-state {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 20px;
  justify-content: center;
  color: #e7e4e4;
  font-size: 14px;
}

/* Layout */
.bodyjadwal {
  display: flex;
  flex-direction: space-between;
  gap: 12px !important;
}

.image-container {
  flex-shrink: 0;
  width: 40vh !important;
  height: 65vh !important;
}

.profile-image {
  border-radius: 8px;
  border: 2px solid #1b1616;
}

.image-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  background: #0d0c0c;
  border-radius: 8px;
}

/* Fields Grid */
.fields-grid {
  display: grid;
  gap: 12px;
}

.field-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 10px;
  background: #f9f9f98f;
  border-radius: 6px;
  border: 1px solid #e0e0e0;
}

.field-label {
  font-size: 12px;
  color: #a71a1a;
  font-weight: 500;
}

.field-value {
  font-size: 14px;
  color: #160f0f;
  word-break: break-word;
  min-height: 20px;
}

/* Extra */
.extra-content {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px dashed #e0e0e0;
}

/* Responsive */
@media (max-width: 768px) {
  .fields-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>
