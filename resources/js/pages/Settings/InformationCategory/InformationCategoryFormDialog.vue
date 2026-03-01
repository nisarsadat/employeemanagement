<template>
  <v-dialog v-model="model" max-width="820" scrollable :scrim="true">
    <v-card rounded="xl" class="dialog-card overflow-hidden" :dir="rtl ? 'rtl' : 'ltr'">
      <div class="dialog-header">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center ga-3">
            <div class="icon-badge">
              <v-icon icon="mdi-shape-outline" size="22" />
            </div>

            <div class="min-w-0">
              <div class="text-h6 font-weight-bold mb-0">
                {{ isEdit ? t('ویرایش کتگوری معلومات') : t('ثبت کتگوری معلومات جدید') }}
              </div>
              <!-- <div class="text-caption opacity-90 text-truncate">
                {{ isEdit ? t('کتگوری را ویرایش کنید') : t('کتگوری جدید را وارد کنید') }}
              </div> -->
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

            <div class="grid-1">
              <v-text-field
                v-model="draft.information_category"
                :label="t('کتگوری') + ' *'"
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-shape-outline"
                :rules="[rules.required]"
                :disabled="loadingEdit"
                :loading="loadingEdit"
                :error="hasError('information_category')"
                :error-messages="getErrorMessages('information_category')"
                class="field"
              />
            </div>
          </div>

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
              :disabled="loadingEdit"
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
import { useInformationCategoryRepository } from '@/stores/InformationCategoryRepository'

const { t, locale } = useI18n()
const rtl = computed(() => locale.value === 'fa')

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editId: { type: [Number, String, null], default: null },
})

const emit = defineEmits(['update:modelValue', 'saved'])
const repo = useInformationCategoryRepository()

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const isEdit = computed(() => props.editId !== null && props.editId !== undefined && props.editId !== '')

const formRef = ref(null)
const saving = ref(false)
const loadingEdit = ref(false)

const draft = reactive({ information_category: '' })
const backendErrors = reactive({})

const rules = {
  required: (v) => !!v || v === 0 || t('این فیلد ضروری است'),
}

function resetDraft() {
  draft.information_category = ''
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
function unwrapItem(res) {
  const root = res?.data ?? res
  return root?.data ?? root
}

async function loadForEdit() {
  if (!isEdit.value) return
  clearErrors()
  loadingEdit.value = true

  try {
    const res = await repo.FetchInformationCategory(props.editId)
    const item = unwrapItem(res)
    draft.information_category = item?.information_category ?? ''
  } catch (e) {
    console.error('loadForEdit error', e)
    backendErrors.general = [t('خطا در دریافت معلومات کتگوری')]
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
  resetDraft()
  clearErrors()
}

async function onSubmit() {
  if (loadingEdit.value) return
  clearErrors()

  const res = await formRef.value?.validate?.()
  if (!res?.valid) return

  saving.value = true
  try {
    const payload = { information_category: draft.information_category }

    if (isEdit.value) {
      await repo.UpdateInformationCategory(props.editId, payload)
    } else {
      await repo.CreateInformationCategory(payload)
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
/* same style as your dialogs */
.field :deep(.v-field) {
  border-radius: 12px;
  background: #fff;
  transition: all 0.2s;
  border: 1px solid transparent;
  overflow: hidden;
}
.field :deep(.v-field__outline) { display: none; }
.field :deep(.v-field)::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 12px;
  border: 1.5px solid #e0e0e0;
  pointer-events: none;
  transition: all 0.2s;
}
.field :deep(.v-field):hover::before {
  border-color: #bdbdbd;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.field :deep(.v-field--focused) {
  background: #fff;
  box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.15);
}
.field :deep(.v-field--focused)::before {
  border-color: #2196f3;
  border-width: 2px;
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.1);
}
.field :deep(.v-field--error) { background: #fff5f5; }
.field :deep(.v-field--error)::before {
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.02);
}

.dialog-card { border: 1px solid rgba(0, 0, 0, 0.06); background: #fff; }
.dialog-header {
  padding: 18px;
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
.grid-1 { display: grid; grid-template-columns: 1fr; gap: 14px; }
.actions { display: flex; justify-content: flex-end; gap: 10px; }
.v-btn { text-transform: none; font-weight: 700; letter-spacing: normal; }
</style>