<template>
  <v-container fluid class="pt-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Top Bar -->
    <v-row class="align-center mb-4" dense>
      <v-col cols="12" md="6">
        <v-text-field
          v-model.trim="repo.Search"
          :label="t('لټون: د حساب ډول')"
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
          {{ t('نوی حساب ډول') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Table -->
    <v-card elevation="2">
      <v-data-table
        :headers="headers"
        :items="repo.AccountTypes"
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

        <template #item.actions="{ item }">
          <v-btn
            size="small"
            variant="text"
            color="primary"
            icon="mdi-pencil"
            @click="openEdit(item.id)"
          />
          <v-btn
            size="small"
            variant="text"
            color="error"
            icon="mdi-delete"
            @click="deleteType(item)"
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
    <AccountTypeFormDialog
      v-model="dialogCreateEdit"
      :editMode="editMode"
      :value="draft"
      :busy="repo.loading"
      @save="onFormSave"
    />
  </v-container>
</template>

<script setup>
  import { ref, reactive, computed, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useAccountsRepository } from '@/stores/AccountsRepository'; // SAME REPO
  import AccountTypeFormDialog from './AccountTypeFormDialog.vue';

  const repo = useAccountsRepository();

  const { t, locale } = useI18n();
  const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''));

  // headers (localized)
  const headers = computed(() => [
    { title: t('نوم'), key: 'name', sortable: false },
    { title: t('یادونه'), key: 'note', sortable: false },
    { title: t('کړنې'), key: 'actions', sortable: false, align: 'end' },
  ]);

  const pageCount = computed(() => {
    const per = Number(repo.itemsPerPage || 10);
    return Math.max(1, Math.ceil(Number(repo.totalItems || 0) / per));
  });

  const dialogCreateEdit = ref(false);
  const editMode = ref(false);
  const draft = reactive({ id: null, name: '', note: '' });

  const fetchAll = async () => {
    await repo.FetchAccountTypesData({ page: repo.page, itemsPerPage: repo.itemsPerPage });
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

  // Create
  const openCreate = () => {
    editMode.value = false;
    Object.assign(draft, { id: null, name: '', note: '' });
    dialogCreateEdit.value = true;
  };

  // Edit
  const openEdit = async (id) => {
    editMode.value = true;
    await repo.FetchAccountTypeData(id);
    const t0 = repo.AccountType || {};
    Object.assign(draft, {
      id: t0.id ?? null,
      name: t0.name ?? '',
      note: t0.note ?? '',
    });
    dialogCreateEdit.value = true;
  };

  // Save
  const onFormSave = async (payload) => {
    if (editMode.value && payload.id) {
      await repo.UpdateAccountType(payload.id, payload);
    } else {
      await repo.CreateAccountType(payload);
    }
    dialogCreateEdit.value = false;
    await fetchAll();
  };

  // Delete (3 confirms, no popup)
  const deleteType = async (item) => {
    if (!item?.id) return;
    const msg = (s) => `${t('ایا د حذف کولو ډاډ لرئ؟')} "${item.name}"? (${s}/3)`;
    if (!window.confirm(msg(1))) return;
    if (!window.confirm(msg(2))) return;
    if (!window.confirm(msg(3))) return;
    await repo.DeleteAccountType(item.id);
    await fetchAll();
  };

  onMounted(fetchAll);
</script>
