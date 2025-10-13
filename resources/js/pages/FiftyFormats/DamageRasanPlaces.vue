<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('نقاط آسیب رسان')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید')"
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
        <!-- LOCATION -->
        <template #item.province="{ item }">
          <v-text-field
            v-model="item.draft.province"
            :label="t('ولایت')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.zone="{ item }">
          <v-text-field
            v-model="item.draft.zone"
            :label="t('زون')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.district="{ item }">
          <v-text-field
            v-model="item.draft.district"
            :label="t('ولسوالی')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.village="{ item }">
          <v-text-field
            v-model="item.draft.village"
            :label="t('قریه/ناحیه')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>

        <!-- DETAILS -->
        <template #item.specific_place="{ item }">
          <v-text-field
            v-model="item.draft.specific_place"
            :label="t('موقعیت مشخص')"
            class="text-group-note"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.specific_dot="{ item }">
          <v-text-field
            v-model="item.draft.specific_dot"
            :label="t('نقطه مشخص')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.coordinates="{ item }">
          <v-text-field
            v-model="item.draft.coordinates"
            :label="t('مختصات')"
            hint="34.5553, 69.2075"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
            persistent-hint
          />
        </template>

        <!-- THREAT -->
        <template #item.tahdid_type="{ item }">
          <v-text-field
            v-model="item.draft.tahdid_type"
            :label="t('نوع تهدید')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.percentage="{ item }">
          <v-text-field
            v-model="item.draft.percentage"
            :label="t('درصدی')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.damage_place="{ item }">
          <v-text-field
            v-model="item.draft.damage_place"
            :label="t('محل آسیب')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>

        <!-- NOTE -->
        <template #item.note="{ item }">
          <v-text-field
            v-model="item.draft.note"
            :label="t('یادداشت')"
            class="text-group-note"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>

        <!-- RESOURCE (select by id; show name if present) -->
        <template #item.resource_id="{ item }">
          <v-autocomplete
            v-model="item.draft.resource_id"
            :items="resources"
            item-title="code_number"
            item-value="id"
            :label="t('منبع')"
            clearable
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
            @update:model-value="onResourceSelect(item)"
          />
        </template>

        <template #item.resource_phone="{ item }">
          <v-text-field
            v-model="item.draft.resource_phone"
            :label="t('نام منبع')"
            class="text-group"
            density="compact"
            variant="outlined"
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

        <!-- NO DATA -->
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

  /* Resource list from repository (assumed same as earlier) */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceSelect(item) {
    const found = resources.value.find((r) => r.id === item.draft.resource_id);
    // show name in table (not persisted by API)
    item.draft.resource_phone =  found?.phone_number ?? '';
  }

  /* HEADERS for DamageRasanPlaces */
  const headers = computed(() => [
    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه/ناحیه'), key: 'village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('نقطه مشخص'), key: 'specific_dot' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('نوع تهدید'), key: 'tahdid_type' },
    { title: t('درصدی'), key: 'percentage' },
    { title: t('محل آسیب'), key: 'damage_place' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('منبع'), key: 'resource_id' },
    { title: t('نام منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  /* FILTER headers = same keys */
  const headersFT = headers;

  /* Top filter groups */
  const topGroups = computed(() => [
    { title: t('موقعیت'), keys: ['province', 'zone', 'district', 'village'] },
    { title: t('جزییات'), keys: ['specific_place', 'specific_dot', 'coordinates'] },
    { title: t('تهدید/آسیب'), keys: ['tahdid_type', 'percentage', 'damage_place'] },
    { title: t('منبع/یادداشت'), keys: ['note', 'resource_id', 'resource_phone'] },
  ]);

  /* Map API -> row with draft */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',

      specific_place: x.specific_place ?? '',
      specific_dot: x.specific_dot ?? '',
      coordinates: x.coordinates ?? '',

      tahdid_type: x.tahdid_type ?? '',
      percentage: x.percentage ?? '',
      damage_place: x.damage_place ?? '',

      note: x.note ?? '',


      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base } };
  }

  /* Diff + FormData (only changed; exclude display-only fields) */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'province',
      'zone',
      'district',
      'village',
      'specific_place',
      'specific_dot',
      'coordinates',
      'tahdid_type',
      'percentage',
      'damage_place',
      'note',
      'resource_id',
    ];
    for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    return changed;
  }

  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null || v === '') return;
      fd.append(k, v);
    });
    return fd;
  }

  /* Hydrate + Fetch (use MAWAD repo functions analogs) */
  function hydrate() {
    rows.value = (repo.DamageRasanPlaces || []).map(toRowModel);
  }
  async function fetch() {
    await repo.FetchDamageRasanPlacesData?.({
      page: repo.page || 1,
      itemsPerPage: repo.itemsPerPage || 50,
      search: repo.Search || '',
    });
    hydrate();
  }

  /* Add blank row */
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
        const created = await repo.CreateDamageRasanPlaces?.(fd);
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
      const updated = await repo.UpdateDamageRasanPlaces?.(item.id, fd);
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
    await repo.DeleteDamageRasanPlaces?.(item.id);
    await fetch();
  }

  /* init */
  onMounted(async () => {
    await Promise.allSettled([repo.GetResourceCode?.()]); // if you need resources dropdown
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
    overflow-x: auto;
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
  .text-group,
  .text-group-note {
    margin: -16px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
  }
</style>
