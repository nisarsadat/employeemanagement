<template>
    <div class="max-w-full overflow-auto">
      <div class="flex items-center gap-2 mb-3">
        <button class="px-3 py-1 rounded bg-gray-800 text-white" @click="addRow()">+ Add Row</button>
        <button class="px-3 py-1 rounded bg-gray-200" @click="clearRows()">Clear</button>
      </div>

      <table class="min-w-[700px] w-full border-collapse">
        <thead>
          <tr>
            <th
              v-for="(col, c) in columns"
              :key="col.key"
              class="border px-3 py-2 text-left bg-gray-50"
            >
              {{ col.label }}
            </th>
            <th class="border px-3 py-2 bg-gray-50 w-14">⋯</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(row, r) in rows" :key="row._id">
            <td
              v-for="(col, c) in columns"
              :key="col.key"
              class="border px-0 py-0"
            >
              <input
                class="w-full px-3 py-2 outline-none"
                :type="col.type || 'text'"
                v-model="rows[r][col.key]"
                :ref="setCellRef(r, c)"
                @keydown="handleKey(r, c, $event)"
              />
            </td>
            <td class="border px-2 py-1 text-center">
              <button
                class="px-2 py-1 rounded bg-red-500 text-white"
                @click="removeRow(r)"
                title="Delete row"
              >
                ×
              </button>
            </td>
          </tr>

          <!-- Optional last empty “append” row UX (shows faint inputs) -->
          <tr>
            <td v-for="(col, c) in columns" :key="'ghost-'+col.key" class="border px-0 py-0">
              <input
                class="w-full px-3 py-2 outline-none text-gray-400"
                :placeholder="c===0 ? 'Type here and press Enter…' : ''"
                @keydown.enter.prevent="addRowAndFocus(rows.length, 0)"
              />
            </td>
            <td class="border"></td>
          </tr>
        </tbody>
      </table>

      <!-- Debug / example output -->
      <pre class="mt-4 bg-gray-50 p-3 rounded text-sm overflow-auto">{{ rows }}</pre>
    </div>
  </template>

  <script setup>
  import { reactive, ref, nextTick } from 'vue'

  /**
   * PROPS-LIKE CONFIG (you can turn these into real props if you want)
   * Define columns and initial rows for the grid.
   */
  const columns = [
    { key: 'item',    label: 'Item',        type: 'text' },
    { key: 'qty',     label: 'Quantity',    type: 'number' },
    { key: 'price',   label: 'Unit Price',  type: 'number' },
    { key: 'notes',   label: 'Notes',       type: 'text' },
  ]

  // initial data (could come from API)
  const rows = reactive([
    mkRow(),
  ])

  /** Utilities */
  function mkRow() {
    return {
      _id: crypto.randomUUID(), // stable key for v-for
      item: '',
      qty:  '',
      price:'',
      notes:''
    }
  }

  /** Cell refs matrix to control focus like Excel */
  const cellRefs = ref([]) // 2D array: [rowIndex][colIndex] => element

  function setCellRef(r, c) {
    return (el) => {
      if (!cellRefs.value[r]) cellRefs.value[r] = []
      cellRefs.value[r][c] = el
    }
  }

  function focusCell(r, c) {
    nextTick(() => {
      const el = cellRefs.value?.[r]?.[c]
      if (el) {
        el.focus()
        el.select?.()
      }
    })
  }

  /** Row ops */
  function addRow() {
    rows.push(mkRow())
  }

  function addRowAndFocus(r, c) {
    addRow()
    focusCell(r, c)
  }

  function removeRow(r) {
    rows.splice(r, 1)
    // keep focus sane
    const newR = Math.max(0, r - 1)
    focusCell(newR, 0)
  }

  function clearRows() {
    rows.splice(0, rows.length, mkRow())
    focusCell(0, 0)
  }

  /**
   * Keyboard navigation:
   * Enter: move down (Shift+Enter: up). If at last row, auto-add row and keep same column.
   * Tab: move right (Shift+Tab: left). If at row-end, jump next/prev row.
   * Arrow keys: optional basic support for vertical moves.
   */
  function handleKey(r, c, e) {
    const lastRow = rows.length - 1
    const lastCol = columns.length - 1

    // Helper to clamp
    const clamp = (n, min, max) => Math.max(min, Math.min(max, n))

    if (e.key === 'Enter') {
      e.preventDefault()
      if (e.shiftKey) {
        // up
        const targetR = clamp(r - 1, 0, lastRow)
        focusCell(targetR, c)
      } else {
        // down (Excel-like). If at bottom, add a row then focus same column.
        if (r === lastRow) {
          addRowAndFocus(r + 1, c)
        } else {
          focusCell(r + 1, c)
        }
      }
    } else if (e.key === 'Tab') {
      e.preventDefault()
      if (e.shiftKey) {
        // left
        const prevC = c - 1
        if (prevC >= 0) {
          focusCell(r, prevC)
        } else {
          // jump to previous row last col
          const prevR = r - 1
          if (prevR >= 0) focusCell(prevR, lastCol)
        }
      } else {
        // right
        const nextC = c + 1
        if (nextC <= lastCol) {
          focusCell(r, nextC)
        } else {
          // next row, first col; if at last row, add a row
          if (r === lastRow) {
            addRowAndFocus(r + 1, 0)
          } else {
            focusCell(r + 1, 0)
          }
        }
      }
    } else if (e.key === 'ArrowDown') {
      e.preventDefault()
      if (r === lastRow) {
        addRowAndFocus(r + 1, c)
      } else {
        focusCell(r + 1, c)
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      focusCell(Math.max(0, r - 1), c)
    } else if (e.key === 'ArrowRight') {
      // allow caret to move inside input; only jump if caret is at end
      const el = e.target
      if (el.selectionStart === el.value.length && el.selectionEnd === el.value.length) {
        e.preventDefault()
        const nextC = c + 1
        if (nextC <= lastCol) focusCell(r, nextC)
      }
    } else if (e.key === 'ArrowLeft') {
      const el = e.target
      if (el.selectionStart === 0 && el.selectionEnd === 0) {
        e.preventDefault()
        const prevC = c - 1
        if (prevC >= 0) focusCell(r, prevC)
      }
    }
  }
  </script>

  <style scoped>
  table { border: 1px solid #e5e7eb; }
  th, td { border: 1px solid #e5e7eb; }
  input { border: none; }
  input:focus { outline: 2px solid #3b82f6; outline-offset: -1px; }
  </style>
