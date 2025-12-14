<!-- src/components/chat/VoiceRecorder.vue -->
<template>
  <div class="voice-wrap">
    <!-- idle -->
    <v-btn
      v-if="!recording"
      icon
      variant="tonal"
      color="primary"
      class="voice-btn"
      :disabled="loading"
      @click="start"
    >
      <v-icon>mdi-microphone</v-icon>
      <v-tooltip activator="parent" location="top">Record voice</v-tooltip>
    </v-btn>

    <!-- recording -->
    <template v-else>
      <v-btn
        icon
        variant="tonal"
        color="error"
        class="voice-btn"
        @click="cancel"
      >
        <v-icon>mdi-close</v-icon>
        <v-tooltip activator="parent" location="top">Cancel</v-tooltip>
      </v-btn>

      <div class="timer">{{ mmss }}</div>
    </template>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';

const emit = defineEmits(['ready', 'recording-changed']);

const recording = ref(false);
const loading = ref(false);
const seconds = ref(0);

let timerId = null;
let mediaRecorder = null;
let chunks = [];
let streamRef = null;
let finalizeResolver = null;

/* ===============================
   UI
================================ */
const mmss = computed(() => {
  const m = String(Math.floor(seconds.value / 60)).padStart(2, '0');
  const s = String(seconds.value % 60).padStart(2, '0');
  return `${m}:${s}`;
});

/* ===============================
   Helpers
================================ */
const cleanupTimer = () => {
  if (timerId) clearInterval(timerId);
  timerId = null;
};

const stopTracks = () => {
  try {
    streamRef?.getTracks?.().forEach(t => t.stop());
  } catch {}
  streamRef = null;
};

const resetState = () => {
  cleanupTimer();
  stopTracks();
  chunks = [];
  mediaRecorder = null;
  recording.value = false;
  seconds.value = 0;
  emit('recording-changed', false);

  if (finalizeResolver) {
    finalizeResolver(null);
    finalizeResolver = null;
  }
};

/* ===============================
   Start recording
================================ */
const start = async () => {
  loading.value = true;
  try {
    resetState();

    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    streamRef = stream;

    const mime =
      MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
        ? 'audio/webm;codecs=opus'
        : MediaRecorder.isTypeSupported('audio/webm')
          ? 'audio/webm'
          : '';

    mediaRecorder = mime
      ? new MediaRecorder(stream, { mimeType: mime })
      : new MediaRecorder(stream);

    chunks = [];
    mediaRecorder.ondataavailable = e => {
      if (e.data && e.data.size > 0) chunks.push(e.data);
    };

    seconds.value = 0;
    cleanupTimer();
    timerId = setInterval(() => seconds.value++, 1000);

    recording.value = true;
    emit('recording-changed', true);

    // IMPORTANT: timeslice ensures data arrives continuously
    mediaRecorder.start(250);
  } catch {
    resetState();
  } finally {
    loading.value = false;
  }
};

/* ===============================
   Finalize (called by parent send)
================================ */
const finalize = () =>
  new Promise(resolve => {
    try {
      if (!recording.value || !mediaRecorder) return resolve(null);

      finalizeResolver = resolve;

      recording.value = false;
      emit('recording-changed', false);
      cleanupTimer();

      mediaRecorder.onstop = () => {
        stopTracks();

        const blob = new Blob(chunks, {
          type: mediaRecorder.mimeType || 'audio/webm',
        });

        if (!blob.size) {
          resetState();
          return resolve(null);
        }

        const ext = blob.type.includes('ogg')
          ? 'ogg'
          : blob.type.includes('mp4')
            ? 'mp4'
            : 'webm';

        const file = new File(
          [blob],
          `voice_${Date.now()}.${ext}`,
          { type: blob.type }
        );

        emit('ready', file);
        resolve(file);
        finalizeResolver = null;
        resetState();
      };

      mediaRecorder.requestData?.();
      mediaRecorder.stop();
    } catch {
      resetState();
      resolve(null);
    }
  });

/* ===============================
   Cancel recording
================================ */
const cancel = () => {
  try {
    if (recording.value && mediaRecorder) {
      mediaRecorder.onstop = null;
      mediaRecorder.stop();
    }
  } catch {}
  resetState();
};

defineExpose({ finalize, cancel, recording });

onBeforeUnmount(() => {
  try {
    if (recording.value) cancel();
  } catch {}
});
</script>

<style scoped>
.voice-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}

.voice-btn {
  border-radius: 50%;
}

.timer {
  font-weight: 800;
  font-size: 12px;
  color: #334155;
  background: #f1f5f9;
  border: 1px solid rgba(0, 0, 0, 0.08);
  padding: 6px 10px;
  border-radius: 999px;
}
</style>
