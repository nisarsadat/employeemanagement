<!-- src/views/fifthyFormats/FiftyFormatsRoute.vue -->
<template>
  <v-app dir="rtl">
    <div class="min-h-screen bg-grey-lighten-4">
      <!-- Container -->
      <section class="mx-auto px-3 px-sm-4 py-6 py-md-8" style="max-width: 1400px">
        <!-- Header -->
        <header class="mb-4 mb-md-6">
          <v-card flat color="transparent">
            <v-card-text class="pa-0">
              <div class="d-flex align-center flex-row-reverse">
                <div class="icon-wrapper mr-2">
                  <v-icon size="32" color="primary">
                    mdi-view-grid-outline
                  </v-icon>
                </div>
                <div class="flex-grow-1">
                  <h1 class="text-h5 text-sm-h4 text-md-h3 font-weight-bold text-primary text-right">
                    {{ t('پنجاه فورم‌ها') }}
                  </h1>
                  <p class="text-body-2 text-sm-body-1 text-grey-darken-1 mt-1 mt-sm-2 text-right">
                    {{
                      t(
                        'لینک‌های سریع برای مدیریت هر فورم. برای فیلتر کردن فورم‌های موجود از جستجو استفاده کنید.'
                      )
                    }}
                  </p>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </header>

        <!-- Toolbar -->
        <v-card class="mb-4 mb-md-6 pa-3 pa-sm-4" elevation="0" rounded="lg">
          <div class="d-flex align-center justify-space-between flex-wrap">
            <!-- Search Section -->
            <v-text-field
              v-model="q"
              density="compact"
              :placeholder="t('جستجوی فورم‌ها...')"
              append-inner-icon="mdi-magnify"
              variant="outlined"
              hide-details
              class="search-field"
              style="min-width: 200px; max-width: 400px"
              clearable
            />
            
            <!-- Results Counter -->
            <div class="text-caption text-sm-body-2 text-grey-darken-1 mt-2 mt-sm-0">
              {{
                t('نمایش {showing} از {total} فورم', {
                  showing: filtered.length,
                  total: original.length,
                })
              }}
              <span v-if="q">
                {{ t('برای «{query}»', { query: q }) }}
              </span>
            </div>
          </div>
        </v-card>

        <!-- Grid of module cards (permission-guarded) -->
        <v-row dense class="ma-0">
          <v-col
            v-for="item in filtered"
            :key="item.path"
            cols="12"
            sm="6"
            md="4"
            lg="3"
            xl="2"
            class="pa-1 pa-sm-2"
          >
            <router-link :to="toPath(item)" style="text-decoration: none">
              <v-card
                class="module-card fill-height"
                elevation="2"
                hover
              >
                <v-card-text class="pa-3 pa-sm-4">
                  <div class="d-flex align-center gap-3 flex-row-reverse">
                    <!-- Icon -->
                    <div class="icon-wrapper flex-shrink-0">
                      <v-icon
                        :size="smAndUp ? 32 : 28"
                        color="primary"
                        class="module-icon"
                      >
                        {{ getMaterialIcon(item.icon) }}
                      </v-icon>
                    </div>
                    <!-- Label -->
                    <div class="flex-grow-1 min-w-0">
                      <h3
                        class="text-body-2 text-sm-body-1 font-weight-bold line-clamp-2 mb-0 text-right"
                      >
                        {{ t(item.labelFa) }}
                      </h3>
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
            <h3 class="text-h6 text-sm-h5 text-grey-darken-2 mb-1 mb-sm-2">
              {{ t('هیچ فورمی پیدا نشد') }}
            </h3>
            <p class="text-body-2 text-sm-body-1 text-grey">
              {{
                t(
                  'برای «{query}» هیچ نتیجه‌ای پیدا نشد. لطفاً واژه‌های جستجو را تغییر بدهید.',
                  { query: q }
                )
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
  import { useLoginRepository } from '@/stores/LoginRepository';

  const auth = useLoginRepository();
  const { t } = useI18n();
  const { smAndUp } = useDisplay();

  const q = ref('');

  const APP_BASE = '/app';
  const toPath = (item) => `${APP_BASE}${item.path}`;

  function canView(item) {
    const perm = item?.perm ? `${item.perm}.view` : null;
    if (!perm || typeof auth?.can !== 'function') return true;
    return auth.can(perm);
  }

  // 🔹 Module data — Dari labels as keys for t()
  const original = ref([
    // Base
    {
      labelFa: 'فورم‌های فساد',
      path: '/fasads',
      icon: '🧩',
      perm: 'fasads',
    },
    {
      labelFa: 'فورم‌های فساد ساعین',
      path: '/saeenfasad',
      icon: '🧠',
      perm: 'saeen_fasads',
    },
    {
      labelFa: 'فورم‌های سرقت',
      path: '/heist',
      icon: '🐱‍👤',
      perm: 'heists',
    },
    {
      labelFa: 'فورم‌های بندی‌ها',
      path: '/captives',
      icon: '🔒',
      perm: 'captives',
    },
    {
      labelFa: 'فورم‌های ملک‌ها',
      path: '/mulkanos',
      icon: '🏠',
      perm: 'mulkanos',
    },
    {
      labelFa: 'فورم‌های دخول/خروج',
      path: '/entry-exits',
      icon: '➡️',
      perm: 'entry_exits',
    },
    {
      labelFa: 'فورم‌های صرافی‌ها',
      path: '/sarafyanos',
      icon: '💱',
      perm: 'sarafyanos',
    },
    {
      labelFa: 'فورم‌های کلب‌ها',
      path: '/clubs',
      icon: '🎯',
      perm: 'clubs',
    },
    {
      labelFa: 'فورم‌های مهاجرت',
      path: '/mahajerats',
      icon: '🧳',
      perm: 'mahajerats',
    },
    {
      labelFa: 'فورم‌های سربازان',
      path: '/soldiers',
      icon: '🪖',
      perm: 'soldiers',
    },

    // One
    {
      labelFa: 'فورم‌های مواد مخدر',
      path: '/mawads',
      icon: '💊',
      perm: 'mawads',
    },
    {
      labelFa: 'فورم‌های مجرمین گمشده',
      path: '/lostmujremeens',
      icon: '🔍',
      perm: 'lost_mujremeens',
    },
    {
      labelFa: 'فورم‌های خانه‌های سربازان قدیمی',
      path: '/oldsoldiershomes',
      icon: '🏚️',
      perm: 'old_soldiers_homes',
    },
    {
      labelFa: 'فورم‌های دزدان',
      path: '/robbers',
      icon: '💰',
      perm: 'robbers',
    },
    {
      labelFa: 'فورم‌های افراد مسلح غیرمسؤول',
      path: '/nonresponsablepeople',
      icon: '⚠️',
      perm: 'non_responsable_people',
    },
    {
      labelFa: 'فورم‌های خانه‌های مشکوک',
      path: '/mashkokhomes',
      icon: '🏘️',
      perm: 'mashkok_homes',
    },
    {
      labelFa: 'فورم‌های ساحات آسیب‌دیده',
      path: '/damageplaces',
      icon: '🏚️',
      perm: 'damage_places',
    },
    {
      labelFa: 'فورم‌های ساحات رسانندهٔ آسیب',
      path: '/damagerasanplaces',
      icon: '💥',
      perm: 'damage_rasan_places',
    },
    {
      labelFa: 'فورم‌های دکان‌های مشکوک',
      path: '/mashkokshops',
      icon: '🏪',
      perm: 'mashkok_shops',
    },
    {
      labelFa: 'فورم‌های لوچک‌ها',
      path: '/luchakanos',
      icon: '👤',
      perm: 'luchakanos',
    },

    // Two
    {
      labelFa: 'فورم‌های مکتب‌ها',
      path: '/schools',
      icon: '🏫',
      perm: 'schools',
    },
    {
      labelFa: 'فورم‌های ایستگاه‌های بس',
      path: '/hadobusstops',
      icon: '🚏',
      perm: 'hado_bus_stops',
    },
    {
      labelFa: 'فورم‌های شرکت‌های ترانسپورتی',
      path: '/transportcompanies',
      icon: '🚚',
      perm: 'transport_companies',
    },
    {
      labelFa: 'فورم‌های کمپنی‌های فروش موتر',
      path: '/carsalescompanies',
      icon: '🚗',
      perm: 'car_sales_companies',
    },
    {
      labelFa: 'فورم‌های پارکینگ',
      path: '/parkings',
      icon: '🅿️',
      perm: 'parkings',
    },
    {
      labelFa: 'فورم‌های بانک‌ها',
      path: '/banks',
      icon: '🏦',
      perm: 'banks',
    },
    {
      labelFa: 'فورم‌های آپارتمان‌ها',
      path: '/apartments',
      icon: '🏢',
      perm: 'apartments',
    },
    {
      labelFa: 'فورم‌های رهنمای معاملات',
      path: '/rahnomamamlats',
      icon: '📋',
      perm: 'rahnoma_mamlats',
    },
    {
      labelFa: 'فورم‌های شفاخانه‌ها',
      path: '/hosppitals',
      icon: '🏥',
      perm: 'hosppitals',
    },
    {
      labelFa: 'فورم‌های کارگاه‌ها',
      path: '/workshops',
      icon: '🔧',
      perm: 'work_shops',
    },

    // Three
    {
      labelFa: 'فورم‌های بدماش‌ها',
      path: '/badmashanos',
      icon: '✊',
      perm: 'badmashanos',
    },
    {
      labelFa: 'فورم‌های سرای‌ها',
      path: '/sarayanos',
      icon: '🏭',
      perm: 'sarayanos',
    },
    {
      labelFa: 'فورم‌های هوتل‌ها',
      path: '/hotels',
      icon: '🏨',
      perm: 'hotels',
    },
    {
      labelFa: 'فورم‌های هوسٹل‌ها',
      path: '/hostels',
      icon: '🛏️',
      perm: 'hostels',
    },
    {
      labelFa: 'فورم‌های تاجران',
      path: '/tujarans',
      icon: '👔',
      perm: 'tujarans',
    },
    {
      labelFa: 'فورم‌های مجاهدین',
      path: '/mujahedinos',
      icon: '🕌',
      perm: 'mujahedinos',
    },
    {
      labelFa: 'فورم‌های دفاتر امارت',
      path: '/emaratoffices',
      icon: '🏛️',
      perm: 'emarat_offices',
    },
    {
      labelFa: 'فورم‌های قومندانان جهادی قدیمی',
      path: '/oldjahadis',
      icon: '🎖️',
      perm: 'old_jahadis',
    },
    {
      labelFa: 'فورم‌های افراد مشکوک',
      path: '/mashkokpeoples',
      icon: '🕵️‍♂️',
      perm: 'mashkok_people',
    },
    {
      labelFa: 'فورم‌های مدارس دینی',
      path: '/madrasas',
      icon: '📖',
      perm: 'madrasas',
    },

    // Four
    {
      labelFa: 'فورم‌های وکیلان',
      path: '/wakilanos',
      icon: '⚖️',
      perm: 'wakilanos',
    },
    {
      labelFa: 'فورم‌های پوهنتون‌ها',
      path: '/universities',
      icon: '🎓',
      perm: 'universities',
    },
    {
      labelFa: 'فورم‌های مسجدها',
      path: '/masjids',
      icon: '🕌',
      perm: 'masjids',
    },
    {
      labelFa: 'فورم‌های شاهراه‌ها',
      path: '/highways',
      icon: '🛣️',
      perm: 'highways',
    },
    {
      labelFa: 'فورم‌های شرکت‌ها',
      path: '/companies',
      icon: '🏢',
      perm: 'companies',
    },
    {
      labelFa: 'فورم‌های ساحات دزدی شده',
      path: '/donelostplaces',
      icon: '🏚️',
      perm: 'donelostplaces',
    },
    {
      labelFa: 'فورم‌های پروژه‌های اقتصادی',
      path: '/econemyprojects',
      icon: '📊',
      perm: 'econemyprojects',
    },
    {
      labelFa: 'فورم‌های ساحات دستگیری مجرمین',
      path: '/criminalcaptureplaces',
      icon: '🚔',
      perm: 'criminalcaptureplaces',
    },
    {
      labelFa: 'فورم‌های پمپ‌ها',
      path: '/pumps',
      icon: '⛽',
      perm: 'pumps',
    },
    {
      labelFa: 'فورم‌های شوراها',
      path: '/shoras',
      icon: '👥',
      perm: 'shoras',
    },
    {
      labelFa: 'فورم‌های چک‌پوینت‌ها',
      path: '/checkpoints',
      icon: '🛂',
      perm: 'checkpoints',
    },
    {
      labelFa: 'فورم‌های اماکن حکومتی',
      path: '/governmentplaces',
      icon: '🏛️',
      perm: 'governmentplaces',
    },
    {
      labelFa: 'فورم‌های سوپرمارکت‌ها',
      path: '/supermarkets',
      icon: '🛒',
      perm: 'supermarkets',
    },
    {
      labelFa: 'فورم‌های پارک‌ها',
      path: '/parks',
      icon: '🌳',
      perm: 'parks',
    },
    {
      labelFa: 'فورم‌های مجتمع‌ها',
      path: '/complexes',
      icon: '🏬',
      perm: 'complexes',
    },
    {
      labelFa: 'فورم‌های اماکن دیپلوماتیک',
      path: '/deplomaticplaces',
      icon: '🇺🇳',
      perm: 'deplomaticplaces',
    },
  ]);

  const items = ref([...original.value]);

  const visibleItems = computed(() => items.value.filter(canView));

  const filtered = computed(() => {
    const term = q.value?.trim().toLowerCase() || '';
    const base = visibleItems.value;
    if (!term) return base;
    return base.filter(
      (i) =>
        i.labelFa.toLowerCase().includes(term) ||
        i.path.toLowerCase().includes(term)
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
      '👊': 'mdi-fist',
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

  onMounted(() => {
    document.documentElement.setAttribute('dir', 'rtl');
    document.documentElement.setAttribute('lang', 'fa');
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
</style>