<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('آمریت‌ها')" :subtitle="t('مدیریت آمریت‌ها')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

    <!-- Search + Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="search"
            :label="t('جستجو در آمریت‌ها...')"
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

        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
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
    <v-row class="px-2" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" class="d-flex align-center justify-end">
        <div class="text-caption text-medium-emphasis">
          {{ t('تعداد') }}: <b>{{ filteredRows.length }}</b>
        </div>
      </v-col>
    </v-row>

    <!-- List -->
    <FilterableTable
      :headers="headersFT"
      :rows="filteredRows"
      :showIndex="true"
      :rtl="isRtl"
      :loading="repo.loading"
      class="mb-4 mx-2"
      @edit="startEdit"
    >
      <!-- Actions -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- Edit -->
          <v-tooltip :text="t('ویرایش آمریت')" location="top">
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

          <!-- Delete -->
          <v-tooltip :text="t('حذف آمریت')" location="top">
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
      @update:page="handlePageChange"
      @update:items-per-page="handlePerPageChange"
    />

    <!-- Dialog -->
    <ZoneFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

    <!-- Delete Confirm -->
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
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import AppBar from '@/components/AppBar.vue';
import FilterableTable from '@/components/FilterableTable.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import GlobalPagination from '@/components/GlobalPagination.vue';

import ZoneFormDialog from './ZoneFormDialog.vue';
import { useZoneHawzaRepository } from '@/stores/ZoneHawzaRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  usePageRefresh('/app/settings/zones/zones', fetchAll);
const { t, locale } = useI18n();
const repo = useZoneHawzaRepository();

const isRtl = ref(locale.value === 'fa');
watch(locale, (newLocale) => (isRtl.value = newLocale === 'fa'));

const showError = ref(false);
const errorMessage = ref('');
const search = ref('');

const rows = ref([]);

const headersFT = computed(() => [
  { title: t('نام آمریت'), key: 'name' },
  { title: t('تعداد حوزه‌ها'), key: 'hawzas_count' },
]);

const filteredRows = computed(() => rows.value || []);

function showErrorMessage(msg) {
  errorMessage.value = msg;
  showError.value = true;
}

function clearSearch() {
  search.value = '';
  repo.page = 1;
  fetchAll();
}

/* dialog */
const dialog = ref(false);
const editId = ref(null);

function openCreate() {
  editId.value = null;
  dialog.value = true;
}

function startEdit(row) {
  const r = row?.raw ?? row;
  if (!r?.id) return;
  editId.value = r.id;
  dialog.value = true;
}

function onSaved() {
  fetchAll();
}

/* delete */
const deleteDialog = ref(false);
const deleteTarget = ref(null);

function openDeleteDialog(row) {
  const r = row?.raw ?? row;
  if (!r?.id) return;
  deleteTarget.value = r;
  deleteDialog.value = true;
}

async function handleDeleteConfirmed(target) {
  const r = target || deleteTarget.value;
  if (!r?.id) {
    deleteDialog.value = false;
    deleteTarget.value = null;
    return;
  }
  try {
    await repo.DeleteZone(r.id);
    await fetchAll();
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در حذف آمریت'));
  } finally {
    deleteDialog.value = false;
    deleteTarget.value = null;
  }
}

/* api */
async function fetchAll() {
  try {
    const res = await repo.FetchZonesData({
      page: repo.page,
      perPage: repo.itemsPerPage,
      search: search.value,
    });
    rows.value = res?.data || [];
  } catch (e) {
    console.error(e);
    showErrorMessage(t('خطا در دریافت آمریت‌ها'));
  }
}

function handlePageChange(page) {
  repo.page = page;
  fetchAll();
}

function handlePerPageChange(perPage) {
  repo.itemsPerPage = perPage;
  repo.page = 1;
  fetchAll();
}

watch(search, () => {
  repo.page = 1;
  fetchAll();
});

onMounted(fetchAll);
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
</style>