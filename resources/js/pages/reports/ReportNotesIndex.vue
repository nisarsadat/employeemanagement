<!-- src/pages/ReportNotes/ReportNotesIndex.vue -->
<template>
  <AppBar
    :title="t('Report Notes')"
    :subtitle="t('Manage notes for reports')"
    @toggle-drawer="drawer = !drawer"
  >
    <template #actions><v-btn icon /></template>
  </AppBar>

  <v-container fluid class="pt-6" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-4">
      <div>
        <div class="text-h6 font-weight-bold">{{ t('Report Notes') }}</div>
        <div class="text-caption text-medium-emphasis">
          {{ t('Manage notes for daily / weekly / monthly / quarter / yearly reports') }}
        </div>
      </div>

      <div class="d-flex ga-2">
        <v-btn color="primary" variant="flat" @click="openCreate">
          <v-icon class="mr-2">mdi-plus</v-icon>
          {{ t('Create') }}
        </v-btn>

        <v-btn
          color="error"
          variant="tonal"
          :disabled="selectedIds.length === 0"
          @click="confirmBulkDelete"
        >
          <v-icon class="mr-2">mdi-delete</v-icon>
          {{ t('Delete Selected') }}
        </v-btn>
      </div>
    </div>

    <!-- Filters -->
    <v-card rounded="xl" class="mb-4">
      <v-card-text class="py-3">
        <v-row dense>
          <v-col cols="12" md="5">
            <v-text-field
              v-model="filters.search"
              :label="t('Search')"
              variant="outlined"
              density="compact"
              clearable
              @keyup.enter="fetchData(1)"
            />
          </v-col>

          <v-col cols="12" md="4">
            <v-select
              v-model="filters.type"
              :items="typeOptions"
              item-title="label"
              item-value="value"
              :label="t('Type')"
              variant="outlined"
              density="compact"
              clearable
              @update:modelValue="fetchData(1)"
            />
          </v-col>

          <v-col cols="12" md="3">
            <v-select
              v-model="filters.perPage"
              :items="[10, 25, 50, 100, 250, 500, 1000]"
              :label="t('Per Page')"
              variant="outlined"
              density="compact"
              @update:modelValue="fetchData(1)"
            />
          </v-col>
        </v-row>

        <div class="d-flex justify-end ga-2 mt-2">
          <v-btn variant="tonal" @click="resetFilters">
            {{ t('Reset') }}
          </v-btn>
          <v-btn color="primary" variant="flat" @click="fetchData(1)">
            {{ t('Apply') }}
          </v-btn>
        </div>
      </v-card-text>
    </v-card>

    <!-- Table -->
    <v-card rounded="xl">
      <v-card-text class="pa-0">
        <v-data-table
          :headers="headers"
          :items="rows"
          :loading="loading"
          item-value="id"
          show-select
          v-model="selectedIds"
          class="elevation-0"
        >
          <template #item.type="{ item }">
            <v-chip size="small" variant="tonal">
              {{ typeLabel(item.type) }}
            </v-chip>
          </template>

          <template #item.report_note="{ item }">
            <div class="text-body-2" style="white-space: pre-wrap">
              {{ item.report_note }}
            </div>
          </template>

          <template #item.actions="{ item }">
            <div class="d-flex ga-2">
              <v-btn size="small" variant="tonal" @click="openEdit(item)">
                <v-icon size="18">mdi-pencil</v-icon>
              </v-btn>

              <v-btn size="small" variant="tonal" color="error" @click="confirmDelete(item)">
                <v-icon size="18">mdi-delete</v-icon>
              </v-btn>
            </div>
          </template>

          <template #no-data>
            <div class="py-10 text-center text-medium-emphasis">
              {{ t('No report notes found.') }}
            </div>
          </template>
        </v-data-table>
      </v-card-text>

      <!-- Pagination footer -->
      <v-divider />
      <v-card-actions class="py-3 d-flex justify-space-between">
        <div class="text-caption text-medium-emphasis">
          {{ t('Page') }} {{ meta.current_page || 1 }} {{ t('of') }} {{ meta.last_page || 1 }} —
          {{ t('Total') }}: {{ meta.total || 0 }}
        </div>

        <div class="d-flex ga-2">
          <v-btn
            variant="tonal"
            :disabled="(meta.current_page || 1) <= 1"
            @click="fetchData((meta.current_page || 1) - 1)"
          >
            {{ t('Prev') }}
          </v-btn>

          <v-btn
            variant="tonal"
            :disabled="(meta.current_page || 1) >= (meta.last_page || 1)"
            @click="fetchData((meta.current_page || 1) + 1)"
          >
            {{ t('Next') }}
          </v-btn>
        </div>
      </v-card-actions>
    </v-card>

    <!-- Create/Edit Popup -->
    <v-dialog v-model="dialog.open" max-width="720">
      <v-card rounded="xl">
        <v-card-title class="d-flex align-center justify-space-between">
          <div class="text-subtitle-1 font-weight-bold">
            {{ dialog.mode === 'create' ? t('Create Report Note') : t('Edit Report Note') }}
          </div>
          <v-btn icon variant="text" @click="closeDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider />

        <v-card-text class="pt-4">
          <v-form ref="formRef" @submit.prevent="submitDialog">
            <v-row dense>
              <v-col cols="12" md="12">
                <v-select
                  v-model="dialog.form.type"
                  :items="typeOptions"
                  item-title="label"
                  item-value="value"
                  :label="t('Type')"
                  variant="outlined"
                  density="compact"
                  :rules="[rules.required, rules.typeValid]"
                  :error="hasError('type')"
                  :error-messages="getErrorMessages('type')"
                />
              </v-col>

              <v-col cols="12" md="12">
                <v-textarea
                  v-model="dialog.form.report_note"
                  :label="t('Report Note')"
                  variant="outlined"
                  density="compact"
                  rows="6"
                  auto-grow
                  :rules="[rules.required]"
                  :error="hasError('report_note')"
                  :error-messages="getErrorMessages('report_note')"
                />
              </v-col>
            </v-row>

            <div class="d-flex justify-end ga-2 mt-2">
              <v-btn variant="tonal" @click="closeDialog">{{ t('Cancel') }}</v-btn>
              <v-btn color="primary" variant="flat" type="submit" :loading="dialog.saving">
                {{ dialog.mode === 'create' ? t('Create') : t('Update') }}
              </v-btn>
            </div>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Delete Confirm -->
    <v-dialog v-model="deleteDialog.open" max-width="520">
      <v-card rounded="xl">
        <v-card-title class="text-subtitle-1 font-weight-bold">
          {{ t('Confirm Delete') }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pt-4">
          {{ t('Are you sure you want to delete this report note?') }}
        </v-card-text>
        <v-card-actions class="d-flex justify-end ga-2">
          <v-btn variant="tonal" @click="deleteDialog.open = false">{{ t('Cancel') }}</v-btn>
          <v-btn color="error" variant="flat" :loading="deleteDialog.loading" @click="doDelete">
            {{ t('Delete') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Bulk Delete Confirm -->
    <v-dialog v-model="bulkDeleteDialog.open" max-width="520">
      <v-card rounded="xl">
        <v-card-title class="text-subtitle-1 font-weight-bold">
          {{ t('Confirm Bulk Delete') }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pt-4">
          {{ t('Delete') }} <b>{{ selectedIds.length }}</b> {{ t('selected report notes?') }}
        </v-card-text>
        <v-card-actions class="d-flex justify-end ga-2">
          <v-btn variant="tonal" @click="bulkDeleteDialog.open = false">{{ t('Cancel') }}</v-btn>
          <v-btn
            color="error"
            variant="flat"
            :loading="bulkDeleteDialog.loading"
            @click="doBulkDelete"
          >
            {{ t('Delete') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar.open" :timeout="2500">
      {{ snackbar.text }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppBar from '@/components/AppBar.vue'
import { useReportNoteRepository } from '@/stores/ReportNoteRepository'

const { t, locale } = useI18n()

const drawer = ref(false)
const rtl = computed(() => locale.value === 'fa' || locale.value === 'ps')

const repo = useReportNoteRepository()

const TYPE_VALUES = ['daily', 'weekly', 'monthly', 'quarter', 'yearly']

const typeOptions = computed(() => [
  { label: t('Daily'), value: 'daily' },
  { label: t('Weekly'), value: 'weekly' },
  { label: t('Monthly'), value: 'monthly' },
  { label: t('Quarter'), value: 'quarter' },
  { label: t('Yearly'), value: 'yearly' },
])

function typeLabel(v) {
  const s = String(v || '').toLowerCase()
  if (s === 'daily') return t('Daily')
  if (s === 'weekly') return t('Weekly')
  if (s === 'monthly') return t('Monthly')
  if (s === 'quarter') return t('Quarter')
  if (s === 'yearly') return t('Yearly')
  return v
}

const headers = computed(() => [
  { title: t('ID'), key: 'id', width: 80 },
  { title: t('Type'), key: 'type', width: 140 },
  { title: t('Report Note'), key: 'report_note' },
  { title: t('Created'), key: 'created_at', width: 180 },
  { title: t('Actions'), key: 'actions', sortable: false, width: 120 },
])

const filters = reactive({
  search: '',
  type: null,
  perPage: 50,
})

const loading = ref(false)
const rows = ref([])
const meta = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
})

const selectedIds = ref([])

const snackbar = reactive({
  open: false,
  text: '',
})
function toast(text) {
  snackbar.text = text
  snackbar.open = true
}

const formRef = ref(null)
const dialog = reactive({
  open: false,
  mode: 'create',
  saving: false,
  id: null,
  form: {
    type: 'daily',
    report_note: '',
  },
  errors: {},
})

const deleteDialog = reactive({
  open: false,
  loading: false,
  id: null,
})

const bulkDeleteDialog = reactive({
  open: false,
  loading: false,
})

const rules = {
  required: (v) => (!!v || v === 0) || t('Required'),
  typeValid: (v) => TYPE_VALUES.includes(v) || t('Invalid type'),
}

function setErrorsFromApi(err) {
  dialog.errors = {}
  const data = err?.response?.data
  if (data?.errors && typeof data.errors === 'object') {
    dialog.errors = data.errors
  }
}

function hasError(field) {
  return Array.isArray(dialog.errors?.[field]) && dialog.errors[field].length > 0
}
function getErrorMessages(field) {
  return dialog.errors?.[field] || []
}

async function fetchData(page = 1) {
  loading.value = true
  try {
    const res = await repo.FetchReportNotesData({
      page,
      perPage: filters.perPage,
      search: filters.search || undefined,
      type: filters.type || undefined,
    })

    rows.value = res.data || []
    meta.current_page = res.current_page || 1
    meta.last_page = res.last_page || 1
    meta.total = res.total || 0

    selectedIds.value = []
  } catch (e) {
    toast(t('Failed to load report notes.'))
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.search = ''
  filters.type = null
  filters.perPage = 50
  fetchData(1)
}

function openCreate() {
  dialog.mode = 'create'
  dialog.id = null
  dialog.errors = {}
  dialog.form.type = 'daily'
  dialog.form.report_note = ''
  dialog.open = true
}

function openEdit(item) {
  dialog.mode = 'edit'
  dialog.id = item.id
  dialog.errors = {}
  dialog.form.type = item.type
  dialog.form.report_note = item.report_note
  dialog.open = true
}

function closeDialog() {
  dialog.open = false
  dialog.errors = {}
}

async function submitDialog() {
  dialog.errors = {}

  const form = formRef.value
  if (form) {
    const result = await form.validate()
    if (!result.valid) return
  }

  dialog.saving = true
  try {
    const payload = {
      type: dialog.form.type,
      report_note: dialog.form.report_note,
    }

    if (dialog.mode === 'create') {
      await repo.CreateReportNote(payload)
      toast(t('Created successfully.'))
    } else {
      await repo.UpdateReportNote(dialog.id, payload)
      toast(t('Updated successfully.'))
    }

    dialog.open = false
    await fetchData(meta.current_page || 1)
  } catch (e) {
    setErrorsFromApi(e)
    if (!Object.keys(dialog.errors).length) toast(t('Save failed.'))
  } finally {
    dialog.saving = false
  }
}

function confirmDelete(item) {
  deleteDialog.id = item.id
  deleteDialog.open = true
}

async function doDelete() {
  deleteDialog.loading = true
  try {
    await repo.DeleteReportNote(deleteDialog.id)
    toast(t('Deleted successfully.'))
    deleteDialog.open = false
    await fetchData(meta.current_page || 1)
  } catch (e) {
    toast(t('Delete failed.'))
  } finally {
    deleteDialog.loading = false
  }
}

function confirmBulkDelete() {
  bulkDeleteDialog.open = true
}

async function doBulkDelete() {
  bulkDeleteDialog.loading = true
  try {
    await repo.BulkDeleteReportNotes(selectedIds.value)
    toast(t('Deleted selected notes.'))
    bulkDeleteDialog.open = false
    await fetchData(1)
  } catch (e) {
    toast(t('Bulk delete failed.'))
  } finally {
    bulkDeleteDialog.loading = false
  }
}

onMounted(() => {
  fetchData(1)
})
</script>

<style scoped>
/* keep it clean + compatible with your project */
</style>
