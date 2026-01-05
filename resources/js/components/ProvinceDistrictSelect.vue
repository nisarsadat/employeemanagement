<template>
  <v-row>
    <!-- Province -->
    <v-col :cols="colsProvince" :md="mdProvince">
      <v-autocomplete 
        v-model="provinceNameProxy"
        :items="provinceItems"
        :item-title="itemTitle"
        :item-value="itemValue"
        :label="provinceLabel"
        density="compact"
        variant="outlined"
        clearable
        :loading="lookups.loading"
        :disabled="disabled"
        hide-details="auto"
        @update:modelValue="onProvincePicked"
      />
    </v-col>

    <!-- District -->
    <v-col :cols="colsDistrict" :md="mdDistrict">
      <v-autocomplete 
        v-model="districtNameProxy"
        :items="districtItems"
        :item-title="itemTitle"
        :item-value="itemValue"
        :label="districtLabel"
        density="compact"
        variant="outlined"
        clearable
        :loading="lookups.loading"
        :disabled="disabled || !provinceNameProxy"
        hide-details="auto"
        @update:modelValue="onDistrictPicked"
      />
    </v-col>
  </v-row>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useLookupsRepository } from '@/stores/LookupsRepository'

/* ================= PROPS ================= */
const props = defineProps({
  province: { type: String, default: '' },   // backend expects NAME
  district: { type: String, default: '' },

  provinceLabel: { type: String, default: 'ولایت' },
  districtLabel: { type: String, default: 'ولسوالی' },

  disabled: { type: Boolean, default: false },

  colsProvince: { type: [Number, String], default: 12 },
  mdProvince: { type: [Number, String], default: 4 },
  colsDistrict: { type: [Number, String], default: 12 },
  mdDistrict: { type: [Number, String], default: 4 },
})

const emit = defineEmits(['update:province', 'update:district', 'change'])
const lookups = useLookupsRepository()

/* ================= HELPERS ================= */
const clean = (v) => (v == null ? '' : String(v).trim())

const unwrap = (x) => {
  if (Array.isArray(x)) return x
  if (x && Array.isArray(x.data)) return x.data
  return []
}

const itemTitle = (x) =>
  x?.name ?? x?.province_name ?? x?.district_name ?? x?.title ?? ''

const itemValue = (x) => itemTitle(x)

const getProvinceId = (p) =>
  p?.id ?? p?.province_id ?? p?.provinceId ?? null

const getDistrictProvinceId = (d) =>
  d?.province_id ?? d?.provinceId ?? null

/* ================= STORE DATA ================= */
const provincesRaw = computed(() => unwrap(lookups.Provinces))
const districtsRaw = computed(() => unwrap(lookups.Districts))

/* ================= V-MODEL PROXIES ================= */
const provinceNameProxy = computed({
  get: () => clean(props.province),
  set: (v) => emit('update:province', clean(v)),
})

const districtNameProxy = computed({
  get: () => clean(props.district),
  set: (v) => emit('update:district', clean(v)),
})

/* ================= ITEMS ================= */
const provinceItems = computed(() => provincesRaw.value)

const districtItems = computed(() => {
  if (!provinceNameProxy.value) return []

  const province = provinceItems.value.find(
    (p) => clean(itemValue(p)) === provinceNameProxy.value
  )
  const pid = getProvinceId(province)
  if (!pid) return []

  return districtsRaw.value.filter(
    (d) => String(getDistrictProvinceId(d)) === String(pid)
  )
})

/* ================= EVENTS ================= */
function onProvincePicked() {
  if (!provinceNameProxy.value) {
    districtNameProxy.value = ''
    emit('change', { province: '', district: '' })
    return
  }

  const d = districtsRaw.value.find(
    (x) => clean(itemValue(x)) === districtNameProxy.value
  )

  if (d) {
    const pid = getDistrictProvinceId(d)
    const p = provinceItems.value.find(
      (x) => String(getProvinceId(x)) === String(pid)
    )
    if (p && clean(itemValue(p)) !== provinceNameProxy.value) {
      districtNameProxy.value = ''
    }
  }

  emit('change', {
    province: provinceNameProxy.value,
    district: districtNameProxy.value,
  })
}

function onDistrictPicked() {
  if (!districtNameProxy.value) {
    emit('change', { province: provinceNameProxy.value, district: '' })
    return
  }

  const d = districtsRaw.value.find(
    (x) => clean(itemValue(x)) === districtNameProxy.value
  )

  const pid = getDistrictProvinceId(d)
  if (pid != null) {
    const p = provinceItems.value.find(
      (x) => String(getProvinceId(x)) === String(pid)
    )
    if (p && clean(itemValue(p)) !== provinceNameProxy.value) {
      provinceNameProxy.value = clean(itemValue(p))
    }
  }

  emit('change', {
    province: provinceNameProxy.value,
    district: districtNameProxy.value,
  })
}

/* ================= WATCH ================= */
watch(
  () => provinceNameProxy.value,
  () => {
    if (!districtNameProxy.value) return

    const d = districtsRaw.value.find(
      (x) => clean(itemValue(x)) === districtNameProxy.value
    )
    if (!d) {
      districtNameProxy.value = ''
      return
    }

    const pid = getDistrictProvinceId(d)
    const p = provinceItems.value.find(
      (x) => String(getProvinceId(x)) === String(pid)
    )
    if (p && clean(itemValue(p)) !== provinceNameProxy.value) {
      districtNameProxy.value = ''
    }
  }
)

/* ================= INIT ================= */
onMounted(async () => {
  await Promise.all([
    lookups.GetProvinces(),
    lookups.GetDistricts(),
  ])

  // auto set province if only district exists
  if (districtNameProxy.value && !provinceNameProxy.value) {
    const d = districtsRaw.value.find(
      (x) => clean(itemValue(x)) === districtNameProxy.value
    )
    const pid = getDistrictProvinceId(d)
    const p = provinceItems.value.find(
      (x) => String(getProvinceId(x)) === String(pid)
    )
    if (p) provinceNameProxy.value = clean(itemValue(p))
  }
})
</script>
