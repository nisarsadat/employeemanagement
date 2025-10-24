<template>
  <div class="pa-4">
    <v-toolbar flat class="mb-4">
      <v-toolbar-title>Assign Roles & Permissions</v-toolbar-title>
    </v-toolbar>

    <v-row>
      <v-col cols="12" md="4">
        <v-select
          label="Select User"
          :items="users"
          item-title="username"
          item-value="id"
          v-model="selectedUserId"
          @update:model-value="onSelectUser"
        />
      </v-col>

      <v-col cols="12" md="4">
        <v-select
          label="Roles"
          multiple
          :items="roles"
          item-title="name"
          item-value="name"
          v-model="selectedRoles"
          @change="saveRoles"
        />
      </v-col>

      <v-col cols="12" md="4">
        <v-select
          label="Direct Permissions"
          multiple
          :items="permissions"
          item-title="name"
          item-value="name"
          v-model="selectedPermissions"
          @change="savePermissions"
        />
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRbacRepository } from '@/stores/RbacRepository'

const repo = useRbacRepository()

const users = ref([])
const roles = ref([])
const permissions = ref([])

const selectedUserId = ref(null)
const selectedRoles = ref([])
const selectedPermissions = ref([])

onMounted(async () => {
  await Promise.all([loadUsers(), loadRoles(), loadPermissions()])
})

async function loadUsers() {
  users.value = await repo.fetchUsers()
}
async function loadRoles() {
  roles.value = await repo.fetchRoles()
}
async function loadPermissions() {
  permissions.value = await repo.fetchPermissions()
}

function onSelectUser() {
  const u = users.value.find(u => u.id === selectedUserId.value)
  selectedRoles.value = u?.roles || []
  selectedPermissions.value = u?.permissions || []
}

async function saveRoles() {
  if (!selectedUserId.value) return
  await repo.syncUserRoles(selectedUserId.value, selectedRoles.value)
}
async function savePermissions() {
  if (!selectedUserId.value) return
  await repo.syncUserPermissions(selectedUserId.value, selectedPermissions.value)
}
</script>
