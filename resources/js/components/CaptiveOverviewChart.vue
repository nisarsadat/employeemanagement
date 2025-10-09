<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from "vue";
import * as echarts from "echarts/core";
import { PieChart } from "echarts/charts";
import { TitleComponent, TooltipComponent, LegendComponent } from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";

echarts.use([PieChart, TitleComponent, TooltipComponent, LegendComponent, CanvasRenderer]);

/**
 * Props:
 *  - items: د جدول د اوسني مخ قطارونه (row models یا draft شیان).
 *  - groupBy: له کوم فیلډ سره شمېرنه وشي (ډيفالټ: 'suspect_type').
 *  - title: د چارټ سرلیک.
 *  - tooltipFields: په ټوولټیپ کې د هر کس لپاره کوم فیلډونه ښکاره شي.
 *  - fieldLabels: د پورته فیلډونو Pashto سرلیکونه (د ټوولټیپ لپاره).
 */
const props = defineProps({
  items: { type: Array, default: () => [] },
  groupBy: { type: String, default: "suspect_type" },
  title: { type: String, default: "عمومي جاج (د اوسني مخ معلومات)" },
  tooltipFields: {
    type: Array,
    default: () => ["name", "father_name", "tazkira"],
  },
  fieldLabels: {
    type: Object,
    default: () => ({
      name: "نوم",
      father_name: "د پلار نوم",
      tazkira: "تذکره",
    }),
  },
});

const emit = defineEmits(["hover", "leave", "select"]);

const el = ref(null);
let chart = null;

// Map د ګروپ له کلي څخه تر ټولو اړونده قطارونو پورې
const groupMap = computed(() => {
  const m = new Map();
  for (const r of props.items || []) {
    const key = (r?.[props.groupBy] ?? "نامعلوم") || "نامعلوم";
    if (!m.has(key)) m.set(key, []);
    m.get(key).push(r);
  }
  return m;
});

// د چارټ لپاره شمېرنې
const seriesData = computed(() =>
  Array.from(groupMap.value, ([name, arr]) => ({ name, value: arr.length }))
);

function buildTooltipHTML(name) {
  const list = groupMap.value.get(name) || [];
  const total = list.length;

  const rows = list.slice(0, 6).map((it) => {
    const parts = props.tooltipFields.map((f) => {
      const label = props.fieldLabels?.[f] ?? f;
      const v = it?.[f] ?? "—";
      return `<span style="white-space:nowrap"><b>${label}:</b> ${escapeHTML(String(v))}</span>`;
    });
    return `<li>${parts.join(" — ")}</li>`;
  });

  const more =
    total > 6
      ? `<div style="margin-top:4px;color:#555">+ ${total - 6} نور…</div>`
      : "";

  return `
    <div dir="rtl" style="text-align:right">
      <div style="margin-bottom:6px"><b>${escapeHTML(name)}</b> — شمېر: ${total}</div>
      <ul style="padding-inline-start:18px;margin:0">${rows.join("")}</ul>
      ${more}
    </div>
  `;
}

function escapeHTML(s) {
  return s.replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
}

function render() {
  if (!el.value) return;
  if (!chart) chart = echarts.init(el.value, null, { locale: "PS" });

  const option = {
    title: { text: props.title, left: "center" },
    textStyle: { fontFamily: "Amiri, 'Scheherazade New', 'Noto Naskh Arabic', sans-serif" },
    tooltip: {
      trigger: "item",
      borderWidth: 1,
      confine: true,
      backgroundColor: "#fff",
      textStyle: { color: "#111" },
      formatter: (p) => {
        const name = p?.name ?? "نامعلوم";
        return buildTooltipHTML(name);
      },
    },
    legend: { bottom: 10, left: "center" },
    series: [
      {
        name: "ډولونه",
        type: "pie",
        radius: "60%",
        center: ["50%", "50%"],
        selectedMode: "single",
        data: seriesData.value,
        emphasis: {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: "rgba(0,0,0,.35)",
          },
          label: { show: true },
        },
        label: {
          formatter: (p) => `${p.name}: ${p.value} (${p.percent}٪)`,
        },
      },
    ],
  };

  chart.setOption(option);

  // Events
  chart.off("mouseover");
  chart.off("globalout");
  chart.off("click");

  chart.on("mouseover", (params) => {
    const key = params?.name ?? "نامعلوم";
    emit("hover", {
      key,
      total: groupMap.value.get(key)?.length ?? 0,
      items: groupMap.value.get(key) ?? [],
    });
  });

  // کله چې موږوس بهر شي -> پاکول
  chart.on("globalout", () => {
    emit("leave");
  });

  // کلیک = انتخاب (په بل کانتینر کې بشپړ لست ښکاره کړئ)
  chart.on("click", (params) => {
    const key = params?.name ?? "نامعلوم";
    emit("select", {
      key,
      total: groupMap.value.get(key)?.length ?? 0,
      items: groupMap.value.get(key) ?? [],
    });
  });

  chart.resize();
}

function onResize() {
  if (chart) chart.resize();
}

onMounted(() => {
  render();
  window.addEventListener("resize", onResize);
});

watch(seriesData, render, { deep: true });
watch(() => props.title, render);
watch(() => props.groupBy, render);

onBeforeUnmount(() => {
  window.removeEventListener("resize", onResize);
  if (chart) {
    chart.dispose();
    chart = null;
  }
});
</script>

<template>
  <div class="captive-chart-card">
    <div ref="el" class="chart-host"></div>
  </div>
</template>

<style scoped>
.captive-chart-card {
  border: 1px solid rgba(0,0,0,.08);
  border-radius: 10px;
  padding: 10px;
  background: var(--v-theme-surface);
  direction: rtl; /* Pashto */
  text-align: right;
}
.chart-host {
  width: 100%;
  height: 320px;
}
</style>
