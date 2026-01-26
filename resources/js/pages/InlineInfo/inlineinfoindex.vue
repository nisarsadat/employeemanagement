<template>
  <AppBar :title="t('مرکز ارتباطات')" @toggle-drawer="drawer = !drawer">
    <template #actions>
      <v-btn icon></v-btn>
    </template>
  </AppBar>

  <!-- 🟩 Parent Row -->
  <v-container fluid class="bg-bgproject split-wrapper">
    <v-row class="align-start">
      <!-- 🟩 Right Section (Resource Info) -->
      <v-col cols="12" md="4" lg="5" v-if="auth.can('inlineinfo.view')">
        <div class="column-scroll">
          <ResourceInfo />
        </div>
      </v-col>
      <!-- 🟦 Left Section (tabs + window) -->
      <v-col cols="12" md="8" lg="7" class="border-end">
        <div class="column-scroll">
          <v-tabs v-model="tab" fixed-tabs>
            <v-tab color="primary" value="resource">
              {{ t('اطلاعات') }}
            </v-tab>
            <v-tab color="primary" value="event"> {{ t('واقعات') }} </v-tab
            ><v-tab color="primary" value="khayrat">
              {{ t('راپور خیریت') }}
            </v-tab>
          </v-tabs>

          <v-window v-model="tab" v-if="auth.can('inlineinfo.view')">
            <v-window-item value="resource">
              <Resourcetab />
            </v-window-item>
            <v-window-item value="event">
              <Eventtab />
            </v-window-item>
            <v-window-item value="khayrat">
              <khayratreport />
            </v-window-item>
          </v-window>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
  import { ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import AppBar from '../../components/AppBar.vue';
  import Resourcetab from './Resourcetab.vue';
  import Eventtab from './Eventtab.vue';
  import ResourceInfo from './ResourceInfo.vue';
  import khayratreport from './khayratreport.vue';
  import { useLoginRepository } from '../../stores/LoginRepository';

  const auth = useLoginRepository();
  const { t } = useI18n();
  const drawer = ref(false);
  const tab = ref('resource');
</script>

<style scoped>
  .border-end {
    border-inline-end: 3px dotted #a8a7a7;
  }

  /* Wrapper just to keep things tidy */
  .split-wrapper {
    padding-top: 0;
  }

  /* 🔹 Each column scrolls independently */
  .column-scroll {
    max-height: calc(100vh - 120px); /* tune this if needed */
    overflow-y: auto;
    padding-bottom: 24px;
  }

  /* Optional: slim scrollbar */
  .column-scroll::-webkit-scrollbar {
    width: 4px;
  }
  .column-scroll::-webkit-scrollbar-track {
    background: transparent;
  }
  .column-scroll::-webkit-scrollbar-thumb {
    border-radius: 999px;
  }
</style>
