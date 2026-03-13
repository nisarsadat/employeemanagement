<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('نوعیت اسناد')" :subtitle="t('مدیریت انواع اسناد')">
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
            v-model.trim="repo.Search"
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

        <v-col cols="12" sm="4" md="3" lg="2">
          <v-btn
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="openCreate"
          >
            {{ t('جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- Count -->
    <v-row class="px-2 mt-1">
      <v-col cols="12" class="d-flex justify-end">
        <div class="text-caption text-medium-emphasis">
          {{ t('تعداد') }}: <b>{{ repo.totalItems }}</b>
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
      class="mb-4 mx-2"
    >
      <template #cell-name="{ row }">
        <div class="d-flex align-center justify-center w-100">
          <v-icon size="16" class="me-1" color="primary">mdi-file-document-outline</v-icon>
          <span class="text-caption">{{ row.name || '—' }}</span>
        </div>
      </template>

      <template #actions="{ item }">
        <div class="d-flex ga-2 justify-center">
          <v-icon size="small" color="amber" @click.stop="startEdit(item?.raw ?? item)">
            mdi-pencil
          </v-icon>

          <v-icon size="small" color="red" @click.stop="openDelete(item?.raw ?? item)">
            mdi-delete
          </v-icon>
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
    <DocsTypeFormDialog v-model="dialog" :editId="editId" @saved="fetchAll" />

    <!-- Delete -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="confirmDelete"
    />
  </v-container>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

import AppBar from '@/components/AppBar.vue';
import FilterableTable from '@/components/FilterableTable.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import GlobalPagination from '@/components/GlobalPagination.vue';

import DocsTypeFormDialog from './DocsTypeFormDialog.vue';
import { useDocsTypeRepository } from '@/stores/DocsTypeRepository';

const { t, locale } = useI18n();
const isRtl = ref(locale.value === 'fa');
watch(locale, (v) => (isRtl.value = v === 'fa'));

const repo = useDocsTypeRepository();

const dialog = ref(false);
const editId = ref(null);

const deleteDialog = ref(false);
const deleteTarget = ref(null);

const headersFT = computed(() => [{ title: t('نوعیت اسناد'), key: 'name', align: 'center' }]);

const rowsFT = computed(() => repo.DocsTypes || []);

function openCreate() {
  editId.value = null;
  dialog.value = true;
}

function startEdit(payload) {
  const row = payload?.raw ?? payload;
  if (!row?.id) return;
  editId.value = row.id;
  dialog.value = true;
}

function openDelete(payload) {
  const row = payload?.raw ?? payload;
  if (!row?.id) return;
  deleteTarget.value = row;
  deleteDialog.value = true;
}

async function confirmDelete(payload) {
  const row = payload?.raw ?? payload ?? deleteTarget.value;
  if (!row?.id) return;

  await repo.DeleteDocsType(row.id);
  await fetchAll();

  deleteDialog.value = false;
  deleteTarget.value = null;
}

async function fetchAll() {
  await repo.FetchDocsTypes();
}

function clearSearch() {
  repo.Search = '';
  repo.page = 1;
  fetchAll();
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

onMounted(fetchAll);
</script>