<template>
  <v-expand-transition>
    <div v-if="expanded" class="inline-panel" :style="{ direction: dir }">
      <v-card class="simple-panel">
        <!-- Header -->
        <div class="panel-header">
          <div class="header-content">
            <div class="title-section">
              <v-icon icon="mdi-information" class="mr-2 ml-12" size="20" />
              <strong>{{ titleText }}</strong>
            </div>

            <!-- ✅ Close button -->
            <v-btn
              v-if="closable"
              icon
              size="small"
              variant="text"
              color="white"
              :title="'بستن'"
              @click="close"
            >
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </div>

        <v-divider />

        <!-- Body -->
        <div class="panel-body">
          <template v-if="loading">
            <div class="loading-state">
              <v-progress-circular indeterminate size="24" width="2" color="primary" />
              <span>در حال بارگذاری...</span>
            </div>
          </template>

          <template v-else>
            <!-- First Row: Image + Fields -->
            <div class="bodyjadwal">
              <div class="picpart" :style="columnsStyle">
                <!-- Image -->
                <div v-if="showBodyImage && hasImage" class="image-container">
                  <v-img :src="imageSrc" height="100%" width="100%" cover class="profile-image">
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

        <!-- Footer Close (optional second place to close) -->
        <div v-if="closable" class="px-3 py-2 flex justify-end">
          <v-btn color="primary" variant="flat" @click="close">بستن</v-btn>
        </div>
      </v-card>
    </div>
  </v-expand-transition>
</template>

<script setup>
  import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

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

    /* Show image in body */
    showBodyImage: { type: Boolean, default: true },

    /* ✅ Control close button */
    closable: { type: Boolean, default: true },

    /* ✅ Optional: base URL for relative images (fallbacks to env) */
    fileBaseUrl: { type: String, default: '' },
  });

  const emit = defineEmits(['update:modelValue', 'fetch', 'close']);

  const expanded = ref(props.modelValue);
  watch(
    () => props.modelValue,
    (v) => (expanded.value = v)
  );
  watch(expanded, (v) => emit('update:modelValue', v));

  function close() {
    expanded.value = false;
    emit('close');
  }

  function onKeydown(e) {
    if (!expanded.value) return;
    if (e.key === 'Escape' || e.key === 'Esc') close();
  }

  onMounted(() => window.addEventListener('keydown', onKeydown));
  onUnmounted(() => window.removeEventListener('keydown', onKeydown));

  /* If you use this programmatically somewhere */
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

  /* ✅ Robust image resolver (blob:, data:, http, or relative) */
  function resolveImageUrl(src) {
    if (!src) return null;
    if (/^(blob:|data:|https?:\/\/)/i.test(src)) return src;
    const base =
      props.fileBaseUrl || import.meta.env.VITE_FILE_BASE_URL || import.meta.env.VITE_API_URL || '';
    if (!base) return src;
    return `${String(base).replace(/\/+$/, '')}/${String(src).replace(/^\/+/, '')}`;
  }
  const imageSrc = computed(() => resolveImageUrl(getByPath(props.item, props.imageKey)));
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
    flex: 1 1 auto;
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
