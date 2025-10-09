<template>
    <v-container class="py-6" fluid>
      <v-row class="items-center mb-4">
        <v-col cols="12" md="6">
          <h2 class="text-h5 font-weight-medium">Excel-like Editor</h2>
          <div class="text-body-2 text-medium-emphasis">
            Inline edit rows, add new entries, and delete. The “Category” column is a dropdown (A/B/C).
          </div>
        </v-col>
        <v-col cols="12" md="6" class="text-right">
          <v-btn color="primary" @click="addBlankRow" :loading="ui.adding">
            Add Row
          </v-btn>
        </v-col>
      </v-row>

      <v-card>
        <v-data-table
          :headers="headers"
          :items="rows"
          :loading="ui.loading"
          item-key="idOrTmp"
          class="elevation-0"
          :items-per-page="10"
        >
          <!-- Category (dropdown) -->
          <template #item.category="{ item }">
            <div v-if="item.editing">
              <v-select
                v-model="item.draft.category"
                :items="categoryOptions"
                density="compact"
                hide-details
                variant="outlined"
              />
            </div>
            <div v-else>{{ item.category }}</div>
          </template>

          <!-- Name -->
          <template #item.name="{ item }">
            <div v-if="item.editing">
              <v-text-field
                v-model.trim="item.draft.name"
                density="compact"
                hide-details
                variant="outlined"
                placeholder="Name"
              />
            </div>
            <div v-else>{{ item.name }}</div>
          </template>

          <!-- Amount -->
          <template #item.amount="{ item }">
            <div v-if="item.editing">
              <v-text-field
                v-model.number="item.draft.amount"
                type="number"
                density="compact"
                hide-details
                variant="outlined"
                placeholder="0"
              />
            </div>
            <div v-else>{{ formatCurrency(item.amount) }}</div>
          </template>

          <!-- Date -->
          <template #item.date="{ item }">
            <div v-if="item.editing">
              <v-text-field
                v-model="item.draft.date"
                type="date"
                density="compact"
                hide-details
                variant="outlined"
              />
            </div>
            <div v-else>{{ item.date }}</div>
          </template>

          <!-- Notes -->
          <template #item.notes="{ item }">
            <div v-if="item.editing">
              <v-text-field
                v-model.trim="item.draft.notes"
                density="compact"
                hide-details
                variant="outlined"
                placeholder="Optional"
              />
            </div>
            <div v-else class="text-truncate" style="max-width: 280px;">
              {{ item.notes }}
            </div>
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="d-flex ga-2">
              <v-btn
                v-if="!item.editing"
                size="small"
                variant="text"
                @click="startEdit(item)"
              >
                Edit
              </v-btn>
              <v-btn
                v-else
                size="small"
                color="primary"
                :loading="savingIds.has(item.idOrTmp)"
                @click="saveRow(item)"
              >
                Save
              </v-btn>
              <v-btn
                v-if="item.editing"
                size="small"
                variant="text"
                @click="cancelEdit(item)"
              >
                Cancel
              </v-btn>

              <v-btn
                size="small"
                color="error"
                variant="text"
                :loading="deletingIds.has(item.idOrTmp)"
                @click="deleteRow(item)"
              >
                Delete
              </v-btn>
            </div>
          </template>

          <template #no-data>
            <div class="text-medium-emphasis py-8">No data yet. Click “Add Row”.</div>
          </template>
        </v-data-table>
      </v-card>
    </v-container>
  </template>

  <script setup>
  import { ref, reactive, computed, onMounted } from 'vue'

  // ---- CONFIG ----
  // Adjust these to match your Laravel routes (Repository already exists on backend).
  const API = {
    index:   '/api/items',        // GET
    create:  '/api/items',        // POST
    update:  (id) => `/api/items/${id}`, // PUT/PATCH
    destroy: (id) => `/api/items/${id}`  // DELETE
  }

  // Table columns
  const headers = [
    { title: 'Category', key: 'category', width: 140 },
    { title: 'Name',     key: 'name',     minWidth: 180 },
    { title: 'Amount',   key: 'amount',   width: 120 },
    { title: 'Date',     key: 'date',     width: 160 },
    { title: 'Notes',    key: 'notes',    minWidth: 260 },
    { title: 'Actions',  key: 'actions',  width: 220, sortable: false }
  ]

  // Dropdown choices
  const categoryOptions = ['A', 'B', 'C']

  // State
  const ui = reactive({
    loading: false,
    adding: false,
  })

  const rows = ref([]) // each row shape explained below
  const tmpIdCounter = ref(0)
  const savingIds = ref(new Set())
  const deletingIds = ref(new Set())

  // Helpers
  function newTempId () {
    tmpIdCounter.value += 1
    return `tmp-${tmpIdCounter.value}`
  }

  function toRowModel(apiItem) {
    // Map backend fields -> frontend row model
    // Expecting { id, category, name, amount, date, notes }
    const idOrTmp = apiItem.id ?? newTempId()
    return {
      id: apiItem.id ?? null,
      idOrTmp,
      category: apiItem.category ?? 'A',
      name: apiItem.name ?? '',
      amount: Number(apiItem.amount ?? 0),
      date: apiItem.date ?? new Date().toISOString().slice(0,10),
      notes: apiItem.notes ?? '',
      editing: false,
      // draft copy for editing
      draft: {}
    }
  }

  function startEdit(item) {
    item.editing = true
    item.draft = {
      category: item.category,
      name: item.name,
      amount: item.amount,
      date: item.date,
      notes: item.notes
    }
  }

  function cancelEdit(item) {
    if (!item.id && String(item.idOrTmp).startsWith('tmp-')) {
      // brand new unsaved row -> remove if cancel
      rows.value = rows.value.filter(r => r.idOrTmp !== item.idOrTmp)
    } else {
      item.editing = false
      item.draft = {}
    }
  }

  function validateDraft(d) {
    const errors = []
    if (!d.name || !d.name.trim()) errors.push('Name is required')
    if (!categoryOptions.includes(d.category)) errors.push('Category must be A, B, or C')
    if (isNaN(Number(d.amount))) errors.push('Amount must be a number')
    if (!d.date) errors.push('Date is required')
    return errors
  }

  async function saveRow(item) {
    const d = item.draft
    const errors = validateDraft(d)
    if (errors.length) {
      alert(errors.join('\n'))
      return
    }
    savingIds.value.add(item.idOrTmp)
    try {
      if (item.id) {
        // Update
        const res = await (/** @type any */(getAxios())).put(API.update(item.id), d)
        const saved = toRowModel(res.data.data ?? res.data) // support both {data:{}} or raw
        // keep same idOrTmp to avoid table re-key glitch
        Object.assign(item, { ...saved, idOrTmp: item.idOrTmp, editing: false, draft: {} })
      } else {
        // Create
        const res = await (/** @type any */(getAxios())).post(API.create, d)
        const saved = toRowModel(res.data.data ?? res.data)
        Object.assign(item, { ...saved, idOrTmp: saved.id ?? item.idOrTmp, editing: false, draft: {} })
      }
    } catch (e) {
      console.error(e)
      alert('Save failed. Check console or backend logs.')
    } finally {
      savingIds.value.delete(item.idOrTmp)
    }
  }

  async function deleteRow(item) {
    if (!confirm('Delete this row?')) return
    deletingIds.value.add(item.idOrTmp)
    try {
      if (item.id) {
        await (/** @type any */(getAxios())).delete(API.destroy(item.id))
      }
      rows.value = rows.value.filter(r => r.idOrTmp !== item.idOrTmp)
    } catch (e) {
      console.error(e)
      alert('Delete failed.')
    } finally {
      deletingIds.value.delete(item.idOrTmp)
    }
  }

  function addBlankRow() {
    ui.adding = true
    const blank = toRowModel({
      // no id yet (new row)
      category: 'A',
      name: '',
      amount: 0,
      date: new Date().toISOString().slice(0,10),
      notes: ''
    })
    blank.editing = true
    rows.value = [blank, ...rows.value]
    ui.adding = false
  }

  function formatCurrency(n) {
    const num = Number(n ?? 0)
    return isNaN(num) ? '-' : new Intl.NumberFormat(undefined, { style: 'currency', currency: 'EUR' }).format(num)
  }

  function getAxios() {
    // You said you already set: app.config.globalProperties.$axios = axios
    // Inside SFCs, we can access it via `getCurrentInstance()` or simply rely on global `window.axios`.
    // To keep it simple and robust, try window.axios first, then this.$axios if injected.
    if (typeof window !== 'undefined' && window.axios) return window.axios
    // Fallback: attempt globalProperties (works if you expose it app-wide)
    // @ts-ignore
    if (getCurrentInstance?.()?.appContext?.config?.globalProperties?.$axios) {
      // @ts-ignore
      return getCurrentInstance().appContext.config.globalProperties.$axios
    }
    // As last resort, throw:
    throw new Error('Axios instance not found. Make sure you set app.config.globalProperties.$axios = axios or window.axios = axios.')
  }

  async function fetchAll() {
    ui.loading = true
    try {
      const res = await (/** @type any */(getAxios())).get(API.index)
      const list = Array.isArray(res.data?.data) ? res.data.data : (Array.isArray(res.data) ? res.data : [])
      rows.value = list.map(toRowModel)
    } catch (e) {
      console.error(e)
      rows.value = []
    } finally {
      ui.loading = false
    }
  }

  onMounted(fetchAll)
  </script>

  <style scoped>
  .text-truncate {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
  </style>
