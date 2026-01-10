<template>
  <v-row>
    <!-- Zone -->
    <v-col :cols="colsZone" :md="mdZone">
      <v-autocomplete
        v-model="zoneNameProxy"
        :items="zoneItems"
        :item-title="itemTitle"
        :item-value="itemValue"
        :label="zoneLabel"
        density="compact"
        variant="outlined"
        clearable
        :loading="lookups.loading"
        :disabled="disabled"
        hide-details="auto"
        @update:modelValue="onZonePicked"
      />
    </v-col>

    <!-- Hawza -->
    <v-col :cols="colsHawza" :md="mdHawza">
      <v-autocomplete
        v-model="hawzaNameProxy"
        :items="hawzaItems"
        :item-title="itemTitle"
        :item-value="itemValue"
        :label="hawzaLabel"
        density="compact"
        variant="outlined"
        clearable
        :loading="lookups.loading"
        :disabled="disabled || !zoneNameProxy"
        hide-details="auto"
        @update:modelValue="onHawzaPicked"
      />
    </v-col>
  </v-row>
</template>

<script setup>
  import { computed, onMounted, watch } from 'vue';
  import { useLookupsRepository } from '@/stores/LookupsRepository';

  /* ================= PROPS ================= */
  const props = defineProps({
    zone: { type: String, default: '' },
    hawza: { type: String, default: '' },

    zoneLabel: { type: String, default: 'زون' },
    hawzaLabel: { type: String, default: 'حوزه' },

    disabled: { type: Boolean, default: false },

    colsZone: { type: [Number, String], default: 12 },
    mdZone: { type: [Number, String], default: 4 },
    colsHawza: { type: [Number, String], default: 12 },
    mdHawza: { type: [Number, String], default: 4 },

    /* ✅ NEW: validation + backend errors */
    zoneRules: { type: Array, default: () => [] },
    hawzaRules: { type: Array, default: () => [] },

    zoneError: { type: Boolean, default: false },
    zoneErrorMessages: { type: Array, default: () => [] },

    hawzaError: { type: Boolean, default: false },
    hawzaErrorMessages: { type: Array, default: () => [] },
  });

  const emit = defineEmits(['update:zone', 'update:hawza', 'change']);
  const lookups = useLookupsRepository();

  /* ================= HELPERS ================= */
  const clean = (v) => (v == null ? '' : String(v).trim());

  const unwrap = (x) => {
    if (Array.isArray(x)) return x;
    if (x && Array.isArray(x.data)) return x.data;
    return [];
  };

  const itemTitle = (x) => x?.name ?? x?.zone_name ?? x?.hawza_name ?? x?.title ?? '';
  const itemValue = (x) => itemTitle(x);

  const getZoneId = (z) => z?.id ?? z?.zone_id ?? z?.zoneId ?? null;
  const getHawzaZoneId = (h) => h?.zone_id ?? h?.zoneId ?? null;

  /* ================= STORE DATA ================= */
  const zonesRaw = computed(() => unwrap(lookups.Zones));
  const hawzasRaw = computed(() => unwrap(lookups.Hawzas));

  /* ================= V-MODEL PROXIES ================= */
  const zoneNameProxy = computed({
    get: () => clean(props.zone),
    set: (v) => emit('update:zone', clean(v)),
  });

  const hawzaNameProxy = computed({
    get: () => clean(props.hawza),
    set: (v) => emit('update:hawza', clean(v)),
  });

  /* ================= ITEMS ================= */
  const zoneItems = computed(() => zonesRaw.value);

  const hawzaItems = computed(() => {
    if (!zoneNameProxy.value) return [];

    const zone = zoneItems.value.find((z) => clean(itemValue(z)) === zoneNameProxy.value);
    const zid = getZoneId(zone);
    if (!zid) return [];

    return hawzasRaw.value.filter((h) => String(getHawzaZoneId(h)) === String(zid));
  });

  /* ================= EVENTS ================= */
  function onZonePicked() {
    if (!zoneNameProxy.value) {
      hawzaNameProxy.value = '';
      emit('change', { zone: '', hawza: '' });
      return;
    }

    // if a hawza is selected but belongs to another zone -> clear it
    const h = hawzasRaw.value.find((x) => clean(itemValue(x)) === hawzaNameProxy.value);
    if (h) {
      const zid = getHawzaZoneId(h);
      const z = zoneItems.value.find((x) => String(getZoneId(x)) === String(zid));
      if (z && clean(itemValue(z)) !== zoneNameProxy.value) {
        hawzaNameProxy.value = '';
      }
    }

    emit('change', {
      zone: zoneNameProxy.value,
      hawza: hawzaNameProxy.value,
    });
  }

  function onHawzaPicked() {
    if (!hawzaNameProxy.value) {
      emit('change', { zone: zoneNameProxy.value, hawza: '' });
      return;
    }

    const h = hawzasRaw.value.find((x) => clean(itemValue(x)) === hawzaNameProxy.value);

    const zid = getHawzaZoneId(h);
    if (zid != null) {
      const z = zoneItems.value.find((x) => String(getZoneId(x)) === String(zid));
      if (z && clean(itemValue(z)) !== zoneNameProxy.value) {
        zoneNameProxy.value = clean(itemValue(z));
      }
    }

    emit('change', {
      zone: zoneNameProxy.value,
      hawza: hawzaNameProxy.value,
    });
  }

  /* ================= WATCH ================= */
  watch(
    () => zoneNameProxy.value,
    () => {
      if (!hawzaNameProxy.value) return;

      const h = hawzasRaw.value.find((x) => clean(itemValue(x)) === hawzaNameProxy.value);
      if (!h) {
        hawzaNameProxy.value = '';
        return;
      }

      const zid = getHawzaZoneId(h);
      const z = zoneItems.value.find((x) => String(getZoneId(x)) === String(zid));
      if (z && clean(itemValue(z)) !== zoneNameProxy.value) {
        hawzaNameProxy.value = '';
      }
    }
  );

  /* ================= INIT ================= */
  onMounted(async () => {
    await Promise.all([lookups.GetZones(), lookups.GetHawzas()]);

    // auto set zone if only hawza exists
    if (hawzaNameProxy.value && !zoneNameProxy.value) {
      const h = hawzasRaw.value.find((x) => clean(itemValue(x)) === hawzaNameProxy.value);
      const zid = getHawzaZoneId(h);
      const z = zoneItems.value.find((x) => String(getZoneId(x)) === String(zid));
      if (z) zoneNameProxy.value = clean(itemValue(z));
    }
  });
</script>
