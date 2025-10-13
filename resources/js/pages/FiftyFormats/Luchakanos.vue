<template>
  <v-container fluid class="pt-6 mt-12">
    <AppBar
      :title="t('لچکانو')"
      :subtitle="t('اضافه کردن یک ریکارډ لچکانو')"
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

        <!-- Main address (grouped like Fasad) -->
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

        <!-- Now address (grouped like Fasad) -->
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

        <!-- Place details -->
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

        <!-- Job / gun / contact -->
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
        <template #item.gun_type="{ item }">
          <v-text-field
            v-model="item.draft.gun_type"
            class="text-group"
            density="compact"
            variant="outlined"
            :label="t('نوع اسلحه')"
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

        <!-- History / note -->
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

        <!-- Resource (same behavior as Fasad) -->
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
              {{ t('حذف دیتا') }}
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="py-8 text-medium-emphasis">
            {{ t('ریکارډ لچکانو یافت نشد، لطفا اضافه کنید') }}
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
import { ref, onMounted, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppBar from '@/components/AppBar.vue'
import FilterableTable from '@/components/FilterableTable.vue'
import { useMawadRepository } from '@/stores/MawadRepository'

const { t, locale } = useI18n()
const drawer = ref(false)
const isRtl = ref(locale.value === 'fa')
watch(locale, (l) => (isRtl.value = l === 'fa'))

const repo = useMawadRepository()

const rows = ref([])
const filters = ref({})
let tmpId = 0
const newTmpId = () => `tmp-${++tmpId}`

/* Resources dropdown (fill phone+id) — exactly like Fasad */
const resources = computed(() => repo.resourcecode || [])
function onResourceCodeChange(item) {
  const code = item.draft.resource_code
  const found = resources.value.find((r) => r.code_number === code)
  item.draft.resource_phone = found?.phone_number ?? ''
  item.draft.resource_id = found?.id ?? null
}

/* Headers (table + filter) */
const headers = computed(() => [
  { title: t('تخلص'), key: 'nickname' },
  { title: t('نام'), key: 'name' },
  { title: t('نام پدر'), key: 'father_name' },

  { title: t('آدرس اصلی'), key: 'main_province' },
  { title: t('آدرس فعلی'), key: 'now_province' },

  { title: t('موقعیت مشخص فعلی'), key: 'now_specific_place' },
  { title: t('شماره سرک فعلی'), key: 'now_street_number' },
  { title: t('شماره خانه فعلی'), key: 'now_home_number' },

  { title: t('وظیفه قبلی'), key: 'old_job' },
  { title: t('وظیفه فعلی'), key: 'job' },
  { title: t('نوع اسلحه'), key: 'gun_type' },
  { title: t('شماره تماس'), key: 'phone_number' },

  { title: t('سابقه جرم'), key: 'history_jorm' },
  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },

  { title: t('عملیات'), key: 'actions', sortable: false },
])

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
  { title: t('نوع اسلحه'), key: 'gun_type' },
  { title: t('شماره تماس'), key: 'phone_number' },

  { title: t('سابقه جرم'), key: 'history_jorm' },
  { title: t('یادداشت'), key: 'note' },

  { title: t('کود منبع'), key: 'resource_code' },
  { title: t('شماره منبع'), key: 'resource_phone' },
])

const topGroups = computed(() => [
  { title: t('مشخصات'), keys: ['name', 'nickname', 'father_name'] },
  { title: t('آدرس اصلی'), keys: ['main_province', 'main_district', 'main_village'] },
  { title: t('آدرس فعلی'), keys: ['now_province', 'now_zone', 'now_district', 'now_village'] },
  { title: t('موقعیت'), keys: ['now_specific_place', 'now_street_number', 'now_home_number'] },
  { title: t('وظیفه/اسلحه/تماس'), keys: ['old_job', 'job', 'gun_type', 'phone_number'] },
  { title: t('سوابق/یادداشت'), keys: ['history_jorm', 'note'] },
  { title: t('منبع'), keys: ['resource_code', 'resource_phone'] },
])

/* Map API -> row with draft (Fasad pattern) */
function toRowModel(x = {}) {
  const base = {
    id: x.id ?? null,
    idOrTmp: x.id ?? newTmpId(),
    loading: false,

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
    gun_type: x.gun_type ?? '',
    phone_number: x.phone_number ?? '',

    history_jorm: x.history_jorm ?? '',
    note: x.note ?? '',

    resource_code: x.resource_code ?? '',
    resource_phone: x.resource_phone ?? '',
    resource_id: x.resource_id ?? null,
  }
  return { ...base, draft: { ...base } }
}

/* Diff (same style as Fasad: only changed fields) */
function diffDraft(item) {
  const d = item.draft || {}
  const changed = {}
  const keys = [
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
    'gun_type',
    'phone_number',
    'history_jorm',
    'note',
    'resource_code',
    'resource_phone',
    'resource_id',
  ]
  for (const k of keys) if ((d[k] ?? '') !== (item[k] ?? '')) changed[k] = d[k]
  return changed
}

function toFormDataSelective(obj) {
  const fd = new FormData()
  Object.entries(obj || {}).forEach(([k, v]) => {
    if (v === undefined || v === null) return
    fd.append(k, v)
  })
  return fd
}

/* Hydrate + Fetch */
function hydrate() {
  rows.value = (repo.Luchakanos || []).map(toRowModel)
}
async function fetch() {
  await repo.FetchLuchakanosData?.({
    page: repo.page || 1,
    itemsPerPage: repo.itemsPerPage || 50,
    search: repo.Search || '',
  })
  hydrate()
}

/* Add row (like Fasad) */
function addBlankRow() {
  const newRow = toRowModel({})
  rows.value = [...rows.value, newRow]
  const per = repo.itemsPerPage || 50
  const totalLocal = rows.value.length
  repo.page = Math.max(1, Math.ceil(totalLocal / per))
}

/* Save / Delete — same flow as Fasad */
async function save(item) {
  item.loading = true
  try {
    if (!item.id) {
      const fd = toFormDataSelective(item.draft)
      const created = await repo.CreateLuchakano?.(fd)
      if (created?.id) {
        const model = toRowModel(created)
        model.idOrTmp = item.idOrTmp
        Object.assign(item, model)
        item.draft = { ...model }
      } else {
        await fetch()
      }
      return
    }

    const changed = diffDraft(item)
    if (Object.keys(changed).length === 0) return

    const fd = toFormDataSelective(changed)
    const updated = await repo.UpdateLuchakano?.(item.id, fd)
    if (updated?.id) {
      const model = toRowModel(updated)
      model.idOrTmp = item.idOrTmp
      Object.assign(item, model)
      item.draft = { ...model }
    }
  } finally {
    item.loading = false
  }
}

async function del(item) {
  if (!item.id) {
    rows.value = rows.value.filter((r) => r.idOrTmp !== item.idOrTmp)
    return
  }
  if (!confirm(t('آیا مطمئن هستید؟'))) return
  await repo.DeleteLuchakano?.(item.id)
  await fetch()
}

/* init */
onMounted(async () => {
  await Promise.allSettled([repo.GetResourceCode?.()])
  await fetch()
})
</script>

<style scoped>
table th { white-space: nowrap; }
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
.text-group { display: grid; width: 140px; gap: 2px; }
.text-group-note { display: grid; width: 380px; gap: 2px; }

.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }

.tablebody { display: flex !important; gap: 2px !important; }

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

/* compact spacing like Fasad */
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
