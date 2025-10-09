<template>
    <v-container fluid class="py-6 mt-12">
        <AppBar
            :title="t('رزیدانت ها')"
            :subtitle="t('اضافه کردن یک رزیدانت')"
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
                    {{ t("اضافه کدن رزیدانت") }}
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-col>
        </v-row>

        <v-card>
            <v-data-table
                :headers="headers"
                :items="rows"
                :loading="repo.loading"
                :items-per-page="repo.itemsPerPage"
                :page="repo.page"
                :items-length="repo.totalItems"
                item-key="idOrTmp"
                class="elevation-0 custom-table"
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
                <!-- Picture cell with view/edit -->
                <template #item.picture="{ item }">
                    <div class="pic-wrap">
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

                        <v-btn
                            v-if="!item.previewUrl && !item.picture"
                            size="x-small"
                            color="primary"
                            variant="outlined"
                            icon
                            @click="triggerFile(item)"
                        >
                            <v-icon>mdi-plus</v-icon>
                        </v-btn>

                        <div v-else class="pic-thumb" :title="t('گزینه ها')">
                            <v-avatar size="32">
                                <v-img
                                    :src="item.previewUrl || item.picture"
                                    :alt="t('تصویر')"
                                    cover
                                />
                            </v-avatar>

                            <div class="pic-overlay">
                                <v-btn
                                    size="x-small"
                                    color="primary"
                                    variant="flat"
                                    @click.stop="triggerFile(item)"
                                >
                                    <v-icon start>mdi-pencil</v-icon
                                    >{{ t("ویرایش") }}
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
                                    <v-icon start>mdi-eye</v-icon
                                    >{{ t("نمایش") }}
                                </v-btn>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Basic fields -->
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
                            :error-messages="
                                getErrorMessages(item, 'main_province')
                            "
                        />
                        <v-text-field
                            v-model="item.draft.main_district"
                            density="compact"
                            variant="outlined"
                            hide-details
                            :label="t('ولسوالی اصلی')"
                            :error="hasError(item, 'main_district')"
                            :error-messages="
                                getErrorMessages(item, 'main_district')
                            "
                        />
                        <v-text-field
                            v-model="item.draft.main_village"
                            density="compact"
                            variant="outlined"
                            hide-details
                            :label="t('ناحیه/قریه اصلی')"
                            :error="hasError(item, 'main_village')"
                            :error-messages="
                                getErrorMessages(item, 'main_village')
                            "
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
                            :error-messages="
                                getErrorMessages(item, 'now_province')
                            "
                        />
                        <v-text-field
                            v-model="item.draft.now_district"
                            density="compact"
                            variant="outlined"
                            hide-details
                            :label="t('ولسوالی فعلی')"
                            :error="hasError(item, 'now_district')"
                            :error-messages="
                                getErrorMessages(item, 'now_district')
                            "
                        />
                        <v-text-field
                            v-model="item.draft.now_village"
                            density="compact"
                            variant="outlined"
                            hide-details
                            :label="t('ناحیه/قریه فعلی')"
                            :error="hasError(item, 'now_village')"
                            :error-messages="
                                getErrorMessages(item, 'now_village')
                            "
                        />
                    </div>
                </template>

                <!-- Other fields -->
                <template #item.code_number="{ item }">
                    <v-text-field
                        v-model="item.draft.code_number"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('کود نمبر')"
                        hide-details
                        :error="hasError(item, 'code_number')"
                        :error-messages="getErrorMessages(item, 'code_number')"
                    />
                </template>

                <template #item.zone="{ item }">
                    <v-text-field
                        v-model="item.draft.zone"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('زون')"
                        hide-details
                        :error="hasError(item, 'zone')"
                        :error-messages="getErrorMessages(item, 'zone')"
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
                        :error="hasError(item, 'phone_number')"
                        :error-messages="getErrorMessages(item, 'phone_number')"
                    />
                </template>

                <template #item.whatsapp_number="{ item }">
                    <v-text-field
                        v-model="item.draft.whatsapp_number"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('شماره واتساپ')"
                        hide-details
                        :error="hasError(item, 'whatsapp_number')"
                        :error-messages="
                            getErrorMessages(item, 'whatsapp_number')
                        "
                    />
                </template>

                <template #item.hawza="{ item }">
                    <v-text-field
                        v-model="item.draft.hawza"
                        class="text-group"
                        density="compact"
                        variant="outlined"
                        :label="t('حوزه')"
                        hide-details
                        :error="hasError(item, 'hawza')"
                        :error-messages="getErrorMessages(item, 'hawza')"
                    />
                </template>

                <template #item.officeemployee_id="{ item }">
                    <v-autocomplete
                        v-model="item.draft.officeemployee_id"
                        :items="repo.officeemployees || []"
                        item-title="code_number"
                        item-value="id"
                        density="compact"
                        variant="outlined"
                        class="drop-group"
                        bg-color="grey-lighten-5"
                        hide-details
                        clearable
                        :label="t('کود نمبر کارمند مدیریت مربوطه')"
                        :placeholder="t('مربوطه مدیریت کود')"
                        persistent-placeholder
                        :error="hasError(item, 'officeemployee_id')"
                        :error-messages="
                            getErrorMessages(item, 'officeemployee_id')
                        "
                    />
                </template>

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

                <!-- Actions -->
                <template #item.actions="{ item }">
                    <div class="flex items-center gap-1">
                        <v-btn
                            class="bg-primary"
                            variant="outlined"
                            @click="save(item)"
                            :loading="item.loading"
                        >
                            {{ t("ثبت دیتا") }}
                        </v-btn>
                        <v-btn
                            class="bg-error"
                            variant="outlined"
                            @click="del(item)"
                        >
                            {{ t("حذف دیتا") }}
                        </v-btn>
                        <v-btn
                            variant="outlined"
                            class="bg-info"
                            @click="openPeek(item)"
                        >
                            {{ t("جزییات") }}
                        </v-btn>
                    </div>
                </template>

                <template #no-data>
                    <div class="py-8 text-medium-emphasis">
                        {{ t("رزیدانت دریافت نشد") }}
                    </div>
                </template>
            </v-data-table>

            <!-- Image preview dialog -->
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

    <!-- Peek panel -->
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
            dir="rtl"
            @fetch="fetchOne"
        />
    </v-container>

    <!-- Filterable view -->
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
        <template #actions>
            <v-btn variant="text" @click="showError = false">{{
                t("بستن")
            }}</v-btn>
        </template>
    </v-snackbar>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import AppBar from "../../components/AppBar.vue";
import PeekButton from "@/components/PeekButton.vue";
import FilterableTable from "@/components/FilterableTable.vue";
import { useEmployeOfficeRepository } from "../../stores/EmployeeRepository";

const { t, locale } = useI18n();
const repo = useEmployeOfficeRepository();

const drawer = ref(false);
const rows = ref([]);
const filters = ref({});

const isRtl = ref(locale.value === "fa");
watch(locale, (newLocale) => {
    isRtl.value = newLocale === "fa";
});

/* preview + snackbar */
const previewOpen = ref(false);
const previewSrc = ref(null);
const showError = ref(false);
const errorMessage = ref("");
function openPreview(src) {
    previewSrc.value = src || null;
    if (previewSrc.value) previewOpen.value = true;
}
function showErrorMessage(msg) {
    errorMessage.value = msg;
    showError.value = true;
}

/* validation store per row */
const itemErrors = ref({});
function setItemErrors(item, errors) {
    itemErrors.value[item.idOrTmp] = errors;
}
function clearItemErrors(item) {
    if (itemErrors.value[item.idOrTmp]) delete itemErrors.value[item.idOrTmp];
}
function hasError(item, field) {
    return !!itemErrors.value[item.idOrTmp]?.[field];
}
function getErrorMessages(item, field) {
    return itemErrors.value[item.idOrTmp]?.[field] ?? [];
}

/* headers */
const headers = computed(() => [
    { title: t("تصویر"), key: "picture", width: 120, sortable: false },
    { title: t("نام"), key: "name" },
    { title: t("نام پدر"), key: "father_name", width: 300 },
    { title: t("نام مستعار"), key: "nickname" },
    { title: t("کود نمبر"), key: "code_number" },
    { title: t("سکونت اصلی"), key: "main_province" },
    { title: t("سکونت فعلی"), key: "now_province" },
    { title: t("شماره تماس"), key: "phone_number" },
    { title: t("شماره واتساپ"), key: "whatsapp_number" },
    { title: t("کود نمبر کارمند مدیریت مربوطه"), key: "officeemployee_id" },
    { title: t("حوزه"), key: "hawza" },
    { title: t("زون"), key: "zone" },
    { title: t("ملاحضات"), key: "notes" },
    { title: t("عمل"), key: "actions", sortable: false },
]);

/* filter headers + groups */
const headersFT = computed(() => [
    { title: t("نام"), key: "name" },
    { title: t("نام پدر"), key: "father_name" },
    { title: t("نام مستعار"), key: "nickname" },
    { title: t("کود نمبر"), key: "code_number" },

    { title: t("ولایت اصلی"), key: "main_province" },
    { title: t("ولسوالی اصلی"), key: "main_district" },
    { title: t("قریه/ناحیه اصلی"), key: "main_village" },

    { title: t("ولایت فعلی"), key: "now_province" },
    { title: t("ولسوالی فعلی"), key: "now_district" },
    { title: t("قریه/ناحیه فعلی"), key: "now_village" },

    { title: t("شماره تماس"), key: "phone_number" },
    { title: t("شماره واتساپ"), key: "whatsapp_number" },
    { title: t("حوزه"), key: "hawza" },
    { title: t("زون"), key: "zone" },
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

/* row model */
let tmpId = 0;
const newTmpId = () => `tmp-${++tmpId}`;

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

        phone_number: x.phone_number ?? "",
        whatsapp_number: x.whatsapp_number ?? "",
        hawza: x.hawza ?? "",
        zone: x.zone ?? "",
        code_number: x.code_number ?? "",
        officeemployee_id: x.officeemployee_id ?? "",
        notes: x.notes ?? "",
    };
    return { ...base, draft: { ...base, picture: null } };
}

/* fetch / hydrate */
function hydrate() {
    rows.value = (repo.Resedints || []).map(toRowModel);
}

async function fetch() {
    await repo.FetchResedintsData({
        page: repo.page || 1,
        itemsPerPage: repo.itemsPerPage || 50,
    });
    if (!repo.officeemployees?.length) {
        await repo.GetOfficeEmployee().catch(() => {});
    }
    hydrate();
}

/* add row */
function addBlankRow() {
    rows.value = [toRowModel({}), ...rows.value];
}

/* image picker */
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

/* diff + payload */
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
        "zone",
        "hawza",
        "phone_number",
        "whatsapp_number",
        "officeemployee_id",
        "code_number",
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

/* save / delete */
async function save(item) {
    if (!item.draft?.name?.trim()) {
        showErrorMessage(t("نام الزامی است"));
        return;
    }

    item.loading = true;
    clearItemErrors(item);

    try {
        // create
        if (!item.id) {
            const fd = toFormDataSelective(item.draft);
            const created = await repo.CreateResedint(fd);
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

        // update
        const { changed, imageChanged } = diffDraft(item);
        if (Object.keys(changed).length === 0) {
            return;
        }

        const fd = toFormDataSelective(changed);
        const updated = await repo.UpdateResedint(item.id, fd);
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
        console.error("Save error:", error);
        const errors = error?.response?.data?.errors;
        if (errors) {
            setItemErrors(item, errors);
            showErrorMessage(t("لطفا خطاهای فرم را بررسی کنید"));
        } else {
            showErrorMessage(
                t("خطا در ذخیره اطلاعات") +
                    ": " +
                    (error?.message || t("خطای ناشناخته"))
            );
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
    if (!confirm(t('آیا از حذف "{name}" مطمئن هستید؟', { name: item.name })))
        return;

    try {
        await repo.DeleteResedintEmployee(item.id);
        await fetch();
        showErrorMessage(t("رزیدانت حذف شد"));
    } catch (error) {
        console.error("Delete error:", error);
        showErrorMessage(
            t("خطا در حذف رزیدانت") +
                ": " +
                (error?.message || t("خطای ناشناخته"))
        );
    }
}

/* peek */
const expanded = ref(false);
const selectedId = ref(null);
const loading = ref(false);
const detail = ref(null);

async function fetchOne(id) {
    loading.value = true;
    try {
        await repo.FetchResedintData?.(id); // if available
        detail.value = repo.Resedint || [];
    } catch (e) {
        console.error("Fetch one error:", e);
        showErrorMessage(t("خطا در دریافت رزیدانت"));
    } finally {
        loading.value = false;
    }
}
function openPeek(row) {
    if (!row?.id) return;
    selectedId.value = row.id;
    expanded.value = true;
    fetchOne(row.id);
}

/* init */
onMounted(async () => {
    await repo.GetOfficeEmployee().catch(() => {});
    await fetch();
});
</script>

<style scoped>
.custom-table :deep(.v-data-table__thead th) {
    background: rgb(var(--v-theme-primary));
    color: #fff;
    font-weight: 400;
}

/* inputs */
.addr-group {
    display: grid;
    grid-template-columns: repeat(3, minmax(150px, 1fr));
    gap: 2px;
}
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
.drop-group {
    width: 250px;
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

/* picture cell + overlay */
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

/* compact fields */
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
