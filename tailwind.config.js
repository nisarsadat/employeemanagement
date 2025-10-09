/** @type {import('tailwindcss').Config} */
export default {
    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
    ],
    corePlugins: { preflight: false }, // important with Vuetify
    theme: { extend: {} },
    plugins: [],
  }
