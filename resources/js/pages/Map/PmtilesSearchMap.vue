<!-- PmtilesSearchMap.vue -->
<template>
  <div class="search-container">
    <!-- Search Input -->
    <div class="search-input-wrapper">
      <input
        v-model="q"
        type="text"
        :placeholder="placeholderText"
        @input="onType"
        @keyup.enter="pickFirst"
        class="search-input"
      />
      <button v-if="q" @click="clear" class="clear-btn">×</button>
    </div>

    <!-- Search Results -->
    <div v-if="results.length" class="search-results">
      <div
        v-for="(r, i) in results"
        :key="r.__k || i"
        class="result-item"
        @click="select(r)"
      >
        <div class="result-name">
          {{ r.displayName }}
          <span class="badge" :class="'lvl-' + (r.rank || 'x')">
            {{ r.type }}
          </span>
        </div>
        <div class="result-path" v-if="r.admin_path?.length">
          {{ r.admin_path.join(' ▸ ') }}
        </div>
        <div class="result-coords">
          {{ r.lon.toFixed(4) }}, {{ r.lat.toFixed(4) }}
        </div>
      </div>
    </div>

    <div v-if="q && !results.length" class="no-results">
      No results found
    </div>
    <!-- <div class="coord-helper">
      <small>
        e.g.
        <code>69.2075,34.5553</code> / <code>69.2075 34.5553</code> /
        <code>69.2075،34.5553</code> or just <code>35.03</code>
      </small>
    </div> -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  // where the JSON lives under /public (or Laravel public)
  gazetteerUrl: { type: String, default: '/data/afg_gazetteer.json' }
})
const emit = defineEmits(['flyTo'])

const q = ref('')
const all = ref([]) // normalized gazetteer entries
const results = ref([])
let timer = null

const placeholderText = computed(
  () => 'Search name or coordinates (lng,lat)…'
)

function norm(s) {
  if (!s) return ''
  return s
    .toString()
    .normalize('NFKC')
    // remove tashkeel / diacritics
    .replace(/[\u064B-\u065F\u0670\u06D6-\u06ED]/g, '')
    // normalize Arabic forms
    .replace(/ي/g, 'ی')
    .replace(/ك/g, 'ک')
    .toLowerCase()
    .trim()
}

function rankByType(t) {
  if (t === 'province') return 4
  if (t === 'district') return 6
  if (t === 'nahya' || t === 'hawza') return 8
  if (t === 'village') return 10
  if (t === 'road' || t === 'street') return 11
  return 12
}

// 🔍 how deep we want to zoom when flying
function deepZoom(entry) {
  const type = entry.type || 'place'

  let base =
    type === 'province'
      ? 8
      : type === 'district'
      ? 11
      : type === 'nahya' || type === 'hawza'
      ? 13
      : type === 'village'
      ? 15
      : type === 'road' || type === 'street'
      ? 16
      : 14

  // if JSON has a zoom value, go +2 deeper than that
  if (entry.zoom_hint != null) {
    base = Math.max(base, entry.zoom_hint + 2)
  }

  // clamp to map limits
  return Math.max(4, Math.min(18, base))
}

async function loadGazetteer() {
  try {
    const res = await fetch(props.gazetteerUrl, { cache: 'no-store' })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const j = await res.json()

    const items = Array.isArray(j) ? j : Array.isArray(j.items) ? j.items : []

    all.value = items.map((it, idx) => {
      const name_local = it.name_local ?? it.name ?? ''
      const name_en = it.name_en ?? ''
      const aliases = Array.isArray(it.aliases) ? it.aliases : []
      const admin_path = Array.isArray(it.admin_path) ? it.admin_path : []
      const type = it.type ?? 'place'
      const rank =
        typeof it.zoom === 'number' ? it.zoom : rankByType(type)

      const lon = Number(it.lon)
      const lat = Number(it.lat)

      const coordTokens = []
      if (isFinite(lon) && isFinite(lat)) {
        // full precision strings
        coordTokens.push(String(lon), String(lat))
        // rounded variants help prefix search like "71.35" / "35.03"
        coordTokens.push(lon.toFixed(4), lat.toFixed(4))
        coordTokens.push(lon.toFixed(3), lat.toFixed(3))
        coordTokens.push(lon.toFixed(2), lat.toFixed(2))
      }

      return {
        __k: it.id ?? `${type}:${idx}`,
        id: it.id ?? null,
        type,
        rank,
        lon,
        lat,
        zoom_hint:
          typeof it.zoom === 'number' ? Number(it.zoom) : null,
        name_local,
        name_en,
        aliases,
        admin_path,
        // 🔥 precomputed search bucket (names + admin path + coordinates)
        _bag: [
          name_local,
          name_en,
          ...aliases,
          ...admin_path,
          ...coordTokens
        ]
          .map(norm)
          .filter(Boolean)
      }
    })

    console.log('✅ Gazetteer loaded:', all.value.length, 'features')
  } catch (e) {
    console.error('Failed to load gazetteer:', e)
    all.value = []
  }
}

function score(queryNorm, entry) {
  if (!queryNorm) return -1
  let best = -1
  for (const token of entry._bag) {
    if (token === queryNorm) best = Math.max(best, 120) // exact match (name or coord)
    else if (token.startsWith(queryNorm)) best = Math.max(best, 90) // prefix (e.g. 35.03…)
    else if (token.includes(queryNorm)) best = Math.max(best, 60) // contains (e.g. 45 inside 71.4587)
  }
  return best
}

function onType() {
  if (timer) clearTimeout(timer)
  timer = setTimeout(run, 120)
}

function run() {
  const s = q.value.trim()
  if (!s) {
    results.value = []
    return
  }

  // 🧭 direct coordinate fly (two numbers)
  // accepts "71.35,35.03", "71.35 35.03", "71.35،35.03"
  const coordPattern =
    /^(-?\d+(?:\.\d+)?)\s*[,، ]\s*(-?\d+(?:\.\d+)?)$/
  const m = s.match(coordPattern)
  if (m) {
    let a = parseFloat(m[1])
    let b = parseFloat(m[2])

    if (isFinite(a) && isFinite(b)) {
      // small helper: Afghanistan-ish bbox
      const inLon = (v) => v >= 60 && v <= 75
      const inLat = (v) => v >= 29 && v <= 39

      let lon = a
      let lat = b

      // if looks reversed, swap → works for "lat,lon" too
      if (inLat(a) && inLon(b)) {
        lon = b
        lat = a
      }

      const fake = {
        __k: 'coord',
        id: null,
        type: 'coord',
        rank: 12,
        lon,
        lat,
        zoom_hint: 16,
        name_local: '',
        name_en: '',
        admin_path: []
      }

      results.value = [
        {
          ...fake,
          displayName: `🧭 ${lon.toFixed(4)}, ${lat.toFixed(4)}`
        }
      ]
      return
    }
  }

  const nq = norm(s)
  const cand = []
  for (const e of all.value) {
    const sc = score(nq, e)
    if (sc >= 0) {
      cand.push({
        ...e,
        score: sc,
        displayName: e.name_local || e.name_en || '(unnamed)'
      })
    }
  }

  cand.sort(
    (a, b) =>
      b.score - a.score ||
      a.rank - b.rank ||
      a.displayName.localeCompare(b.displayName, 'fa')
  )

  results.value = cand.slice(0, 50)
}

function pickFirst() {
  if (results.value.length) select(results.value[0])
}

function select(entry) {
  const zoom = deepZoom(entry)

  // 🔔 emit full payload so map can fly + highlight
  emit('flyTo', {
    lng: entry.lon,
    lat: entry.lat,
    zoom,
    feature: {
      id: entry.id,
      type: entry.type,
      name_local: entry.name_local,
      name_en: entry.name_en,
      admin_path: entry.admin_path
    }
  })

  results.value = []
}

function clear() {
  q.value = ''
  results.value = []
}

onMounted(loadGazetteer)
</script>

<style scoped>
.search-container {
  position: relative;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  padding: 10px;
  max-width: 520px;
  direction: rtl;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  width: 100%;
  padding: 10px 36px 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
}

.search-input:focus {
  border-color: #007cba;
}

.clear-btn {
  position: absolute;
  right: 8px;
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #666;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.search-results {
  max-height: 360px;
  overflow-y: auto;
  margin-top: 8px;
  border: 1px solid #eee;
  border-radius: 8px;
}

.result-item {
  padding: 10px 12px;
  border-bottom: 1px solid #f4f4f5;
  cursor: pointer;
}

.result-item:hover {
  background: #f8fafc;
}

.result-item:last-child {
  border-bottom: none;
}

.result-name {
  font-weight: 700;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 999px;
  background: #eef2ff;
  color: #3949ab;
}

.badge.lvl-4 {
  background: #e8f5e9;
  color: #1b5e20;
}

.badge.lvl-6 {
  background: #fffde7;
  color: #9e7700;
}

.badge.lvl-8 {
  background: #e3f2fd;
  color: #0d47a1;
}

.result-path {
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
}

.result-coords {
  font-size: 12px;
  color: #6b7280;
  font-family: ui-monospace, monospace;
  margin-top: 2px;
}

.no-results {
  padding: 10px;
  text-align: center;
  color: #666;
  font-style: italic;
}

.coord-helper {
  margin-top: 8px;
  text-align: center;
  color: #9ca3af;
}
</style>
