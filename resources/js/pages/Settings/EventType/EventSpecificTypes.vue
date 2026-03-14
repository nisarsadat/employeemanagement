<!-- src/pages/EventSpecificTypes/EventSpecificTypesIndex.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('نوع مشخص رویداد')" :subtitle="t('مدیریت نوع مشخص رویداد')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- Error -->
    <v-alert
      v-if="repo.error"
      type="error"
      variant="tonal"
      class="mb-4 mx-2"
      border="start"
      closable
      @click:close="repo.error = null"
    >
      {{ repo.error }}
    </v-alert>

    <!-- Search + Filter + Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <v-col cols="12" md="4" lg="3">
          <v-select
            v-model="filterEventTypeId"
            :items="eventTypeOptions"
            item-title="name"
            item-value="id"
            clearable
            density="comfortable"
            variant="outlined"
            color="primary"
            rounded="lg"
            hide-details
            class="bg-white"
            :label="t('فیلتر نوع رویداد')"
            prepend-inner-icon="mdi-filter-variant"
            @update:modelValue="handleEventTypeFilterChange"
          />
        </v-col>

        <v-col cols="12" md="6" lg="7">
          <v-text-field
            v-model.trim="search"
            :label="t('جستجو...')"
            density="comfortable"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            color="primary"
            rounded="lg"
            hide-details
            @keyup.enter="fetchAll"
            clearable
            @click:clear="clearSearch"
            class="bg-white"
          />
        </v-col>

        <v-col cols="12" md="2" lg="2" class="pl-md-2">
          <v-btn
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            @click="openCreate"
            prepend-icon="mdi-plus"
          >
            {{ t('جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- Count -->
    <v-row class="px-2 mt-1" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" class="d-flex align-center justify-end">
        <div class="text-caption text-medium-emphasis">
          {{ t('تعداد') }}: <b>{{ repo.totalItems }}</b>
        </div>
      </v-col>
    </v-row>

    <!-- List -->
    <FilterableTable
      class="ft-center mb-4 mx-2"
      :headers="headersFT"
      :rows="rowsFT"
      :showIndex="true"
      :rtl="isRtl"
      :loading="repo.loading"
    >
      <!-- Event type -->
      <template #cell-event_type_name="{ row }">
        <div class="">
          <v-icon size="16" class="me-1" color="primary">mdi-shape-outline</v-icon>
          <span class="text-caption">{{ row.event_type_name || row.event_type?.name || '—' }}</span>
        </div>
      </template>

      <!-- Name -->
      <template #cell-name="{ row }">
        <div class="">
          <v-icon size="16" class="me-1" color="primary">mdi-tag-outline</v-icon>
          <span class="text-caption">{{ row.name || '—' }}</span>
        </div>
      </template>

      <!-- Actions -->
      <template #actions="{ item }">
        <div class="ga-2">
          <v-tooltip :text="t('ویرایش نوع مشخص رویداد')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="amber-darken-2"
                @click.stop="startEdit(item?.raw ?? item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('حذف نوع مشخص رویداد')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                size="small"
                v-bind="tooltipProps"
                color="red-darken-2"
                @click.stop="openDeleteDialog(item?.raw ?? item)"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
        </div>
      </template>
    </FilterableTable>

    <GlobalPagination
      :page="repo.page"
      :items-per-page="repo.itemsPerPage"
      :total-items="repo.totalItems"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    />

    <!-- Create/Edit Dialog -->
    <EventSpecificTypeFormDialog
      v-model="dialog"
      :editId="editId"
      :eventTypeOptions="eventTypeOptions"
      @saved="onSaved"
    />

    <!-- Delete confirm -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">
          {{ t('بستن') }}
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

import AppBar from '@/components/AppBar.vue'
import FilterableTable from '@/components/FilterableTable.vue'
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue'
import GlobalPagination from '@/components/GlobalPagination.vue'

import EventSpecificTypeFormDialog from './EventSpecificTypeFormDialog.vue'
import { useEventRepo } from '@/stores/eventRepo'
  import { usePageRefresh } from '@/composables/usePageRefresh';

  usePageRefresh('/app/docstypesindex', fetchAll);
const { t, locale } = useI18n()
const isRtl = ref(locale.value === 'fa')
watch(locale, (v) => (isRtl.value = v === 'fa'))

const repo = useEventRepo()

const dialog = ref(false)
const editId = ref(null)

const deleteDialog = ref(false)
const deleteTarget = ref(null)

const showError = ref(false)
const errorMessage = ref('')
function showErrorMessage(msg) {
  errorMessage.value = msg
  showError.value = true
}

const search = ref('')
const rows = ref([])

const eventTypeOptions = ref([])
const filterEventTypeId = ref(null)

const headersFT = computed(() => [
  { title: t('نوع رویداد'), key: 'event_type_name', align: 'center' },
  { title: t('نام مشخص نوع رویداد '), key: 'name', align: 'center' },
])

const rowsFT = computed(() => rows.value || [])

function getRow(payload) {
  return payload?.raw ?? payload
}

function openCreate() {
  editId.value = null
  dialog.value = true
}

function startEdit(payload) {
  const row = getRow(payload)
  if (!row?.id) return
  editId.value = row.id
  dialog.value = true
}

function onSaved() {
  fetchAll()
}

function openDeleteDialog(payload) {
  const row = getRow(payload)
  if (!row?.id) return
  deleteTarget.value = row
  deleteDialog.value = true
}

async function handleDeleteConfirmed(target) {
  const row = target || deleteTarget.value

  if (!row?.id) {
    deleteDialog.value = false
    deleteTarget.value = null
    return
  }

  try {
    await repo.DeleteEventSpecificType(row.id)
    await fetchAll()
  } catch (e) {
    console.error('DeleteEventSpecificType error', e)
    showErrorMessage(t('خطا در حذف نوع مشخص رویداد'))
  } finally {
    deleteDialog.value = false
    deleteTarget.value = null
  }
}

async function loadOptions() {
  try {
    const res = await repo.FetchEventTypeOptions()
    eventTypeOptions.value = res?.data || []
  } catch (e) {
    console.error('FetchEventTypeOptions error', e)
  }
}

async function fetchAll() {
  try {
    const res = await repo.FetchEventSpecificTypesData({
      page: repo.page,
      perPage: repo.itemsPerPage,
      search: search.value,
      event_type_id: filterEventTypeId.value,
    })

    rows.value = res?.data || []
  } catch (e) {
    console.error('fetchAll event specific types error', e)
    showErrorMessage(t('خطا در دریافت نوع مشخص رویداد'))
  }
}

function clearSearch() {
  search.value = ''
  repo.page = 1
  fetchAll()
}

function handlePageChange(page) {
  repo.page = page
  fetchAll()
}

function handlePerPageChange(perPage) {
  repo.itemsPerPage = perPage
  repo.page = 1
  fetchAll()
}

function handleEventTypeFilterChange() {
  repo.page = 1
  fetchAll()
}

watch(search, () => {
  repo.page = 1
  fetchAll()
})

onMounted(async () => {
  await loadOptions()
  await fetchAll()
})
</script>

<style scoped>
:deep(.v-data-table__tr:hover) {
  background-color: rgba(33, 150, 243, 0.04) !important;
}
:deep(.v-btn--icon) {
  transition: all 0.2s ease;
}
:deep(.v-btn--icon:hover) {
  transform: scale(1.1);
}
.ft-center :deep(th),
.ft-center :deep(td) {
  text-align: center !important;
  justify-content: center !important;
}

.ft-center :deep(.v-data-table-header__content) {
  justify-content: center !important;
}

.ft-center :deep(.v-data-table__td),
.ft-center :deep(.v-data-table__th) {
  vertical-align: middle !important;
}
</style>