<template>
  <v-navigation-drawer
    v-model="drawerOpen"
    temporary
    :location="props.location"
    width="240"
    :dir="isRtl ? 'rtl' : 'ltr'"
    class="blur-drawer"
    @mouseleave="drawerOpen = false"
  >
    <div class="drawer-root">
      <!-- Scrollable nav -->
      <div class="scrollable-content">
        <!-- ✅ Control opened groups from ONE place -->
        <v-list v-model:opened="openedGroups">
          <template v-for="item in filteredNavigationItems" :key="item.key">
            <!-- Single items -->
            <v-list-item
              v-if="!item.children"
              :to="item.to"
              rounded="lg"
              :dir="isRtl ? 'rtl' : 'ltr'"
              :active="isActive(item)"
              class="nav-item"
              :class="{ 'nav-item-active': isActive(item) }"
            >
              <template #prepend>
                <div class="icon-bg" :class="{ 'icon-bg-active': isActive(item) }">
                  <v-icon :icon="item.icon" />
                </div>
              </template>
              <v-list-item-title>{{ item.title }}</v-list-item-title>
            </v-list-item>

            <!-- Groups -->
            <v-list-group v-else :value="item.key">
              <template #activator="{ props }">
                <v-list-item
                  v-bind="props"
                  :append-icon="null"
                  class="nav-item"
                  :class="{
                    'nav-item-active': isGroupOpen(item),
                    'group-open': isGroupOpen(item),
                  }"
                  :dir="isRtl ? 'rtl' : 'ltr'"
                >
                  <template #prepend>
                    <div class="icon-bg" :class="{ 'icon-bg-active': isGroupOpen(item) }">
                      <v-icon :icon="item.icon" />
                    </div>
                  </template>

                  <v-list-item-title>{{ item.title }}</v-list-item-title>

                  <template #append>
                    <v-icon
                      :icon="isGroupOpen(item) ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                      size="small"
                      class="group-chevron"
                    />
                  </template>
                </v-list-item>
              </template>

              <v-list-item
                v-for="child in item.children"
                :key="child.key"
                :to="child.to"
                class="nav-child-item"
                :class="{ 'nav-child-active': isActive(child) }"
                :active="isActive(child)"
              >
                <template #prepend>
                  <div class="child-indicator">
                    <div class="child-dot" :class="{ 'child-dot-active': isActive(child) }"></div>
                  </div>
                </template>
                <v-list-item-title>{{ child.title }}</v-list-item-title>
              </v-list-item>
            </v-list-group>
          </template>
        </v-list>
      </div>

      <!-- ✅ Fixed bottom profile (does not move on scroll) -->
      <div class="profile-fixed">
        <v-menu
          v-model="profileMenu"
          location="top start"
          :dir="isRtl ? 'rtl' : 'ltr'"
          :close-on-content-click="true"
        >
          <template #activator="{ props }">
            <v-list-item v-bind="props" class="profile-tile my-2" rounded="lg" role="button">
              <template #prepend>
                <v-avatar size="30" class="mr-3 br-1" v-if="avatarSrc">
                  <v-img :src="avatarSrc" alt="avatar" cover />
                </v-avatar>
                <v-avatar size="40" class="mr-3 bg-primary bold" v-else>
                  <span class="text-subtitle-1 font-weight-bold">{{ initials }}</span>
                </v-avatar>
              </template>

              <v-list-item-title class="text-body-2 font-weight-medium">
                {{ displayName }}
              </v-list-item-title>
              <v-list-item-subtitle class="text-caption text-grey-darken-1">
                {{ usernameAt }}
              </v-list-item-subtitle>
            </v-list-item>
          </template>

          <v-card min-width="240">
            <v-list density="compact">
              <v-list-item @click="goToProfile" prepend-icon="mdi-account-circle-outline">
                <v-list-item-title>{{ t('Profile') }}</v-list-item-title>
              </v-list-item>

              <v-divider class="my-1" />

              <v-list-item @click="onLogout" prepend-icon="mdi-logout-variant">
                <v-list-item-title>{{ t('Logout') }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card>
        </v-menu>
      </div>
    </div>
  </v-navigation-drawer>
</template>

<script setup>
  import { ref, watch, computed, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute } from 'vue-router';

  // Stores
  import { useLoginRepository } from '@/stores/LoginRepository';
  import { useUsersRepository } from '@/stores/UsersRepository';
  import { useRouter } from 'vue-router';

  const router = useRouter();
  const props = defineProps({
    modelValue: { type: Boolean, default: false }, // open/close from parent
    location: { type: String, default: 'left' }, // left/right from parent
  });
  const emit = defineEmits(['update:modelValue']);

  const drawerOpen = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
  });

  const route = useRoute();
  const { locale, t } = useI18n();

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  const goToProfile = async () => {
    profileMenu.value = false;
    await router.push('/app/profile');
  };
  // repos
  const auth = useLoginRepository();
  const usersRepo = useUsersRepository(); // kept for future user actions if needed

  onMounted(async () => {
    try {
      (await auth.ensureMe?.()) ?? auth.fetchUser?.();
    } catch {}
  });

  // profile menu
  const profileMenu = ref(false);

  // ✅ NEW: opened groups array
  // Make it single-open (accordion behavior) by keeping at most 1 item.
  const openedGroups = ref([]);

  // auto-open correct group based on current route
  watch(
    () => route.path,
    () => {
      const key = groupKeyByRoute(route.path);
      openedGroups.value = key ? [key] : [];
    },
    { immediate: true }
  );

  function groupKeyByRoute(path) {
    if (
      path.startsWith('/app/dailyfoulreports') ||
      path.startsWith('/app/monthlyfoulreports') ||
      path.startsWith('/app/yearlyfoulreports') ||
      path.startsWith('/app/weeklyfoulreports') ||
      path.startsWith('/app/quarterlyfoulreports') ||
      path.startsWith('/app/alltimefoulreports')
    )
      return 'reports';

    if (path.startsWith('/app/settings/')) return 'system-settings';

    return null;
  }

  // derive profile display
  const userObj = computed(() => {
    const u = auth.user;
    if (!u) return null;
    return u.name ? u : u.user ?? null;
  });

  const displayName = computed(() => userObj.value?.name ?? userObj.value?.username ?? t('Guest'));

  const usernameAt = computed(() => {
    const uname = userObj.value?.username;
    return uname ? `@${uname}` : '@—';
  });

  const avatarSrc = computed(() => {
    const imgUrl = userObj.value?.image_url;
    const imgRel = userObj.value?.image;
    return imgUrl || (imgRel ? `/storage/${imgRel}` : null);
  });

  const initials = computed(() => {
    const n = (userObj.value?.name || '').trim();
    if (!n) return '😊';
    const parts = n.split(/\s+/).slice(0, 2);
    return parts.map((p) => p[0]?.toUpperCase() || '').join('');
  });

  // Helper
  const toName = (name) => ({ name });

  // Permission helpers
  const canView = (item) => {
    if (!item) return false;
    if (item.perm && !auth.can(item.perm)) return false;
    if (Array.isArray(item.allPerms) && item.allPerms.length) {
      for (const p of item.allPerms) if (!auth.can(p)) return false;
    }
    if (Array.isArray(item.anyPerms) && item.anyPerms.length) {
      const ok = item.anyPerms.some((p) => auth.can(p));
      if (!ok) return false;
    }
    return true;
  };

  const withPerms = (node) => ({ ...node });

  const navigationItems = computed(() =>
    [
      {
        key: 'dashboard',
        title: t('داشبورد'),
        icon: 'mdi-view-dashboard-variant-outline',
        to: toName('Dashboard'),
        perm: 'dashboards.view',
      },

      {
        key: 'officeemployee',
        title: t('کارمندان'),
        icon: 'mdi-badge-account-outline',
        to: { path: '/app/officeemployee' },
        perm: 'officeemployee.view',
      },
      {
        key: 'resources',
        title: t('منابع'),
        icon: 'mdi-database-outline',
        to: { path: '/app/resources' },
        perm: 'resources.view',
      },
      {
        key: 'informations',
        title: t('اطلاعات'),
        icon: 'mdi-file-document-multiple-outline',
        to: { path: '/app/informations' },
        perm: 'informations.view',
      },
      {
        key: 'events',
        title: t('واقعات'),
        icon: 'mdi-alert-circle-outline',
        to: { path: '/app/events' },
        perm: 'events.view',
      },
      {
        key: 'captives',
        title: t('زندانی های تسلیم شده'),
        icon: 'mdi-handcuffs',
        to: { path: '/app/delevirycaptive' },
        perm: 'captives.view',
      },
      {
        key: 'fifthyformats',
        title: t('۵۶ فارمت'),
        icon: 'mdi-file-table-box-multiple-outline',
        to: { path: '/app/fifthyformatsroute' },
        perm: 'fifthyformatsroute.view',
      },
      {
        key: 'finance',
        title: t('بخش حساب مالی'),
        icon: 'mdi-cash-register',
        to: { path: '/app/finincalindex' },
        perm: 'finincalindex.view',
      },
      {
        key: 'outgoingincommings',
        title: t('صادره وارده'),
        icon: 'mdi-swap-horizontal-bold',
        to: { path: '/app/outgoingincommings' },
        perm: 'outgoingincomming.view',
      },

      // Reports Group
      {
        key: 'reports',
        title: t('راپورها'),
        icon: 'mdi-chart-box-multiple-outline',
        perm: 'reports.view',
        children: [
          {
            key: 'r-daily',
            title: t('راپور روزانه'),
            to: { path: '/app/dailyfoulreports' },
            icon: 'mdi-calendar-today',
            perm: 'dailyfoulreports.view',
          },
          {
            key: 'r-weekly',
            title: t('راپور هفته وار'),
            to: { path: '/app/weeklyfoulreports' },
            icon: 'mdi-calendar-week',
            perm: 'weeklyfoulreports.view',
          },
          {
            key: 'r-monthly',
            title: t('راپور ماهانه'),
            to: { path: '/app/monthlyfoulreports' },
            icon: 'mdi-calendar-month',
            perm: 'monthlyfoulreports.view',
          },
          {
            key: 'r-quarterly',
            title: t('راپور ربعه وار'),
            to: { path: '/app/quarterlyfoulreports' },
            icon: 'mdi-calendar-range',
            perm: 'quarterlyfoulreports.view',
          },
          {
            key: 'r-yearly',
            title: t('راپور سالانه'),
            to: { path: '/app/yearlyfoulreports' },
            icon: 'mdi-calendar',
            perm: 'yearlyfoulreports.view',
          },
          {
            key: 'r-archive',
            title: t('آرشیف'),
            to: { path: '/app/alltimefoulreports' },
            icon: 'mdi-archive-outline',
            perm: 'alltimefoulreports.view',
          },
          {
            key: 'reportnote',
            title: t('نوت'),
            to: { path: '/app/reportnote' },
            icon: 'mdi-note-text-outline',
            perm: 'reports.view',
          },
        ],
      },
      {
        key: 'khayratreports',
        title: t('راپور خیریت'),
        icon: 'mdi-chart-donut-variant',
        to: { path: '/app/khayratreports' },
        perm: 'khayratreport.view',
      },
      {
        key: 'map',
        title: t('نقشه'),
        icon: 'mdi-map-marker-radius-outline',
        to: { path: '/app/map-afg' },
        perm: 'map-afg.view',
      },
      {
        key: 'inlineinfo',
        title: t('ارتباطی مرکز'),
        icon: 'mdi-chat-processing-outline',
        to: { path: '/app/inlineinfo' },
        perm: 'inlineinfo.view',
      },
      {
        key: 'roles',
        title: t('نقش ها'),
        icon: 'mdi-shield-account-outline',
        to: { path: '/app/roles' },
        perm: 'roles.view',
      },
      {
        key: 'users',
        title: t('استفاده کننده ها'),
        icon: 'mdi-account-group-outline',
        to: { path: '/app/users' },
        perm: 'users.view',
      },
      {
        key: 'settings',
        title: t('تنظیمات سیستم'),
        icon: 'mdi-cog-outline',
        to: { path: '/app/settings' },
        perm: 'settings.view',
      },
    ].map(withPerms)
  );

  const filteredNavigationItems = computed(() => {
    const result = [];
    for (const item of navigationItems.value) {
      if (!item.children) {
        if (canView(item)) result.push(item);
        continue;
      }
      if (!canView(item)) continue;
      const visibleChildren = item.children.filter((c) => canView(c));
      if (visibleChildren.length) result.push({ ...item, children: visibleChildren });
    }
    return result;
  });

  // Active helpers
  const isActive = (item) => {
    if (item.to?.name) return route.name === item.to.name;
    if (item.to?.path) return route.path === item.to.path;
    return false;
  };

  // ✅ group open check based on openedGroups
  const isGroupOpen = (groupItem) => openedGroups.value.includes(groupItem.key);

  // Actions
  const onLogout = async () => {
    profileMenu.value = false;
    await auth.Logout();
  };
</script>

<style scoped>
  .drawer-root {
    height: 100%;
    display: flex;
    flex-direction: column;
    min-height: 0; /* critical */
  }

  /* IMPORTANT: allow it to shrink inside flex */
  .scrollable-content {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
  }
  /* ✅ BEAUTIFUL SCROLLBAR (glass + modern) */
  .scrollable-content {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    padding-bottom: 110px;

    /* Firefox */
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 0, 0, 0.25) transparent;
  }

  /* Chrome / Edge / Safari */
  .scrollable-content::-webkit-scrollbar {
    width: 18px;
  }

  .scrollable-content::-webkit-scrollbar-track {
    background: transparent;
    margin: 10px 0; /* top/bottom gap */
    border-radius: 999px;
  }

  .scrollable-content::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: linear-gradient(
      180deg,
      rgba(var(--v-theme-primary), 0.55),
      rgba(var(--v-theme-primary), 0.25)
    );
    border: 3px solid rgba(255, 255, 255, 0.55); /* makes it look “floating” */
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12), inset 0 0 0 1px rgba(255, 255, 255, 0.45);
  }

  .scrollable-content::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(
      180deg,
      rgba(var(--v-theme-primary), 0.75),
      rgba(var(--v-theme-primary), 0.35)
    );
  }

  /* Optional: subtle when not interacting */
  .scrollable-content::-webkit-scrollbar-thumb:active {
    background: linear-gradient(
      180deg,
      rgba(var(--v-theme-primary), 0.9),
      rgba(var(--v-theme-primary), 0.45)
    );
  }

  /* Dark mode tuning */
  .v-theme--dark .scrollable-content {
    scrollbar-color: rgba(255, 255, 255, 0.28) transparent;
  }

  .v-theme--dark .scrollable-content::-webkit-scrollbar-thumb {
    border: 3px solid rgba(0, 0, 0, 0.35);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35), inset 0 0 0 1px rgba(255, 255, 255, 0.12);
  }
  .profile-fixed {
    flex: 0 0 auto;
    padding: 0 8px 8px;
    background: inherit;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(6px);
    z-index: 10;
  }

  /* keep your existing styles (no logic/icon changes) */
  .icon-bg {
    background-color: white;
    color: black;
    width: 35px;
    height: 33px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-right: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  [dir='rtl'] .icon-bg {
    margin-left: 16px;
    margin-right: 0;
  }
  .icon-bg-active {
    background-color: rgb(var(--v-theme-primary));
    color: white;
    box-shadow: 0 4px 8px rgba(var(--v-theme-primary), 0.3);
    transform: scale(1.05);
  }

  /* Navigation item styles */
  .nav-item {
    margin: 2px 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
  }
  .nav-item:hover {
    border-radius: 12px !important;
    background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 0.1) 0%,
      rgba(var(--v-theme-primary), 0.05) 100%
    );
    border-color: rgba(var(--v-theme-primary), 0.2);
    transform: translateX(2px);
  }
  .nav-item-active {
    border-radius: 12px !important;
    background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 0.15) 0%,
      rgba(var(--v-theme-primary), 0.08) 100%
    );
    border-color: rgba(var(--v-theme-primary), 0.3);
    box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.15);
  }
  .nav-item-active .v-list-item-title {
    color: rgb(var(--v-theme-primary));
    font-weight: 700;
  }

  /* Group styles */
  .group-open {
    background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 0.08) 0%,
      rgba(var(--v-theme-primary), 0.04) 100%
    );
  }
  .group-chevron {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: rgba(0, 0, 0, 0.6);
  }
  .nav-item-active .group-chevron {
    color: rgb(var(--v-theme-primary));
  }

  /* Child item styles */
  .nav-child-item {
    margin: 2px 8px;
    border-radius: 8px !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
    min-height: 40px;
  }
  .nav-child-item:hover {
    background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 0.08) 0%,
      rgba(var(--v-theme-primary), 0.04) 100%
    );
    border-color: rgba(var(--v-theme-primary), 0.15);
    transform: translateX(1px);
  }
  .nav-child-active {
    background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 0.12) 0%,
      rgba(var(--v-theme-primary), 0.06) 100%
    );
    border-color: rgba(var(--v-theme-primary), 0.25);
  }
  .nav-child-active .v-list-item-title {
    color: rgb(var(--v-theme-primary));
    font-weight: 500;
  }

  .child-indicator {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
  }
  [dir='rtl'] .child-indicator {
    margin-left: 12px;
    margin-right: 0;
  }
  .child-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: rgba(0, 0, 0, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .child-dot-active {
    background-color: rgb(var(--v-theme-primary));
    transform: scale(1.3);
    box-shadow: 0 2px 4px rgba(var(--v-theme-primary), 0.3);
  }

  .brright {
    border-left: none !important;
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

  /* Profile tile */
  .profile-tile {
    transition: background-color 0.2s ease;
  }
  .profile-tile:hover {
    background-color: rgba(0, 0, 0, 0.06);
  }
  :deep(.v-avatar) {
    background: var(--v-theme-surface);
  }

  /* Smooth transitions */
  .v-list-item {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  /* Focus states */
  .nav-item:focus-within,
  .nav-child-item:focus-within {
    outline: 2px solid rgba(var(--v-theme-primary), 0.3);
    outline-offset: 2px;
  }
  .blur-drawer {
    background: rgba(255, 255, 255, 0.6) !important; /* light mode */
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(14px);
    border-right: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 4%;
  }

  /* Dark mode support */
  .v-theme--dark .blur-drawer {
    background: rgba(30, 30, 30, 0.6) !important;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
  }
</style>
