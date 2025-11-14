<template>
  <AppBar :title="t('کارمند های فعال و غیر فعال')" @toggle-drawer="drawer = !drawer">
    <template #actions>
      <v-btn icon />
    </template>
  </AppBar>

  <v-container fluid class="bg-bgproject py-4" :dir="isRtl ? 'rtl' : 'ltr'">
    <v-row class="align-start">
      <v-col cols="12">
        <!-- Two tabs: Active | Inactive -->
        <v-tabs
          v-model="tab"
          fixed-tabs
          class="mb-3 rounded-xl"
          align-tabs="center"
          density="comfortable"
          color="primary"
        >
          <v-tab value="active">{{ t('فعال') }}</v-tab>
          <v-tab value="inactive">{{ t('غیر فعال') }}</v-tab>
        </v-tabs>

        <v-window v-model="tab">
          <v-window-item value="active">
            <v-card elevation="2" class="pa-2 rounded-xl">
              <ActiveEmployeesIndex />
            </v-card>
          </v-window-item>

          <v-window-item value="inactive">
            <v-card elevation="2" class="pa-2 rounded-xl">
              <InactiveEmployeesIndex />
            </v-card>
          </v-window-item>
        </v-window>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

import AppBar from '../../components/AppBar.vue'

// Replace these with your actual component paths
import ActiveEmployeesIndex from './ActiveEmployeesIndex.vue'
import InactiveEmployeesIndex from './InactiveEmployeesIndex.vue'

const { t, locale } = useI18n()
const isRtl = computed(() => /^ps|^fa|^ar/i.test(locale.value || ''))

const drawer = ref(false)
const tab = ref('active') // 'active' | 'inactive'
</script>

<style scoped>
:deep(.v-tabs) {
  backdrop-filter: saturate(120%) blur(2px);
}

:deep(.v-tab) {
  font-weight: 600;
  letter-spacing: 0.2px;
}

:deep(.v-card) {
  border-radius: 16px;
}

:deep(.v-window-item) {
  padding: 8px;
}

/* Better spacing for RTL/LTR */
.v-container[dir='rtl'] :deep(.v-tab__slider) {
  inset-inline-start: 0 !important;
}
</style>
