<!-- src/views/fifthyFormats/FiftyFormatsRoute.vue -->
<template>
  <v-app :dir="direction">
    <div class="min-h-screen bg-grey-lighten-4">
      <!-- Container -->
      <section class="mx-auto px-4 py-8" style="max-width: 1400px">
        <!-- Header -->
        <header class="mb-6">
          <v-card flat color="transparent">
            <v-card-text class="pa-0">
              <h1
                class="text-h4 font-weight-bold text-primary"
                :class="direction === 'rtl' ? 'text-right' : 'text-left'"
              >
                {{ direction === 'rtl' ? t(' پنجاه فارمته') : t('Fifty Formats') }}
              </h1>
              <p
                class="text-body-1 text-grey-darken-1 mt-2"
                :class="direction === 'rtl' ? 'text-right' : 'text-left'"
              >
                {{
                  direction === 'rtl'
                    ? t(
                        '.لینک‌های سریع برای مدیریت هر ماژول. از جستجو برای فیلتر کردن ماژول‌های موجود استفاده کنید'
                      )
                    : t(
                        'Quick links to manage each module. Use search to filter available modules.'
                      )
                }}
              </p>
            </v-card-text>
          </v-card>
        </header>

        <!-- Toolbar -->
        <v-card class="mb-6 pa-4" elevation="0" rounded="xl">
          <v-row align="center" class="ma-0">
            <!-- Search Section -->
            <v-col cols="12" md="6" class="py-0">
              <div
                class="d-flex align-center gap-2"
                :class="direction === 'rtl' ? 'flex-row-reverse justify-start' : 'justify-start'"
              >
                <v-text-field
                  v-model="q"
                  density="compact"
                  :placeholder="
                    direction === 'rtl' ? t('جستجوی ماژول‌ها...') : t('Search modules...')
                  "
                  :prepend-inner-icon="direction === 'rtl' ? undefined : 'mdi-magnify'"
                  :append-inner-icon="direction === 'rtl' ? 'mdi-magnify' : undefined"
                  variant="outlined"
                  hide-details
                  class="flex-grow-1"
                  style="max-width: 400px"
                  clearable
                ></v-text-field>
                <v-btn
                  variant="outlined"
                  color="secondary"
                  @click="q = ''"
                  :prepend-icon="direction === 'rtl' ? undefined : 'mdi-autorenew'"
                  :append-icon="direction === 'rtl' ? 'mdi-autorenew' : undefined"
                >
                  {{ direction === 'rtl' ? t('پاک کردن') : t('Clear') }}
                </v-btn>
              </div>
            </v-col>

            <!-- Actions Section -->
            <v-col cols="12" md="6" class="py-0">
              <div
                class="d-flex align-center gap-2"
                :class="direction === 'rtl' ? 'flex-row-reverse justify-start' : 'justify-end'"
              >
                <v-btn
                  variant="outlined"
                  color="primary"
                  @click="shuffle"
                  :prepend-icon="direction === 'rtl' ? undefined : 'mdi-shuffle-variant'"
                  :append-icon="direction === 'rtl' ? 'mdi-shuffle-variant' : undefined"
                >
                  {{ direction === 'rtl' ? t('تصادفی سازی') : t('Randomize') }}
                </v-btn>
                <v-btn
                  variant="outlined"
                  color="warning"
                  @click="resetOrder"
                  :prepend-icon="direction === 'rtl' ? undefined : 'mdi-restore'"
                  :append-icon="direction === 'rtl' ? 'mdi-restore' : undefined"
                >
                  {{ direction === 'rtl' ? t('بازنشانی ترتیب') : t('Reset Order') }}
                </v-btn>
              </div>
            </v-col>
          </v-row>
        </v-card>

        <!-- Grid of module cards -->
        <v-row dense class="ma-0">
          <v-col
            v-for="item in filtered"
            :key="item.to"
            cols="12"
            sm="6"
            md="4"
            lg="3"
            xl="2"
            class="pa-2"
          >
            <router-link :to="item.to" style="text-decoration: none">
              <v-card
                class="module-card fill-height"
                elevation="2"
                hover
                :color="getCardColor(item)"
              >
                <v-card-text class="pa-3">
                  <div
                    class="d-flex align-center gap-3"
                    :class="direction === 'rtl' ? 'flex-row-reverse' : ''"
                  >
                    <!-- Icon -->
                    <div class="icon-wrapper flex-shrink-0">
                      <v-icon size="28" :color="getIconColor(item)" class="module-icon">
                        {{ getMaterialIcon(item.icon) }}
                      </v-icon>
                    </div>

                    <!-- Label and Badge -->
                    <div class="flex-grow-1 min-w-0">
                      <h3
                        class="text-body-1 font-weight-bold line-clamp-2 mb-0"
                        :class="direction === 'rtl' ? 'text-right' : 'text-left'"
                        :style="{ color: getTextColor(item) }"
                      >
                        {{ direction === 'rtl' ? t(item.dariLabel) : t(item.label) }}
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
          class="mt-10 text-center pa-8"
          elevation="2"
          color="grey-lighten-3"
        >
          <v-card-text>
            <v-icon size="64" color="grey" class="mb-4">mdi-magnify-remove</v-icon>
            <h3 class="text-h5 text-grey-darken-2 mb-2">
              {{ direction === 'rtl' ? t('ماژولی یافت نشد') : t('No modules found') }}
            </h3>
            <p class="text-body-1 text-grey">
              {{
                direction === 'rtl'
                  ? t('نتیجه‌ای برای "{query}" یافت نشد. عبارت جستجو را تغییر دهید.', { query: q })
                  : t('No results found for "{query}". Try adjusting your search terms.', {
                      query: q,
                    })
              }}
            </p>
            <v-btn
              class="mt-4"
              color="primary"
              @click="q = ''"
              :prepend-icon="direction === 'rtl' ? undefined : 'mdi-backspace'"
              :append-icon="direction === 'rtl' ? 'mdi-backspace' : undefined"
            >
              {{ direction === 'rtl' ? t('پاک کردن جستجو') : t('Clear Search') }}
            </v-btn>
          </v-card-text>
        </v-card>

        <!-- Results counter -->
        <v-card v-if="filtered.length > 0" class="mt-4" variant="flat" color="transparent">
          <v-card-text class="text-center pa-2">
            <span class="text-body-2 text-grey">
              {{
                direction === 'rtl'
                  ? t('نمایش {showing} از {total} ماژول', {
                      showing: filtered.length,
                      total: original.length,
                    })
                  : t('Showing {showing} of {total} modules', {
                      showing: filtered.length,
                      total: original.length,
                    })
              }}
              <span v-if="q">
                {{
                  direction === 'rtl'
                    ? t('برای "{query}"', { query: q })
                    : t('for "{query}"', { query: q })
                }}
              </span>
            </span>
          </v-card-text>
        </v-card>

        <!-- Language/Layout Toggle -->
        <v-card class="mt-4" variant="flat" color="transparent">
          <v-card-actions class="justify-center">
            <v-btn
              variant="outlined"
              color="primary"
              @click="toggleDirection"
              :prepend-icon="
                direction === 'rtl' ? 'mdi-format-align-right' : 'mdi-format-align-left'
              "
            >
              {{ direction === 'rtl' ? t('Switch to LTR') : t('Switch to RTL') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </section>
    </div>
  </v-app>
</template>

<script setup>
  import { ref, computed, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { t, locale } = useI18n();
  const q = ref('');
  const direction = ref('rtl'); // Default to RTL for Dari

  // Enhanced module data with better colors and icons
  const original = ref([
    {
      label: 'Fasad',
      dariLabel: 'فساد',
      to: '/fasads',
      path: '/fasads',
      icon: '🧩',
      color: 'blue-lighten-3',
      materialIcon: 'mdi-puzzle',
    },
    {
      label: 'Saeen Fasad',
      dariLabel: 'فساد ثانی',
      to: '/saeenfasad',
      path: '/saeenfasad',
      icon: '🧠',
      color: 'deep-purple-lighten-3',
      materialIcon: 'mdi-brain',
    },
    {
      label: 'Heist',
      dariLabel: 'سرقت',
      to: '/heist',
      path: '/heist',
      icon: '🕵️',
      color: 'red-lighten-3',
      materialIcon: 'mdi-spy',
    },
    {
      label: 'Captive',
      dariLabel: 'اسیر',
      to: '/captives',
      path: '/captives',
      icon: '🔒',
      color: 'orange-lighten-3',
      materialIcon: 'mdi-lock',
    },
    {
      label: 'Mulkano',
      dariLabel: 'ملک‌ها',
      to: '/mulkanos',
      path: '/mulkanos',
      icon: '🏠',
      color: 'green-lighten-3',
      materialIcon: 'mdi-home',
    },
    {
      label: 'Entry/Exit',
      dariLabel: 'ورود/خروج',
      to: '/entry-exits',
      path: '/entry-exits',
      icon: '➡️',
      color: 'cyan-lighten-3',
      materialIcon: 'mdi-export',
    },
    {
      label: 'Sarafyano',
      dariLabel: 'صرافی‌ها',
      to: '/sarafyanos',
      path: '/sarafyanos',
      icon: '💱',
      color: 'amber-lighten-3',
      materialIcon: 'mdi-currency-usd',
    },
    {
      label: 'Clubs',
      dariLabel: 'کلب‌ها',
      to: '/clubs',
      path: '/clubs',
      icon: '🎯',
      color: 'pink-lighten-3',
      materialIcon: 'mdi-target',
    },
    {
      label: 'Mahajerat',
      dariLabel: 'مهاجرت‌ها',
      to: '/mahajerats',
      path: '/mahajerats',
      icon: '🧳',
      color: 'teal-lighten-3',
      materialIcon: 'mdi-briefcase',
    },
    {
      label: 'Soldier',
      dariLabel: 'سرباز',
      to: '/soldiers',
      path: '/soldiers',
      icon: '🪖',
      color: 'brown-lighten-3',
      materialIcon: 'mdi-shield-account',
    },
    {
      label: 'Mawads',
      dariLabel: 'مخدره مواد',
      to: '/mawads',
      path: '/mawads',
      icon: '💊',
      color: 'deep-orange-lighten-3',
      materialIcon: 'mdi-pill',
    },
    {
      label: 'Lost Mujremeen',
      dariLabel: ' مجرم های نادرک',
      to: '/lostmujremeens',
      path: '/lostmujremeens',
      icon: '🔍',
      color: 'indigo-lighten-3',
      materialIcon: 'mdi-magnify',
    },
    {
      label: 'Old Soldiers Homes',
      dariLabel: 'خانه های نظامیان بیشین',
      to: '/oldsoldiershomes',
      path: '/oldsoldiershomes',
      icon: '🏚️',
      color: 'blue-grey-lighten-3',
      materialIcon: 'mdi-home',
    },
    {
      label: 'Robbers',
      dariLabel: 'اختطاف گرانو فارمت',
      to: '/robbers',
      path: '/robbers',
      icon: '💰',
      color: 'lime-lighten-3',
      materialIcon: 'mdi-cash',
    },
    {
      label: 'None Resposable People',
      dariLabel: 'فارمت سلاح دار های غیر مسول',
      to: '/nonresponsablepeople',
      path: '/nonresponsablepeople',
      icon: '⚠️',
      color: 'yellow-lighten-3',
      materialIcon: 'mdi-alert',
    },
    {
      label: 'Mashkok Homes',
      dariLabel: 'فارمت خانه های مشکوک',
      to: '/mashkokhomes',
      path: '/mashkokhomes',
      icon: '🏘️',
      color: 'purple-lighten-3',
      materialIcon: 'mdi-home-group',
    },
    {
      label: 'Damage PLaces',
      dariLabel: 'جا های آسیب بزیر فارمت',
      to: '/damageplaces',
      path: '/damageplaces',
      icon: '🏚️',
      color: 'red-lighten-2',
      materialIcon: 'mdi-home-alert',
    },
    {
      label: 'Damage Rasan PLaces',
      dariLabel: 'جا های آسیب رسان فارمت',
      to: '/damagerasanplaces',
      path: '/damagerasanplaces',
      icon: '💥',
      color: 'deep-orange-lighten-2',
      materialIcon: 'mdi-bomb',
    },
    {
      label: 'Mashkok Shops',
      dariLabel: 'فارمت دوکان های مشکوک',
      to: '/mashkokshops',
      path: '/mashkokshops',
      icon: '🏪',
      color: 'amber-lighten-2',
      materialIcon: 'mdi-store',
    },
    {
      label: 'Luchakano',
      dariLabel: 'فارمت لوچک ها و بی بند و بار ها',
      to: '/luchakanos',
      path: '/luchakanos',
      icon: '👤',
      color: 'pink-lighten-2',
      materialIcon: 'mdi-account',
    },
    {
      label: 'Schools',
      dariLabel: 'فامت مکاتب و مراکز علمی',
      to: '/schools',
      path: '/schools',
      icon: '🏫',
      color: 'light-blue-lighten-3',
      materialIcon: 'mdi-school',
    },
    {
      label: 'Hado Bus Stops Format',
      dariLabel: ' فامت ایستگاه ها',
      to: '/hadobusstops',
      path: '/hadobusstops',
      icon: '🚏',
      color: 'cyan-lighten-2',
      materialIcon: 'mdi-bus-stop',
    },
    {
      label: 'Transport Company',
      dariLabel: 'فارمت شرکت های ترانسبورتی',
      to: '/transportcompanies',
      path: '/transportcompanies',
      icon: '🚚',
      color: 'teal-lighten-2',
      materialIcon: 'mdi-truck',
    },
    {
      label: 'Car Sales Companies',
      dariLabel: 'فارمت موتر فروشی ها',
      to: '/carsalescompanies',
      path: '/carsalescompanies',
      icon: '🚗',
      color: 'green-lighten-2',
      materialIcon: 'mdi-car',
    },
    {
      label: 'Parking Formats',
      dariLabel: 'فارمت پارکینګ ها ',
      to: '/parkings',
      path: '/parkings',
      icon: '🅿️',
      color: 'blue-lighten-2',
      materialIcon: 'mdi-parking',
    },
    {
      label: 'Banks Formats',
      dariLabel: 'فارمت بانک ها ',
      to: '/banks',
      path: '/banks',
      icon: '🏦',
      color: 'indigo-lighten-2',
      materialIcon: 'mdi-bank',
    },
    {
      label: 'Apartments Formats',
      dariLabel: 'فارمت آبارتمنت  ها ',
      to: '/apartments',
      path: '/apartments',
      icon: '🏢',
      color: 'purple-lighten-2',
      materialIcon: 'mdi-office-building',
    },
    {
      label: 'Rahnoma Mamlat Formats',
      dariLabel: 'فارمت رهنمای معاملات ها ',
      to: '/rahnomamamlats',
      path: '/rahnomamamlats',
      icon: '📋',
      color: 'deep-purple-lighten-2',
      materialIcon: 'mdi-clipboard-text',
    },
    {
      label: 'Hosppitals Formats',
      dariLabel: 'فارمت بیمارستان ها ',
      to: '/hosppitals',
      path: '/hosppitals',
      icon: '🏥',
      color: 'red-lighten-1',
      materialIcon: 'mdi-hospital',
    },
    {
      label: 'Work Shops Formats',
      dariLabel: 'فارمت مستری خانه ها ',
      to: '/workshops',
      path: '/workshops',
      icon: '🔧',
      color: 'orange-lighten-2',
      materialIcon: 'mdi-wrench',
    },
    {
      label: 'Badmashano ',
      dariLabel: 'فارمت بدماشه ها و زور دار ها ',
      to: '/badmashanos',
      path: '/badmashanos',
      icon: '👊',
      color: 'deep-orange-lighten-2',
      materialIcon: 'mdi-fist',
    },
    {
      label: 'Sarayono ',
      dariLabel: 'فارمت سرای ها و گدام ها ',
      to: '/sarayanos',
      path: '/sarayanos',
      icon: '🏭',
      color: 'brown-lighten-2',
      materialIcon: 'mdi-warehouse',
    },
    {
      label: 'Hotels ',
      dariLabel: 'فارمت مسافرخانه ها و هوتل ها و رستورانت ها ',
      to: '/hotels',
      path: '/hotels',
      icon: '🏨',
      color: 'pink-lighten-2',
      materialIcon: 'mdi-hotel',
    },
    {
      label: 'Hostels ',
      dariLabel: 'فارمت لیله ها و اطاق ها کرایی',
      to: '/hostels',
      path: '/hostels',
      icon: '🛏️',
      color: 'light-blue-lighten-2',
      materialIcon: 'mdi-bed',
    },
    {
      label: 'Tujarans ',
      dariLabel: 'فارمت علما تجارها متنفذین ',
      to: '/tujarans',
      path: '/tujarans',
      icon: '👔',
      color: 'cyan-lighten-2',
      materialIcon: 'mdi-tie',
    },
    {
      label: 'Mujahedinos ',
      dariLabel: 'فارمت مجاهدین',
      to: '/mujahedinos',
      path: '/mujahedinos',
      icon: '🕌',
      color: 'green-lighten-1',
      materialIcon: 'mdi-mosque',
    },
    {
      label: 'Emaratoffices ',
      dariLabel: 'فارمت دوایر مهمان خانه ها و خانه های مسولین مربوط به امارت اسلامی',
      to: '/emaratoffices',
      path: '/emaratoffices',
      icon: '🏛️',
      color: 'amber-lighten-1',
      materialIcon: 'mdi-government',
    },
    {
      label: 'oldjahadis ',
      dariLabel: 'فارمت قومندان های سابقه جهادی',
      to: '/oldjahadis',
      path: '/oldjahadis',
      icon: '🎖️',
      color: 'orange-lighten-1',
      materialIcon: 'mdi-medal',
    },
    {
      label: 'mashkokpeoples ',
      dariLabel: 'فارمت افراد مشکوک',
      to: '/mashkokpeoples',
      path: '/mashkokpeoples',
      icon: '🕵️‍♂️',
      color: 'purple-lighten-2',
      materialIcon: 'mdi-account-question',
    },
    {
      label: 'madrasas ',
      dariLabel: 'فارمت مدرسه های دینی',
      to: '/madrasas',
      path: '/madrasas',
      icon: '📖',
      color: 'teal-lighten-1',
      materialIcon: 'mdi-book-open-variant',
    },
  ]);

  const items = ref([...original.value]);

  const filtered = computed(() => {
    const term = q.value?.trim().toLowerCase() || '';
    if (!term) return items.value;

    return items.value.filter(
      (i) =>
        i.label.toLowerCase().includes(term) ||
        t(i.dariLabel).toLowerCase().includes(term) ||
        (i.dariLabel && i.dariLabel.toLowerCase().includes(term)) ||
        i.path.toLowerCase().includes(term)
    );
  });

  // Helper functions for dynamic styling
  function getCardColor(item) {
    return item.color || 'white';
  }

  function getIconColor(item) {
    // Map color names to appropriate icon colors
    const colorMap = {
      'blue-lighten-3': 'blue-darken-3',
      'deep-purple-lighten-3': 'deep-purple-darken-3',
      'red-lighten-3': 'red-darken-3',
      'orange-lighten-3': 'orange-darken-3',
      'green-lighten-3': 'green-darken-3',
      'cyan-lighten-3': 'cyan-darken-3',
      'amber-lighten-3': 'amber-darken-3',
      'pink-lighten-3': 'pink-darken-3',
      'teal-lighten-3': 'teal-darken-3',
      'brown-lighten-3': 'brown-darken-3',
      'deep-orange-lighten-3': 'deep-orange-darken-3',
      'indigo-lighten-3': 'indigo-darken-3',
      'blue-grey-lighten-3': 'blue-grey-darken-3',
      'lime-lighten-3': 'lime-darken-3',
      'yellow-lighten-3': 'yellow-darken-3',
      'purple-lighten-3': 'purple-darken-3',
      'red-lighten-2': 'red-darken-4',
      'deep-orange-lighten-2': 'deep-orange-darken-4',
      'amber-lighten-2': 'amber-darken-4',
      'pink-lighten-2': 'pink-darken-4',
      'light-blue-lighten-3': 'light-blue-darken-3',
      'cyan-lighten-2': 'cyan-darken-4',
      'teal-lighten-2': 'teal-darken-4',
      'green-lighten-2': 'green-darken-4',
      'blue-lighten-2': 'blue-darken-4',
      'indigo-lighten-2': 'indigo-darken-4',
      'purple-lighten-2': 'purple-darken-4',
      'deep-purple-lighten-2': 'deep-purple-darken-4',
      'red-lighten-1': 'red-darken-4',
      'orange-lighten-2': 'orange-darken-4',
      'deep-orange-lighten-2': 'deep-orange-darken-4',
      'brown-lighten-2': 'brown-darken-4',
      'pink-lighten-2': 'pink-darken-4',
      'light-blue-lighten-2': 'light-blue-darken-4',
      'cyan-lighten-2': 'cyan-darken-4',
      'green-lighten-1': 'green-darken-4',
      'amber-lighten-1': 'amber-darken-4',
      'orange-lighten-1': 'orange-darken-4',
      'purple-lighten-2': 'purple-darken-4',
      'teal-lighten-1': 'teal-darken-4',
    };
    return colorMap[item.color] || 'primary';
  }

  function getTextColor(item) {
    // Return dark text color for better readability
    const textColorMap = {
      'blue-lighten-3': 'blue-darken-4',
      'deep-purple-lighten-3': 'deep-purple-darken-4',
      'red-lighten-3': 'red-darken-4',
      'orange-lighten-3': 'orange-darken-4',
      'green-lighten-3': 'green-darken-4',
      'cyan-lighten-3': 'cyan-darken-4',
      'amber-lighten-3': 'amber-darken-4',
      'pink-lighten-3': 'pink-darken-4',
      'teal-lighten-3': 'teal-darken-4',
      'brown-lighten-3': 'brown-darken-4',
      'deep-orange-lighten-3': 'deep-orange-darken-4',
      'indigo-lighten-3': 'indigo-darken-4',
      'blue-grey-lighten-3': 'blue-grey-darken-4',
      'lime-lighten-3': 'lime-darken-4',
      'yellow-lighten-3': 'yellow-darken-4',
      'purple-lighten-3': 'purple-darken-4',
      'red-lighten-2': 'red-darken-4',
      'deep-orange-lighten-2': 'deep-orange-darken-4',
      'amber-lighten-2': 'amber-darken-4',
      'pink-lighten-2': 'pink-darken-4',
      'light-blue-lighten-3': 'light-blue-darken-4',
      'cyan-lighten-2': 'cyan-darken-4',
      'teal-lighten-2': 'teal-darken-4',
      'green-lighten-2': 'green-darken-4',
      'blue-lighten-2': 'blue-darken-4',
      'indigo-lighten-2': 'indigo-darken-4',
      'purple-lighten-2': 'purple-darken-4',
      'deep-purple-lighten-2': 'deep-purple-darken-4',
      'red-lighten-1': 'red-darken-4',
      'orange-lighten-2': 'orange-darken-4',
      'deep-orange-lighten-2': 'deep-orange-darken-4',
      'brown-lighten-2': 'brown-darken-4',
      'pink-lighten-2': 'pink-darken-4',
      'light-blue-lighten-2': 'light-blue-darken-4',
      'cyan-lighten-2': 'cyan-darken-4',
      'green-lighten-1': 'green-darken-4',
      'amber-lighten-1': 'amber-darken-4',
      'orange-lighten-1': 'orange-darken-4',
      'purple-lighten-2': 'purple-darken-4',
      'teal-lighten-1': 'teal-darken-4',
    };
    return textColorMap[item.color] || 'primary';
  }

  function getMaterialIcon(emojiIcon) {
    const iconMap = {
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
      '🏢': 'mdi-office-building',
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
    };
    return iconMap[emojiIcon] || 'mdi-cube';
  }

  function shuffle() {
    items.value = [...items.value].sort(() => Math.random() - 0.5);
  }

  function resetOrder() {
    items.value = [...original.value];
  }

  function toggleDirection() {
    direction.value = direction.value === 'rtl' ? 'ltr' : 'rtl';
    // Also update the HTML dir attribute and lang
    document.documentElement.setAttribute('dir', direction.value);
    document.documentElement.setAttribute('lang', direction.value === 'rtl' ? 'fa' : 'en');
  }

  // Set initial direction on mount
  onMounted(() => {
    document.documentElement.setAttribute('dir', direction.value);
    document.documentElement.setAttribute('lang', direction.value === 'rtl' ? 'fa' : 'en');
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
  }

  .module-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
  }

  .icon-wrapper {
    transition: transform 0.3s ease;
    flex-shrink: 0;
  }

  .module-card:hover .icon-wrapper {
    transform: scale(1.1);
  }

  .gap-2 {
    gap: 8px;
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

  /* Text alignment for RTL/LTR */
  .text-right {
    text-align: right;
  }

  .text-left {
    text-align: left;
  }

  /* Ensure proper RTL layout for flex containers */
  [dir='rtl'] .flex-row-reverse {
    flex-direction: row-reverse;
  }

  /* Fix toolbar alignment */
  [dir='rtl'] .justify-start {
    justify-content: flex-start !important;
  }

  [dir='ltr'] .justify-start {
    justify-content: flex-start !important;
  }

  [dir='rtl'] .justify-end {
    justify-content: flex-end !important;
  }

  [dir='ltr'] .justify-end {
    justify-content: flex-end !important;
  }

  /* Larger text for better readability */
  .text-body-1 {
    font-size: 1rem !important;
    font-weight: 600 !important;
  }
</style>