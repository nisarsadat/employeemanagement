<template>
  <v-app class="app-shell bg-white">
    <v-layout class="app-layout">
      <NavigationDrawer
        v-model="drawerOpen"
        :location="location"
        :dir="isRtl ? 'rtl' : 'ltr'"
        class="bg-bgproject"
        flat
        color="bgproject"
      />

      <div
        class="drawer-hover-zone"
        :class="isRtl ? 'right' : 'left'"
        @mouseenter="drawerOpen = true"
      ></div>

      <v-main class="main-shell bg-bgproject">
        <div class="main-shell__inner">
          <router-view />
        </div>
      </v-main>
    </v-layout>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import NavigationDrawer from '@/components/NavigationDrawer.vue';

const drawerOpen = ref(false);
const { locale } = useI18n();

const rtlLangs = ['fa', 'ps'];
const isRtl = computed(() => rtlLangs.includes(locale.value));
const location = computed(() => (isRtl.value ? 'right' : 'left'));
</script>

<style>
html,
body,
#app {
  height: 100%;
  margin: 0;
  overflow: hidden;
}

.app-shell {
  height: 100vh;
  overflow: hidden;
}

.app-layout {
  height: 100vh;
}

.drawer-hover-zone {
  position: fixed;
  top: 0;
  bottom: 0;
  width: 10px;
  z-index: 3000;
  background: transparent;
}

.drawer-hover-zone.left {
  left: 0;
}

.drawer-hover-zone.right {
  right: 0;
}

.main-shell {
  height: 100vh;
  overflow: hidden;
}

.main-shell__inner {
  height: 100vh;
  overflow-x: hidden;
  overflow-y: auto;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  padding-inline: 12px;
  padding-block: 10px;
  box-sizing: border-box;
}

/* desktop */
@media (min-width: 1280px) {
  .main-shell__inner {
    padding-inline: 18px;
    padding-block: 14px;
  }
}

/* smaller screens */
@media (max-width: 600px) {
  .main-shell__inner {
    padding-inline: 8px;
    padding-block: 8px;
  }

  .drawer-hover-zone {
    width: 6px;
  }
}
</style>
