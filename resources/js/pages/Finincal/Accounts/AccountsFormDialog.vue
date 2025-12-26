<template>
  <v-dialog v-model="model" max-width="720" scrollable>
    <v-card rounded="xl" class="overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-primary py-6 px-4">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-avatar color="white" size="48" class="me-3">
              <v-icon icon="mdi-bank" color="primary" size="28" />
            </v-avatar>
            <div>
              <h2 class="text-h5 font-weight-bold text-white mb-1">
                {{ isEdit ? "ویرایش حساب" : "ثبت حساب جدید" }}
              </h2>
              <p class="text-white text-caption opacity-75 mb-0">
                {{ isEdit ? "اطلاعات حساب را ویرایش کنید" : "اطلاعات حساب جدید را وارد کنید" }}
              </p>
            </div>
          </div>

          <v-btn icon="mdi-close" variant="text" color="white" size="small" @click="close" />
        </div>
      </div>

      <v-divider />

      <v-card-text class="pa-4">
        <v-form ref="formRef" @submit.prevent="onSubmit">
          <div class="d-grid gap-3">
            <v-text-field
              v-model="draft.name"
              label="نام حساب"
              density="compact"
              variant="outlined"
              prepend-inner-icon="mdi-text"
              :rules="[rules.required]"
              :error="hasError('name')"
              :error-messages="getErrorMessages('name')"
            />

            <v-text-field
              v-model="draft.price"
              label="موجودی / قیمت"
              density="compact"
              variant="outlined"
              type="number"
              prepend-inner-icon="mdi-currency-usd"
              :rules="[rules.requiredNumber]"
              :error="hasError('price')"
              :error-messages="getErrorMessages('price')"
            />

            <v-textarea
              v-model="draft.details"
              label="جزئیات"
              density="compact"
              variant="outlined"
              rows="3"
              auto-grow
              prepend-inner-icon="mdi-note-text"
              :error="hasError('details')"
              :error-messages="getErrorMessages('details')"
            />
          </div>

          <div v-if="backendErrors.general?.length" class="mt-3">
            <v-alert type="error" variant="tonal" density="compact">
              <div v-for="(m, i) in backendErrors.general" :key="i">{{ m }}</div>
            </v-alert>
          </div>

          <div class="d-flex justify-end gap-2 mt-4">
            <v-btn variant="text" @click="close">انصراف</v-btn>
            <v-btn color="primary" :loading="saving" type="submit">
              {{ isEdit ? "ذخیره تغییرات" : "ثبت حساب" }}
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, reactive, ref, watch } from "vue";
import { useFinanceRepository } from "@/stores/financeRepository";

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editId: { type: [Number, String, null], default: null },
});

const emit = defineEmits(["update:modelValue", "saved"]);

const repo = useFinanceRepository();

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit("update:modelValue", v),
});

const isEdit = computed(() => !!props.editId);

const formRef = ref(null);
const saving = ref(false);

const draft = reactive({
  name: "",
  price: "",
  details: "",
});

const backendErrors = reactive({});

const rules = {
  required: (v) => (!!v || v === 0) || "این فیلد ضروری است",
  requiredNumber: (v) => {
    if (v === null || v === undefined || v === "") return "این فیلد ضروری است";
    const n = Number(v);
    return Number.isFinite(n) ? true : "عدد معتبر وارد کنید";
  },
};

function clearErrors() {
  Object.keys(backendErrors).forEach((k) => delete backendErrors[k]);
}

function hasError(key) {
  return Array.isArray(backendErrors[key]) && backendErrors[key].length > 0;
}

function getErrorMessages(key) {
  return backendErrors[key] || [];
}

function resetDraft() {
  draft.name = "";
  draft.price = "";
  draft.details = "";
}

async function loadForEdit() {
  if (!props.editId) return;
  clearErrors();
  const item = await repo.FetchAccountData(props.editId);
  draft.name = item?.name ?? "";
  draft.price = item?.price ?? "";
  draft.details = item?.details ?? "";
}

watch(
  () => model.value,
  async (open) => {
    if (!open) return;
    resetDraft();
    clearErrors();
    if (props.editId) await loadForEdit();
  }
);

function close() {
  model.value = false;
}

async function onSubmit() {
  clearErrors();

  const res = await formRef.value?.validate();
  if (!res?.valid) return;

  saving.value = true;
  try {
    const payload = {
      name: draft.name,
      price: draft.price,
      details: draft.details,
    };

    if (isEdit.value) {
      await repo.UpdateAccount(props.editId, payload);
    } else {
      await repo.CreateAccount(payload);
    }

    emit("saved");
    close();
  } catch (e) {
    const errors = e?.response?.data?.errors;
    if (errors && typeof errors === "object") {
      Object.assign(backendErrors, errors);
    } else {
      backendErrors.general = ["خطا رخ داد. دوباره تلاش کنید."];
    }
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
.bg-gradient-primary {
  background: linear-gradient(135deg, rgba(25, 118, 210, 1), rgba(33, 150, 243, 0.9));
}
.d-grid {
  display: grid;
}
.gap-3 {
  gap: 12px;
}
.gap-2 {
  gap: 8px;
}
</style>
