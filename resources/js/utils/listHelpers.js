// src/utils/listHelpers.js

export function cleanParams(obj = {}) {
  const out = {};

  Object.entries(obj).forEach(([key, value]) => {
    if (
      value !== undefined &&
      value !== null &&
      value !== '' &&
      !(Array.isArray(value) && value.length === 0)
    ) {
      out[key] = value;
    }
  });

  return out;
}

export function buildListParams(store, params = {}, options = {}) {
  const {
    includeSearch = true,
    includePagination = true,
    includeDateFilter = false,
    defaultDateField = 'join_date',
  } = options;

  const result = {};

  if (includePagination) {
    result.page = params.page ?? store.page ?? 1;
    result.perPage = params.perPage ?? params.itemsPerPage ?? store.itemsPerPage ?? 10;
  }

  if (includeSearch) {
    result.search = params.search ?? store.Search ?? '';
  }

  if (includeDateFilter) {
    result.year = params.year ?? null;
    result.month = params.month ?? null;
    result.date_field = params.date_field ?? defaultDateField;
  }

  if ('status' in params) {
    result.status = params.status;
  }

  return cleanParams(result);
}

export function applyPaginatedResponse(store, res, targetKey) {
  const items = res?.data?.data ?? res?.data ?? [];
  store[targetKey] = Array.isArray(items) ? items : [];
  store.totalItems = res?.data?.meta?.total ?? res?.data?.total ?? store[targetKey].length;
}
