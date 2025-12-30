<template>
  <v-tooltip :text="tooltipText">
    <template #activator="{ props }">
      <v-btn
        v-bind="props"
        @click="findDuplicates"
        icon
        size="small"
        variant="tonal"
        :disabled="loading"
        class="action-btn"
      >
        <v-badge :content="String(duplicateCount)" color="red" size="small" show-zero>
          <v-icon size="20" color="primary" :class="{ 'spin-refresh': loading }">
            mdi-content-duplicate
          </v-icon>
        </v-badge>
      </v-btn>
    </template>
  </v-tooltip>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const loading = ref(false);
const duplicateCount = ref(0);
const duplicates = ref([]);

// Get current language from localStorage or default to 'fa' (Dari)
const currentLang = localStorage.getItem('apartment-lang') || 'fa';

// Translation dictionary
const translations = {
  fa: {
    // Dari
    tooltip: 'جستجوی کپی‌ها در این صفحه',
    tooltipWithCount: 'کپی پیدا شده',
    noTables: 'جدولی در این صفحه یافت نشد',
    noDuplicates: 'کپی‌ای یافت نشد',
    error: 'خطا:',
    close: 'بستن',
    debug: 'اشکال‌زدایی',
    viewDetails: 'مشاهده جزئیات',
    summary: 'خلاصه',
    totalDuplicates: 'مجموع کپی‌ها',
    tablesWithDuplicates: 'جداول دارای کپی',
    duplicateSet: 'مجموعه کپی',
    duplicateRows: 'ردیف‌های کپی',
    duplicateData: 'داده‌های کپی',
    duplicateRowNumbers: 'شماره ردیف‌های کپی',
    debugInfo: 'اطلاعات اشکال‌زدایی',
    attention: 'توجه لازم',
    runningDebug: 'در حال اشکال‌زدایی...',
    tablesChecked: 'جداول بررسی شده',
    note: 'یادداشت',
    sample: 'نمونه',
    row: 'ردیف',
    rows: 'ردیف',
    type: 'نوع',
    selector: 'انتخابگر',
    ok: 'تأیید',

    // missing keys used in template strings
    totalRows: 'مجموع ردیف‌ها',
  },
  en: {
    // English
    tooltip: 'Find duplicates in current page',
    tooltipWithCount: 'duplicate(s) found',
    noTables: 'No tables found on this page',
    noDuplicates: 'No duplicates found',
    error: 'Error:',
    close: 'Close',
    debug: 'Debug',
    viewDetails: 'View Details',
    summary: 'Summary',
    totalDuplicates: 'Total Duplicates',
    tablesWithDuplicates: 'Tables with Duplicates',
    duplicateSet: 'Duplicate Set',
    duplicateRows: 'Duplicate Rows',
    duplicateData: 'Duplicate Data',
    duplicateRowNumbers: 'Duplicate Row Numbers',
    debugInfo: 'Debug Information',
    attention: 'Attention Required',
    runningDebug: 'Running debug check...',
    tablesChecked: 'Tables Checked',
    note: 'Note',
    sample: 'Sample',
    row: 'Row',
    rows: 'rows',
    type: 'Type',
    selector: 'Selector',
    ok: 'OK',

    // missing keys used in template strings
    totalRows: 'Total Rows',
  },
};

const t = (key) => translations[currentLang]?.[key] || translations.en[key] || key;

const tooltipText = computed(() => {
  if (duplicateCount.value === 0) return t('tooltip');
  return `${duplicateCount.value} ${t('tooltipWithCount')}`;
});

const findDuplicates = async () => {
  if (loading.value) return;

  loading.value = true;
  duplicateCount.value = 0; // Reset counter

  try {
    const tables = findAllTables();

    if (tables.length === 0) {
      showMessage(t('noTables'));
      return;
    }

    console.log('🔍 Found tables:', tables);

    let totalDuplicates = 0;
    const allResults = [];

    for (const table of tables) {
      const result = await checkTableForDuplicates(table);
      console.log(`📊 Table "${table.name}" result:`, result);

      if (result.duplicates.length > 0) {
        totalDuplicates += result.duplicateCount;
        allResults.push({
          tableName: table.name,
          ...result,
        });
      }
    }

    duplicates.value = allResults;
    duplicateCount.value = totalDuplicates;

    console.log('📈 Final results:', {
      totalDuplicates,
      allResults,
      duplicateCount: duplicateCount.value,
    });

    if (totalDuplicates > 0) {
      showResults(allResults, totalDuplicates);
    } else {
      showNoDuplicatesWithDebug(tables);
    }
  } catch (error) {
    console.error('❌ Duplicate finder error:', error);
    showMessage(`${t('error')} ${error?.message || String(error)}`);
  } finally {
    loading.value = false;
  }
};

// ✅ Always resolve to the real inner <table> (Vuetify wrapper-safe)
const resolveRealTableRoot = (element) => {
  if (!element) return null;
  if (element.tagName === 'TABLE') return element;
  const innerTable = element.querySelector?.('table');
  if (innerTable) return innerTable;
  return element;
};

// Find all tables in the DOM (deduped + prefer real roots)
const findAllTables = () => {
  const tables = [];

  const selectors = [
    '.v-data-table',
    '.v-table',
    '.v-table__wrapper',
    '.v-data-table__wrapper',
    '.filterable-table',
    'table',
    '[role="grid"]',
    '[role="table"]',
  ];

  selectors.forEach((selector) => {
    const elements = document.querySelectorAll(selector);
    elements.forEach((element, index) => {
      const root = resolveRealTableRoot(element);
      if (!root) return;

      // Deduplicate by resolved root
      if (tables.some((t) => t.root === root)) return;

      tables.push({
        type: selector.replace('.', '').replace('[', '').replace(']', ''),
        element,
        root,
        name: `${selector} ${index + 1}`,
        getRows: () => getRowsFromElement(root),
      });
    });
  });

  return tables;
};

const getCellValue = (cell) => {
  if (!cell) return '';

  // ✅ Ignore action-only cells (buttons/icons)
  const hasOnlyButtons = cell.querySelectorAll('button, .v-btn').length > 0;
  const hasInputLike = cell.querySelector(
    'input, textarea, .v-select__selection-text, .v-autocomplete__selection-text, .v-field__input'
  );
  const rawText = cell.textContent?.replace(/\s+/g, ' ').trim();

  if (hasOnlyButtons && !hasInputLike && (!rawText || rawText.length < 2)) {
    return '';
  }

  // 1) real inputs (v-text-field / v-textarea)
  const input = cell.querySelector('input');
  if (input && input.value != null && String(input.value).trim()) return String(input.value).trim();

  const textarea = cell.querySelector('textarea');
  if (textarea && textarea.value != null && String(textarea.value).trim())
    return String(textarea.value).trim();

  // 2) v-select / autocomplete selected text (Vuetify 3)
  const selection =
    cell.querySelector('.v-select__selection-text') ||
    cell.querySelector('.v-autocomplete__selection-text') ||
    cell.querySelector('.v-field__input');
  if (selection && selection.textContent && selection.textContent.trim())
    return selection.textContent.trim();

  // 3) chips
  const chips = cell.querySelectorAll('.v-chip');
  if (chips.length) {
    const chipText = Array.from(chips)
      .map((c) => c.textContent?.trim())
      .filter(Boolean)
      .join(' ');
    if (chipText) return chipText;
  }

  // 4) fallback: normal text
  return rawText || '';
};

// Get headers from table element
const getHeadersFromElement = (tableEl) => {
  const root = resolveRealTableRoot(tableEl);
  const headers = [];

  const headerSelectors = ['thead th', '.v-data-table__thead th', 'th[role="columnheader"]', 'th'];

  for (const selector of headerSelectors) {
    const headerElements = root.querySelectorAll(selector);
    if (headerElements.length > 0) {
      headerElements.forEach((header) => {
        const headerText = header.textContent?.trim();
        if (headerText) {
          const cleanHeader = headerText
            .replace(/\s+/g, '_')
            .replace(
              /[^\w\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF_]/g,
              ''
            )
            .toLowerCase();
          headers.push(cleanHeader);
        }
      });
      break;
    }
  }

  return headers;
};

// Get rows from any <table> element (wrapper-safe)
const getRowsFromElement = (tableEl) => {
  const rows = [];
  const root = resolveRealTableRoot(tableEl);

  const rowSelectors = [
    'tbody tr',
    '.v-data-table__tbody tr',
    '.v-table__tbody tr',
    'tr[role="row"]',
    '.v-data-table__tr',
    '.v-table__tr',
    'tr',
  ];

  let tableRows = [];
  for (const selector of rowSelectors) {
    const found = root.querySelectorAll(selector);
    if (found.length > 0) {
      tableRows = found;
      break;
    }
  }

  console.log(`📋 Found ${tableRows.length} rows in element`);

  const headers = getHeadersFromElement(root);

  tableRows.forEach((row, rowIndex) => {
    const rowData = {};

    const cellSelectors = ['td', '.v-data-table__td', '.v-table__td', '[role="cell"]'];
    let cells = [];

    for (const selector of cellSelectors) {
      const found = row.querySelectorAll(selector);
      if (found.length > 0) {
        cells = found;
        break;
      }
    }

    if (cells.length === 0) {
      cells = row.querySelectorAll(':scope > *');
    }

    cells.forEach((cell, cellIndex) => {
      const columnName = headers[cellIndex] || `column_${cellIndex + 1}`;
      let cellText = getCellValue(cell);
      cellText = String(cellText || '').replace(/\s+/g, ' ').trim();

      if (cellText) {
        const cleanColumnName =
          columnName.replace(/_+/g, '_').replace(/^_|_$/g, '') || `col_${cellIndex + 1}`;
        rowData[cleanColumnName] = cellText;
      }
    });

    rowData._rowIndex = rowIndex;
    rowData._rowElement = row;

    if (Object.keys(rowData).filter((k) => !k.startsWith('_')).length > 0) {
      rows.push(rowData);
    }
  });

  console.log(`📄 Extracted ${rows.length} rows with data`);
  if (rows.length > 0) console.log('📝 Sample row data:', rows[0]);

  return rows;
};

// Check table for duplicates
const checkTableForDuplicates = async (table) => {
  try {
    const rows = await table.getRows();
    if (rows.length < 2) return { duplicates: [], duplicateCount: 0, totalRows: rows.length };

    console.log(`🔍 Checking table "${table.name}" with ${rows.length} rows`);

    const duplicatesGroups = [];
    const seen = new Map();

    rows.forEach((row, rowIndex) => {
      const comparisonKey = createComparisonKeyWithoutID(row);
      if (comparisonKey) {
        if (seen.has(comparisonKey)) {
          const g = seen.get(comparisonKey);
          g.rows.push({ index: rowIndex, data: row });
        } else {
          seen.set(comparisonKey, { rows: [{ index: rowIndex, data: row }] });
        }
      }
    });

    seen.forEach((group, key) => {
      if (group.rows.length > 1) {
        const parsed = JSON.parse(key); // [[field, value], ...]
        const matchedFields = parsed.map(([field]) => field);

        duplicatesGroups.push({
          key,
          rows: group.rows,
          count: group.rows.length,
          sample: group.rows[0].data,
          matchedFields,
        });
      }
    });

    const dupCount = duplicatesGroups.reduce((sum, d) => sum + (d.count - 1), 0);

    console.log(`📊 Found ${duplicatesGroups.length} duplicate groups (${dupCount} total duplicates)`);

    return {
      duplicates: duplicatesGroups,
      duplicateCount: dupCount,
      totalRows: rows.length,
    };
  } catch (error) {
    console.error(`❌ Error checking table ${table.name}:`, error);
    return { duplicates: [], duplicateCount: 0, totalRows: 0, error: error?.message || String(error) };
  }
};

// Create comparison key WITHOUT ID fields
const createComparisonKeyWithoutID = (row) => {
  const dataToCompare = {};

  const isIgnoredField = (k) => {
    const key = String(k || '').trim().toLowerCase();

    if (key.startsWith('_')) return true;

    if (key === 'id') return true;
    if (key.endsWith('_id') || key.endsWith('-id')) return true;
    if (key === 'uuid' || key.endsWith('_uuid')) return true;
    if (key === 'آی‌دی' || key === 'ایدی') return true;

    // timestamps
    if (key === 'created_at' || key === 'updated_at' || key === 'deleted_at') return true;
    if (key.endsWith('_at')) return true;

    return false;
  };

  Object.entries(row).forEach(([key, value]) => {
    if (isIgnoredField(key)) return;
    if (value === null || value === undefined) return;

    const str = String(value).replace(/\s+/g, ' ').trim();
    if (!str) return;

    const cleanValue = str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    dataToCompare[key] = cleanValue;
  });

  if (Object.keys(dataToCompare).length === 0) return null;

  const sortedEntries = Object.entries(dataToCompare).sort(([a], [b]) => a.localeCompare(b));
  return JSON.stringify(sortedEntries);
};

// Show results in modal
const showResults = (results, totalDuplicates) => {
  const modal = createModal();
  const isRtl = currentLang === 'fa';

  let content = `
    <div style="max-width: 900px; direction: ${isRtl ? 'rtl' : 'ltr'}; text-align: ${
    isRtl ? 'right' : 'left'
  }">
      <div style="margin-bottom: 20px; border-bottom: 2px solid #d32f2f; padding-bottom: 10px;">
        <h3 style="color: #d32f2f; margin: 0; display: flex; align-items: center; gap: 8px;">
          <span style="font-size: 24px;">🔍</span>
          <span>${t('tooltip')}</span>
        </h3>
      </div>

      <div style="margin-bottom: 20px; padding: 16px; background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%); border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h4 style="margin: 0 0 8px 0; color: #d32f2f;">${t('summary')}</h4>
            <p style="margin: 0; font-size: 14px;">
              <strong>${t('totalDuplicates')}:</strong>
              <span style="color: #d32f2f; font-weight: bold;">${totalDuplicates}</span><br>
              <strong>${t('tablesWithDuplicates')}:</strong> ${results.length}
            </p>
          </div>
          <div style="background: white; padding: 8px 16px; border-radius: 20px; border: 1px solid #ffcdd2;">
            <span style="color: #d32f2f; font-weight: bold;">${t('attention')}</span>
          </div>
        </div>
      </div>`;

  results.forEach((result) => {
    content += `
      <div style="margin-bottom: 24px; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
        <div style="background: #f5f5f5; padding: 12px 16px; border-bottom: 1px solid #e0e0e0;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <h4 style="margin: 0; color: #333;">
                <span style="font-size: 18px;">📋</span>
                ${result.tableName}
              </h4>
              <p style="margin: 4px 0 0 0; font-size: 12px; color: #666;">
                ${t('totalRows')}: ${result.totalRows} | ${t('duplicateRows')}: ${result.duplicateCount}
              </p>
            </div>
            <span style="background: #ffebee; color: #d32f2f; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
              ⚠️ ${result.duplicateCount} ${t('duplicateRows')}
            </span>
          </div>
        </div>
        <div style="padding: 16px;">`;

    result.duplicates.forEach((dup, dupIndex) => {
      content += `
        <div style="margin-bottom: 20px; padding: 16px; background: #fff8e1; border: 1px solid #ffd54f; border-radius: 8px;">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
            <div>
              <strong style="color: #e65100; font-size: 16px;">${t('duplicateSet')} ${
        dupIndex + 1
      }</strong>
              <span style="margin-${
                isRtl ? 'right' : 'left'
              }: 12px; background: #ff9800; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                ${dup.count} ${t('rows')}
              </span>
            </div>
          </div>

          <div style="font-size: 13px; color: #666; margin-bottom: 12px; background: white; padding: 12px; border-radius: 6px;">
            <strong style="display: block; margin-bottom: 6px; color: #2196f3;">${t(
              'duplicateData'
            )}:</strong>

            <div style="margin-bottom: 10px; font-size: 12px; color: #444;">
              <strong>Matched fields:</strong> ${dup.matchedFields?.join(', ') || '-'}
            </div>`;

      const sampleFields = Object.entries(dup.sample || {}).filter(([key]) => !key.startsWith('_'));
      sampleFields.forEach(([key, value]) => {
        const strVal = String(value ?? '');
        const displayValue = strVal.length > 50 ? strVal.substring(0, 50) + '...' : strVal;
        content += `
            <div style="margin-bottom: 4px; display: flex; flex-direction: ${
              isRtl ? 'row-reverse' : 'row'
            }">
              <span style="min-width: 120px; color: #5d4037; font-weight: 500; text-align: ${
                isRtl ? 'right' : 'left'
              }">${key}:</span>
              <span style="color: #333; flex: 1; text-align: ${
                isRtl ? 'right' : 'left'
              }">${displayValue}</span>
            </div>`;
      });

      content += `
          </div>

          <div style="font-size: 12px; color: #666; background: #f5f5f5; padding: 10px; border-radius: 6px;">
            <strong style="color: #d32f2f;">${t('duplicateRowNumbers')}:</strong>
            <span style="margin-${isRtl ? 'right' : 'left'}: 8px; font-weight: bold;">
              ${dup.rows
                .map(
                  (r) =>
                    `<span style="background: #ffcdd2; padding: 2px 6px; border-radius: 4px; margin-${
                      isRtl ? 'left' : 'right'
                    }: 4px;">${t('row')} ${r.index + 1}</span>`
                )
                .join(' ')}
            </span>
          </div>
        </div>`;
    });

    content += `</div></div>`;
  });

  content += `
    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e0e0e0; text-align: center;">
      <button id="closeResultsBtn" style="
        background: #2196f3;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
      " onmouseover="this.style.background='#1976d2'" onmouseout="this.style.background='#2196f3'">
        ${t('close')}
      </button>
    </div>
  </div>`;

  modal.innerHTML = content;

  setTimeout(() => {
    const closeBtn = document.getElementById('closeResultsBtn');
    if (closeBtn) {
      closeBtn.onclick = () => {
        document.body.removeChild(modal);
        document.body.removeChild(modal._overlay);
      };
    }
  }, 100);
};

// Show no duplicates with debug info
const showNoDuplicatesWithDebug = (tables) => {
  const modal = createModal();
  const isRtl = currentLang === 'fa';

  let tableInfo = '';
  tables.forEach((table, index) => {
    const root = table.root || resolveRealTableRoot(table.element);
    const rowsInDom = root?.querySelectorAll?.('tbody tr, tr[role="row"], tr')?.length || 0;

    tableInfo += `
      <div style="margin-bottom: 8px; padding: 8px; border-bottom: 1px solid #eee; text-align: ${
        isRtl ? 'right' : 'left'
      }">
        <strong>${index + 1}. ${table.name}</strong><br>
        ${t('type')}: ${table.type} | ${t('rows')} در DOM: ${rowsInDom}<br>
        ${t('selector')}: ${root?.tagName || 'N/A'}
      </div>`;
  });

  modal.innerHTML = `
    <div style="text-align: center; padding: 20px; max-width: 600px; direction: ${
      isRtl ? 'rtl' : 'ltr'
    }">
      <div style="font-size: 48px; margin-bottom: 16px; color: #4caf50">
        ✅
      </div>
      <h3 style="margin: 0 0 16px 0; color: #333;">${t('noDuplicates')}</h3>
      <p style="margin: 0 0 24px 0; color: #666; line-height: 1.5;">
        ${t('tablesChecked')} ${tables.length} ${t('noDuplicates')}.
      </p>

      <details style="margin-bottom: 24px; text-align: ${isRtl ? 'right' : 'left'}">
        <summary style="cursor: pointer; color: #2196F3; font-weight: bold; padding: 8px; background: #f5f5f5; border-radius: 4px;">
          🔍 ${t('viewDetails')}
        </summary>
        <div style="margin-top: 8px; padding: 12px; background: #fafafa; border-radius: 4px; font-size: 12px;">
          <div style="margin-bottom: 8px; text-align: ${isRtl ? 'right' : 'left'}"><strong>${t(
    'tablesChecked'
  )}:</strong></div>
          ${tableInfo}
          <div style="margin-top: 16px; color: #666; font-size: 11px; text-align: ${
            isRtl ? 'right' : 'left'
          }">
            <strong>${t('note')}:</strong> ${t(
    'note'
  )} ردیف‌ها ممکن است یکسان به نظر برسند اما دارای داده‌های پنهان متفاوتی باشند.
          </div>
        </div>
      </details>

      <div style="display: flex; justify-content: center; gap: 12px;">
        <button id="debugCheckBtn" style="
          background: #ff9800;
          color: white;
          border: none;
          padding: 10px 20px;
          border-radius: 6px;
          cursor: pointer;
          font-size: 14px;
        ">${t('debug')}</button>

        <button id="closeMessageBtn" style="
          background: #2196f3;
          color: white;
          border: none;
          padding: 10px 20px;
          border-radius: 6px;
          cursor: pointer;
          font-size: 14px;
        ">${t('ok')}</button>
      </div>
    </div>
  `;

  setTimeout(() => {
    const closeBtn = document.getElementById('closeMessageBtn');
    const debugBtn = document.getElementById('debugCheckBtn');

    if (closeBtn) {
      closeBtn.onclick = () => {
        document.body.removeChild(modal);
        document.body.removeChild(modal._overlay);
      };
    }

    if (debugBtn) {
      debugBtn.onclick = async () => {
        modal.innerHTML = `<div style="padding: 40px; text-align: center;">
          <div class="spin-refresh" style="display: inline-block; font-size: 24px;">🔄</div>
          <p>${t('runningDebug')}</p>
        </div>`;

        const debugResults = await performDebugCheck(tables);
        document.body.removeChild(modal);
        document.body.removeChild(modal._overlay);
        showDebugResults(debugResults);
      };
    }
  }, 100);
};

// Perform debug check
const performDebugCheck = async (tables) => {
  const results = [];

  for (const table of tables) {
    try {
      const rows = await table.getRows();
      const rowDetails = rows.map((row, index) => ({
        index,
        data: row,
        cleanText: Object.entries(row)
          .filter(([key]) => !key.startsWith('_'))
          .map(([_, value]) => String(value).trim().toLowerCase())
          .filter(Boolean)
          .join(' | '),
      }));

      results.push({
        table: table.name,
        rows: rowDetails,
        totalRows: rows.length,
      });
    } catch (error) {
      console.error(`Debug error for ${table.name}:`, error);
    }
  }

  return results;
};

// Show debug results
const showDebugResults = (debugResults) => {
  const modal = createModal();
  const isRtl = currentLang === 'fa';

  let content = `
    <div style="max-width: 900px; direction: ${isRtl ? 'rtl' : 'ltr'}">
      <div style="margin-bottom: 20px; border-bottom: 2px solid #ff9800; padding-bottom: 10px;">
        <h3 style="color: #ff9800; margin: 0; display: flex; align-items: center; gap: 8px;">
          <span style="font-size: 24px;">🔧</span>
          <span>${t('debugInfo')}</span>
        </h3>
      </div>`;

  debugResults.forEach((result) => {
    content += `
      <div style="margin-bottom: 24px; border: 1px solid #ff9800; border-radius: 8px; overflow: hidden;">
        <div style="background: #fff3e0; padding: 12px 16px;">
          <h4 style="margin: 0; color: #e65100; text-align: ${isRtl ? 'right' : 'left'}">
            ${result.table} (${result.totalRows} ${t('rows')})
          </h4>
        </div>
        <div style="padding: 16px; max-height: 300px; overflow-y: auto;">`;

    result.rows.forEach((row) => {
      const preview = row.cleanText.length > 100 ? row.cleanText.substring(0, 100) + '...' : row.cleanText;
      content += `
        <div style="margin-bottom: 12px; padding: 10px; background: #fafafa; border-radius: 4px; border-${
          isRtl ? 'right' : 'left'
        }: 3px solid #4caf50;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 4px; flex-direction: ${
            isRtl ? 'row-reverse' : 'row'
          }">
            <strong style="color: #2196f3;">${t('row')} ${row.index + 1}</strong>
            <span style="font-size: 11px; color: #666; text-align: ${isRtl ? 'right' : 'left'}">${preview}</span>
          </div>
          <div style="font-size: 11px; color: #666; font-family: monospace; max-height: 100px; overflow-y: auto; padding: 8px; background: white; border-radius: 3px; direction: ltr; text-align: left;">
            ${escapeHtml(JSON.stringify(row.data, null, 2))}
          </div>
        </div>`;
    });

    content += `</div></div>`;
  });

  content += `
    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e0e0e0; text-align: center;">
      <button id="closeDebugBtn" style="
        background: #2196f3;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
      ">${t('close')}</button>
    </div>
  </div>`;

  modal.innerHTML = content;

  setTimeout(() => {
    const closeBtn = document.getElementById('closeDebugBtn');
    if (closeBtn) {
      closeBtn.onclick = () => {
        document.body.removeChild(modal);
        document.body.removeChild(modal._overlay);
      };
    }
  }, 100);
};

// Show simple message
const showMessage = (message) => {
  const modal = createModal();
  const isRtl = currentLang === 'fa';
  const msg = String(message ?? '');
  const isError = msg.toLowerCase().includes('error') || msg.toLowerCase().includes('خطا');

  modal.innerHTML = `
    <div style="text-align: center; padding: 20px; direction: ${isRtl ? 'rtl' : 'ltr'}">
      <div style="font-size: 48px; margin-bottom: 16px; color: ${isError ? '#d32f2f' : '#4caf50'}">
        ${isError ? '❌' : '✅'}
      </div>
      <h3 style="margin: 0 0 16px 0; color: #333;">${isError ? t('error') : t('note')}</h3>
      <p style="margin: 0 0 24px 0; color: #666; line-height: 1.5;">${escapeHtml(msg)}</p>
      <button id="closeMessageBtn" style="
        background: #2196f3;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
      ">${t('ok')}</button>
    </div>
  `;

  setTimeout(() => {
    const closeBtn = document.getElementById('closeMessageBtn');
    if (closeBtn) {
      closeBtn.onclick = () => {
        document.body.removeChild(modal);
        document.body.removeChild(modal._overlay);
      };
    }
  }, 100);
};

// Create modal with overlay
const createModal = () => {
  const existingModal = document.querySelector('.duplicate-finder-modal');
  const existingOverlay = document.querySelector('.duplicate-finder-overlay');
  const existingStyle = document.querySelector('#duplicate-finder-style');

  if (existingModal) document.body.removeChild(existingModal);
  if (existingOverlay) document.body.removeChild(existingOverlay);
  if (existingStyle) document.head.removeChild(existingStyle);

  const overlay = document.createElement('div');
  overlay.className = 'duplicate-finder-overlay';
  overlay.style.cssText = `
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    animation: fadeIn 0.2s ease;
  `;

  const modal = document.createElement('div');
  modal.className = 'duplicate-finder-modal';
  modal.style.cssText = `
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    z-index: 10000;
    max-width: 90%;
    max-height: 85vh;
    overflow-y: auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    animation: slideIn 0.3s ease;
  `;

  const style = document.createElement('style');
  style.id = 'duplicate-finder-style';
  style.textContent = `
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideIn { from { opacity: 0; transform: translate(-50%, -48%); } to { opacity: 1; transform: translate(-50%, -50%); } }
    .duplicate-finder-modal::-webkit-scrollbar { width: 8px; }
    .duplicate-finder-modal::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .duplicate-finder-modal::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
    .duplicate-finder-modal::-webkit-scrollbar-thumb:hover { background: #555; }
  `;
  document.head.appendChild(style);

  modal._overlay = overlay;

  document.body.appendChild(overlay);
  document.body.appendChild(modal);

  overlay.onclick = () => {
    document.body.removeChild(modal);
    document.body.removeChild(overlay);
    if (style.parentNode) document.head.removeChild(style);
  };

  return modal;
};

// ✅ Prevent modal HTML breaking when showing JSON/text
const escapeHtml = (str) => {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
};

// Auto-check on page load (optional)
onMounted(() => {
  // setTimeout(() => findDuplicates(), 1000);
});
</script>

<style scoped>
.spin-refresh {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.action-btn {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: rgba(var(--v-theme-surface), 0.7) !important;
  border: 1px solid rgba(var(--v-theme-outline), 0.15);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-btn:hover {
  background: rgba(var(--v-theme-surface), 0.9) !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.action-btn:active {
  transform: scale(0.95);
}
</style>
