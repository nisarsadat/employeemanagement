<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('مکتب ها')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید مکتب')"
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
        <!-- School basic -->
        <template #item.school_name="{ item }">
          <v-text-field
            v-model="item.draft.school_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام مکتب')"
            hide-details
          />
        </template>
        <template #item.school_type="{ item }">
          <v-text-field
            v-model="item.draft.school_type"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نوع مکتب')"
            hide-details
          />
        </template>

        <!-- Principal info -->
        <template #item.principle_name="{ item }">
          <v-text-field
            v-model="item.draft.principle_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام مدیر (Principle)')"
            hide-details
          />
        </template>
        <template #item.principle_f_name="{ item }">
          <v-text-field
            v-model="item.draft.principle_f_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام پدر مدیر')"
            hide-details
          />
        </template>
        <template #item.principle_phone="{ item }">
          <v-text-field
            v-model="item.draft.principle_phone"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره مدیر')"
            hide-details
          />
        </template>

        <!-- Quantities -->
        <template #item.male_teachers_quantity="{ item }">
          <v-text-field
            v-model="item.draft.male_teachers_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('معلمان مرد')"
            hide-details
            type="number"
          />
        </template>
        <template #item.female_teachers_quantity="{ item }">
          <v-text-field
            v-model="item.draft.female_teachers_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('معلمان زن')"
            hide-details
            type="number"
          />
        </template>
        <template #item.male_students_quantity="{ item }">
          <v-text-field
            v-model="item.draft.male_students_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شاگردان پسر')"
            hide-details
            type="number"
          />
        </template>
        <template #item.female_students_quantity="{ item }">
          <v-text-field
            v-model="item.draft.female_students_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شاگردان دختر')"
            hide-details
            type="number"
          />
        </template>

        <!-- Address (grouped) -->
        <template #item.province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.province"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولایت')"
            />
            <v-text-field
              v-model="item.draft.zone"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('زون')"
            />
            <v-text-field
              v-model="item.draft.district"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولسوالی/ناحیه')"
            />
            <v-text-field
              v-model="item.draft.village"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('قریه/ناحیه کوچک')"
            />
          </div>
        </template>

        <template #item.specific_place="{ item }">
          <v-text-field
            v-model="item.draft.specific_place"
            density="compact"
            variant="outlined"
            class="text-group-note"
            :label="t('موقعیت مشخص')"
            hide-details
          />
        </template>
        <template #item.street_number="{ item }">
          <v-text-field
            v-model="item.draft.street_number"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('شماره سرک')"
            hide-details
          />
        </template>
        <template #item.coordinates="{ item }">
          <v-text-field
            v-model="item.draft.coordinates"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('مختصات')"
            hide-details
          />
        </template>

        <!-- Note -->
        <template #item.note="{ item }">
          <v-text-field
            v-model="item.draft.note"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('یادداشت')"
            hide-details
          />
        </template>

        <!-- Resource code (autocomplete) + phone (readonly) -->
        <template #item.resource_code="{ item }">
          <v-autocomplete
            v-model="item.draft.resource_code"
            :items="resources"
            item-title="code_number"
            item-value="code_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کود منبع')"
            hide-details
            clearable
            @update:model-value="onResourceCodeChange(item)"
          />
        </template>
        <template #item.resource_phone="{ item }">
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
              class="bg-primary"
              variant="outlined"
              @click="save(item)"
              :loading="item.loading"
            >
              {{ t('ثبت دیتا') }}
            </v-btn>
            <v-btn class="bg-error" variant="outlined" @click="del(item)">
              {{ t('حذف') }}
            </v-btn>
          </div>
        </template>

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
      class="mb-20 mx-2"
    />
  </v-container>
</template>

<script setup>
  import { ref, onMounted, computed, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useSchoolRepository } from '@/stores/SchoolRepository';

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));
  const drawer = ref(false);

  const repo = useSchoolRepository();

  const rows = ref([]);
  const filters = ref({});
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  /* Resources dropdown -> code/phone behavior and also set resource_id */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* HEADERS (table) */
  const headers = computed(() => [
    { title: t('نام مکتب'), key: 'school_name' },
    { title: t('نوع مکتب'), key: 'school_type' },

    { title: t('نام مدیر'), key: 'principle_name' },
    { title: t('نام پدر مدیر'), key: 'principle_f_name' },
    { title: t('شماره مدیر'), key: 'principle_phone' },

    { title: t('معلمان مرد'), key: 'male_teachers_quantity' },
    { title: t('معلمان زن'), key: 'female_teachers_quantity' },
    { title: t('شاگردان پسر'), key: 'male_students_quantity' },
    { title: t('شاگردان دختر'), key: 'female_students_quantity' },

    { title: t('آدرس'), key: 'province' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  /* FILTER headers */
  const headersFT = computed(() => [
    { title: t('نام مکتب'), key: 'school_name' },
    { title: t('نوع مکتب'), key: 'school_type' },

    { title: t('نام مدیر'), key: 'principle_name' },
    { title: t('نام پدر مدیر'), key: 'principle_f_name' },
    { title: t('شماره مدیر'), key: 'principle_phone' },

    { title: t('معلمان مرد'), key: 'male_teachers_quantity' },
    { title: t('معلمان زن'), key: 'female_teachers_quantity' },
    { title: t('شاگردان پسر'), key: 'male_students_quantity' },
    { title: t('شاگردان دختر'), key: 'female_students_quantity' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی/ناحیه'), key: 'district' },
    { title: t('قریه/ناحیه کوچک'), key: 'village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  /* Top filter groups */
  const topGroups = computed(() => [
    { title: t('مشخصات مکتب'), keys: ['school_name', 'school_type'] },
    { title: t('مدیر'), keys: ['principle_name', 'principle_f_name', 'principle_phone'] },
    {
      title: t('کارمندان/شاگردان'),
      keys: [
        'male_teachers_quantity',
        'female_teachers_quantity',
        'male_students_quantity',
        'female_students_quantity',
      ],
    },
    { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village'] },
    { title: t('موقعیت/سرک/مختصات'), keys: ['specific_place', 'street_number', 'coordinates'] },
    { title: t('یادداشت'), keys: ['note'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
  ]);

  /* Map API -> row with draft */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      school_name: x.school_name ?? '',
      school_type: x.school_type ?? '',

      principle_name: x.principle_name ?? '',
      principle_f_name: x.principle_f_name ?? '',
      principle_phone: x.principle_phone ?? '',

      male_teachers_quantity: x.male_teachers_quantity ?? '',
      female_teachers_quantity: x.female_teachers_quantity ?? '',
      male_students_quantity: x.male_students_quantity ?? '',
      female_students_quantity: x.female_students_quantity ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      specific_place: x.specific_place ?? '',
      village: x.village ?? '',
      street_number: x.street_number ?? '',
      coordinates: x.coordinates ?? '',

      note: x.note ?? '',

      // resource display fields + id
      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base } };
  }

  /* Diff (only your provided fields + resource fields) */
  const EDITABLE_KEYS = [
    'school_name',
    'school_type',

    'principle_name',
    'principle_f_name',
    'principle_phone',

    'male_teachers_quantity',
    'female_teachers_quantity',
    'male_students_quantity',
    'female_students_quantity',

    'province',
    'zone',
    'district',
    'specific_place',
    'village',
    'street_number',
    'coordinates',

    'note',

    'resource_code',
    'resource_phone',
    'resource_id',
  ];
  const REQUIRED_ON_UPDATE = ['school_name']; // keep minimal required like your pattern

  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    for (const k of EDITABLE_KEYS) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    for (const k of REQUIRED_ON_UPDATE) {
      if (d[k] !== undefined) changed[k] = d[k];
    }
    return changed;
  }

  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  /* Hydrate + Fetch */
  function hydrate() {
    rows.value = (repo.Schools || []).map(toRowModel);
  }
  async function fetch() {
    await repo.FetchSchoolsData?.({
      page: repo.page || 1,
      itemsPerPage: repo.itemsPerPage || 50,
      search: repo.Search || '',
    });
    hydrate();
  }

  /* Add row */
  function addBlankRow() {
    const newRow = toRowModel({});
    rows.value = [...rows.value, newRow];
    const per = repo.itemsPerPage || 50;
    repo.page = Math.max(1, Math.ceil(rows.value.length / per));
  }

  /* Save / Delete */
  async function save(item) {
    item.loading = true;
    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateSchool?.(fd);
        if (created?.id) {
          const model = toRowModel(created);
          model.idOrTmp = item.idOrTmp;
          Object.assign(item, model);
          item.draft = { ...model };
        } else {
          await fetch();
        }
        return;
      }

      const changed = diffDraft(item);
      if (Object.keys(changed).length === 0) return;

      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateSchool?.(item.id, fd);
      if (updated?.id) {
        const model = toRowModel(updated);
        model.idOrTmp = item.idOrTmp;
        Object.assign(item, model);
        item.draft = { ...model };
      }
    } finally {
      item.loading = false;
    }
  }

  async function del(item) {
    if (!item.id) {
      rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp);
      return;
    }
    if (!confirm(t('آیا مطمئن هستید؟'))) return;
    await repo.DeleteSchool?.(item.id);
    await fetch();
  }

  /* init */
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

  /* layout like your template */
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

  .tablebody {
    display: flex !important;
    gap: 2px !important;
  }

  /* compact spacing */
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
