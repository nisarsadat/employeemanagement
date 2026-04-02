<template>
  <v-container fluid class="pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('مکتوب های صادره/وارده')" :subtitle="t('فهرست و جستجو')">
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

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
            class="bg-white search-field"
          />
        </v-col>

        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            v-if="auth.can('outgoingincomming.create')"
            color="primary"
            variant="flat"
            size="large"
            class="text-none"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('ثبت جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- STATS -->
    <v-row class="px-2 mt-2 d-flex justify-center" :dir="isRtl ? 'rtl' : 'ltr'">
      <v-col cols="12" md="3" lg="3">
        <v-card class="stat-card" rounded="xl" @click="applyDocsTypeFilter(DOCS_TYPES.AMIR)">
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-primary fontfamily">{{ amirCount }}</div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('د امیرالمومنین شیخ صاحب فرامین') }}
              </div>
            </div>
            <v-avatar color="primary" variant="tonal" size="48">
              <v-icon color="primary">mdi-crown</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          rounded="xl"
          @click="applyDocsTypeFilter(DOCS_TYPES.R069_APPROVED)"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-indigo fontfamily">
                {{ r069ApprovedCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('د ۰۶۹ ریاست له لوري احکام شوي وړاندیزونه ') }}
              </div>
            </div>
            <v-avatar color="indigo" variant="tonal" size="48">
              <v-icon color="indigo">mdi-check-decagram</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          rounded="xl"
          @click="applyDocsTypeFilter(DOCS_TYPES.R069_REJECTED)"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-green fontfamily">
                {{ r069RejectedCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('د ۰۶۹ ریاست له لوري رد  شوي وړاندیزونه ') }}
              </div>
            </div>
            <v-avatar color="green" variant="tonal" size="48">
              <v-icon color="green">mdi-close-octagon</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3" lg="3">
        <v-card
          class="stat-card"
          rounded="xl"
          @click="applyDocsTypeFilter(DOCS_TYPES.LOY_APPROVED)"
        >
          <v-card-text class="pa-4 d-flex align-center justify-space-between">
            <div>
              <div class="text-h5 font-weight-bold text-cyan fontfamily">
                {{ loyApprovedCount }}
              </div>
              <div class="text-caption text-medium-emphasis fontfamily">
                {{ t('د لوي ریاست له لوري احکام شوي وړاندیزونه ') }}
              </div>
            </div>
            <v-avatar color="cyan" variant="tonal" size="48">
              <v-icon color="cyan">mdi-bank</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Peek -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detailForPeek"
        :loading="loadingOne"
        title-key="marja"
        :fields="headersFT"
        :cols="3"
        dir="rtl"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- MAIN TABLE -->
    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="updateFilters"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      :dateKeys="[
        'office_outgoing_date',
        'office_incoming_date',
        'reyasat_outgoing_date',
        'loy_reyasat_outgoing_date',
        'answer_date',
        'peshnehad',
        'outgoing_incoming_date',
      ]"
      @edit="startEdit"
      @peek="openPeek"
      class="mx-2"
    >
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <v-tooltip text="مشاهده" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon size="small" v-bind="tooltipProps" @click.stop="openPeek(item)" color="blue">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="ویرایش" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('outgoingincomming.update')"
                size="small"
                v-bind="tooltipProps"
                @click.stop="startEdit(item)"
                color="amber-darken-2"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <v-tooltip text="حذف" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-if="auth.can('outgoingincomming.delete')"
                size="small"
                v-bind="tooltipProps"
                @click.stop="openDeleteDialog(item)"
                color="red-darken-2"
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
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';

  import AppBar from '@/components/AppBar.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  import GlobalPagination from '@/components/GlobalPagination.vue';

  import { useOutgoingIncomingRepository } from '@/stores/OutgoingIncomingRepository';
  import { useLoginRepository } from '@/stores/LoginRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';

  const auth = useLoginRepository();
  const router = useRouter();
  const { t, locale } = useI18n();
  const repo = useOutgoingIncomingRepository();

  /* RTL */
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (n) => (isRtl.value = n === 'fa'));

  /* Helpers */
  function getRow(payload) {
    return payload?.raw ?? payload;
  }

  function hasValue(v) {
    if (v === null || v === undefined) return false;
    const s = String(v).trim();
    return s !== '' && s !== '0';
  }

  function normalizeText(v) {
    return String(v ?? '')
      .trim()
      .replace(/\s+/g, ' ');
  }

  /* Data */
  const rowsFT = computed(() => (Array.isArray(repo.Items) ? repo.Items : []));
  const filters = ref({});

  /* Headers */
  const headersFT = computed(() => [
    { title: t('نوع سند'), key: 'docs_type' },
    { title: t('مرجع'), key: 'marja' },
    { title: t('تفصیل'), key: 'tafseel' },

    { title: t('صادره (دفتر) صفحه'), key: 'office_outgoing_page' },
    { title: t('صادره (دفتر) تاریخ'), key: 'office_outgoing_date' },

    { title: t('وارده (دفتر) صفحه'), key: 'office_incoming_page' },
    { title: t('وارده (دفتر) تاریخ'), key: 'office_incoming_date' },

    { title: t('صادره به ریاست صفحه'), key: 'reyasat_outgoing_page' },
    { title: t('صادره به ریاست تاریخ'), key: 'reyasat_outgoing_date' },

    { title: t('وارده به ریاست صفحه'), key: 'reyasat_incomming_page' },
    { title: t('وارده به ریاست تاریخ'), key: 'reyasat_incomming_date' },

    { title: t('صادره (لوی ریاست) صفحه'), key: 'loy_reyasat_outgoing_page' },
    { title: t('صادره (لوی ریاست) تاریخ'), key: 'loy_reyasat_outgoing_date' },

    { title: t('صفحه پیشنهاد'), key: 'peshnehad' },
    { title: t('تاریخ پیشنهاد'), key: 'outgoing_incoming_date' },

    { title: t('صفحه پاسخ'), key: 'answer_page' },
    { title: t('پاسخ تاریخ'), key: 'answer_date' },

    { title: t('صفحه'), key: 'page' },
    { title: t('دوسیه'), key: 'dusya' },
    { title: t('صفحه فهرست'), key: 'fihrist_page' },

    { title: t('تعداد نقل'), key: 'naqal_count' },
    { title: t('اوراق'), key: 'awraq' },
    { title: t('ضمیمه'), key: 'zamima' },

    { title: t('ملاحظات'), key: 'notes' },
  ]);

  /* Docs type cards */
  const DOCS_TYPES = Object.freeze({
    AMIR: 'د امیر المومنین شیخ صاحب فرامین',
    R069_APPROVED: 'د ۰۶۹ ریاست له لوري احکام شوي وړاندیزونه',
    R069_REJECTED: 'د ۰۶۹ ریاست له لوري رد  شوي وړاندیزونه',
    LOY_APPROVED: 'د لوي ریاست  له لوري احکام   شوي وړاندیزونه',
  });

  function isDocsType(row, typeValue) {
    return normalizeText(row?.docs_type) === normalizeText(typeValue);
  }

  const amirCount = computed(
    () => rowsFT.value.filter((r) => isDocsType(r, DOCS_TYPES.AMIR)).length
  );
  const r069ApprovedCount = computed(
    () => rowsFT.value.filter((r) => isDocsType(r, DOCS_TYPES.R069_APPROVED)).length
  );
  const r069RejectedCount = computed(
    () => rowsFT.value.filter((r) => isDocsType(r, DOCS_TYPES.R069_REJECTED)).length
  );
  const loyApprovedCount = computed(
    () => rowsFT.value.filter((r) => isDocsType(r, DOCS_TYPES.LOY_APPROVED)).length
  );

  function applyDocsTypeFilter(typeValue) {
    const next = { ...(filters.value || {}) };
    delete next.__docsType;

    if (typeValue) {
      next.__docsType = typeValue;
    }

    filters.value = next;
  }

  function updateFilters(v) {
    filters.value = { ...(v || {}) };
  }

  /* Top groups */
  const topGroups = computed(() => [
    {
      title: t('اساسی'),
      keys: ['docs_type', 'outgoing_incoming_date', 'peshnehad', 'marja', 'who_sighn'],
    },
    {
      title: t('دفتر'),
      keys: [
        'office_outgoing_date',
        'office_outgoing_page',
        'office_incoming_date',
        'office_incoming_page',
      ],
    },
    {
      title: t('ریاست‌ها'),
      keys: [
        'reyasat_outgoing_date',
        'reyasat_outgoing_page',
        'loy_reyasat_outgoing_date',
        'loy_reyasat_outgoing_page',
      ],
    },
    { title: t('پاسخ'), keys: ['answer_date', 'answer_page'] },
    {
      title: t('فهرست/شماره‌ها'),
      keys: ['page', 'dusya', 'fihrist_page', 'naqal_count', 'awraq', 'zamima'],
    },
    { title: t('متن‌ها'), keys: ['tafseel', 'notes'] },
  ]);

  /* Error */
  const showError = ref(false);
  const errorMessage = ref('');

  function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
  }

  /* Peek */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  const detailForPeek = computed(() => detail.value || null);

  async function fetchOne(id) {
    if (!id) return;

    loadingOne.value = true;
    try {
      const d = await repo.FetchOutgoingIncomingItem(id);
      detail.value = d || {};
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت معلومات'));
    } finally {
      loadingOne.value = false;
    }
  }

  function openPeek(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }

  /* Fetch all */
  async function fetchAll() {
    try {
      await repo.FetchOutgoingIncomingData({
        page: repo.page,
        perPage: repo.itemsPerPage,
        search: repo.Search,
      });
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در دریافت فهرست'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    repo.page = 1;
    fetchAll();
  }

  function handlePageChange(p) {
    repo.page = p;
    fetchAll();
  }

  function handlePerPageChange(n) {
    repo.itemsPerPage = n;
    repo.page = 1;
    fetchAll();
  }

  /* Navigation */
  function goCreate() {
    router.push('/app/outgoingincomingform');
  }

  function startEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;

    router.push(`/app/outgoingincomingform/${row.id}`);
  }

  /* Delete */
  const deleteDialog = ref(false);
  const deleteTarget = ref(null);

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
      await repo.DeleteOutgoingIncoming(row.id);
      await fetchAll();
    } catch (e) {
      console.error(e);
      showErrorMessage(t('خطا در حذف'));
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  }

  onMounted(() => {
    fetchAll();
  });
  usePageRefresh('/app/outgoingincommings', fetchAll);

</script>

<style scoped>
  .stat-card {
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    height: 100px;
    font-family: var(--app-font) !important;
  }
  .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
  }
  .search-field :deep(.v-field) {
    min-height: 48px;
  }
  .fontfamily {
    font-family: var(--app-font) !important;
  }
</style>
