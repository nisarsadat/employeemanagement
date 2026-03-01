<template>
  <v-dialog v-model="model" max-width="920" scrollable transition="dialog-bottom-transition">
    <v-card rounded="xl" elevation="5" :dir="isRtl ? 'rtl' : 'ltr'" class="direction-dialog">
      <!-- Header with premium gradient -->
      <v-card-title class="d-flex align-center justify-space-between pa-4 bg-gradient-deep">
        <div class="d-flex align-center ga-3">
          <v-avatar color="black" size="44" variant="flat" class="elevation-3">
            <v-icon color="white" size="26">mdi-text-box-edit</v-icon>
          </v-avatar>
          <div>
            <span class="text-h6 font-weight-bold text-white"
              >{{ t('نظر آمریت تعقیب اطلاع') }} {{ t('او') }} {{ t('رهنمود رهبریت') }}</span
            >
            <div class="d-flex align-center ga-2 mt-1" v-if="id">
              <v-chip
                size="x-small"
                color="white"
                variant="outlined"
                class="text-white text-opacity-90"
              >
                ID: {{ id }}
              </v-chip>
            </div>
          </div>
        </div>

        <v-btn
          icon
          variant="text"
          @click="close"
          class="btn-close"
          color="white"
          :ripple="{ center: true }"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-divider class="border-opacity-25" />

      <!-- Body with improved spacing and animations -->
      <v-card-text class="pa-6" style="max-height: 70vh">
        <v-row>
          <v-col cols="12" md="12">
            <v-textarea
              v-model="local.tahlil"
              :label="t('نظر آمریت تعقیب اطلاع')"
              rows="5"
              auto-grow
              density="comfortable"
              variant="outlined"
              :disabled="loading"
              prepend-inner-icon="mdi-comment-search"
              bg-color="surface"
              class="rounded-lg mb-4"
              hide-details="auto"
              persistent-counter
            />
          </v-col>

          <v-col cols="12" md="12">
            <v-textarea
              v-model="local.lead_drirection"
              :label="t('رهنمود رهبریت')"
              rows="5"
              auto-grow
              density="comfortable"
              variant="outlined"
              :disabled="loading"
              prepend-inner-icon="mdi-compass"
              bg-color="surface"
              class="rounded-lg"
              hide-details="auto"
              persistent-counter
            />
          </v-col>
        </v-row>
      </v-card-text>

      <v-divider class="border-opacity-25" />

      <!-- Footer with enhanced buttons -->
      <v-card-actions class="pa-4 d-flex justify-end ga-3 bg-grey-lighten-4">
        <v-btn
          variant="outlined"
          @click="close"
          :disabled="loading"
          prepend-icon="mdi-close"
          class="text-none px-6"
          rounded="pill"
          elevation="0"
        >
          {{ t('بستن') }}
        </v-btn>

        <v-btn
          v-if="auth.can('direction.create')"
          color="primary"
          variant="flat"
          :loading="loading"
          @click="save"
          prepend-icon="mdi-content-save-check"
          class="text-none px-8"
          rounded="pill"
          size="large"
          elevation="2"
        >
          {{ t('ثبت') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { computed, reactive, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useLoginRepository } from '../../stores/LoginRepository';
  const auth = useLoginRepository();

  const { t } = useI18n();

  const props = defineProps({
    modelValue: { type: Boolean, default: false },
    id: { type: [Number, String], default: null },
    tahlil: { type: String, default: '' },
    lead_drirection: { type: String, default: '' },
    isRtl: { type: Boolean, default: true },
    loading: { type: Boolean, default: false },
  });

  const emit = defineEmits(['update:modelValue', 'save']);

  const model = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
  });

  const local = reactive({
    id: null,
    tahlil: '',
    lead_drirection: '',
  });

  function hydrateFromProps() {
    local.id = props.id ?? null;
    local.tahlil = props.tahlil ?? '';
    local.lead_drirection = props.lead_drirection ?? '';
  }

  watch(
    () => model.value,
    (open) => {
      if (open) hydrateFromProps();
    }
  );

  function close() {
    model.value = false;
  }

  function save() {
    emit('save', {
      id: local.id,
      tahlil: local.tahlil,
      lead_drirection: local.lead_drirection,
    });
  }
</script>

<style scoped>
  .bg-gradient-deep {
    background: linear-gradient(135deg, #099292 0%, #1f7ba2 100%);
  }

  .btn-close {
    transition: all 0.2s ease;
  }

  .btn-close:hover {
    transform: rotate(90deg) scale(1.1);
    background-color: rgba(24, 99, 107, 0.1);
  }

  :deep(.v-field--variant-outlined) {
    transition: all 0.2s ease;
    background-color: rgba(255, 255, 255, 0.8);
  }

  :deep(.v-field--variant-outlined:hover) {
    border-color: #011519;
    background-color: white;
  }

  :deep(.v-field--focused) {
    border-color: #096c80 !important;
  }

  :deep(.v-textarea .v-field__field) {
    min-height: 120px;
  }

  .dialog-bottom-transition-enter-active,
  .dialog-bottom-transition-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
      opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .dialog-bottom-transition-enter-from,
  .dialog-bottom-transition-leave-to {
    transform: translateY(30px);
    opacity: 0;
  }

  /* RTL specific adjustments */
  [dir='rtl'] :deep(.v-field__prepend-inner) {
    margin-left: 6px;
    margin-right: 0;
  }

  [dir='rtl'] :deep(.v-field__append-inner) {
    margin-right: 6px;
    margin-left: 0;
  }
</style>
