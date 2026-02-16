<template>
  <div class="pic-wrap">
    <!-- Has picture -->
    <v-avatar
      v-if="picture"
      :size="size"
      class="cursor-pointer"
      @click.stop="open()"
    >
      <v-img :src="normalized" :alt="alt" cover />
    </v-avatar>

    <!-- No picture -->
    <v-avatar v-else :size="size">
      <v-icon :size="fallbackIconSize">{{ fallbackIcon }}</v-icon>
    </v-avatar>

    <!-- Preview -->
    <v-dialog v-model="previewOpen" :max-width="previewMaxWidth">
      <v-card>
        <v-toolbar
          density="compact"
          :color="toolbarColor"
          :title="title"
          class="text-white"
        />
        <v-card-text class="d-flex justify-center">
          <v-img
            v-if="normalized"
            :src="normalized"
            :alt="title"
            :max-width="previewMaxImageWidth"
            :max-height="previewMaxImageHeight"
            cover
          />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn :color="toolbarColor" variant="flat" @click="previewOpen = false">
            {{ closeText }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  picture: { type: [String, null], default: null },
  alt: { type: String, default: '' },

  // UI
  size: { type: [Number, String], default: 32 },
  fallbackIcon: { type: String, default: 'mdi-account' },
  fallbackIconSize: { type: [Number, String], default: 20 },

  // Preview
  title: { type: String, default: 'پیش نمایش تصویر' },
  closeText: { type: String, default: 'بستن' },
  toolbarColor: { type: String, default: 'primary' },
  previewMaxWidth: { type: [Number, String], default: 520 },
  previewMaxImageWidth: { type: [Number, String], default: 480 },
  previewMaxImageHeight: { type: [Number, String], default: 420 },
});

const previewOpen = ref(false);

const normalized = computed(() => normalizePicture(props.picture));

function open() {
  if (!normalized.value) return;
  previewOpen.value = true;
}

function normalizePicture(src) {
  if (!src) return null;
  let clean = String(src).trim();

  if (clean.startsWith('blob:') || clean.startsWith('data:')) return clean;

  // fix "\/" -> "/"
  clean = clean.replace(/\\\//g, '/');

  if (clean.startsWith('http://') || clean.startsWith('https://')) return clean;
  if (clean.startsWith('/')) return clean;

  return '/' + clean.replace(/^\/+/, '');
}
</script>

<style scoped>
.pic-wrap {
  display: inline-flex;
  align-items: center;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
