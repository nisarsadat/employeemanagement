import { computed } from 'vue';

export function useTablePrintExport(options = {}) {
  const {
    repo,
    moduleKey,
    getFilters,
    getHeaders,
    getSelectedColumnKeys,
    getTitle,
    getPrintLabels,
    getLocale,
    defaultFileName = 'table-print',
  } = options;

  const exportPayload = computed(() => {
    const filters = typeof getFilters === 'function' ? getFilters() : {};
    const headers = typeof getHeaders === 'function' ? getHeaders() : [];
    const selectedColumns =
      typeof getSelectedColumnKeys === 'function' ? getSelectedColumnKeys() : [];

    const columnLabels = headers.reduce((acc, h) => {
      if (h?.key) acc[h.key] = h.title ?? h.key;
      return acc;
    }, {});

    return {
      module: moduleKey,
      ...filters,
      locale: typeof getLocale === 'function' ? getLocale() : 'fa',
      title: typeof getTitle === 'function' ? getTitle() : defaultFileName,
      selected_columns: selectedColumns,
      column_labels: columnLabels,
      print_labels: typeof getPrintLabels === 'function' ? getPrintLabels() : {},
      file_name: defaultFileName,
    };
  });

  async function printAll() {
    if (!repo?.OpenResourcesPrintView) {
      throw new Error('OpenResourcesPrintView method is missing on repo.');
    }

    return await repo.OpenResourcesPrintView(exportPayload.value);
  }

  return {
    exportPayload,
    printAll,
  };
}