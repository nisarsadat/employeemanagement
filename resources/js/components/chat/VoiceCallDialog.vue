<template>
  <v-dialog v-model="model" max-width="560" persistent>
    <v-card rounded="xl" class="call-shell" :dir="'rtl'">
      <!-- Header -->
      <div class="call-head">
        <div class="d-flex align-center ga-3">
          <div class="call-avatar withecol">
            <v-icon size="22">mdi-phone</v-icon>
          </div>

          <div class="min-w-0">
            <div class="call-title withecol">صوتي تماس</div>
            <div class="call-sub">
              <span class="call-status">{{ statusLabel }}</span>
            </div>
          </div>
        </div>

        <v-btn icon variant="text" class="close-btn withecol" @click="endCall">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </div>

      <!-- User Row -->
      <div class="user-row mt-5">
        <v-avatar size="64" class="user-avatar">
          <v-img v-if="user?.image_url" :src="user.image_url" />
          <v-icon v-else size="64">mdi-account-circle</v-icon>
        </v-avatar>

        <div class="min-w-0">
          <div class="user-name">
            {{ user?.name || 'کاروونکی' }}
          </div>
          <div class="user-meta">
            <v-icon size="16">mdi-access-point</v-icon>
            <span>{{ statusLabel }}</span>
          </div>
        </div>
      </div>

      <!-- Pulse / Indicator -->
      <div class="pulse-wrap mt-5">
        <div class="pulse">
          <span class="dot"></span>
          <span class="dot"></span>
          <span class="dot"></span>
        </div>
        <div class="pulse-text">
          {{ props.status === 'connected' ? 'اوس خبرې کولی شې' : 'د اړیکې په تمه…' }}
        </div>
      </div>

      <!-- Controls -->
      <div class="call-controls mt-6">
        <div class="controls-bar">
          <!-- ✅ only show accept when ringing -->
          <v-btn
            v-if="status === 'ringing'"
            rounded="xl"
            class="btn-accept"
            variant="flat"
            :disabled="status === 'connected'"
            @click="$emit('accept')"
          >
            <v-icon start>mdi-phone-check</v-icon>
            ومنه
          </v-btn>

          <v-btn rounded="xl" class="btn-end" variant="flat" @click="endCall">
            <v-icon start>mdi-phone-hangup</v-icon>
            پای
          </v-btn>

          <v-btn
            rounded="xl"
            class="btn-toggle"
            variant="tonal"
            :color="muted ? 'grey' : 'primary'"
            @click="$emit('toggle-mute')"
          >
            <v-icon start>{{ muted ? 'mdi-microphone-off' : 'mdi-microphone' }}</v-icon>
            {{ muted ? 'غږ چالان' : 'غږ بند' }}
          </v-btn>
        </div>

        <div class="mini-note">
          <v-icon size="16">mdi-shield-check</v-icon>
          <span>ستاسې مایک یوازې د کال پر وخت کار کوي.</span>
        </div>
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
  if (props.status === 'ringing') return 'زنګ وهي…';
  if (props.status === 'connected') return 'وصل شو';
  if (props.status === 'ended') return 'کال ختم شو';
  return 'چمتو';
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

<style scoped>
.call-shell {
  padding: 16px;
  border: 1px solid rgb(12, 223, 40);
  background: linear-gradient(180deg, rgb(10, 38, 102), rgba(15, 23, 42, 0.01));
}

.call-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.call-avatar {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: rgba(59, 130, 246, 0.12);
  border: 1px solid rgba(59, 130, 246, 0.18);
}

.call-title {
  font-size: 18px;
  font-weight: 900;
  line-height: 1.2;
}

.call-sub {
  margin-top: 2px;
  font-size: 12px;
  opacity: 0.85;
}

.call-status {
  font-weight: 800;
}

.close-btn {
  border-radius: 14px;
}

.user-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px;
  border-radius: 18px;
  background: rgba(27, 173, 117, 0.249);
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.user-avatar {
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.user-name {
  font-size: 16px;
  font-weight: 900;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 360px;
}

.user-meta {
  margin-top: 4px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  opacity: 0.85;
  font-weight: 800;
}

/* Pulse */
.pulse-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 14px 10px;
  border-radius: 18px;
  background: rgba(50, 76, 138, 0.149);
  border: 1px solid rgba(15, 23, 42, 0.08);
}

.pulse {
  display: inline-flex;
  gap: 8px;
  align-items: center;
  justify-content: center;
}

.pulse .dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: rgba(59, 130, 246, 0.85);
  animation: bounce 1.1s infinite ease-in-out;
}

.pulse .dot:nth-child(2) {
  animation-delay: 0.15s;
}
.pulse .dot:nth-child(3) {
  animation-delay: 0.3s;
}
.withecol
{
  color: aliceblue;
}
@keyframes bounce {
  0%,
  80%,
  100% {
    transform: translateY(0);
    opacity: 0.45;
  }
  40% {
    transform: translateY(-6px);
    opacity: 1;
  }
}

.pulse-text {
  font-size: 12px;
  font-weight: 900;
  opacity: 0.85;
  text-align: center;
}

.call-controls {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.controls-bar {
  display: flex;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
  padding: 10px;
  border-radius: 18px;
  background: rgba(15, 23, 42, 0.03);
  border: 1px solid rgba(15, 23, 42, 0.08);
}

.btn-accept {
  background: linear-gradient(180deg, rgba(34, 197, 94, 0.95), rgba(34, 197, 94, 0.8));
  color: white;
  font-weight: 900;
}

.btn-end {
  background: linear-gradient(180deg, rgba(239, 68, 68, 0.95), rgba(239, 68, 68, 0.8));
  color: white;
  font-weight: 900;
}

.btn-toggle {
  font-weight: 900;
}

.mini-note {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 12px;
  opacity: 0.8;
}
</style>