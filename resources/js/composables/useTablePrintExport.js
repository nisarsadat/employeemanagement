import { computed } from 'vue';

export function useTablePrintExport(options = {}) {
  const {
    repo,
    moduleKey,
    getFilters,
    getHeaders,
    getSelectedColumnKeys,
    defaultFileName = 'table-print',
  } = options;

  const exportPayload = computed(() => {
    const filters = typeof getFilters === 'function' ? getFilters() : {};
    const headers = typeof getHeaders === 'function' ? getHeaders() : [];
    const selectedColumns =
      typeof getSelectedColumnKeys === 'function' ? getSelectedColumnKeys() : [];

    return {
      module: moduleKey,
      filters,
      selected_columns: selectedColumns,
      headers: headers.map((h) => ({
        key: h.key,
        title: h.title,
      })),
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