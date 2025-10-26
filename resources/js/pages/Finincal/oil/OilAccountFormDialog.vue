<template>
  <v-dialog v-model="internalOpen" max-width="640" transition="dialog-top-transition" @update:modelValue="onModel">
    <v-card :dir="isRtl ? 'rtl' : 'ltr'" rounded="xl">
      <v-card-title class="text-h6">{{ editMode ? t('د حساب سمون') : t('نوی د تيلو حساب') }}</v-card-title>
      <v-divider />
      <v-card-text>
        <v-form ref="formRef" @submit.prevent="emitSave">
          <v-row dense>
            <v-col cols="12">
              <v-text-field v-model.trim="localDraft.name" :label="t('نوم')" variant="outlined" density="compact"
                            :rules="[rules.required]" required />
            </v-col>
            <v-col cols="12">
              <v-textarea v-model.trim="localDraft.details" :label="t('تفصیل')" rows="3" variant="outlined" density="compact" auto-grow />
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
      <v-divider />
      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="close">{{ t('لغوه') }}</v-btn>
        <v-btn color="primary" :loading="busy" @click="emitSave">{{ editMode ? t('اوسمهالول') : t('ثبتول') }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { reactive, ref, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editMode: { type: Boolean, default: false },
  value: { type: Object, default: () => ({ id: null, name: '', details: '' }) },
  busy: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue', 'save']);

const { t, locale } = useI18n();
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));
const internalOpen = ref(props.modelValue);
const formRef = ref(null);
const localDraft = reactive({ id: null, name: '', details: '' });

watch(() => props.modelValue, v => { internalOpen.value = v; if (v) prime(); });
function onModel(v){ emit('update:modelValue', v); }
function close(){ internalOpen.value = false; emit('update:modelValue', false); }

function prime(){
  Object.assign(localDraft, {
    id: props.value?.id ?? null,
    name: props.value?.name ?? '',
    details: props.value?.details ?? '',
  });
}

const rules = { required: v => !!v || t('اړین دی') };

async function emitSave(){
  const ok = await formRef.value?.validate();
  if (!ok?.valid) return;
  emit('save', { id: localDraft.id, name: localDraft.name, details: localDraft.details || '' });
}
</script>
