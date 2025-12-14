<template>
  <v-menu
    v-model="open"
    :close-on-content-click="false"
    location="top start"
    offset="10"
    max-width="360"
    min-width="320"
  >
    <!-- activator is controlled from parent (you already do attachOpen=true) -->
    <template #activator="{ props: menuProps }">
      <!-- optional: if you want to use as activator directly, wrap a button here -->
      <span v-bind="menuProps"></span>
    </template>

    <v-card rounded="xl" class="menu-card">
      <div class="menu-header">
        <div class="d-flex align-center gap-2">
          <v-icon color="primary">mdi-paperclip</v-icon>
          <span class="text-subtitle-2 font-weight-bold">{{ title }}</span>
        </div>

        <v-btn icon size="small" variant="text" @click="close">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </div>

      <v-divider />

      <!-- options -->
      <v-list density="compact" class="py-2">
        <v-list-item class="opt" @click="pick('images')">
          <template #prepend>
            <v-icon color="primary">mdi-image-plus</v-icon>
          </template>
          <v-list-item-title>Images</v-list-item-title>
          <v-list-item-subtitle>JPG, PNG, GIF…</v-list-item-subtitle>
        </v-list-item>

        <v-list-item class="opt" @click="pick('videos')">
          <template #prepend>
            <v-icon color="primary">mdi-video-plus</v-icon>
          </template>
          <v-list-item-title>Videos</v-list-item-title>
          <v-list-item-subtitle>MP4, MOV…</v-list-item-subtitle>
        </v-list-item>

        <v-list-item class="opt" @click="pick('docs')">
          <template #prepend>
            <v-icon color="primary">mdi-file-plus</v-icon>
          </template>
          <v-list-item-title>Docs</v-list-item-title>
          <v-list-item-subtitle>PDF, Word, Excel…</v-list-item-subtitle>
        </v-list-item>

        <v-list-item class="opt" @click="pick('voices')">
          <template #prepend>
            <v-icon color="primary">mdi-microphone-plus</v-icon>
          </template>
          <v-list-item-title>Voice files</v-list-item-title>
          <v-list-item-subtitle>Audio files</v-list-item-subtitle>
        </v-list-item>
      </v-list>

      <!-- selected chips (inside menu) -->
      <div v-if="hasAny" class="selected-wrap">
        <div class="selected-title">Selected</div>

        <div v-if="local.images.length" class="chips">
          <div class="chip-label">
            <v-icon size="16" color="primary">mdi-image</v-icon>
            <span>Images</span>
          </div>
          <v-chip
            v-for="(f, idx) in local.images"
            :key="`img-${idx}`"
            closable
            size="small"
            class="ma-1"
            @click:close="remove('images', idx)"
          >
            {{ f.name }}
          </v-chip>
        </div>

        <div v-if="local.videos.length" class="chips">
          <div class="chip-label">
            <v-icon size="16" color="primary">mdi-video</v-icon>
            <span>Videos</span>
          </div>
          <v-chip
            v-for="(f, idx) in local.videos"
            :key="`vid-${idx}`"
            closable
            size="small"
            class="ma-1"
            @click:close="remove('videos', idx)"
          >
            {{ f.name }}
          </v-chip>
        </div>

        <div v-if="local.docs.length" class="chips">
          <div class="chip-label">
            <v-icon size="16" color="primary">mdi-file-document</v-icon>
            <span>Docs</span>
          </div>
          <v-chip
            v-for="(f, idx) in local.docs"
            :key="`doc-${idx}`"
            closable
            size="small"
            class="ma-1"
            @click:close="remove('docs', idx)"
          >
            {{ f.name }}
          </v-chip>
        </div>

        <div v-if="local.voices.length" class="chips">
          <div class="chip-label">
            <v-icon size="16" color="primary">mdi-microphone</v-icon>
            <span>Voices</span>
          </div>
          <v-chip
            v-for="(f, idx) in local.voices"
            :key="`voc-${idx}`"
            closable
            size="small"
            class="ma-1"
            @click:close="remove('voices', idx)"
          >
            {{ f.name }}
          </v-chip>
        </div>
      </div>

      <v-divider />

      <div class="menu-actions">
        <v-btn variant="text" @click="clearAll" :disabled="!hasAny">Clear</v-btn>
        <v-btn color="primary" variant="flat" rounded="lg" @click="apply" :disabled="!hasAny">
          Apply
        </v-btn>
      </div>
    </v-card>

    <!-- hidden native inputs -->
    <input
      ref="imagesInput"
      type="file"
      multiple
      accept="image/*"
      class="hidden-input"
      @change="onPicked('images', $event)"
    />
    <input
      ref="videosInput"
      type="file"
      multiple
      accept="video/*"
      class="hidden-input"
      @change="onPicked('videos', $event)"
    />
    <input
      ref="docsInput"
      type="file"
      multiple
      accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt"
      class="hidden-input"
      @change="onPicked('docs', $event)"
    />
    <input
      ref="voicesInput"
      type="file"
      multiple
      accept="audio/*"
      class="hidden-input"
      @change="onPicked('voices', $event)"
    />
  </v-menu>
</template>

<script setup>
  import { computed, reactive, ref, watch } from 'vue';

  const props = defineProps({
    modelValue: { type: Boolean, default: false },
    value: {
      type: Object,
      default: () => ({ images: [], videos: [], docs: [], voices: [] }),
    },
    title: { type: String, default: 'Attach files' },
  });

  const emit = defineEmits(['update:modelValue', 'update:value']);

  const open = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
  });

  const local = reactive({
    images: [],
    videos: [],
    docs: [],
    voices: [],
  });

  const imagesInput = ref(null);
  const videosInput = ref(null);
  const docsInput = ref(null);
  const voicesInput = ref(null);

  watch(
    () => props.value,
    (v) => {
      local.images = Array.isArray(v?.images) ? [...v.images] : [];
      local.videos = Array.isArray(v?.videos) ? [...v.videos] : [];
      local.docs = Array.isArray(v?.docs) ? [...v.docs] : [];
      local.voices = Array.isArray(v?.voices) ? [...v.voices] : [];
    },
    { immediate: true, deep: true }
  );

  const hasAny = computed(() => {
    return (
      (local.images?.length || 0) +
        (local.videos?.length || 0) +
        (local.docs?.length || 0) +
        (local.voices?.length || 0) >
      0
    );
  });

  const pick = (key) => {
    const map = {
      images: imagesInput,
      videos: videosInput,
      docs: docsInput,
      voices: voicesInput,
    };
    const el = map[key]?.value;
    if (!el) return;
    el.click();
  };

  const toFilesArray = (fileList) => {
    if (!fileList) return [];
    return Array.from(fileList);
  };

  const onPicked = (key, e) => {
    const files = toFilesArray(e?.target?.files);

    // append (not replace)
    local[key] = [...(local[key] || []), ...files];

    // reset input so picking same file again works
    try {
      e.target.value = '';
    } catch {}
  };

  const remove = (key, idx) => {
    local[key].splice(idx, 1);
  };

  const clearAll = () => {
    local.images = [];
    local.videos = [];
    local.docs = [];
    local.voices = [];
  };

  const apply = () => {
    emit('update:value', {
      images: local.images,
      videos: local.videos,
      docs: local.docs,
      voices: local.voices,
    });
    open.value = false;
  };

  const close = () => (open.value = false);
</script>

<style scoped>
  .menu-card {
    border: 1px solid rgba(0, 0, 0, 0.06);
  }
  .menu-header {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .opt {
    border-radius: 12px;
    margin: 4px 8px;
  }
  .opt:hover {
    background: rgba(102, 126, 234, 0.08);
  }

  .selected-wrap {
    padding: 10px 12px 2px;
  }
  .selected-title {
    font-weight: 800;
    font-size: 12px;
    margin-bottom: 8px;
    color: #334155;
  }
  .chips {
    margin-bottom: 10px;
  }
  .chip-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 12px;
    color: #475569;
    margin: 0 0 6px 2px;
  }

  .menu-actions {
    padding: 10px 12px;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
  }

  .hidden-input {
    display: none;
  }
</style>
