<template>
  <v-container fluid class="py-6 mt-12">
    <AppBar
      :title="t('افراد مشکوک ')"
      :subtitle="t('اضافه کردن ریکارډ جدید مشکوکات')"
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

    <v-card class="tablebody">
      <v-data-table
        :headers="headers"
        :items="rows"
        :loading="repo.loading"
        :items-per-page="repo.itemsPerPage"
        :page="repo.page"
        :items-length="repo.totalItems || 0"
        :dir="isRtl ? 'rtl' : 'ltr'"
        item-key="idOrTmp"
        class="elevation-2 custom-table"
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
        <!-- Picture with view/edit overlay (exact style you provided) -->
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
              color="primary"
              variant="outlined"
              icon
              @click="triggerFile(item)"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>

            <div v-else class="pic-thumb" :title="t('گزینه ها')">
              <v-avatar size="32">
                <v-img :src="item.previewUrl || item.picture" :alt="t('تصویر')" cover />
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

        <!-- Intro -->
        <template #item.name="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.name"
              density="compact"
              variant="outlined"
              :label="t('نام')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.father_name"
              density="compact"
              variant="outlined"
              :label="t('نام پدر')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.phone_number"
              density="compact"
              variant="outlined"
              :label="t('شماره تماس')"
              hide-details
            />
          </div>
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

        <!-- Current address -->
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
              v-model="item.draft.now_districts"
              density="compact"
              variant="outlined"
              :label="t('ولسوالی‌های فعلی')"
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
        <template #item.now_specific_place="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.now_specific_place"
              density="compact"
              variant="outlined"
              :label="t('موقعیت مشخص فعلی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.now_street_number"
              density="compact"
              variant="outlined"
              :label="t('شماره سرک فعلی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.now_home_number"
              density="compact"
              variant="outlined"
              :label="t('شماره خانه فعلی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.coordinates"
              density="compact"
              variant="outlined"
              :label="t('مختصات')"
              hide-details
            />
          </div>
        </template>

        <!-- Jobs -->
        <template #item.old_job="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.old_job"
              density="compact"
              variant="outlined"
              :label="t('وظیفه قبلی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.now_job"
              density="compact"
              variant="outlined"
              :label="t('وظیفه فعلی')"
              hide-details
            />
            <v-text-field
              v-model="item.draft.mushkokyat_points"
              density="compact"
              variant="outlined"
              :label="t('نقاط مشکوکیت')"
              hide-details
            />
          </div>
        </template>

        <!-- Notes -->
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
              {{ t('حذف') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">{{ t('هیچ ریکارډ یافت نشد') }}</div>
        </template>
      </v-data-table>

      <!-- Image preview dialog -->
      <v-dialog v-model="previewOpen" max-width="520">
        <v-card>
          <v-toolbar density="compact" color="primary" :title="t('پیش نمایش')" class="text-white" />
          <v-card-text class="d-flex justify-center">
            <v-img :src="previewSrc" :alt="t('پیش نمایش')" max-width="480" max-height="420" cover />
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

  <!-- Filterable/printable table -->
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
</template>

<script setup>
  import { ref, onMounted, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '@/components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import { useBadmashanoRepository } from '@/stores/BadmashanoRepository';

  const { t, locale } = useI18n();
  const drawer = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const repo = useBadmashanoRepository();
  const rows = ref([]);
  const filters = ref({});

  /* Headers (main) */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', width: 120, sortable: false },

    { title: t('نام/پدر/تماس'), key: 'name' },

    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },
    { title: t('موقعیت/مختصات'), key: 'now_specific_place' },

    { title: t('وظایف/نقاط مشکوکیت'), key: 'old_job' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('عملیات'), key: 'actions', sortable: false },
  ]);

  /* Headers (FilterableTable) */
  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },

    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه/ناحیه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('زون فعلی'), key: 'now_zone' },
    { title: t('ولسوالی‌های فعلی'), key: 'now_districts' },
    { title: t('قریه/ناحیه فعلی'), key: 'now_village' },
    { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
    { title: t('شماره سرک فعلی'), key: 'now_street_number' },
    { title: t('شماره خانه فعلی'), key: 'now_home_number' },
    { title: t('مختصات'), key: 'coordinates' },

    { title: t('وظیفه قبلی'), key: 'old_job' },
    { title: t('وظیفه فعلی'), key: 'now_job' },
    { title: t('شماره تماس'), key: 'phone_number' },
    { title: t('نقاط مشکوکیت'), key: 'mushkokyat_points' },

    { title: t('یادداشت'), key: 'note' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  /* Top groups */
  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'father_name', 'phone_number'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_districts', 'now_village'] },
    {
      title: t('موقعیت/مختصات'),
      keys: ['now_specific_place', 'now_street_number', 'now_home_number', 'coordinates'],
    },
    { title: t('وظایف/مشکوکیت'), keys: ['old_job', 'now_job', 'mushkokyat_points'] },
    { title: t('منبع/یادداشت'), keys: ['resource_code', 'resource_phone', 'note'] },
  ]);

  /* Resource code -> phone + id */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* Preview dialog */
  const previewOpen = ref(false);
  const previewSrc = ref(null);
  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }

  /* Rows/model */
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),

      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,

      name: x.name ?? '',
      father_name: x.father_name ?? '',

      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',

      now_province: x.now_province ?? '',
      now_zone: x.now_zone ?? '',
      now_districts: x.now_districts ?? '',
      now_village: x.now_village ?? '',
      now_specific_place: x.now_specific_place ?? '',
      now_street_number: x.now_street_number ?? '',
      now_home_number: x.now_home_number ?? '',
      coordinates: x.coordinates ?? '',

      old_job: x.old_job ?? '',
      now_job: x.now_job ?? '',
      phone_number: x.phone_number ?? '',
      mushkokyat_points: x.mushkokyat_points ?? '',

      note: x.note ?? '',

      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base, picture: null } };
  }

  function hydrate() {
    rows.value = (repo.MashkokPeoples || []).map(toRowModel);
  }

  async function fetch() {
    await repo.FetchMashkokPeoplesData?.({
      page: repo.page || 1,
      itemsPerPage: repo.itemsPerPage || 50,
      search: repo.Search || '',
    });
    hydrate();
  }

  function addBlankRow() {
    rows.value = [toRowModel({}), ...rows.value];
  }

  /* Image picker (same behavior as your example) */
  function triggerFile(item) {
    const el = document.getElementById(`up-${item.idOrTmp}`);
    if (el) el.click();
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

  /* Diff + payload */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'name',
      'father_name',
      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_zone',
      'now_districts',
      'now_village',
      'now_specific_place',
      'now_street_number',
      'now_home_number',
      'coordinates',
      'old_job',
      'now_job',
      'phone_number',
      'mushkokyat_points',
      'note',
      'resource_code',
      'resource_phone',
      'resource_id',
    ];
    for (const k of keys) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    const imageChanged = !!(d.picture && typeof d.picture === 'object');
    if (imageChanged) {
      changed.picture = d.picture;
      changed.old_picture = item.picture;
    }
    return { changed, imageChanged };
  }

  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  /* Save / Delete */
  async function save(item) {
    if (!item.draft?.name?.trim()) {
      alert(t('نام الزامی است'));
      return;
    }

    // Create
    if (!item.id) {
      const fd = toFormDataSelective(item.draft);
      const created = await repo.CreateMashkokPeople?.(fd).catch(() => null);
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

    // Update
    const { changed, imageChanged } = diffDraft(item);
    if (Object.keys(changed).length === 0) return;

    const fd = toFormDataSelective(changed);
    const updated = await repo.UpdateMashkokPeople?.(item.id, fd).catch(() => null);
    if (updated?.id) {
      const model = toRowModel(updated);
      model.idOrTmp = item.idOrTmp;
      if (!imageChanged && item._blobUrl) {
        URL.revokeObjectURL(item._blobUrl);
        item._blobUrl = null;
      }
      Object.assign(item, model);
      item.draft = { ...model, picture: null };
    }
  }

  async function del(item) {
    if (!item.id) {
      rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp);
      return;
    }
    if (!confirm(t('آیا مطمئن هستید؟'))) return;
    await repo.DeleteMashkokPeople?.(item.id);
    await fetch();
  }

  /* Init */
  onMounted(async () => {
    await Promise.allSettled([repo.GetResourceCode?.()]);
    await fetch();
  });
</script>

<style scoped>
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }

  /* groups */
  .addr-group {
    display: grid;
    grid-template-columns: repeat(4, minmax(180px, 1fr));
    gap: 2px;
  }
  .text-group {
    display: grid;
    width: 220px;
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

  /* image overlay (same as your example) */
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
    position: relative;
    display: inline-flex;
    cursor: pointer;
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

  /* compact inputs */
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
