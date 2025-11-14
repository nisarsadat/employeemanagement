<template>
  <v-container fluid class="mt-12 pl-6" :dir="isRtl ? 'rtl' : 'ltr'">
    <AppBar
      :title="t('Permissions')"
      :subtitle="t('Define granular abilities')"
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions><v-btn icon /></template>
    </AppBar>

    <!-- Search + Create -->
    <v-row class="d-flex align-center py-4">
      <v-col cols="12" md="9">
        <v-text-field
          v-model="searchModel"
          :label="t('Search permissions')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          hide-details
          clearable
        />
      </v-col>
      <v-col cols="12" md="3" class="d-flex justify-end gap-2">
        <v-btn color="primary" @click="openCreate">
          {{ t('Create Permission') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Table -->
    <v-data-table-server
      :items="repo.permissions"
      :items-length="repo.permissionsTotal"
      :page="repo.permissionsPage"
      :items-per-page="repo.permissionsPerPage"
      :headers="headers"
      :loading="repo.loading"
      item-key="id"
      class="elevation-0"
      @update:options="onTableChange"
    >
      <!-- # column -->
      <template #item.rownum="{ index }">
        {{ (repo.permissionsPage - 1) * repo.permissionsPerPage + index + 1 }}
      </template>

      <!-- Module column -->
      <template #item.module="{ item }">
        <v-chip size="small" variant="tonal">
          {{ splitName(item.name).module }}
        </v-chip>
      </template>

      <!-- Action column -->
      <template #item.action="{ item }">
        <v-chip size="small" color="primary" variant="tonal">
          {{ splitName(item.name).action }}
        </v-chip>
      </template>

      <!-- Guard column -->
      <template #item.guard_name="{ item }">
        <v-chip size="small" variant="tonal">{{ item.guard_name || 'web' }}</v-chip>
      </template>

      <!-- Actions menu -->
      <template #item.actions="{ item }">
        <v-menu>
          <template #activator="{ props }">
            <v-btn icon variant="text" v-bind="props">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list density="compact">
            <v-list-item @click="openRename(item)">
              <template #prepend><v-icon>mdi-pencil-outline</v-icon></template>
              <v-list-item-title>{{ t('Rename') }}</v-list-item-title>
            </v-list-item>
            <v-list-item @click="removePermission(item.id)">
              <template #prepend><v-icon>mdi-delete-outline</v-icon></template>
              <v-list-item-title>{{ t('Delete') }}</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>

      <template #no-data>
        <v-alert type="info" color="primary" variant="tonal" class="ma-4">
          {{ t('No permissions found. Create your first permission!') }}
        </v-alert>
      </template>
    </v-data-table-server>

    <!-- Create Dialog -->
    <v-dialog v-model="createDialog" max-width="520">
      <v-card>
        <v-card-title>{{ t('Create Permission') }}</v-card-title>
        <v-card-text>
          <v-text-field v-model="createName" :label="t('Permission name')" density="compact" />
          <v-select
            v-model="createGuard"
            :items="['web','sanctum']"
            :label="t('Guard')"
            density="compact"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="createDialog=false">{{ t('Cancel') }}</v-btn>
          <v-btn color="primary" :loading="repo.loading" @click="createPermission">
            {{ t('Create') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Rename Dialog -->
    <v-dialog v-model="renameDialog" max-width="520">
      <v-card>
        <v-card-title>{{ t('Rename Permission') }}</v-card-title>
        <v-card-text>
          <v-text-field v-model="renameName" :label="t('Permission name')" density="compact" />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="renameDialog=false">{{ t('Cancel') }}</v-btn>
          <v-btn color="primary" :loading="repo.loading" @click="renamePermission">
            {{ t('Save') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppBar from '@/components/AppBar.vue'
import { useRbacRepository } from '@/stores/RbacRepository'

const repo = useRbacRepository()
const { t, locale } = useI18n()
const isRtl = ref(locale.value === 'fa')
watch(locale, (l) => (isRtl.value = l === 'fa'))

const drawer = ref(false)

/** Table headers: #, Module, Action, Guard, Actions */
const headers = computed(() => [
  { title: '#', key: 'rownum', sortable: false, width: 70, align: 'end' },
  { title: t('Module'), key: 'module', sortable: true },
  { title: t('Action'), key: 'action', sortable: true, width: 140 },
  { title: t('Guard'), key: 'guard_name', sortable: true, width: 120 },
  { title: t('Action(s)'), key: 'actions', sortable: false, align: 'end', width: 120 },
])

/** Split 'module.action' → { module, action } with safe fallback */
const splitName = (name) => {
  if (!name) return { module: '', action: '' }
  const idx = name.lastIndexOf('.')
  if (idx === -1) return { module: name, action: '' }
  return { module: name.slice(0, idx), action: name.slice(idx + 1) }
}

// search binding
const searchModel = computed({
  get: () => repo.permissionsSearch,
  set: (val) => {
    repo.permissionsSearch = val || ''
    repo.permissionsPage = 1
    repo.fetchPermissions()
  }
})

const onTableChange = ({ page, itemsPerPage }) => {
  repo.permissionsPage = page
  repo.permissionsPerPage = itemsPerPage
  repo.fetchPermissions()
}

// dialogs
const createDialog = ref(false)
const renameDialog = ref(false)

const createName = ref('')
const createGuard = ref('web')
const renameName = ref('')
const editingId = ref(null)

const openCreate = () => {
  createName.value = ''
  createGuard.value = 'web'
  createDialog.value = true
}
const createPermission = async () => {
  if (!createName.value?.trim()) return
  await repo.createPermission(createName.value.trim(), createGuard.value)
  createDialog.value = false
}

const openRename = (perm) => {
  editingId.value = perm.id
  renameName.value = perm.name
  renameDialog.value = true
}
const renamePermission = async () => {
  await repo.updatePermission(editingId.value, renameName.value.trim())
  renameDialog.value = false
}

const removePermission = async (id) => {
  await repo.deletePermission(id)
}

// initial load
repo.fetchPermissions()
</script>
