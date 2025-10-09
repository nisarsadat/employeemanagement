<template>
    <div :dir="rtl ? 'rtl' : 'ltr'" class="max-w-full">
        <!-- Print button (hidden when printing) -->
        <div class="mb-2 flex justify-end print:hidden">
            <button
                type="button"
                class="px-3 py-1.5 text-xs rounded bg-gray-800 text-white hover:bg-gray-700"
                @click="printTable"
            >
                چاپ جدول
            </button>
        </div>

        <!-- ✅ Only this wrapper prints -->
        <div id="print-this-table" class="bg-white shadow-md rounded-lg w-full relative">
            <!-- ✅ Horizontal scroll container -->
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border border-gray-200">
                    <colgroup>
                        <col v-if="showIndex" style="width: 64px" />
                        <col
                            v-for="h in headers"
                            :key="'col-' + colKey(h)"
                            :style="colWidth(h) ? { width: colWidth(h) } : undefined"
                        />
                    </colgroup>

                    <thead class="bg-gray-50">
                        <!-- Row 1: top groups + non-group titles -->
                        <tr>
                            <th
                                v-if="showIndex"
                                rowspan="2"
                                class="px-2 py-2 border text-center text-xs sm:text-sm align-top font-medium text-gray-700"
                            >
                                <div class="cell-wrap whitespace-nowrap">{{ indexLabel }}</div>
                            </th>

                            <template v-for="h in headers" :key="'top-' + colKey(h)">
                                <th
                                    v-if="isFirstKeyOfGroup(hKey(h))"
                                    :colspan="groupColspan(hKey(h))"
                                    class="px-2 py-2 border text-center align-top"
                                >
                                    <div
                                        class="cell-wrap whitespace-nowrap text-[12px] sm:text-xs font-semibold text-gray-700"
                                    >
                                        {{ groupByKey.get(hKey(h))?.title || "" }}
                                    </div>
                                </th>

                                <template v-else-if="coveredByGroupInRow1(hKey(h))"></template>

                                <th v-else class="px-2 py-2 border text-center align-top">
                                    <div
                                        class="cell-wrap whitespace-nowrap text-[12px] sm:text-xs font-semibold text-gray-700"
                                    >
                                        {{ h.title }}
                                    </div>
                                </th>
                            </template>
                        </tr>

                        <!-- Row 2: per-column titles + dropdowns -->
                        <tr>
                            <template v-for="h in headers" :key="'sub-' + colKey(h)">
                                <th class="px-2 py-2 border text-center align-top">
                                    <div class="cell-wrap">
                                        <div
                                            class="whitespace-nowrap text-[12px] sm:text-xs font-medium text-gray-700"
                                        >
                                            {{ h.title }}
                                        </div>

                                        <div
                                            v-if="dropdownSet.has(hKey(h)) && showFilterFor(hKey(h))"
                                            class="w-full mt-1 relative print:hidden"
                                        >
                                            <!-- Trigger (placeholder only; no selected chips) -->
                                            <button
                                                type="button"
                                                :ref="(el) => (triggerRefs[hKey(h) || ''] = el)"
                                                class="w-full inline-flex items-center gap-2 border border-gray-300 hover:border-gray-400 focus:border-gray-500 focus:ring-2 focus:ring-gray-200 rounded-md px-2 py-1.5 text-[11px] sm:text-xs bg-white transition"
                                                :aria-expanded="openDropdown === hKey(h)"
                                                :aria-haspopup="true"
                                                @click="openDropdownAt(hKey(h))"
                                            >
                                                <span class="flex-1 min-w-0 text-left truncate text-gray-700">
                                                    {{ allLabel }}
                                                </span>
                                                <span
                                                    class="text-[10px] shrink-0 transition-transform"
                                                    :class="{ 'rotate-180': openDropdown === hKey(h) }"
                                                    aria-hidden="true"
                                                    >▼</span
                                                >
                                            </button>

                                            <!-- Panel inside a fixed overlay (not clipped; correct coords) -->
                                            <Teleport to="body">
                                                <div
                                                    v-show="openDropdown === hKey(h)"
                                                    ref="overlayEl"
                                                    class="fixed inset-0 z-[9999] pointer-events-none"
                                                    @keydown.esc.prevent.stop="closeDropdown"
                                                >
                                                    <div
                                                        ref="panelEl"
                                                        :style="panelStyle"
                                                        class="absolute pointer-events-auto bg-white border border-gray-200 shadow-2xl rounded-lg overflow-hidden"
                                                        role="dialog"
                                                        aria-modal="true"
                                                    >
                                                        <!-- Sticky tools -->
                                                        <div
                                                            class="sticky top-0 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/75 border-b border-gray-100"
                                                        >
                                                            <div class="px-3 pt-2 pb-2 flex flex-col gap-2">
                                                                <input
                                                                    :placeholder="searchPlaceholder"
                                                                    v-model.trim="dropdownQuery[openDropdown || '']"
                                                                    class="w-full border border-gray-300 focus:border-gray-500 rounded-md px-2 py-1.5 text-[12px] outline-none"
                                                                    type="text"
                                                                    @keydown.stop
                                                                />
                                                                <div class="flex gap-2">
                                                                    <button
                                                                        class="flex-1 text-[11px] px-2 py-1 rounded-md border border-gray-300 hover:bg-gray-50"
                                                                        @click="clearAll(openDropdown || '')"
                                                                        type="button"
                                                                    >
                                                                        {{ btnClear }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="px-3 pb-2 text-[11px] text-gray-500">
                                                                {{
                                                                    countLabel(
                                                                        (localFilters[openDropdown || ''] || [])
                                                                            .length,
                                                                        getFilteredOptions(openDropdown).length
                                                                    )
                                                                }}
                                                            </div>
                                                        </div>

                                                        <!-- Options (stays open while selecting) -->
                                                        <div class="p-2 overflow-auto" :style="{ maxHeight: '60vh' }">
                                                            <div
                                                                v-if="getFilteredOptions(openDropdown).length === 0"
                                                                class="text-center text-[12px] text-gray-500 py-6"
                                                            >
                                                                {{ noResults }}
                                                            </div>

                                                            <label
                                                                v-for="opt in getFilteredOptions(openDropdown)"
                                                                :key="opt || '__EMPTY__'"
                                                                class="flex items-center gap-3 px-2 py-2 rounded-md hover:bg-gray-50 cursor-pointer select-none"
                                                                @click.stop
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    :value="opt"
                                                                    v-model="localFilters[openDropdown || '']"
                                                                    class="h-4 w-4 rounded border-gray-300 focus:ring-2 focus:ring-gray-200"
                                                                />
                                                                <span class="text-[12px] text-gray-800 break-all">
                                                                    {{ opt || placeholderEmpty }}
                                                                </span>
                                                            </label>
                                                        </div>

                                                        <!-- Footer -->
                                                        <div
                                                            class="border-t border-gray-100 px-3 py-2 flex items-center justify-between"
                                                        >
                                                            <div class="text-[11px] text-gray-500">
                                                                {{ footerHint }}
                                                            </div>
                                                            <button
                                                                type="button"
                                                                class="text-[11px] px-3 py-1.5 rounded-md bg-gray-900 text-white hover:bg-gray-800"
                                                                @click="closeDropdown"
                                                            >
                                                                {{ btnDone }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </Teleport>

                                            <!-- Printed value -->
                                            <div class="hidden print:block text-[10px] text-gray-600 mt-0.5">
                                                {{ getPrintedFilterValue(hKey(h)) }}
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(row, i) in filteredRows"
                            :key="row?.[rowKey] ?? i"
                            class="hover:bg-gray-50 transition print:hover:bg-transparent"
                        >
                            <td
                                v-if="showIndex"
                                class="px-2 py-2 border text-center align-top text-xs sm:text-sm text-gray-700"
                            >
                                {{ i + 1 }}
                            </td>

                            <td
                                v-for="h in headers"
                                :key="'cell-' + colKey(h) + '-' + (row?.[rowKey] ?? i)"
                                class="px-2 py-2 border align-top whitespace-pre-wrap break-words text-xs sm:text-sm text-gray-800 text-center"
                            >
                                <template v-if="$slots['cell-' + colKey(h)]">
                                    <slot :name="'cell-' + colKey(h)" :row="row" />
                                </template>

                                <template v-else>
                                    <div v-if="!isComposite(h)">
                                        {{ row[h.key] }}
                                    </div>
                                    <div v-else class="space-y-0.5">
                                        <div v-for="sub in h.key" :key="'v-' + sub">
                                            {{ row[sub] }}
                                        </div>
                                    </div>
                                </template>
                            </td>
                        </tr>

                        <tr v-if="filteredRows.length === 0">
                            <td
                                :colspan="(showIndex ? 1 : 0) + headers.length"
                                class="text-center py-5 text-gray-500 text-sm"
                            >
                                نتیجه‌ای یافت نشد
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from "vue";

/* === Props (runtime) === */
const props = defineProps({
    headers: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    filters: { type: Object, default: null },
    rtl: { type: Boolean, default: true },
    showIndex: { type: Boolean, default: true },
    indexLabel: { type: String, default: "شماره" },
    rowKey: { type: String, default: "idOrTmp" },
    dropdownKeys: { type: Array, default: () => [] },
    optionsMap: { type: Object, default: null },
    includeEmptyOption: { type: Boolean, default: true },
    allLabel: { type: String, default: "همه" },
    topGroups: { type: Array, default: () => [] },
});
const emit = defineEmits(["update:filters"]);

/* === Texts === */
const allLabel = computed(() => props.allLabel ?? "همه");
const searchPlaceholder = "جستجو…";
const noResults = "نتیجه‌ای پیدا نشد";
const btnClear = "پاک کردن";
const btnDone = "انجام شد";
const footerHint = "برای بستن انجام شد را بزنید ";
const placeholderEmpty = "(خالی)";

/* === Defaults === */
const rows = computed(() => props.rows ?? []);
const rtl = computed(() => props.rtl ?? true);
const showIndex = computed(() => props.showIndex ?? true);
const indexLabel = computed(() => props.indexLabel ?? "شماره");
const rowKey = computed(() => props.rowKey ?? "idOrTmp");
const includeEmptyOption = computed(() => props.includeEmptyOption ?? true);

/* === Dropdown open/close + overlay positioning === */
const openDropdown = ref(null); // string | null
const dropdownQuery = ref({}); // { [key]: string }
const triggerRefs = ref({}); // { [key]: HTMLElement | null }
const panelEl = ref(null);
const overlayEl = ref(null);
const panelStyle = ref({ display: "none" });

function openDropdownAt(key) {
    if (!key) return;
    if (openDropdown.value === key) {
        closeDropdown();
        return;
    }
    openDropdown.value = key;
    if (dropdownQuery.value[key] == null) dropdownQuery.value[key] = "";
    nextTick(() => positionPanelFor(key));
}

function closeDropdown() {
    openDropdown.value = null;
    panelStyle.value = { display: "none" };
}

/* Always open ABOVE the trigger */
function positionPanelFor(key) {
    if (!key) return;
    const trigger = triggerRefs.value[key];
    if (!trigger || !overlayEl.value) return;

    const rect = trigger.getBoundingClientRect();
    const vw = window.innerWidth;
    const gap = 8;
    const isRTL = rtl.value;
    const topPx = rect.top - gap; // dropdown’s bottom aligns with trigger’s top

    const base = {
        display: "block",
        position: "absolute",
        bottom: `calc(100vh - ${topPx}px)`,
        maxHeight: "60vh",
        minWidth: `${Math.max(rect.width, 220)}px`,
    };

    panelStyle.value = isRTL
        ? { ...base, right: `${vw - rect.right}px`, left: "" }
        : { ...base, left: `${rect.left}px`, right: "" };
}

function handleReposition() {
    if (openDropdown.value) positionPanelFor(openDropdown.value);
}

onMounted(() => {
    window.addEventListener("scroll", handleReposition, true);
    window.addEventListener("resize", handleReposition);
    document.addEventListener("click", handleClickOutsideDoc);
});
onUnmounted(() => {
    window.removeEventListener("scroll", handleReposition, true);
    window.removeEventListener("resize", handleReposition);
    document.removeEventListener("click", handleClickOutsideDoc);
});

/* === Header helpers === */
const isComposite = (h) => Array.isArray(h?.key);
const colKey = (h) => (isComposite(h) ? h.key.join("__") : h.key);
const hKey = (h) => (isComposite(h) ? null : h.key);

/* === Grouping maps === */
const groupByKey = computed(() => {
    const map = new Map();
    (props.topGroups ?? []).forEach((g) => (g.keys ?? []).forEach((k) => map.set(k, g)));
    return map;
});
const groupedKeys = computed(() => new Set([...groupByKey.value.keys()]));

function isFirstKeyOfGroup(key) {
    if (!key) return false;
    const grp = groupByKey.value.get(key);
    if (!grp) return false;
    for (const h of props.headers) {
        const k = hKey(h);
        if (k && grp.keys.includes(k)) return k === key;
    }
    return false;
}

function groupColspan(key) {
    if (!key) return 1;
    const grp = groupByKey.value.get(key);
    if (!grp) return 1;
    const keysInOrder = props.headers.map((h) => hKey(h)).filter(Boolean);
    return grp.keys.filter((k) => keysInOrder.includes(k)).length || 1;
}

function coveredByGroupInRow1(key) {
    if (!key) return false;
    if (!groupedKeys.value.has(key)) return false;
    return !isFirstKeyOfGroup(key);
}

/* Which keys have dropdowns? */
const dropdownSet = computed(() => {
    if (props.dropdownKeys && props.dropdownKeys.length) return new Set(props.dropdownKeys);
    const set = new Set();
    props.headers.forEach((h) => {
        if (isComposite(h)) return;
        if (h.key && !["picture", "actions"].includes(h.key)) set.add(h.key);
    });
    return set;
});
const showFilterFor = (_key) => true;

/* === Filters state with feedback-loop guard === */
const localFilters = ref({});

function initEmptyFilters() {
    const obj = {};
    props.headers.forEach((h) => {
        if (!isComposite(h) && h.key && dropdownSet.value.has(h.key)) {
            obj[h.key] = [];
        }
    });
    return obj;
}

function deepEqual(a, b) {
    try {
        return JSON.stringify(a) === JSON.stringify(b);
    } catch {
        return false;
    }
}

let updatingFromParent = false;

watch(
    () => props.filters,
    (incoming) => {
        updatingFromParent = true;

        if (incoming && Object.keys(incoming).length) {
            const normalized = {};
            for (const k in incoming) {
                const v = incoming[k];
                normalized[k] = Array.isArray(v) ? v.slice() : v ? [v] : [];
            }
            if (!deepEqual(localFilters.value, normalized)) {
                localFilters.value = normalized;
            }
        } else {
            const empty = initEmptyFilters();
            if (!deepEqual(localFilters.value, empty)) {
                localFilters.value = empty;
            }
        }

        nextTick(() => {
            updatingFromParent = false;
        });
    },
    { immediate: true, deep: false }
);

watch(
    localFilters,
    (v) => {
        if (updatingFromParent) return;
        if (!deepEqual(v, props.filters)) {
            emit("update:filters", JSON.parse(JSON.stringify(v)));
        }
    },
    { deep: true, flush: "post" }
);

/* === Options & in-panel filtering === */
const autoOptions = computed(() => {
    const map = {};
    const add = (k) => {
        const set = new Set();
        rows.value.forEach((r) => {
            const raw = r?.[k];
            const v = (raw ?? "").toString().trim();
            if (v) set.add(v);
            else if (includeEmptyOption.value) set.add("");
        });
        map[k] = Array.from(set).sort((a, b) => a.localeCompare(b));
    };
    props.headers.forEach((h) => {
        if (!isComposite(h)) {
            if (h.key && !["picture", "actions"].includes(h.key)) add(h.key);
        }
    });
    return map;
});

const getOptions = (key) =>
    props.optionsMap && props.optionsMap[key] && props.optionsMap[key].length
        ? props.optionsMap[key]
        : autoOptions.value[key] || [];

function getFilteredOptions(key) {
    if (!key) return [];
    const q = (dropdownQuery.value[key] || "").toLowerCase();
    const list = getOptions(key);
    if (!q) return list;
    return list.filter((o) => (o || "").toLowerCase().includes(q));
}

function clearAll(key) {
    if (!key) return;
    localFilters.value = { ...localFilters.value, [key]: [] };
}

function countLabel(sel, tot) {
    return sel === 0 ? `0 از ${tot} انتخاب شده` : `${sel} از ${tot} انتخاب شده`;
}

/* === Column widths === */
function colWidth(h) {
    const w = h && h.width;
    if (typeof w === "number") return `${w}px`;
    if (typeof w === "string") return /^\d+$/.test(w) ? `${w}px` : w;
    return null;
}

/* === Table filtering (applies selections) === */
const filteredRows = computed(() => {
    const f = localFilters.value || {};
    return rows.value.filter((row) => {
        for (const h of props.headers) {
            if (!isComposite(h)) {
                const k = h.key;
                if (!k || k === "actions" || k === "picture") continue;
                if (dropdownSet.value.has(k)) {
                    const selectedValues = f[k];
                    if (!selectedValues || selectedValues.length === 0) continue;
                    const rowValue = (row?.[k] ?? "").toString();
                    if (!selectedValues.includes(rowValue)) return false;
                }
            }
        }
        return true;
    });
});

/* === Print helper === */
function getPrintedFilterValue(key) {
    if (!key) return allLabel.value;
    const values = localFilters.value[key];
    if (!values || values.length === 0) return allLabel.value;
    return values.map((v) => (v === "" ? placeholderEmpty : v)).join("، ");
}

// --- click outside
function handleClickOutsideDoc(e) {
    if (!openDropdown.value) return;
    const t = e.target;
    const key = openDropdown.value;
    const trigger = triggerRefs.value[key];

    const p = panelEl.value;
    const panelContainsTarget = Array.isArray(p)
        ? p.some((el) => el && typeof el.contains === "function" && el.contains(t))
        : !!(p && typeof p.contains === "function" && p.contains(t));

    const insideTrigger = !!(trigger && typeof trigger.contains === "function" && trigger.contains(t));

    if (!panelContainsTarget && !insideTrigger) closeDropdown();
}

function printTable() {
    window.print();
}
</script>

<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #print-this-table,
    #print-this-table * {
        visibility: visible !important;
    }

    #print-this-table {
        position: fixed;
        inset: 0;
        margin: 0;
        padding: 8mm;
        width: auto;
        height: auto;
        box-shadow: none !important;
        background: white !important;
    }

    thead {
        display: table-header-group;
    }
    tfoot {
        display: table-footer-group;
    }
    tr,
    img {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

/* helpers */
.cell-wrap {
    word-break: break-word;
    white-space: normal;
}
</style>
