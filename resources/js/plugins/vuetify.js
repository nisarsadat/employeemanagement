// // resources/js/plugins/vuetify.js
// import 'vuetify/styles';
// import { createVuetify } from 'vuetify';
// import { aliases, mdi } from 'vuetify/iconsets/mdi';
// import '@mdi/font/css/materialdesignicons.css';

// const vuetify = createVuetify({
//   icons: {
//     defaultSet: 'mdi',
//     aliases,
//     sets: { mdi },
//   },
// });

// export default vuetify;
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export default createVuetify({
  components,
  directives,
  defaults: {
    global: { density: 'compact' },   // smaller components app-wide
    VBtn: { density: 'compact', size: 'default' },
    VTextField: { density: 'compact' },
    VSelect: { density: 'compact' },
    VAutocomplete: { density: 'compact' },
    VDataTable: { density: 'compact' },
  },
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          background: '#F8F9FA',  // Light gray background
          surface: '#FFFFFF',     // White surfaces
          primary: '##08989f',     // Deep green (Material Green 800)
          navi: '#c7edef',     // Deep green (Material Green 800)
          blackicon: '#000',     // Deep green (Material Green 800)
          'primary-darken-1': '#1B5E20',  // Darker green
          secondary: '#607D8B',   // Blue gray
          'secondary-darken-1': '#455A64',
          accent: '#8BC34A',      // Light green accent
          error: '#D32F2F',
          info: '#0288D1',
          bgproject: '#f5f7fa',
          success: '#388E3C',     // Different shade of greena
          warning: '#F57C00',
          'on-primary': '#FFFFFF', // White text on primary
          'on-secondary': '#FFFFFF', // White text on secondary
          green: '#388E3C'
        }
      },
      dark: {
        colors: {
          background: '#121212',
          surface: '#1E1E1E',
          primary: '#2E7D32',      // Brighter green for dark mode
          'primary-darken-1': '#388E3C',
          navi: '#08480b',     // Deep green (Material Green 800)
          secondary: '#546E7A',
          'secondary-darken-1': '#455A64',
          accent: '#7CB342',       // Softer green accent
          error: '#F44336',
          info: '#29B6F6',
          success: '#08989f',      // Light green success
          warning: '#FFA726',
          'on-primary': '#FFFFFF',
          'on-secondary': '#FFFFFF',
          bgproject: '#455A64',
          blackicon: '#000',     // Deep green (Material Green 800)
          green: '#08989f'

        }
      }
    }
  }
})
