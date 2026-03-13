import { defineStore } from 'pinia';
import axios from 'axios';

export const useLookupRepository = defineStore('LookupRepository', {
  state: () => ({
    resources: [],
    resourcesLoading: false,
    resourceSearch: '',
    resourceMap: {},

    officeEmployees: [],
    officeEmployeesLoading: false,
    officeEmployeeSearch: '',
    officeEmployeeMap: {},
  }),

  actions: {
    clearResources() {
      this.resources = [];
      this.resourceSearch = '';
    },

    clearOfficeEmployees() {
      this.officeEmployees = [];
      this.officeEmployeeSearch = '';
    },

    async FetchResourcesLookup({ search = '', perPage = 20 } = {}) {
      this.resourcesLoading = true;

      try {
        const { data } = await axios.get('/resources', {
          params: {
            search,
            perPage,
          },
        });

        const rows = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];

        this.resources = rows;
        this.resourceSearch = search;

        rows.forEach((row) => {
          if (row?.id != null) this.resourceMap[row.id] = row;
        });

        return rows;
      } catch (e) {
        console.error('FetchResourcesLookup error:', e);
        this.resources = [];
        throw e;
      } finally {
        this.resourcesLoading = false;
      }
    },

    async FetchOfficeEmployeesLookup({ search = '', perPage = 20 } = {}) {
      this.officeEmployeesLoading = true;

      try {
        const { data } = await axios.get('/office-employees', {
          params: {
            search,
            perPage,
          },
        });

        const rows = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];

        this.officeEmployees = rows;
        this.officeEmployeeSearch = search;

        rows.forEach((row) => {
          if (row?.id != null) this.officeEmployeeMap[row.id] = row;
        });

        return rows;
      } catch (e) {
        console.error('FetchOfficeEmployeesLookup error:', e);
        this.officeEmployees = [];
        throw e;
      } finally {
        this.officeEmployeesLoading = false;
      }
    },

    getResourceById(id) {
      return this.resourceMap[id] || this.resources.find((x) => x?.id === id) || null;
    },

    getOfficeEmployeeById(id) {
      return (
        this.officeEmployeeMap[id] || this.officeEmployees.find((x) => x?.id === id) || null
      );
    },
  },
});