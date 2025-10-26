<template>
  <v-dialog
    v-model="internalOpen"
    max-width="700"
    class="form-wrapper"
    transition="dialog-top-transition"
    @update:modelValue="onModel"
  >
    <v-card :dir="isRtl ? 'rtl' : 'ltr'" rounded="xl">
      <v-card-title class="text-h6">
        {{ editMode ? t('د حساب سمون') : t('نوی حساب جوړول') }}
      </v-card-title>
      <v-divider />
      <v-card-text>
        <v-form ref="formRef" @submit.prevent="submit">
          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model.trim="localDraft.name"
                :label="t('د حساب نوم')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                required
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="localDraft.price"
                :label="t('بیه / قیمت')"
                type="number"
                variant="outlined"
                density="compact"
                :rules="[rules.required, rules.nonNegative]"
                required
              />
            </v-col>

            <v-col cols="12" md="12">
              <v-select
                v-model="localDraft.account_type_id"
                :items="accountTypeItems"
                item-title="name"
                item-value="id"
                :label="t('د حساب ډول')"
                variant="outlined"
                density="compact"
                :rules="[rules.required]"
                :loading="loadingTypes"
                required
              />
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model.trim="localDraft.details"
                :label="t('تفصیل')"
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

  const { t, locale } = useI18n();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  const props = defineProps({
    modelValue: { type: Boolean, default: false }, // v-model
    editMode: { type: Boolean, default: false },
    value: {
      type: Object,
      default: () => ({ id: null, name: '', price: 0, account_type_id: null, details: '' }),
    },
    accountTypeItems: { type: Array, default: () => [] },
    loadingTypes: { type: Boolean, default: false },
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
  const localDraft = reactive({
    id: null,
    name: '',
    price: 0,
    account_type_id: null,
    details: '',
  });

  function prime() {
    Object.assign(localDraft, {
      id: props.value?.id ?? null,
      name: props.value?.name ?? '',
      price: props.value?.price ?? 0,
      account_type_id: props.value?.account_type_id ?? null,
      details: props.value?.details ?? '',
    });
  }

  const rules = {
    required: (v) => !!v || v === 0 || t('اړین دی'),
    nonNegative: (v) => Number(v) >= 0 || t('شمېره باید له صفر څخه کمه نه وي'),
  };

  async function submit() {
    const ok = await formRef.value?.validate();
    if (!ok?.valid) return;

    emit('save', {
      id: localDraft.id,
      name: localDraft.name,
      price: Number(localDraft.price ?? 0),
      account_type_id: localDraft.account_type_id,
      details: localDraft.details || '',
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
