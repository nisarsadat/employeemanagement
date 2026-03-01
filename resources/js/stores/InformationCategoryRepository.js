import { defineStore } from 'pinia'
import axios from 'axios'

export const useInformationCategoryRepository = defineStore('InformationCategoryRepository', {
  state: () => ({
    loading: false,
    error: null,
  }),

  actions: {
    async FetchInformationCategories({ page = 1, perPage = 10000, search = '' } = {}) {
      this.loading = true
      this.error = null
      try {
        const res = await axios.get('/information-categories', {
          params: { page, perPage, search },
        })
        return res
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error while fetching information categories'
        throw e
      } finally {
        this.loading = false
      }
    },

    async FetchInformationCategory(id) {
      this.loading = true
      this.error = null
      try {
        return await axios.get(`/information-categories/${id}`)
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error while fetching information category'
        throw e
      } finally {
        this.loading = false
      }
    },

    async CreateInformationCategory(payload) {
      this.loading = true
      this.error = null
      try {
        return await axios.post('/information-categories', payload)
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error while creating information category'
        throw e
      } finally {
        this.loading = false
      }
    },

    async UpdateInformationCategory(id, payload) {
      this.loading = true
      this.error = null
      try {
        return await axios.put(`/information-categories/${id}`, payload)
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error while updating information category'
        throw e
      } finally {
        this.loading = false
      }
    },

    async DeleteInformationCategory(id) {
      this.loading = true
      this.error = null
      try {
        return await axios.delete(`/information-categories/${id}`)
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error while deleting information category'
        throw e
      } finally {
        this.loading = false
      }
    },
  },
})