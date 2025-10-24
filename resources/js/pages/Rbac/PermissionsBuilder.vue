<template>
  <div class="pa-4">
    <v-toolbar flat class="mb-4">
      <v-toolbar-title>Permissions Builder</v-toolbar-title>
      <v-spacer />
      <v-btn color="primary" variant="outlined" @click="load">Refresh</v-btn>
    </v-toolbar>

    <v-card class="mb-6" flat>
      <v-card-text>
        <v-form @submit.prevent="onCreateResource">
          <div class="flex items-end gap-3">
            <v-text-field
              v-model="newResource"
              label="Add Resource (e.g. employees)"
              placeholder="snake/slug only"
              hint="[a-z0-9_-]"
              persistent-hint
              clearable
              :rules="[v=>!/[^a-z0-9_\-]/.test(v||'') || 'Lowercase letters, digits, _ or - only']"
            />
            <v-btn color="primary" type="submit">Create CRUD</v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>

    <v-data-table
      :headers="headers"
      :items="resourceRows"
      item-key="resource"
      class="elevation-1"
      :loading="loading"
    >
      <template #item.view="{ item }">
        <v-checkbox-btn :model-value="has(item.resource,'view')" @update:model-value="(v)=>toggle(item,'view',v)" />
      </template>
      <template #item.create="{ item }">
        <v-checkbox-btn :model-value="has(item.resource,'create')" @update:model-value="(v)=>toggle(item,'create',v)" />
      </template>
      <template #item.update="{ item }">
        <v-checkbox-btn :model-value="has(item.resource,'update')" @update:model-value="(v)=>toggle(item,'update',v)" />
      </template>
      <template #item.delete="{ item }">
        <v-checkbox-btn :model-value="has(item.resource,'delete')" @update:model-value="(v)=>toggle(item,'delete',v)" />
      </template>
      <template #item.actions="{ item }">
        <v-btn icon color="error" @click="removeResource(item.resource)">
          <v-icon>mdi-delete</v-icon>
        </v-btn>
      </template>
      <template #no-data>
        <div class="py-8 text-medium-emphasis">No resources yet.</div>
      </template>
    </v-data-table>

    <v-snackbar v-model="snack.show" :color="snack.color" :timeout="2500">
      {{ snack.text }}
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRbacRepository } from '@/stores/RbacRepository'
import { useAuthRepository } from '@/stores/AuthRepository'
import { useRouter } from 'vue-router'

const auth = useAuthRepository()
const router = useRouter()

// admin-only access
if (!auth.hasRole('admin')) {
  router.replace('/403')
}

const repo = useRbacRepository()
const loading = ref(false)
const newResource = ref('')
const resources = ref([])
const permissions = ref([])

const headers = [
  { title: 'Resource', key: 'resource' },
  { title: 'view', key: 'view' },
  { title: 'create', key: 'create' },
  { title: 'update', key: 'update' },
  { title: 'delete', key: 'delete' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const resourceRows = computed(() => (resources.value || []).map(r => ({ resource: r })))

function has(resource, action) {
  const name = `${resource}.${action}`
  return Array.isArray(permissions.value) && permissions.value.some(p => p.name === name)
}

async function toggle(item, action, checked) {
  try {
    if (checked) {
      await repo.ensurePermission(item.resource, action)
      notify('Created ' + item.resource + '.' + action)
    } else {
      await repo.deletePermissionByName(item.resource, action)
      notify('Deleted ' + item.resource + '.' + action, 'warning')
    }
    await load()
  } catch (e) {
    notify('Action failed', 'error')
  }
}

async function onCreateResource() {
  const r = (newResource.value || '').trim().toLowerCase()
  if (!r) return
  try {
    loading.value = true
    await repo.createResource(r)
    newResource.value = ''
    notify('Created CRUD permissions for ' + r)
    await load()
  } catch (e) {
    notify('Create failed', 'error')
  } finally {
    loading.value = false
  }
}

async function removeResource(r) {
  if (!confirm(`Delete all permissions for \"${r}\"?`)) return
  try {
    loading.value = true
    await repo.deleteResource(r)
    notify('Deleted resource ' + r, 'warning')
    await load()
  } catch (e) {
    notify('Delete failed', 'error')
  } finally {
    loading.value = false
  }
}

async function load() {
  loading.value = true
  try {
    const [res, perms] = await Promise.all([
      repo.fetchResources(),
      repo.fetchPermissions(),
    ])
    resources.value = res
    permissions.value = perms
  } finally {
    loading.value = false
  }
}

const snack = ref({ show: false, text: '', color: 'success' })
function notify(text, color = 'success') {
  snack.value = { show: true, text, color }
}

onMounted(load)
</script>

<style scoped>
.flex { display: flex; }
.items-end { align-items: flex-end; }
.gap-3 { gap: 12px; }
</style>
