<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('مرجع‌ها')" :subtitle="t('مدیریت مرجع‌ها')">
      <template #actions>
        <v-btn icon></v-btn>
      </template>
    </AppBar>

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

    <v-container class="pa-0">
      <v-row class="align-center">
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="search"
            :label="t('جستجو در مرجع‌ها...')"
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

    <v-row class="px-2 mt-1">
      <v-col cols="12" class="d-flex justify-end">
        <div class="text-caption text-medium-emphasis">
          {{ t('تعداد') }}: <b>{{ rowsFT.length }}</b>
        </div>
      </v-col>
    </v-row>

    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :showIndex="true"
      :rtl="isRtl"
      :loading="repo.loading"
      class="mb-20 mx-2"
    >
      <template #cell-name="{ row }">
        <div class="d-flex align-center justify-center w-100">
          <v-icon size="16" class="me-1" color="primary">mdi-account-tie-outline</v-icon>
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

    <MarjaFormDialog v-model="dialog" :editId="editId" @saved="fetchAll" />

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

import MarjaFormDialog from './MarjaFormDialog.vue';
import { useMarjaRepository } from '@/stores/MarjaRepository';

const { t, locale } = useI18n();
const isRtl = ref(locale.value === 'fa');
watch(locale, (v) => (isRtl.value = v === 'fa'));

const repo = useMarjaRepository();

const dialog = ref(false);
const editId = ref(null);

const deleteDialog = ref(false);
const deleteTarget = ref(null);

const search = ref('');
const rows = ref([]);

const headersFT = computed(() => [{ title: t('نام مرجع'), key: 'name', align: 'center' }]);
const rowsFT = computed(() => rows.value || []);

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

  await repo.DeleteMarja(row.id);
  await fetchAll();

  deleteDialog.value = false;
  deleteTarget.value = null;
}

async function fetchAll() {
  const res = await repo.FetchMarjas({ page: 1, perPage: 10000, search: search.value });
  rows.value = res?.data || [];
}

function clearSearch() {
  search.value = '';
  fetchAll();
}

watch(search, fetchAll);
onMounted(fetchAll);
</script>
