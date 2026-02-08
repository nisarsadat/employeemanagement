// src/stores/ReportNoteRepository.js
import { defineStore } from 'pinia'
import axios from 'axios'

function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type']
    return
  }
  axios.defaults.headers.common['Content-Type'] = type
}

export const useReportNoteRepository = defineStore('ReportNoteRepository', {
  state: () => ({
    baseUrl: '/report-notes',
  }),

  actions: {
    async FetchReportNotesData(params = {}) {
      // params: { perPage, page, search, type }
      setContentType(null)
      const res = await axios.get(this.baseUrl, { params })
      return res.data
    },

    async FetchReportNoteData(id) {
      if (!id) throw new Error('id is required')
      setContentType(null)
      const res = await axios.get(`${this.baseUrl}/${id}`)
      return res.data
    },

    async CreateReportNote(payload) {
      // payload: { report_note, type }
      setContentType('application/json')
      const res = await axios.post(this.baseUrl, payload)
      return res.data
    },

    async UpdateReportNote(id, payload) {
      // payload: { report_note, type }
      if (!id) throw new Error('id is required')
      setContentType('application/json')
      const res = await axios.put(`${this.baseUrl}/${id}`, payload)
      return res.data
    },

    async DeleteReportNote(id) {
      if (!id) throw new Error('id is required')
      setContentType(null)
      const res = await axios.delete(`${this.baseUrl}/${id}`)
      return res.data
    },

    async BulkDeleteReportNotes(ids = []) {
      if (!Array.isArray(ids) || ids.length === 0) throw new Error('ids are required')
      setContentType('application/json')
      const res = await axios.post(`${this.baseUrl}/bulk-delete`, { ids })
      return res.data
    },
  },
})
