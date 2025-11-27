<template>
  <v-navigation-drawer
    app
    permanent
    width="240"
    :dir="isRtl ? 'rtl' : 'ltr'"
    :color="$vuetify.theme.current.dark ? 'grey-darken-4' : 'bgproject'"
    class="brright px-2 d-flex flex-column"
  >
    <!-- Top logo -->
    <div class="logo-container">
      <v-list-item class="logo-container2" />
    </div>

    <!-- Scrollable nav -->
    <div class="scrollable-content">
      <v-list>
        <template v-for="item in filteredNavigationItems" :key="item.title">
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
          <v-list-group
            v-else
            :value="isGroupOpen(item)"
            v-if="item.children && item.children.length"
          >
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
              :key="child.title"
              :to="child.to"
              class="nav-child-item"
              :class="{
                'nav-child-active': isActive(child),
              }"
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

    <!-- Bottom profile block -->
    <v-divider class="mt-2 mb-1" />

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
              <span class="text-subtitle-1 font-weight-bold ">{{ initials }}</span>
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

      <v-card min-width="220">
        <v-list density="compact">
          <v-list-item @click="onLogout"  prepend-icon="mdi-logout-variant">
            <v-list-item-title>{{ t('Logout') }}</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-card>
    </v-menu>
  </v-navigation-drawer>
</template>

<script setup>
  import { ref, watch, computed, onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute } from 'vue-router';

  // Stores
  import { useLoginRepository } from '@/stores/LoginRepository';
  import { useUsersRepository } from '@/stores/UsersRepository';

  const route = useRoute();
  const { locale, t } = useI18n();

  const isRtl = ref(locale.value === 'fa');
  watch(locale, (l) => (isRtl.value = l === 'fa'));

  // repos
  const auth = useLoginRepository();
  const usersRepo = useUsersRepository(); // kept for future user actions if needed

  // Ensure we have current user info (image/name/username) after reload
  onMounted(async () => {
    try {
      // call a method that ensures we have /me loaded if missing
      (await auth.ensureMe?.()) ?? auth.fetchUser?.();
    } catch {}
  });

  // profile menu
  const profileMenu = ref(false);

  // derive profile display
  // handle { user: {...} } or flat {...}
  const userObj = computed(() => {
    const u = auth.user;
    if (!u) return null;
    return u.name ? u : u.user ?? null;
  });

  const displayName = computed(
    () =>
      userObj.value?.name ??
      userObj.value?.username ?? // fallback if no name set
      t('Guest')
  );

  const usernameAt = computed(() => {
    const uname = userObj.value?.username;
    return uname ? `@${uname}` : '@—';
  });

  const avatarSrc = computed(() => {
    // API during login gave "image" (relative path) but not "image_url"
    const imgUrl = userObj.value?.image_url;
    const imgRel = userObj.value?.image; // e.g. "avatars/xxx.png"
    // If only relative exists, let <v-img> resolve it (or prefix yourself if needed)
    return imgUrl || (imgRel ? `/storage/${imgRel}` : null);
  });

  const initials = computed(() => {
    const n = (userObj.value?.name || '').trim();
    if (!n) return '😊';
    const parts = n.split(/\s+/).slice(0, 2);
    return parts.map((p) => p[0]?.toUpperCase() || '').join('');
  });

  // Helper to build named route targets that all live under /app
  const toName = (name) => ({ name });

  /**
   * Permission helpers
   * - item.perm: single permission required
   * - item.anyPerms: at least one of these permissions
   * - item.allPerms: must have all of these permissions
   * If none provided, visible by default.
   */
  const canView = (item) => {
    if (!item) return false;
    // Single perm
    if (item.perm && !auth.can(item.perm)) return false;
    // All-perms
    if (Array.isArray(item.allPerms) && item.allPerms.length) {
      for (const p of item.allPerms) if (!auth.can(p)) return false;
    }
    // Any-perms
    if (Array.isArray(item.anyPerms) && item.anyPerms.length) {
      const ok = item.anyPerms.some((p) => auth.can(p));
      if (!ok) return false;
    }
    return true;
  };

  const withPerms = (node) => ({ ...node }); // placeholder if you want to transform

  // Base menu (add your permission keys here)
  const navigationItems = computed(() =>
    [
      {
        title: t('دشبورد'),
        icon: 'mdi-view-dashboard-outline',
        to: toName('Dashboard'),
        perm: 'dashboards.view',
      },

      {
        title: t('نقشه'),
        icon: 'mdi-map',
        to: { path: '/app/map-afg' },
        perm: 'map-afg.view',
      },

      {
        title: t('فعال و غیر فعال'),
        icon: 'mdi-check',
        to: { path: '/app/activeandinactiveemployees' },
        perm: 'activeandinactiveemployees.view',
      },

      {
        title: t('مرکز معلومات'),
        icon: 'mdi-menu',
        to: { path: '/app/inlineinfo' },
        perm: 'inlineinfo.view',
      },

      {
        title: t('استفاده کننده ها'),
        icon: 'mdi-account',
        to: { path: '/app/users' },
        perm: 'users.view',
      },

      {
        title: t('  کارمندان'),
        icon: 'mdi-account-outline',
        to: { path: '/app/officeemployee' },
        perm: 'officeemployee.view',
      },

      {
        title: t('منابع'),
        icon: 'mdi-pen',
        to: { path: '/app/resources' },
        perm: 'resources.view',
      },

      {
        title: t('اطلاعات'),
        icon: 'mdi-book',
        to: { path: '/app/informations' },
        perm: 'informations.view',
      },

      {
        title: t('واقعات'),
        icon: 'mdi-book-outline',
        to: { path: '/app/events' },
        perm: 'events.view',
      },

      {
        title: t('زندانی های تسلیم شده'),
        icon: 'mdi-lock',
        to: { path: '/app/delevirycaptive' },
        perm: 'captives.view',
      },

      {
        title: t('۵۶ فارمت'),
        icon: 'mdi-view-dashboard-outline',
        to: { path: '/app/fifthyformatsroute' },
        perm: 'fifthyformatsroute.view',
      },

      {
        title: t('بخش حساب مالی'),
        icon: 'mdi-cash-multiple',
        to: { path: '/app/finincalindex' },
        perm: 'finincalindex.view',
      },

      { title: t('نقش ها'), icon: 'mdi-home', to: { path: '/app/roles' }, perm: 'roles.view' },

      // 🔹 Reports group (can have many report items)
      {
        title: t(' راپورها'),
        icon: 'mdi-graph',
        children: [
          {
            title: t('۵۶ فارمت'),
            icon: 'mdi-file-chart',
            to: { path: '/app/fiftyformatreports' },
            perm: 'fiftyformatreports.view',
          },
          {
            title: t(' کارمندان'),
            icon: 'mdi-cash',
            to: { path: '/app/officeemployeereports' },
            perm: 'officeemployeereports.view',
          },
          {
            title: t(' واقعات'),
            icon: 'mdi-cash',
            to: { path: '/app/eventreports' },
            perm: 'eventreports.view',
          },{
            title: t('اطلاعات'),
            icon: 'mdi-cash',
            to: { path: '/app/informationreports' },
            perm: 'informationreports.view',
          },
        ],
      },
    ].map(withPerms)
  );

  /**
   * Filter items by permissions (and trim children accordingly).
   * Groups are shown only if at least one child is visible AND the group itself passes canView().
   */
  const filteredNavigationItems = computed(() => {
    const result = [];
    for (const item of navigationItems.value) {
      // If single node (no children)
      if (!item.children) {
        if (canView(item)) result.push(item);
        continue;
      }
      // Group node
      if (!canView(item)) continue;
      const visibleChildren = item.children.filter((c) => canView(c));
      if (visibleChildren.length) {
        result.push({ ...item, children: visibleChildren });
      }
    }
    return result;
  });

  // Active helpers
  const isActive = (item) => {
    if (item.to?.name) return route.name === item.to.name;
    if (item.to?.path) return route.path === item.to.path;
    return false;
  };

  const isGroupOpen = (groupItem) => {
    if (!groupItem.children?.length) return false;
    return groupItem.children.some(isActive);
  };

  // Actions
  const onLogout = async () => {
    profileMenu.value = false;
    await auth.Logout();
  };
</script>

<style scoped>
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

  .scrollable-content {
    height: calc(99vh - 200px);
    overflow-y: auto;
  }
  .scrollable-content::-webkit-scrollbar {
    width: 0px;
  }

  /* Navigation item styles */
  .nav-item {
    margin: 2px 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
  }

  .nav-item:hover {
    border-radius: 12px !important;
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.1) 0%, rgba(var(--v-theme-primary), 0.05) 100%);
    border-color: rgba(var(--v-theme-primary), 0.2);
    transform: translateX(2px);
  }

  .nav-item-active {
    border-radius: 12px !important;
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.15) 0%, rgba(var(--v-theme-primary), 0.08) 100%);
    border-color: rgba(var(--v-theme-primary), 0.3);
    box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.15);
  }

  .nav-item-active .v-list-item-title {
    color: rgb(var(--v-theme-primary));
    font-weight: 700;
  }

  /* Group styles */
  .group-open {
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.08) 0%, rgba(var(--v-theme-primary), 0.04) 100%);
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
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.08) 0%, rgba(var(--v-theme-primary), 0.04) 100%);
    border-color: rgba(var(--v-theme-primary), 0.15);
    transform: translateX(1px);
  }

  .nav-child-active {
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.12) 0%, rgba(var(--v-theme-primary), 0.06) 100%);
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

  /* Smooth transitions for all interactive elements */
  .v-list-item {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  /* Focus states for accessibility */
  .nav-item:focus-within,
  .nav-child-item:focus-within {
    outline: 2px solid rgba(var(--v-theme-primary), 0.3);
    outline-offset: 2px;
  }
</style>