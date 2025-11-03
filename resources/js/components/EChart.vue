<template>
  <div ref="el" :style="{ width: '100%', height }"></div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue'
import * as echarts from 'echarts'

const props = defineProps({
  option: { type: Object, required: true },
  height: { type: String, default: '320px' },
  theme: { type: [String, Object], default: null }
})

const el = ref(null)
let chart = null
let ro = null

const init = () => {
  if (!el.value) return
  chart = echarts.init(el.value, props.theme || undefined)
  chart.setOption(props.option, true)
  ro = new ResizeObserver(() => chart?.resize())
  ro.observe(el.value)
}

watch(() => props.option, (opt) => {
  if (chart && opt) chart.setOption(opt, true)
}, { deep: true })

onMounted(init)

onBeforeUnmount(() => {
  try { ro && ro.disconnect() } catch {}
  try { chart && chart.dispose() } catch {}
})
</script>
