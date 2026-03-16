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
  // supports BOTH:
  // 1) full axios response
  // 2) already-unwrapped response.data
  const body =
    res && typeof res === 'object' && 'data' in res && ('status' in res || 'headers' in res)
      ? res.data
      : res;

  const items = Array.isArray(body?.data) ? body.data : Array.isArray(body) ? body : [];

  store[targetKey] = items;

  store.totalItems = Number(
    body?.meta?.total ??
      body?.total ??
      body?.totalItems ??
      body?.total_items ??
      body?.['total-items'] ??
      items.length
  );

  if (body?.meta?.current_page) {
    store.page = Number(body.meta.current_page);
  }

  if (body?.meta?.per_page) {
    store.itemsPerPage = Number(body.meta.per_page);
  }
}
