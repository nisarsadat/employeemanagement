<!-- src/pages/ProvinceDistricts/DistrictsIndex.vue -->
<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('والسوالی/حوزه')" :subtitle="t('مدیریت ولسوالی‌ها')">
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
            :label="t('جستجو در ولسوالی‌ها...')"
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

    <!-- Optional filter: Province -->
    <v-row class="px-2" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" sm="4" md="3">
        <v-autocomplete
          v-model="listProvinceId"
          :items="provinceOptions"
          item-title="name"
          item-value="id"
          :label="t('فیلتر ولایت')"
          clearable
          density="comfortable"
          variant="outlined"
          rounded="lg"
          class="bg-white"
        />
      </v-col>

      <v-col cols="12" sm="8" md="9" class="d-flex align-center justify-end">
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
      class=" mx-2"
    >
      <template #cell-province_name="{ row }">
        <div class="">
          <v-icon size="16" class="me-1" color="primary">mdi-map-marker</v-icon>
          <span class="text-caption">{{ row.province_name || '—' }}</span>
        </div>
      </template>

      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip :text="t('ویرایش ولسوالی')" location="top">
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

          <v-tooltip :text="t('حذف ولسوالی')" location="top">
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

    <!-- Create/Edit Dialog -->
    <DistrictFormDialog
      v-model="dialog"
      :editId="editId"
      :provinces="provinceOptions"
      @saved="onSaved"
    />

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

import DistrictFormDialog from './DistrictFormDialog.vue';
import { useProvinceDistrictRepository } from '@/stores/ProvinceDistrictRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  usePageRefresh('/app/settings/districts', fetchAll);
const { t, locale } = useI18n();
const repo = useProvinceDistrictRepository();

const isRtl = ref(locale.value === 'fa');
watch(locale, (newLocale) => (isRtl.value = newLocale === 'fa'));

const showError = ref(false);
const errorMessage = ref('');
function showErrorMessage(message) {
  errorMessage.value = message;
  showError.value = true;
}

function getRow(payload) {
  return payload?.raw ?? payload;
}

const provinces = ref([]);
const provinceOptions = computed(() => provinces.value || []);

const search = ref('');
const listProvinceId = ref(null);

const rows = ref([]);

const headersFT = computed(() => [
  {
    title: t('ولایت'),
    key: 'province_name',
    align: 'center',
  },
  {
    title: t('والسوالی/حوزه'),
    key: 'name',
    align: 'center',
  },
]);

const filteredRows = computed(() => rows.value || []);

const dialog = ref(false);
const editId = ref(null);

const deleteDialog = ref(false);
const deleteTarget = ref(null);

function openCreate() {
  editId.value = null;
  dialog.value = true;
}

function startEdit(payload) {
  const row = getRow(payload);
  if (!row?.id) return;
  editId.value = row.id;
  dialog.value = true;
}

function onSaved() {
  fetchAll();
}

function openDeleteDialog(payload) {
  const row = getRow(payload);
  if (!row?.id) return;
  deleteTarget.value = row;
  deleteDialog.value = true;
}

async function handleDeleteConfirmed(target) {
  const row = target || deleteTarget.value;

  if (!row?.id) {
    deleteDialog.value = false;
    deleteTarget.value = null;
    return;
  }

  try {
    await repo.DeleteDistrict(row.id);
    await fetchAll();
  } catch (e) {
    console.error('DeleteDistrict error', e);
    showErrorMessage(t('خطا در حذف ولسوالی'));
  } finally {
    deleteDialog.value = false;
    deleteTarget.value = null;
  }
}

async function fetchProvinces() {
  try {
    const res = await repo.FetchProvincesData({
      page: 1,
      perPage: 500,
      search: '',
    });
    provinces.value = res?.data || [];
  } catch (e) {
    console.error('fetchProvinces error', e);
    showErrorMessage(t('خطا در دریافت ولایت‌ها'));
  }
}

async function fetchAll() {
  try {
    const res = await repo.FetchDistrictsData({
      page: repo.page,
      perPage: repo.itemsPerPage,
      search: search.value,
      province_id: listProvinceId.value,
    });
    rows.value = res?.data || [];
  } catch (e) {
    console.error('fetchAll districts error', e);
    showErrorMessage(t('خطا در دریافت ولسوالی‌ها'));
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

function clearSearch() {
  search.value = '';
  repo.page = 1;
  fetchAll();
}

watch([search, listProvinceId], () => {
  repo.page = 1;
  fetchAll();
});

onMounted(async () => {
  await fetchProvinces();
  await fetchAll();
});
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