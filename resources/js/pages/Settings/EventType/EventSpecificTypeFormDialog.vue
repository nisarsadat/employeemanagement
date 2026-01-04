<!-- src/pages/EventSpecificTypes/EventSpecificTypeFormDialog.vue -->
<template>
  <v-dialog v-model="model" max-width="860" scrollable :scrim="true">
    <v-card rounded="xl" class="dialog-card overflow-hidden" :dir="rtl ? 'rtl' : 'ltr'">
      <!-- Header -->
      <div class="dialog-header">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center gap-3">
            <div class="icon-badge">
              <v-icon icon="mdi-tag-multiple-outline" size="22" />
            </div>

            <div>
              <div class="text-h6 font-weight-bold mb-0">
                {{ isEdit ? t('ویرایش نوع مشخص رویداد') : t('ثبت نوع مشخص رویداد جدید') }}
              </div>
              <div class="text-caption opacity-90">
                {{ isEdit ? t('اطلاعات نوع مشخص رویداد را ویرایش کنید') : t('اطلاعات نوع مشخص رویداد جدید را وارد کنید') }}
              </div>
            </div>
          </div>

          <v-btn icon="mdi-close" variant="text" size="small" class="rounded-lg" @click="close" />
        </div>
      </div>

      <v-divider />

      <v-card-text class="pa-5">
        <v-form ref="formRef" @submit.prevent="onSubmit">
          <div class="section">
            <div class="section-title">
              <v-icon icon="mdi-information-outline" size="18" class="me-2" />
              {{ t('اطلاعات اصلی') }}
            </div>

            <div class="grid-2">
              <v-select
                v-model="draft.event_type_id"
                :items="eventTypeOptions"
                item-title="name"
                item-value="id"
                :label="t('نوع رویداد') + ' *'"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-shape-outline"
                :rules="[rules.selectRequired]"
                :error="hasError('event_type_id')"
                :error-messages="getErrorMessages('event_type_id')"
                class="field"
              />

              <v-text-field
                v-model="draft.name"
                :label="t('نام') + ' *'"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-format-title"
                :rules="[rules.required]"
                :error="hasError('name')"
                :error-messages="getErrorMessages('name')"
                class="field"
              />
            </div>
          </div>

          <!-- General errors -->
          <div v-if="backendErrors.general?.length" class="mt-4">
            <v-alert type="error" variant="tonal" density="compact" class="rounded-lg">
              <div v-for="(m, i) in backendErrors.general" :key="i" class="text-caption">
                {{ m }}
              </div>
            </v-alert>
          </div>

          <div class="actions mt-6">
            <v-btn variant="text" class="rounded-lg px-4" color="grey-darken-1" @click="close">
              {{ t('انصراف') }}
            </v-btn>

            <v-btn
              color="primary"
              :loading="saving"
              type="submit"
              class="rounded-lg px-6"
              prepend-icon="mdi-check"
              elevation="1"
            >
              {{ isEdit ? t('ذخیره تغییرات') : t('ثبت دیتا') }}
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useEventRepo } from '@/stores/eventRepo'

const { t, locale } = useI18n()
const rtl = computed(() => locale.value === 'fa')

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editId: { type: [Number, String, null], default: null },
  eventTypeOptions: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue', 'saved'])

const repo = useEventRepo()

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const isEdit = computed(() => props.editId !== null && props.editId !== undefined && props.editId !== '')

const formRef = ref(null)
const saving = ref(false)
const loadingEdit = ref(false)

const draft = reactive({
  event_type_id: null,
  name: '',
})

const backendErrors = reactive({})

const rules = {
  required: (v) => !!String(v ?? '').trim() || t('این فیلد ضروری است'),
  selectRequired: (v) => !!v || t('این فیلد ضروری است'),
}

function clearErrors() {
  Object.keys(backendErrors).forEach((k) => delete backendErrors[k])
}

function hasError(key) {
  return Array.isArray(backendErrors[key]) && backendErrors[key].length > 0
}

function getErrorMessages(key) {
  return backendErrors[key] || []
}

function resetDraft() {
  draft.event_type_id = null
  draft.name = ''
}

/** ✅ unwrap response: {data:{...}} */
function unwrapData(res) {
  return res?.data ?? res
}

async function loadForEdit() {
  if (!isEdit.value) return

  clearErrors()
  loadingEdit.value = true

  try {
    let item = null

    // If you add FetchEventSpecificTypeData later, it will be used automatically
    if (typeof repo.FetchEventSpecificTypeData === 'function') {
      const res = await repo.FetchEventSpecificTypeData(props.editId)
      item = unwrapData(res)
    } else {
      // Fallback: fetch list and find (same style)
      const res = await repo.FetchEventSpecificTypesData({ page: 1, perPage: 5000, search: '', event_type_id: null })
      item = (res?.data || []).find((x) => String(x?.id) === String(props.editId))
    }

    draft.event_type_id = item?.event_type_id ?? null
    draft.name = item?.name ?? ''
  } catch (e) {
    console.error('loadForEdit error', e)
    backendErrors.general = [t('خطا در دریافت معلومات نوع مشخص رویداد')]
  } finally {
    loadingEdit.value = false
  }
}

watch(
  () => model.value,
  async (open) => {
    if (!open) return
    resetDraft()
    clearErrors()
    if (isEdit.value) await loadForEdit()
  }
)

watch(
  () => props.editId,
  async () => {
    if (!model.value) return
    resetDraft()
    clearErrors()
    if (isEdit.value) await loadForEdit()
  }
)

function close() {
  model.value = false
}

async function onSubmit() {
  clearErrors()

  const res = await formRef.value?.validate?.()
  if (!res?.valid) return

  saving.value = true
  try {
    const payload = {
      event_type_id: draft.event_type_id,
      name: draft.name,
    }

    if (isEdit.value) {
      await repo.UpdateEventSpecificType(props.editId, payload)
    } else {
      await repo.CreateEventSpecificType(payload)
    }

    emit('saved')
    close()
  } catch (e) {
    const errors = e?.response?.data?.errors
    if (errors && typeof errors === 'object') {
      Object.assign(backendErrors, errors)
    } else {
      backendErrors.general = [t('خطا رخ داد. دوباره تلاش کنید.')]
    }
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.field :deep(.v-field) {
  border-radius: 12px;
  background: #ffffff;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid transparent;
  overflow: hidden;
}
.field :deep(.v-field__outline) {
  display: none;
}
.field :deep(.v-field)::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 12px;
  border: 1.5px solid #e0e0e0;
  pointer-events: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.field :deep(.v-field):hover::before {
  border-color: #bdbdbd;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.field :deep(.v-field--focused) {
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.15);
}
.field :deep(.v-field--focused)::before {
  border-color: #2196f3;
  border-width: 2px;
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.1);
}
.field :deep(.v-field--error) {
  background: #fff5f5;
}
.field :deep(.v-field--error)::before {
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.02);
}
.dialog-card {
  border: 1px solid rgba(0, 0, 0, 0.06);
  background: #fff;
}
.dialog-header {
  padding: 18px 18px;
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.12), rgba(33, 150, 243, 0.06));
}
.icon-badge {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: rgba(33, 150, 243, 0.14);
  color: #2196f3;
  border: 1px solid rgba(33, 150, 243, 0.18);
}
.section {
  background: #fafafa;
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 16px;
  padding: 14px;
}
.section-title {
  display: flex;
  align-items: center;
  font-weight: 800;
  font-size: 13px;
  margin-bottom: 10px;
  color: rgba(0, 0, 0, 0.7);
}
.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
@media (max-width: 700px) {
  .grid-2 {
    grid-template-columns: 1fr;
  }
}
.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
.v-btn {
  text-transform: none;
  font-weight: 700;
  letter-spacing: normal;
}
</style>
