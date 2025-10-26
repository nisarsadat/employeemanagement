<template>
  <v-dialog
    v-model="internalOpen"
    max-width="600"
    class="form-wrapper"
    transition="dialog-top-transition"
    @update:modelValue="onModel"
  >
    <v-card :dir="isRtl ? 'rtl' : 'ltr'" rounded="xl">
      <v-card-title class="text-h6">
        {{ editMode ? t('د حساب ډول سمون') : t('د حساب ډول جوړول') }}
      </v-card-title>
      <v-divider />
      <v-card-text>
        <v-form ref="formRef" @submit.prevent="submit">
          <v-row dense>
            <v-col cols="12">
              <v-text-field
                v-model.trim="localDraft.name"
                :label="t('د حساب ډول نوم')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                required
              />
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model.trim="localDraft.note"
                :label="t('یادونه')"
                variant="outlined"
                density="compact"
                rows="3"
                auto-grow
              />
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
      <v-divider />
      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="close">{{ t('لغوه') }}</v-btn>
        <v-btn color="primary" :loading="busy" @click="submit">
          {{ editMode ? t('اوسمهالول') : t('جوړول') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { reactive, ref, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { t, locale } = useI18n(); // assumes you have i18n set up
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  const props = defineProps({
    modelValue: { type: Boolean, default: false }, // v-model
    editMode: { type: Boolean, default: false },
    value: {
      type: Object,
      default: () => ({ id: null, name: '', note: '' }),
    },
    busy: { type: Boolean, default: false },
  });

  const emit = defineEmits(['update:modelValue', 'save']);

  const internalOpen = ref(props.modelValue);
  watch(
    () => props.modelValue,
    (v) => {
      internalOpen.value = v;
      if (v) prime();
    }
  );

  function onModel(v) {
    emit('update:modelValue', v);
  }
  function close() {
    internalOpen.value = false;
    emit('update:modelValue', false);
  }

  const formRef = ref(null);
  const localDraft = reactive({ id: null, name: '', note: '' });

  function prime() {
    Object.assign(localDraft, {
      id: props.value?.id ?? null,
      name: props.value?.name ?? '',
      note: props.value?.note ?? '',
    });
  }

  const rules = {
    required: (v) => !!v || v === 0 || t('اړین دی'),
  };

  async function submit() {
    const ok = await formRef.value?.validate();
    if (!ok?.valid) return;

    emit('save', {
      id: localDraft.id,
      name: localDraft.name,
      note: localDraft.note || '',
    });
  }
</script>
<style scoped>
  .form-wrapper :deep(.v-field__outline) {
    --v-field-border-opacity: 0.08;
  }

  .form-wrapper :deep(.v-input__control) {
    min-height: 44px !important;
  }
</style>
