<template>
  <v-row>
    <!-- Event Type -->
    <v-col :cols="colsEventType" :md="mdEventType">
      <v-autocomplete
        v-model="eventTypeNameProxy"
        :items="eventTypeItems"
        :item-title="itemTitle"
        :item-value="itemValue"
        :label="eventTypeLabel"
        density="compact"
        variant="outlined"
        clearable
        :loading="lookups.loading"
        :disabled="disabled"
        hide-details="auto"
        :rules="eventTypeRules"
        :error="eventTypeError"
        :error-messages="eventTypeErrorMessages"
        @update:modelValue="onEventTypePicked($event)"
        auto-select-first
        :return-object="false"
        :menu-props="{ maxHeight: 360 }"
      />
    </v-col>

    <!-- Event Specific Type -->
    <v-col :cols="colsSpecificType" :md="mdSpecificType">
      <v-autocomplete
        v-model="eventSpecificTypeNameProxy"
        :items="eventSpecificTypeItems"
        :item-title="itemTitle"
        :item-value="itemValue"
        :label="eventSpecificTypeLabel"
        density="compact"
        variant="outlined"
        clearable
        :loading="lookups.loading"
        :disabled="disabled || !eventTypeNameProxy"
        hide-details="auto"
        :rules="specificTypeRules"
        :error="specificTypeError"
        :error-messages="specificTypeErrorMessages"
        @update:modelValue="onSpecificTypePicked($event)"
        auto-select-first
        :return-object="false"
        :menu-props="{ maxHeight: 360 }"
      />
    </v-col>
  </v-row>
</template>

<script setup>
import { computed, onMounted, watch } from "vue";
import { useLookupsRepository } from "@/stores/LookupsRepository";

/* ================= PROPS ================= */
const props = defineProps({
  event_type: { type: String, default: "" },
  event_specific_type: { type: String, default: "" },

  eventTypeLabel: { type: String, default: "نوع رویداد" },
  eventSpecificTypeLabel: { type: String, default: "نوع مشخص رویداد" },

  disabled: { type: Boolean, default: false },

  colsEventType: { type: [Number, String], default: 12 },
  mdEventType: { type: [Number, String], default: 4 },
  colsSpecificType: { type: [Number, String], default: 12 },
  mdSpecificType: { type: [Number, String], default: 4 },

  /* ✅ validation + backend errors */
  eventTypeRules: { type: Array, default: () => [] },
  specificTypeRules: { type: Array, default: () => [] },

  eventTypeError: { type: Boolean, default: false },
  eventTypeErrorMessages: { type: Array, default: () => [] },

  specificTypeError: { type: Boolean, default: false },
  specificTypeErrorMessages: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:event_type", "update:event_specific_type", "change"]);
const lookups = useLookupsRepository();

/* ================= HELPERS ================= */
const clean = (v) => (v == null ? "" : String(v).trim());

const unwrap = (x) => {
  if (Array.isArray(x)) return x;
  if (x && Array.isArray(x.data)) return x.data;
  return [];
};

const itemTitle = (x) =>
  x?.name ?? x?.title ?? x?.event_type_name ?? x?.event_specific_type_name ?? "";

const itemValue = (x) => itemTitle(x);

// ✅ adjust these if your API keys differ
const getEventTypeId = (t) => t?.id ?? t?.event_type_id ?? t?.eventTypeId ?? null;
const getSpecificEventTypeEventTypeId = (s) => s?.event_type_id ?? s?.eventTypeId ?? null;

/* ================= STORE DATA ================= */
const eventTypesRaw = computed(() => unwrap(lookups.EventTypes));
const specificTypesRaw = computed(() => unwrap(lookups.EventSpecificTypes));

/* ================= V-MODEL PROXIES ================= */
const eventTypeNameProxy = computed({
  get: () => clean(props.event_type),
  set: (v) => emit("update:event_type", clean(v)),
});

const eventSpecificTypeNameProxy = computed({
  get: () => clean(props.event_specific_type),
  set: (v) => emit("update:event_specific_type", clean(v)),
});

/* ================= ITEMS ================= */
const eventTypeItems = computed(() => eventTypesRaw.value);

const eventSpecificTypeItems = computed(() => {
  if (!eventTypeNameProxy.value) return [];

  const typeRow = eventTypeItems.value.find((t) => clean(itemValue(t)) === eventTypeNameProxy.value);
  const tid = getEventTypeId(typeRow);
  if (!tid) return [];

  return specificTypesRaw.value.filter(
    (s) => String(getSpecificEventTypeEventTypeId(s)) === String(tid)
  );
});

/* ================= EVENTS ================= */
function onEventTypePicked(newType) {
  const nextType = clean(newType);

  if (!nextType) {
    eventSpecificTypeNameProxy.value = "";
    emit("change", { event_type: "", event_specific_type: "" });
    return;
  }

  // if a specific type is selected but belongs to another event type -> clear it
  if (eventSpecificTypeNameProxy.value) {
    const s = specificTypesRaw.value.find(
      (x) => clean(itemValue(x)) === eventSpecificTypeNameProxy.value
    );

    if (!s) {
      eventSpecificTypeNameProxy.value = "";
    } else {
      const tid = getSpecificEventTypeEventTypeId(s);
      const tRow = eventTypeItems.value.find((x) => String(getEventTypeId(x)) === String(tid));
      if (tRow && clean(itemValue(tRow)) !== nextType) {
        eventSpecificTypeNameProxy.value = "";
      }
    }
  }

  emit("change", {
    event_type: nextType,
    event_specific_type: eventSpecificTypeNameProxy.value,
  });
}

function onSpecificTypePicked(newSpecific) {
  const nextSpecific = clean(newSpecific);

  if (!nextSpecific) {
    emit("change", { event_type: eventTypeNameProxy.value, event_specific_type: "" });
    return;
  }

  const s = specificTypesRaw.value.find((x) => clean(itemValue(x)) === nextSpecific);

  const tid = getSpecificEventTypeEventTypeId(s);
  if (tid != null) {
    const tRow = eventTypeItems.value.find((x) => String(getEventTypeId(x)) === String(tid));
    if (tRow) {
      const correctType = clean(itemValue(tRow));
      if (correctType && correctType !== eventTypeNameProxy.value) {
        eventTypeNameProxy.value = correctType;
      }
    }
  }

  emit("change", {
    event_type: eventTypeNameProxy.value,
    event_specific_type: nextSpecific,
  });
}

/* ================= WATCH ================= */
watch(
  () => eventTypeNameProxy.value,
  (newType) => {
    const nextType = clean(newType);
    if (!eventSpecificTypeNameProxy.value) return;

    const s = specificTypesRaw.value.find(
      (x) => clean(itemValue(x)) === eventSpecificTypeNameProxy.value
    );

    if (!s) {
      eventSpecificTypeNameProxy.value = "";
      return;
    }

    const tid = getSpecificEventTypeEventTypeId(s);
    const tRow = eventTypeItems.value.find((x) => String(getEventTypeId(x)) === String(tid));
    if (tRow && clean(itemValue(tRow)) !== nextType) {
      eventSpecificTypeNameProxy.value = "";
    }
  }
);

/* ================= INIT ================= */
onMounted(async () => {
  await Promise.all([lookups.GetEventTypes(), lookups.GetEventSpecificTypes()]);

  // auto set event_type if only specific exists
  if (eventSpecificTypeNameProxy.value && !eventTypeNameProxy.value) {
    const s = specificTypesRaw.value.find(
      (x) => clean(itemValue(x)) === eventSpecificTypeNameProxy.value
    );
    const tid = getSpecificEventTypeEventTypeId(s);
    const tRow = eventTypeItems.value.find((x) => String(getEventTypeId(x)) === String(tid));
    if (tRow) eventTypeNameProxy.value = clean(itemValue(tRow));
  }
});
</script>
