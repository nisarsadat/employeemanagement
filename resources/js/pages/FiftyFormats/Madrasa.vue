<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('مدرسه ')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید مدرسه')"
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
        <!-- Picture (preview + upload) -->
        <!-- <template #item.picture="{ item }">
          <div class="pic-cell">
            <v-avatar size="48" class="mb-1" v-if="pictureSrc(item)">
              <img :src="pictureSrc(item)" alt="pic" />
            </v-avatar>
            <v-file-input
              v-model="item.draft.picture"
              accept="image/*"
              density="compact"
              variant="outlined"
              :label="t('عکس')"
              hide-details
              prepend-inner-icon="mdi-image"
              class="pic-input"
              @change="onPictureChange(item)"
            />
          </div>
        </template> -->

        <!-- Basic info -->
        <template #item.name="{ item }">
          <v-text-field
            v-model="item.draft.name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام مدرسه')"
            hide-details
          />
        </template>
        <template #item.madrasa_type="{ item }">
          <v-text-field
            v-model="item.draft.madrasa_type"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نوع مدرسه')"
            hide-details
          />
        </template>

        <!-- Responsible -->
        <template #item.responsible_name="{ item }">
          <v-text-field
            v-model="item.draft.responsible_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('مسؤول: نام')"
            hide-details
          />
        </template>
        <template #item.responsible_f_name="{ item }">
          <v-text-field
            v-model="item.draft.responsible_f_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('مسؤول: نام پدر')"
            hide-details
          />
        </template>
        <template #item.responsible_position="{ item }">
          <v-text-field
            v-model="item.draft.responsible_position"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('مسؤول: موقف')"
            hide-details
          />
        </template>
        <template #item.responsible_phone="{ item }">
          <v-text-field
            v-model="item.draft.responsible_phone"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('مسؤول: شماره تماس')"
            hide-details
          />
        </template>
        <template #item.responsible_main_province="{ item }">
          <v-text-field
            v-model="item.draft.responsible_main_province"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('ولایت اصلی مسؤول')"
            hide-details
          />
        </template>
        <template #item.tarafdari_type_responsible="{ item }">
          <v-text-field
            v-model="item.draft.tarafdari_type_responsible"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نوع طرفداری مسؤول')"
            hide-details
          />
        </template>

        <!-- Religious & counts -->
        <template #item.mazhab="{ item }">
          <v-text-field
            v-model="item.draft.mazhab"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('مذهب')"
            hide-details
          />
        </template>
        <template #item.male_teacher_quantity="{ item }">
          <v-text-field
            v-model="item.draft.male_teacher_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تعداد استادان (ذکور)')"
            hide-details
          />
        </template>
        <template #item.female_teacher_quantity="{ item }">
          <v-text-field
            v-model="item.draft.female_teacher_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تعداد استادان (اناث)')"
            hide-details
          />
        </template>
        <template #item.male_students_quantity="{ item }">
          <v-text-field
            v-model="item.draft.male_students_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تعداد شاگردان (ذکور)')"
            hide-details
          />
        </template>
        <template #item.female_students_quantity="{ item }">
          <v-text-field
            v-model="item.draft.female_students_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تعداد شاگردان (اناث)')"
            hide-details
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
              :label="t('ولسوالی')"
            />
            <v-text-field
              v-model="item.draft.village"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('قریه/ناحیه')"
            />
          </div>
        </template>

        <!-- Location details -->
        <template #item.specific_place="{ item }">
          <v-text-field
            v-model="item.draft.specific_place"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('موقعیت مشخص')"
            hide-details
          />
        </template>
        <template #item.street_number="{ item }">
          <v-text-field
            v-model="item.draft.street_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره سرک')"
            hide-details
          />
        </template>
        <template #item.coordinates="{ item }">
          <v-text-field
            v-model="item.draft.coordinates"
            class="text-group"
            density="compact"
            variant="outlined"
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
  import { useBadmashanoRepository } from '@/stores/BadmashanoRepository';

  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));
  const drawer = ref(false);

  const repo = useBadmashanoRepository();

  const rows = ref([]);
  const filters = ref({});
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  /* Resources dropdown -> code -> phone + id */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* Picture helpers */
  function pictureSrc(item) {
    const p = item?.draft?.picture ?? item?.picture;
    if (!p) return '';
    if (typeof p === 'string') return p; // API URL
    if (p instanceof File) return URL.createObjectURL(p);
    return '';
  }
  function onPictureChange(item) {
    // FormData handles File
  }

  /* HEADERS (table) */
  const headers = computed(() => [
    // { title: t('عکس'), key: 'picture' },

    { title: t('نام مدرسه'), key: 'name' },
    { title: t('نوع مدرسه'), key: 'madrasa_type' },

    { title: t('مسؤول: نام'), key: 'responsible_name' },
    { title: t('مسؤول: نام پدر'), key: 'responsible_f_name' },
    { title: t('مسؤول: موقف'), key: 'responsible_position' },
    { title: t('مسؤول: شماره تماس'), key: 'responsible_phone' },
    { title: t('ولایت اصلی مسؤول'), key: 'responsible_main_province' },
    { title: t('نوع طرفداری مسؤول'), key: 'tarafdari_type_responsible' },

    { title: t('مذهب'), key: 'mazhab' },
    { title: t('استادان (ذکور)'), key: 'male_teacher_quantity' },
    { title: t('استادان (اناث)'), key: 'female_teacher_quantity' },
    { title: t('شاگردان (ذکور)'), key: 'male_students_quantity' },
    { title: t('شاگردان (اناث)'), key: 'female_students_quantity' },

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
    { title: t('نام مدرسه'), key: 'name' },
    { title: t('نوع مدرسه'), key: 'madrasa_type' },

    { title: t('مسؤول: نام'), key: 'responsible_name' },
    { title: t('مسؤول: نام پدر'), key: 'responsible_f_name' },
    { title: t('مسؤول: موقف'), key: 'responsible_position' },
    { title: t('مسؤول: شماره تماس'), key: 'responsible_phone' },
    { title: t('ولایت اصلی مسؤول'), key: 'responsible_main_province' },
    { title: t('نوع طرفداری مسؤول'), key: 'tarafdari_type_responsible' },

    { title: t('مذهب'), key: 'mazhab' },
    { title: t('استادان (ذکور)'), key: 'male_teacher_quantity' },
    { title: t('استادان (اناث)'), key: 'female_teacher_quantity' },
    { title: t('شاگردان (ذکور)'), key: 'male_students_quantity' },
    { title: t('شاگردان (اناث)'), key: 'female_students_quantity' },

    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه/ناحیه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('شماره سرک'), key: 'street_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  /* Top filter groups */
  const topGroups = computed(() => [
    { title: t('مدرسه'), keys: ['name', 'madrasa_type'] },
    {
      title: t('مسؤول مدرسه'),
      keys: [
        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'responsible_main_province',
        'tarafdari_type_responsible',
      ],
    },
    {
      title: t('دینی و احصائیه'),
      keys: [
        'mazhab',
        'male_teacher_quantity',
        'female_teacher_quantity',
        'male_students_quantity',
        'female_students_quantity',
      ],
    },
    { title: t('آدرس'), keys: ['province', 'zone', 'district', 'village'] },
    { title: t('موقعیت/سرک/مختصات'), keys: ['specific_place', 'street_number', 'coordinates'] },
    { title: t('یادداشت/منبع'), keys: ['note', 'resource_code', 'resource_phone'] },
  ]);

  /* Map API -> row with draft */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      picture: x.picture ?? null,

      name: x.name ?? '',
      madrasa_type: x.madrasa_type ?? '',

      responsible_name: x.responsible_name ?? '',
      responsible_f_name: x.responsible_f_name ?? '',
      responsible_position: x.responsible_position ?? '',
      responsible_phone: x.responsible_phone ?? '',
      responsible_main_province: x.responsible_main_province ?? '',
      tarafdari_type_responsible: x.tarafdari_type_responsible ?? '',

      mazhab: x.mazhab ?? '',
      male_teacher_quantity: x.male_teacher_quantity ?? '',
      female_teacher_quantity: x.female_teacher_quantity ?? '',
      male_students_quantity: x.male_students_quantity ?? '',
      female_students_quantity: x.female_students_quantity ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',
      specific_place: x.specific_place ?? '',
      street_number: x.street_number ?? '',
      coordinates: x.coordinates ?? '',

      note: x.note ?? '',

      // resource fields
      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base } };
  }

  /* Diff -> only edited keys + always requireds if any */
  const EDITABLE_KEYS = [
    'picture',

    'name',
    'madrasa_type',

    'responsible_name',
    'responsible_f_name',
    'responsible_position',
    'responsible_phone',
    'responsible_main_province',
    'tarafdari_type_responsible',

    'mazhab',
    'male_teacher_quantity',
    'female_teacher_quantity',
    'male_students_quantity',
    'female_students_quantity',

    'province',
    'zone',
    'district',
    'village',
    'specific_place',
    'street_number',
    'coordinates',

    'note',

    'resource_code',
    'resource_phone',
    'resource_id',
  ];
  const REQUIRED_ON_UPDATE = ['name'];

  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    for (const k of EDITABLE_KEYS) {
      const prev = item[k];
      const next = d[k];

      if (next instanceof File) {
        if (!(prev instanceof File)) changed[k] = next;
        continue;
      }
      if ((next ?? '') !== (prev ?? '')) changed[k] = next;
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
    rows.value = (repo.Madrasas || []).map(toRowModel);
  }
  async function fetch() {
    await repo.FetchMadrasasData?.({
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
        const created = await repo.CreateMadrasa?.(fd);
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
      const updated = await repo.UpdateMadrasa?.(item.id, fd);
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
    await repo.DeleteMadrasa?.(item.id);
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

  /* layout like Fasad */
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

  /* compact spacing like Fasad */
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

  /* picture cell */
  .pic-cell {
    display: grid;
    gap: 4px;
    width: 220px;
  }
  .pic-input :deep(.v-input__control) {
    min-width: 200px;
  }
</style>
