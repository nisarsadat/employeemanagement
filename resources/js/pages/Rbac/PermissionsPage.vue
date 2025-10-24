<template>
  <div class="pa-4">
    <v-toolbar flat class="mb-4">
      <v-toolbar-title>Permissions</v-toolbar-title>
      <v-spacer />
      <v-btn color="primary" @click="openCreate">New Permission</v-btn>
    </v-toolbar>

    <v-data-table :items="permissions" :headers="headers" item-key="id">
      <template #item.actions="{ item }">
        <v-btn icon="mdi-pencil" size="small" @click="openEdit(item)" />
        <v-btn icon="mdi-delete" size="small" color="error" @click="remove(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="dialog" max-width="420">
      <v-card>
        <v-card-title>{{ edited?.id ? 'Edit Permission' : 'Create Permission' }}</v-card-title>
        <v-card-text>
          <v-text-field label="Name" v-model="editedName" />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn text @click="dialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="save">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRbacRepository } from '@/stores/RbacRepository'

const repo = useRbacRepository()
const permissions = ref([])
const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Name', key: 'name' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const dialog = ref(false)
const edited = ref(null)
const editedName = ref('')

onMounted(load)

async function load() {
  permissions.value = await repo.fetchPermissions()
}

function openCreate() {
  edited.value = null
  editedName.value = ''
  dialog.value = true
}

function openEdit(item) {
  edited.value = item
  editedName.value = item.name
  dialog.value = true
}

async function save() {
  if (edited.value?.id) {
    await repo.updatePermission(edited.value.id, editedName.value)
  } else {
    await repo.createPermission(editedName.value)
  }
  dialog.value = false
  await load()
}

async function remove(item) {
  if (confirm(`Delete permission "${item.name}"?`)) {
    await repo.deletePermission(item.id)
    await load()
  }
}
</script>
