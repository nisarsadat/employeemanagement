<template>
  <v-dialog v-model="model" max-width="520" persistent>
    <v-card rounded="xl" class="pa-4">
      <div class="d-flex align-center justify-space-between">
        <div class="text-h6 font-weight-bold">Voice Call</div>
        <v-btn icon variant="text" @click="endCall">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </div>

      <div class="mt-4 d-flex align-center gap-3">
        <v-avatar size="56">
          <v-img v-if="user?.image_url" :src="user.image_url" />
          <v-icon v-else size="56">mdi-account-circle</v-icon>
        </v-avatar>

        <div class="min-w-0">
          <div class="text-subtitle-1 font-weight-bold text-truncate">
            {{ user?.name || 'User' }}
          </div>
          <div class="text-caption text-medium-emphasis">
            {{ statusLabel }}
          </div>
        </div>
      </div>

      <div class="mt-6 d-flex justify-center gap-3 flex-wrap">
        <v-btn
          rounded="xl"
          variant="tonal"
          color="primary"
          :disabled="status === 'connected'"
          @click="$emit('accept')"
        >
          <v-icon start>mdi-phone-check</v-icon>
          Accept
        </v-btn>

        <v-btn rounded="xl" color="error" @click="endCall">
          <v-icon start>mdi-phone-hangup</v-icon>
          End
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

      <!-- hidden audio to satisfy some browsers -->
      <audio ref="ringEl" preload="auto" />
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  user: { type: Object, default: null },
  status: { type: String, default: 'idle' }, // idle|ringing|connected|ended
  muted: { type: Boolean, default: false },
  // optional: play ringing inside dialog too
  ringSrc: { type: String, default: '/sounds/ringtone.mp3' },
  playRing: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'accept', 'end', 'toggle-mute']);

const ringEl = ref(null);

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

const stopLocalRing = () => {
  try {
    if (!ringEl.value) return;
    ringEl.value.pause();
    ringEl.value.currentTime = 0;
  } catch {}
};

const startLocalRing = async () => {
  try {
    if (!ringEl.value) return;
    ringEl.value.src = props.ringSrc;
    ringEl.value.loop = true;
    await ringEl.value.play();
  } catch {
    // autoplay may be blocked until user gesture
  }
};

watch(
  () => props.playRing,
  async (v) => {
    if (!props.modelValue) return;
    if (v) await startLocalRing();
    else stopLocalRing();
  }
);

watch(
  () => props.modelValue,
  (open) => {
    if (!open) stopLocalRing();
    else if (props.playRing) startLocalRing();
  }
);

const endCall = () => {
  stopLocalRing();
  emit('end');
  emit('update:modelValue', false);
};
</script>
