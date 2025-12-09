<template>
  <div :dir="rtl ? 'rtl' : 'ltr'" class="max-w-full">
    <!-- Toolbar -->
    <div
      class="mb-4 flex flex-wrap gap-1.5 justify-end print:hidden p-3 bg-gradient-to-r from-gray-50 to-white rounded-xl border border-gray-100 shadow-sm"
      :dir="rtl ? 'ltr' : 'rtl'"
    >
      <button
        type="button"
        class="px-2.5 py-1.5 text-xs font-medium rounded-md bg-gradient-to-r from-gray-800 to-gray-900 text-white hover:from-gray-700 hover:to-gray-800 transition-all duration-200 shadow-sm hover:shadow"
        @click="printAll"
      >
        <span class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
            />
          </svg>
          چاپ همه
        </span>
      </button>

      <!-- Print selected ROWS -->
      <button
        type="button"
        class="px-2.5 py-1.5 text-xs font-medium rounded-md bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-500 hover:to-blue-600 transition-all duration-200 shadow-sm hover:shadow disabled:opacity-40 disabled:cursor-not-allowed"
        :disabled="selectedCount === 0"
        @click="printSelected"
      >
        <span class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            />
          </svg>
          چاپ ردیف‌های انتخاب‌شده
          <span v-if="selectedCount > 0" class="bg-white/20 text-[10px] px-1.5 py-0.5 rounded-full">
            {{ selectedCount }}
          </span>
        </span>
      </button>

      <!-- Print selected COLUMNS -->
      <button
        type="button"
        class="px-2.5 py-1.5 text-xs font-medium rounded-md bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:from-indigo-500 hover:to-purple-500 transition-all duration-200 shadow-sm hover:shadow disabled:opacity-40 disabled:cursor-not-allowed"
        :disabled="selectedColsCount === 0"
        @click="printSelectedColumns"
      >
        <span class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 10h16M4 14h16M4 18h16"
            />
          </svg>
          چاپ ستون‌های انتخابی
          <span
            v-if="selectedColsCount > 0"
            class="bg-white/20 text-[10px] px-1.5 py-0.5 rounded-full"
          >
            {{ selectedColsCount }}
          </span>
        </span>
      </button>

      <!-- Clear filters button -->
      <button
        type="button"
        class="px-2.5 py-1.5 text-xs font-medium rounded-md bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:from-amber-400 hover:to-orange-400 transition-all duration-200 shadow-sm hover:shadow"
        @click="clearAllFilters"
        :disabled="!hasActiveFilters"
      >
        <span class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            />
          </svg>
          حذف فیلترها
        </span>
      </button>

      <button
        type="button"
        class="px-2.5 py-1.5 text-xs font-medium rounded-md bg-gradient-to-r from-gray-900 to-black text-white hover:from-gray-800 hover:to-gray-900 transition-all duration-200 shadow-sm hover:shadow"
        @click="downloadPdf"
      >
        <span class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          دانلود PDF
        </span>
      </button>

      <button
        type="button"
        class="px-2.5 py-1.5 text-xs font-medium rounded-md bg-gradient-to-r from-emerald-600 to-teal-600 text-white hover:from-emerald-500 hover:to-teal-500 transition-all duration-200 shadow-sm hover:shadow"
        @click="downloadExcel"
      >
        <span class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          دانلود Excel/CSV
        </span>
      </button>
    </div>

    <!-- Table (zoomed 67%) -->
    <div
      id="print-this-table"
      class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200 shadow-xl overflow-hidden table-zoom"
    >
      <!-- 👇 fixed height + vertical scroll -->
      <div class="overflow-x-auto overflow-y-auto rounded-2xl max-h-[55vh]">
        <table class="min-w-full table-auto">
          <colgroup>
            <col v-if="selectable" style="width: 48px" />
            <col v-if="showIndex" style="width: 72px" />
            <col v-if="showActions" style="width: 290px" />
            <col
              v-for="h in headers"
              :key="'col-' + colKey(h)"
              :style="colWidth(h) ? { width: colWidth(h) } : undefined"
            />
          </colgroup>

          <thead class="table-header">
            <!-- Row 1 -->
            <tr>
              <!-- Select column header – no select-all / print icon -->
              <th
                v-if="selectable"
                rowspan="2"
                class="px-2 py-2 text-center align-top"
                :class="{ 'print-col-hidden': printOnlyCols && !selectedCols.has(SELECT_KEY) }"
              >
                <div class="flex items-center justify-center">
                  <span class="text-[11px] font-medium text-white">انتخاب</span>
                </div>
              </th>

              <!-- Index column -->
              <th
                v-if="showIndex"
                rowspan="2"
                class="px-2 py-2 text-center align-top font-semibold text-white"
                :class="{ 'print-col-hidden': printOnlyCols && !selectedCols.has(INDEX_KEY) }"
              >
                <div class="flex flex-col items-center justify-center gap-1.5">
                  <div class="cell-wrap whitespace-nowrap text-xs font-semibold">
                    {{ indexLabel }}
                  </div>
                  <div class="print:hidden">
                    <input
                      type="checkbox"
                      class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500 cursor-pointer transition"
                      :checked="selectedCols.has(INDEX_KEY)"
                      @change="toggleCol(INDEX_KEY, $event.target.checked)"
                      :title="'نمایش ستون ردیف در چاپ'"
                    />
                  </div>
                </div>
              </th>

              <!-- Actions header -->
              <th
                v-if="showActions"
                rowspan="2"
                class="px-2 py-2 text-center align-top print:hidden"
              >
                <slot name="actions-header">
                  <span class="text-xs font-bold text-white">عملیات</span>
                </slot>
              </th>

              <!-- Group or single headers -->
              <template v-for="h in headers" :key="'top-' + colKey(h)">
                <th
                  v-if="isFirstKeyOfGroup(hKey(h))"
                  :colspan="groupColspan(hKey(h))"
                  class="px-2 py-2 text-center align-top"
                  :class="{ 'print-col-hidden': printOnlyCols && !groupHasAnySelected(hKey(h)) }"
                >
                  <div class="cell-wrap whitespace-nowrap text-xs font-bold text-white py-1">
                    {{ groupByKey.get(hKey(h))?.title || '' }}
                  </div>
                </th>

                <template v-else-if="coveredByGroupInRow1(hKey(h))"></template>

                <th
                  v-else
                  class="px-2 py-2 text-center align-top"
                  :class="{ 'print-col-hidden': printOnlyCols && !isColKeySelected(hKey(h)) }"
                >
                  <div class="cell-wrap whitespace-nowrap text-xs font-bold text-white">
                    {{ h.title }}
                  </div>
                </th>
              </template>
            </tr>

            <!-- Row 2 -->
            <tr>
              <template v-for="h in headers" :key="'sub-' + colKey(h)">
                <th
                  class="px-2 py-2 text-center align-top"
                  :class="[
                    { 'print-col-hidden': printOnlyCols && !isColKeySelected(hKey(h)) },
                    dropdownSet.has(hKey(h)) && showFilterFor(hKey(h)) ? 'cursor-pointer' : '',
                  ]"
                  :ref="(el) => hKey(h) && (triggerRefs[hKey(h)] = el)"
                  @click="
                    dropdownSet.has(hKey(h)) && showFilterFor(hKey(h)) && openDropdownAt(hKey(h))
                  "
                >
                  <div class="cell-wrap space-y-1">
                    <div class="flex items-center justify-center gap-1.5">
                      <div class="whitespace-nowrap text-xs font-medium text-white">
                        {{ h.title }}
                      </div>

                      <!-- ❌ Filter icon removed – only the title is visible -->

                      <!-- Column checkbox for print columns -->
                      <div v-if="!isComposite(h) && hKey(h)" class="print:hidden" @click.stop>
                        <input
                          type="checkbox"
                          class="w-3.5 h-3.5 rounded border-gray-200 text-indigo-600 focus:ring-2 focus:ring-indigo-500 cursor-pointer transition"
                          :checked="selectedCols.has(hKey(h))"
                          @change="toggleCol(hKey(h), $event.target.checked)"
                          :title="'انتخاب ستون: ' + (h.title || hKey(h))"
                        />
                      </div>
                    </div>

                    <!-- Printed value -->
                    <div
                      class="hidden print:block text-[11px] text-gray-700 mt-1 font-medium px-2 py-0.5 bg-gray-50/70 rounded"
                    >
                      {{ getPrintedFilterValue(hKey(h)) }}
                    </div>
                  </div>

                  <!-- Filter dropdown -->
                  <Teleport to="body" v-if="dropdownSet.has(hKey(h)) && showFilterFor(hKey(h))">
                    <div
                      v-show="openDropdown === hKey(h)"
                      ref="overlayEl"
                      class="fixed inset-0 z-[9999] pointer-events-none"
                      @keydown.esc.prevent.stop="closeDropdown"
                    >
                      <div
                        ref="panelEl"
                        :style="panelStyle"
                        class="absolute pointer-events-auto bg-white border border-gray-200 shadow-2xl rounded-xl overflow-hidden backdrop-blur-sm"
                        role="dialog"
                        aria-modal="true"
                      >
                        <!-- Sticky header -->
                        <div
                          class="sticky top-0 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 shadow-sm"
                        >
                          <div class="p-4">
                            <div class="flex items-center justify-between mb-3">
                              <h3 class="text-sm font-bold text-gray-900">فیلتر: {{ h.title }}</h3>
                              <button
                                @click="closeDropdown"
                                class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors"
                                type="button"
                              >
                                <svg
                                  class="w-4 h-4 text-gray-500"
                                  fill="none"
                                  stroke="currentColor"
                                  viewBox="0 0 24 24"
                                >
                                  <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                  />
                                </svg>
                              </button>
                            </div>

                            <div class="relative mb-3">
                              <svg
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                              >
                                <path
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                              </svg>
                              <input
                                :placeholder="searchPlaceholder"
                                v-model.trim="dropdownQuery[openDropdown || '']"
                                class="w-full border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-lg px-9 py-2.5 text-sm outline-none transition-all bg-white"
                                type="text"
                                @keydown.stop
                              />
                            </div>

                            <div class="flex gap-2">
                              <button
                                class="flex-1 text-xs px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200 font-medium text-gray-700"
                                @click="clearAll(openDropdown || '')"
                                type="button"
                              >
                                {{ btnClear }}
                              </button>
                              <button
                                type="button"
                                class="text-xs px-4 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-500 hover:to-blue-600 transition-all duration-200 font-medium"
                                @click="closeDropdown"
                              >
                                {{ btnDone }}
                              </button>
                            </div>
                          </div>

                          <div class="px-4 pb-2 text-xs text-gray-600 font-medium">
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
                            class="text-center text-sm text-gray-500 py-6"
                          >
                            <svg
                              class="w-10 h-10 mx-auto text-gray-300 mb-2"
                              fill="none"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                              />
                            </svg>
                            {{ noResults }}
                          </div>

                          <label
                            v-for="opt in getFilteredOptions(openDropdown || '')"
                            :key="opt || '__EMPTY__'"
                            class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-50 cursor-pointer select-none transition-colors	duration-150 group/option"
                            @click.stop
                          >
                            <div class="relative">
                              <input
                                type="checkbox"
                                :value="opt"
                                v-model="localFilters[openDropdown || '']"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer transition group-hover/option:border-blue-400"
                              />
                            </div>
                            <span
                              class="text-sm text-gray-800 break-all group-hover/option:text-gray-900 font-medium"
                            >
                              {{ opt || placeholderEmpty }}
                            </span>
                            <span
                              v-if="localFilters[openDropdown || '']?.includes(opt)"
                              class="ml-auto text-[11px] text-blue-600 font-semibold"
                            >
                              انتخاب شده
                            </span>
                          </label>
                        </div>

                        <div
                          class="border-t border-gray-100 px-4 py-3 bg-gradient-to-r from-gray-50 to-white/50"
                        >
                          <div class="flex items-center justify-between">
                            <div class="text-[11px] text-gray-500 font-medium">
                              {{ footerHint }}
                            </div>
                            <div class="flex gap-2">
                              <button
                                type="button"
                                class="text-xs px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200 font-medium text-gray-700"
                                @click="clearAll(openDropdown || '')"
                              >
                                پاک کردن همه
                              </button>
                              <button
                                type="button"
                                class="text-xs px-4 py-2 rounded-lg bg-gradient-to-r from-gray-900 to-black text-white hover:from-gray-800 hover:to-gray-900 transition-all duration-200 font-medium"
                                @click="closeDropdown"
                              >
                                اعمال فیلتر
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </Teleport>
                </th>
              </template>
            </tr>
          </thead>

          <tbody>
            <!-- Loading state -->
            <tr v-if="loading">
              <td
                :colspan="
                  (selectable ? 1 : 0) +
                  (showIndex ? 1 : 0) +
                  (showActions ? 1 : 0) +
                  headers.length
                "
                class="text-center py-10"
              >
                <div class="flex flex-col items-center justify-center gap-3">
                  <div class="flex items-center gap-1.5">
                    <span
                      class="w-2 h-2 rounded-full bg-gray-500 animate-[bounce_1.2s_infinite_-0.32s]"
                    ></span>
                    <span
                      class="w-2 h-2 rounded-full bg-gray-500 animate-[bounce_1.2s_infinite_-0.16s]"
                    ></span>
                    <span
                      class="w-2 h-2 rounded-full bg-gray-500 animate-[bounce_1.2s_infinite]"
                    ></span>
                  </div>
                  <div class="text-gray-600 text-xs font-medium">در حال بارگذاری داده‌ها...</div>
                </div>
              </td>
            </tr>

            <!-- Data rows -->
            <template v-else>
              <tr
                v-for="(row, i) in filteredRows"
                :key="getRowId(row, i)"
                :class="[
                  'transition-colors duration-200 group/row print:hover:bg-transparent hover:bg-slate-100/80 text-xs',
                  {
                    'print-hidden': printOnlySelected && !selectedSet.has(getRowId(row, i)),
                    'bg-white': i % 2 === 0,
                    'bg-slate-50': i % 2 !== 0,
                  },
                ]"
              >
                <!-- select checkbox cell -->
                <td
                  v-if="selectable"
                  class="px-2 py-3 text-center align-top"
                  :class="{ 'print-col-hidden': printOnlyCols && !selectedCols.has(SELECT_KEY) }"
                >
                  <div class="flex items-center justify-center">
                    <input
                      type="checkbox"
                      :value="getRowId(row, i)"
                      :checked="selectedSet.has(getRowId(row, i))"
                      @change="toggleRow(getRowId(row, i), $event.target.checked)"
                      class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer transition group-hover/row:border-blue-400"
                    />
                  </div>
                </td>

                <!-- index cell -->
                <td
                  v-if="showIndex"
                  class="px-2 py-3 text-center align-top text-xs font-medium text-gray-700"
                  :class="{ 'print-col-hidden': printOnlyCols && !selectedCols.has(INDEX_KEY) }"
                >
                  <span
                    class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-300 text-gray-800 font-bold shadow-sm"
                  >
                    {{ i + 1 }}
                  </span>
                </td>

                <!-- Actions cell -->
                <td v-if="showActions" class="px-2 py-3 text-center align-top print:hidden">
                  <slot name="actions" :item="row" />
                </td>

                <!-- data cells -->
                <td
                  v-for="h in headers"
                  :key="'cell-' + colKey(h) + '-' + getRowId(row, i)"
                  class="px-2 py-3 align-top whitespace-pre-wrap break-words text-[11px] text-gray-800 text-center group-hover/row:text-gray-900 transition-colors duration-200"
                  :class="{ 'print-col-hidden': printOnlyCols && !isColKeySelected(hKey(h)) }"
                >
                  <template v-if="$slots['cell-' + colKey(h)]">
                    <slot :name="'cell-' + colKey(h)" :row="row" />
                  </template>

                  <template v-else>
                    <div v-if="!isComposite(h)" class="font-medium">
                      {{ displayValueByKey(h.key, row[h.key]) }}
                    </div>
                    <div v-else class="space-y-1">
                      <div v-for="sub in h.key" :key="'v-' + sub" class="text-[11px]">
                        {{ displayValueByKey(sub, row[sub]) }}
                      </div>
                    </div>
                  </template>
                </td>
              </tr>

              <!-- Empty state -->
              <tr v-if="filteredRows.length === 0">
                <td
                  :colspan="
                    (selectable ? 1 : 0) +
                    (showIndex ? 1 : 0) +
                    (showActions ? 1 : 0) +
                    headers.length
                  "
                  class="text-center py-10"
                >
                  <div class="flex flex-col items-center justify-center gap-3">
                    <div
                      class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center shadow-inner"
                    >
                      <svg
                        class="w-8 h-8 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                      </svg>
                    </div>
                    <div class="text-gray-600 text-sm font-bold">نتیجه‌ای یافت نشد</div>
                    <p class="text-gray-500 text-xs max-w-md text-center">
                      با تغییر فیلترها یا جستجوی متفاوت، نتایج بیشتری مشاهده کنید.
                    </p>
                    <button
                      v-if="hasActiveFilters"
                      @click="clearAllFilters"
                      class="mt-1 px-3 py-1.5 text-xs font-medium rounded-md bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:from-amber-400 hover:to-orange-400 transition-all duration-200"
                    >
                      حذف فیلترها
                    </button>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- ✅ Footer: record count after filters (no other changes) -->
      <div
        class="flex items-center justify-between px-4 py-2 text-[11px] text-gray-600 bg-white/80 border-t border-gray-100 print:hidden"
      >
        <div>
          تعداد ردیف‌ها بعد از فیلتر:
          <span class="font-bold text-gray-900">{{ filteredRows.length }}</span>
          از
          <span class="font-medium text-gray-700">{{ rows.length }}</span>
        </div>

        <div v-if="selectedCount > 0" class="text-[11px] text-gray-700">
          ردیف‌های انتخاب‌شده:
          <span class="font-semibold text-blue-700">{{ selectedCount }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { computed, ref, watch, onMounted, onUnmounted, nextTick, useSlots } from 'vue';
  import { useCalendar } from '../../composables/useCalendar';
  import dayjs from 'dayjs';

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
    enableEdit: { type: Boolean, default: false },
    enablePeek: { type: Boolean, default: false },
    enableDownload: { type: Boolean, default: false },
    enableDelete: { type: Boolean, default: false },
    dateKeys: { type: Array, default: () => [] },
    autoDetectDates: { type: Boolean, default: true },
    loading: { type: Boolean, default: false },
  });

  /* === Emits === */
  const emit = defineEmits(['update:filters']);

  /* === Calendar / dates === */
  const { formatDisplay } = useCalendar();

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

  function normalizeToIso(val) {
    if (val == null) return '';
    let s = toAsciiDigits(String(val).trim()).replace(/\./g, '-').replace(/\//g, '-');
    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
    let m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
    if (m) return `${m[1]}-${m[2].padStart(2, '0')}-${m[3].padStart(2, '0')}`;
    m = s.match(/^(\d{4})\s+(\d{1,2})\s+(\d{1,2})$/);
    if (m) return `${m[1]}-${m[2].padStart(2, '0')}-${m[3].padStart(2, '0')}`;
    const d = dayjs(s);
    return d.isValid() ? d.format('YYYY-MM-DD') : '';
  }

  function looksLikeDateKey(key = '') {
    if (!key) return false;
    const k = key.toLowerCase();
    return /(date|dob|birth|joined|left|hired|apply|start|end|expiry|expire|issued|issue|event)/.test(
      k
    );
  }

  function looksLikeDateValue(val) {
    if (val == null) return false;
    if (typeof val === 'number') return false;
    const s = toAsciiDigits(String(val).trim());
    const m = s.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
    if (!m) return false;
    const year = +m[1],
      month = +m[2],
      day = +m[3];
    if (month < 1 || month > 12) return false;
    if (day < 1 || day > 31) return false;
    if (year < 1000 || year > 2200) return false;
    return true;
  }

  function isDateCell(key, value) {
    if (Array.isArray(props.dateKeys) && props.dateKeys.includes(key)) return true;
    if (!props.autoDetectDates) return false;
    return looksLikeDateKey(key) || looksLikeDateValue(value);
  }

  function displayValueByKey(key, rawValue) {
    if (typeof rawValue === 'number') return rawValue;
    if (typeof rawValue === 'string' && /^[0-9]+$/.test(toAsciiDigits(rawValue))) return rawValue;
    if (!isDateCell(key, rawValue)) return rawValue;
    const iso = normalizeToIso(rawValue);
    return iso ? formatDisplay(iso) : rawValue;
  }

  /* === Computeds / maps === */
  const rows = computed(() => props.rows ?? []);
  const rtl = computed(() => props.rtl ?? true);
  const showIndex = computed(() => props.showIndex ?? true);
  const rowKey = computed(() => props.rowKey ?? 'idOrTmp');
  const includeEmptyOption = computed(() => props.includeEmptyOption ?? true);
  const selectable = computed(() => props.selectable !== false);

  /* helper: stable row id (fix selecting all when rowKey is missing/same) */
  function getRowId(row, index) {
    const k = rowKey.value;
    const v = k && row ? row[k] : undefined;
    if (v === undefined || v === null || v === '') {
      return `row-${index}`;
    }
    return v;
  }

  const autoOptions = computed(() => {
    const map = {};
    const add = (k) => {
      const set = new Set();
      rows.value.forEach((r) => {
        const raw = r?.[k];
        const disp = displayValueByKey(k, raw);
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

  const localFilters = ref({});

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

  /* === Slots & Actions column === */
  const slots = useSlots();
  const showActions = computed(() => !!slots.actions);

  /* === Selection (rows) === */
  const selectedSet = ref(new Set());
  const selectedCount = computed(() => selectedSet.value.size);

  function toggleRow(id, checked) {
    if (checked) selectedSet.value.add(id);
    else selectedSet.value.delete(id);
  }

  /* === Active filters === */
  const hasActiveFilters = computed(() => {
    const filters = localFilters.value || {};
    return Object.values(filters).some((arr) => Array.isArray(arr) && arr.length > 0);
  });

  function clearAllFilters() {
    const empty = initEmptyFilters();
    localFilters.value = empty;
  }

  /* === Column selection === */
  const selectedCols = ref(new Set());
  const selectedColsCount = computed(() => selectedCols.value.size);

  const SELECT_KEY = '__SELECT__';
  const INDEX_KEY = '__INDEX__';

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
      minWidth: `${Math.max(rect.width, 320)}px`,
    };
    panelStyle.value = isRTL
      ? { ...base, right: `${vw - rect.right}px`, left: '' }
      : { ...base, left: `${rect.left}px`, right: '' };
  }

  function handleReposition() {
    if (openDropdown.value) positionPanelFor(openDropdown.value);
  }

  /* === Header helpers === */
  const isComposite = (h) => Array.isArray(h?.key);
  const colKey = (h) => (isComposite(h) ? h.key.join('__') : h.key);
  const hKey = (h) => (isComposite(h) ? null : h.key);

  /* === Grouping === */
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

  /* === Dropdown keys === */
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

  /* === Filters === */
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

  /* === Print flags === */
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
          const v = displayValueByKey(c.key, r[c.key]);
          return escapeCsv(v);
        })
        .join(',')
    );
    return [headerLine, ...dataLines].join('\n');
  }

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

  function downloadExcel() {
    const rowsToUse = [];
    if (selectedSet.value.size > 0) {
      filteredRows.value.forEach((r, idx) => {
        const id = getRowId(r, idx);
        if (selectedSet.value.has(id)) rowsToUse.push(r);
      });
    } else {
      rowsToUse.push(...filteredRows.value);
    }
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

  /* === Lifecycle === */
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
</script>

<style>
  @keyframes bounce {
    0%,
    80%,
    100% {
      transform: scale(0);
    }
    40% {
      transform: scale(1);
    }
  }

  /* Header color */
  .table-header {
    background-color: #08989f;
  }

  /* ✅ header cells border color = header background */
  .table-header th {
    background-color: #08989f;
    border-color: #08989f !important;
  }

  /* ✅ no cell border for rows */
  tbody td {
    border-color: transparent !important;
  }

  /* Print overrides */
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
      transform: scale(1) !important;
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

    .print-hidden {
      display: none !important;
    }
    .print-col-hidden {
      display: none !important;
    }
  }

  .cell-wrap {
    word-break: break-word;
    white-space: normal;
  }

  button:disabled:hover {
    opacity: 0.4;
    transform: none !important;
    box-shadow: none !important;
  }

  .overflow-x-auto::-webkit-scrollbar {
    height: 8px;
  }
  .overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }
  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
  }
  .overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
  }
  /* ===========================
   TABLE LAYOUT – remove gaps
   =========================== */
  #print-this-table table {
    border-collapse: separate;
    border-spacing: 0; /* 🔥 removes white gaps between header cells */
  }

  /* ===========================
   HEADER COLORS (PRIMARY)
   =========================== */
  .table-header {
    background-color: #08989f !important;
  }

  /* all header cells: same background + same border color */
  .table-header th {
    background-color: #08989f !important;
    border-top: 1px solid #08989f !important;
    border-bottom: 1px solid #08989f !important;
    border-left: 1px solid #08989f !important;
    border-right: 1px solid #08989f !important;
    color: #ffffff !important;
  }

  /* ===========================
   ROW STYLE (Vuetify inset bar)
   =========================== */

  /* Remove all default borders from body cells */
  tbody td {
    border: none !important;
  }

  /* LTR inset primary border inside every row */
  tbody tr {
    position: relative;
    box-shadow: inset 4px 0 0 0 #08989f; /* left vertical color bar */
    border-bottom: 1px solid white !important;
  }

  /* RTL inset primary border (right side) */
  [dir='rtl'] tbody tr {
    box-shadow: inset -4px 0 0 0 #08989f; /* right side */
    border-bottom: 1px solid white !important;
  }

  /* Optional hover */
  tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.03);
  }
  .overflow-x-auto::-webkit-scrollbar {
    width: 6px; /* 👈 vertical scrollbar width */
    height: 8px; /* 👈 horizontal scrollbar height (keep as you like) */
  }

  .overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }

  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
  }

  .overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
  }
  /* Make the whole header block sticky inside the scroll area */
  .overflow-x-auto > table > thead.table-header {
    position: sticky;
    top: 0;
    z-index: 40; /* above body rows */
  }
  /* Default: hide scrollbars but scroll works */
  .overflow-x-auto::-webkit-scrollbar {
    width: 0px;
    height: 0px;
  }

  /* On hover: show scrollbars */
  .overflow-x-auto:hover::-webkit-scrollbar {
    width: 4px; /* vertical */
    height: 4px; /* horizontal */
  }

  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #bfbfbf;
    border-radius: 4px;
  }

  .overflow-x-auto:hover::-webkit-scrollbar-thumb {
    background: #888;
  }

  .overflow-x-auto::-webkit-scrollbar-track {
    background: transparent;
  }
  /* Default: hide */
  .overflow-x-auto {
    scrollbar-width: none;
  }

  /* On hover: show */
  .overflow-x-auto:hover {
    scrollbar-width: thin;
    scrollbar-color: #b9b6b6 transparent;
  }
</style>
