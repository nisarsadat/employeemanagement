<template>
    <v-container fluid class="pt-6 mt-12">
        <AppBar
            :title="t('کارمندان میریت')"
            :subtitle="t('اضافه کردن یک کارمند مدیریت')"
            @toggle-drawer="drawer = !drawer"
        >
            <template #actions>
                <v-btn icon></v-btn>
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
                <v-btn color="primary" @click="addBlankRow">{{
                    t("اضافه کردن کارمند مدیریت")
                }}</v-btn>
            </v-col>
        </v-row>

        <v-card class="tablebody">
            <v-data-table
                :headers="headers"
                :items="rows"
                :loading="repo.loading"
                :items-per-page="repo.itemsPerPage"
                :page="repo.page"
                :items-length="repo.totalItems"
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
                <!-- Picture cell with hover actions -->
                <template #item.picture="{ item }">
                    <div class="pic-wrap">
                        <!-- hidden file input (kept) -->
                        <input
                            class="hidden-file"
                            type="file"
                            accept="image/*"
                            :id="`up-${item.idOrTmp}`"
                            @change="
                                (e) =>
                                    onPickFile(
                                        item,
                                        e.target.files?.[0] ?? null
                                    )
                            "
                        />

                        <!-- no picture → plus button -->
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

                        <!-- has picture → avatar with hover overlay -->
                        <div v-else class="pic-thumb" :title="t('گزینه ها')">
                            <v-avatar size="32">
                                <v-img
                                    :src="item.previewUrl || item.picture"
                                    :alt="t('عکس')"
                                    cover
                                />
                            </v-avatar>

                            <!-- hover overlay -->
                            <div class="pic-overlay">
                                <v-btn
                                    size="x-small"
                                    color="primary"
                                    variant="flat"
                                    @click.stop="triggerFile(item)"
                                >
                                    <v-icon start>mdi-pencil</v-icon>
                                    {{ t("ویرایش") }}
                                </v-btn>
                                <v-btn
                                    size="x-small"
                                    color="secondary"
                                    variant="flat"
                                    @click.stop="
                                        openPreview(
                                            item.previewUrl || item.picture
                                        )
                                    "
                                >
                                    <v-icon start>mdi-eye</v-icon>
                                    {{ t("نمایش") }}
                                </v-btn>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Basic text fields (outlined) -->
                <template #item.name="{ item }">
                    <v-text-field
                        v-model="item.draft.name"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('نام')"
                        hide-details
                        :error="hasError(item, 'name')"
                        :error-messages="getErrorMessages(item, 'name')"
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
                        :error="hasError(item, 'father_name')"
                        :error-messages="getErrorMessages(item, 'father_name')"
                    />
                </template>
                <template #item.nickname="{ item }">
                    <v-text-field
                        v-model="item.draft.nickname"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('نام مستعار')"
                        hide-details
                        :error="hasError(item, 'nickname')"
                        :error-messages="getErrorMessages(item, 'nickname')"
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
                            :error="hasError(item, 'main_province')"
                            :error-messages="getErrorMessages(item, 'main_province')"
                        />
                        <v-text-field
                            v-model="item.draft.main_district"
                            density="compact"
                            variant="outlined"
                            hide-details
                            :label="t('ولسوالی اصلی')"
                            :error="hasError(item, 'main_district')"
                            :error-messages="getErrorMessages(item, 'main_district')"
                        />
                        <v-text-field
                            v-model="item.draft.main_village"
                            density="compact"
                            variant="outlined"
                            :label="t('قریه/ناحیه اصلی')"
                            hide-details
                            :error="hasError(item, 'main_village')"
                            :error-messages="getErrorMessages(item, 'main_village')"
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
                            :error="hasError(item, 'now_province')"
                            :error-messages="getErrorMessages(item, 'now_province')"
                        />
                        <v-text-field
                            v-model="item.draft.now_district"
                            density="compact"
                            variant="outlined"
                            hide-details
                            :label="t('ولسوالی فعلی')"
                            :error="hasError(item, 'now_district')"
                            :error-messages="getErrorMessages(item, 'now_district')"
                        />
                        <v-text-field
                            v-model="item.draft.now_village"
                            density="compact"
                            variant="outlined"
                            hide-details
                            :label="t('قریه/ناحیه فعلی')"
                            :error="hasError(item, 'now_village')"
                            :error-messages="getErrorMessages(item, 'now_village')"
                        />
                    </div>
                </template>

                <!-- Studies -->
                <template #item.releg_study="{ item }">
                    <v-text-field
                        v-model="item.draft.releg_study"
                        class="text-group"
                        density="compact"
                        :label="t('تغلیمات دینی')"
                        variant="outlined"
                        hide-details
                        :error="hasError(item, 'releg_study')"
                        :error-messages="getErrorMessages(item, 'releg_study')"
                    />
                </template>
                <template #item.new_study="{ item }">
                    <v-text-field
                        v-model="item.draft.new_study"
                        class="text-group"
                        density="compact"
                        :label="t('تعلیمات عصری')"
                        variant="outlined"
                        hide-details
                        :error="hasError(item, 'new_study')"
                        :error-messages="getErrorMessages(item, 'new_study')"
                    />
                </template>

                <!-- Phone / Tazkira -->
                <template #item.phone_number="{ item }">
                    <v-text-field
                        v-model="item.draft.phone_number"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('شماره تماس')"
                        hide-details
                        :error="hasError(item, 'phone_number')"
                        :error-messages="getErrorMessages(item, 'phone_number')"
                    />
                </template>
                <template #item.code_number="{ item }">
                    <v-text-field
                        v-model="item.draft.code_number"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('مود نمبر')"
                        hide-details
                        :error="hasError(item, 'code_number')"
                        :error-messages="getErrorMessages(item, 'code_number')"
                    />
                </template>
                <template #item.tazkira="{ item }">
                    <v-text-field
                        v-model="item.draft.tazkira"
                        density="compact"
                        class="text-group"
                        variant="outlined"
                        :label="t('شماره تذکره')"
                        hide-details
                        :error="hasError(item, 'tazkira')"
                        :error-messages="getErrorMessages(item, 'tazkira')"
                    />
                </template>

                <!-- Dates -->
                <template #item.apply_date="{ item }">
                    <v-text-field
                        v-model="item.draft.apply_date"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('تاریخ مقرر شدن')"
                        hide-details
                        :error="hasError(item, 'apply_date')"
                        :error-messages="getErrorMessages(item, 'apply_date')"
                    />
                </template>
                <template #item.join_date="{ item }">
                    <v-text-field
                        v-model="item.draft.join_date"
                        class="text-group"
                        density="compact"
                        :label="t('تاریخ آمدن')"
                        variant="outlined"
                        hide-details
                        :error="hasError(item, 'join_date')"
                        :error-messages="getErrorMessages(item, 'join_date')"
                    />
                </template>
                <template #item.left_date="{ item }">
                    <v-text-field
                        v-model="item.draft.left_date"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('تاریخ رفتن')"
                        hide-details
                        :error="hasError(item, 'left_date')"
                        :error-messages="getErrorMessages(item, 'left_date')"
                    />
                </template>

                <!-- Notes -->
                <template #item.notes="{ item }">
                    <v-text-field
                        v-model="item.draft.notes"
                        density="compact"
                        class="text-group-note"
                        variant="outlined"
                        :label="t('ملاحضات')"
                        hide-details
                        :error="hasError(item, 'notes')"
                        :error-messages="getErrorMessages(item, 'notes')"
                    />
                </template>

                <!-- Actions: Save + Delete -->
                <template #item.actions="{ item }">
                    <div class="flex items-center gap-1">
                        <v-btn
                            class="bg-primary"
                            variant="outlined"
                            @click="save(item)"
                            :loading="item.loading"
                            >{{ t("ثبت دیتا") }}</v-btn
                        >
                        <v-btn
                            class="bg-error"
                            variant="outlined"
                            @click="del(item)"
                            >{{ t("حذف دیتا") }}</v-btn
                        >
                        <v-btn
                            variant="outlined"
                            class="bg-info"
                            @click="openPeek(item?.raw ?? item)"
                        >
                            {{ t("جزییات") }}
                        </v-btn>
                    </div>
                </template>

                <template #no-data>
                    <div class="py-8 text-medium-emphasis">
                        {{ t("کارمند دفتر یافت نشد لطفا اضافه کنید") }}
                    </div>
                </template>
            </v-data-table>
            <v-dialog v-model="previewOpen" max-width="520">
                <v-card>
                    <v-toolbar
                        density="compact"
                        color="primary"
                        :title="t('پیش نمایش')"
                        class="text-white"
                    />
                    <v-card-text class="d-flex justify-center">
                        <v-img
                            :src="previewSrc"
                            :alt="t('پیش نمایش')"
                            max-width="480"
                            max-height="420"
                            cover
                        />
                    </v-card-text>
                    <v-card-actions class="justify-end">
                        <v-btn
                            color="primary"
                            variant="flat"
                            @click="previewOpen = false"
                            >{{ t("بستن") }}</v-btn
                        >
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-card>
    </v-container>

    <v-container fluid class="">
        <PeekButton
            v-model="expanded"
            :id="selectedId"
            :item="detail"
            :loading="loading"
            title-key="name"
            image-key="picture"
            :fields="headers"
            :cols="3"
            dir="rtl"
            @fetch="FetchOne"
        />
    </v-container>
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

    <!-- Error Snackbar -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
        {{ errorMessage }}
        <template v-slot:actions>
            <v-btn variant="text" @click="showError = false">
                {{ t("بستن") }}
            </v-btn>
        </template>
    </v-snackbar>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useEmployeOfficeRepository } from "../../stores/EmployeeRepository";
import AppBar from "../../components/AppBar.vue";
import PeekButton from "@/components/PeekButton.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import { useI18n } from "vue-i18n";

const { t, locale } = useI18n();

const previewOpen = ref(false);
const previewSrc = ref(null);
const showError = ref(false);
const errorMessage = ref("");

function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
}

function showErrorMessage(message) {
    errorMessage.value = message;
    showError.value = true;
}

const repo = useEmployeOfficeRepository();
const rows = ref([]);
let tmpId = 0;
const newTmpId = () => `tmp-${++tmpId}`;

const isRtl = ref(locale.value === "fa");
watch(locale, (newLocale) => {
    isRtl.value = newLocale === "fa";
});

// Error handling functions
const itemErrors = ref({});

function setItemErrors(item, errors) {
    itemErrors.value[item.idOrTmp] = errors;
}

function clearItemErrors(item) {
    if (itemErrors.value[item.idOrTmp]) {
        delete itemErrors.value[item.idOrTmp];
    }
}

function hasError(item, field) {
    return !!(itemErrors.value[item.idOrTmp] && itemErrors.value[item.idOrTmp][field]);
}

function getErrorMessages(item, field) {
    if (itemErrors.value[item.idOrTmp] && itemErrors.value[item.idOrTmp][field]) {
        return itemErrors.value[item.idOrTmp][field];
    }
    return [];
}

// For Vuetify v-data-table (all keys are strings!)
const headers = computed(() => [
    { title: t("تضویر"), key: "picture", width: 120, sortable: false },
    { title: t("نام"), key: "name" },
    { title: t("نام پدر"), key: "father_name" },
    { title: t("نام مستعار"), key: "nickname" },
    { title: t("کود نمبر"), key: "code_number" },
    { title: t("آدرس اصلی"), key: "main_province" },
    { title: t("آدرس فعلی"), key: "now_province" },
    { title: t("تعلیمات دینی"), key: "releg_study" },
    { title: t("تعلیمات عصری"), key: "new_study" },
    { title: t("شماره تماس"), key: "phone_number" },
    { title: t("شماره تذکره"), key: "tazkira" },
    { title: t("تاریخ مقرر شدن"), key: "apply_date" },
    { title: t("تاریخ آمدن"), key: "join_date" },
    { title: t("تاریخ رفتن"), key: "left_date" },
    { title: t("ملاحضات"), key: "notes" },
    { title: t("عمل"), key: "actions", sortable: false },
]);

// For FilterableTable (composite keys supported)
const headersFT = computed(() => [
    { title: t("نام"), key: "name" },
    { title: t("نام پدر"), key: "father_name" },
    { title: t("نام مستعار"), key: "nickname" },
    { title: t("کود نمبر"), key: "code_number" },

    { title: t("ولایت اصلی"), key: "main_province" },
    { title: t("ولسوالی"), key: "main_district" },
    { title: t("قریه"), key: "main_village" },

    { title: t("ولایت فعلی"), key: "now_province" },
    { title: t("ولسوالی"), key: "now_district" },
    { title: t("قریه"), key: "now_village" },

    { title: t("تعلیمات دینی"), key: "releg_study" },
    { title: t("تعلیمات عصری"), key: "new_study" },
    { title: t("شماره تماس"), key: "phone_number" },
    { title: t("شماره تذکره"), key: "tazkira" },
    { title: t("تاریخ مقرر شدن"), key: "apply_date" },
    { title: t("تاریخ آمدن"), key: "join_date" },
    { title: t("تاریخ رفتن"), key: "left_date" },
    { title: t("ملاحضات"), key: "notes" },
]);

const topGroups = computed(() => [
    {
        title: t("معرفت"),
        keys: ["name", "father_name", "nickname", "code_number"],
    },
    {
        title: t("آدرس اصلی"),
        keys: ["main_province", "main_district", "main_village"],
    },
    {
        title: t("آدرس فعلی"),
        keys: ["now_province", "now_district", "now_village"],
    },
]);

const filters = ref({});

// map API -> row with a draft used by inputs
function toRowModel(x = {}) {
    const base = {
        id: x.id ?? null,
        idOrTmp: x.id ?? newTmpId(),
        picture: x.picture ?? null,
        previewUrl: x.picture ?? null,
        loading: false,

        name: x.name ?? "",
        father_name: x.father_name ?? "",
        nickname: x.nickname ?? "",

        main_province: x.main_province ?? "",
        main_district: x.main_district ?? "",
        main_village: x.main_village ?? "",

        now_province: x.now_province ?? "",
        now_district: x.now_district ?? "",
        now_village: x.now_village ?? "",

        releg_study: x.releg_study ?? "",
        new_study: x.new_study ?? "",

        phone_number: x.phone_number ?? "",
        code_number: x.code_number ?? "",
        tazkira: x.tazkira ?? "",

        apply_date: x.apply_date ?? "",
        join_date: x.join_date ?? "",
        left_date: x.left_date ?? "",
        notes: x.notes ?? "",
    };
    return { ...base, draft: { ...base, picture: null } };
}

function hydrate() {
    rows.value = (repo.Employees || []).map(toRowModel);
}

async function fetch() {
    await repo.FetchEmployeesData({
        page: repo.page || 1,
        itemsPerPage: repo.itemsPerPage || 50,
    });
    hydrate();
}

function addBlankRow() {
    const newRow = toRowModel({});
    rows.value = [...rows.value, newRow];
    const per = repo.itemsPerPage || 50;
    const totalLocal = rows.value.length;
    repo.page = Math.max(1, Math.ceil(totalLocal / per));
}

/* ===== image picker logic ===== */
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

/* ===== diff + merge ===== */
function diffDraft(item) {
    const d = item.draft || {};
    const changed = {};
    const keys = [
        "name",
        "father_name",
        "nickname",
        "main_province",
        "main_district",
        "main_village",
        "now_province",
        "now_district",
        "now_village",
        "releg_study",
        "new_study",
        "phone_number",
        "code_number",
        "tazkira",
        "apply_date",
        "join_date",
        "left_date",
        "notes",
    ];
    for (const k of keys) {
        if ((d[k] ?? "") !== (item[k] ?? "")) changed[k] = d[k];
    }
    const imageChanged = !!(d.picture && typeof d.picture === "object");
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

/* ===== Save / Delete with error handling ===== */
async function save(item) {
    if (!item.draft?.name?.trim()) {
        showErrorMessage(t("نام الزامی است"));
        return;
    }

    item.loading = true;
    clearItemErrors(item);

    try {
        // CREATE
        if (!item.id) {
            const fd = toFormDataSelective(item.draft);
            const created = await repo.CreateEmployee(fd);

            if (created && created.id) {
                const model = toRowModel(created);
                model.idOrTmp = item.idOrTmp;
                Object.assign(item, model);
                item.draft = { ...model, picture: null };
                showErrorMessage(t("کارمند با موفقیت ایجاد شد"));
            } else {
                await fetch();
            }
            return;
        }

        // UPDATE
        const { changed, imageChanged } = diffDraft(item);
        if (Object.keys(changed).length === 0) {
            showErrorMessage(t("هیچ تغییری برای ذخیره وجود ندارد"));
            return;
        }

        const fd = toFormDataSelective(changed);
        const updated = await repo.UpdateEmployee(item.id, fd);

        if (updated && updated.id) {
            const model = toRowModel(updated);
            model.idOrTmp = item.idOrTmp;
            if (!imageChanged && item._blobUrl) {
                URL.revokeObjectURL(item._blobUrl);
                item._blobUrl = null;
            }
            Object.assign(item, model);
            item.draft = { ...model, picture: null };
            showErrorMessage(t("کارمند با موفقیت بروزرسانی شد"));
        }
    } catch (error) {
        console.error("Save error:", error);

        if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            setItemErrors(item, errors);
            showErrorMessage(t("لطفا خطاهای فرم را بررسی کنید"));
        } else {
            showErrorMessage(t("خطا در ذخیره اطلاعات") + ": " + (error.message || t("خطای ناشناخته")));
        }
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
        await repo.DeleteEmployee(item.id);
        await fetch();
        showErrorMessage(t("کارمند با موفقیت حذف شد"));
    } catch (error) {
        console.error("Delete error:", error);
        showErrorMessage(t("خطا در حذف کارمند") + ": " + (error.message || t("خطای ناشناخته")));
    }
}

// peek button
const expanded = ref(false);
const selectedId = ref(null);
const loading = ref(false);
const detail = ref(null);

async function fetchOne(id) {
    loading.value = true;
    try {
        const res = await repo.FetchEmployeeData(id);
        detail.value = repo.Employee || [];
    } catch (error) {
        console.error("Fetch one error:", error);
        showErrorMessage(t("خطا در دریافت اطلاعات کارمند"));
    } finally {
        loading.value = false;
    }
}

function openPeek(payload) {
    const row = payload?.raw ?? payload;
    if (!row?.id) return;
    expanded.value = true;
    fetchOne(row.id);
}

/* ===== init ===== */
onMounted(fetch);
</script>

<style scoped>
/* ... existing styles remain unchanged ... */
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
    grid-template-columns: repeat(3, minmax(120px, 1fr));
    gap: 2px;
}
.text-group {
    display: grid;
    width: 120px;
    gap: 2px;
}
.text-group-note {
    display: grid;
    width: 400px;
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

.pic-cell {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.hidden-file {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}
.pic-thumb {
    display: inline-flex;
    cursor: pointer;
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

.addr-group,
.text-group,
.text-group-note {
    margin: -16px !important;
    padding: -10px !important;
    row-gap: -6px !important;
    column-gap: 2px !important;
    gap: -10px !important;
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
</style>
