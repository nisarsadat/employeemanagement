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

      <!-- ✅ remote audio output -->
      <audio ref="remoteAudio" autoplay></audio>
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
});

const emit = defineEmits(['update:modelValue', 'accept', 'end', 'toggle-mute']);

const remoteAudio = ref(null);

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

// ✅ parent will set remote stream
const setRemoteStream = (stream) => {
  if (remoteAudio.value) remoteAudio.value.srcObject = stream || null;
};

defineExpose({ setRemoteStream });

const endCall = () => {
  emit('end');
  emit('update:modelValue', false);
};
</script>