<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('بدماشانو / Badmashano')"
      :subtitle="t('اضافه کردن یک ریکارډ جدید بدماشانو')"
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

        <!-- Simple fields -->
        <template #item.nickname="{ item }">
          <v-text-field
            v-model="item.draft.nickname"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تخلص')"
            hide-details
          />
        </template>
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

        <!-- Main address (grouped) -->
        <template #item.main_province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.main_province"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولایت اصلی')"
            />
            <v-text-field
              v-model="item.draft.main_district"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولسوالی اصلی')"
            />
            <v-text-field
              v-model="item.draft.main_village"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('قریه/ناحیه اصلی')"
            />
          </div>
        </template>

        <!-- Now address (grouped) -->
        <template #item.now_province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.now_province"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولایت فعلی')"
            />
            <v-text-field
              v-model="item.draft.now_zone"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('زون فعلی')"
            />
            <v-text-field
              v-model="item.draft.now_district"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('ولسوالی فعلی')"
            />
            <v-text-field
              v-model="item.draft.now_village"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('قریه/ناحیه فعلی')"
            />
          </div>
        </template>

        <!-- Current place details -->
        <template #item.now_specific_place="{ item }">
          <v-text-field
            v-model="item.draft.now_specific_place"
            density="compact"
            variant="outlined"
            class="text-group-note"
            :label="t('موقعیت مشخص فعلی')"
            hide-details
          />
        </template>
        <template #item.now_street_number="{ item }">
          <v-text-field
            v-model="item.draft.now_street_number"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('شماره سرک فعلی')"
            hide-details
          />
        </template>
        <template #item.now_home_number="{ item }">
          <v-text-field
            v-model="item.draft.now_home_number"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('شماره خانه فعلی')"
            hide-details
          />
        </template>

        <!-- Job/contact/custom -->
        <template #item.old_job="{ item }">
          <v-text-field
            v-model="item.draft.old_job"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('وظیفه قبلی')"
            hide-details
          />
        </template>
        <template #item.job="{ item }">
          <v-text-field
            v-model="item.draft.job"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('وظیفه فعلی')"
            hide-details
          />
        </template>
        <template #item.friends_quantity="{ item }">
          <v-text-field
            v-model="item.draft.friends_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تعداد دوستان')"
            hide-details
          />
        </template>
        <template #item.supplier="{ item }">
          <v-text-field
            v-model="item.draft.supplier"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تمویل کننده')"
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

        <!-- History / note / jorm -->
        <template #item.history_jorm="{ item }">
          <v-text-field
            v-model="item.draft.history_jorm"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('سابقه جرم')"
            hide-details
          />
        </template>
        <template #item.jorm_type="{ item }">
          <v-text-field
            v-model="item.draft.jorm_type"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نوع جرم')"
            hide-details
          />
        </template>
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

  /* Resources dropdown -> keep code/phone behavior and also set resource_id */
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
    if (typeof p === 'string') return p; // already a URL from API
    if (p instanceof File) return URL.createObjectURL(p);
    return '';
  }
  function onPictureChange(item) {
    // keep as-is; FormData will pick up File
  }

  /* HEADERS (table) */
  const headers = computed(() => [
    // { title: t('عکس'), key: 'picture' },

    { title: t('تخلص'), key: 'nickname' },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },

    { title: t('موقعیت فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک'), key: 'now_street_number' },
    { title: t('شماره خانه'), key: 'now_home_number' },

    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'job' },
    { title: t('تعداد دوستان'), key: 'friends_quantity' },
    { title: t('تمویل کننده'), key: 'supplier' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('سابقه جرم'), key: 'history_jorm' },
    { title: t('نوع جرم'), key: 'jorm_type' },
    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  /* FILTER headers (Fasad-style) */
  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('تخلص'), key: 'nickname' },

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
    { title: t('تعداد دوستان'), key: 'friends_quantity' },
    { title: t('تمویل کننده'), key: 'supplier' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('سابقه جرم'), key: 'history_jorm' },
    { title: t('نوع جرم'), key: 'jorm_type' },
    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  /* Top filter groups */
  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'nickname', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    { title: t('موقعیت'), keys: ['now_specific_place', 'now_street_number', 'now_home_number'] },
    { title: t('وظیفه/تماس'), keys: ['old_job', 'job', 'phone_number'] },
    {
      title: t('سایر'),
      keys: ['friends_quantity', 'supplier', 'history_jorm', 'jorm_type', 'note'],
    },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
  ]);

  /* Map API -> row with draft */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      // picture can be URL (string) from API or File when editing
      picture: x.picture ?? null,

      nickname: x.nickname ?? '',
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

      old_job: x.old_job ?? '',
      job: x.job ?? '',
      friends_quantity: x.friends_quantity ?? '',
      supplier: x.supplier ?? '',
      phone_number: x.phone_number ?? '',

      history_jorm: x.history_jorm ?? '',
      jorm_type: x.jorm_type ?? '',
      note: x.note ?? '',

      // resource fields (code/phone persisted per API)
      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base } };
  }

  /* Diff -> include only edited keys + always requireds */
  const EDITABLE_KEYS = [
    'picture',
    'nickname',
    'name',
    'father_name',

    'main_province',
    'main_district',
    'main_village',

    'now_province',
    'now_zone',
    'now_district',
    'now_village',
    'now_specific_place',
    'now_street_number',
    'now_home_number',

    'old_job',
    'job',
    'friends_quantity',
    'supplier',
    'phone_number',

    'history_jorm',
    'jorm_type',
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

      // Files need special compare to avoid object URL differences
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
    rows.value = (repo.Badmashanos || []).map(toRowModel);
  }
  async function fetch() {
    await repo.FetchBadmashanosData?.({
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

  /* Save / Delete (Fasad-style flow) */
  async function save(item) {
    item.loading = true;
    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateBadmashano?.(fd);
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
      const updated = await repo.UpdateBadmashano?.(item.id, fd);
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
    await repo.DeleteBadmashano?.(item.id);
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
