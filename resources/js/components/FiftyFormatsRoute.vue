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
          <v-row cols="6" class="ma-0">
            <!-- Search Section -->
            <v-col cols="6" class="py-0 pb-3 pb-md-0">
              <v-text-field
                v-model="q"
                density="compact"
                :placeholder="direction === 'rtl' ? t('د ماډلونو لټون...') : t('Search modules...')"
                :prepend-inner-icon="direction === 'rtl' ? undefined : 'mdi-magnify'"
                :append-inner-icon="direction === 'rtl' ? 'mdi-magnify' : undefined"
                variant="outlined"
                hide-details
                class="flex-grow-1 search-field"
                style="min-width: 200px; max-width: 400px"
                clearable
              />
            </v-col>
          </v-row>
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
                :color="getCardColor(item)"
              >
                <v-card-text class="pa-2 pa-sm-3">
                  <div
                    class="d-flex align-center gap-2 gap-sm-3"
                    :class="direction === 'rtl' ? 'flex-row-reverse' : ''"
                  >
                    <!-- Icon -->
                    <div class="icon-wrapper flex-shrink-0">
                      <v-icon
                        :size="smAndUp ? 28 : 24"
                        :color="getIconColor(item)"
                        class="module-icon"
                      >
                        {{ getMaterialIcon(item.icon) }}
                      </v-icon>
                    </div>
                    <!-- Label -->
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
            <v-icon :size="smAndUp ? 64 : 48" color="grey" class="mb-2 mb-sm-4"
              >mdi-magnify-remove</v-icon
            >
            <h3 class="text-h6 text-sm-h5 text-grey-darken-2 mb-1 mb-sm-2">
              {{ direction === 'rtl' ? t('هیڅ ماډل ونه موندل شو') : t('No modules found') }}
            </h3>
            <p class="text-body-2 text-sm-body-1 text-grey">
              {{
                direction === 'rtl'
                  ? t('د "{query}" لپاره هیڅ پایله ونه موندل شوه. خپل د لټون شرایط سم کړئ.', {
                      query: q,
                    })
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
  import { useLoginRepository } from '@/stores/LoginRepository';

  const auth = useLoginRepository();
  const { t } = useI18n();
  const { smAndUp } = useDisplay();

  const q = ref('');
  const direction = ref('rtl'); // Default RTL for Pashto

  // 🔒 ensure everything routes under the protected app shell
  const APP_BASE = '/app';
  const toPath = (item) => `${APP_BASE}${item.path}`;

  /**
   * Helper: check permission for card visibility
   * Uses your rbac.php naming: `${module}.view`
   */
  function canView(item) {
    const perm = item?.perm ? `${item.perm}.view` : null;
    // if auth.can is not defined for any reason, fall back to true to avoid crashes
    if (!perm || typeof auth?.can !== 'function') return true;
    return auth.can(perm);
  }

  /**
   * Enhanced module data with `perm` matching config/rbac.php modules.
   * (kept exact design/labels/paths/icons)
   */
  const original = ref([
    // Base
    {
      label: 'Fasad Format',
      pashtoLabel: 'فساد فورمې',
      path: '/fasads',
      icon: '🧩',
      color: 'blue-lighten-3',
      perm: 'fasads',
    },
    {
      label: 'Saeen Fasad Format',
      pashtoLabel: 'فساد ثاني فورمې',
      path: '/saeenfasad',
      icon: '🧠',
      color: 'deep-purple-lighten-3',
      perm: 'saeen_fasads',
    },
    {
      label: 'Heist Format',
      pashtoLabel: 'سرقت فورمې',
      path: '/heist',
      icon: '🕵️',
      color: 'red-lighten-3',
      perm: 'heists',
    },
    {
      label: 'Captive Format',
      pashtoLabel: 'بندي فورمې',
      path: '/captives',
      icon: '🔒',
      color: 'orange-lighten-3',
      perm: 'captives',
    },
    {
      label: 'Mulkano Format',
      pashtoLabel: 'ملکونو فورمې',
      path: '/mulkanos',
      icon: '🏠',
      color: 'green-lighten-3',
      perm: 'mulkanos',
    },
    {
      label: 'Entry/Exit Format',
      pashtoLabel: 'ننوتل/وتل فورمې',
      path: '/entry-exits',
      icon: '➡️',
      color: 'cyan-lighten-3',
      perm: 'entry_exits',
    },
    {
      label: 'Sarafyano Format',
      pashtoLabel: 'صرفی فورمې',
      path: '/sarafyanos',
      icon: '💱',
      color: 'amber-lighten-3',
      perm: 'sarafyanos',
    },
    {
      label: 'Clubs Format',
      pashtoLabel: 'کلبونو فورمې',
      path: '/clubs',
      icon: '🎯',
      color: 'pink-lighten-3',
      perm: 'clubs',
    },
    {
      label: 'Mahajerat Format',
      pashtoLabel: 'کډوالۍ فورمې',
      path: '/mahajerats',
      icon: '🧳',
      color: 'teal-lighten-3',
      perm: 'mahajerats',
    },
    {
      label: 'Soldier Format',
      pashtoLabel: 'سرپي فورمې',
      path: '/soldiers',
      icon: '🪖',
      color: 'brown-lighten-3',
      perm: 'soldiers',
    },

    // One
    {
      label: 'Mawads Format',
      pashtoLabel: 'مخدره موادو فورمې',
      path: '/mawads',
      icon: '💊',
      color: 'deep-orange-lighten-3',
      perm: 'mawads',
    },
    {
      label: 'Lost Mujremeen Format',
      pashtoLabel: 'ورک شوي مجرمين فورمې',
      path: '/lostmujremeens',
      icon: '🔍',
      color: 'indigo-lighten-3',
      perm: 'lost_mujremeens',
    },
    {
      label: 'Old Soldiers Homes Format',
      pashtoLabel: 'زړو سرپيو کورونو فورمې',
      path: '/oldsoldiershomes',
      icon: '🏚️',
      color: 'blue-grey-lighten-3',
      perm: 'old_soldiers_homes',
    },
    {
      label: 'Robbers Format',
      pashtoLabel: 'غلاګرو فورمې',
      path: '/robbers',
      icon: '💰',
      color: 'lime-lighten-3',
      perm: 'robbers',
    },
    {
      label: 'None Resposable People Format',
      pashtoLabel: 'غير مسول وسله وال فورمې',
      path: '/nonresponsablepeople',
      icon: '⚠️',
      color: 'yellow-lighten-3',
      perm: 'non_responsable_people',
    },
    {
      label: 'Mashkok Homes Format',
      pashtoLabel: 'شبې کورونو فورمې',
      path: '/mashkokhomes',
      icon: '🏘️',
      color: 'purple-lighten-3',
      perm: 'mashkok_homes',
    },
    {
      label: 'Damage PLaces Format',
      pashtoLabel: 'زخمي ځايونو فورمې',
      path: '/damageplaces',
      icon: '🏚️',
      color: 'red-lighten-2',
      perm: 'damage_places',
    },
    {
      label: 'Damage Rasan PLaces Format',
      pashtoLabel: 'زخمي رسونکو ځايونو فورمې',
      path: '/damagerasanplaces',
      icon: '💥',
      color: 'deep-orange-lighten-2',
      perm: 'damage_rasan_places',
    },
    {
      label: 'Mashkok Shops Format',
      pashtoLabel: 'شبې پلورنځيو فورمې',
      path: '/mashkokshops',
      icon: '🏪',
      color: 'amber-lighten-2',
      perm: 'mashkok_shops',
    },
    {
      label: 'Luchakano Format',
      pashtoLabel: 'لوچکانو فورمې',
      path: '/luchakanos',
      icon: '👤',
      color: 'pink-lighten-2',
      perm: 'luchakanos',
    },

    // Two
    {
      label: 'Schools Format',
      pashtoLabel: 'ښوونځيو فورمې',
      path: '/schools',
      icon: '🏫',
      color: 'light-blue-lighten-3',
      perm: 'schools',
    },
    {
      label: 'Hado Bus Stops Format',
      pashtoLabel: 'د بس درېدو ځايونو فورمې',
      path: '/hadobusstops',
      icon: '🚏',
      color: 'cyan-lighten-2',
      perm: 'hado_bus_stops',
    },
    {
      label: 'Transport Company Format',
      pashtoLabel: 'د ترانسپورټ شرکتونو فورمې',
      path: '/transportcompanies',
      icon: '🚚',
      color: 'teal-lighten-2',
      perm: 'transport_companies',
    },
    {
      label: 'Car Sales Companies Format',
      pashtoLabel: 'د موټر پلورنځيو فورمې',
      path: '/carsalescompanies',
      icon: '🚗',
      color: 'green-lighten-2',
      perm: 'car_sales_companies',
    },
    {
      label: 'Parking Formats',
      pashtoLabel: 'د پارکینګ فورمې',
      path: '/parkings',
      icon: '🅿️',
      color: 'blue-lighten-2',
      perm: 'parkings',
    },
    {
      label: 'Banks Formats',
      pashtoLabel: 'د بانکونو فورمې',
      path: '/banks',
      icon: '🏦',
      color: 'indigo-lighten-2',
      perm: 'banks',
    },
    {
      label: 'Apartments Formats',
      pashtoLabel: 'د آپارتمانونو فورمې',
      path: '/apartments',
      icon: '🏢',
      color: 'purple-lighten-2',
      perm: 'apartments',
    },
    {
      label: 'Rahnoma Mamlat Formats',
      pashtoLabel: 'د رهنمای معاملاتو فورمې',
      path: '/rahnomamamlats',
      icon: '📋',
      color: 'deep-purple-lighten-2',
      perm: 'rahnoma_mamlats',
    },
    {
      label: 'Hosppitals Formats',
      pashtoLabel: 'د روغتونونو فورمې',
      path: '/hosppitals',
      icon: '🏥',
      color: 'red-lighten-1',
      perm: 'hosppitals',
    },
    {
      label: 'Work Shops Formats',
      pashtoLabel: 'د کارخونو فورمې',
      path: '/workshops',
      icon: '🔧',
      color: 'orange-lighten-2',
      perm: 'work_shops',
    },

    // Three
    {
      label: 'Badmashano Format',
      pashtoLabel: 'بدماشو فورمې',
      path: '/badmashanos',
      icon: '👊',
      color: 'deep-orange-lighten-2',
      perm: 'badmashanos',
    },
    {
      label: 'Sarayono Format',
      pashtoLabel: 'سرایونو فورمې',
      path: '/sarayanos',
      icon: '🏭',
      color: 'brown-lighten-2',
      perm: 'sarayanos',
    },
    {
      label: 'Hotels Format',
      pashtoLabel: 'هوټلونو فورمې',
      path: '/hotels',
      icon: '🏨',
      color: 'pink-lighten-2',
      perm: 'hotels',
    },
    {
      label: 'Hostels Format',
      pashtoLabel: 'هوسټلونو فورمې',
      path: '/hostels',
      icon: '🛏️',
      color: 'light-blue-lighten-2',
      perm: 'hostels',
    },
    {
      label: 'Tujarans Format',
      pashtoLabel: 'سوداګرو فورمې',
      path: '/tujarans',
      icon: '👔',
      color: 'cyan-lighten-2',
      perm: 'tujarans',
    },
    {
      label: 'Mujahedinos Format',
      pashtoLabel: 'مجاهدينو فورمې',
      path: '/mujahedinos',
      icon: '🕌',
      color: 'green-lighten-1',
      perm: 'mujahedinos',
    },
    {
      label: 'Emaratoffices Format',
      pashtoLabel: 'د امارت دفترونو فورمې',
      path: '/emaratoffices',
      icon: '🏛️',
      color: 'amber-lighten-1',
      perm: 'emarat_offices',
    },
    {
      label: 'oldjahadis Format',
      pashtoLabel: 'زړو جهادي قومندانانو فورمې',
      path: '/oldjahadis',
      icon: '🎖️',
      color: 'orange-lighten-1',
      perm: 'old_jahadis',
    },
    {
      label: 'mashkokpeoples Format',
      pashtoLabel: 'شبې خلکو فورمې',
      path: '/mashkokpeoples',
      icon: '🕵️‍♂️',
      color: 'purple-lighten-2',
      perm: 'mashkok_people',
    },
    {
      label: 'madrasas Format',
      pashtoLabel: 'ديني مدرسو فورمې',
      path: '/madrasas',
      icon: '📖',
      color: 'teal-lighten-1',
      perm: 'madrasas',
    },

    // Four
    {
      label: 'wakilanos Format',
      pashtoLabel: 'وکیلانو فورمې',
      path: '/wakilanos',
      icon: '⚖️',
      color: 'blue-grey-lighten-2',
      perm: 'wakilanos',
    },
    {
      label: 'universities Format',
      pashtoLabel: 'پوهنتونونو فورمې',
      path: '/universities',
      icon: '🎓',
      color: 'deep-purple-lighten-2',
      perm: 'universities',
    },
    {
      label: 'masjids Format',
      pashtoLabel: 'جوماتونو فورمې',
      path: '/masjids',
      icon: '🕌',
      color: 'green-lighten-2',
      perm: 'masjids',
    },
    {
      label: 'highways Format',
      pashtoLabel: 'لویو لارو فورمې',
      path: '/highways',
      icon: '🛣️',
      color: 'grey-lighten-1',
      perm: 'highways',
    },
    {
      label: 'companies Format',
      pashtoLabel: 'شرکتونو فورمې',
      path: '/companies',
      icon: '🏢',
      color: 'indigo-lighten-2',
      perm: 'companies',
    },
    {
      label: 'donelostplaces Format',
      pashtoLabel: 'د غلا شویو ځايونو فورمې',
      path: '/donelostplaces',
      icon: '🏚️',
      color: 'red-lighten-2',
      perm: 'donelostplaces',
    },
    {
      label: 'econemyprojects Format',
      pashtoLabel: 'د اقتصادي پروژو فورمې',
      path: '/econemyprojects',
      icon: '📊',
      color: 'light-green-lighten-2',
      perm: 'econemyprojects',
    },
    {
      label: 'criminalcaptureplaces Format',
      pashtoLabel: 'د مجرمینو د نیولو ځايونو فورمې',
      path: '/criminalcaptureplaces',
      icon: '🚔',
      color: 'deep-orange-lighten-2',
      perm: 'criminalcaptureplaces',
    },
    {
      label: 'pumps Format',
      pashtoLabel: 'پمپونو فورمې',
      path: '/pumps',
      icon: '⛽',
      color: 'orange-lighten-2',
      perm: 'pumps',
    },
    {
      label: 'shoras Format',
      pashtoLabel: 'شوراګانو فورمې',
      path: '/shoras',
      icon: '👥',
      color: 'cyan-lighten-2',
      perm: 'shoras',
    },
    {
      label: 'checkpoints Format',
      pashtoLabel: 'چک پوینټونو فورمې',
      path: '/checkpoints',
      icon: '🛂',
      color: 'amber-lighten-2',
      perm: 'checkpoints',
    },
    {
      label: 'governmentplaces Format',
      pashtoLabel: 'د حکومتي ځايونو فورمې',
      path: '/governmentplaces',
      icon: '🏛️',
      color: 'blue-lighten-2',
      perm: 'governmentplaces',
    },
    {
      label: 'supermarkets Format',
      pashtoLabel: 'سوپر مارکیټونو فورمې',
      path: '/supermarkets',
      icon: '🛒',
      color: 'pink-lighten-2',
      perm: 'supermarkets',
    },
    {
      label: 'parks Format',
      pashtoLabel: 'پارکونو فورمې',
      path: '/parks',
      icon: '🌳',
      color: 'light-green-lighten-2',
      perm: 'parks',
    },
    {
      label: 'complexes Format',
      pashtoLabel: 'مجتمعونو فورمې',
      path: '/complexes',
      icon: '🏬',
      color: 'purple-lighten-2',
      perm: 'complexes',
    },
    {
      label: 'deplomaticplaces Format',
      pashtoLabel: ' د دیپلوماتیکو ځايونو فورمې',
      path: '/deplomaticplaces',
      icon: '🇺🇳',
      color: 'teal-lighten-2',
      perm: 'deplomaticplaces',
    },
  ]);

  const items = ref([...original.value]);

  /** Only show items the user can view */
  const visibleItems = computed(() => items.value.filter(canView));

  /** Search within visible items */
  const filtered = computed(() => {
    const term = q.value?.trim().toLowerCase() || '';
    const base = visibleItems.value;
    if (!term) return base;
    return base.filter(
      (i) =>
        i.label.toLowerCase().includes(term) ||
        i.pashtoLabel?.toLowerCase().includes(term) ||
        i.path.toLowerCase().includes(term)
    );
  });

  // Styling helpers (unchanged)
  function getCardColor(item) {
    return item.color || 'white';
  }
  function getIconColor(item) {
    const m = {
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
      'brown-lighten-2': 'brown-darken-4',
      'light-green-lighten-2': 'light-green-darken-4',
      'grey-lighten-1': 'grey-darken-4',
    };
    return m[item.color] || 'primary';
  }
  function getTextColor(item) {
    const m = {
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
      'brown-lighten-2': 'brown-darken-4',
      'light-green-lighten-2': 'light-green-darken-4',
      'grey-lighten-1': 'grey-darken-4',
    };
    return m[item.color] || 'primary';
  }
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

  // Actions
  function shuffle() {
    // keep permission guard intact while shuffling:
    const v = [...visibleItems.value];
    items.value = [...v].sort(() => Math.random() - 0.5);
  }
  function resetOrder() {
    items.value = [...original.value];
  }
  function toggleDirection() {
    direction.value = direction.value === 'rtl' ? 'ltr' : 'rtl';
    document.documentElement.setAttribute('dir', direction.value);
    document.documentElement.setAttribute('lang', direction.value === 'rtl' ? 'ps' : 'en');
  }
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
  .text-right {
    text-align: right;
  }
  .text-left {
    text-align: left;
  }
  [dir='rtl'] .flex-row-reverse {
    flex-direction: row-reverse;
  }
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
  .text-body-2 {
    font-size: 0.875rem !important;
    font-weight: 600 !important;
  }
  .text-body-1 {
    font-size: 1rem !important;
    font-weight: 600 !important;
  }
  @media (max-width: 960px) {
    .justify-md-end {
      justify-content: flex-start !important;
    }
    [dir='rtl'] .justify-md-end {
      justify-content: flex-start !important;
    }
  }
</style>
