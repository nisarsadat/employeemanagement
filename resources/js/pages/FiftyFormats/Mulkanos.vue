<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('ملکانُو')"
      :subtitle="t('اضافه کردن یک ریکارډ مُلکانو')"
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
          {{ t('اضافه کردن ریکارډ مُلکانو') }}
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
        <!-- Picture -->
        <template #item.picture="{ item }">
          <div class="pic-wrap">
            <input
              class="hidden-file"
              type="file"
              accept="image/*"
              :id="`up-${item.idOrTmp}`"
              @change="(e) => onPickFile(item, e.target.files?.[0] ?? null)"
            />
            <v-btn
              v-if="!item.previewUrl && !item.picture"
              size="small"
              color="primary"
              variant="outlined"
              icon
              @click="triggerFile(item)"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>

            <div v-else class="pic-thumb">
              <v-avatar size="32">
                <v-img :src="item.previewUrl || item.picture" :alt="t('عکس')" cover />
              </v-avatar>

              <div class="pic-overlay">
                <v-btn
                  size="x-small"
                  color="primary"
                  variant="flat"
                  @click.stop="triggerFile(item)"
                >
                  <v-icon start>mdi-pencil</v-icon>{{ t('ویرایش') }}
                </v-btn>
                <v-btn
                  size="x-small"
                  color="secondary"
                  variant="flat"
                  @click.stop="openPreview(item.previewUrl || item.picture)"
                >
                  <v-icon start>mdi-eye</v-icon>{{ t('نمایش') }}
                </v-btn>
              </div>
            </div>
          </div>
        </template>

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
        <template #item.grand_father_name="{ item }">
          <v-text-field
            v-model="item.draft.grand_father_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام پدرکلان')"
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
            <v-text-field
              v-model="item.draft.now_village"
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
        <template #item.guzar="{ item }">
          <v-text-field
            v-model="item.draft.guzar"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('گذر')"
            hide-details
          />
        </template>

        <!-- Specific / amounts / jobs -->
        <template #item.tafseel_sahi="{ item }">
          <v-text-field
            v-model="item.draft.tafseel_sahi"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('تفسیر صحیح / توضیحات')"
            hide-details
          />
        </template>
        <template #item.street_amount="{ item }">
          <v-text-field
            v-model="item.draft.street_amount"
            class="text-group"
            density="compact"
            variant="outlined"
            type="number"
            :label="t('مقدار سرک')"
            hide-details
          />
        </template>
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
        <template #item.new_job="{ item }">
          <v-text-field
            v-model="item.draft.new_job"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('وظیفه جدید')"
            hide-details
          />
        </template>

        <!-- Quantities -->
        <template #item.agent_quantity="{ item }">
          <v-text-field
            v-model="item.draft.agent_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            type="number"
            :label="t('تعداد عامل')"
            hide-details
          />
        </template>
        <template #item.home_quantity="{ item }">
          <v-text-field
            v-model="item.draft.home_quantity"
            class="text-group"
            density="compact"
            variant="outlined"
            type="number"
            :label="t('تعداد خانه')"
            hide-details
          />
        </template>

        <!-- Contact -->
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

        <!-- History / org -->
        <template #item.jorm_history="{ item }">
          <v-text-field
            v-model="item.draft.jorm_history"
            class="text-group-note"
            density="compact"
            variant="outlined"
            :label="t('سابقه جرم')"
            hide-details
          />
        </template>
        <template #item.tanzim="{ item }">
          <v-text-field
            v-model="item.draft.tanzim"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('تنظیم / وابستگی')"
            hide-details
          />
        </template>

        <!-- Resource (like Captive) -->
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

        <!-- Notes -->
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
            {{ t('ریکارډ مُلکانو یافت نشد، لطفا اضافه کنید') }}
          </div>
        </template>
      </v-data-table>

      <!-- Big image preview dialog -->
      <v-dialog v-model="previewOpen" max-width="720">
        <v-card>
          <v-toolbar
            density="compact"
            color="primary"
            :title="t('پیش نمایش تصویر')"
            class="text-white"
          />
          <v-card-text class="d-flex justify-center">
            <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="680" max-height="560" cover />
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn color="primary" variant="flat" @click="previewOpen = false">{{
              t('بستن')
            }}</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
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

  /* ✅ single repo only */
  import { useFasadRepository } from '@/stores/FasadRepository';

  const { t, locale } = useI18n();
  const drawer = ref(false);

  const previewOpen = ref(false);
  const previewSrc = ref(null);
  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const repo = useFasadRepository();

  const rows = ref([]);
  const filters = ref({});
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  /* === Resource (same as Captive) === */
  const resources = computed(() => repo.resourcecode || []); // [{id, code_number, phone_number}, ...]

  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone_number = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* Headers */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', width: 120, sortable: false },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('گذر'), key: 'guzar' },
    { title: t('تفسیر صحیح'), key: 'tafseel_sahi' },
    { title: t('مقدار سرک'), key: 'street_amount' },
    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه جدید'), key: 'new_job' },
    { title: t('تعداد عامل'), key: 'agent_quantity' },
    { title: t('تعداد خانه'), key: 'home_quantity' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('سابقه جرم'), key: 'jorm_history' },
    { title: t('تنظیم'), key: 'tanzim' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    { title: t('ملاحظات'), key: 'notes' },
    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },
    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },
    { title: t('گذر'), key: 'guzar' },
    { title: t('تفسیر صحیح'), key: 'tafseel_sahi' },
    { title: t('مقدار سرک'), key: 'street_amount' },
    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه جدید'), key: 'new_job' },
    { title: t('تعداد عامل'), key: 'agent_quantity' },
    { title: t('تعداد خانه'), key: 'home_quantity' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('سابقه جرم'), key: 'jorm_history' },
    { title: t('تنظیم'), key: 'tanzim' },
    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone_number' },
    { title: t('ملاحظات'), key: 'notes' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'father_name', 'grand_father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    { title: t('منزل/موقعیت'), keys: ['specific_place', 'street_number', 'home_number', 'guzar'] },
    {
      title: t('توضیحات/مقادیر/وظایف'),
      keys: ['tafseel_sahi', 'street_amount', 'old_job', 'new_job'],
    },
    { title: t('مقادیر'), keys: ['agent_quantity', 'home_quantity'] },
    { title: t('تماس'), keys: ['phone_number'] },
    { title: t('سابقه/وابستگی'), keys: ['jorm_history', 'tanzim'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone_number'] },
    { title: t('دیگر'), keys: ['notes'] },
  ]);

  /* Model mapping */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),

      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,

      loading: false,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      grand_father_name: x.grand_father_name ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',

      specific_place: x.specific_place ?? '',
      street_number: x.street_number ?? '',
      home_number: x.home_number ?? '',
      guzar: x.guzar ?? '',

      tafseel_sahi: x.tafseel_sahi ?? '',
      street_amount: x.street_amount ?? '',
      old_job: x.old_job ?? '',
      new_job: x.new_job ?? '',

      agent_quantity: x.agent_quantity ?? '',
      home_quantity: x.home_quantity ?? '',

      phone_number: x.phone_number ?? '',

      jorm_history: x.jorm_history ?? '',
      tanzim: x.tanzim ?? '',

      // Resource flatten (like Captive)
      resource_code: x.resource_code ?? x.resource?.code_number ?? '',
      resource_phone_number: x.resource_phone_number ?? x.resource?.phone_number ?? '',
      resource_id: x.resource_id ?? x.resource?.id ?? null,

      notes: x.notes ?? '',
    };
    return { ...base, draft: { ...base, picture: null } };
  }

  function hydrate() {
    rows.value = (repo.Mulkanos || []).map(toRowModel);
  }

  async function fetch() {
    await Promise.allSettled([
      repo.GetResourceCode?.(), // codes/phones
      repo.FetchMulkanosData?.({ page: repo.page || 1, itemsPerPage: repo.itemsPerPage || 50 }),
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

  /* image pick + preview + view dialog */
  function triggerFile(item) {
    document.getElementById(`up-${item.idOrTmp}`)?.click();
  }
  function onPickFile(item, file) {
    item.draft.picture = file || null;
    if (file) {
      if (item._blobUrl) URL.revokeObjectURL(item._blobUrl);
      item._blobUrl = URL.createObjectURL(file);
      item.previewUrl = item._blobUrl;
    } else {
      item.previewUrl = item.picture || null;
    }
  }

  /* diff — send only changed fields */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'name',
      'father_name',
      'grand_father_name',
      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_zone',
      'now_district',
      'now_village',
      'specific_place',
      'street_number',
      'home_number',
      'guzar',
      'tafseel_sahi',
      'street_amount',
      'old_job',
      'new_job',
      'agent_quantity',
      'home_quantity',
      'phone_number',
      'jorm_history',
      'tanzim',
      'resource_code',
      'resource_phone_number',
      'resource_id',
      'notes',
    ];
    for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];

    if (d.picture && typeof d.picture === 'object') changed.picture = d.picture;
    else if (typeof d.picture === 'string' && d.picture !== (item.picture ?? ''))
      changed.picture = d.picture;

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

  /* Save / Delete via Fasad repo */
  async function save(item) {
    if (!item.draft?.name?.trim()) return;

    item.loading = true;
    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateMulkano?.(fd);
        if (created?.id) {
          const model = toRowModel(created);
          model.idOrTmp = item.idOrTmp;
          Object.assign(item, model);
          item.draft = { ...model, picture: null };
        } else {
          await fetch();
        }
        return;
      }

      const { changed } = diffDraft(item);
      if (Object.keys(changed).length === 0) return;

      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateMulkano?.(item.id, fd);
      if (updated?.id) {
        const model = toRowModel(updated);
        model.idOrTmp = item.idOrTmp;
        if (item._blobUrl) {
          URL.revokeObjectURL(item._blobUrl);
          item._blobUrl = null;
        }
        Object.assign(item, model);
        item.draft = { ...model, picture: null };
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
      await repo.DeleteMulkano?.(item.id);
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
  /* compact spacing like your sample */
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
