<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('اشخاص غیرمسؤول')"
      :subtitle="t('اضافه کردن یک ریکارډ اشخاص غیرمسؤول')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search + Add -->
    <v-row class="d-flex">
      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetch()"
          clearable
          @click:clear="
            () => {
              repo.Search = '';
              fetch();
            }
          "
        />
      </v-col>
      <v-col cols="12" md="3" class="text-md-right">
        <v-btn color="primary" @click="addBlankRow">
          {{ t('اضافه کردن ریکارډ جدید') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- TABLE -->
    <v-card class="tablebody">
      <v-data-table
        :headers="headers"
        :items="rows"
        :loading="repo.loading"
        :items-per-page="repo.itemsPerPage"
        :page="repo.page"
        :items-length="repo.totalItems || 0"
        item-key="idOrTmp"
        class="elevation-2 custom-table"
        :dir="isRtl ? 'rtl' : 'ltr'"
        density="comfortable"
        @update:page="
          (p) => {
            repo.page = p;
            fetch();
          }
        "
        @update:items-per-page="
          (n) => {
            repo.itemsPerPage = n;
            fetch();
          }
        "
      >
        <!-- BASIC INFO -->
        <template #item.nick_name="{ item }"
          ><v-text-field
            v-model="item.draft.nick_name"
            label="نام مستعار"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.name="{ item }"
          ><v-text-field
            v-model="item.draft.name"
            label="نام"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.father_name="{ item }"
          ><v-text-field
            v-model="item.draft.father_name"
            label="نام پدر"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>

        <!-- MAIN ADDRESS -->
        <template #item.main_province="{ item }"
          ><v-text-field
            v-model="item.draft.main_province"
            label="ولایت اصلی"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.main_district="{ item }"
          ><v-text-field
            v-model="item.draft.main_district"
            label="ولسوالی اصلی"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.main_village="{ item }"
          ><v-text-field
            v-model="item.draft.main_village"
            label="قریه اصلی"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>

        <!-- CURRENT ADDRESS -->
        <template #item.now_province="{ item }"
          ><v-text-field
            v-model="item.draft.now_province"
            label="ولایت فعلی"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.now_zone="{ item }"
          ><v-text-field
            v-model="item.draft.now_zone"
            label="زون فعلی"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.now_district="{ item }"
          ><v-text-field
            v-model="item.draft.now_district"
            label="ولسوالی فعلی"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.now_village="{ item }"
          ><v-text-field
            v-model="item.draft.now_village"
            label="قریه فعلی"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.now_specific_place="{ item }"
          ><v-text-field
            v-model="item.draft.now_specific_place"
            label="موقعیت مشخص"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.now_street_number="{ item }"
          ><v-text-field
            v-model="item.draft.now_street_number"
            label="شماره سرک"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.now_home_number="{ item }"
          ><v-text-field
            v-model="item.draft.now_home_number"
            label="شماره خانه"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>

        <!-- SOCIAL / WORK -->
        <template #item.friends_quantity="{ item }"
          ><v-text-field
            v-model="item.draft.friends_quantity"
            label="تعداد دوستان"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.supplier="{ item }"
          ><v-text-field
            v-model="item.draft.supplier"
            label="تمویل‌کننده"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.phone_number="{ item }"
          ><v-text-field
            v-model="item.draft.phone_number"
            label="شماره تماس"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.history_jorm="{ item }"
          ><v-text-field
            v-model="item.draft.history_jorm"
            label="سابقه جرم"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.activity_place="{ item }"
          ><v-text-field
            v-model="item.draft.activity_place"
            label="محل فعالیت"
            density="compact"
            class="text-group"
            variant="outlined"
            hide-details
        /></template>
        <template #item.old_job="{ item }"
          ><v-text-field
            v-model="item.draft.old_job"
            label="وظیفه قبلی"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>
        <template #item.now_job="{ item }"
          ><v-text-field
            v-model="item.draft.now_job"
            label="وظیفه فعلی"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
        /></template>

        <!-- SECURITY INFO -->
        <template #item.gun_type="{ item }"
          ><v-text-field
            v-model="item.draft.gun_type"
            label="نوع سلاح"
            density="compact"
            class="text-group"
            variant="outlined"
            hide-details
        /></template>
        <template #item.tanzim="{ item }"
          ><v-text-field
            v-model="item.draft.tanzim"
            label="تنظیم"
            density="compact"
            class="text-group"
            variant="outlined"
            hide-details
        /></template>
        <template #item.takhalof_type="{ item }"
          ><v-text-field
            v-model="item.draft.takhalof_type"
            label="نوع تخلف"
            density="compact"
            class="text-group"
            variant="outlined"
            hide-details
        /></template>

        <!-- NOTE -->
        <template #item.note="{ item }"
          ><v-text-field
            v-model="item.draft.note"
            label="یادداشت"
            density="compact"
            class="text-group"
            variant="outlined"
            hide-details
        /></template>

        <!-- RESOURCE -->
        <template #item.resource_id="{ item }">
          <v-autocomplete
            v-model="item.draft.resource_id"
            :items="resources"
            item-title="code_number"
            item-value="id"
            :label="t('منبع')"
            clearable
            density="compact"
            variant="outlined"
            class="text-group"
            hide-details
            @update:model-value="onResourceCodeChange(item)"
          /> </template
        ><template #item.resource_phone="{ item }">
          <v-text-field
            v-model="item.draft.resource_phone"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره منبع')"
            hide-details
            readonly
          />
        </template>

        <!-- ACTIONS -->
        <template #item.actions="{ item }">
          <div class="flex items-center gap-1">
            <v-btn
              variant="outlined"
              class="bg-primary"
              :loading="item.loading"
              @click="save(item)"
            >
              {{ t('ثبت دیتا') }}
            </v-btn>
            <v-btn class="bg-error" variant="outlined" @click="del(item)">
              {{ t('حذف') }}
            </v-btn>
          </div>
        </template>

        <!-- No Data -->
        <template #no-data>
          <div class="py-8 text-medium-emphasis">{{ t('هیچ ریکارډ یافت نشد') }}</div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>

  <!-- FILTERABLE TABLE -->
  <v-container fluid>
    <FilterableTable
      :headers="headersFT"
      :rows="rows"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
    />
  </v-container>
</template>

<script setup>
  import { ref, onMounted, computed, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useMawadRepository } from '@/stores/MawadRepository';

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));
  const drawer = ref(false);

  const repo = useMawadRepository();
  const rows = ref([]);
  const filters = ref({});
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  // Resource dropdown
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const selectedId = item.draft.resource_id;
    const found = resources.value.find((r) => r.id === selectedId);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_code = found?.code_number ?? '';
  }

  // HEADERS for main table
  const headers = computed(() => [
    { title: t('نام مستعار'), key: 'nick_name' },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه اصلی'), key: 'main_village' },
    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص'), key: 'now_specific_place' },
    { title: t('شماره سرک'), key: 'now_street_number' },
    { title: t('شماره خانه'), key: 'now_home_number' },
    { title: t('تعداد دوستان'), key: 'friends_quantity' },
    { title: t('تمویل‌کننده'), key: 'supplier' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('سابقه جرم'), key: 'history_jorm' },
    { title: t('محل فعالیت'), key: 'activity_place' },
    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('نوع سلاح'), key: 'gun_type' },
    { title: t('تنظیم'), key: 'tanzim' },
    { title: t('نوع تخلف'), key: 'takhalof_type' },
    { title: t('یادداشت'), key: 'note' },
    { title: t('منبع'), key: 'resource_id' },
    { title: t('شماره منبع'), key: 'resource_phone' },
    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  // Filterable table headers
  const headersFT = headers;

  // Filter group titles
  const topGroups = computed(() => [
    { title: t('مشخصات فردی'), keys: ['nick_name', 'name', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    {
      title: t('آدرس فعلی'),
      keys: [
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
      ],
    },
    {
      title: t('فعالیت و تماس'),
      keys: [
        'friends_quantity',
        'supplier',
        'phone_number',
        'history_jorm',
        'activity_place',
        'old_job',
        'now_job',
      ],
    },
    { title: t('امنیتی'), keys: ['gun_type', 'tanzim', 'takhalof_type'] },
    { title: t('منبع و یادداشت'), keys: ['note', 'resource_id'] },
  ]);

  // Mapping
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,
      nick_name: x.nick_name ?? '',
      name: x.name ?? '',
      father_name: x.father_name ?? '',
      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',
      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',
      now_specific_place: x.now_specific_place ?? '',
      now_street_number: x.now_street_number ?? '',
      now_home_number: x.now_home_number ?? '',
      friends_quantity: x.friends_quantity ?? '',
      supplier: x.supplier ?? '',
      phone_number: x.phone_number ?? '',
      history_jorm: x.history_jorm ?? '',
      activity_place: x.activity_place ?? '',
      old_job: x.old_job ?? '',
      now_job: x.now_job ?? '',
      gun_type: x.gun_type ?? '',
      tanzim: x.tanzim ?? '',
      takhalof_type: x.takhalof_type ?? '',
      note: x.note ?? '',
      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base } };
  }

  // CRUD
  function hydrate() {
    rows.value = (repo.NonResponsablePeoples || []).map(toRowModel);
  }

  async function fetch() {
    await repo.FetchNonResponsablePeoplesData({
      page: repo.page || 1,
      itemsPerPage: repo.itemsPerPage || 50,
    });
    hydrate();
  }

  function addBlankRow() {
    rows.value.push(toRowModel({}));
  }

  function toFormData(obj) {
    const fd = new FormData();
    Object.entries(obj).forEach(([k, v]) => v != null && fd.append(k, v));
    return fd;
  }

  async function save(item) {
    item.loading = true;
    try {
      const fd = toFormData(item.draft);
      const result = item.id
        ? await repo.UpdateNonResponsablePeople(item.id, fd)
        : await repo.CreateNonResponsablePeople(fd);
      if (result?.id) Object.assign(item, toRowModel(result));
      else await fetch();
    } finally {
      item.loading = false;
    }
  }

  async function del(item) {
    if (!item.id) rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp);
    else if (confirm(t('آیا مطمئن هستید؟'))) {
      await repo.DeleteNonResponsable(item.id);
      await fetch();
    }
  }

  onMounted(async () => {
    await Promise.allSettled([repo.GetResourceCode?.()]);
    await fetch();
  });
</script>
<style scoped>
  table th {
    white-space: nowrap;
  }
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }

  /* layout */
  .addr-group {
    display: grid;
    grid-template-columns: repeat(4, minmax(120px, 1fr));
    gap: 2px;
  }
  .text-group {
    display: grid;
    width: 140px;
    gap: 2px;
  }
  .text-group-note {
    display: grid;
    width: 380px;
    gap: 2px;
  }

  .flex {
    display: flex;
  }
  .items-center {
    align-items: center;
  }
  .gap-1 {
    gap: 4px;
  }

  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }
  .pic-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .pic-thumb {
    display: inline-flex;
    cursor: pointer;
    position: relative;
  }
  .pic-overlay {
    position: absolute;
    inset: -6px -6px auto auto;
    display: none;
    gap: 6px;
    background: rgba(0, 0, 0, 0.55);
    padding: 6px;
    border-radius: 8px;
    transform: translateY(-40%);
    white-space: nowrap;
  }
  .pic-thumb:hover .pic-overlay {
    display: inline-flex;
  }
  .pic-overlay :deep(.v-btn) {
    color: #fff;
  }

  .tablebody {
    display: flex !important;
    gap: 2px !important;
  }

  :deep(.v-field-label),
  :deep(.v-label),
  :deep(input::placeholder) {
    font-size: 12px !important;
    line-height: 1.2 !important;
    color: #555 !important;
  }
  :deep(.v-field__input) {
    min-height: 26px !important;
    padding: 2px 6px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
  }

  /* compact spacing */
  .addr-group,
  .text-group,
  .text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
  }
</style>
