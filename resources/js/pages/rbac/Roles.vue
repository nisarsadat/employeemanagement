<!-- src/pages/rbac/Roles.vue -->
<template>
  <v-container fluid class="mt-12 pl-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('Roles')"
      :subtitle="t('Manage roles & permissions')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search + Create -->
    <v-row class="d-flex align-center py-4">
      <v-col cols="12" md="9">
        <v-text-field
          v-model="searchModel"
          :label="t('Search roles')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          hide-details
          clearable
        />
      </v-col>
      <v-col cols="12" md="3" class="d-flex justify-end gap-2">
        <v-btn color="primary" v-if="auth.can('roles.create')" @click="openCreate">
          {{ t('Create Role') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Table -->
    <v-data-table-server
      :items="repo.roles"
      :items-length="repo.rolesTotal"
      :page="repo.rolesPage"
      :items-per-page="repo.rolesPerPage"
      :headers="headers"
      :loading="repo.loading"
      item-key="id"
      class="elevation-0"
      @update:options="onTableChange"
    >
      <template #item.permissions_count="{ item }">
        <v-chip size="small" color="primary" variant="tonal">
          {{ item.permissions?.length ?? item.permissions_count ?? 0 }}
        </v-chip>
      </template>

      <!-- Pashto—English chips -->
      <template #item.permissions="{ item }">
        <div class="d-flex flex-wrap ga-1">
          <v-chip
            v-for="p in item.permissions || []"
            :key="p"
            size="x-small"
            variant="tonal"
            :title="p"
          >
            {{ labelForPermissionBilingual(p) }}
          </v-chip>
        </div>
      </template>

      <template #item.actions="{ item }">
        <v-menu>
          <template #activator="{ props }">
            <v-btn icon variant="text" v-bind="props"><v-icon>mdi-dots-vertical</v-icon></v-btn>
          </template>
          <v-list density="compact">
            <v-list-item @click="openRename(item)" v-if="auth.can('roles.create')">
              <template #prepend><v-icon>mdi-pencil-outline</v-icon></template>
              <v-list-item-title>{{ t('Rename') }}</v-list-item-title>
            </v-list-item>

            <!-- ✅ now routes to page -->
            <v-list-item @click="openAssign(item)" v-if="auth.can('roles.create')">
              <template #prepend><v-icon>mdi-shield-key</v-icon></template>
              <v-list-item-title>{{ t('Assign permissions') }}</v-list-item-title>
            </v-list-item>

            <v-list-item @click="removeRole(item.id)" v-if="auth.can('roles.delete')">
              <template #prepend><v-icon>mdi-delete-outline</v-icon></template>
              <v-list-item-title>{{ t('Delete') }}</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>

      <template #no-data>
        <v-alert type="info" color="primary" variant="tonal" class="ma-4">
          {{ t('No roles found. Create your first role!') }}
        </v-alert>
      </template>
    </v-data-table-server>

    <!-- Create Dialog -->
    <v-dialog v-model="createDialog" max-width="520">
      <v-card>
        <v-card-title>{{ t('Create Role') }}</v-card-title>
        <v-card-text>
          <v-text-field v-model="createName" :label="t('Role name')" density="compact" />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="createDialog = false">{{ t('Cancel') }}</v-btn>
          <v-btn color="primary" :loading="repo.loading" @click="createRole">
            {{ t('Create') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Rename Dialog -->
    <v-dialog v-model="renameDialog" max-width="520">
      <v-card>
        <v-card-title>{{ t('Rename Role') }}</v-card-title>
        <v-card-text>
          <v-text-field v-model="renameName" :label="t('Role name')" density="compact" />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="renameDialog = false">{{ t('Cancel') }}</v-btn>
          <v-btn color="primary" :loading="repo.loading" @click="renameRole">{{ t('Save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import AppBar from '@/components/AppBar.vue'
import { useRbacRepository } from '@/stores/RbacRepository'
import { useLoginRepository } from '@/stores/LoginRepository'

// 🔤 Pashto—English mapping helpers
import {
  mapPermNamesToItemsBilingual as mapPermNamesToItems, // (kept, in case you use later)
  labelForPermissionBilingual as labelForPermission,
} from '../../../composables/usePermI18n'

const auth = useLoginRepository()
const repo = useRbacRepository()
const router = useRouter()
const { t, locale } = useI18n()

const isRtl = ref(locale.value === 'fa' || locale.value === 'ps')
watch(locale, (l) => (isRtl.value = l === 'fa' || l === 'ps'))

const drawer = ref(false)

const headers = computed(() => [
  { title: t('ID'), key: 'id', sortable: true, width: 80 },
  { title: t('Name'), key: 'name', sortable: true },
  { title: t('Permissions'), key: 'permissions_count', sortable: false, width: 220 },
  { title: t('Action'), key: 'actions', sortable: false, align: 'end', width: 120 },
])

// search binding
const searchModel = computed({
  get: () => repo.rolesSearch,
  set: (val) => {
    repo.rolesSearch = val || ''
    repo.rolesPage = 1
    repo.fetchRoles()
  },
})

const onTableChange = ({ page, itemsPerPage }) => {
  repo.rolesPage = page
  repo.rolesPerPage = itemsPerPage
  repo.fetchRoles()
}

// dialogs
const createDialog = ref(false)
const renameDialog = ref(false)

const createName = ref('')
const renameName = ref('')

// expose in template
const labelForPermissionBilingual = labelForPermission

const openCreate = () => {
  createName.value = ''
  createDialog.value = true
}
const createRole = async () => {
  if (!createName.value?.trim()) return
  await repo.createRole(createName.value.trim())
  createDialog.value = false
}

const openRename = (role) => {
  repo.currentRole.id = role.id
  repo.currentRole.name = role.name
  renameName.value = role.name
  renameDialog.value = true
}
const renameRole = async () => {
  await repo.updateRole(repo.currentRole.id, renameName.value.trim())
  renameDialog.value = false
}

/** ✅ route to assign page */
const openAssign = (role) => {
  router.push({ name: 'RolePermissions', params: { id: role.id } })
}

const removeRole = async (id) => {
  await repo.deleteRole(id)
}

// initial load
repo.fetchRoles()
repo.fetchPermissions?.()
</script>

<style scoped>
.ga-1 {
  gap: 4px;
}
</style>
