<template>
  <AppBar :title="t('معلومات خطی')" @toggle-drawer="drawer = !drawer">
    <template #actions>
      <v-btn icon></v-btn>
    </template>
  </AppBar>

  <!-- 🟩 Parent Row -->
  <v-container fluid class="bg-bgproject">
    <v-row class="align-start">
      <!-- 🟦 Left Section (70%) -->
      <v-col cols="12" md="8" lg="7" class="border-end">
        <v-tabs v-model="tab" fixed-tabs>
          <v-tab color="primary" value="resource">{{ t('اطلاعات') }}</v-tab>
          <v-tab color="primary" value="event">{{ t('پیښي') }}</v-tab>
        </v-tabs>

        <v-window v-model="tab"v-if="auth.can('inlineinfo.view')">
          <v-window-item value="resource">
            <Resourcetab />
          </v-window-item>
          <v-window-item value="event">
            <Eventtab />
          </v-window-item>
        </v-window>
      </v-col>

      <!-- 🟩 Right Section (30%) -->
      <v-col cols="12" md="4" lg="5" v-if="auth.can('inlineinfo.view')">
        <ResourceInfo />
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
</style>
