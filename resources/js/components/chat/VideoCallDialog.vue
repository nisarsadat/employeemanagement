<template>
  <v-dialog v-model="model" max-width="920" persistent>
    <v-card rounded="xl" class="pa-4">
      <div class="d-flex align-center justify-space-between">
        <div class="text-h6 font-weight-bold">Video Call</div>
        <v-btn icon variant="text" @click="endCall">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </div>

      <div class="mt-3 text-caption text-medium-emphasis">
        {{ user?.name || 'User' }} • {{ statusLabel }}
      </div>

      <div class="mt-4 video-grid">
        <div class="video-tile">
          <div class="tile-label">Remote</div>
          <video ref="remoteVideo" class="vid" autoplay playsinline></video>
        </div>

        <div class="video-tile">
          <div class="tile-label">Local</div>
          <video ref="localVideo" class="vid" autoplay muted playsinline></video>
        </div>
      </div>

      <div class="mt-5 d-flex justify-center gap-3">
        <v-btn
          rounded="xl"
          variant="tonal"
          color="primary"
          :disabled="status === 'connected'"
          @click="$emit('accept')"
        >
          <v-icon start>mdi-video-check</v-icon>
          Accept
        </v-btn>

        <v-btn rounded="xl" color="error" @click="endCall">
          <v-icon start>mdi-phone-hangup</v-icon>
          End
        </v-btn>

        <v-btn
          rounded="xl"
          variant="tonal"
          :color="cameraOff ? 'grey' : 'primary'"
          @click="$emit('toggle-camera')"
        >
          <v-icon start>{{ cameraOff ? 'mdi-video-off' : 'mdi-video' }}</v-icon>
          {{ cameraOff ? 'Camera On' : 'Camera Off' }}
        </v-btn>

        <v-btn
          rounded="xl"
          variant="tonal"
          :color="muted ? 'grey' : 'primary'"
          @click="$emit('toggle-mute')"
        >
          <v-icon start>{{ muted ? 'mdi-microphone-off' : 'mdi-microphone' }}</v-icon>
          {{ muted ? 'Unmute' : 'Mute' }}
        </v-btn>
      </div>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  user: { type: Object, default: null },
  status: { type: String, default: 'idle' }, // idle|ringing|connected|ended
  muted: { type: Boolean, default: false },
  cameraOff: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'accept', 'end', 'toggle-mute', 'toggle-camera']);

const localVideo = ref(null);
const remoteVideo = ref(null);

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
});

const statusLabel = computed(() => {
  if (props.status === 'ringing') return 'Ringing…';
  if (props.status === 'connected') return 'Connected';
  if (props.status === 'ended') return 'Call ended';
  return 'Ready';
});

const endCall = () => {
  emit('end');
  emit('update:modelValue', false);
};
</script>

<style scoped>
.video-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.video-tile {
  position: relative;
  background: #0b1220;
  border-radius: 16px;
  overflow: hidden;
  min-height: 280px;
  border: 1px solid rgba(0, 0, 0, 0.12);
}
.tile-label {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 2;
  font-size: 12px;
  font-weight: 800;
  color: white;
  background: rgba(255, 255, 255, 0.15);
  padding: 6px 10px;
  border-radius: 999px;
}
.vid {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
@media (max-width: 900px) {
  .video-grid {
    grid-template-columns: 1fr;
  }
}
</style>
