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
              <h1 class="text-h4 font-weight-bold text-primary" :class="direction === 'rtl' ? 'text-right' : 'text-left'">
                {{ direction === 'rtl' ? t(" پنجاه فارمته") : t("Fifty Formats") }}
              </h1>
              <p class="text-body-1 text-grey-darken-1 mt-2" :class="direction === 'rtl' ? 'text-right' : 'text-left'">
                {{ direction === 'rtl' 
                  ? t(".لینک‌های سریع برای مدیریت هر ماژول. از جستجو برای فیلتر کردن ماژول‌های موجود استفاده کنید") 
                  : t("Quick links to manage each module. Use search to filter available modules.") 
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
              <div class="d-flex align-center gap-2" :class="direction === 'rtl' ? 'flex-row-reverse justify-start' : 'justify-start'">
                <v-text-field
                  v-model="q"
                  density="compact"
                  :placeholder="direction === 'rtl' ? t('جستجوی ماژول‌ها...') : t('Search modules...')"
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
                  {{ direction === 'rtl' ? t("پاک کردن") : t("Clear") }}
                </v-btn>
              </div>
            </v-col>

            <!-- Actions Section -->
            <v-col cols="12" md="6" class="py-0">
              <div class="d-flex align-center gap-2" :class="direction === 'rtl' ? 'flex-row-reverse justify-start' : 'justify-end'">
                <v-btn
                  variant="outlined"
                  color="primary"
                  @click="shuffle"
                  :prepend-icon="direction === 'rtl' ? undefined : 'mdi-shuffle-variant'"
                  :append-icon="direction === 'rtl' ? 'mdi-shuffle-variant' : undefined"
                >
                  {{ direction === 'rtl' ? t("تصادفی سازی") : t("Randomize") }}
                </v-btn>
                <v-btn
                  variant="outlined"
                  color="warning"
                  @click="resetOrder"
                  :prepend-icon="direction === 'rtl' ? undefined : 'mdi-restore'"
                  :append-icon="direction === 'rtl' ? 'mdi-restore' : undefined"
                >
                  {{ direction === 'rtl' ? t("بازنشانی ترتیب") : t("Reset Order") }}
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
                  <div class="d-flex align-center gap-3" :class="direction === 'rtl' ? 'flex-row-reverse' : ''">
                    <!-- Icon -->
                    <div class="icon-wrapper flex-shrink-0">
                      <v-icon size="24" :color="getIconColor(item)" class="module-icon">
                        {{ getMaterialIcon(item.icon) }}
                      </v-icon>
                    </div>
                    
                    <!-- Label and Badge -->
                    <div class="flex-grow-1 min-w-0">
                      <h3 class="text-body-2 font-weight-medium text-primary line-clamp-2 mb-0" 
                          :class="direction === 'rtl' ? 'text-right' : 'text-left'">
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
              {{ direction === 'rtl' ? t("ماژولی یافت نشد") : t("No modules found") }}
            </h3>
            <p class="text-body-1 text-grey">
              {{ direction === 'rtl' 
                ? t('نتیجه‌ای برای "{query}" یافت نشد. عبارت جستجو را تغییر دهید.', { query: q }) 
                : t('No results found for "{query}". Try adjusting your search terms.', { query: q }) 
              }}
            </p>
            <v-btn 
              class="mt-4" 
              color="primary" 
              @click="q = ''" 
              :prepend-icon="direction === 'rtl' ? undefined : 'mdi-backspace'"
              :append-icon="direction === 'rtl' ? 'mdi-backspace' : undefined"
            >
              {{ direction === 'rtl' ? t("پاک کردن جستجو") : t("Clear Search") }}
            </v-btn>
          </v-card-text>
        </v-card>

        <!-- Results counter -->
        <v-card v-if="filtered.length > 0" class="mt-4" variant="flat" color="transparent">
          <v-card-text class="text-center pa-2">
            <span class="text-body-2 text-grey">
              {{ direction === 'rtl' 
                ? t('نمایش {showing} از {total} ماژول', { showing: filtered.length, total: original.length }) 
                : t('Showing {showing} of {total} modules', { showing: filtered.length, total: original.length }) 
              }}
              <span v-if="q">
                {{ direction === 'rtl' 
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
              :prepend-icon="direction === 'rtl' ? 'mdi-format-align-right' : 'mdi-format-align-left'"
            >
              {{ direction === 'rtl' ? t("Switch to LTR") : t("Switch to RTL") }}
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

  // Enhanced module data with both English and Dari labels
  const original = ref([
    {
      label: 'Fasad',
      dariLabel: 'فساد',
      to: '/fasads',
      path: '/fasads',
      icon: '🧩',
      color: 'blue-lighten-4',
      materialIcon: 'mdi-puzzle',
    },
    {
      label: 'Saeen Fasad',
      dariLabel: 'فساد ثانی',
      to: '/saeenfasad',
      path: '/saeenfasad',
      icon: '🧠',
      color: 'purple-lighten-4',
      materialIcon: 'mdi-brain',
    },
    {
      label: 'Heist',
      dariLabel: 'سرقت',
      to: '/heist',
      path: '/heist',
      icon: '🕵️',
      color: 'red-lighten-4',
      materialIcon: 'mdi-spy',
    },
    {
      label: 'Captive',
      dariLabel: 'اسیر',
      to: '/captives',
      path: '/captives',
      icon: '🔒',
      color: 'orange-lighten-4',
      materialIcon: 'mdi-lock',
    },
    {
      label: 'Mulkano',
      dariLabel: 'ملک‌ها',
      to: '/mulkanos',
      path: '/mulkanos',
      icon: '🏠',
      color: 'green-lighten-4',
      materialIcon: 'mdi-home',
    },
    {
      label: 'Entry/Exit',
      dariLabel: 'ورود/خروج',
      to: '/entry-exits',
      path: '/entry-exits',
      icon: '➡️',
      color: 'cyan-lighten-4',
      materialIcon: 'mdi-export',
    },
    {
      label: 'Sarafyano',
      dariLabel: 'صرافی‌ها',
      to: '/sarafyanos',
      path: '/sarafyanos',
      icon: '💱',
      color: 'amber-lighten-4',
      materialIcon: 'mdi-currency-usd',
    },
    {
      label: 'Clubs',
      dariLabel: 'کلب‌ها',
      to: '/clubs',
      path: '/clubs',
      icon: '🎯',
      color: 'pink-lighten-4',
      materialIcon: 'mdi-target',
    },
    {
      label: 'Mahajerat',
      dariLabel: 'مهاجرت‌ها',
      to: '/mahajerats',
      path: '/mahajerats',
      icon: '🧳',
      color: 'teal-lighten-4',
      materialIcon: 'mdi-briefcase',
    },
    {
      label: 'Soldier',
      dariLabel: 'سرباز',
      to: '/soldiers',
      path: '/soldiers',
      icon: '🪖',
      color: 'brown-lighten-4',
      materialIcon: 'mdi-shield-account',
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
    const colorMap = {
      'blue-lighten-4': 'blue-darken-2',
      'purple-lighten-4': 'purple-darken-2',
      'red-lighten-4': 'red-darken-2',
      'orange-lighten-4': 'orange-darken-2',
      'green-lighten-4': 'green-darken-2',
      'cyan-lighten-4': 'cyan-darken-2',
      'amber-lighten-4': 'amber-darken-2',
      'pink-lighten-4': 'pink-darken-2',
      'teal-lighten-4': 'teal-darken-2',
      'brown-lighten-4': 'brown-darken-2',
    };
    return colorMap[item.color] || 'primary';
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

<style >
  .module-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 8px;
    min-height: 70px !important;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .module-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
  }

  .icon-wrapper {
    transition: transform 0.3s ease;
    flex-shrink: 0;
  }

  .module-card:hover .icon-wrapper {
    transform: scale(1.05);
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
    line-height: 1.3;
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
  [dir="rtl"] .flex-row-reverse {
    flex-direction: row-reverse;
  }

  /* Fix toolbar alignment */
  [dir="rtl"] .justify-start {
    justify-content: flex-start !important;
  }

  [dir="ltr"] .justify-start {
    justify-content: flex-start !important;
  }

  [dir="rtl"] .justify-end {
    justify-content: flex-end !important;
  }

  [dir="ltr"] .justify-end {
    justify-content: flex-end !important;
  }
</style>