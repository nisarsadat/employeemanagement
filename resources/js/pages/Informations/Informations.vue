<template>
  <v-container fluid class="pt-6 mt-12" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('اطلاعات')"
      :subtitle="t('اضافه کردن اطلاعات')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search -->
    <v-row class="d-flex">
      <v-col cols="12" md="9">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('جستجو')"
          density="compact"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          @keyup.enter="fetchAll"
          clearable
          @click:clear="
            () => {
              repo.Search = '';
              fetchAll();
            }
          "
        />
      </v-col>
      <!-- Optional: add/reset editor row -->
      <!--
      <v-col cols="12" md="3" class="text-md-right">
        <v-btn color="primary" @click="setBlankEditorRow">
          {{ t('اضافه کردن اطلاع') }}
        </v-btn>
      </v-col>
      -->
    </v-row>

    <!-- Single-row editor (create/edit) -->
    <v-card class="tablebody" ref="editorCard">
      <v-data-table
        :headers="headers"
        :items="rows"
        :loading="repo.loading"
        item-key="idOrTmp"
        :dir="isRtl ? 'rtl' : 'ltr'"
        class="elevation-2 custom-table"
        density="comfortable"
        hide-default-footer
      >
        <!-- Codes -->
        <template #item.codes="{ item }">
          <div class="drop-group">
            <v-autocomplete
              v-model="item.draft.resource_id"
              :items="resourceItems"
              item-title="code_number"
              item-value="id"
              variant="outlined"
              density="compact"
              :label="t('کود منبع')"
              hide-details
              clearable
            />
            <v-autocomplete
              v-model="item.draft.officeemployee_id"
              :items="officeEmployeesItems"
              item-title="code_number"
              item-value="id"
              variant="outlined"
              density="compact"
              :label="t('مربوطه کارمند کود')"
              hide-details
              clearable
            />
            <v-autocomplete
              v-model="item.draft.zone_employee_id"
              :items="zoneEmployeesItems"
              item-title="code_number"
              item-value="id"
              variant="outlined"
              density="compact"
              :label="t('مربوطه تدابیری کارمند کود')"
              hide-details
              clearable
            />
          </div>
        </template>

        <!-- Introduction -->
        <template #item.introduction="{ item }">
          <div class="intro-group">
            <v-text-field
              v-model="item.draft.name"
              :label="t('نام')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-text-field
              v-model="item.draft.father_name"
              :label="t('نام پدر')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-text-field
              v-model="item.draft.nickname"
              :label="t('نام مستعار')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-text-field
              v-model="item.draft.grand_father_name"
              :label="t('نام پدر کلان')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
          </div>
        </template>

        <!-- Main address -->
        <template #item.main_province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.main_province"
              :label="t('ولایت اصلی')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-text-field
              v-model="item.draft.main_district"
              :label="t('ولسوالی اصلی')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-text-field
              v-model="item.draft.main_village"
              :label="t('قریه اصلی')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
          </div>
        </template>

        <!-- Now address -->
        <template #item.now_province="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.now_province"
              :label="t('ولایت فعلی')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-text-field
              v-model="item.draft.now_district"
              :label="t('ولسوالی فعلی')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-text-field
              v-model="item.draft.now_village"
              :label="t('قریه فعلی')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
          </div>
        </template>

        <!-- Provision place (optional meta) -->
        <template #item.provision_place="{ item }">
          <div class="addr-group">
            <v-text-field
              v-model="item.draft.zone"
              :label="t('زون')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-text-field
              v-model="item.draft.hawza"
              :label="t('حوزه')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-text-field
              v-model="item.draft.specific_place"
              :label="t('موقعیت مشخص')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
          </div>
        </template>

        <!-- NEW: coordenates -->
        <template #item.coordenates="{ item }">
          <v-text-field
            v-model="item.draft.coordenates"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کوردینات')"
            hide-details
            clearable
          />
        </template>

        <!-- Information type -->
        <template #item.information_type="{ item }">
          <div class="drop-group2">
            <v-autocomplete
              v-model="item.draft.seyasi"
              :items="seyasiItems"
              item-title="name"
              item-value="id"
              :label="t('سیاسی')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
            <v-autocomplete
              v-model="item.draft.jenayee"
              :items="jenayeeItems"
              item-title="name"
              item-value="id"
              :label="t('جنایی')"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
          </div>
        </template>

        <!-- Date / Hour -->
        <template #item.information_date="{ item }">
          <v-text-field
            v-model="item.draft.information_date"
            :label="t('تاریخ اطلاع')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
            clearable
          />
        </template>
        <template #item.information_hour="{ item }">
          <v-text-field
            v-model="item.draft.information_hour"
            :label="t('ساعت اطلاع')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
            clearable
          />
        </template>

        <!-- Degree + Category -->
        <template #item.importance_degree="{ item }">
          <v-text-field
            v-model="item.draft.importance_degree"
            :label="t('درجه موثقیت اطلاع')"
            class="text-group"
            density="compact"
            variant="outlined"
            hide-details
            clearable
          />
        </template>
        <template #item.information_category="{ item }">
          <v-autocomplete
            v-model="item.draft.information_category"
            :items="infoCategoryItems"
            item-title="name"
            item-value="id"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('کتگوری اطلاع')"
            hide-details
            clearable
          />
        </template>

        <!-- Info employee codes -->
        <template #item.infomation_employee_code="{ item }">
          <div class="drop-group2">
            <v-autocomplete
              v-model="item.draft.information_office_employee_code"
              :items="officeEmployeesItems"
              item-title="code_number"
              item-value="id"
              :label="t('کود کارمند اطلاع گیرنده')"
              density="compact"
              variant="outlined"
              clearable
              hide-details
            />
            <v-autocomplete
              v-model="item.draft.infomation_zone_employee_code"
              :items="zoneEmployeesItems"
              item-title="code_number"
              item-value="id"
              :label="t('کود رزیدانت اطلاع گیرنده')"
              density="compact"
              variant="outlined"
              clearable
              hide-details
            />
          </div>
        </template>

        <!-- Big text -->
        <template #item.tafseel="{ item }">
          <v-text-field
            v-model="item.draft.tafseel"
            :label="t('تفصیل')"
            class="text-group-note"
            density="compact"
            variant="outlined"
            hide-details
            clearable
          />
        </template>
        <template #item.talks="{ item }">
          <v-text-field
            v-model="item.draft.talks"
            :label="t('گفت‌وگوهای انجام‌شده')"
            class="text-group-note"
            density="compact"
            variant="outlined"
            hide-details
            clearable
          />
        </template>
        <template #item.outcome="{ item }">
          <v-text-field
            v-model="item.draft.outcome"
            :label="t('نتیجه')"
            class="text-group-note"
            density="compact"
            variant="outlined"
            hide-details
            clearable
          />
        </template>
        <template #item.notes="{ item }">
          <v-text-field
            v-model="item.draft.notes"
            :label="t('ملاحظات')"
            class="text-group-note"
            density="compact"
            variant="outlined"
            hide-details
            clearable
          />
        </template>

        <!-- Documents -->
        <template #item.documents="{ item }">
          <div class="documents-inline">
            <input
              class="hidden-file"
              :id="`docs-${item.idOrTmp}`"
              type="file"
              multiple
              @change="(e) => onPickDocs(item, Array.from(e.target.files || []))"
            />

            <div class="docs-field-row">
              <v-btn
                class="uploader-btn2"
                size="small"
                variant="text"
                density="compact"
                prepend-icon="mdi-tray-arrow-up"
                @click="openDocsPicker(item)"
              >
                {{ t('ّپلود فایل ها') }}
              </v-btn>

              <div class="chips-row">
                <v-chip
                  v-for="(f, idx) in item.draft._newDocs || []"
                  :key="`${item.idOrTmp}-nf-${idx}`"
                  size="small"
                  variant="text"
                  class="chip-outline"
                  :title="f.name"
                >
                  {{ shortName(f.name) }}
                </v-chip>

                <v-chip
                  v-for="(d, idx) in item.existingDocs || []"
                  :key="`${item.idOrTmp}-df-${idx}`"
                  size="small"
                  color="primary"
                  variant="text"
                  class="chip-existing"
                  :title="d.original_name || d.file_name"
                >
                  {{ shortName(d.original_name || d.file_name) }}
                </v-chip>
              </div>

              <v-btn
                class="download-all-btn"
                size="small"
                prepend-icon="mdi-download"
                :disabled="!item.id"
                color="error"
                @click="downloadAll(item)"
              >
                {{ t('دانلود فایل ها') }}
              </v-btn>
            </div>

            <!-- progress -->
            <div v-if="uploadingPct[item.idOrTmp] && uploadingPct[item.idOrTmp] < 100" class="mt-2">
              <v-progress-linear
                :model-value="uploadingPct[item.idOrTmp]"
                height="6"
                color="primary"
                rounded
              />
              <div class="text-caption mt-1">
                ...{{ t('در حال پلود') }} {{ uploadingPct[item.idOrTmp] }}%
              </div>
            </div>
            <div
              v-else-if="uploadingPct[item.idOrTmp] === 100"
              class="text-caption mt-1 text-success"
            >
              100% — {{ t('آپلود شد') }}
            </div>
          </div>
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
              {{ t('ذخیره دیتا') }}
            </v-btn>
            <v-btn class="bg-error" variant="outlined" @click="del(item)">
              {{ t('حذف دیتا') }}
            </v-btn>
            <v-btn color="warning" variant="outlined" @click="clearEditor(item)">
              {{ t('پاک کردن ورودی‌ها') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">{{ t('یک سطر ویرایش/ایجاد آماده است') }}</div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Peek drawer -->
    <v-container fluid>
      <PeekButton
        v-model="expanded"
        :id="selectedId"
        :item="detail"
        :loading="loading"
        title-key="name"
        image-key="picture"
        :fields="headers"
        :cols="3"
        :dir="isRtl ? 'rtl' : 'ltr'"
        @fetch="fetchOne"
      />
    </v-container>

    <!-- Filterable table (print/export) with Edit + Peek -->
    <FilterableTable
      :headers="headersFT"
      :rows="rowsFT"
      :filters="filters"
      @update:filters="(v) => (filters = { ...v })"
      :top-groups="topGroups"
      :showIndex="true"
      :rtl="true"
      :enableEdit="true"
      :enablePeek="true"
      @edit="startEdit"
      @peek="openPeek"
      class="mb-20 mx-2"
    />

    <!-- Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      {{ errorMessage }}
      <template #actions>
        <v-btn variant="text" @click="showError = false">{{ t('بستن') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, watch, computed, nextTick, reactive } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '../../components/AppBar.vue';
  import FilterableTable from '@/components/FilterableTable.vue';
  import PeekButton from '@/components/PeekButton.vue';
  import { useDatabaseRepository } from '../../stores/DatabaseRepository';

  const repo = useDatabaseRepository();
  const { t, locale } = useI18n();

  /* UI / RTL */
  const drawer = ref(false);
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  /* Snackbar */
  const showError = ref(false);
  const errorMessage = ref('');
  const showErrorMessage = (msg) => {
    errorMessage.value = msg;
    showError.value = true;
  };

  /* Upload progress per row */
  const uploadingPct = reactive({});
  const startProgress = (key) => (uploadingPct[key] = 1);
  const setProgress = (key, pct) => (uploadingPct[key] = pct);
  const endProgress = (key) => (uploadingPct[key] = 0);

  /* Slot helper */
  const getRow = (s) => (s && s.raw ? s.raw : s);

  /* Preview (optional image field if you add later) */
  const previewOpen = ref(false);
  const previewSrc = ref(null);
  function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
  }

  /* Data sources normalizer */
  const toArray = (v) => {
    if (v && typeof v.then === 'function') return [];
    if (Array.isArray(v)) return v;
    if (Array.isArray(v?.data)) return v.data;
    if (Array.isArray(v?.items)) return v.items;
    if (v && typeof v === 'object') return Object.values(v);
    return [];
  };

  /* Dropdown options */
  const resourceItems = computed(() => toArray(repo.resource));
  const officeEmployeesItems = computed(() => toArray(repo.officeemployees));
  const zoneEmployeesItems = computed(() => toArray(repo.ZoneEmployees));
  const seyasiItems = computed(() => toArray(repo.seyasiOptions));
  const jenayeeItems = computed(() => toArray(repo.jenayeeOptions));
  const infoCategoryItems = computed(() => toArray(repo.infoCategoryOptions));

  /* Single-row editor */
  let tmpId = 0;
  const newTmpId = () => `tmp-${++tmpId}`;
  const rows = ref([]);

  function toRowModel(x = {}) {
    const base = {
      id: x.id ?? null,
      idOrTmp: x.id ?? newTmpId(),
      loading: false,

      /* codes */
      resource_id: x.resource_id ?? null,
      officeemployee_id: x.officeemployee_id ?? null,
      zone_employee_id: x.zone_employee_id ?? null,

      /* intro */
      name: x.name ?? '',
      father_name: x.father_name ?? '',
      nickname: x.nickname ?? '',
      grand_father_name: x.grand_father_name ?? '',

      /* addresses */
      main_province: x.main_province ?? '',
      main_district: x.main_district ?? '',
      main_village: x.main_village ?? '',
      now_province: x.now_province ?? '',
      now_district: x.now_district ?? '',
      now_village: x.now_village ?? '',

      /* provision/meta (optional) */
      zone: x.zone ?? '',
      hawza: x.hawza ?? '',
      specific_place: x.specific_place ?? '',

      /* types */
      seyasi: x.seyasi ?? '',
      jenayee: x.jenayee ?? '',

      /* time & category */
      information_date: x.information_date ?? '',
      information_hour: x.information_hour ?? '',
      importance_degree: x.importance_degree ?? '',
      information_category: x.information_category ?? '',

      /* info employee codes */
      information_office_employee_code: x.information_office_employee_code ?? '',
      infomation_zone_employee_code: x.infomation_zone_employee_code ?? '',

      /* long texts */
      tafseel: x.tafseel ?? '',
      talks: x.talks ?? '',
      outcome: x.outcome ?? '',
      notes: x.notes ?? '',

      /* coordenates */
      coordenates: x.coordenates ?? '',

      /* documents */
      existingDocs: Array.isArray(x.documents) ? x.documents : [],
    };

    return { ...base, draft: { ...base, _newDocs: [] } };
  }

  function setBlankEditorRow() {
    rows.value = [toRowModel({})];
  }

  /* Table headers (editor) */
  const headers = computed(() => [
    { title: t('کود ها'), key: 'codes', align: 'center' },
    { title: t('معرفت'), key: 'introduction', align: 'center' },
    { title: t('سکونت اصلی'), key: 'main_province', align: 'center' },
    { title: t('سکونت فعلی'), key: 'now_province', align: 'center' },
    { title: t('محل تدارک'), key: 'provision_place', align: 'center' },
    { title: t('کوردینات'), key: 'coordenates', align: 'center' },
    { title: t('نوعیت اطلاع'), key: 'information_type', align: 'center' },
    { title: t('تاریخ اطلاع'), key: 'information_date', align: 'center' },
    { title: t('ساعت اطلاع'), key: 'information_hour', align: 'center' },
    { title: t('درجه موثقیت اطلاع'), key: 'importance_degree', align: 'center' },
    { title: t('کتگوری اطلاع'), key: 'information_category', align: 'center' },
    { title: t('کود اطلاع گیرنده'), key: 'infomation_employee_code', align: 'center' },
    { title: t('تفصیل'), key: 'tafseel', align: 'center' },
    { title: t('گفت‌وگوها'), key: 'talks', align: 'center' },
    { title: t('نتیجه'), key: 'outcome', align: 'center' },
    { title: t('ملاحظات'), key: 'notes', align: 'center' },
    { title: t('اسناد و شواهد'), key: 'documents', align: 'center' },
    { title: t('عمل'), key: 'actions', sortable: false, align: 'center' },
  ]);

  /* Filterable table (bottom) */
  const rowsFT = computed(() => toArray(repo.Informations));
  const headersFT = computed(() => [
    // codes (atomic)
    { title: 'resource', key: 'resource_id' },
    { title: 'employee', key: 'officeemployee_id' },
    { title: 'zone', key: 'zone_employee_id' },

    // intro
    { title: t('نام'), key: 'name' },
    { title: t('نام پدر'), key: 'father_name' },
    { title: t('نام پدرکلان'), key: 'grand_father_name' },
    { title: t('نام مستعار'), key: 'nickname' },

    // addresses
    { title: t('ولایت اصلی'), key: 'main_province' },
    { title: t('ولسوالی اصلی'), key: 'main_district' },
    { title: t('قریه اصلی'), key: 'main_village' },

    { title: t('ولایت فعلی'), key: 'now_province' },
    { title: t('ولسوالی فعلی'), key: 'now_district' },
    { title: t('قریه فعلی'), key: 'now_village' },

    // provision/meta
    { title: t('زون'), key: 'zone' },
    { title: t('حوزه'), key: 'hawza' },
    { title: t('موقعیت مشخص'), key: 'specific_place' },

    // types & time
    { title: t('سیاسی'), key: 'seyasi' },
    { title: t('جنایی'), key: 'jenayee' },
    { title: t('تاریخ اطلاع'), key: 'information_date' },
    { title: t('ساعت اطلاع'), key: 'information_hour' },
    { title: t('درجه موثقیت اطلاع'), key: 'importance_degree' },
    { title: t('کتگوری اطلاع'), key: 'information_category' },

    // employee codes (plain)
    { title: t('کود کارمند اطلاع گیرنده'), key: 'information_office_employee_code' },
    { title: t('کود رزیدانت اطلاع گیرنده'), key: 'infomation_zone_employee_code' },

    // coordenates
    { title: t('کوردینات'), key: 'coordenates' },

    // long texts
    { title: t('تفصیل'), key: 'tafseel' },
    { title: t('گفت‌وگوها'), key: 'talks' },
    { title: t('نتیجه'), key: 'outcome' },
    { title: t('ملاحظات'), key: 'notes' },
  ]);

  const topGroups = computed(() => [
    { title: t('کود ها'), keys: ['resource_id', 'officeemployee_id', 'zone_employee_id'] },
    { title: t('معرفت'), keys: ['name', 'father_name', 'grand_father_name', 'nickname'] },
    { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
    { title: t('آدرس فعلی'), keys: ['now_province', 'now_district', 'now_village'] },
    {
      title: t('محل تدارک / متفرقه'),
      keys: ['zone', 'hawza', 'specific_place', 'coordenates'],
    },
    {
      title: t('نوع و زمان اطلاع'),
      keys: [
        'seyasi',
        'jenayee',
        'information_date',
        'information_hour',
        'importance_degree',
        'information_category',
      ],
    },
    {
      title: t('کودهای کارمند'),
      keys: ['information_office_employee_code', 'infomation_zone_employee_code'],
    },
    { title: t('متن‌ها'), keys: ['tafseel', 'talks', 'outcome', 'notes'] },
  ]);

  /* Filters for FilterableTable */
  const filters = ref({});

  /* Docs UI */
  function openDocsPicker(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;
    document.getElementById(`docs-${item.idOrTmp}`)?.click();
  }
  function onPickDocs(slotItem, files) {
    const item = getRow(slotItem);
    if (!item?.draft) return;
    item.draft._newDocs = Array.isArray(files) ? files : [];
  }
  function shortName(name = '') {
    if (name.length <= 18) return name;
    const dot = name.lastIndexOf('.');
    if (dot === -1) return name.slice(0, 15) + '…';
    const ext = name.slice(dot);
    const base = name.slice(0, 15);
    return `${base}…${ext}`;
  }

  /* Diff & payload */
  function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
      'resource_id',
      'officeemployee_id',
      'zone_employee_id',
      'name',
      'father_name',
      'nickname',
      'grand_father_name',
      'main_province',
      'main_district',
      'main_village',
      'now_province',
      'now_district',
      'now_village',
      'zone',
      'hawza',
      'specific_place',
      'coordenates',
      'seyasi',
      'jenayee',
      'information_date',
      'information_hour',
      'importance_degree',
      'information_category',
      'information_office_employee_code',
      'infomation_zone_employee_code',
      'tafseel',
      'talks',
      'outcome',
      'notes',
    ];
    for (const k of keys) {
      if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k];
    }
    return changed;
  }

  function appendFD(fd, k, v) {
    if (v == null) return;
    if (Array.isArray(v)) {
      v.forEach((val) => appendFD(fd, k + '[]', val));
      return;
    }
    if (typeof v === 'object' && !(v instanceof File) && !(v instanceof Blob)) {
      fd.append(k, v.id ?? v.value ?? JSON.stringify(v));
      return;
    }
    fd.append(k, v);
  }
  function toFormDataSelective(obj) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(obj)) appendFD(fd, k, v);
    return fd;
  }

  /* Save / Delete / Download zip */
  async function save(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;

    const changed = item.id ? diffDraft(item) : { ...item.draft };
    const fd = toFormDataSelective(changed);
    (item.draft._newDocs || []).forEach((f) => fd.append('documents[]', f));

    const key = item.idOrTmp ?? item.id ?? `tmp-${Date.now()}`;
    item.loading = true;
    startProgress(key);

    try {
      if (!item.id) {
        const created = await repo
          .CreateInformation(fd, (pct) => setProgress(key, pct))
          .catch(() => null);
        if (created?.id) {
          // refresh & reset
          await fetchAll();
          setBlankEditorRow();
          showErrorMessage(t('اطلاع ایجاد شد'));
        } else {
          await fetchAll();
          setBlankEditorRow();
        }
        return;
      }

      // UPDATE
      if ([...fd.keys()].length === 0) {
        showErrorMessage(t('هیچ تغییری برای ذخیره وجود ندارد'));
        return;
      }
      const updated = await repo
        .UpdateInformation(item.id, fd, (pct) => setProgress(key, pct))
        .catch(() => null);
      if (updated?.id) {
        await fetchAll();
        setBlankEditorRow();
        showErrorMessage(t('اطلاع بروزرسانی شد'));
      }
    } finally {
      endProgress(key);
      item.loading = false;
    }
  }

  async function del(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;
    if (!item.id) {
      setBlankEditorRow();
      return;
    }
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name || t('رکورد') }))) return;

    try {
      await repo.DeleteInformation(item.id);
      await fetchAll();
      setBlankEditorRow();
      showErrorMessage(t('اطلاع حذف شد'));
    } catch {
      showErrorMessage(t('خطا در حذف اطلاع'));
    }
  }

  async function downloadAll(slotItem) {
    const item = getRow(slotItem);
    if (!item?.id) return;
    const base = (item.name || 'documents').toLowerCase().replace(/\s+/g, '_');
    await repo.DownloadInformationZip(item.id, `${base}_documents.zip`);
  }

  function clearEditor(slotItem) {
    const item = getRow(slotItem);
    if (!item) return;
    item.draft = { ...toRowModel({}).draft };
    item.draft._newDocs = [];
    showErrorMessage(t('ورودی‌ها پاک شد'));
  }

  /* Fetch ALL for FilterableTable */
  async function fetchAll() {
    try {
      await repo.FetchInformationsData({
        page: 1,
        itemsPerPage: 100000,
        search: repo.Search,
      });
    } catch (e) {
      // ignore
    }
  }

  /* Start edit from FilterableTable */
  const editorCard = ref(null);
  async function startEdit(row) {
    try {
      const id = row?.id;
      if (!id) return;

      if (typeof repo.FetchInformationData === 'function') {
        await repo.FetchInformationData(id);
        const single =
          (repo.Information && Array.isArray(repo.Information)
            ? repo.Information[0]
            : repo.Information) || row;
        rows.value = [toRowModel(single)];
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        const single = list.find((r) => r.id === id) || row;
        rows.value = [toRowModel(single)];
      }

      await nextTick();
      const el = editorCard.value?.$el || editorCard.value;
      el?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    } catch {
      showErrorMessage(t('خطا در باز کردن مورد برای ویرایش'));
    }
  }

  /* Peek state & methods */
  const expanded = ref(false);
  const selectedId = ref(null);
  const loading = ref(false);
  const detail = ref(null);

  async function fetchOne(id) {
    loading.value = true;
    try {
      if (typeof repo.FetchInformationData === 'function') {
        await repo.FetchInformationData(id);
        detail.value =
          (repo.Information && Array.isArray(repo.Information)
            ? repo.Information[0]
            : repo.Information) || {};
      } else {
        await fetchAll();
        const list = rowsFT.value || [];
        detail.value = list.find((r) => r.id === id) || {};
      }
    } finally {
      loading.value = false;
    }
  }
  function openPeek(payload) {
    const row = payload?.raw ?? payload;
    if (!row?.id) return;
    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
  }

  /* Init */
  onMounted(async () => {
    await Promise.allSettled([
      repo.GetOfficeEmployee?.(),
      repo.GetResource?.(),
      repo.GetZoneEmployees?.(),
    ]);
    await fetchAll();
    setBlankEditorRow();
  });
</script>

<style scoped>
  .custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
  }
  .custom-table :deep(td) {
    vertical-align: middle;
    padding-top: 8px;
    padding-bottom: 8px;
  }

  /* compact inputs */
  .text-group {
    display: grid;
    width: 200px;
    gap: 2px;
  }
  .text-group-note {
    display: grid;
    width: 400px;
    gap: 2px;
  }
  .addr-group {
    display: grid;
    grid-template-columns: repeat(3, minmax(200px, 1fr));
    gap: 10px !important;
  }
  .intro-group {
    display: grid;
    grid-template-columns: repeat(4, minmax(200px, 1fr));
    gap: 10px;
  }
  .drop-group {
    display: grid;
    grid-template-columns: repeat(3, minmax(250px, 1fr));
    gap: 10px;
  }
  .drop-group2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(250px, 1fr));
    gap: 10px;
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

  /* labels/fields */
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

  /* Documents layout */
  .documents-inline {
    min-width: 520px;
  }
  .docs-field-row {
    display: flex;
    align-items: center;
    gap: 4px;
    border: 1px dashed #9e9e9e;
    border-radius: 8px;
    padding: 6px 8px;
    box-shadow: 0 1px 3px rgba(96, 1, 1, 0.08);
  }
  .uploader-btn2 {
    white-space: nowrap;
    text-transform: none;
    padding: 0 10px;
    box-shadow: 0 1px 2px rgba(141, 2, 2, 0.14), 0 2px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(252, 250, 250, 0.06);
    border-radius: 8px;
  }
  .uploader-btn2:hover {
    box-shadow: 0 0 0 3px rgba(1, 63, 125, 0.25) !important;
  }
  .uploader-btn2:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(1, 63, 125, 0.25);
  }
  .chip-existing {
    background: #c8dae1;
    color: black !important;
  }
  .chips-row {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 2px 0;
    scrollbar-width: thin;
  }
  .chip-outline {
    background: #c8dae1;
  }
  .download-all-btn {
    white-space: nowrap;
  }

  /* Hidden inputs */
  .hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
  }

  /* tighter table cell padding */
  .custom-table :deep(td) {
    padding-left: 8px !important;
    padding-right: 1px !important;
  }
  :deep(.v-text-field),
  :deep(.v-autocomplete) {
    margin-right: -10px !important;
    margin-left: 2px;
  }
</style>
