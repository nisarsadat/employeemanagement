<template>
  <v-navigation-drawer
    app
    permanent
    width="240"
    :dir="isRtl ? 'rtl' : 'ltr'"
    :color="$vuetify.theme.current.dark ? 'grey-darken-4' : 'bgproject'"
    class="brright px-2"
  >
    <div class="logo-container">
      <v-list-item class="logo-container2">
        <!-- <v-img src="/assets/apartment.png" class="rounded-lg" /> -->
        <!-- Dynamically adjust the size -->
      </v-list-item>
    </div>
    <div class="scrollable-content">
      <!-- Main Navigation -->
      <v-list class="">
        <template v-for="item in navigationItems" :key="item.title">
          <!-- Single Items -->
          <v-list-item
            v-if="!item.children"
            :prepend-icon="item.icon"
            :title="item.title"
            :value="item.path"
            :to="item.path"
            :active="activeItem === item.path"
            @click="setActive(item.path)"
            rounded="2"
            :dir="isRtl ? 'rtl' : 'ltr'"
          >
            <template v-slot:prepend>
              <div class="icon-bg">
                <v-icon :icon="item.icon"></v-icon>
              </div>
            </template>
          </v-list-item>

          <!-- Group Items -->
          <v-list-group v-else :value="item.value">
            <template v-slot:activator="{ props }">
              <v-list-item
                v-bind="props"
                :append-icon="null"
                :title="item.title"
                :active="activeGroup === item.value"
                @click="setActiveGroup(item)"
                :class="{
                  'bg-navigationActive': activeGroup === item.value,
                  'text-white': activeGroup === item.value,
                }"
                :dir="isRtl ? 'rtl' : 'ltr'"
              >
                <template v-slot:prepend>
                  <div class="icon-bg">
                    <v-icon :icon="item.icon"></v-icon>
                  </div>
                </template>
              </v-list-item>
            </template>

            <v-list-item
              v-for="child in item.children"
              :key="child.title"
              :title="child.title"
              :value="child.path"
              :to="child.path"
              prepend-icon="mdi-circle-medium"
              :active="activeItem === child.path"
              @click="setActive(child.path)"
              :class="{
                'bg-navigationActive': activeItem === child.path,
                'text-white': activeItem === child.path,
              }"
            ></v-list-item>
          </v-list-group>
        </template>
      </v-list>
    </div>
    <!--
        <div class="bordertop">
            <v-list class="logoutPart">
                <LogInOut />
            </v-list>
        </div> -->
  </v-navigation-drawer>
</template>

<script setup>
  import { ref } from 'vue';
  import { useTheme } from 'vuetify';
  import { computed } from 'vue';
  import { useAuthRepository } from '@/stores/AuthRepository';
  // import LogInOut from "./LogInOut.vue";
  // const theme = useTheme();
  const activeItem = ref('');
  const activeGroup = ref('');
  import { useI18n } from 'vue-i18n';
  import { watch, computed } from 'vue';
  const { locale, t } = useI18n();
  const isRtl = ref(locale.value === 'fa'); // Assuming 'fa' is the code for Dari

  watch(locale, (newLocale) => {
    isRtl.value = newLocale === 'fa';
  });
  const auth = useAuthRepository();
  const isAdmin = computed(() => auth.hasRole('admin'))
  const can = (p) => auth.can(p)

  const navigationItems = computed(() => [
    {
      title: t('Dashboard'),
      icon: 'mdi-view-dashboard-outline',
      path: '/Dashboard',
    },
    // {
    //     title: t("expenses"),
    //     icon: "mdi-cash-minus",
    //     value: "Expense",
    //     children: [
    //         { title: t("expense"), path: "/expense" },
    //         { title: t("expenseCategory"), path: "/ExpenseCategory" },
    //     ],
    // },
    // {
    //     title: t("MoneyAccount"),
    //     icon: "mdi-cash-plus",
    //     path: "/moneyAccount",
    // },
    // {
    //     title: t("salary"),
    //     icon: "mdi-cash",
    //     path: "/Salary",
    // },

    // {
    //     title: t("reservation"),
    //     icon: "mdi-sale-outline",
    //     path: "/Reservission",
    // },
    // {
    //     title: t("rentManagement"),
    //     icon: "mdi-sale-outline",
    //     path: "/Rent",
    // },
    // {
    //     title: t("plaza"),
    //     icon: "mdi-home-outline",
    //     value: "Plaza",
    //     children: [
    //         { title: t("apartment"), path: "/apartment" },
    //         { title: t("floor"), path: "/Floor" },
    //         { title: t("flat"), path: "/Flat" },
    //     ],
    // },
    { title: t(' خطی اطلاعات'), path: '/inlineinfo', icon: 'mdi-menu' },
    // Permission-gated links
    ...(can('users.view') ? [{ title: t('استفاده کوونکی'), path: '/users', icon: 'mdi-account' }] : []),
    ...(can('employees.view') ? [{ title: t('  کارمندان'), path: '/officeemployee', icon: 'mdi-account-outline' }] : []),
    // { title: t('تدابیری کارمندان'), path: '/zoneemployee', icon: 'mdi-account-outline' },
    // { title: t('د رزیدینتانو په اره'), path: '/resedintemployee', icon: 'mdi-account-outline' },
    // {
    //   title: t('افراد'),
    //   icon: 'mdi-account-outline',
    //   value: 'people',
    //   children: [
    //     { title: t('استفاده کوونکی'), path: '/users' },
    //     { title: t('د مدیریت کارمندان'), path: '/officeemployee' },
    //     { title: t('تدابیری کارمندان'), path: '/zoneemployee' },
    //     { title: t('د رزیدینتانو په اره'), path: '/resedintemployee' },
    //   ],
    // },
    ...(can('resources.view') ? [{ title: t('منابع'), path: '/resources' , icon: 'mdi-pen' }] : []),
    { title: t('اطلاعات'), path: '/informations' ,  icon: 'mdi-book'  },
    { title: t('پیښي'), path: '/events' ,  icon: 'mdi-book-outline'  },
    { title: t('تسلیم شوی بندیان'), path: '/delevirycaptive' ,  icon: 'mdi-lock'  },
    // {
    //   title: t('دیتابیس'),
    //   icon: 'mdi-cog-outline',
    //   value: 'settings',
    //   children: [
    //     { title: t('منابع'), path: '/resources' },
    //     { title: t('اطلاعات'), path: '/informations' },
    //     { title: t('پیښي'), path: '/events' },
    //     { title: t('تسلیم شوی بندیان'), path: '/delevirycaptive' },
    //   ],
    // },
    // RBAC (admin only)
    ...(isAdmin.value ? [
      { title: 'RBAC Roles', path: '/rbac/roles', icon: 'mdi-security' },
      { title: 'RBAC Permissions', path: '/rbac/permissions', icon: 'mdi-key' },
      { title: 'RBAC Assign', path: '/rbac/assign', icon: 'mdi-account-key' },
      { title: 'RBAC Builder', path: '/rbac/permissions-builder', icon: 'mdi-tools' },
    ] : []),

    {
      title: t('پنڅوس فارمټ '),
      icon: 'mdi-view-dashboard-outline',
      path: '/fifthyformatsroute',
    },
    {
      title: t('reports'),
      icon: 'mdi-graph',
      value: 'Report',
      children: [
        { title: t('reservation'), path: '/ReservationReport' },
        { title: t('Account'), path: '/AccountSummary' },
        { title: t('Customer'), path: '/CustomerReport' },
        { title: t('MonthlyIncome'), path: '/MonthlyIncome' },
      ],
    },
  ]);

  const setActive = (path) => {
    activeItem.value = path;
  };

  const setActiveGroup = (group) => {
    activeGroup.value = group;
  };
</script>

<style scoped>
  /* Only added this new class for icon background */
  .icon-bg {
    background-color: white;
    color: black;
    width: 35px;
    height: 33px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 8px rgba(24, 23, 23, 0.25);
    margin-right: 16px;
  }
  [dir='rtl'] .icon-bg {
    background-color: white;
    color: black;
    width: 35px;
    height: 33px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 8px rgba(24, 23, 23, 0.25);
    margin-left: 16px;
  }
  /* Rest of your existing styles remain exactly the same */
  .scrollable-content {
    height: calc(90vh - 130px);
    overflow-y: auto;
  }
  .scrollable-content::-webkit-scrollbar {
    width: 0px;
  }

  .scrollable-content::-webkit-scrollbar-track {
    background-color: #f8f8f8 !important;
  }

  .scrollable-content::-webkit-scrollbar-thumb {
    background-color: #f8f8f8 !important;
    border-radius: 4px;
  }

  .scrollable-content::-webkit-scrollbar-thumb:hover {
    background-color: #f8f8f8 !important;
  }

  .v-navigation-drawer {
    transition: background-color 0.2s ease;
  }

  .v-list .v-list-item:hover {
    background-color: rgba(var(--v-theme-primary));
    border-radius: 9px !important;
  }

  .v-list-item--active {
    background-color: rgb(var(--v-theme-primary), 10) !important;
    border-radius: 9px !important;
  }

  .v-list-item--active .v-list-item-title,
  .v-list-item--active .v-icon {
    border-radius: 9px !important;
  }
  .brright {
    border-left: none !important;
  }

  .logoutPart {
    display: flex;
    flex-direction: column;
    justify-content: end;
  }

  .bordertop {
    border-top: 2px solid gray !important;
    margin-right: -12px;
    margin-left: -12px;
  }
  .logo-container {
    margin: 0 auto;
    padding-top: 18px !important;
    width: 16vh;
    height: 16vh;
  }
  .logo-container2 {
    margin: 0 auto;
  }
</style>
