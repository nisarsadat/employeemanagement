<!-- src/views/fifthyFormats/FiftyFormatsRoute.vue -->
<template>
  <AppBar
    :title="t('پنجاه فورم‌ها')"
    :subtitle="t('لینک‌های سریع پنجاه فورم')"
    @toggle-drawer="drawer = !drawer"
  >
    <template #actions>
      <v-btn icon></v-btn>
    </template>
  </AppBar>

  <v-app dir="rtl" class="fontfamilyis">
    <div class="min-h-screen bg-grey-lighten-4">
      <!-- Container -->
      <section class="mx-auto px-3 px-sm-4 py-6 py-md-8" style="max-width: 1400px">
        <!-- Header -->
        <header class="mb-4 mb-md-6">
          <v-card flat color="transparent">
            <v-card-text class="pa-0">
              <div class="d-flex align-center flex-row-reverse">
                <div class="icon-wrapper mr-2">
                  <v-icon size="32" color="primary"> mdi-view-grid-outline </v-icon>
                </div>
                <div class="flex-grow-1 mx-6">
                  <h1
                    class="text-h5 text-sm-h4 text-md-h3 font-weight-bold text-primary text-right fontfamilyis"
                  >
                    {{ t('پنجاه فورم‌ها') }}
                  </h1>
                  <p
                    class="text-body-2 text-sm-body-1 text-grey mt-1 mt-sm-2 text-right fontfamilyis"
                  >
                    {{
                      t(
                        'لینک‌های سریع برای مدیریت هر فورم. برای فیلتر کردن فورم‌های موجود از جستجو استفاده کنید'
                      )
                    }}
                  </p>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </header>

        <!-- Toolbar -->
        <v-card class="mb-4 mb-md-6 pa-4 pa-sm-5" elevation="2" rounded="xl">
          <div class="d-flex align-center justify-space-between flex-wrap gap-3">
            <!-- Results Counter -->
            <div class="text-body-2 text-grey-darken-1 font-weight-medium fontfamilyis">
              <span class="d-inline-flex align-center gap-1">
                {{ t('نمایش') }}
                <span class="text-primary font-weight-bold">{{ filtered.length }}</span>
                {{ t('از') }}
                <span class="text-primary font-weight-bold">{{ original.length }}</span>
                {{ t('فورم') }}

                <span v-if="q" class="d-inline-flex align-center gap-1 mr-2">
                  {{ t('برای') }}
                  <span class="text-secondary font-weight-bold">«{{ q }}»</span>
                </span>
              </span>
            </div>

            <!-- Search Section -->
            <v-text-field
              v-model="q"
              density="compact"
              :placeholder="t('جستجوی فورم‌ها...')"
              append-inner-icon="mdi-magnify"
              variant="outlined"
              hide-details
              clearable
              color="primary"
              bg-color="grey-lighten-4"
              class="search-field flex-grow-1 fontfamilyis"
              style="min-width: 400px; max-width: 800px"
              single-line
            />
          </div>
        </v-card>

        <!-- Grid of module cards (permission-guarded) -->
        <v-row dense class="ma-0 fontfamilyis">
          <v-col
            v-for="item in filtered"
            :key="item.path"
            cols="12"
            sm="6"
            md="4"
            lg="3"
            xl="2"
            class="pa-1 pa-sm-2 fontfamilyis"
          >
            <router-link :to="toPath(item)" style="text-decoration: none">
              <v-card class="module-card fill-height fontfamilyis" elevation="2" hover>
                <v-card-text class="pa-3 pa-sm-4 fontfamilyis">
                  <div class="d-flex align-center gap-3 flex-row-reverse fontfamilyis">
                    <!-- Icon -->
                    <div class="icon-wrapper flex-shrink-0 fontfamilyis">
                      <v-icon :size="smAndUp ? 32 : 28" color="primary" class="module-icon">
                        {{ getMaterialIcon(item.icon) }}
                      </v-icon>
                    </div>

                    <!-- Label + count -->
                    <div class="flex-grow-1 min-w-0 text-right fontfamilyis">
                      <h3
                        class="text-body-2 text-sm-body-1 font-weight-bold line-clamp-2 mb-0 fontfamilyis"
                      >
                        {{ t(item.labelFa) }}
                      </h3>

                      <!-- 🔹 Always show a number (0+) -->
                      <div class="text-caption text-grey-darken-1 mt-1 fontfamilyis">
                        <template v-if="loadingCounts">
                          <span class="text-grey-darken-1 fontfamilyis">
                            {{ t('در حال بارگیری...') }}
                          </span>
                        </template>

                        <template v-else>
                          {{ t('ریکارد های موجود') }}:
                          <span class="text-primary text-body-1 font-weight-bold mx-1 fontfamilyis">
                            {{ displayCount(item.slug) }}
                          </span>
                        </template>
                      </div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </router-link>
          </v-col>
        </v-row>

        <!-- Empty state -->
        <v-card
          v-if="filtered.length === 0"
          class="mt-6 mt-md-10 text-center pa-4 pa-sm-8"
          elevation="2"
          color="grey-lighten-3"
          rounded="lg"
        >
          <v-card-text>
            <v-icon :size="smAndUp ? 64 : 48" color="grey" class="mb-2 mb-sm-4">
              mdi-magnify-remove
            </v-icon>
            <h3 class="text-h6 text-sm-h5 text-grey-darken-2 mb-1 mb-sm-2 fontfamilyis">
              {{ t('هیچ فورمی پیدا نشد') }}
            </h3>
            <p class="text-body-2 text-sm-body-1 text-grey fontfamilyis">
              {{
                t('برای «{query}» هیچ نتیجه‌ای پیدا نشد. لطفاً واژه‌های جستجو را تغییر بدهید.', {
                  query: q,
                })
              }}
            </p>
            <v-btn
              class="mt-2 mt-sm-4"
              color="primary"
              size="small"
              @click="q = ''"
              append-icon="mdi-backspace"
            >
              {{ t('پاک کردن جستجو') }}
            </v-btn>
          </v-card-text>
        </v-card>
      </section>
    </div>
  </v-app>
</template>

<script setup>
  import { ref, computed, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useDisplay } from 'vuetify';
  import axios from 'axios';
  import { useLoginRepository } from '@/stores/LoginRepository';
  import AppBar from '@/components/AppBar.vue';

  const drawer = ref(false);

  const auth = useLoginRepository();
  const { t } = useI18n();
  const { smAndUp } = useDisplay();

  const q = ref('');
  const loadingCounts = ref(true);
  // 🔹 56 formats list (names taken from routes/api.php)
  const original = ref([
    // ---------------------- Main Fifty Formats (10) ----------------------
    {
      slug: 'fasads',
      labelFa: 'فورم‌های فساد',
      path: '/fasads',
      icon: '🧩',
      perm: 'fasads',
    },
    {
      slug: 'saeenfasad',
      labelFa: 'فورم‌های فساد ساعین',
      path: '/saeenfasad',
      icon: '🧠',
      perm: 'saeen_fasads',
    },
    {
      slug: 'heist',
      labelFa: 'فورم‌های سرقت',
      path: '/heist',
      icon: '🐱‍👤',
      perm: 'heists',
    },
    {
      slug: 'captives',
      labelFa: 'فورم‌های بندی‌ها',
      path: '/captives',
      icon: '🔒',
      perm: 'captives',
    },
    {
      slug: 'mulkanos',
      labelFa: 'فورم‌های ملک‌ها',
      path: '/mulkanos',
      icon: '🏠',
      perm: 'mulkanos',
    },
    {
      slug: 'entry-exits',
      labelFa: 'فورم‌های دخول/خروج',
      path: '/entry-exits',
      icon: '➡️',
      perm: 'entry_exits',
    },
    {
      slug: 'sarafyanos',
      labelFa: 'فورم‌های صرافی‌ها',
      path: '/sarafyanos',
      icon: '💱',
      perm: 'sarafyanos',
    },
    {
      slug: 'clubs',
      labelFa: 'فورم‌های کلب‌ها',
      path: '/clubs',
      icon: '🎯',
      perm: 'clubs',
    },
    {
      slug: 'mahajerats',
      labelFa: 'فورم‌های مهاجرت',
      path: '/mahajerats',
      icon: '🧳',
      perm: 'mahajerats',
    },
    {
      slug: 'soldiers',
      labelFa: 'فورم‌های سربازان',
      path: '/soldiers',
      icon: '🪖',
      perm: 'soldiers',
    },

    // ---------------------- Fifthy Format One (10) ----------------------
    {
      slug: 'mawads',
      labelFa: 'فورم‌های مواد مخدر',
      path: '/mawads',
      icon: '💊',
      perm: 'mawads',
    },
    {
      slug: 'lost-mujremeens',
      labelFa: 'فورم‌های مجرمین گمشده',
      path: '/lostmujremeens',
      icon: '🔍',
      perm: 'lost_mujremeens',
    },
    {
      slug: 'old-soldiers-homes',
      labelFa: 'فورم‌های خانه‌های سربازان قدیمی',
      path: '/oldsoldiershomes',
      icon: '🏚️',
      perm: 'old_soldiers_homes',
    },
    {
      slug: 'robbers',
      labelFa: 'فورم‌های دزدان',
      path: '/robbers',
      icon: '💰',
      perm: 'robbers',
    },
    {
      slug: 'non-responsable-people',
      labelFa: 'فورم‌های افراد مسلح غیرمسؤول',
      path: '/nonresponsablepeople',
      icon: '⚠️',
      perm: 'non_responsable_people',
    },
    {
      slug: 'mashkok-homes',
      labelFa: 'فورم‌های خانه‌های مشکوک',
      path: '/mashkokhomes',
      icon: '🏘️',
      perm: 'mashkok_homes',
    },
    {
      slug: 'damage-places',
      labelFa: 'فورم‌های ساحات آسیب‌دیده',
      path: '/damageplaces',
      icon: '🏚️',
      perm: 'damage_places',
    },
    {
      slug: 'damage-rasan-places',
      labelFa: 'فورم‌های ساحات رسانندهٔ آسیب',
      path: '/damagerasanplaces',
      icon: '💥',
      perm: 'damage_rasan_places',
    },
    {
      slug: 'mashkok-shops',
      labelFa: 'فورم‌های دکان‌های مشکوک',
      path: '/mashkokshops',
      icon: '🏪',
      perm: 'mashkok_shops',
    },
    {
      slug: 'luchakanos',
      labelFa: 'فورم‌های لوچک‌ها',
      path: '/luchakanos',
      icon: '👤',
      perm: 'luchakanos',
    },

    // ---------------------- Fifthy Format Two (10) ----------------------
    {
      slug: 'schools',
      labelFa: 'فورم‌های مکتب‌ها',
      path: '/schools',
      icon: '🏫',
      perm: 'schools',
    },
    {
      slug: 'hado-bus-stops',
      labelFa: 'فورم‌های ایستگاه‌های بس',
      path: '/hadobusstops',
      icon: '🚏',
      perm: 'hado_bus_stops',
    },
    {
      slug: 'transport-companies',
      labelFa: 'فورم‌های شرکت‌های ترانسپورتی',
      path: '/transportcompanies',
      icon: '🚚',
      perm: 'transport_companies',
    },
    {
      slug: 'car-sales-companies',
      labelFa: 'فورم‌های کمپنی‌های فروش موتر',
      path: '/carsalescompanies',
      icon: '🚗',
      perm: 'car_sales_companies',
    },
    {
      slug: 'parkings',
      labelFa: 'فورم‌های پارکینگ',
      path: '/parkings',
      icon: '🅿️',
      perm: 'parkings',
    },
    {
      slug: 'banks',
      labelFa: 'فورم‌های بانک‌ها',
      path: '/banks',
      icon: '🏦',
      perm: 'banks',
    },
    {
      slug: 'apartments',
      labelFa: 'فورم‌های آپارتمان‌ها',
      path: '/apartments',
      icon: '🏢',
      perm: 'apartments',
    },
    {
      slug: 'rahnoma-mamlats',
      labelFa: 'فورم‌های رهنمای معاملات',
      path: '/rahnomamamlats',
      icon: '📋',
      perm: 'rahnoma_mamlats',
    },
    {
      slug: 'hosppitals',
      labelFa: 'فورم‌های شفاخانه‌ها',
      path: '/hosppitals',
      icon: '🏥',
      perm: 'hosppitals',
    },
    {
      slug: 'work-shops',
      labelFa: 'فورم‌های کارگاه‌ها',
      path: '/workshops',
      icon: '🔧',
      perm: 'work_shops',
    },

    // ---------------------- Fifthy Format Three (10) ----------------------
    {
      slug: 'badmashanos',
      labelFa: 'فورم‌های بدماش‌ها',
      path: '/badmashanos',
      icon: '✊',
      perm: 'badmashanos',
    },
    {
      slug: 'sarayanos',
      labelFa: 'فورم‌های سرای‌ها',
      path: '/sarayanos',
      icon: '🏭',
      perm: 'sarayanos',
    },
    {
      slug: 'emarat-offices',
      labelFa: 'فورم‌های دفاتر امارت',
      path: '/emaratoffices',
      icon: '🏛️',
      perm: 'emarat_offices',
    },
    {
      slug: 'hostels',
      labelFa: 'فورم‌های هوسٹل‌ها',
      path: '/hostels',
      icon: '🛏️',
      perm: 'hostels',
    },
    {
      slug: 'hotels',
      labelFa: 'فورم‌های هوتل‌ها',
      path: '/hotels',
      icon: '🏨',
      perm: 'hotels',
    },
    {
      slug: 'madrasas',
      labelFa: 'فورم‌های مدارس دینی',
      path: '/madrasas',
      icon: '📖',
      perm: 'madrasas',
    },
    {
      slug: 'mashkok-people',
      labelFa: 'فورم‌های افراد مشکوک',
      path: '/mashkokpeoples',
      icon: '🕵️‍♂️',
      perm: 'mashkok_people',
    },
    {
      slug: 'mujahedinos',
      labelFa: 'فورم‌های مجاهدین',
      path: '/mujahedinos',
      icon: '🕌',
      perm: 'mujahedinos',
    },
    {
      slug: 'old-jahadis',
      labelFa: 'فورم‌های قومندانان جهادی قدیمی',
      path: '/oldjahadis',
      icon: '🎖️',
      perm: 'old_jahadis',
    },

    {
      slug: 'tujarans',
      labelFa: 'فورم‌های تاجران',
      path: '/tujarans',
      icon: '👔',
      perm: 'tujarans',
    },

    // ---------------------- Fifthy Format Four (16) ----------------------
    {
      slug: 'checkpoints',
      labelFa: 'فورم‌های چک‌پوینت‌ها',
      path: '/checkpoints',
      icon: '🛂',
      perm: 'checkpoints',
    },
    {
      slug: 'masjids',
      labelFa: 'فورم‌های مسجدها',
      path: '/masjids',
      icon: '🕌',
      perm: 'masjids',
    },
    {
      slug: 'highways',
      labelFa: 'فورم‌های شاهراه‌ها',
      path: '/highways',
      icon: '🛣️',
      perm: 'highways',
    },
    {
      slug: 'companies',
      labelFa: 'فورم‌های شرکت‌ها',
      path: '/companies',
      icon: '🏢',
      perm: 'companies',
    },
    {
      slug: 'done-lost-places',
      labelFa: 'فورم‌های ساحات دزدی شده',
      path: '/done-lost-places',
      icon: '🏚️',
      perm: 'donelostplaces',
    },
    {
      slug: 'econemy-projects',
      labelFa: 'فورم‌های پروژه‌های اقتصادی',
      path: '/econemy-projects',
      icon: '📊',
      perm: 'econemyprojects',
    },
    {
      slug: 'criminal-capture-places',
      labelFa: 'فورم‌های ساحات دستگیری مجرمین',
      path: '/criminal-capture-places',
      icon: '🚔',
      perm: 'criminalcaptureplaces',
    },
    {
      slug: 'pumps',
      labelFa: 'فورم‌های پمپ‌ها',
      path: '/pumps',
      icon: '⛽',
      perm: 'pumps',
    },
    {
      slug: 'shoras',
      labelFa: 'فورم‌های شوراها',
      path: '/shoras',
      icon: '👥',
      perm: 'shoras',
    },
    {
      slug: 'governmentplaces',
      labelFa: 'فورم‌های اماکن حکومتی',
      path: '/governmentplaces',
      icon: '🏛️',
      perm: 'governmentplaces',
    },
    {
      slug: 'supermarkets',
      labelFa: 'فورم‌های سوپرمارکت‌ها',
      path: '/supermarkets',
      icon: '🛒',
      perm: 'supermarkets',
    },
    {
      slug: 'parks',
      labelFa: 'فورم‌های پارک‌ها',
      path: '/parks',
      icon: '🌳',
      perm: 'parks',
    },
    {
      slug: 'complexes',
      labelFa: 'فورم‌های مجتمع‌ها',
      path: '/complexes',
      icon: '🏬',
      perm: 'complexes',
    },
    {
      slug: 'deplomaticplaces',
      labelFa: 'فورم‌های اماکن دیپلوماتیک',
      path: '/deplomaticplaces',
      icon: '🇺🇳',
      perm: 'deplomaticplaces',
    },
    {
      slug: 'universities',
      labelFa: 'فورم‌های پوهنتون‌ها',
      path: '/universities',
      icon: '🎓',
      perm: 'universities',
    },
    {
      slug: 'wakilanos',
      labelFa: 'فورم‌های وکیلان',
      path: '/wakilanos',
      icon: '⚖️',
      perm: 'wakilanos',
    },
  ]);

  // 👉 counts: all slugs start at 0 so UI always shows a number
  const counts = ref(Object.fromEntries(original.value.map((item) => [item.slug, 0])));

  const APP_BASE = '/app';
  const toPath = (item) => `${APP_BASE}${item.path}`;

  function canView(item) {
    const perm = item?.perm ? `${item.perm}.view` : null;
    if (!perm || typeof auth?.can !== 'function') return true;
    return auth.can(perm);
  }

  const items = ref([...original.value]);

  const visibleItems = computed(() => items.value.filter(canView));

  const filtered = computed(() => {
    const term = q.value?.trim().toLowerCase() || '';
    const base = visibleItems.value;
    if (!term) return base;
    return base.filter(
      (i) => i.labelFa.toLowerCase().includes(term) || i.path.toLowerCase().includes(term)
    );
  });

  function getMaterialIcon(emojiIcon) {
    const m = {
      '🧩': 'mdi-puzzle',
      '🧠': 'mdi-brain',
      '🕵️': 'mdi-spy',
      '🔒': 'mdi-lock',
      '🏠': 'mdi-home',
      '➡️': 'mdi-export',
      '💱': 'mdi-currency-usd',
      '🎯': 'mdi-target',
      '🧳': 'mdi-briefcase',
      '🪖': 'mdi-shield-account',
      '💊': 'mdi-pill',
      '🔍': 'mdi-magnify',
      '🏚️': 'mdi-home',
      '💰': 'mdi-cash',
      '⚠️': 'mdi-alert',
      '🏘️': 'mdi-home-group',
      '💥': 'mdi-bomb',
      '🏪': 'mdi-store',
      '👤': 'mdi-account',
      '🏫': 'mdi-school',
      '🚏': 'mdi-bus-stop',
      '🚚': 'mdi-truck',
      '🚗': 'mdi-car',
      '🅿️': 'mdi-parking',
      '🏦': 'mdi-bank',
      '📋': 'mdi-clipboard-text',
      '🏥': 'mdi-hospital',
      '🔧': 'mdi-wrench',
      '✊': 'mdi-fist',
      '🏭': 'mdi-warehouse',
      '🏨': 'mdi-hotel',
      '🛏️': 'mdi-bed',
      '👔': 'mdi-tie',
      '🕌': 'mdi-mosque',
      '🏛️': 'mdi-government',
      '🎖️': 'mdi-medal',
      '🕵️‍♂️': 'mdi-account-question',
      '📖': 'mdi-book-open-variant',
      '⚖️': 'mdi-scale-balance',
      '🎓': 'mdi-school',
      '🛣️': 'mdi-highway',
      '📊': 'mdi-chart-bar',
      '🚔': 'mdi-police-badge',
      '⛽': 'mdi-gas-station',
      '👥': 'mdi-account-group',
      '🛂': 'mdi-shield-check',
      '🛒': 'mdi-cart',
      '🌳': 'mdi-tree',
      '🏬': 'mdi-domain',
      '🇺🇳': 'mdi-diploma',
    };
    return m[emojiIcon] || 'mdi-cube';
  }

  // Always returns a number (0 if missing)
  function displayCount(slug) {
    return counts.value[slug] ?? 0;
  }

  async function fetchCounts() {
    loadingCounts.value = true; // 🔹 start loading

    try {
      // If axios.defaults.baseURL = '/api', this hits /api/fifty-formats/counts
      const { data } = await axios.get('/fifty-formats/counts');
      if (data && typeof data === 'object') {
        for (const [slug, value] of Object.entries(data)) {
          if (Object.prototype.hasOwnProperty.call(counts.value, slug)) {
            counts.value[slug] = value ?? 0;
          }
        }
      }
    } catch (error) {
      console.error('Failed to load 56-format counts', error);
    } finally {
      loadingCounts.value = false; // 🔹 done loading (success or error)
    }
  }

  onMounted(() => {
    document.documentElement.setAttribute('dir', 'rtl');
    document.documentElement.setAttribute('lang', 'fa');
    fetchCounts();
  });
</script>

<style scoped>
  .module-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 12px;
    min-height: 80px !important;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: white;
    border: 1px solid #e0e0e0;
  }
  .module-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1) !important;
    border-color: #2196f3;
  }
  .icon-wrapper {
    transition: all 0.3s ease;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background-color: rgba(33, 150, 243, 0.1);
  }
  .module-card:hover .icon-wrapper {
    transform: scale(1.1);
    background-color: rgba(33, 150, 243, 0.15);
  }
  .gap-3 {
    gap: 12px;
  }
  .min-h-screen {
    min-height: 100vh;
  }
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
  }
  .v-card-text {
    flex: 0 0 auto;
    padding: 16px !important;
  }
  .min-w-0 {
    min-width: 0;
  }
  .flex-shrink-0 {
    flex-shrink: 0;
  }
  .flex-grow-1 {
    flex-grow: 1;
  }
  .text-right {
    text-align: right;
  }
  [dir='rtl'] .flex-row-reverse {
    flex-direction: row-reverse;
  }
  @media (max-width: 600px) {
    .search-field {
      min-width: 180px !important;
    }
    .module-card {
      min-height: 70px !important;
      border-radius: 10px;
    }
    .icon-wrapper {
      width: 40px;
      height: 40px;
    }
  }
  @media (max-width: 400px) {
    .search-field {
      min-width: 150px !important;
    }
  }
  .text-body-2 {
    font-size: 0.875rem !important;
    font-weight: 600 !important;
  }
  .text-body-1 {
    font-size: 1rem !important;
    font-weight: 600 !important;
  }
  .fontfamilyis {
    font-family: var(--app-font) !important;
  }
</style>
