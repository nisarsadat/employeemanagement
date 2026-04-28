// src/stores/JawasiRepository.js
import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { buildListParams, applyPaginatedResponse } from '@/utils/listHelpers';

axios.defaults.headers.common['Accept'] = 'application/json';

function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
    return;
  }
  axios.defaults.headers.common['Content-Type'] = type;
}

function errText(e, fallback = 'یوه ستونزه رامنځته شوه') {
  return e?.response?.data?.message || e?.message || (typeof e === 'string' ? e : fallback);
}

export const useJawasiRepository = defineStore('JawasiRepository', {
  state() {
    return {
      router: useRouter(),

      page: 1,
      itemsPerPage: 10,
      totalItems: 0,

      // done
      Jawasis: reactive([]),
      Jawasi: reactive({}),

      resourcecode: reactive([]),

      isLoading: false,
      loading: false,
      error: null,

      Search: '',
      rail: false,
      drawerVisible: true,
    };
  },

  actions: {
    toggleRail() {
      this.rail = !this.rail;
    },
    toggleDrawerVisible() {
      this.drawerVisible = !this.drawerVisible;
    },
    setDrawerVisible(v) {
      this.drawerVisible = !!v;
    },

    async GetResourceCode() {
      this.loading = true;
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/resources`);
        this.resourcecode = data?.data ?? data ?? [];
      } catch (e) {
        console.error(e);
        this.error = e;
        this.resourcecode = [];
      } finally {
        this.loading = false;
      }
    },

    async FetchJawasisData(params = {}) {
      this.loading = true;
      try {
        setContentType('application/json');

        const baseParams = buildListParams(
          this,
          {},
          {
            includeSearch: true,
            includePagination: true,
            includeDateFilter: false,
          }
        );

        const queryParams = {
          ...baseParams,
          ...params,
        };

        const { data: res } = await axios.get(`jawasis`, {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'Jawasis');
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Jawasis = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },

    async FetchJawasiFilterOptions(params = {}) {
      try {
        setContentType('application/json');

        const { data } = await axios.get(`/jawasis/filter-options`, {
          params,
        });

        return data;
      } catch (e) {
        console.error(e);
        this.error = e;
        return { data: { options: [] } };
      }
    },

    async FetchJawasiData(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.get(`/jawasis/${id}`);
        this.Jawasi = data?.data ?? {};
      } catch (e) {
        console.error(e);
        this.error = e;
        this.Jawasi = {};
      }
    },

    async CreateJawasi(formData) {
      try {
        setContentType(null);
        const res = await axios.post(`/jawasis`, formData);
        const saved = res.data?.data ?? res.data;
        toast.success('د جواسیس رېکارډ په بریالیتوب سره اضافه شو', { autoClose: 1200 });
        return saved;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ اضافه نشو'));
        throw e;
      }
    },

    async UpdateJawasi(id, data) {
      try {
        let res;
        if (data instanceof FormData) {
          data.append('_method', 'PUT');
          setContentType(null);
          res = await axios.post(`/jawasis/${id}`, data);
        } else {
          setContentType('application/json');
          res = await axios.put(`/jawasis/${id}`, data);
        }
        toast.success('د جواسیس رېکارډ په بریالیتوب سره بدل شو', { autoClose: 1200 });
        return res.data?.data ?? res.data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'رېکارډ بدل نشو'));
        throw e;
      }
    },

    async DeleteJawasi(id) {
      try {
        await axios.delete(`/jawasis/${id}`);
        toast.success('د جواسیس رېکارډ په بریالیتوب سره حذف شو', { autoClose: 1200 });
        await this.FetchJawasisData({ page: this.page, itemsPerPage: this.itemsPerPage });
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'حذف ناکام شو'));
        throw e;
      }
    },
    async OpenJawasisPrintView(payload = {}) {
      const {
        selected_columns,
        column_labels,
        print_labels,
        locale,
        title,
        file_name,
        module,
        filters,
        ...topLevelFilters
      } = payload;

      const params = {
        ...(filters || {}),
        ...topLevelFilters,

        selected_columns: selected_columns || [],
        column_labels: column_labels || {},
        print_labels: print_labels || {},
        locale: locale || 'fa',
        title: title || '',
        file_name: file_name || 'jawasis-print',
      };

      const res = await axios.get('/jawasis/print', {
        params,
        responseType: 'text',
      });

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        throw new Error('Print window blocked');
      }

      printWindow.document.open();
      printWindow.document.write(res.data);
      printWindow.document.close();
      printWindow.focus();
    },
    async DownloadJawasiZip(id, name = `jawasi_${id}`) {
      try {
        setContentType(null);

        const response = await axios.get(`/jawasis/${id}/download-zip`, {
          responseType: 'blob',
          withCredentials: true,
        });

        const contentType = response.headers['content-type'] || '';
        if (!contentType.includes('zip')) {
          const text =
            (await response.data.text?.()) ??
            (await new Promise((resolve) => {
              const reader = new FileReader();
              reader.onload = () => resolve(reader.result);
              reader.readAsText(response.data);
            }));

          console.error('DownloadJawasiZip not zip:', text);
          toast.error('سرور د ZIP فایل بېرته را ونه لېږه، کېدای شي کومه تېروتنه وي.');
          return;
        }

        const blob = new Blob([response.data], { type: 'application/zip' });
        const url = window.URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `${name}.zip`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        window.URL.revokeObjectURL(url);
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'د اسنادو ډاونلوډ ناکام شو'));
        throw e;
      }
    },
    async DeleteJawasiPicture(id) {
      try {
        setContentType('application/json');
        const { data } = await axios.delete(`/jawasis/${id}/picture`);
        toast.success('عکس په بریالیتوب سره حذف شو', { autoClose: 1200 });
        return data?.data ?? data;
      } catch (e) {
        console.error(e);
        this.error = e;
        toast.error(errText(e, 'عکس حذف نه شو'));
        throw e;
      }
    },
  },
});
