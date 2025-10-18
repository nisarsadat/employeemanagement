<template>
  <div :dir="rtl ? 'rtl' : 'ltr'" class="max-w-full">
    <!-- Toolbar (hidden when printing) -->
    <div class="mb-2 flex flex-wrap gap-2 justify-end print:hidden">
      <button
        type="button"
        class="px-3 py-1.5 text-xs rounded bg-gray-800 text-white hover:bg-gray-700"
        @click="printAll"
      >
        چاپ همه
      </button>

      <!-- Print selected ROWS -->
      <button
        type="button"
        class="px-3 py-1.5 text-xs rounded bg-gray-800 text-white hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="selectedCount === 0"
        @click="printSelected"
      >
        چاپ ردیف‌های انتخاب‌شده
      </button>

      <!-- Print selected COLUMNS -->
      <button
        type="button"
        class="px-3 py-1.5 text-xs rounded bg-indigo-600 text-white hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="selectedColsCount === 0"
        @click="printSelectedColumns"
      >
        چاپ ستون‌های انتخابی
      </button>

      <button
        type="button"
        class="px-3 py-1.5 text-xs rounded bg-gray-900 text-white hover:bg-gray-800"
        @click="downloadPdf"
      >
        دانلود PDF (براساس انتخاب)
      </button>

      <button
        type="button"
        class="px-3 py-1.5 text-xs rounded bg-emerald-600 text-white hover:bg-emerald-500"
        @click="downloadExcel"
      >
        دانلود Excel/CSV (براساس انتخاب)
      </button>
    </div>

    <!-- ✅ Only this wrapper prints -->
    <div id="print-this-table" class="bg-white shadow-md rounded-lg w-full relative">
      <div class="overflow-x-auto">
        <table class="min-w-full table-auto border border-gray-200">
          <colgroup>
            <col v-if="selectable" style="width: 40px" />
            <col v-if="showIndex" style="width: 64px" />
            <col
              v-for="h in headers"
              :key="'col-' + colKey(h)"
              :style="colWidth(h) ? { width: colWidth(h) } : undefined"
            />
            <!-- Actions column width -->
            <col v-if="showActions" style="width: 120px" />
          </colgroup>

          <thead class="bg-gray-50">
            <!-- Row 1 -->
            <tr>
              <!-- Select column -->
              <th
                v-if="selectable"
                rowspan="2"
                class="px-2 py-2 border text-center align-top"
                :class="{ 'print-col-hidden': printOnlyCols && !selectedCols.has(SELECT_KEY) }"
              >
                <div class="flex items-center justify-center gap-2">
                  <input
                    type="checkbox"
                    :checked="allVisibleSelected"
                    @change="toggleSelectAllVisible($event.target.checked)"
                    class="align-middle"
                    :title="'انتخاب همهٔ ردیف‌ها'"
                  />
                  <input
                    type="checkbox"
                    class="print:hidden h-4 w-4"
                    :checked="selectedCols.has(SELECT_KEY)"
                    @change="toggleCol(SELECT_KEY, $event.target.checked)"
                    :title="'نمایش ستون انتخاب در چاپ ستون‌های انتخابی'"
                  />
                </div>
              </th>

              <!-- Index column -->
              <th
                v-if="showIndex"
                rowspan="2"
                class="px-2 py-2 border text-center text-xs sm:text-sm align-top font-medium text-gray-700"
                :class="{ 'print-col-hidden': printOnlyCols && !selectedCols.has(INDEX_KEY) }"
              >
                <div class="flex items-center justify-center gap-2">
                  <div class="cell-wrap whitespace-nowrap">{{ indexLabel }}</div>
                  <input
                    type="checkbox"
                    class="print:hidden h-4 w-4"
                    :checked="selectedCols.has(INDEX_KEY)"
                    @change="toggleCol(INDEX_KEY, $event.target.checked)"
                    :title="'نمایش ستون ردیف در چاپ ستون‌های انتخابی'"
                  />
                </div>
              </th>

              <!-- Group or single headers -->
              <template v-for="h in headers" :key="'top-' + colKey(h)">
                <th
                  v-if="isFirstKeyOfGroup(hKey(h))"
                  :colspan="groupColspan(hKey(h))"
                  class="px-2 py-2 border text-center align-top"
                  :class="{ 'print-col-hidden': printOnlyCols && !groupHasAnySelected(hKey(h)) }"
                >
                  <div
                    class="cell-wrap whitespace-nowrap text-[12px] sm:text-xs font-semibold text-gray-700"
                  >
                    {{ groupByKey.get(hKey(h))?.title || '' }}
                  </div>
                </th>

                <template v-else-if="coveredByGroupInRow1(hKey(h))"></template>

                <th
                  v-else
                  class="px-2 py-2 border text-center align-top"
                  :class="{ 'print-col-hidden': printOnlyCols && !isColKeySelected(hKey(h)) }"
                >
                  <div
                    class="cell-wrap whitespace-nowrap text-[12px] sm:text-xs font-semibold text-gray-700"
                  >
                    {{ h.title }}
                  </div>
                </th>
              </template>

              <!-- Actions header (not printed) -->
              <th
                v-if="showActions"
                rowspan="2"
                class="px-2 py-2 border text-center align-top print:hidden"
              >
                عملیات
              </th>
            </tr>

            <!-- Row 2 -->
            <tr>
              <template v-for="h in headers" :key="'sub-' + colKey(h)">
                <th
                  class="px-2 py-2 border text-center align-top"
                  :class="{ 'print-col-hidden': printOnlyCols && !isColKeySelected(hKey(h)) }"
                >
                  <div class="cell-wrap">
                    <div class="flex items-center justify-center gap-2">
                      <div
                        class="whitespace-nowrap text-[12px] sm:text-xs font-medium text-gray-700"
                      >
                        {{ h.title }}
                      </div>

                      <!-- Column checkbox -->
                      <input
                        v-if="!isComposite(h) && hKey(h)"
                        type="checkbox"
                        class="print:hidden h-4 w-4 align-middle"
                        :checked="selectedCols.has(hKey(h))"
                        @change="toggleCol(hKey(h), $event.target.checked)"
                        :title="'انتخاب ستون: ' + (h.title || hKey(h))"
                      />
                    </div>

                    <!-- Filter dropdown -->
                    <div
                      v-if="dropdownSet.has(hKey(h)) && showFilterFor(hKey(h))"
                      class="w-full mt-1 relative print:hidden"
                    >
                      <button
                        type="button"
                        :ref="(el) => (triggerRefs[hKey(h) || ''] = el)"
                        class="w-full inline-flex items-center gap-2 border border-gray-300 hover:border-gray-400 focus:border-gray-500 focus:ring-2 focus:ring-gray-200 rounded-md px-2 py-1.5 text-[11px] sm:text-xs bg-white transition"
                        :aria-expanded="openDropdown === hKey(h)"
                        :aria-haspopup="true"
                        @click="openDropdownAt(hKey(h))"
                      >
                        <span class="flex-1 min-w-0 text-left truncate text-gray-700">{{
                          allLabel
                        }}</span>
                        <span
                          class="text-[10px] shrink-0 transition-transform"
                          :class="{ 'rotate-180': openDropdown === hKey(h) }"
                          >▼</span
                        >
                      </button>

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

                              <!-- SAFE counts -->
                              <div class="px-3 pb-2 text-[11px] text-gray-500">
                                {{
                                  countLabel(
                                    (localFilters[openDropdown || ''] || []).length,
                                    getFilteredOptionsCount(openDropdown || '')
                                  )
                                }}
                              </div>
                            </div>

                            <!-- Options -->
                            <div class="p-2 overflow-auto" :style="{ maxHeight: '60vh' }">
                              <div
                                v-if="getFilteredOptionsCount(openDropdown || '') === 0"
                                class="text-center text-[12px] text-gray-500 py-6"
                              >
                                {{ noResults }}
                              </div>

                              <label
                                v-for="opt in getFilteredOptions(openDropdown || '')"
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

                            <div
                              class="border-t border-gray-100 px-3 py-2 flex items-center justify-between"
                            >
                              <div class="text-[11px] text-gray-500">{{ footerHint }}</div>
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
              :class="{ 'print-hidden': printOnlySelected && !selectedSet.has(row[rowKey]) }"
            >
              <!-- select checkbox cell -->
              <td
                v-if="selectable"
                class="px-2 py-2 border text-center align-top"
                :class="{ 'print-col-hidden': printOnlyCols && !selectedCols.has(SELECT_KEY) }"
              >
                <input
                  type="checkbox"
                  :value="row[rowKey]"
                  :checked="selectedSet.has(row[rowKey])"
                  @change="toggleRow(row[rowKey], $event.target.checked)"
                />
              </td>

              <!-- index cell -->
              <td
                v-if="showIndex"
                class="px-2 py-2 border text-center align-top text-xs sm:text-sm text-gray-700"
                :class="{ 'print-col-hidden': printOnlyCols && !selectedCols.has(INDEX_KEY) }"
              >
                {{ i + 1 }}
              </td>

              <!-- data cells -->
              <td
                v-for="h in headers"
                :key="'cell-' + colKey(h) + '-' + (row?.[rowKey] ?? i)"
                class="px-2 py-2 border align-top whitespace-pre-wrap break-words text-xs sm:text-sm text-gray-800 text-center"
                :class="{ 'print-col-hidden': printOnlyCols && !isColKeySelected(hKey(h)) }"
              >
                <template v-if="$slots['cell-' + colKey(h)]">
                  <slot :name="'cell-' + colKey(h)" :row="row" />
                </template>

                <!-- 🟢 Replace everything below with our formatted block -->
                <template v-else>
                  <div v-if="!isComposite(h)">
                    {{ displayValueByKey(h.key, row[h.key]) }}
                  </div>
                  <div v-else class="space-y-0.5">
                    <div v-for="sub in h.key" :key="'v-' + sub">
                      {{ displayValueByKey(sub, row[sub]) }}
                    </div>
                  </div>
                </template>
              </td>

              <!-- Actions cell -->
              <td v-if="showActions" class="px-2 py-2 border text-center align-top print:hidden">
                <div class="inline-flex gap-2">
                  <button
                    v-if="enablePeek"
                    type="button"
                    class="px-2.5 py-1 text-xs rounded bg-slate-700 text-white hover:bg-slate-600"
                    @click="$emit('peek', row)"
                    title="نمایش"
                  >
                    مشاهده
                  </button>

                  <button
                    v-if="enableEdit"
                    type="button"
                    class="px-2.5 py-1 text-xs rounded bg-blue-600 text-white hover:bg-blue-500"
                    @click="$emit('edit', row)"
                    title="ویرایش"
                  >
                    ویرایش
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredRows.length === 0">
              <td
                :colspan="
                  (selectable ? 1 : 0) +
                  (showIndex ? 1 : 0) +
                  headers.length +
                  (showActions ? 1 : 0)
                "
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
  import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue';

  /* === Props === */
  const props = defineProps({
    headers: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    filters: { type: Object, default: null },
    rtl: { type: Boolean, default: true },
    showIndex: { type: Boolean, default: true },
    rowKey: { type: String, default: 'idOrTmp' },
    dropdownKeys: { type: Array, default: () => [] },
    optionsMap: { type: Object, default: null },
    includeEmptyOption: { type: Boolean, default: true },
    allLabel: { type: String, default: 'همه' },
    topGroups: { type: Array, default: () => [] },
    selectable: { type: Boolean, default: true },
    enableEdit: { type: Boolean, default: true },
    enablePeek: { type: Boolean, default: true },
    dateKeys: { type: Array, default: () => [] }, // manual list (optional)
    autoDetectDates: { type: Boolean, default: true },
  });

  /* === Emits === */
  const emit = defineEmits(['update:filters', 'edit', 'peek']);
  // date part
  import { useCalendar } from '../../composables/useCalendar';
  import dayjs from 'dayjs';

  const { formatDisplay } = useCalendar();

  // convert Persian/Arabic digits → ASCII
  function toAsciiDigits(s = '') {
    const map = {
      '۰': '0',
      '۱': '1',
      '۲': '2',
      '۳': '3',
      '۴': '4',
      '۵': '5',
      '۶': '6',
      '۷': '7',
      '۸': '8',
      '۹': '9',
      '٠': '0',
      '١': '1',
      '٢': '2',
      '٣': '3',
      '٤': '4',
      '٥': '5',
      '٦': '6',
      '٧': '7',
      '٨': '8',
      '٩': '9',
    };
    return String(s).replace(/[۰-۹٠-٩]/g, (d) => map[d] ?? d);
  }

  // normalize many common inputs to ISO (YYYY-MM-DD)
  function normalizeToIso(val) {
    if (val == null) return '';
    let s = toAsciiDigits(String(val).trim()).replace(/\./g, '-').replace(/\//g, '-');
    // exact ISO
    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
    // yyyy-m-d
    let m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
    if (m) return `${m[1]}-${m[2].padStart(2, '0')}-${m[3].padStart(2, '0')}`;
    // yyyy m d (spaces)
    m = s.match(/^(\d{4})\s+(\d{1,2})\s+(\d{1,2})$/);
    if (m) return `${m[1]}-${m[2].padStart(2, '0')}-${m[3].padStart(2, '0')}`;
    // fallback
    const d = dayjs(s);
    return d.isValid() ? d.format('YYYY-MM-DD') : '';
  }

  // HEURISTICS: does key look like a date field?
  function looksLikeDateKey(key = '') {
    if (!key) return false;
    const k = key.toLowerCase();
    return /(date|dob|birth|joined|left|hired|apply|start|end|expiry|expire|issued|issue)/.test(k);
  }

  // HEURISTICS: does value look like a date?
  function looksLikeDateValue(val) {
    if (val == null) return false;
    // Don’t even try if it’s a pure number (like 123234)
    if (typeof val === 'number') return false;

    const s = toAsciiDigits(String(val).trim());

    // Must have separators (YYYY/MM/DD or YYYY-MM-DD), not raw digits
    const m = s.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
    if (!m) return false;

    const year = +m[1],
      month = +m[2],
      day = +m[3];
    if (month < 1 || month > 12) return false;
    if (day < 1 || day > 31) return false;

    // Basic sanity for common calendars (tune if you want):
    // Accept wide range so Jalali/Hijri like 1404/07/25 also pass
    if (year < 1000 || year > 2200) return false;

    return true;
  }

  // Decide if a cell should be treated as a date (by key OR by value)
  function isDateCell(key, value) {
    if (Array.isArray(props.dateKeys) && props.dateKeys.includes(key)) return true;
    if (!props.autoDetectDates) return false;
    return looksLikeDateKey(key) || looksLikeDateValue(value);
  }

  // MAIN: value shown in UI/filters/print/CSV
  function displayValueByKey(key, rawValue) {
    // 🔒 Do NOT format numbers (or digit-only strings) as dates
    if (typeof rawValue === 'number') return rawValue;
    if (typeof rawValue === 'string' && /^[0-9]+$/.test(toAsciiDigits(rawValue))) return rawValue;

    if (!isDateCell(key, rawValue)) return rawValue;
    const iso = normalizeToIso(rawValue);
    return iso ? formatDisplay(iso) : rawValue;
  }

  const autoOptions = computed(() => {
    const map = {};
    const add = (k) => {
      const set = new Set();
      rows.value.forEach((r) => {
        const raw = r?.[k];
        const disp = displayValueByKey(k, raw); // ⬅️ use formatted value for options
        const v = (disp ?? '').toString().trim();
        if (v) set.add(v);
        else if (includeEmptyOption.value) set.add('');
      });
      map[k] = Array.from(set).sort((a, b) => a.localeCompare(b));
    };
    props.headers.forEach((h) => {
      if (!isComposite(h)) {
        if (h.key && !['picture', 'actions'].includes(h.key)) add(h.key);
      }
    });
    return map;
  });
  const filteredRows = computed(() => {
    const f = localFilters.value || {};
    return rows.value.filter((row) => {
      for (const h of props.headers) {
        if (!isComposite(h)) {
          const k = h.key;
          if (!k || k === 'actions' || k === 'picture') continue;
          if (dropdownSet.value.has(k)) {
            const selectedValues = f[k];
            if (!selectedValues || selectedValues.length === 0) continue;
            const rowDisplay = displayValueByKey(k, row?.[k]) || '';
            if (!selectedValues.includes(rowDisplay)) return false;
          }
        }
      }
      return true;
    });
  });

  /* === Texts === */
  const allLabel = computed(() => props.allLabel ?? 'همه');
  const indexLabel = 'ردیف';
  const searchPlaceholder = 'جستجو…';
  const noResults = 'نتیجه‌ای پیدا نشد';
  const btnClear = 'پاک کردن';
  const btnDone = 'انجام شد';
  const footerHint = 'برای بستن انجام شد را بزنید ';
  const placeholderEmpty = '(خالی)';

  /* === Basics === */
  const rows = computed(() => props.rows ?? []);
  const rtl = computed(() => props.rtl ?? true);
  const showIndex = computed(() => props.showIndex ?? true);
  const rowKey = computed(() => props.rowKey ?? 'idOrTmp');
  const includeEmptyOption = computed(() => props.includeEmptyOption ?? true);
  const selectable = computed(() => props.selectable !== false);

  /* Actions column visibility */
  const showActions = computed(() => props.enableEdit || props.enablePeek);

  /* Keys for special columns */
  const INDEX_KEY = '__index__';
  const SELECT_KEY = '__select__';

  /* === ROW selection === */
  const selectedSet = ref(new Set());
  const selectedCount = computed(() => selectedSet.value.size);
  function toggleRow(id, checked) {
    if (checked) selectedSet.value.add(id);
    else selectedSet.value.delete(id);
  }
  const allVisibleSelected = computed(() => {
    const list = filteredRows.value.map((r) => r[rowKey.value]);
    if (list.length === 0) return false;
    return list.every((id) => selectedSet.value.has(id));
  });
  function toggleSelectAllVisible(checked) {
    const list = filteredRows.value.map((r) => r[rowKey.value]);
    if (checked) list.forEach((id) => selectedSet.value.add(id));
    else list.forEach((id) => selectedSet.value.delete(id));
  }

  /* === COLUMN selection === */
  const selectedCols = ref(new Set());
  const selectedColsCount = computed(() => selectedCols.value.size);
  function toggleCol(key, checked) {
    if (!key) return;
    checked ? selectedCols.value.add(key) : selectedCols.value.delete(key);
  }
  function isColKeySelected(key) {
    return !printOnlyCols.value ? true : !!(key && selectedCols.value.has(key));
  }
  function groupHasAnySelected(firstKey) {
    if (!printOnlyCols.value) return true;
    if (!firstKey) return false;
    const grp = groupByKey.value.get(firstKey);
    if (!grp) return selectedCols.value.has(firstKey);
    return (grp.keys || []).some((k) => selectedCols.value.has(k));
  }

  /* === Dropdown state === */
  const openDropdown = ref(null);
  const dropdownQuery = ref({});
  const triggerRefs = ref({});
  const panelEl = ref(null);
  const overlayEl = ref(null);
  const panelStyle = ref({ display: 'none' });

  function openDropdownAt(key) {
    if (!key) return;
    if (openDropdown.value === key) {
      closeDropdown();
      return;
    }
    openDropdown.value = key;
    if (dropdownQuery.value[key] == null) dropdownQuery.value[key] = '';
    nextTick(() => positionPanelFor(key));
  }
  function closeDropdown() {
    openDropdown.value = null;
    panelStyle.value = { display: 'none' };
  }

  /* Position panel (open ABOVE) */
  function positionPanelFor(key) {
    if (!key) return;
    const trigger = triggerRefs.value[key];
    if (!trigger || !overlayEl.value) return;
    const rect = trigger.getBoundingClientRect();
    const vw = window.innerWidth;
    const gap = 8;
    const isRTL = rtl.value;
    const topPx = rect.top - gap;
    const base = {
      display: 'block',
      position: 'absolute',
      bottom: `calc(100vh - ${topPx}px)`,
      maxHeight: '60vh',
      minWidth: `${Math.max(rect.width, 220)}px`,
    };
    panelStyle.value = isRTL
      ? { ...base, right: `${vw - rect.right}px`, left: '' }
      : { ...base, left: `${rect.left}px`, right: '' };
  }
  function handleReposition() {
    if (openDropdown.value) positionPanelFor(openDropdown.value);
  }

  /* Lifecycle */
  onMounted(() => {
    window.addEventListener('scroll', handleReposition, true);
    window.addEventListener('resize', handleReposition);
    document.addEventListener('click', handleClickOutsideDoc);
    window.addEventListener('afterprint', handleAfterPrint);
  });
  onUnmounted(() => {
    window.removeEventListener('scroll', handleReposition, true);
    window.removeEventListener('resize', handleReposition);
    document.removeEventListener('click', handleClickOutsideDoc);
    window.removeEventListener('afterprint', handleAfterPrint);
  });

  /* === Header helpers === */
  const isComposite = (h) => Array.isArray(h?.key);
  const colKey = (h) => (isComposite(h) ? h.key.join('__') : h.key);
  const hKey = (h) => (isComposite(h) ? null : h.key);

  /* === Grouping maps === */
  const groupByKey = computed(() => {
    const map = new Map();
    (props.topGroups ?? []).forEach((g) => (g?.keys ?? []).forEach((k) => map.set(k, g)));
    return map;
  });
  const groupedKeys = computed(() => new Set([...groupByKey.value.keys()]));

  function isFirstKeyOfGroup(key) {
    if (!key) return false;
    const grp = groupByKey.value.get(key);
    if (!grp) return false;
    for (const h of props.headers) {
      const k = hKey(h);
      if (k && (grp.keys || []).includes(k)) return k === key;
    }
    return false;
  }
  function groupColspan(key) {
    if (!key) return 1;
    const grp = groupByKey.value.get(key);
    if (!grp) return 1;
    const keysInOrder = props.headers.map((h) => hKey(h)).filter(Boolean);
    return (grp.keys || []).filter((k) => keysInOrder.includes(k)).length || 1;
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
      if (h.key && !['picture', 'actions'].includes(h.key)) set.add(h.key);
    });
    return set;
  });
  const showFilterFor = (_key) => true;

  /* === Filters (safe) === */
  const localFilters = ref({});
  function initEmptyFilters() {
    const obj = {};
    props.headers.forEach((h) => {
      if (!isComposite(h) && h.key && dropdownSet.value.has(h.key)) obj[h.key] = [];
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
        if (!deepEqual(localFilters.value, normalized)) localFilters.value = normalized;
      } else {
        const empty = initEmptyFilters();
        if (!deepEqual(localFilters.value, empty)) localFilters.value = empty;
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
      if (!deepEqual(v, props.filters)) emit('update:filters', JSON.parse(JSON.stringify(v)));
    },
    { deep: true, flush: 'post' }
  );

  function getOptions(key) {
    if (!key) return [];
    const custom = props.optionsMap?.[key];
    if (Array.isArray(custom) && custom.length) return custom;
    return autoOptions.value[key] || [];
  }
  function getFilteredOptions(key) {
    if (!key) return [];
    const q = (dropdownQuery.value[key] || '').toLowerCase();
    const list = getOptions(key) || [];
    if (!q) return list;
    return list.filter((o) => ((o ?? '') + '').toLowerCase().includes(q));
  }
  function getFilteredOptionsCount(key) {
    return (getFilteredOptions(key) || []).length;
  }
  function clearAll(key) {
    if (!key) return;
    localFilters.value = { ...localFilters.value, [key]: [] };
  }
  function countLabel(sel, tot) {
    const t = Number(tot || 0);
    return sel === 0 ? `0 از ${t} انتخاب شده` : `${sel} از ${t} انتخاب شده`;
  }

  /* === Column widths === */
  function colWidth(h) {
    const w = h && h.width;
    if (typeof w === 'number') return `${w}px`;
    if (typeof w === 'string') return /^\d+$/.test(w) ? `${w}px` : w;
    return null;
  }

  /* === Print (await nextTick) === */
  const printOnlySelected = ref(false);
  const printOnlyCols = ref(false);

  function handleAfterPrint() {
    printOnlySelected.value = false;
    printOnlyCols.value = false;
  }
  async function printAll() {
    printOnlySelected.value = false;
    printOnlyCols.value = false;
    await nextTick();
    window.print();
  }
  async function printSelected() {
    if (selectedSet.value.size === 0) return;
    printOnlyCols.value = false;
    printOnlySelected.value = true;
    await nextTick();
    window.print();
  }
  async function printSelectedColumns() {
    if (selectedCols.value.size === 0) return;
    printOnlySelected.value = false;
    printOnlyCols.value = true;
    await nextTick();
    window.print();
  }

  /* Printed value under each header in print mode */
  function getPrintedFilterValue(key) {
    if (!key) return allLabel.value;
    const arr = localFilters.value?.[key];
    if (!Array.isArray(arr) || arr.length === 0) return allLabel.value;
    return arr.map((v) => (v === '' ? placeholderEmpty : String(v))).join('، ');
  }

  /* === Click outside dropdown === */
  function handleClickOutsideDoc(e) {
    if (!openDropdown.value) return;
    const t = e.target;
    const key = openDropdown.value;
    const trigger = triggerRefs.value[key];
    const p = panelEl.value;
    const panelContainsTarget = Array.isArray(p)
      ? p.some((el) => el && typeof el.contains === 'function' && el.contains(t))
      : !!(p && typeof p.contains === 'function' && p.contains(t));
    const insideTrigger = !!(
      trigger &&
      typeof trigger.contains === 'function' &&
      trigger.contains(t)
    );
    if (!panelContainsTarget && !insideTrigger) closeDropdown();
  }

  /* === PDF/Excel === */
  function downloadPdf() {
    if (selectedCols.value.size > 0) {
      printSelectedColumns();
      return;
    }
    if (selectedSet.value.size > 0) {
      printSelected();
      return;
    }
    printAll();
  }
  function headersFlatKeys() {
    return props.headers
      .filter((h) => !isComposite(h) && h.key && !['picture', 'actions'].includes(h.key))
      .map((h) => ({ key: h.key, title: h.title ?? h.key }));
  }
  function escapeCsv(val) {
    if (val == null) return '';
    const s = String(val).replace(/\r?\n/g, ' ');
    if (/[",;]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
    return s;
  }
  function makeCsv(rowsToUse) {
    const cols = headersFlatKeys();
    const headerLine = cols.map((c) => escapeCsv(c.title)).join(',');
    const dataLines = rowsToUse.map((r) =>
      cols
        .map((c) => {
          const v = displayValueByKey(c.key, r[c.key]); // ⬅️ formatted for export
          return escapeCsv(v);
        })
        .join(',')
    );
    return [headerLine, ...dataLines].join('\n');
  }

  function downloadExcel() {
    const rowsToUse =
      selectedSet.value.size > 0
        ? filteredRows.value.filter((r) => selectedSet.value.has(r[rowKey.value]))
        : filteredRows.value;
    const csv = makeCsv(rowsToUse);
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    const ts = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
    a.href = url;
    a.download = `table-export-${ts}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
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

    /* rows */
    .print-hidden {
      display: none !important;
    }
    /* columns */
    .print-col-hidden {
      display: none !important;
    }
  }
  .cell-wrap {
    word-break: break-word;
    white-space: normal;
  }
  button:disabled:hover {
    opacity: 0.5;
  }
</style>
