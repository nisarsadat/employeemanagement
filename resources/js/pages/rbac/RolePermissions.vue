<!-- src/pages/rbac/RolePermissions.vue -->
<template>
  <v-container fluid class="mt-12 pl-6 role-perms" :dir="'rtl'">
    <AppBar
      :title="ps('صلاحیتونه ټاکل')"
      :subtitle="
        repo.currentRole?.name ? `${ps('رول')}: ${repo.currentRole.name}` : ps('د رول صلاحیتونه')
      "
      @toggle-drawer="drawer = !drawer"
    >
      <template #actions>
        <v-tooltip :text="ps('واپس (Escape)')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn icon variant="text" @click="tryGoBack" :disabled="repo.loading" v-bind="props">
              <v-icon>mdi-arrow-left</v-icon>
            </v-btn>
          </template>
        </v-tooltip>
      </template>
    </AppBar>

    <v-card class="mt-6" rounded="xl" variant="flat" border>
      <!-- Top header / stats -->
      <div class="top-strip px-5 py-4">
        <div class="d-flex align-center justify-space-between flex-wrap ga-3">
          <div class="d-flex align-center flex-wrap ga-2">
            <v-badge :content="selectedCount" :max="999" color="success" floating inline>
              <v-chip size="small" variant="tonal" color="primary" class="font-weight-medium">
                <v-icon start size="18">mdi-shield-key-outline</v-icon>
                {{ ps('صلاحیتونه') }}
              </v-chip>
            </v-badge>

            <v-chip size="small" variant="tonal">
              <v-icon start size="18">mdi-format-list-bulleted</v-icon>
              {{ totalCount }} {{ ps('ټول') }}
            </v-chip>

            <v-chip
              v-if="q.trim()"
              size="small"
              variant="tonal"
              color="info"
              class="text-truncate"
              style="max-width: 340px"
            >
              <v-icon start size="18">mdi-magnify</v-icon>
              {{ ps('لټون') }}: "{{ q }}"
              <v-icon end size="16" @click="q = ''" style="cursor: pointer">mdi-close</v-icon>
            </v-chip>

            <v-chip
              v-if="isDirty"
              size="small"
              variant="tonal"
              color="warning"
              @click="showChanges"
            >
              <v-icon start size="18">mdi-alert-circle-outline</v-icon>
              {{ ps('بدلونونه') }}
              <v-progress-circular v-if="saving" indeterminate size="16" width="2" class="ml-1" />
            </v-chip>
          </div>

          <div class="d-flex align-center ga-2 flex-wrap">
            <!-- Undo/Redo -->
            <v-tooltip :text="ps('واپس (Ctrl+Z)')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn
                  variant="tonal"
                  @click="undo"
                  :disabled="repo.loading || !canUndo"
                  v-bind="props"
                  size="small"
                >
                  <v-icon start>mdi-undo</v-icon>
                </v-btn>
              </template>
            </v-tooltip>

            <v-tooltip :text="ps('بیاړو (Ctrl+Y)')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn
                  variant="tonal"
                  @click="redo"
                  :disabled="repo.loading || !canRedo"
                  v-bind="props"
                  size="small"
                >
                  <v-icon start>mdi-redo</v-icon>
                </v-btn>
              </template>
            </v-tooltip>

            <v-divider vertical class="mx-1" />

            <!-- Selection Actions -->
            <v-tooltip :text="ps('یوازې ښکاره شوي وټاکه')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn
                  variant="tonal"
                  @click="selectVisible"
                  :disabled="repo.loading || !visibleFlat.length"
                  v-bind="props"
                  size="small"
                >
                  <v-icon start>mdi-select-search</v-icon>
                </v-btn>
              </template>
            </v-tooltip>

            <v-tooltip :text="ps('ټول وټاکه (Ctrl+A)')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn
                  variant="tonal"
                  @click="selectAll"
                  :disabled="repo.loading || !allFlat.length"
                  v-bind="props"
                  size="small"
                >
                  <v-icon start>mdi-check-all</v-icon>
                </v-btn>
              </template>
            </v-tooltip>

            <v-tooltip :text="ps('پاکول')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn
                  variant="tonal"
                  @click="clearAll"
                  :disabled="repo.loading || !selectedCount"
                  v-bind="props"
                  size="small"
                >
                  <v-icon start>mdi-close-circle-outline</v-icon>
                </v-btn>
              </template>
            </v-tooltip>

            <!-- Save -->
            <v-tooltip :text="ps('ساتل (Ctrl+S)')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn
                  color="primary"
                  :loading="saving"
                  :disabled="!isDirty || repo.loading"
                  @click="save"
                  v-bind="props"
                  size="small"
                >
                  <v-icon start>mdi-content-save-outline</v-icon>
                </v-btn>
              </template>
            </v-tooltip>
          </div>
        </div>
      </div>

      <v-divider />

      <v-card-text class="px-5 pb-5">
        <!-- Search -->
        <v-text-field
          v-model="q"
          :label="ps('د صلاحیتونو لټون...')"
          prepend-inner-icon="mdi-magnify"
          append-inner-icon="mdi-close"
          @click:append-inner="q = ''"
          @keyup.esc="q = ''"
          variant="outlined"
          density="comfortable"
          hide-details
          clearable
          class="mb-4"
          :disabled="repo.loading"
        />

        <!-- Quick Stats -->
        <div v-if="!repo.loading && groupedVisible.length" class="mb-4">
          <v-alert variant="tonal" color="info" density="compact" rounded="lg">
            <div class="d-flex align-center justify-space-between">
              <div class="d-flex align-center ga-3">
                <div class="d-flex align-center ga-1">
                  <v-icon size="18" color="success">mdi-check-circle</v-icon>
                  <span class="text-body-2">{{ selectedCount }} {{ ps('ټاکل شوي') }}</span>
                </div>
                <v-divider vertical />
                <div class="d-flex align-center ga-1">
                  <v-icon size="18" color="primary">mdi-folder</v-icon>
                  <span class="text-body-2">{{ groupedVisible.length }} {{ ps('ګروپونه') }}</span>
                </div>
                <v-divider vertical />
                <div class="d-flex align-center ga-1">
                  <v-icon size="18">mdi-file-document</v-icon>
                  <span class="text-body-2">{{ totalCount }} {{ ps('ټول صلاحیتونه') }}</span>
                </div>
              </div>
              <v-btn size="x-small" variant="text" @click="toggleAllPanels">
                {{ allPanelsOpen ? ps('ټول بند کړئ') : ps('ټول خلاص کړئ') }}
              </v-btn>
            </div>
          </v-alert>
        </div>

        <!-- Loading -->
        <v-row v-if="repo.loading" dense>
          <v-col v-for="i in 12" :key="i" cols="12" sm="6" md="4" lg="3">
            <v-skeleton-loader type="card" class="skeleton-card" />
          </v-col>
        </v-row>

        <!-- Content -->
        <div v-else>
          <!-- Grouped permissions (accordion) -->
          <v-expansion-panels v-model="openPanels" multiple variant="accordion" class="perm-panels">
            <v-expansion-panel
              v-for="g in groupedVisible"
              :key="g.key"
              rounded="xl"
              class="mb-3 perm-group"
            >
              <v-expansion-panel-title class="panel-title">
                <template v-slot:default="{ expanded }">
                  <div class="d-flex align-center justify-space-between w-100 ga-2 flex-wrap">
                    <div class="d-flex align-center ga-2">
                      <v-icon :size="18" :color="expanded ? 'primary' : undefined">
                        {{ expanded ? 'mdi-folder-open-outline' : 'mdi-folder-key-outline' }}
                      </v-icon>

                      <div class="font-weight-bold group-title">
                        {{ groupLabelLocalized(g.key) }}
                      </div>

                      <v-chip size="x-small" variant="tonal">
                        {{ g.items.length }} {{ ps('توکي') }}
                      </v-chip>

                      <v-chip size="x-small" variant="tonal" color="success">
                        {{ countSelectedInGroup(g.items) }} {{ ps('ټاکل شوي') }}
                      </v-chip>

                      <v-chip
                        v-if="countSelectedInGroup(g.items) === g.items.length && g.items.length > 0"
                        size="x-small"
                        variant="flat"
                        color="success"
                      >
                        <v-icon size="12">mdi-check</v-icon>
                      </v-chip>
                    </div>

                    <div class="d-flex align-center ga-2 flex-wrap">
                      <v-btn
                        variant="tonal"
                        size="small"
                        @click.stop="selectGroup(g.items)"
                        :disabled="!g.items.length"
                      >
                        <v-icon start size="18">mdi-check</v-icon>
                        {{ ps('ټول') }}
                      </v-btn>

                      <v-btn
                        variant="tonal"
                        size="small"
                        @click.stop="clearGroup(g.items)"
                        :disabled="!countSelectedInGroup(g.items)"
                      >
                        <v-icon start size="18">mdi-close</v-icon>
                        {{ ps('پاکول') }}
                      </v-btn>

                      <v-btn
                        icon
                        size="small"
                        variant="text"
                        @click.stop="toggleDragMode(g.key)"
                        :color="dragModeGroup === g.key ? 'primary' : undefined"
                      >
                        <v-icon size="18">
                          {{ dragModeGroup === g.key ? 'mdi-drag-variant' : 'mdi-drag' }}
                        </v-icon>
                      </v-btn>
                    </div>
                  </div>
                </template>
              </v-expansion-panel-title>

              <v-expansion-panel-text>
                <!-- Drag and Drop Zone -->
                <v-alert
                  v-if="dragModeGroup === g.key"
                  variant="tonal"
                  color="warning"
                  density="compact"
                  class="mb-3"
                  rounded="lg"
                >
                  <div class="d-flex align-center ga-2">
                    <v-icon>mdi-drag-variant</v-icon>
                    <span class="text-caption">{{ ps('صلاحیتونه په دې ساحه کې ولیږدوئ') }}</span>
                  </div>
                </v-alert>

                <!-- ✅ four columns on lg (lg="3") -->
                <v-row dense>
                  <v-col v-for="p in g.items" :key="p.value" cols="12" sm="6" md="4" lg="3">
                    <v-card
                      class="perm-card"
                      rounded="xl"
                      variant="tonal"
                      :class="{
                        'is-selected': selectedSet.has(p.value),
                        'is-dragging': draggedItem === p.value,
                      }"
                      @click="toggle(p.value)"
                      draggable
                      @dragstart="dragStart(p.value, g.key)"
                      @dragover.prevent="dragOver(p.value)"
                      @dragleave="dragLeave(p.value)"
                      @drop="dropOn(p.value)"
                      @dragend="dragEnd"
                    >
                      <v-card-text class="d-flex align-center justify-space-between py-3">
                        <div class="d-flex flex-column pr-2">
                          <div class="text-body-2 font-weight-bold label">
                            {{ p.label }}
                          </div>

                          <!-- ✅ Pashto-only subtitle (no English slug) -->
                          <div class="text-caption opacity-70 value">
                            {{ labelForPermissionPashtoOnly(p.value) }}
                          </div>
                        </div>

                        <div class="d-flex align-center ga-1">
                          <v-checkbox
                            :model-value="selectedSet.has(p.value)"
                            @update:model-value="toggle(p.value)"
                            density="compact"
                            hide-details
                            class="perm-check"
                            @click.stop
                          />
                          <v-icon v-if="dragModeGroup === g.key" size="16" class="opacity-50">
                            mdi-drag-vertical
                          </v-icon>
                        </div>
                      </v-card-text>

                      <div class="selected-bar" />
                      <div class="glow-ring" />
                      <div class="drop-indicator" v-if="dropTarget === p.value" />
                    </v-card>
                  </v-col>
                </v-row>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>

          <!-- Empty -->
          <v-alert
            v-if="!groupedVisible.length && !repo.loading"
            type="info"
            variant="tonal"
            class="mt-4"
            rounded="lg"
          >
            <div class="d-flex align-center ga-2">
              <v-icon>mdi-information-outline</v-icon>
              <div>
                <div class="font-weight-medium">{{ ps('هیڅ صلاحیت ونه موندل شو') }}</div>
                <div class="text-caption opacity-80">
                  {{ ps('لټون بدل کړئ یا یې پاک کړئ.') }}
                </div>
              </div>
            </div>
          </v-alert>

          <!-- Selection Summary -->
          <v-card v-if="selectedCount > 0 && !repo.loading" variant="tonal" class="mt-4" rounded="lg">
            <v-card-text class="py-3">
              <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center ga-3">
                  <v-avatar size="36" color="primary" variant="tonal">
                    <v-icon color="primary">mdi-shield-check</v-icon>
                  </v-avatar>
                  <div>
                    <div class="font-weight-bold">
                      {{ selectedCount }} {{ ps('صلاحیتونه ټاکل شوي') }}
                    </div>
                    <div class="text-caption opacity-70">
                      {{ ps('په') }} {{ selectedGroupsCount }} {{ ps('ګروپونو کې') }}
                    </div>
                  </div>
                </div>
                <v-btn
                  color="primary"
                  variant="flat"
                  @click="save"
                  :loading="saving"
                  :disabled="!isDirty"
                >
                  <v-icon start>mdi-content-save-check</v-icon>
                  {{ ps('ساتل') }}
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </div>

        <!-- Actions -->
        <div class="actions-bar d-flex justify-end ga-2 mt-6">
          <v-btn variant="text" @click="tryGoBack" :disabled="repo.loading || saving">
            {{ ps('لغوه') }}
          </v-btn>

          <v-btn
            color="primary"
            :loading="saving"
            :disabled="!isDirty || repo.loading"
            @click="save"
          >
            <v-icon start>mdi-content-save-outline</v-icon>
            {{ ps('ساتل') }}
            <v-badge v-if="selectedCount > 0" :content="selectedCount" inline color="white" class="ml-2" />
          </v-btn>
        </div>
      </v-card-text>
    </v-card>

    <!-- Changes Summary Dialog -->
    <v-dialog v-model="changesDialog" max-width="600">
      <v-card rounded="xl">
        <v-card-title class="font-weight-bold">
          <v-icon start color="warning">mdi-alert-circle</v-icon>
          {{ ps('د بدلونونو لنډیز') }}
        </v-card-title>
        <v-card-text>
          <v-list density="compact">
            <v-list-item>
              <template v-slot:prepend>
                <v-avatar size="32" color="success" variant="tonal">
                  <v-icon size="18">mdi-plus</v-icon>
                </v-avatar>
              </template>
              <v-list-item-title>{{ addedCount }} {{ ps('نوي اضافه شوي') }}</v-list-item-title>
            </v-list-item>
            <v-list-item>
              <template v-slot:prepend>
                <v-avatar size="32" color="error" variant="tonal">
                  <v-icon size="18">mdi-minus</v-icon>
                </v-avatar>
              </template>
              <v-list-item-title>{{ removedCount }} {{ ps('لرې شوي') }}</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions class="justify-end ga-2">
          <v-btn variant="text" @click="changesDialog = false">{{ ps('بندول') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Unsaved changes dialog -->
    <v-dialog v-model="leaveDialog" max-width="520">
      <v-card rounded="xl">
        <v-card-title class="font-weight-bold">
          <v-icon start color="warning">mdi-alert</v-icon>
          {{ ps('بدلونونه خوندي شوي نه دي') }}
        </v-card-title>
        <v-card-text class="opacity-80">
          {{ ps('تاسو بدلونونه کړي دي. غواړئ پرته له ساتلو ووځئ؟') }}
        </v-card-text>
        <v-card-actions class="justify-end ga-2">
          <v-btn variant="text" @click="leaveDialog = false">{{ ps('بېرته') }}</v-btn>
          <v-btn color="error" variant="tonal" @click="forceGoBack">
            {{ ps('وتل') }}
          </v-btn>
          <v-btn color="primary" @click="saveAndGoBack">
            <v-icon start>mdi-content-save</v-icon>
            {{ ps('ساتل او وتل') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Success Snackbar -->
    <v-snackbar v-model="saveSuccess" color="success" timeout="3000">
      <div class="d-flex align-center ga-2">
        <v-icon>mdi-check-circle</v-icon>
        <span>{{ ps('صلاحیتونه په بریالیتوب سره ساتل شول!') }}</span>
      </div>
    </v-snackbar>
  </v-container>
</template>

<script setup>
  import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import AppBar from '@/components/AppBar.vue';
  import { useRbacRepository } from '@/stores/RbacRepository';

  // ✅ Pashto labels + mapper
  import {
    mapPermNamesToItemsBilingual as mapPermNamesToItems,
    MODULE_PASHTO,
    labelForPermissionPashtoOnly,
  } from '../../../composables/usePermI18n';

  const ps = (s) => s;

  const repo = useRbacRepository();
  const route = useRoute();
  const router = useRouter();

  const drawer = ref(false);
  const q = ref('');
  const openPanels = ref([]);
  const saving = ref(false);
  const saveSuccess = ref(false);
  const changesDialog = ref(false);
  const leaveDialog = ref(false);

  // Drag & Drop
  const draggedItem = ref(null);
  const dragModeGroup = ref(null);
  const dropTarget = ref(null);
  const dragOriginGroup = ref(null);

  // History for undo/redo
  const history = ref([]);
  const historyIndex = ref(-1);

  const roleId = computed(() => Number(route.params.id));

  /** Build items */
  const permItems = computed(() => mapPermNamesToItems(repo.allPermissionNames || []));

  /** Selected state */
  const selectedSet = ref(new Set());
  const selectedCount = computed(() => selectedSet.value.size);

  /** History management */
  const pushToHistory = () => {
    const snapshot = [...selectedSet.value];
    history.value = history.value.slice(0, historyIndex.value + 1);
    history.value.push(snapshot);
    historyIndex.value = history.value.length - 1;
  };

  const undo = () => {
    if (historyIndex.value > 0) {
      historyIndex.value--;
      selectedSet.value = new Set(history.value[historyIndex.value]);
    }
  };

  const redo = () => {
    if (historyIndex.value < history.value.length - 1) {
      historyIndex.value++;
      selectedSet.value = new Set(history.value[historyIndex.value]);
    }
  };

  const canUndo = computed(() => historyIndex.value > 0);
  const canRedo = computed(() => historyIndex.value < history.value.length - 1);

  /** Change detection */
  const initialSnapshot = ref('');
  const serializeSet = (set) => JSON.stringify([...set].sort());
  const isDirty = computed(() => serializeSet(selectedSet.value) !== initialSnapshot.value);

  /** Change statistics */
  const initialSet = computed(() => new Set(JSON.parse(initialSnapshot.value || '[]')));
  const addedCount = computed(() => {
    let count = 0;
    for (const perm of selectedSet.value) {
      if (!initialSet.value.has(perm)) count++;
    }
    return count;
  });
  const removedCount = computed(() => {
    let count = 0;
    for (const perm of initialSet.value) {
      if (!selectedSet.value.has(perm)) count++;
    }
    return count;
  });

  /** Flat helpers */
  const allFlat = computed(() => permItems.value.map((x) => x.value));

  /** Search filter */
  const filteredFlatItems = computed(() => {
    const s = (q.value || '').trim().toLowerCase();
    let items = permItems.value;

    if (s) {
      items = items.filter((x) => {
        const label = String(x.label || '').toLowerCase();
        const value = String(x.value || '').toLowerCase();
        return label.includes(s) || value.includes(s);
      });
    }

    return items;
  });

  /** Grouping */
  const groupKeyOf = (permValue) => {
    const v = String(permValue || '').trim();
    if (!v) return 'other';

    if (v.includes('.')) return v.split('.')[0].toLowerCase();
    if (v.includes(':')) return v.split(':')[0].toLowerCase();

    return v.toLowerCase();
  };

  /** Humanize for fallback */
  const humanizeKey = (k) =>
    String(k || '')
      .replace(/[_-]+/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();

  /** ✅ Group label: Pashto if known, else fallback */
  const groupLabelLocalized = (key) => {
    const k = String(key || '').trim();
    return MODULE_PASHTO[k] || humanizeKey(k);
  };

  /** Grouped permissions */
  const groupedVisible = computed(() => {
    const map = new Map();

    for (const item of filteredFlatItems.value) {
      const key = groupKeyOf(item.value);
      if (!map.has(key)) map.set(key, []);
      map.get(key).push(item);
    }

    const groups = [...map.entries()].map(([key, items]) => ({
      key,
      label: groupLabelLocalized(key),
      items: items.sort((a, b) => String(a.label).localeCompare(String(b.label))),
    }));

    groups.sort((a, b) => {
      if (a.key === 'other') return 1;
      if (b.key === 'other') return -1;
      return String(a.label).localeCompare(String(b.label));
    });

    return groups;
  });

  /** Visible flat */
  const visibleFlat = computed(() => filteredFlatItems.value.map((x) => x.value));

  /** Counters */
  const totalCount = computed(() => permItems.value.length);
  const selectedGroupsCount = computed(() => {
    const groups = new Set();
    for (const perm of selectedSet.value) {
      groups.add(groupKeyOf(perm));
    }
    return groups.size;
  });

  /** Panel management */
  const allPanelsOpen = computed(() => {
    return openPanels.value.length === groupedVisible.value.length;
  });

  const toggleAllPanels = () => {
    if (allPanelsOpen.value) {
      openPanels.value = [];
    } else {
      openPanels.value = groupedVisible.value.map((g) => g.key);
    }
  };

  /** Selection actions */
  const toggle = (permName) => {
    pushToHistory();
    const set = new Set(selectedSet.value);
    if (set.has(permName)) set.delete(permName);
    else set.add(permName);
    selectedSet.value = set;
  };

  const selectAll = () => {
    pushToHistory();
    selectedSet.value = new Set(allFlat.value);
  };

  const selectVisible = () => {
    pushToHistory();
    const set = new Set(selectedSet.value);
    for (const v of visibleFlat.value) set.add(v);
    selectedSet.value = set;
  };

  const clearAll = () => {
    pushToHistory();
    selectedSet.value = new Set();
  };

  const countSelectedInGroup = (items) => {
    let c = 0;
    for (const it of items) if (selectedSet.value.has(it.value)) c++;
    return c;
  };

  const selectGroup = (items) => {
    pushToHistory();
    const set = new Set(selectedSet.value);
    for (const it of items) set.add(it.value);
    selectedSet.value = set;
  };

  const clearGroup = (items) => {
    pushToHistory();
    const set = new Set(selectedSet.value);
    for (const it of items) set.delete(it.value);
    selectedSet.value = set;
  };

  /** Drag & Drop */
  const toggleDragMode = (groupKey) => {
    dragModeGroup.value = dragModeGroup.value === groupKey ? null : groupKey;
  };

  const dragStart = (permName, groupKey) => {
    if (dragModeGroup.value !== groupKey) return;
    draggedItem.value = permName;
    dragOriginGroup.value = groupKey;
  };

  const dragOver = (permName) => {
    if (!draggedItem.value) return;
    dropTarget.value = permName;
  };

  const dragLeave = () => {
    dropTarget.value = null;
  };

  const dropOn = (permName) => {
    if (!draggedItem.value || draggedItem.value === permName) return;

    pushToHistory();
    const set = new Set(selectedSet.value);
    const hasDragged = set.has(draggedItem.value);
    const hasTarget = set.has(permName);

    if (hasDragged) set.delete(draggedItem.value);
    else set.add(draggedItem.value);

    if (hasTarget) set.delete(permName);
    else set.add(permName);

    selectedSet.value = set;
    dropTarget.value = null;
  };

  const dragEnd = () => {
    draggedItem.value = null;
    dropTarget.value = null;
    dragOriginGroup.value = null;
  };

  /** Change display */
  const showChanges = () => {
    changesDialog.value = true;
  };

  /** Navigation */
  const tryGoBack = () => {
    if (repo.loading || saving.value) return;
    if (isDirty.value) {
      leaveDialog.value = true;
      return;
    }
    router.back();
  };

  const forceGoBack = () => {
    leaveDialog.value = false;
    router.back();
  };

  const saveAndGoBack = async () => {
    await save();
    if (!saving.value) {
      router.back();
    }
  };

  /** Load data */
  const load = async () => {
    try {
      await repo.fetchRole(roleId.value);
      await repo.fetchAllPermissionNames();

      if (!repo.currentRole) {
        router.push({ name: 'roles' });
        return;
      }

      const perms = repo.currentRole?.permissions || [];
      selectedSet.value = new Set(perms);
      initialSnapshot.value = serializeSet(selectedSet.value);

      // Initialize history
      history.value = [[...selectedSet.value]];
      historyIndex.value = 0;

      // Auto open first few groups
      if (groupedVisible.value.length > 0) {
        openPanels.value = groupedVisible.value.slice(0, 3).map((g) => g.key);
      }
    } catch (error) {
      console.error('Failed to load role permissions:', error);
    }
  };

  const save = async () => {
    try {
      saving.value = true;
      await repo.assignPermissionsToRole(repo.currentRole.id, [...selectedSet.value]);
      initialSnapshot.value = serializeSet(selectedSet.value);
      saveSuccess.value = true;

      // ✅ go back to Roles page after success
      setTimeout(() => {
        router.push({ name: 'roles' });
      }, 400);
    } catch (error) {
      console.error('Error saving permissions:', error);
    } finally {
      saving.value = false;
    }
  };

  /** Lifecycle */
  onMounted(() => {
    load();

    const handleKeyDown = (event) => {
      if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return;

      const ctrlKey = event.ctrlKey || event.metaKey;

      if ((ctrlKey && event.key === 's') || event.key === 'S') {
        event.preventDefault();
        if (isDirty.value) save();
      } else if ((ctrlKey && event.key === 'z') || event.key === 'Z') {
        event.preventDefault();
        if (canUndo.value) undo();
      } else if ((ctrlKey && event.key === 'y') || event.key === 'Y') {
        event.preventDefault();
        if (canRedo.value) redo();
      } else if ((ctrlKey && event.key === 'a') || event.key === 'A') {
        event.preventDefault();
        selectAll();
      } else if (event.key === 'Escape') {
        event.preventDefault();
        tryGoBack();
      }
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
  });

  onBeforeUnmount(() => {
    if (isDirty.value && !saving.value) {
      const confirmLeave = window.confirm(
        ps('بدلونونه خوندي شوي نه دي. ایا په رښتیا غواړئ پاڼه پرېږدئ؟')
      );
      if (!confirmLeave) {
        router.push(route.path);
      }
    }
  });
</script>

<style scoped>
  .role-perms {
    font-family: inherit;
  }

  /* Spacing */
  .ga-1 {
    gap: 4px;
  }
  .ga-2 {
    gap: 8px;
  }
  .ga-3 {
    gap: 12px;
  }

  /* Top strip */
  .top-strip {
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.02), transparent);
    backdrop-filter: blur(10px);
  }

  @media (prefers-color-scheme: dark) {
    .top-strip {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent);
    }
  }

  /* Panels */
  .perm-panels :deep(.v-expansion-panel) {
    border: 1px solid rgba(0, 0, 0, 0.06);
    overflow: hidden;
    box-shadow: 0 8px 26px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
  }

  .perm-panels :deep(.v-expansion-panel:hover) {
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
  }

  .panel-title {
    min-height: 62px;
    transition: background-color 0.3s ease;
  }

  .group-title {
    letter-spacing: 0.2px;
    font-size: 1.1rem;
  }

  /* Cards */
  .perm-card {
    cursor: pointer;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.06);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
  }

  .perm-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
    border-color: rgba(0, 0, 0, 0.12);
  }

  .perm-card.is-selected {
    border-color: rgba(25, 118, 210, 0.55);
    box-shadow: 0 16px 40px rgba(25, 118, 210, 0.15);
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.05), rgba(25, 118, 210, 0.02));
  }

  .perm-card.is-dragging {
    opacity: 0.5;
    transform: scale(0.95);
  }

  /* Selected bar animation */
  .selected-bar {
    position: absolute;
    inset-inline: 0;
    bottom: 0;
    height: 3px;
    opacity: 0;
    background: linear-gradient(
      90deg,
      rgba(25, 118, 210, 0) 0%,
      rgba(25, 118, 210, 0.8) 50%,
      rgba(25, 118, 210, 0) 100%
    );
    transition: opacity 0.3s ease;
    animation: shimmer 2s infinite;
  }

  .perm-card.is-selected .selected-bar {
    opacity: 1;
  }

  @keyframes shimmer {
    0% {
      background-position: -200% center;
    }
    100% {
      background-position: 200% center;
    }
  }

  /* Glow ring */
  .glow-ring {
    position: absolute;
    inset: -40%;
    background: radial-gradient(
      circle,
      rgba(25, 118, 210, 0.2) 0%,
      rgba(25, 118, 210, 0.1) 40%,
      rgba(25, 118, 210, 0) 70%
    );
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    animation: pulse 2s infinite;
  }

  .perm-card.is-selected .glow-ring {
    opacity: 0.8;
  }

  @keyframes pulse {
    0%,
    100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.05);
    }
  }

  /* Drop indicator */
  .drop-indicator {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border: 2px dashed rgba(25, 118, 210, 0.5);
    border-radius: 16px;
    pointer-events: none;
    animation: pulse-border 1.5s infinite;
  }

  @keyframes pulse-border {
    0%,
    100% {
      opacity: 0.5;
    }
    50% {
      opacity: 1;
    }
  }

  /* Checkbox styling */
  .perm-check :deep(.v-selection-control__input) {
    margin-inline-start: 8px;
  }

  .perm-check :deep(.mdi-check) {
    animation: check-pop 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  @keyframes check-pop {
    0% {
      transform: scale(0.5);
      opacity: 0;
    }
    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  /* Text */
  .label {
    line-height: 1.2;
    font-size: 0.9rem;
  }

  .value {
    word-break: break-all;
    font-family: inherit;
    font-size: 0.78rem;
  }

  /* Skeleton */
  .skeleton-card :deep(.v-skeleton-loader__bone) {
    border-radius: 18px;
  }

  .skeleton-card :deep(.v-skeleton-loader__bone::after) {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: loading 1.5s infinite;
  }

  @keyframes loading {
    0% {
      transform: translateX(-100%);
    }
    100% {
      transform: translateX(100%);
    }
  }

  /* Sticky actions */
  .actions-bar {
    position: sticky;
    bottom: 20px;
    padding: 16px;
    background: linear-gradient(
      180deg,
      transparent,
      rgba(255, 255, 255, 0.95) 20%,
      rgba(255, 255, 255, 1) 40%
    );
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.1);
    z-index: 100;
  }

  @media (prefers-color-scheme: dark) {
    .actions-bar {
      background: linear-gradient(
        180deg,
        transparent,
        rgba(30, 30, 30, 0.95) 20%,
        rgba(30, 30, 30, 1) 40%
      );
      box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.3);
    }

    .perm-card {
      background: rgba(255, 255, 255, 0.02);
      border-color: rgba(255, 255, 255, 0.08);
    }

    .perm-panels :deep(.v-expansion-panel) {
      border-color: rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.02);
    }
  }

  /* Responsive adjustments */
  @media (max-width: 960px) {
    .perm-card {
      margin-bottom: 8px;
    }

    .top-strip .d-flex {
      flex-direction: column;
      align-items: flex-start !important;
    }

    .actions-bar {
      flex-direction: column;
      align-items: stretch;
    }

    .actions-bar .v-btn {
      width: 100%;
      margin-bottom: 8px;
    }
  }

  /* Smooth scroll */
  .perm-panels {
    scroll-behavior: smooth;
  }

  /* Focus styles for accessibility */
  .perm-card:focus-visible {
    outline: 2px solid rgba(25, 118, 210, 0.8);
    outline-offset: 2px;
  }

  /* Loading overlay */
  .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(4px);
  }

  @media (prefers-color-scheme: dark) {
    .loading-overlay {
      background: rgba(0, 0, 0, 0.8);
    }
  }
</style>
