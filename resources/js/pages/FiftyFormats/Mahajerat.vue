<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('مهاجرت')"
      :subtitle="t('اضافه کردن یک ریکارډ مهاجرت')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

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
          {{ t('اضافه کردن ریکارډ مهاجرت') }}
        </v-btn>
      </v-col>
    </v-row>

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
        <!-- Basics -->
        <template #item.name="{ item }">
          <v-text-field
            v-model="item.draft.name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام')"
            hide-details
          />
        </template>
        <template #item.father_name="{ item }">
          <v-text-field
            v-model="item.draft.father_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام پدر')"
            hide-details
          />
        </template>
        <template #item.scientific_level="{ item }">
          <v-text-field
            v-model="item.draft.scientific_level"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('سطح علمی')"
            hide-details
          />
        </template>

        <!-- Main address -->
        <template #item.main_province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.main_province"
              density="compact"
              variant="outlined"
              :label="t('ولایت اصلی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.main_district"
              density="compact"
              variant="outlined"
              :label="t('ولسوالی اصلی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.main_village"
              density="compact"
              variant="outlined"
              :label="t('قریه/ناحیه اصلی')"
              hide-details
            />
          </div>
        </template>

        <!-- Now address -->
        <template #item.now_province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.now_province"
              density="compact"
              variant="outlined"
              :label="t('ولایت فعلی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.now_zone"
              density="compact"
              variant="outlined"
              :label="t('زون فعلی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.now_district"
              density="compact"
              variant="outlined"
              :label="t('ولسوالی فعلی')"
              hide-details
            />
            <!-- NOTE: field name matches your backend key now_vinage -->
            <v-text-field
              v-model="item.draft.now_vinage"
              density="compact"
              variant="outlined"
              :label="t('قریه/ناحیه فعلی')"
              hide-details
            />
          </div>
        </template>

        <!-- Address details -->
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
            :label="t('سرک نمبر')"
            hide-details
          />
        </template>
        <template #item.home_number="{ item }">
          <v-text-field
            v-model="item.draft.home_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نمبر خانه')"
            hide-details
          />
        </template>
        <template #item.cordinates="{ item }">
          <v-text-field
            v-model="item.draft.cordinates"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('مختصات')"
            hide-details
          />
        </template>

        <!-- Migration target / counts / contact -->
        <template #item.country="{ item }">
          <v-text-field
            v-model="item.draft.country"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کشور')"
            hide-details
          />
        </template>
        <template #item.male_amount="{ item }">
          <v-text-field
            v-model="item.draft.male_amount"
            type="number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تعداد مرد')"
            hide-details
          />
        </template>
        <template #item.female_amount="{ item }">
          <v-text-field
            v-model="item.draft.female_amount"
            type="number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تعداد زن')"
            hide-details
          />
        </template>
        <template #item.phone_number="{ item }">
          <v-text-field
            v-model="item.draft.phone_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره تماس')"
            hide-details
          />
        </template>

        <!-- Resource -->
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
        <template #item.resource_phone_number="{ item }">
          <v-text-field
            v-model="item.draft.resource_phone_number"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('شماره منبع')"
            hide-details
            readonly
          />
        </template>

        <!-- Note -->
        <template #item.note="{ item }">
          <v-text-field
            v-model="item.draft.note"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('ملاحظات')"
            hide-details
          />
        </template>

        <!-- Actions -->
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
              {{ t('حذف دیتا') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">
            {{ t('ریکارډ مهاجرت یافت نشد، لطفا اضافه کنید') }}
          </div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>

  <!-- FilterableTable -->
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
  import { useFasadRepository } from '@/stores/FasadRepository';

  const { t, locale } = useI18n();
  const drawer = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const repo = useFasadRepository();

  const rows = ref([]);
  const filters = ref({});
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  /* Resources dropdown */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone_number = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* Headers */
  const headers = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('سطح علمی'), key: 'scientific_level' },

    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('مختصات'), key: 'cordinates' },

    { title: t('کشور'), key: 'country' },
    { title: t('مرد'), key: 'male_amount' },
    { title: t('زن'), key: 'female_amount' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },

    { title: t('ملاحظات'), key: 'note' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('سطح علمی'), key: 'scientific_level' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_vinage' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('مختصات'), key: 'cordinates' },

    { title: t('کشور'), key: 'country' },
    { title: t('مرد'), key: 'male_amount' },
    { title: t('زن'), key: 'female_amount' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    { title: t('ملاحظات'), key: 'note' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'father_name', 'scientific_level'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_vinage'] },
    {
      title: t('منزل/موقعیت'),
      keys: ['specific_place', 'street_number', 'home_number', 'cordinates'],
    },
    {
      title: t('مقصد/تعداد/تماس'),
      keys: ['country', 'male_amount', 'female_amount', 'phone_number'],
    },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['note'] },
  ]);

  /* Model */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      scientific_level: x.scientific_level ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_district: x.now_district ?? '',
      now_vinage: x.now_vinage ?? '',

      specific_place: x.specific_place ?? '',
      street_number: x.street_number ?? '',
      home_number: x.home_number ?? '',
      cordinates: x.cordinates ?? '',

      country: x.country ?? '',
      male_amount: x.male_amount ?? '',
      female_amount: x.female_amount ?? '',
      phone_number: x.phone_number ?? '',

      // Resource flatten
      resource_code: x.resource_code ?? x.resource?.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? x.resource?.phone_number ?? '',
      resource_id: x.resource_id ?? x.resource?.id ?? null,

      note: x.note ?? '',
    };
    return { ...base, draft: { ...base } };
  }

  function hydrate() {
    rows.value = (repo.Mahajerats || []).map(toRowModel);
  }

  async function fetch() {
    await Promise.allSettled([
      repo.GetResourceCode?.(),
      repo.FetchMahajeratsData?.({ page: repo.page || 1, itemsPerPage: repo.itemsPerPage || 50 }),
    ]);
    hydrate();
  }

  function addBlankRow() {
    const newRow = toRowModel({});
    rows.value = [...rows.value, newRow];
    const per = repo.itemsPerPage || 50;
    const totalLocal = rows.value.length;
    repo.page = Math.max(1, Math.ceil(totalLocal / per));
  }

  /* diff — send only changed fields */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'name',
      'father_name',
      'scientific_level',
      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_zone',
      'now_district',
      'now_vinage',
      'specific_place',
      'street_number',
      'home_number',
      'cordinates',
      'country',
      'male_amount',
      'female_amount',
      'phone_number',
      'resource_code',
      'resource_phone_number',
      'resource_id',
      'note',
    ];
    for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    return { changed };
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  /* Save / Delete using Fasad repo */
  async function save(item) {
    if (!item.draft?.name?.trim()) return;

    item.loading = true;
    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateMahajerat?.(fd);
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

      const { changed } = diffDraft(item);
      if (Object.keys(changed).length === 0) return;

      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateMahajerat?.(item.id, fd);
      if (updated?.id) {
        const model = toRowModel(updated);
        model.idOrTmp = item.idOrTmp;
        Object.assign(item, model);
        item.draft = { ...model };
      }
    } catch (e) {
      console.error('Save error:', e);
    } finally {
      item.loading = false;
    }
  }

  async function del(item) {
    if (!item.id) {
      rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp);
      return;
    }
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name }))) return;

    try {
      await repo.DeleteMahajerat?.(item.id);
      await fetch();
    } catch (e) {
      console.error('Delete error:', e);
    }
  }

  onMounted(fetch);
  repo.GetResourceCode?.();
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
</style>
