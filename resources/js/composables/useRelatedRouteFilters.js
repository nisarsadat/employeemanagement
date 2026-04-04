import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export function useRelatedRouteFilters() {
  const route = useRoute();
  const router = useRouter();

  function asNumber(value) {
    if (value === null || value === undefined || value === '') return null;
    const n = Number(value);
    return Number.isFinite(n) ? n : null;
  }

  function asString(value) {
    const v = String(value ?? '').trim();
    return v || null;
  }

  const relatedFilters = computed(() => ({
    office_employee_id: asNumber(route.query?.office_employee_id),
    resource_id: asNumber(route.query?.resource_id),
    information_id: asNumber(route.query?.information_id),
    event_id: asNumber(route.query?.event_id),
    fifty_six_format_id: asNumber(route.query?.fifty_six_format_id),

    date: asString(route.query?.date),
    date_from: asString(route.query?.date_from || route.query?.from_date),
    date_to: asString(route.query?.date_to || route.query?.to_date),
    from_date: asString(route.query?.from_date || route.query?.date_from),
    to_date: asString(route.query?.to_date || route.query?.date_to),

    type: asString(route.query?.type),
    stat: asString(route.query?.stat),

    open: asString(route.query?.open),
    source: asString(route.query?.source),
  }));

  function pushWithRelation(path, query = {}) {
    return router.push({
      path,
      query: cleanQuery(query),
    });
  }

  function clearRelationQueries(extraKeepKeys = []) {
    const keep = {};
    for (const key of extraKeepKeys) {
      if (route.query?.[key] != null && route.query?.[key] !== '') {
        keep[key] = route.query[key];
      }
    }

    return router.replace({
      path: route.path,
      query: keep,
    });
  }

  function cleanQuery(query) {
    return Object.fromEntries(
      Object.entries(query).filter(
        ([_, value]) => value !== null && value !== undefined && value !== ''
      )
    );
  }

  return {
    relatedFilters,
    pushWithRelation,
    clearRelationQueries,
    cleanQuery,
  };
}