<template>
  <v-container class="py-4" dir="rtl">
    <div class="d-flex justify-end ga-2 mb-3 no-print">
      <v-btn variant="text" @click="goBack">{{ t('برگشت') }}</v-btn>
      <v-btn color="error" prepend-icon="mdi-printer" @click="printPage">{{ t('چاپ') }}</v-btn>
    </div>

    <div class="sheet">
      <h1 class="text-h6 text-center mb-1">{{ t('فورم نهایی جلسه') }}</h1>
      <p class="text-center text-caption mb-4">{{ item.meeting_title || '—' }}</p>

      <table class="grid">
        <tbody>
          <tr v-for="row in rows" :key="row.label">
            <th>{{ row.label }}</th>
            <td>{{ row.value }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </v-container>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useDatabaseRepository } from '@/stores/DatabaseRepository';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const repo = useDatabaseRepository();
const item = ref({});

const employeeCodes = computed(() => {
  const people = Array.isArray(item.value.office_employees) ? item.value.office_employees : [];
  const codes = people.map((person) => person.code_number).filter(Boolean);
  return codes.length ? codes.join('، ') : '—';
});

const rows = computed(() => [
  [t('عنوان جلسه'), item.value.meeting_title],
  [t('ارگان'), item.value.organ],
  [t('ولایت'), item.value.province],
  [t('ولسوالی'), item.value.district],
  [t('محل مشخص'), item.value.specific_place],
  [t('کوردینات'), item.value.coordinates],
  [t('تاریخ شروع'), item.value.start_date],
  [t('تاریخ ختم'), item.value.end_date],
  [t('مجموع زمان'), item.value.total_time],
  [t('اشتراک کننده گان'), item.value.participant],
  [t('اشتراک کننده مسئول'), item.value.boss_participant],
  [t('ترسره کوونکي'), employeeCodes.value],
  [t('فعالیت های انجام شده'), item.value.done_actions],
  [t('حالات مشکوک'), item.value.mashkok_situations],
  [t('مشکلات'), item.value.problems],
  [t('تهیه کننده جلسه'), item.value.meeting_supplier],
  [t('شماره تماس تهیه کننده'), item.value.meeting_supplier_phone],
  [t('مسئول امنیت جلسه'), item.value.meeting_amniyat_responsable],
  [t('شماره تماس مسئول امنیت'), item.value.meeting_amniyat_responsable_phone],
  [t('ملاحظات'), item.value.note],
].map(([label, value]) => ({
  label,
  value: value === null || value === undefined || String(value).trim() === '' ? '—' : value,
})));

function goBack() {
  router.push('/app/meetings');
}

function printPage() {
  window.print();
}

onMounted(async () => {
  const data = await repo.FetchMeetingData(route.params.id);
  item.value = data || {};
});
</script>

<style scoped>
.sheet {
  background: #fff;
  border: 1px solid #222;
  padding: 16px;
}
.grid {
  width: 100%;
  border-collapse: collapse;
}
.grid th,
.grid td {
  border: 1px solid #222;
  padding: 6px 8px;
  text-align: right;
  vertical-align: top;
}
.grid th {
  width: 32%;
  background: #f4f4f4;
}
@media print {
  .no-print {
    display: none !important;
  }
}
</style>
