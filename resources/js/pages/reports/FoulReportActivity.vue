<template>
  <template v-if="variant === 'cards'">
    <v-col cols="12" sm="6" lg="3">
      <v-card class="stat-card" elevation="0" :dir="isRtl ? 'rtl' : 'ltr'" @click="goMeetings">
        <v-card-text class="pa-4 d-flex align-center justify-space-between">
          <div>
            <div class="stat-label fontfamily">{{ t('جلسات') }}</div>
            <div class="stat-number fontfamily">{{ report?.meetings?.total ?? 0 }}</div>
            <v-chip size="x-small" color="secondary" variant="tonal" class="mt-2 fontfamily">
              <v-icon icon="mdi-account-group" size="x-small" class="mr-1" />
              {{ t('ثبت شده') }}
            </v-chip>
          </div>

          <v-avatar size="44" color="secondary" variant="tonal">
            <v-icon icon="mdi-account-group" />
          </v-avatar>
        </v-card-text>
      </v-card>
    </v-col>

    <v-col cols="12" sm="6" lg="3">
      <v-card class="stat-card" elevation="0" :dir="isRtl ? 'rtl' : 'ltr'" @click="goAssessments">
        <v-card-text class="pa-4 d-flex align-center justify-space-between">
          <div>
            <div class="stat-label fontfamily">{{ t('ارزیابی امنیتی') }}</div>
            <div class="stat-number fontfamily">{{ report?.security_assessments?.total ?? 0 }}</div>
            <v-chip size="x-small" color="deep-purple" variant="tonal" class="mt-2 fontfamily">
              <v-icon icon="mdi-shield-account" size="x-small" class="mr-1" />
              {{ t('ثبت شده') }}
            </v-chip>
          </div>

          <v-avatar size="44" color="deep-purple" variant="tonal">
            <v-icon icon="mdi-shield-account" />
          </v-avatar>
        </v-card-text>
      </v-card>
    </v-col>
  </template>

  <v-row v-else class="mt-4">
    <v-col cols="12" lg="6">
      <v-card class="data-card" elevation="0" :dir="isRtl ? 'rtl' : 'ltr'">
        <div class="card-header primary-header" @click="goMeetings">
          <div class="d-flex align-center">
            <v-avatar size="34" color="secondary" variant="tonal" class="mr-2">
              <v-icon icon="mdi-account-group" size="18" />
            </v-avatar>
            <div>
              <div class="card-title fontfamily">{{ t('جلسات') }}</div>
              <div class="card-subtitle fontfamily">{{ t('فهرست جلسات این دوره') }}</div>
            </div>
          </div>
          <v-chip color="secondary" variant="tonal" size="small" class="fontfamily">
            {{ report?.meetings?.total ?? 0 }}
          </v-chip>
        </div>

        <v-divider />

        <v-card-text class="pa-0" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-data-table
            :headers="meetingHeaders"
            :items="report?.meetings?.rows ?? []"
            item-key="id"
            density="comfortable"
            class="elevation-0"
            :loading="loading"
            hide-default-footer
            :no-data-text="t('هیچ دیتا وجود ندارد')"
            @click:row="(_, row) => goMeeting(row?.item)"
          />
        </v-card-text>
      </v-card>
    </v-col>

    <v-col cols="12" lg="6">
      <v-card class="data-card" elevation="0" :dir="isRtl ? 'rtl' : 'ltr'">
        <div class="card-header success-header" @click="goAssessments">
          <div class="d-flex align-center">
            <v-avatar size="34" color="deep-purple" variant="tonal" class="mr-2">
              <v-icon icon="mdi-shield-account" size="18" />
            </v-avatar>
            <div>
              <div class="card-title fontfamily">{{ t('ارزیابی امنیتی') }}</div>
              <div class="card-subtitle fontfamily">{{ t('فهرست ارزیابی های امنیتی این دوره') }}</div>
            </div>
          </div>
          <v-chip color="deep-purple" variant="tonal" size="small" class="fontfamily">
            {{ report?.security_assessments?.total ?? 0 }}
          </v-chip>
        </div>

        <v-divider />

        <v-card-text class="pa-0" :dir="isRtl ? 'rtl' : 'ltr'">
          <v-data-table
            :headers="assessmentHeaders"
            :items="report?.security_assessments?.rows ?? []"
            item-key="id"
            density="comfortable"
            class="elevation-0"
            :loading="loading"
            hide-default-footer
            :no-data-text="t('هیچ دیتا وجود ندارد')"
            @click:row="(_, row) => goAssessment(row?.item)"
          />
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>

<script setup>
  import { computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRouter } from 'vue-router';

  const props = defineProps({
    variant: { type: String, default: 'tables' },
    report: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    isRtl: { type: Boolean, default: true },
  });

  const { t } = useI18n();
  const router = useRouter();

  const meetingHeaders = computed(() => [
    { title: t('عنوان جلسه'), key: 'meeting_title' },
    { title: t('ارگان'), key: 'organ' },
    { title: t('ولایت'), key: 'province' },
    { title: t('ولسوالی'), key: 'district' },
    { title: t('تاریخ شروع'), key: 'start_date' },
  ]);

  const assessmentHeaders = computed(() => [
    { title: t('نام محل'), key: 'place_name' },
    { title: t('نام مسؤول / مالک'), key: 'responsible_owner_name' },
    { title: t('ولایت'), key: 'province' },
    { title: t('ناحیه / ولسوالی'), key: 'district' },
  ]);

  function goMeetings() {
    router.push({ path: '/app/meetings' });
  }

  function goMeeting(item) {
    if (!item?.id) return goMeetings();
    router.push({ path: `/app/meetings/form/${item.id}` });
  }

  function goAssessments() {
    router.push({ path: '/app/security-assessments' });
  }

  function goAssessment(item) {
    if (!item?.id) return goAssessments();
    router.push({ path: `/app/security-assessments/form/${item.id}` });
  }
</script>

<style scoped>
  .fontfamily,
  .fontfamily * {
    font-family: var(--app-font) !important;
  }

  .stat-card {
    border-radius: 12px;
    border: 1px solid #e9eef7;
    background: #ffffff;
    transition: all 0.2s ease;
    cursor: pointer;
    min-height: 92px;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(16, 24, 40, 0.08) !important;
  }

  .stat-label {
    font-size: 12px;
    color: rgba(17, 24, 39, 0.65);
  }

  .stat-number {
    font-size: 26px;
    font-weight: 900;
    line-height: 1.1;
    margin-top: 2px;
  }

  .data-card {
    border-radius: 12px;
    border: 1px solid #e9eef7;
    overflow: hidden;
    background: #fff;
  }

  .card-header {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
  }

  .card-title {
    font-size: 14px;
    font-weight: 800;
    color: rgba(17, 24, 39, 0.9);
  }

  .card-subtitle {
    font-size: 12px;
    color: rgba(17, 24, 39, 0.55);
    margin-top: 2px;
  }

  .primary-header {
    background: linear-gradient(135deg, #eef2ff 0%, #dde5ff 100%);
  }

  .success-header {
    background: linear-gradient(135deg, #e8fbf2 0%, #d2f7e5 100%);
  }

  :deep(.v-data-table) {
    font-size: 13px;
  }

  :deep(.v-data-table tbody tr) {
    cursor: pointer;
  }

  :deep(.v-data-table-header th) {
    font-weight: 800 !important;
    color: rgba(17, 24, 39, 0.7) !important;
    font-size: 12px !important;
  }
</style>
