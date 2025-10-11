<!-- CaptiveOverviewPanel.vue -->
<script setup>
import { computed, ref, watch, onBeforeUnmount, nextTick } from "vue";
import CaptiveOverviewChart from "@/components/CaptiveOverviewChart.vue";

/** Props: pass the same array you use for the chart (row models or draft) */
const props = defineProps({
    items: { type: Array, default: () => [] }, // rows.map(r => r.draft ?? r)
    groupBy: { type: String, default: "suspect_type" },
});

/* =============== CONFIG – ADAPT THESE TWO IF NEEDED =============== */
// 1) Where your API lives (used for relative paths like "/uploads/x.jpg")
const API_BASE = import.meta.env.VITE_API_URL || "";

// 2) How to get an auth token (if you use bearer tokens). If you use cookies,
//    leave TOKEN null and rely on credentials: 'include'.
const TOKEN = localStorage.getItem("auth_token") || null; // <-- change key if needed
/* ================================================================= */

/* ───────────────────────── Helpers ───────────────────────── */

const toAbsoluteUrl = (p) => {
    if (!p) return "";
    if (p.startsWith("http")) return p;
    if (p.startsWith("data:image/")) return p;
    return `${API_BASE}${p}`;
};

// image cache to avoid refetching; maps original URL -> blobObjectURL
const blobUrlCache = new Map();
// track created blob URLs so we can revoke them on unmount/refresh
const createdBlobUrls = new Set();

const revokeAllBlobUrls = () => {
    for (const u of createdBlobUrls) URL.revokeObjectURL(u);
    createdBlobUrls.clear();
};

async function resolveSecureImageSrc(raw) {
    // 0) falsy
    if (!raw) return "";

    // 1) base64 already OK
    if (raw.startsWith("data:image/")) return raw;

    // 2) absolute/relative URL
    const url = toAbsoluteUrl(raw);

    // If it looks public, we can try returning it directly, but many backends still need cookies.
    // Safer approach: try a HEAD to check CORS/permissions (optional). We'll go straight to fetch blob.

    // Cache?
    if (blobUrlCache.has(url)) return blobUrlCache.get(url);

    try {
        const res = await fetch(url, {
            method: "GET",
            // If your backend uses cookies (Sanctum), keep this line:
            credentials: "include",
            // If your backend uses bearer tokens, keep Authorization:
            headers: TOKEN ? { Authorization: `Bearer ${TOKEN}` } : {},
        });

        if (!res.ok) {
            // Fall back to returning the URL directly (may still work if public)
            return url;
        }

        const blob = await res.blob();
        const objectUrl = URL.createObjectURL(blob);
        createdBlobUrls.add(objectUrl);
        blobUrlCache.set(url, objectUrl);
        return objectUrl;
    } catch {
        // Network issue – fall back to direct URL so at least <img> tries to load it
        return url;
    }
}

/** Build fast lookup maps using the best available unique key */
const byId = computed(() => new Map(props.items.map((r) => [r?.id, r])));
const byTazkira = computed(
    () => new Map(props.items.map((r) => [r?.tazkira, r]))
);

/** Fallback composite key if neither id nor tazkira is reliable */
const makeCompositeKey = (r) =>
    [
        r?.name ?? "",
        r?.father_name ?? "",
        r?.captive_date ?? "",
        r?.suspect_type ?? "",
    ].join("|");

const byComposite = computed(
    () => new Map(props.items.map((r) => [makeCompositeKey(r), r]))
);

/** Try to hydrate a lightweight item into the full row */
const hydrateOne = (it) => {
    if (!it || typeof it !== "object") return it;

    if (it.id != null && byId.value.has(it.id)) return byId.value.get(it.id);
    if (it.tazkira && byTazkira.value.has(it.tazkira))
        return byTazkira.value.get(it.tazkira);

    const ck = makeCompositeKey(it);
    if (byComposite.value.has(ck)) return byComposite.value.get(ck);

    return it;
};

const hydrateMany = (arr = []) => arr.map(hydrateOne);

/* ───────────────────────── State ───────────────────────── */

const hoverInfo = ref({ key: "", total: 0, items: [] });
const selectedInfo = ref({ key: "", total: 0, items: [] });

/* ───────────────────────── Image enrichment ─────────────────────────
   For each selected row, create a field `_imgSrc` that is a safe, displayable src.
---------------------------------------------------------------------*/

async function enrichImagesForSelection() {
    const rows = selectedInfo.value.items || [];
    const tasks = rows.map(async (row) => {
        const raw = row?.picture || "";
        const src = await resolveSecureImageSrc(raw);
        row._imgSrc = src; // mutate is fine here since it's our local display layer
    });
    await Promise.allSettled(tasks);
    // force a tiny nextTick to let v-img pick up the updated src
    await nextTick();
}

// Re-enrich when selection changes (new rows or different items)
watch(
    () => selectedInfo.value.items,
    async () => {
        // revoke old object URLs (prevents memory leaks) before loading new ones
        revokeAllBlobUrls();
        await enrichImagesForSelection();
    },
    { immediate: false }
);

// Optional: also enrich for hover table if you want images there later.
// (Your hover table currently doesn't show images, so we skip.)

onBeforeUnmount(() => {
    revokeAllBlobUrls();
});

/* ───────────────────────── Event handlers from chart ───────────────────────── */

function onHoverChart(payload) {
    const hydrated = hydrateMany(payload?.items);
    hoverInfo.value = {
        key: payload?.key ?? "",
        total: hydrated.length,
        items: hydrated,
    };
}

function onLeaveChart() {
    hoverInfo.value = { key: "", total: 0, items: [] };
}

async function onSelectChart(payload) {
    const hydrated = hydrateMany(payload?.items);
    selectedInfo.value = {
        key: payload?.key ?? "",
        total: hydrated.length,
        items: hydrated,
    };
    // Enrich images for the new selection
    await enrichImagesForSelection();
}
</script>

<template>
    <v-row dir="rtl" class="elevation-1 bg-white" rounded>
        <!-- 📊 Chart on the left -->
        <v-col cols="12" md="6">
            <CaptiveOverviewChart
                :items="props.items"
                :group-by="props.groupBy"
                title="عمومي جاج (د اوسني مخ معلومات)"
                :tooltip-fields="[
                    'name',
                    'father_name',
                    'tazkira',
                    'suspect_type',
                ]"
                :field-labels="{
                    name: 'نوم',
                    father_name: 'د پلار نوم',
                    tazkira: 'تذکره',
                    suspect_type: 'د شکمن ډول',
                }"
                @hover="onHoverChart"
                @leave="onLeaveChart"
                @select="onSelectChart"
                class="br-none bg-transparent shadow-none"
                style="background: transparent; box-shadow: none; border: none"
            />
        </v-col>

        <!-- 📋 Hover info on the right (no border, no background) -->
        <v-col
            cols="12"
            md="6"
            class="pa-0"
            style="background: transparent; border: none"
        >
            <div>
                <h3 class="text-lg font-bold mb-2">د هوور معلومات</h3>
                <div v-if="hoverInfo.total">
                    <div class="mb-2">
                        <strong>{{ hoverInfo.key }}</strong> —
                        {{ hoverInfo.total }} توکي
                    </div>
                    <div style="max-height: 400px; overflow: auto">
                        <v-table
                            density="compact"
                            class="elevation-0"
                            style="
                                background: transparent;
                                box-shadow: none;
                                border: none;
                            "
                        >
                            <thead>
                                <tr>
                                    <th>نوم</th>
                                    <th>د پلار نوم</th>
                                    <th>تذکره</th>
                                    <th>د شکمن ډول</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(it, i) in hoverInfo.items" :key="i">
                                    <td>{{ it?.name || "—" }}</td>
                                    <td>{{ it?.father_name || "—" }}</td>
                                    <td>{{ it?.tazkira || "—" }}</td>
                                    <td>{{ it?.suspect_type || "—" }}</td>
                                </tr>
                            </tbody>
                        </v-table>
                    </div>
                </div>
                <div v-else>په چارټ کې پر یوه ټوټه هوور وکړئ.</div>
            </div>
        </v-col>
    </v-row>

    <!-- د کلیک/ټاکنې ثابت لېست -->
    <v-row class="mt-4"  dir="rtl" v-if="selectedInfo.total">
        <v-col cols="12" md="12">
            <v-card>
                <v-card-title>
                    ټاکل شوی: <strong>{{ selectedInfo.key }}</strong> —
                    {{ selectedInfo.total }} توکي
                </v-card-title>
                <v-card-text>
                    <div style="overflow: auto">
                        <v-table>
                            <thead>
                                <tr>
                                    <th>انځور</th>
                                    <th>نوم</th>
                                    <th>د پلار نوم</th>
                                    <th>تذکره</th>
                                    <th>عمر</th>
                                    <th>ملکي حالت</th>
                                    <th>ژبه</th>
                                    <th>علمي کچه</th>
                                    <th>اصلي ولایت/سیمه/کلی</th>
                                    <th>اوسنی ولایت/سیمه/کلی</th>
                                    <th>نیولو ځای</th>
                                    <th>مراجعه</th>
                                    <th>نیولو نېټه</th>
                                    <th>نیولو ساعت</th>
                                    <th>د شکمن ډول</th>
                                    <th>تلیفون</th>
                                    <th>د اسیر حالت</th>
                                    <th>د شکمن وسایل</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(it, i) in selectedInfo.items"
                                    :key="i"
                                >
                                    <td style="min-width: 90px">
                                        <v-avatar size="72">
                                            <v-img
                                                :src="
                                                    it?._imgSrc ||
                                                    '/no-photo.png'
                                                "
                                                width="72"
                                                height="72"
                                                cover
                                                class="rounded"
                                                :eager="true"
                                            />
                                        </v-avatar>
                                    </td>
                                    <td>{{ it?.name || "—" }}</td>
                                    <td>{{ it?.father_name || "—" }}</td>
                                    <td>{{ it?.tazkira || "—" }}</td>
                                    <td>{{ it?.age || "—" }}</td>
                                    <td>{{ it?.halat_madani || "—" }}</td>
                                    <td>{{ it?.language || "—" }}</td>
                                    <td>{{ it?.scientific_level || "—" }}</td>
                                    <td>
                                        {{
                                            [
                                                it?.main_province,
                                                it?.main_district,
                                                it?.main_village,
                                            ]
                                                .filter(Boolean)
                                                .join(" / ") || "—"
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            [
                                                it?.now_province,
                                                it?.now_district,
                                                it?.now_village,
                                            ]
                                                .filter(Boolean)
                                                .join(" / ") || "—"
                                        }}
                                    </td>
                                    <td>{{ it?.captive_place || "—" }}</td>
                                    <td>{{ it?.captive_marja || "—" }}</td>
                                    <td>{{ it?.captive_date || "—" }}</td>
                                    <td>{{ it?.captive_hour || "—" }}</td>
                                    <td>{{ it?.suspect_type || "—" }}</td>
                                    <td>{{ it?.phone_number || "—" }}</td>
                                    <td>{{ it?.captive_halat || "—" }}</td>
                                    <td>{{ it?.suspect_tools || "—" }}</td>
                                </tr>
                            </tbody>
                        </v-table>
                    </div>

                    <!-- Debug – enable for a second to confirm picture field exists
          <pre class="mt-4">{{ JSON.stringify(selectedInfo.items?.[0], null, 2) }}</pre>
          -->
                </v-card-text>
            </v-card>
        </v-col>
    </v-row>
</template>

<style scoped>
tbody tr > td {
    vertical-align: middle;
}
</style>
