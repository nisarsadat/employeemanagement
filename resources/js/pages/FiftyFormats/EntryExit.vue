<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('ورود/خروج')"
      :subtitle="t('اضافه کردن یک ریکارډ ورود/خروج')"
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
          {{ t('اضافه کردن ریکارډ ورود/خروج') }}
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
        <!-- Columns -->
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

        <template #item.entry_way="{ item }">
          <v-text-field
            v-model="item.draft.entry_way"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('طریق ورود/خروج')"
            hide-details
          />
        </template>
        <template #item.way_type="{ item }">
          <v-text-field
            v-model="item.draft.way_type"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نوعه طریق')"
            hide-details
          />
        </template>

        <template #item.province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.province"
              density="compact"
              variant="outlined"
              :label="t('ولایت')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.zone"
              density="compact"
              variant="outlined"
              :label="t('زون')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.district"
              density="compact"
              variant="outlined"
              :label="t('ولسوالی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.village"
              density="compact"
              variant="outlined"
              :label="t('قریه/ناحیه')"
              hide-details
            />
          </div>
        </template>

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
        <template #item.cordinate="{ item }">
          <v-text-field
            v-model="item.draft.cordinate"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('مختصات')"
            hide-details
          />
        </template>

        <template #item.amnayaty_poste="{ item }">
          <v-text-field
            v-model="item.draft.amnayaty_poste"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('پُسته امنیتی')"
            hide-details
          />
        </template>
        <template #item.amnayaty_ishala="{ item }">
          <v-text-field
            v-model="item.draft.amnayaty_ishala"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('اشاره امنیتی')"
            hide-details
          />
        </template>

        <template #item.where_went="{ item }">
          <v-text-field
            v-model="item.draft.where_went"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('به کجا رفت')"
            hide-details
          />
        </template>
        <template #item.where_come="{ item }">
          <v-text-field
            v-model="item.draft.where_come"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('از کجا آمد')"
            hide-details
          />
        </template>

        <!-- Resource (same as Captive/Mulkano) -->
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

        <template #item.notes="{ item }">
          <v-text-field
            v-model="item.draft.notes"
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
            {{ t('ریکارډ ورود/خروج یافت نشد، لطفا اضافه کنید') }}
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

  /* Resource dropdown (same as other pages) */
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
    { title: t('طریق ورود/خروج'), key: 'entry_way' },
    { title: t('نوعه طریق'), key: 'way_type' },
    { title: t('موقعیت'), key: 'province' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('مختصات'), key: 'cordinate' },
    { title: t('پُسته امنیتی'), key: 'amnayaty_poste' },
    { title: t('اشاره امنیتی'), key: 'amnayaty_ishala' },
    { title: t('به کجا رفت'), key: 'where_went' },
    { title: t('از کجا آمد'), key: 'where_come' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    { title: t('ملاحظات'), key: 'notes' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('طریق ورود/خروج'), key: 'entry_way' },
    { title: t('نوعه طریق'), key: 'way_type' },
    { title: t('ولایت'), key: 'province' },
    { title: t('زون'), key: 'zone' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('قریه/ناحیه'), key: 'village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('مختصات'), key: 'cordinate' },
    { title: t('پُسته امنیتی'), key: 'amnayaty_poste' },
    { title: t('اشاره امنیتی'), key: 'amnayaty_ishala' },
    { title: t('به کجا رفت'), key: 'where_went' },
    { title: t('از کجا آمد'), key: 'where_come' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    { title: t('ملاحظات'), key: 'notes' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'entry_way', 'way_type'] },
    {
      title: t('موقعیت'),
      keys: ['province', 'zone', 'district', 'village', 'specific_place', 'cordinate'],
    },
    { title: t('امنیتی'), keys: ['amnayaty_poste', 'amnayaty_ishala'] },
    { title: t('رفت و آمد'), keys: ['where_went', 'where_come'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['notes'] },
  ]);

  /* Model */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),

      loading: false,

      name: x.name ?? '',
      entry_way: x.entry_way ?? '',
      way_type: x.way_type ?? '',

      province: x.province ?? '',
      zone: x.zone ?? '',
      district: x.district ?? '',
      village: x.village ?? '',

      specific_place: x.specific_place ?? '',
      cordinate: x.cordinate ?? '',

      amnayaty_poste: x.amnayaty_poste ?? '',
      amnayaty_ishala: x.amnayaty_ishala ?? '',

      where_went: x.where_went ?? '',
      where_come: x.where_come ?? '',

      // Resource flatten
      resource_code: x.resource_code ?? x.resource?.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? x.resource?.phone_number ?? '',
      resource_id: x.resource_id ?? x.resource?.id ?? null,

      notes: x.notes ?? '',
    };
    return { ...base, draft: { ...base } };
  }

  function hydrate() {
    rows.value = (repo.EntryExits || []).map(toRowModel);
  }

  async function fetch() {
    await Promise.allSettled([
      repo.GetResourceCode?.(),
      repo.FetchEntryExitsData?.({ page: repo.page || 1, itemsPerPage: repo.itemsPerPage || 50 }),
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
      'entry_way',
      'way_type',
      'province',
      'zone',
      'district',
      'village',
      'specific_place',
      'cordinate',
      'amnayaty_poste',
      'amnayaty_ishala',
      'where_went',
      'where_come',
      'resource_code',
      'resource_phone_number',
      'resource_id',
      'notes',
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

  /* Save / Delete (Fasad repo only) */
  async function save(item) {
    if (!item.draft?.name?.trim()) return;

    item.loading = true;
    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateEntryExit?.(fd);
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
      const updated = await repo.UpdateEntryExit?.(item.id, fd);
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
      await repo.DeleteEntryExit?.(item.id);
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
