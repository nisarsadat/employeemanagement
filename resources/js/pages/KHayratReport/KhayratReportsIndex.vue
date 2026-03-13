<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('گزارش خیرات')" :subtitle="t('مدیریت گزارش‌های خیرات')">
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

    <!-- Search + Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="search"
            :label="t('جستجو در گزارش‌ها...')"
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
            v-if="auth.can('khayratreport.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            @click="openCreate"
            prepend-icon="mdi-plus"
          >
            {{ t('خیریت جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- Count -->
    <v-row class="px-2 mt-1" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" class="d-flex align-center justify-end">
        <div class="text-caption text-medium-emphasis">
          {{ t('تعداد') }}: <b>{{ rowsFT.length }}</b>
        </div>
      </v-col>
    </v-row>

    <!-- List -->
    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :showIndex="true"
      :rtl="isRtl"
      :loading="repo.loading"
      :autoDetectDates="false"
      :dateKeys="['saved_date']"
      class="mb-20 mx-2"
    >
      <!-- resource -->
      <template #cell-resource_name="{ row }">
        <div class="d-flex align-center justify-center w-100">
          <v-icon size="16" class="me-1" color="primary">mdi-account-badge</v-icon>
          <span class="text-caption">{{ row.resource_name || '—' }}</span>
        </div>
      </template>

      <!-- office employee -->
      <template #cell-office_employee_name="{ row }">
        <div class="d-flex align-center justify-center w-100">
          <v-icon size="16" class="me-1" color="primary">mdi-account</v-icon>
          <span class="text-caption">{{ row.office_employee_name || '—' }}</span>
        </div>
      </template>

      <!-- zone -->
      <template #cell-zone="{ row }">
        <div class="d-flex align-center justify-center w-100">
          <v-icon size="16" class="me-1" color="primary">mdi-map-marker</v-icon>
          <span class="text-caption">{{ row.zone || '—' }}</span>
        </div>
      </template>

      <!-- hawza -->
      <template #cell-hawza="{ row }">
        <div class="d-flex align-center justify-center w-100">
          <v-icon size="16" class="me-1" color="primary">mdi-map-marker-radius</v-icon>
          <span class="text-caption">{{ row.hawza || '—' }}</span>
        </div>
      </template>

      <!-- specefic_place -->
      <template #cell-specefic_place="{ row }">
        <div class="d-flex align-center justify-center w-100">
          <v-icon size="16" class="me-1" color="primary">mdi-map</v-icon>
          <span class="text-caption">{{ row.specefic_place || '—' }}</span>
        </div>
      </template>

      <!-- Actions -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2 justify-center">
          <v-tooltip :text="t('ویرایش')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('khayratreport.update')"
                size="small"
                v-bind="tooltipProps"
                color="amber-darken-2"
                @click.stop="startEdit(item?.raw ?? item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip :text="t('حذف')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('khayratreport.delete')"
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
    <KhayratReportFormDialog v-model="dialog" :editId="editId" @saved="onSaved" />

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
  import { ref, computed, watch, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';

  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';

  import KhayratReportFormDialog from './KhayratReportFormDialog.vue';
  import { useKhayratReportRepository } from '@/stores/KhayratReportRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';
  const auth = useLoginRepository();
  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (v) => (isRtl.value = v === 'fa'));

  const repo = useKhayratReportRepository();

  const dialog = ref(false);
  const editId = ref(null);

  const deleteDialog = ref(false);
  const deleteTarget = ref(null);

  const showError = ref(false);
  const errorMessage = ref('');
  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  const search = ref('');
  const rows = ref([]);

  const headersFT = computed(() => [
    { title: t('منبع'), key: 'resource_code', align: 'center' },
    { title: t('کارمند'), key: 'office_employee_code', align: 'center' },
    { title: t('زون'), key: 'zone', align: 'center' },
    { title: t('حوزه'), key: 'hawza', align: 'center' },
    { title: t('محل مشخص'), key: 'specefic_place', align: 'center' },
    { title: t('تاریخ ثبت'), key: 'saved_date', align: 'center' },
  ]);

  const rowsFT = computed(() => rows.value || []);

  function getRow(payload) {
    return payload?.raw ?? payload;
  }

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
      await repo.DeleteKhayratReport(row.id);
      await fetchAll();
    } catch (e) {
      console.error('DeleteKhayratReport error', e);
      showErrorMessage(t('خطا در حذف گزارش'));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  async function fetchAll() {
    try {
      await repo.FetchKhayratReportsData({
        page: repo.page,
        perPage: repo.itemsPerPage,
        search: search.value,
      });
      rows.value = repo.KhayratReports || [];
    } catch (e) {
      console.error('fetchAll khayrat reports error', e);
      showErrorMessage(t('خطا در دریافت گزارش‌ها'));
    }
  }

  function clearSearch() {
    search.value = '';
    repo.page = 1;
    fetchAll();
  }

  function handlePageChange(p) {
    repo.page = p;
    fetchAll();
  }

  function handlePerPageChange(n) {
    repo.itemsPerPage = n;
    repo.perPage = n;
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