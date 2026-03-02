<template>
  <v-dialog v-model="model" max-width="960" persistent>
    <v-card rounded="xl" class="call-shell" :dir="'rtl'">
      <!-- Header -->
      <div class="call-head">
        <div class="d-flex align-center ga-3">
          <div class="call-avatar">
            <v-icon size="22">mdi-video</v-icon>
          </div>

          <div class="min-w-0">
            <div class="call-title">ویډیو تماس</div>
            <div class="call-sub">
              <span class="call-name">{{ user?.name || 'کاروونکی' }}</span>
              <span class="dot">•</span>
              <span class="call-status">{{ statusLabel }}</span>
            </div>
          </div>
        </div>

        <v-btn icon variant="text" class="close-btn" @click="endCall">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </div>

      <!-- Videos -->
      <div class="video-wrap mt-4">
        <div class="video-grid">
          <!-- Remote -->
          <div class="video-tile remote">
            <div class="tile-top">
              <div class="tile-chip">
                <v-icon size="16">mdi-account-voice</v-icon>
                <span>مخالف</span>
              </div>

              <div class="tile-chip subtle" v-if="status !== 'connected'">
                <v-icon size="16">mdi-access-point</v-icon>
                <span>{{ statusLabel }}</span>
              </div>
            </div>

            <video ref="remoteVideo" class="vid" autoplay playsinline></video>

            <div class="tile-hint" v-if="status !== 'connected'">
              <div class="hint-line"></div>
              <div class="hint-text">د اړیکې په تمه…</div>
              <div class="hint-line"></div>
            </div>
          </div>

          <!-- Local -->
          <div class="video-tile local">
            <div class="tile-top">
              <div class="tile-chip">
                <v-icon size="16">mdi-account</v-icon>
                <span>ته</span>
              </div>

              <div class="tile-chip subtle" v-if="muted">
                <v-icon size="16">mdi-microphone-off</v-icon>
                <span>غږ بند</span>
              </div>

              <div class="tile-chip subtle" v-if="cameraOff">
                <v-icon size="16">mdi-video-off</v-icon>
                <span>کمره بنده</span>
              </div>
            </div>

            <video ref="localVideo" class="vid" autoplay muted playsinline></video>
          </div>
        </div>
      </div>

      <!-- Controls -->
      <div class="call-controls mt-5">
        <div class="controls-bar">
          <!-- ✅ should not show accept while "going call" (i.e., not ringing) -->
          <v-btn
            v-if="status === 'ringing'"
            rounded="xl"
            class="btn-accept"
            variant="flat"
            :disabled="status === 'connected'"
            @click="$emit('accept')"
          >
            <v-icon start>mdi-video-check</v-icon>
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
            :color="cameraOff ? 'grey' : 'primary'"
            @click="$emit('toggle-camera')"
          >
            <v-icon start>{{ cameraOff ? 'mdi-video-off' : 'mdi-video' }}</v-icon>
            {{ cameraOff ? 'کمره چالان' : 'کمره بند' }}
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
          <span>ستاسې کمره/مایک یوازې د کال پر وخت کار کوي.</span>
        </div>
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
  if (props.status === 'ringing') return 'زنګ وهي…';
  if (props.status === 'connected') return 'وصل شو';
  if (props.status === 'ended') return 'کال ختم شو';
  return 'چمتو';
});

// ✅ parent will set streams
const setLocalStream = (stream) => {
  if (localVideo.value) localVideo.value.srcObject = stream || null;
};

const setRemoteStream = (stream) => {
  if (remoteVideo.value) remoteVideo.value.srcObject = stream || null;
};

defineExpose({ setLocalStream, setRemoteStream });

const endCall = () => {
  emit('end');
  emit('update:modelValue', false);
};
</script>

<style scoped>
.call-shell {
  padding: 16px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  background: linear-gradient(180deg, rgb(15, 23, 42), rgba(15, 23, 42, 0.01));
  color: white;
}

.call-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 4px 2px 0 2px;
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
  letter-spacing: 0.2px;
  line-height: 1.2;
}

.call-sub {
  margin-top: 2px;
  font-size: 12px;
  opacity: 0.85;
  display: flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
}

.call-name {
  font-weight: 800;
  max-width: 280px;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dot {
  opacity: 0.55;
}

.call-status {
  font-weight: 700;
}

.close-btn {
  border-radius: 14px;
}

.video-wrap {
  border-radius: 18px;
}

.video-grid {
  display: grid;
  grid-template-columns: 1.35fr 0.65fr;
  gap: 12px;
}

.video-tile {
  position: relative;
  background: #0b1220;
  border-radius: 18px;
  overflow: hidden;
  min-height: 310px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
}

.video-tile.local {
  min-height: 310px;
}

.tile-top {
  position: absolute;
  top: 10px;
  right: 10px;
  left: 10px;
  z-index: 2;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
}

.tile-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border-radius: 999px;
  color: #fff;
  font-size: 12px;
  font-weight: 900;
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.14);
  backdrop-filter: blur(8px);
}

.tile-chip.subtle {
  opacity: 0.9;
  font-weight: 800;
}

.vid {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.tile-hint {
  position: absolute;
  inset: auto 0 16px 0;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: rgba(255, 255, 255, 0.92);
  font-size: 12px;
  font-weight: 800;
  text-align: center;
  padding: 0 14px;
}

.hint-line {
  height: 1px;
  width: 60px;
  background: rgba(255, 255, 255, 0.25);
}

.hint-text {
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.14);
  backdrop-filter: blur(8px);
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

@media (max-width: 900px) {
  .video-grid {
    grid-template-columns: 1fr;
  }
  .video-tile {
    min-height: 260px;
  }
}
</style>