<!-- src/views/fifthyFormats/FiftyFormatsRoute.vue -->
<template>
  <v-app :dir="direction">
    <div class="min-h-screen bg-grey-lighten-4">
      <!-- Container -->
      <section class="mx-auto px-3 px-sm-4 py-6 py-md-8" style="max-width: 1400px">
        <!-- Header -->
        <header class="mb-4 mb-md-6">
          <v-card flat color="transparent">
            <v-card-text class="pa-0">
              <h1
                class="text-h5 text-sm-h4 text-md-h3 font-weight-bold text-primary"
                :class="direction === 'rtl' ? 'text-right' : 'text-left'"
              >
                {{ direction === 'rtl' ? t('پنځوس فورمې') : t('Fifty Formats') }}
              </h1>
              <p
                class="text-body-2 text-sm-body-1 text-grey-darken-1 mt-1 mt-sm-2"
                :class="direction === 'rtl' ? 'text-right' : 'text-left'"
              >
                {{
                  direction === 'rtl'
                    ? t(
                        '.د هرې ماډل د مدیریت لپاره چټکې لینکونه. د موجودو ماډلونو د فلټر کولو لپاره د لټون په کارولو سره'
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
        <v-card class="mb-4 mb-md-6 pa-3 pa-sm-4" elevation="0" rounded="xl">
          <v-row align="center" class="ma-0">
            <!-- Search Section -->
            <v-col cols="12" md="6" class="py-0 pb-3 pb-md-0">
              <div
                class="d-flex align-center gap-2 flex-wrap"
                :class="direction === 'rtl' ? 'flex-row-reverse justify-start' : 'justify-start'"
              >
                <v-text-field
                  v-model="q"
                  density="compact"
                  :placeholder="
                    direction === 'rtl' ? t('د ماډلونو لټون...') : t('Search modules...')
                  "
                  :prepend-inner-icon="direction === 'rtl' ? undefined : 'mdi-magnify'"
                  :append-inner-icon="direction === 'rtl' ? 'mdi-magnify' : undefined"
                  variant="outlined"
                  hide-details
                  class="flex-grow-1 search-field"
                  style="min-width: 200px; max-width: 400px"
                  clearable
                ></v-text-field>
                <v-btn
                  variant="outlined"
                  color="secondary"
                  @click="q = ''"
                  size="small"
                  :prepend-icon="direction === 'rtl' ? undefined : 'mdi-autorenew'"
                  :append-icon="direction === 'rtl' ? 'mdi-autorenew' : undefined"
                  class="action-btn"
                >
                  {{ direction === 'rtl' ? t('پاک کول') : t('Clear') }}
                </v-btn>
              </div>
            </v-col>

            <!-- Actions Section -->
            <v-col cols="12" md="6" class="py-0">
              <div
                class="d-flex align-center gap-2 flex-wrap"
                :class="direction === 'rtl' ? 'flex-row-reverse justify-start justify-md-end' : 'justify-start justify-md-end'"
              >
                <v-btn
                  variant="outlined"
                  color="primary"
                  @click="shuffle"
                  size="small"
                  :prepend-icon="direction === 'rtl' ? undefined : 'mdi-shuffle-variant'"
                  :append-icon="direction === 'rtl' ? 'mdi-shuffle-variant' : undefined"
                  class="action-btn"
                >
                  {{ direction === 'rtl' ? t('تصادفي کول') : t('Randomize') }}
                </v-btn>
                <v-btn
                  variant="outlined"
                  color="warning"
                  @click="resetOrder"
                  size="small"
                  :prepend-icon="direction === 'rtl' ? undefined : 'mdi-restore'"
                  :append-icon="direction === 'rtl' ? 'mdi-restore' : undefined"
                  class="action-btn"
                >
                  {{ direction === 'rtl' ? t('بیا ترتیبول') : t('Reset Order') }}
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
            class="pa-1 pa-sm-2"
          >
            <router-link :to="item.to" style="text-decoration: none">
              <v-card
                class="module-card fill-height"
                elevation="2"
                hover
                :color="getCardColor(item)"
              >
                <v-card-text class="pa-2 pa-sm-3">
                  <div
                    class="d-flex align-center gap-2 gap-sm-3"
                    :class="direction === 'rtl' ? 'flex-row-reverse' : ''"
                  >
                    <!-- Icon -->
                    <div class="icon-wrapper flex-shrink-0">
                      <v-icon size="24" :size="smAndUp ? 28 : 24" :color="getIconColor(item)" class="module-icon">
                        {{ getMaterialIcon(item.icon) }}
                      </v-icon>
                    </div>

                    <!-- Label and Badge -->
                    <div class="flex-grow-1 min-w-0">
                      <h3
                        class="text-body-2 text-sm-body-1 font-weight-bold line-clamp-2 mb-0"
                        :class="direction === 'rtl' ? 'text-right' : 'text-left'"
                        :style="{ color: getTextColor(item) }"
                      >
                        {{ direction === 'rtl' ? t(item.pashtoLabel) : t(item.label) }}
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
        >
          <v-card-text>
            <v-icon size="48" :size="smAndUp ? 64 : 48" color="grey" class="mb-2 mb-sm-4">mdi-magnify-remove</v-icon>
            <h3 class="text-h6 text-sm-h5 text-grey-darken-2 mb-1 mb-sm-2">
              {{ direction === 'rtl' ? t('هیڅ ماډل ونه موندل شو') : t('No modules found') }}
            </h3>
            <p class="text-body-2 text-sm-body-1 text-grey">
              {{
                direction === 'rtl'
                  ? t('د "{query}" لپاره هیڅ پایله ونه موندل شوه. خپل د لټون شرایط سم کړئ.', { query: q })
                  : t('No results found for "{query}". Try adjusting your search terms.', {
                      query: q,
                    })
              }}
            </p>
            <v-btn
              class="mt-2 mt-sm-4"
              color="primary"
              size="small"
              @click="q = ''"
              :prepend-icon="direction === 'rtl' ? undefined : 'mdi-backspace'"
              :append-icon="direction === 'rtl' ? 'mdi-backspace' : undefined"
            >
              {{ direction === 'rtl' ? t('د لټون پاکول') : t('Clear Search') }}
            </v-btn>
          </v-card-text>
        </v-card>

        <!-- Results counter -->
        <v-card v-if="filtered.length > 0" class="mt-3 mt-sm-4" variant="flat" color="transparent">
          <v-card-text class="text-center pa-1 pa-sm-2">
            <span class="text-caption text-sm-body-2 text-grey">
              {{
                direction === 'rtl'
                  ? t('د {total} ماډلونو څخه {showing} نمایشیږي', {
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
                    ? t('د "{query}" لپاره', { query: q })
                    : t('for "{query}"', { query: q })
                }}
              </span>
            </span>
          </v-card-text>
        </v-card>

        <!-- Language/Layout Toggle -->
        <v-card class="mt-3 mt-sm-4" variant="flat" color="transparent">
          <v-card-actions class="justify-center">
            <v-btn
              variant="outlined"
              color="primary"
              size="small"
              @click="toggleDirection"
              :prepend-icon="
                direction === 'rtl' ? 'mdi-format-align-right' : 'mdi-format-align-left'
              "
            >
              {{ direction === 'rtl' ? t('LTR ته بدلول') : t('Switch to RTL') }}
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
  import { useDisplay } from 'vuetify';

  const { t, locale } = useI18n();
  const { smAndUp, mdAndUp } = useDisplay();
  const q = ref('');
  const direction = ref('rtl'); // Default to RTL for Pashto

  // Enhanced module data with Pashto labels and better colors and icons
  const original = ref([
    {
      label: 'Fasad Format',
      pashtoLabel: 'فساد فورمې',
      to: '/fasads',
      path: '/fasads',
      icon: '🧩',
      color: 'blue-lighten-3',
      materialIcon: 'mdi-puzzle',
    },
    {
      label: 'Saeen Fasad Format',
      pashtoLabel: 'فساد ثاني فورمې',
      to: '/saeenfasad',
      path: '/saeenfasad',
      icon: '🧠',
      color: 'deep-purple-lighten-3',
      materialIcon: 'mdi-brain',
    },
    {
      label: 'Heist Format',
      pashtoLabel: 'سرقت فورمې',
      to: '/heist',
      path: '/heist',
      icon: '🕵️',
      color: 'red-lighten-3',
      materialIcon: 'mdi-spy',
    },
    {
      label: 'Captive Format',
      pashtoLabel: 'بندي فورمې',
      to: '/captives',
      path: '/captives',
      icon: '🔒',
      color: 'orange-lighten-3',
      materialIcon: 'mdi-lock',
    },
    {
      label: 'Mulkano Format',
      pashtoLabel: 'ملکونو فورمې',
      to: '/mulkanos',
      path: '/mulkanos',
      icon: '🏠',
      color: 'green-lighten-3',
      materialIcon: 'mdi-home',
    },
    {
      label: 'Entry/Exit Format',
      pashtoLabel: 'ننوتل/وتل فورمې',
      to: '/entry-exits',
      path: '/entry-exits',
      icon: '➡️',
      color: 'cyan-lighten-3',
      materialIcon: 'mdi-export',
    },
    {
      label: 'Sarafyano Format',
      pashtoLabel: 'صرفی فورمې',
      to: '/sarafyanos',
      path: '/sarafyanos',
      icon: '💱',
      color: 'amber-lighten-3',
      materialIcon: 'mdi-currency-usd',
    },
    {
      label: 'Clubs Format',
      pashtoLabel: 'کلبونو فورمې',
      to: '/clubs',
      path: '/clubs',
      icon: '🎯',
      color: 'pink-lighten-3',
      materialIcon: 'mdi-target',
    },
    {
      label: 'Mahajerat Format',
      pashtoLabel: 'کډوالۍ فورمې',
      to: '/mahajerats',
      path: '/mahajerats',
      icon: '🧳',
      color: 'teal-lighten-3',
      materialIcon: 'mdi-briefcase',
    },
    {
      label: 'Soldier Format',
      pashtoLabel: 'سرپي فورمې',
      to: '/soldiers',
      path: '/soldiers',
      icon: '🪖',
      color: 'brown-lighten-3',
      materialIcon: 'mdi-shield-account',
    },
    {
      label: 'Mawads Format',
      pashtoLabel: 'مخدره موادو فورمې',
      to: '/mawads',
      path: '/mawads',
      icon: '💊',
      color: 'deep-orange-lighten-3',
      materialIcon: 'mdi-pill',
    },
    {
      label: 'Lost Mujremeen Format',
      pashtoLabel: 'ورک شوي مجرمين فورمې',
      to: '/lostmujremeens',
      path: '/lostmujremeens',
      icon: '🔍',
      color: 'indigo-lighten-3',
      materialIcon: 'mdi-magnify',
    },
    {
      label: 'Old Soldiers Homes Format',
      pashtoLabel: 'زړو سرپيو کورونو فورمې',
      to: '/oldsoldiershomes',
      path: '/oldsoldiershomes',
      icon: '🏚️',
      color: 'blue-grey-lighten-3',
      materialIcon: 'mdi-home',
    },
    {
      label: 'Robbers Format',
      pashtoLabel: 'غلاګرو فورمې',
      to: '/robbers',
      path: '/robbers',
      icon: '💰',
      color: 'lime-lighten-3',
      materialIcon: 'mdi-cash',
    },
    {
      label: 'None Resposable People Format',
      pashtoLabel: 'غير مسول وسله وال فورمې',
      to: '/nonresponsablepeople',
      path: '/nonresponsablepeople',
      icon: '⚠️',
      color: 'yellow-lighten-3',
      materialIcon: 'mdi-alert',
    },
    {
      label: 'Mashkok Homes Format',
      pashtoLabel: 'شبې کورونو فورمې',
      to: '/mashkokhomes',
      path: '/mashkokhomes',
      icon: '🏘️',
      color: 'purple-lighten-3',
      materialIcon: 'mdi-home-group',
    },
    {
      label: 'Damage PLaces Format',
      pashtoLabel: 'زخمي ځايونو فورمې',
      to: '/damageplaces',
      path: '/damageplaces',
      icon: '🏚️',
      color: 'red-lighten-2',
      materialIcon: 'mdi-home-alert',
    },
    {
      label: 'Damage Rasan PLaces Format',
      pashtoLabel: 'زخمي رسونکو ځايونو فورمې',
      to: '/damagerasanplaces',
      path: '/damagerasanplaces',
      icon: '💥',
      color: 'deep-orange-lighten-2',
      materialIcon: 'mdi-bomb',
    },
    {
      label: 'Mashkok Shops Format',
      pashtoLabel: 'شبې پلورنځيو فورمې',
      to: '/mashkokshops',
      path: '/mashkokshops',
      icon: '🏪',
      color: 'amber-lighten-2',
      materialIcon: 'mdi-store',
    },
    {
      label: 'Luchakano Format',
      pashtoLabel: 'لوچکانو فورمې',
      to: '/luchakanos',
      path: '/luchakanos',
      icon: '👤',
      color: 'pink-lighten-2',
      materialIcon: 'mdi-account',
    },
    {
      label: 'Schools Format',
      pashtoLabel: 'ښوونځيو فورمې',
      to: '/schools',
      path: '/schools',
      icon: '🏫',
      color: 'light-blue-lighten-3',
      materialIcon: 'mdi-school',
    },
    {
      label: 'Hado Bus Stops Format',
      pashtoLabel: 'د بس درېدو ځايونو فورمې',
      to: '/hadobusstops',
      path: '/hadobusstops',
      icon: '🚏',
      color: 'cyan-lighten-2',
      materialIcon: 'mdi-bus-stop',
    },
    {
      label: 'Transport Company Format',
      pashtoLabel: 'د ترانسپورټ شرکتونو فورمې',
      to: '/transportcompanies',
      path: '/transportcompanies',
      icon: '🚚',
      color: 'teal-lighten-2',
      materialIcon: 'mdi-truck',
    },
    {
      label: 'Car Sales Companies Format',
      pashtoLabel: 'د موټر پلورنځيو فورمې',
      to: '/carsalescompanies',
      path: '/carsalescompanies',
      icon: '🚗',
      color: 'green-lighten-2',
      materialIcon: 'mdi-car',
    },
    {
      label: 'Parking Formats',
      pashtoLabel: 'د پارکینګ فورمې',
      to: '/parkings',
      path: '/parkings',
      icon: '🅿️',
      color: 'blue-lighten-2',
      materialIcon: 'mdi-parking',
    },
    {
      label: 'Banks Formats',
      pashtoLabel: 'د بانکونو فورمې',
      to: '/banks',
      path: '/banks',
      icon: '🏦',
      color: 'indigo-lighten-2',
      materialIcon: 'mdi-bank',
    },
    {
      label: 'Apartments Formats',
      pashtoLabel: 'د آپارتمانونو فورمې',
      to: '/apartments',
      path: '/apartments',
      icon: '🏢',
      color: 'purple-lighten-2',
      materialIcon: 'mdi-office-building',
    },
    {
      label: 'Rahnoma Mamlat Formats',
      pashtoLabel: 'د رهنمای معاملاتو فورمې',
      to: '/rahnomamamlats',
      path: '/rahnomamamlats',
      icon: '📋',
      color: 'deep-purple-lighten-2',
      materialIcon: 'mdi-clipboard-text',
    },
    {
      label: 'Hosppitals Formats',
      pashtoLabel: 'د روغتونونو فورمې',
      to: '/hosppitals',
      path: '/hosppitals',
      icon: '🏥',
      color: 'red-lighten-1',
      materialIcon: 'mdi-hospital',
    },
    {
      label: 'Work Shops Formats',
      pashtoLabel: 'د کارخونو فورمې',
      to: '/workshops',
      path: '/workshops',
      icon: '🔧',
      color: 'orange-lighten-2',
      materialIcon: 'mdi-wrench',
    },
    {
      label: 'Badmashano Format',
      pashtoLabel: 'بدماشو فورمې',
      to: '/badmashanos',
      path: '/badmashanos',
      icon: '👊',
      color: 'deep-orange-lighten-2',
      materialIcon: 'mdi-fist',
    },
    {
      label: 'Sarayono Format',
      pashtoLabel: 'سرایونو فورمې',
      to: '/sarayanos',
      path: '/sarayanos',
      icon: '🏭',
      color: 'brown-lighten-2',
      materialIcon: 'mdi-warehouse',
    },
    {
      label: 'Hotels Format',
      pashtoLabel: 'هوټلونو فورمې',
      to: '/hotels',
      path: '/hotels',
      icon: '🏨',
      color: 'pink-lighten-2',
      materialIcon: 'mdi-hotel',
    },
    {
      label: 'Hostels Format',
      pashtoLabel: 'هوسټلونو فورمې',
      to: '/hostels',
      path: '/hostels',
      icon: '🛏️',
      color: 'light-blue-lighten-2',
      materialIcon: 'mdi-bed',
    },
    {
      label: 'Tujarans Format',
      pashtoLabel: 'سوداګرو فورمې',
      to: '/tujarans',
      path: '/tujarans',
      icon: '👔',
      color: 'cyan-lighten-2',
      materialIcon: 'mdi-tie',
    },
    {
      label: 'Mujahedinos Format',
      pashtoLabel: 'مجاهدينو فورمې',
      to: '/mujahedinos',
      path: '/mujahedinos',
      icon: '🕌',
      color: 'green-lighten-1',
      materialIcon: 'mdi-mosque',
    },
    {
      label: 'Emaratoffices Format',
      pashtoLabel: 'د امارت دفترونو فورمې',
      to: '/emaratoffices',
      path: '/emaratoffices',
      icon: '🏛️',
      color: 'amber-lighten-1',
      materialIcon: 'mdi-government',
    },
    {
      label: 'oldjahadis Format',
      pashtoLabel: 'زړو جهادي قومندانانو فورمې',
      to: '/oldjahadis',
      path: '/oldjahadis',
      icon: '🎖️',
      color: 'orange-lighten-1',
      materialIcon: 'mdi-medal',
    },
    {
      label: 'mashkokpeoples Format',
      pashtoLabel: 'شبې خلکو فورمې',
      to: '/mashkokpeoples',
      path: '/mashkokpeoples',
      icon: '🕵️‍♂️',
      color: 'purple-lighten-2',
      materialIcon: 'mdi-account-question',
    },
    {
      label: 'madrasas Format',
      pashtoLabel: 'ديني مدرسو فورمې',
      to: '/madrasas',
      path: '/madrasas',
      icon: '📖',
      color: 'teal-lighten-1',
      materialIcon: 'mdi-book-open-variant',
    },
    {
      label: 'wakilanos Format',
      pashtoLabel: 'وکیلانو فورمې',
      to: '/wakilanos',
      path: '/wakilanos',
      icon: '⚖️',
      color: 'blue-grey-lighten-2',
      materialIcon: 'mdi-scale-balance',
    },
    {
      label: 'universities Format',
      pashtoLabel: 'پوهنتونونو فورمې',
      to: '/universities',
      path: '/universities',
      icon: '🎓',
      color: 'deep-purple-lighten-2',
      materialIcon: 'mdi-school',
    },
    {
      label: 'masjids Format',
      pashtoLabel: 'جوماتونو فورمې',
      to: '/masjids',
      path: '/masjids',
      icon: '🕌',
      color: 'green-lighten-2',
      materialIcon: 'mdi-mosque',
    },
    {
      label: 'highways Format',
      pashtoLabel: 'لویو لارو فورمې',
      to: '/highways',
      path: '/highways',
      icon: '🛣️',
      color: 'grey-lighten-1',
      materialIcon: 'mdi-highway',
    },
    {
      label: 'companies Format',
      pashtoLabel: 'شرکتونو فورمې',
      to: '/companies',
      path: '/companies',
      icon: '🏢',
      color: 'indigo-lighten-2',
      materialIcon: 'mdi-office-building',
    },
    {
      label: 'donelostplaces Format',
      pashtoLabel: 'د غلا شویو ځايونو فورمې',
      to: '/donelostplaces',
      path: '/donelostplaces',
      icon: '🏚️',
      color: 'red-lighten-2',
      materialIcon: 'mdi-home-alert',
    },
    {
      label: 'econemyprojects Format',
      pashtoLabel: 'د اقتصادي پروژو فورمې',
      to: '/econemyprojects',
      path: '/econemyprojects',
      icon: '📊',
      color: 'light-green-lighten-2',
      materialIcon: 'mdi-chart-bar',
    },
    {
      label: 'criminalcaptureplaces Format',
      pashtoLabel: 'د مجرمینو د نیولو ځايونو فورمې',
      to: '/criminalcaptureplaces',
      path: '/criminalcaptureplaces',
      icon: '🚔',
      color: 'deep-orange-lighten-2',
      materialIcon: 'mdi-police-badge',
    },
    {
      label: 'pumps Format',
      pashtoLabel: 'پمپونو فورمې',
      to: '/pumps',
      path: '/pumps',
      icon: '⛽',
      color: 'orange-lighten-2',
      materialIcon: 'mdi-gas-station',
    },
    {
      label: 'shoras Format',
      pashtoLabel: 'شوراګانو فورمې',
      to: '/shoras',
      path: '/shoras',
      icon: '👥',
      color: 'cyan-lighten-2',
      materialIcon: 'mdi-account-group',
    },
    {
      label: 'checkpoints Format',
      pashtoLabel: 'چک پوینټونو فورمې',
      to: '/checkpoints',
      path: '/checkpoints',
      icon: '🛂',
      color: 'amber-lighten-2',
      materialIcon: 'mdi-shield-check',
    },
    {
      label: 'governmentplaces Format',
      pashtoLabel: 'د حکومتي ځايونو فورمې',
      to: '/governmentplaces',
      path: '/governmentplaces',
      icon: '🏛️',
      color: 'blue-lighten-2',
      materialIcon: 'mdi-government',
    },
    {
      label: 'supermarkets Format',
      pashtoLabel: 'سوپر مارکیټونو فورمې',
      to: '/supermarkets',
      path: '/supermarkets',
      icon: '🛒',
      color: 'pink-lighten-2',
      materialIcon: 'mdi-cart',
    },
    {
      label: 'parks Format',
      pashtoLabel: 'پارکونو فورمې',
      to: '/parks',
      path: '/parks',
      icon: '🌳',
      color: 'light-green-lighten-2',
      materialIcon: 'mdi-tree',
    },
    {
      label: 'complexes Format',
      pashtoLabel: 'مجتمعونو فورمې',
      to: '/complexes',
      path: '/complexes',
      icon: '🏬',
      color: 'purple-lighten-2',
      materialIcon: 'mdi-domain',
    },
    {
      label: 'deplomaticplaces Format',
      pashtoLabel: 'د دیپلوماتیکو ځايونو فورمې',
      to: '/deplomaticplaces',
      path: '/deplomaticplaces',
      icon: '🇺🇳',
      color: 'teal-lighten-2',
      materialIcon: 'mdi-diploma',
    },
  ]);

  const items = ref([...original.value]);

  const filtered = computed(() => {
    const term = q.value?.trim().toLowerCase() || '';
    if (!term) return items.value;

    return items.value.filter(
      (i) =>
        i.label.toLowerCase().includes(term) ||
        t(i.pashtoLabel).toLowerCase().includes(term) ||
        (i.pashtoLabel && i.pashtoLabel.toLowerCase().includes(term)) ||
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
      'blue-grey-lighten-2': 'blue-grey-darken-4',
      'deep-purple-lighten-2': 'deep-purple-darken-4',
      'light-green-lighten-2': 'light-green-darken-4',
      'grey-lighten-1': 'grey-darken-4',
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
      'blue-grey-lighten-2': 'blue-grey-darken-4',
      'deep-purple-lighten-2': 'deep-purple-darken-4',
      'light-green-lighten-2': 'light-green-darken-4',
      'grey-lighten-1': 'grey-darken-4',
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
    document.documentElement.setAttribute('lang', direction.value === 'rtl' ? 'ps' : 'en');
  }

  // Set initial direction on mount
  onMounted(() => {
    document.documentElement.setAttribute('dir', direction.value);
    document.documentElement.setAttribute('lang', direction.value === 'rtl' ? 'ps' : 'en');
  });
</script>

<style scoped>
  .module-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 12px;
    min-height: 70px !important;
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
    padding: 12px !important;
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

  /* Responsive adjustments */
  @media (max-width: 600px) {
    .search-field {
      min-width: 180px !important;
    }
    
    .action-btn {
      font-size: 0.75rem;
    }
    
    .module-card {
      min-height: 65px !important;
      border-radius: 10px;
    }
  }

  @media (max-width: 400px) {
    .search-field {
      min-width: 150px !important;
    }
    
    .action-btn {
      font-size: 0.7rem;
      padding: 0 12px;
    }
  }

  /* Larger text for better readability */
  .text-body-2 {
    font-size: 0.875rem !important;
    font-weight: 600 !important;
  }

  .text-body-1 {
    font-size: 1rem !important;
    font-weight: 600 !important;
  }

  /* Improved responsive spacing */
  @media (max-width: 960px) {
    .justify-md-end {
      justify-content: flex-start !important;
    }
    
    [dir='rtl'] .justify-md-end {
      justify-content: flex-start !important;
    }
  }
</style>