import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js'
      ],
      refresh: true,
    }),
    vue(),
  ],

  // maplibre-gl spawns its worker from stringified module factories (Blob URL),
  // so esbuild helpers like __publicField injected by a < es2022 target are
  // invisible inside the worker scope and crash it. Keep both targets >= es2022
  // (class fields are ES2022) so dependency code is never downleveled.
  optimizeDeps: {
    esbuildOptions: {
      target: 'es2022'
    }
  },

  build: {
    sourcemap: true,
    target: 'es2022',
    minify: false
  }
})