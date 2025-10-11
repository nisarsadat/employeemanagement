<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('فساد')"
      :subtitle="t('اضافه کردن یک ریکارډ فساد')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions>
        <v-btn icon />
      </template>
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
          {{ t('اضافه کردن ریکارډ فساد') }}
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
        <!-- Picture cell -->
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
              </div>
            </div>
          </div>
        </template>

        <!-- Simple fields -->
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
        <template #item.nick_name="{ item }">
          <v-text-field
            v-model="item.draft.nick_name"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نام مستعار')"
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

        <!-- Now address -->
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

        <!-- Place / Home -->
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
            :label="t('سرک نمبر')"
            hide-details
          />
        </template>
        <template #item.home_number="{ item }">
          <v-text-field
            v-model="item.draft.home_number"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('نمبر خانه')"
            hide-details
          />
        </template>

        <!-- Work / affiliations -->
        <template #item.jabha="{ item }">
          <v-text-field
            v-model="item.draft.jabha"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('جبهه')"
            hide-details
          />
        </template>
        <template #item.supplier_name="{ item }">
          <v-text-field
            v-model="item.draft.supplier_name"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('نام تمویل‌کننده')"
            hide-details
          />
        </template>
        <template #item.responsibility="{ item }">
          <v-text-field
            v-model="item.draft.responsibility"
            density="compact"
            variant="outlined"
            class="text-group-note"
            :label="t('مسؤولیت')"
            hide-details
          />
        </template>

        <!-- Activity / social -->
        <template #item.activity_zone="{ item }">
          <v-text-field
            v-model="item.draft.activity_zone"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('زون فعالیت')"
            hide-details
          />
        </template>
        <template #item.friends_number="{ item }">
          <v-text-field
            v-model="item.draft.friends_number"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('تعداد دوستان')"
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

        <!-- Band -->
        <template #item.bond_distance="{ item }">
          <v-text-field
            v-model="item.draft.bond_distance"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('فاصله بانډ')"
            hide-details
          />
        </template>
        <template #item.last_band_reason="{ item }">
          <v-text-field
            v-model="item.draft.last_band_reason"
            density="compact"
            variant="outlined"
            class="text-group-note"
            :label="t('دلیل آخرین بند')"
            hide-details
          />
        </template>
        <template #item.band_place="{ item }">
          <v-text-field
            v-model="item.draft.band_place"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('محل بند')"
            hide-details
          />
        </template>

        <!-- Job -->
        <template #item.now_job="{ item }">
          <v-text-field
            v-model="item.draft.now_job"
            density="compact"
            variant="outlined"
            class="text-group"
            :label="t('وظیفه فعلی')"
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
              {{ t('حذف دیتا') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">
            {{ t('ریکارډ فساد یافت نشد، لطفا اضافه کنید') }}
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
  import { useFifthyFormatsRepository } from '@/stores/FifthyFormatsRepository';

  const { t, locale } = useI18n();
  const drawer = ref(false);

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const repo = useFifthyFormatsRepository();

  const rows = ref([]);
  const filters = ref({});
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;

  /* Resources dropdown (fill phone+id) */
  const resources = computed(() => repo.resourcecode || []);
  function onResourceCodeChange(item) {
    const code = item.draft.resource_code;
    const found = resources.value.find((r) => r.code_number === code);
    item.draft.resource_phone = found?.phone_number ?? '';
    item.draft.resource_id = found?.id ?? null;
  }

  /* Headers (table + filter) */
  const headers = computed(() => [
    { title: t('تصویر'), key: 'picture', width: 120, sortable: false },
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },

    { title: t('آدرس اصلی'), key: 'main_province' },
    { title: t('آدرس فعلی'), key: 'now_province' },

    { title: t('موقعیت مشخص'), key: 'specific_place' },
    { title: t('سرک #'), key: 'street_number' },
    { title: t('خانه #'), key: 'home_number' },

    { title: t('جبهه'), key: 'jabha' },
    { title: t('تمویل‌کننده'), key: 'supplier_name' },
    { title: t('مسؤولیت'), key: 'responsibility' },

    { title: t('زون فعالیت'), key: 'activity_zone' },
    { title: t('تعداد دوستان'), key: 'friends_number' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('فاصله بانډ'), key: 'bond_distance' },
    { title: t('دلیل آخرین بند'), key: 'last_band_reason' },
    { title: t('محل بند'), key: 'band_place' },

    { title: t('وظیفه فعلی'), key: 'now_job' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },

    { title: t('عمل'), key: 'actions', sortable: false },
  ]);

  const headersFT = computed(() => [
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام مستعار'), key: 'nick_name' },

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

    { title: t('جبهه'), key: 'jabha' },
    { title: t('تمویل‌کننده'), key: 'supplier_name' },
    { title: t('مسؤولیت'), key: 'responsibility' },

    { title: t('زون فعالیت'), key: 'activity_zone' },
    { title: t('تعداد دوستان'), key: 'friends_number' },
    { title: t('شماره تماس'), key: 'phone_number' },

    { title: t('فاصله بانډ'), key: 'bond_distance' },
    { title: t('دلیل آخرین بند'), key: 'last_band_reason' },
    { title: t('محل بند'), key: 'band_place' },

    { title: t('وظیفه فعلی'), key: 'now_job' },

    { title: t('کود منبع'), key: 'resource_code' },
    { title: t('شماره منبع'), key: 'resource_phone' },
  ]);

  const topGroups = computed(() => [
    { title: t('مشخصات'), keys: ['name', 'nick_name', 'father_name'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
    { title: t('منزل/آدرس'), keys: ['specific_place', 'street_number', 'home_number'] },
    { title: t('فعالیت'), keys: ['jabha', 'supplier_name', 'responsibility', 'activity_zone'] },
    { title: t('تماس/اجتماعی'), keys: ['friends_number', 'phone_number'] },
    { title: t('بانډ/بند'), keys: ['bond_distance', 'last_band_reason', 'band_place'] },
    { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
  ]);

  /* Map API -> row with draft */
  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      picture: x.picture ?? null,
      previewUrl: x.picture ?? null,
      loading: false,

      name: x.name ?? '',
      father_name: x.father_name ?? '',
      nick_name: x.nick_name ?? '',

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

      jabha: x.jabha ?? '',
      supplier_name: x.supplier_name ?? '',
      responsibility: x.responsibility ?? '',

      activity_zone: x.activity_zone ?? '',
      friends_number: x.friends_number ?? '',
      phone_number: x.phone_number ?? '',
      bond_distance: x.bond_distance ?? '',

      last_band_reason: x.last_band_reason ?? '',
      band_place: x.band_place ?? '',
      now_job: x.now_job ?? '',

      resource_code: x.resource_code ?? '',
      resource_phone: x.resource_phone ?? '',
      resource_id: x.resource_id ?? null,
    };
    return { ...base, draft: { ...base, picture: null } };
  }

  function hydrate() {
    rows.value = (repo.fasads || []).map(toRowModel);
  }

  async function fetch() {
    await repo.FetchFasadsData({ page: repo.page || 1, itemsPerPage: repo.itemsPerPage || 50 });
    hydrate();
  }

  function addBlankRow() {
    const newRow = toRowModel({});
    rows.value = [...rows.value, newRow];
    const per = repo.itemsPerPage || 50;
    const totalLocal = rows.value.length;
    repo.page = Math.max(1, Math.ceil(totalLocal / per));
  }

  /* Image picker */
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

  /* Diff */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'name',
      'father_name',
      'nick_name',
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
      'jabha',
      'supplier_name',
      'responsibility',
      'activity_zone',
      'friends_number',
      'phone_number',
      'bond_distance',
      'last_band_reason',
      'band_place',
      'now_job',
      'resource_code',
      'resource_phone',
      'resource_id',
    ];
    for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];

    const imageChanged = !!(d.picture && typeof d.picture === 'object');
    if (imageChanged) {
      changed.picture = d.picture;
      changed.old_picture = item.picture;
    }
    return { changed, imageChanged };
  }

  function toFormDataSelective(obj) {
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      fd.append(k, v);
    });
    return fd;
  }

  /* Save / Delete */
  async function save(item) {
    item.loading = true;

    try {
      if (!item.id) {
        const fd = toFormDataSelective(item.draft);
        const created = await repo.CreateFasad(fd);
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

      const { changed, imageChanged } = diffDraft(item);
      if (Object.keys(changed).length === 0) {
        return;
      }

      const fd = toFormDataSelective(changed);
      const updated = await repo.UpdateFasad(item.id, fd);
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
    } catch (error) {
      console.error('Save error:', error);
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
      await repo.DeleteFasad(item.id);
      await fetch();
    } catch (error) {
      console.error('Delete error:', error);
    }
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