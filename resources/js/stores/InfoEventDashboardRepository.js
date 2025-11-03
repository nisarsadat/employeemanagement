// resources/js/stores/InfoEventDashboardRepository.js
import { defineStore } from 'pinia'
import axios from 'axios'
import { reactive } from 'vue'

axios.defaults.headers.common['Accept'] = 'application/json'

function errText(e, fb = 'ستونزه رامنځته شوه') {
  return e?.response?.data?.message || e?.message || fb
}

export const useInfoEventDashboardRepository = defineStore('InfoEventDashboardRepository', () => {

  const state = reactive({
    loading: false,
    error: null,

    // Filters
    From: '',
    To: '',
    Zone: '',
    Hawza: '',
    ResourceId: null,
    EmployeeId: null,
    GroupBy: 'month', // day|week|month

    // Data
    Cards: [],
    EventsOverTime: [],
    InformationOverTime: [],
    EmployeesByZone: [],
    TopEmployeesByResources: [],
    RecentEvents: [],
    RecentInformation: [],
  })

  function buildQuery(extra = {}) {
    return {
      from: state.From || undefined,
      to: state.To || undefined,
      zone: state.Zone || undefined,
      hawza: state.Hawza || undefined,
      resource_id: state.ResourceId || undefined,
      employee_id: state.EmployeeId || undefined,
      group_by: state.GroupBy || undefined,
      ...extra,
    }
  }

  async function fetchCards() {
    try {
      state.loading = true;
      const { data } = await axios.get('/dashboard/info-events/cards', { params: buildQuery() })
      state.Cards = data.cards || []
    } catch (e) {
      state.error = errText(e)
    } finally {
      state.loading = false
    }
  }

  async function fetchEventsOverTime() {
    try {
      state.loading = true;
      const { data } = await axios.get('/dashboard/info-events/charts/events-over-time', { params: buildQuery() })
      state.EventsOverTime = data.series || []
    } catch (e) {
      state.error = errText(e)
    } finally {
      state.loading = false
    }
  }

  async function fetchInformationOverTime() {
    try {
      state.loading = true;
      const { data } = await axios.get('/dashboard/info-events/charts/information-over-time', { params: buildQuery() })
      state.InformationOverTime = data.series || []
    } catch (e) {
      state.error = errText(e)
    } finally {
      state.loading = false
    }
  }

  async function fetchEmployeesByZone() {
    try {
      state.loading = true;
      const { data } = await axios.get('/dashboard/info-events/charts/employees-by-zone', { params: buildQuery() })
      state.EmployeesByZone = data.rows || []
    } catch (e) {
      state.error = errText(e)
    } finally {
      state.loading = false
    }
  }

  async function fetchTopEmployeesByResources() {
    try {
      state.loading = true;
      const { data } = await axios.get('/dashboard/info-events/tables/top-employees-by-resources', { params: buildQuery() })
      state.TopEmployeesByResources = data.rows || []
    } catch (e) {
      state.error = errText(e)
    } finally {
      state.loading = false
    }
  }

  async function fetchRecentEvents(limit = 10) {
    try {
      state.loading = true;
      const { data } = await axios.get('/dashboard/info-events/tables/recent-events', {
        params: buildQuery({ limit })
      })
      state.RecentEvents = data.rows || []
    } catch (e) {
      state.error = errText(e)
    } finally {
      state.loading = false
    }
  }

  async function fetchRecentInformation(limit = 10) {
    try {
      state.loading = true;
      const { data } = await axios.get('/dashboard/info-events/tables/recent-information', {
        params: buildQuery({ limit })
      })
      state.RecentInformation = data.rows || []
    } catch (e) {
      state.error = errText(e)
    } finally {
      state.loading = false
    }
  }

  async function fetchAll() {
    await Promise.all([
      fetchCards(),
      fetchEventsOverTime(),
      fetchInformationOverTime(),
      fetchEmployeesByZone(),
      fetchTopEmployeesByResources(),
      fetchRecentEvents(),
      fetchRecentInformation(),
    ])
  }

  function resetFilters() {
    state.From = ''
    state.To = ''
    state.Zone = ''
    state.Hawza = ''
    state.ResourceId = null
    state.EmployeeId = null
    state.GroupBy = 'month'
  }

  return {
    state,
    fetchAll,
    fetchCards,
    fetchEventsOverTime,
    fetchInformationOverTime,
    fetchEmployeesByZone,
    fetchTopEmployeesByResources,
    fetchRecentEvents,
    fetchRecentInformation,
    resetFilters
  }
})
