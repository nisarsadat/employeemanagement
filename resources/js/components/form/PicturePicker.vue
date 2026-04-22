<template>
  <div class="d-flex flex-column align-center gap-2">
    <div v-if="label" class="text-subtitle-2 font-weight-medium mb-2">
      {{ label }}
    </div>

    <div class="pic-wrap-simple">
      <input
        ref="inputRef"
        class="hidden-file"
        type="file"
        accept="image/*"
        @change="onPick"
      />

      <v-avatar
        :size="size"
        class="mb-2 cursor-pointer primary-avatar"
        :key="displaySrc || 'no-picture'"
        @click="trigger"
      >
        <v-img v-if="displaySrc" :src="normalizePicture(displaySrc)" :alt="alt" cover />
        <v-icon v-else :size="iconSize">{{ emptyIcon }}</v-icon>
      </v-avatar>

      <div class="d-flex flex-column align-center gap-1">
        <v-btn
          size="small"
          color="primary"
          variant="tonal"
          class="text-none rounded-pill soft-btn my-2"
          @click.stop="trigger"
        >
          <v-icon start size="22">mdi-pencil</v-icon>
          {{ changeText }}
        </v-btn>

        <v-btn
          v-if="displaySrc"
          size="small"
          color="secondary"
          variant="tonal"
          class="text-none rounded-pill soft-btn"
          @click.stop="openPreview()"
        >
          <v-icon start size="22">mdi-eye</v-icon>
          {{ viewText }}
        </v-btn>

        <v-btn
          v-if="displaySrc"
          size="small"
          color="error"
          variant="tonal"
          class="text-none rounded-pill soft-btn"
          @click.stop="removePicture"
        >
          <v-icon start size="22">mdi-delete</v-icon>
          {{ removeText }}
        </v-btn>
      </div>
    </div>

    <!-- Preview -->
    <v-dialog v-model="previewOpen" max-width="520">
      <v-card>
        <v-toolbar
          density="compact"
          color="primary"
          :title="previewTitle"
          class="text-white"
        />
        <v-card-text class="d-flex justify-center">
          <v-img
            :src="normalizePicture(previewSrc)"
            :alt="previewTitle"
            max-width="480"
            max-height="420"
            cover
          />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn color="primary" variant="flat" @click="previewOpen = false">
            {{ closeText }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';

/**
 * v-model:file="pictureFile"
 * v-model:url="pictureUrl"
 *
 * - file: the picked File (or null)
 * - url: existing picture url (from API) (or null)
 */
const props = defineProps({
  file: { type: [File, null], default: null },
  url: { type: [String, null], default: null },

  label: { type: String, default: '' },
  alt: { type: String, default: 'picture' },

  size: { type: [Number, String], default: 88 },
  emptyIcon: { type: String, default: 'mdi-account' },
  iconSize: { type: [Number, String], default: 40 },

  changeText: { type: String, default: 'تغییر عکس' },
  viewText: { type: String, default: 'نمایش' },
  removeText: { type: String, default: 'حذف عکس' },
  closeText: { type: String, default: 'بستن' },
  previewTitle: { type: String, default: 'پیش نمایش' },
});

const emit = defineEmits(['update:file', 'update:url', 'change']);

const inputRef = ref(null);

// local preview blob url
const previewBlobUrl = ref(null);

const displaySrc = computed(() => {
  return previewBlobUrl.value || props.url || null;
});

const previewOpen = ref(false);
const previewSrc = ref(null);

function trigger() {
  inputRef.value?.click();
}

function onPick(e) {
  const f = e.target.files?.[0] ?? null;

  // cleanup old blob
  revokeBlob();

  if (f) {
    previewBlobUrl.value = URL.createObjectURL(f);
    emit('update:file', f);
    emit('change', { file: f, changed: true, removed: false });
  } else {
    emit('update:file', null);
    emit('change', { file: null, changed: false, removed: false });
  }

  if (e.target) e.target.value = '';
}

function removePicture() {
  revokeBlob();
  previewSrc.value = null;
  previewOpen.value = false;

  emit('update:file', null);
  emit('update:url', null);
  emit('change', { file: null, changed: true, removed: true });

  if (inputRef.value) {
    inputRef.value.value = '';
  }
}

function openPreview() {
  const src = displaySrc.value;
  if (!src) return;
  previewSrc.value = src;
  previewOpen.value = true;
}

function normalizePicture(src) {
  if (!src) return null;
  let clean = String(src).trim();

  if (clean.startsWith('blob:') || clean.startsWith('data:')) return clean;

  clean = clean.replace(/\\\//g, '/');

  if (clean.startsWith('http://') || clean.startsWith('https://')) return clean;
  if (clean.startsWith('/')) return clean;

  return '/' + clean.replace(/^\/+/, '');
}

function revokeBlob() {
  if (previewBlobUrl.value && String(previewBlobUrl.value).startsWith('blob:')) {
    URL.revokeObjectURL(previewBlobUrl.value);
  }
  previewBlobUrl.value = null;
}

// if parent changes url (load record), clear blob
watch(
  () => props.url,
  () => {
    revokeBlob();
  }
);

onBeforeUnmount(() => {
  revokeBlob();
});
</script>

<style scoped>
.hidden-file {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

.pic-wrap-simple {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.cursor-pointer {
  cursor: pointer;
}

.primary-avatar {
  border: 2px solid rgba(33, 150, 243, 0.25);
}

/* local gap utility */
.gap-2 {
  gap: 8px;
}

.soft-btn {
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}
</style>