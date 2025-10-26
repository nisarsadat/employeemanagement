<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Top Bar -->
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="6">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('د حساباتو لټون')"
          prepend-inner-icon="mdi-magnify"
          density="compact"
          variant="outlined"
          clearable
          @keyup.enter="fetchAll()"
          @click:clear="
            () => {
              repo.Search = '';
              fetchAll();
            }
          "
        />
      </v-col>
      <v-col cols="12" md="6" class="text-md-right">
        <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate()">
          {{ t('نوی حساب') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Table -->
    <v-card elevation="2">
      <v-data-table
        :headers="headers"
        :items="repo.Accounts"
        :items-length="repo.totalItems"
        :items-per-page="repo.itemsPerPage"
        :page="repo.page"
        :loading="repo.loading"
        item-key="id"
        @update:page="onPage"
        @update:items-per-page="onItemsPerPage"
        class="elevation-0"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >
        <template #loading>
          <div class="pa-6 text-center">{{ t('لېست د پورته کېدو په حال کې دی…') }}</div>
        </template>

        <template #item.price="{ item }">
          <span>{{ formatMoney(item.price) }}</span>
        </template>

        <template #item.account_type="{ item }">
          <span>{{ item?.account_type?.name ?? '—' }}</span>
        </template>

        <template #item.details="{ item }">
          <span class="text-truncate d-inline-block" style="max-width: 360px">{{
            item?.details
          }}</span>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            size="small"
            variant="text"
            color="primary"
            icon="mdi-pencil"
            :aria-label="t('سمون')"
            @click="openEdit(item.id)"
          />
          <v-btn
            size="small"
            variant="text"
            color="error"
            icon="mdi-delete"
            :aria-label="t('حذف')"
            @click="deleteAccount(item)"
          />
        </template>

        <template #bottom>
          <div class="px-4 py-3 d-flex align-center justify-space-between">
            <div class="text-caption">
              {{ t('ټول') }}: <strong>{{ repo.totalItems }}</strong>
            </div>
            <v-pagination
              v-model="repo.page"
              :length="pageCount"
              total-visible="7"
              @update:modelValue="fetchAll"
            />
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Create/Edit Dialog -->
    <AccountFormDialog
      v-model="dialogCreateEdit"
      :editMode="editMode"
      :value="draft"
      :accountTypeItems="accountTypeItems"
      :loadingTypes="loadingTypes"
      :busy="repo.loading"
      @save="onFormSave"
    />
  </v-container>
</template>

<script setup>
  import { ref, reactive, computed, onMounted } from 'vue';
  import axios from 'axios';
  import { useI18n } from 'vue-i18n';
  import { useAccountsRepository } from '@/stores/AccountsRepository';
  import AccountFormDialog from './AccountFormDialog.vue';

  const repo = useAccountsRepository();

  const { t, locale } = useI18n();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  // headers (localized)
  const headers = computed(() => [
    { title: t('نوم'), key: 'name', sortable: false },
    { title: t('بیه / قیمت'), key: 'price', sortable: false },
    { title: t('د حساب ډول'), key: 'account_type', sortable: false },
    { title: t('تفصیل'), key: 'details', sortable: false },
    { title: t('کړنې'), key: 'actions', sortable: false, align: 'end' },
  ]);

  const pageCount = computed(() => {
    const per = Number(repo.itemsPerPage || 10);
    return Math.max(1, Math.ceil(Number(repo.totalItems || 0) / per));
  });

  const dialogCreateEdit = ref(false);
  const editMode = ref(false);

  // draft used by the dialog
  const draft = reactive({
    id: null,
    name: '',
    price: 0,
    account_type_id: null,
    details: '',
  });

  // account types for select
  const accountTypeItems = ref([]);
  const loadingTypes = ref(false);
  const loadAccountTypes = async () => {
    loadingTypes.value = true;
    const resp = await axios.get(`/account-types?perPage=1000`);
    accountTypeItems.value = resp?.data?.data ?? [];
    loadingTypes.value = false;
  };

  // money format
  const formatMoney = (n) => {
    const num = Number(n ?? 0);
    return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  // fetch list
  const fetchAll = async () => {
    await repo.FetchAccountsData({ page: repo.page, itemsPerPage: repo.itemsPerPage });
  };

  const onPage = async (p) => {
    repo.page = p || 1;
    await fetchAll();
  };
  const onItemsPerPage = async (pp) => {
    repo.itemsPerPage = pp || 10;
    repo.page = 1;
    await fetchAll();
  };

  // open create
  const openCreate = async () => {
    editMode.value = false;
    Object.assign(draft, { id: null, name: '', price: 0, account_type_id: null, details: '' });
    if (!accountTypeItems.value.length) await loadAccountTypes();
    dialogCreateEdit.value = true;
  };

  // open edit
  const openEdit = async (id) => {
    editMode.value = true;
    await repo.FetchAccountData(id);
    const a = repo.Account || {};
    Object.assign(draft, {
      id: a.id ?? null,
      name: a.name ?? '',
      price: a.price ?? 0,
      account_type_id: a.account_type_id ?? null,
      details: a.details ?? '',
    });
    if (!accountTypeItems.value.length) await loadAccountTypes();
    dialogCreateEdit.value = true;
  };

  // save (create or update)
  const onFormSave = async (payload) => {
    if (editMode.value && payload.id) {
      await repo.UpdateAccount(payload.id, payload);
    } else {
      await repo.CreateAccount(payload);
    }
    dialogCreateEdit.value = false;
    await fetchAll();
  };

  // delete with 3 confirms
  const deleteAccount = async (item) => {
    if (!item?.id) return;
    const m = (s) => `${t('ایا د حذف کولو ډاډ لرئ؟')} "${item.name}"? (${s}/3)`;
    if (!window.confirm(m(1))) return;
    if (!window.confirm(m(2))) return;
    if (!window.confirm(m(3))) return;
    await repo.DeleteAccount(item.id);
    await fetchAll();
  };

  onMounted(async () => {
    await fetchAll();
    loadAccountTypes();
  });
  fetchAll();
</script>
