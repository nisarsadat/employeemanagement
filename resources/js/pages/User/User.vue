<template>
  <CreateUser v-if="UsersRepository.createDialog" />
  <UpdateUser v-if="UsersRepository.updateDialog" :key="UsersRepository.User.id || 'update-user'" />

  <v-container fluid class="mt-12 pl-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar :title="t('User')" :subtitle="t('Add a User')" @toggle-drawer="drawer = !drawer">
      <template #actions><v-btn icon /></template>
    </AppBar>

    <v-row class="d-flex align-center py-4">
      <v-col cols="12" md="11">
        <v-text-field
          v-model="search"
          :label="t('جستجو')"
          append-inner-icon="mdi-search-web"
          density="compact"
          hide-details
          variant="underlined"
          clearable
          class="rounded"
        />
      </v-col>

      <v-col cols="12" md="1" class="text-right">
        <v-btn v-if="auth.can('users.create')" color="primary" variant="flat" @click="createPopUp">
          <v-icon left>mdi-plus</v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <v-data-table-server
      v-model:page="UsersRepository.page"
      v-model:items-per-page="UsersRepository.itemsPerPage"
      :headers="headers"
      :items="UsersRepository.Users || []"
      :items-length="UsersRepository.totalItems || 0"
      :loading="UsersRepository.loading"
      :search="UsersRepository.Search"
      item-value="id"
      item-key="id"
      class="elevation-0 custom-table"
      :items-per-page-text="t('ItemsPerPage')"
      show-select
      hover
      :dir="isRtl ? 'rtl' : 'ltr'"
      @update:options="(opts) => UsersRepository.FetchUsersData(opts)"
    >
      <template #loading>
        <v-skeleton-loader type="list-item, heading, divider, actions" />
      </template>

      <template #item.image_url="{ item }">
        <v-avatar size="36" class="my-1">
          <v-img
            v-if="item?.image_url || item?.raw?.image_url"
            :src="item.image_url ?? item.raw?.image_url"
            cover
            :alt="item?.username ?? item?.raw?.username ?? 'avatar'"
            class="clickable"
            @click="openPreview(item.image_url ?? item.raw?.image_url)"
          />
          <v-icon v-else size="20" color="grey">mdi-account-circle</v-icon>
        </v-avatar>
      </template>

      <template #item.roles="{ item }">
        <div class="d-flex flex-wrap gap-1">
          <v-chip
            v-for="role in Array.isArray(item.roles) ? item.roles : []"
            :key="role"
            :color="stringToColor(role)"
            variant="flat"
            class="role-chip text-capitalize px-3"
            size="small"
          >
            {{ role }}
          </v-chip>
        </div>
      </template>

      <template #item.is_online="{ item }">
        <v-chip
          :color="item?.is_online ?? item?.raw?.is_online ? 'success' : 'grey'"
          variant="flat"
          size="small"
        >
          {{ item?.is_online ?? item?.raw?.is_online ? 'Online' : 'Offline' }}
        </v-chip>
      </template>

      <template #item.current_login_duration="{ item }">
        {{ item?.current_login_duration ?? item?.raw?.current_login_duration ?? '-' }}
      </template>

      <template #item.last_session_duration="{ item }">
        {{ item?.last_session_duration ?? item?.raw?.last_session_duration ?? 'Last session: 0m' }}
      </template>

      <template #item.total_login_duration="{ item }">
        {{ item?.total_login_duration ?? item?.raw?.total_login_duration ?? 'Total: 0m' }}
      </template>

      <template #item.today_login_duration="{ item }">
        {{ item?.today_login_duration ?? item?.raw?.today_login_duration ?? '0m' }}
      </template>

      <template #item.actions="{ item }">
        <v-menu>
          <template #activator="{ props }">
            <v-btn icon variant="text" color="grey-darken-1" v-bind="props">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>

          <v-list density="compact">
            <v-list-item
              v-if="auth.can('users.update')"
              :disabled="!rowId(item)"
              @click="
                () => {
                  const id = rowId(item);
                  if (id) editItem(id);
                }
              "
            >
              <template #prepend>
                <v-icon color="grey-darken-1">mdi-pencil-outline</v-icon>
              </template>
              <v-list-item-title>{{ t('ایدیت') }}</v-list-item-title>
            </v-list-item>

            <v-list-item
              v-if="auth.can('users.delete')"
              :disabled="!rowId(item)"
              @click="openDeleteDialog(item)"
            >
              <template #prepend>
                <v-icon color="grey-darken-1">mdi-delete-outline</v-icon>
              </template>
              <v-list-item-title>{{ t('دلیت') }}</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>

      <template #no-data>
        <v-alert type="info" color="primary" variant="tonal" class="ma-4">
          {{ t('استفاده کننده دریافت نشد لطفا یکی را اضافه کنید!') }}
        </v-alert>
      </template>
    </v-data-table-server>
  </v-container>

  <v-dialog v-model="previewOpen" max-width="520">
    <v-card rounded="xl">
      <v-card-text class="pa-0">
        <v-img :src="previewSrc" aspect-ratio="1" cover />
      </v-card-text>
      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="previewOpen = false">{{ t('Close') }}</v-btn>
        <v-btn :href="previewSrc" target="_blank" variant="flat" color="primary">
          {{ t('Open original') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
  <DeleteConfirmDialog
    v-model="deleteDialog"
    :target="deleteTarget"
    :is-rtl="isRtl"
    @confirm="handleDeleteConfirmed"
  />
</template>

<script setup>
  import { ref, watch, computed, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '../../components/AppBar.vue';
  import CreateUser from './CreateUser.vue';
  import UpdateUser from './UpdateUser.vue';
  import { useUsersRepository } from '../../stores/UsersRepository';
  import { useLoginRepository } from '../../stores/LoginRepository';
  import { usePageRefresh } from '@/composables/usePageRefresh';
  import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
  const auth = useLoginRepository();
  const UsersRepository = useUsersRepository();
  const deleteDialog = ref(false);
  const deleteTarget = ref(null);
  const drawer = ref(false);
  const { t, locale } = useI18n();
  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  onMounted(() => {
    UsersRepository.FetchUsersData();
    UsersRepository.FetchRolesAll();
  });
  usePageRefresh('/app/users', UsersRepository.FetchUsersData);

  const headers = computed(() => [
    { title: t('انتخاب'), key: 'data-table-select', sortable: false, align: 'end' },
    { title: t('تصویر'), key: 'image_url', sortable: false },
    { title: t('نام کاربر'), key: 'username', sortable: true },
    { title: t('نقش'), key: 'roles', sortable: false },
    { title: t('حالت'), key: 'is_online', sortable: false },
    { title: t('فعلی'), key: 'current_login_duration', sortable: false },
    { title: t('آخرین سیشن'), key: 'last_session_duration', sortable: false },
    { title: t('مجموعه'), key: 'total_login_duration', sortable: false },
    { title: t('امروز'), key: 'today_login_duration', sortable: false },
    { title: t('عملیات'), key: 'actions', sortable: false, align: 'end' },
  ]);

  const search = computed({
    get: () => UsersRepository.Search,
    set: (val) => {
      UsersRepository.Search = val || '';
      UsersRepository.page = 1;
      UsersRepository.FetchUsersData();
    },
  });

  const rowId = (it) => it?.raw?.id ?? it?.id ?? null;

  const createPopUp = () => {
    UsersRepository.createDialog = true;
  };

  const editItem = async (id) => {
    await UsersRepository.startEdit(id);
  };

  const openDeleteDialog = (payload) => {
    const row = payload?.raw ?? payload;
    if (!row?.id) return;
    deleteTarget.value = row;
    deleteDialog.value = true;
  };

  const handleDeleteConfirmed = async (target) => {
    const row = target || deleteTarget.value;

    if (!row?.id) {
      deleteDialog.value = false;
      deleteTarget.value = null;
      return;
    }

    try {
      await UsersRepository.DeleteUser(row.id);
      await UsersRepository.FetchUsersData();
    } finally {
      deleteDialog.value = false;
      deleteTarget.value = null;
    }
  };
  function stringToColor(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }
    const hue = Math.abs(hash) % 360;
    const sat = 65;
    const light = 55;
    return `hsl(${hue}, ${sat}%, ${light}%)`;
  }

  const previewSrc = ref(null);
  const previewOpen = ref(false);

  const openPreview = (src) => {
    if (!src) return;
    previewSrc.value = src;
    previewOpen.value = true;
  };
</script>

<style scoped>
  .v-data-table {
    border-radius: 4px;
    overflow: hidden;
  }
  .v-data-table-header {
    background-color: rgba(0, 0, 0, 0.02);
  }
  .v-data-table-header th {
    font-weight: 600;
    letter-spacing: 0.5px;
  }
  .clickable {
    cursor: zoom-in;
  }
  .role-chip {
    color: white;
  }
</style>
