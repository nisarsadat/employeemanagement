<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('دکاکین مشکوک')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید دکاکین مشکوک')"
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

        <!-- SHOP DETAILS -->
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
        <template #item.market_name="{ item }">
          <v-text-field
            v-model="item.draft.market_name"
            :label="t('نام بازار')"
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

        <!-- PERSON INFO -->
        <template #item.name="{ item }">
          <v-text-field
            v-model="item.draft.name"
            :label="t('نام مالک')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.father_name="{ item }">
          <v-text-field
            v-model="item.draft.father_name"
            :label="t('نام پدر')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.main_province="{ item }">
          <v-text-field
            v-model="item.draft.main_province"
            :label="t('ولایت اصلی')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.phone_numbers="{ item }">
          <v-text-field
            v-model="item.draft.phone_numbers"
            :label="t('شماره تماس')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>

        <!-- SHOP TYPE -->
        <template #item.shop_type="{ item }">
          <v-text-field
            v-model="item.draft.shop_type"
            :label="t('نوع دکان')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
          />
        </template>
        <template #item.mashkok_type="{ item }">
          <v-text-field
            v-model="item.draft.mashkok_type"
            :label="t('نوع شک')"
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

        <!-- RESOURCE -->
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
            :label="t(' منبع')"
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

  const resources = computed(() => repo.resourcecode || []);
  function onResourceSelect(item) {
    const found = resources.value.find((r) => r.id === item.draft.resource_id);
    item.draft.resource_phone = found?.phone_number ?? '';
  }

  const headers = computed(() => [
    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه/ناحیه'), key: 'village' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('نام بازار'), key: 'market_name' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('نام مالک'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('شماره تماس'), key: 'phone_numbers' },

    { title: t('نوع دکان'), key: 'shop_type' },
    { title: t('نوع شک'), key: 'mashkok_type' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('منبع'), key: 'resource_id' },
    { title: t(' منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  const headersFT = headers;
  const topGroups = computed(() => [
    { title: t('موقعیت'), keys: ['province', 'zone', 'district', 'village'] },
    { title: t('بازار'), keys: ['specific_place', 'market_name', 'coordinates'] },
    { title: t('مالک'), keys: ['name', 'father_name', 'main_province', 'phone_numbers'] },
    { title: t('مشخصات دکان'), keys: ['shop_type', 'mashkok_type'] },
    { title: t('منبع/یادداشت'), keys: ['note', 'resource_id', 'resource_phone'] },
  ]);

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
      market_name: x.market_name ?? '',
      coordinates: x.coordinates ?? '',

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      main_province: x.main_province ?? '',
      phone_numbers: x.phone_numbers ?? '',

      shop_type: x.shop_type ?? '',
      mashkok_type: x.mashkok_type ?? '',

      note: x.note ?? '',


      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base } };
  }

  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'province',
      'zone',
      'district',
      'village',
      'specific_place',
      'market_name',
      'coordinates',
      'name',
      'father_name',
      'main_province',
      'phone_numbers',
      'shop_type',
      'mashkok_type',
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

  function hydrate() {
    rows.value = (repo.MashkokShops || []).map(toRowModel);
  }
  async function fetch() {
    await repo.FetchMashkokShopsData?.({
      page: repo.page || 1,
      itemsPerPage: repo.itemsPerPage || 50,
      search: repo.Search || '',
    });
    hydrate();
  }

  function addBlankRow() {
    const newRow = toRowModel({});
    rows.value = [...rows.value, newRow];
  }

  async function save(item) {
    item.loading = true;
    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateMashkokShop?.(fd);
        if (created?.id) Object.assign(item, toRowModel(created));
        else await fetch();
        return;
      }
      const changed = diffDraft(item);
      if (Object.keys(changed).length === 0) return;
      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateMashkokShop?.(item.id, fd);
      if (updated?.id) Object.assign(item, toRowModel(updated));
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
    await repo.DeleteMashkokShop?.(item.id);
    await fetch();
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
  .gap-1 {
    gap: 4px;
  }
  .tablebody {
    display: flex !important;
    overflow-x: auto;
  }
  :deep(.v-field-label),
  :deep(.v-label),
  :deep(input::placeholder) {
    font-size: 12px !important;
    color: #555 !important;
  }
  :deep(.v-field__input) {
    min-height: 26px !important;
    padding: 2px 6px !important;
    font-size: 12px !important;
  }
</style>
