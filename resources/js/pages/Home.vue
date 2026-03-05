<!-- <template>
  <v-app flat fluid class="rounded-xl bg-white">
    <v-layout
      flat
      fluid
      class="no-scroll-layout"
    >
      <NavigationDrawer
        v-if="employeerepository.drawerVisible"
        :location="location"
        :dir="isRtl ? 'rtl' : 'ltr'"
        class="bg-bgproject"
        flat
        :rail="employeerepository.rail"
        color="bgproject"
      />

      <v-main
        class="thepage h-ful scrollabl-content-home rounded-xl bg-bgproject scroll-area scroll-hidden"
      >
        <router-view />
      </v-main>
    </v-layout>
  </v-app>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import NavigationDrawer from "@/components/NavigationDrawer.vue";
import { useEmployeOfficeRepository } from "@/stores/EmployeeRepository";

const employeerepository = useEmployeOfficeRepository();
const { locale } = useI18n();

const isRtl = ref(locale.value === "fa");
watch(locale, (l) => (isRtl.value = l === "fa"));

const location = computed(() => (locale.value === "fa" ? "right" : "left"));
</script> -->
<template>
  <v-app flat fluid class="rounded-xl bg-white">
    <v-layout flat fluid class="no-scroll-layout">
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
      <v-main class="main-scroll rounded-xl bg-bgproject">
        <router-view />
      </v-main>
    </v-layout>
  </v-app>
</template>

<script setup>
  import { ref, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute } from 'vue-router'; // ✅
  import NavigationDrawer from '@/components/NavigationDrawer.vue';
  import { useEmployeOfficeRepository } from '@/stores/EmployeeRepository';
  const drawerOpen = ref(false);
  const employeerepository = useEmployeOfficeRepository();
  const { locale } = useI18n();
  const route = useRoute(); // ✅

  const rtlLangs = ['fa', 'ps'];
  const isRtl = computed(() => rtlLangs.includes(locale.value));
  const location = computed(() => (rtlLangs.includes(locale.value) ? 'right' : 'left'));
</script>
<style>
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
  .main-scroll {
    height: 100vh;
    overflow-y: auto;
    overflow-x: hidden;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    padding-right: 12px;
  }
</style>
