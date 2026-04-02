import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export function useRelatedRouteFilters() {
  const route = useRoute();
  const router = useRouter();

  const relatedFilters = computed(() => ({
    office_employee_id: route.query?.office_employee_id
      ? Number(route.query.office_employee_id)
      : null,

    resource_id: route.query?.resource_id ? Number(route.query.resource_id) : null,

    information_id: route.query?.information_id ? Number(route.query.information_id) : null,

    event_id: route.query?.event_id ? Number(route.query.event_id) : null,

    fifty_six_format_id: route.query?.fifty_six_format_id
      ? Number(route.query.fifty_six_format_id)
      : null,

    open: route.query?.open || null,
    source: route.query?.source || null,
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
      if (route.query?.[key] != null) keep[key] = route.query[key];
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
  };
}
