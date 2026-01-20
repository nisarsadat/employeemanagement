<!-- src/views/fifthyFormats/Luchakano.vue -->
<template>
  <v-container fluid class="page-bg pt-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- APP BAR -->
    <AppBar :title="t('لچکانو')" :subtitle="t('فهرست، جستجو و مدیریت لچکانو')">
      <template #actions>
        <v-btn icon />
      </template>
    </AppBar>

    <!-- 🔍 Search + ➕ Create -->
    <v-container class="pa-0">
      <v-row class="align-center">
        <!-- Search Field -->
        <v-col cols="12" sm="8" md="9" lg="10">
          <v-text-field
            v-model.trim="repo.Search"
            :label="t('جستجو در لچکانو...')"
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

        <!-- Create Button -->
        <v-col cols="12" sm="4" md="3" lg="2" class="pl-sm-2">
          <v-btn
            color="primary"
            variant="flat"
            size="large"
            class="text-none mx-4"
            rounded="lg"
            block
            prepend-icon="mdi-plus"
            @click="goCreate"
          >
            {{ t('اضافه کردن لچکانو جدید') }}
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- 👁 Peek drawer -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loadingOne"
        title-key="name"
        :fields="headersFT"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        :auto-detect-dates="false"
        :date-keys="[]"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- 📋 MAIN TABLE -->
    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      :autoDetectDates="false"
      class="mb-20 mx-2"
      @edit="goEdit"
      @peek="openPeek"
    >
      <!-- 🖼 PICTURE CELL -->
      <template #cell-picture="{ row }">
        <div class="pic-cell">
          <v-avatar
            v-if="row.picture"
            size="34"
            class="pic-thumb"
            @click.stop="openImage(row.picture_url, row.name)"
          >
            <v-img :src="row.picture_url" cover />
          </v-avatar>

          <v-avatar v-else size="34" class="pic-thumb pic-thumb--empty">
            <v-icon size="18">mdi-image-off</v-icon>
          </v-avatar>
        </div>
      </template>

      <!-- 🔗 RESOURCE CODE clickable -->
      <template #cell-resource_code="{ row }">
        <v-tooltip :text="t('رفتن به صفحه منابع')" location="top">
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              v-if="row.resource_code"
              variant="tonal"
              color="primary"
              append-icon="mdi-account"
              size="default"
              @click.stop="goToResource(row)"
            >
              {{ row.resource_code }}
            </v-btn>
            <span v-else>-</span>
          </template>
        </v-tooltip>
      </template>

      <!-- 🗺 Map cell -->
      <template #cell-map="{ row }">
        <FlyToMapButton
          :coordinates="row.coordinates"
          :title="row.name"
          :subtitle="row.nickname"
          type="mawads"
          :photo-url="row.picture"
          :zoom="16"
        />
      </template>

      <!-- ⚙️ ACTIONS -->
      <template #actions="{ item }">
        <div class="d-flex align-center ga-2">
          <!-- مشاهده -->
          <v-tooltip :text="t('مشاهده لچکانو')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon v-bind="tooltipProps" size="small" color="blue" @click.stop="openPeek(item)">
                mdi-eye
              </v-icon>
            </template>
          </v-tooltip>

          <!-- ویرایش -->
          <v-tooltip :text="t('ویرایش لچکانو')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-bind="tooltipProps"
                size="small"
                color="amber-darken-2"
                @click.stop="goEdit(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>

          <!-- حذف -->
          <v-tooltip :text="t('حذف لچکانو')" location="top">
            <template #activator="{ props: tooltipProps }">
              <v-icon
                v-bind="tooltipProps"
                size="small"
                color="red-darken-2"
                @click.stop="openDeleteDialog(item)"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
        </div>
      </template>
    </FilterableTable>

    <!-- 🗑 Delete Confirm Dialog -->
    <DeleteConfirmDialog
      v-model="deleteDialog"
      :target="deleteTarget"
      :is-rtl="isRtl"
      @confirm="handleDeleteConfirmed"
    />
    <!-- 🖼 IMAGE PREVIEW DIALOG -->
    <v-dialog v-model="imgDialog" max-width="920">
      <v-card rounded="xl">
        <v-card-title class="d-flex align-center justify-space-between">
          <div class="d-flex align-center ga-2">
            <v-icon>mdi-image</v-icon>
            <span class="text-subtitle-1 font-weight-bold">
              {{ imgTitle || t('پیش‌نمایش عکس') }}
            </span>
          </div>

          <v-btn icon variant="text" @click="imgDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider />

        <v-card-text>
          <div class="img-preview-wrap">
            <v-img v-if="imgSrc" :src="imgSrc" class="img-preview" cover />
            <div v-else class="muted">{{ t('عکس موجود نیست') }}</div>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- ❗ Error Snackbar -->
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
  import { ref, computed, onMounted, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRouter } from 'vue-router';

  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import FlyToMapButton from '@/components/FlyToMapButton.vue';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';

  import { useMawadRepository } from '@/stores/MawadRepository';

  const repo = useMawadRepository();
  const router = useRouter();

  /* i18n / RTL */
  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* Snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (m) => {
    errorMessage.value = m;
    showError.value = true;
  };

  /* Small helper for row/raw */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* rows from store */
  const rowsFT = computed(() => repo.Luchakanos || []);

  /* TABLE HEADERS (same as before, plus map) */
  const headersFT = computed(() => [
    { title: t('عکس'), key: 'picture', sortable: false },
    { title: t('نام'), key: 'name' },
    { title: t('تخلص'), key: 'nickname' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },

    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک فعلی'), key: 'now_street_number' },
    { title: t('شماره خانه فعلی'), key: 'now_home_number' },

    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'job' },
    { title: t('نوع اسلحه'), key: 'gun_type' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('سابقه جرم'), key: 'history_jorm' },
    { title: t('یادداشت'), key: 'note' },
    { title: t('ولایت مربوطه'), key: 'Province_addr' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
    { title: t('مختصات'), key: 'coordinates' },
    { title: t('نقشه'), key: 'map', sortable: false },
  ]);

  /* TOP GROUPS (same idea as before) */
  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'nickname', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    {
      title: t('موقعیت فعلی'),
      keys: ['now_specific_place', 'now_street_number', 'now_home_number'],
    },
    { title: t('وظیفه/اسلحه/تماس'), keys: ['old_job', 'job', 'gun_type', 'phone_number'] },
    { title: t('سوابق/یادداشت'), keys: ['history_jorm', 'note'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
  ]);
  const imgDialog = ref(false);
  const imgSrc = ref('');
  const imgTitle = ref('');

  function openImage(src, title) {
    imgSrc.value = src || '';
    imgTitle.value = title || '';
    imgDialog.value = true;
  }
  const filters = ref({});

  /* Peek */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loadingOne = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loadingOne.value = true;
    try {
      await repo.FetchLuchakanoData?.(id);
      detail.value =
        repo.Luchakano && Array.isArray(repo.Luchakano) ? repo.Luchakano[0] : repo.Luchakano || {};
    } catch (e) {
      console.error('fetchOne error', e);
      showErrorMessage(t('خطا در دریافت معلومات لچکانو'));
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

  /* Navigation */
  function goCreate() {
    router.push('/app/luchakanoform');
  }

  function goEdit(payload) {
    const row = getRow(payload);
    if (!row?.id) return;
    router.push(`/app/luchakanoform/${row.id}`);
  }

  /* Go to Resource page when clicking resource_code */
  function goToResource(row) {
    if (!row?.resource_code) return;
    // adjust this path to your actual resource route if needed
    router.push(`/app/resources/${row.resource_code}`);
  }

  /* Delete with dialog */
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
    deleteDialog.value = false;
    if (!row?.id) return;
    try {
      await repo.DeleteLuchakano?.(row.id);
      await fetchAll();
      showErrorMessage(t('حذف شد'));
    } catch (e) {
      console.error('delete error', e);
      showErrorMessage(t('خطا در حذف'));
    } finally {
      deleteTarget.value = null;
    }
  }

  /* Fetch ALL */
  async function fetchAll() {
    try {
      await repo.FetchLuchakanosData?.({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search || '',
      });
    } catch (e) {
      console.error('fetchAll error', e);
      showErrorMessage(t('خطا در دریافت فهرست لچکانو'));
    }
  }

  function clearSearch() {
    repo.Search = '';
    fetchAll();
  }

  /* INIT */
  onMounted(async () => {
    await fetchAll();
  });
</script>

<style scoped>
  .page-bg {
    background: radial-gradient(circle at top, #f2f5ff 0, #f5f5f5 40%, #fafafa 100%);
    font-family: inherit;
  }

  .ga-2 {
    gap: 8px;
  }
</style>
