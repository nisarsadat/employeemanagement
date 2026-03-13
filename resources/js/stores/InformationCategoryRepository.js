import { defineStore } from 'pinia'
import axios from 'axios'
import { buildListParams, applyPaginatedResponse } from '@/utils/listHelpers'

export const useInformationCategoryRepository = defineStore('InformationCategoryRepository', {
  state: () => ({
    page: 1,
    itemsPerPage: 10,
    totalItems: 0,

    Search: '',

    InformationCategories: [],
    InformationCategory: {},

    loading: false,
    error: null,
  }),

  actions: {
    async FetchInformationCategories() {
      this.loading = true
      this.error = null
      try {
        const queryParams = buildListParams(this, {
          includeSearch: true,
          includePagination: true,
        })

        const res = await axios.get('/information-categories', {
          params: queryParams,
        })

        applyPaginatedResponse(this, res, 'InformationCategories')
        return res.data
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error while fetching information categories'
        this.InformationCategories = []
        this.totalItems = 0
        throw e
      } finally {
        this.loading = false
      }
    },

    async FetchInformationCategory(id) {
      this.loading = true
      this.error = null
      try {
        const res = await axios.get(`/information-categories/${id}`)
        this.InformationCategory = res?.data?.data ?? res?.data ?? {}
        return res
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
        const res = await axios.delete(`/information-categories/${id}`)
        await this.FetchInformationCategories()
        return res
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error while deleting information category'
        throw e
      } finally {
        this.loading = false
      }
    },
  },
})