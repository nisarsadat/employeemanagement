import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue(),
  ],

  build: {
    // ✅ reduce build output size
    sourcemap: false,

    // ✅ good target for modern browsers (less polyfills)
    target: 'es2017',

    // ✅ keep CSS split (better loading)
    cssCodeSplit: true,

    // ✅ fast minify
    minify: 'esbuild',

    // (only a warning limit, not performance)
    chunkSizeWarningLimit: 1500,

    rollupOptions: {
      output: {
        // ✅ force vendor chunk splitting
        manualChunks(id) {
          if (!id.includes('node_modules')) return

          if (id.includes('vue') || id.includes('vue-router') || id.includes('pinia')) {
            return 'vendor-vue'
          }

          if (id.includes('vuetify')) {
            return 'vendor-vuetify'
          }

          if (id.includes('echarts') || id.includes('chart.js') || id.includes('apexcharts')) {
            return 'vendor-charts'
          }

          if (id.includes('maplibre') || id.includes('mapbox') || id.includes('leaflet') || id.includes('pmtiles')) {
            return 'vendor-map'
          }

          return 'vendor'
        },
      },
    },
  },
})