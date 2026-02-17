<template>
  <AppBar title="نقشه"></AppBar>

  <div class="map-wrap" :dir="isRtl ? 'rtl' : 'ltr'">
    <div ref="mapEl" class="map"></div>

    <!-- ✅ OFFLINE TOP TOOLS (NO ONLINE SEARCH) -->
    <div class="map-top-tools" @click.stop>
      <PmtilesSearchMap class="search-panel" @flyTo="flyTo" />

      <!-- ✅ Points Menu Button -->
      <v-menu v-model="pointsMenuOpen" :close-on-content-click="false" location="bottom">
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            class="points-btn"
            variant="elevated"
            density="comfortable"
            rounded="xl"
            prepend-icon="mdi-format-list-bulleted"
          >
            نقطې ({{ allMapItems.length }})
          </v-btn>
        </template>

        <v-card class="points-menu-card" rounded="xl">
          <div class="points-menu-head">
            <div class="points-title">په نقشه کې نقطې</div>
            <div class="points-sub">ټول: {{ allMapItems.length }}</div>
          </div>

          <v-divider />

          <v-card-text class="points-menu-body">
            <!-- Types -->
            <div v-for="g in groupedPoints" :key="g.type" class="type-block">
              <div class="type-head">
                <div class="type-name">
                  <span class="type-dot"></span>
                  {{ g.label }}
                </div>
                <div class="type-count">{{ g.count }}</div>
              </div>

              <v-list density="compact" class="type-list" nav>
                <v-list-item
                  v-for="it in g.items"
                  :key="`${it.type}:${it.id}`"
                  @click="
                    () => {
                      pointsMenuOpen = false;
                      flyToItem(it);
                      openDetailsMenu(it);
                    }
                  "
                >
                  <template #prepend>
                    <v-icon
                      :color="
                        it.type === 'resource'
                          ? it.is_active
                            ? 'green-darken-2'
                            : 'red-darken-2'
                          : 'primary'
                      "
                    >
                      {{ safeMdiFor(it) }}
                    </v-icon>
                  </template>

                  <v-list-item-title class="mini-title">
                    {{ it.title || it.id }}
                  </v-list-item-title>

                  <v-list-item-subtitle class="mini-sub">
                    {{ it.subtitle }}
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </div>

            <div v-if="!groupedPoints.length" class="empty-points">هېڅ نقطه ونه موندل شوه.</div>
          </v-card-text>

          <v-divider />

          <div class="points-menu-actions">
            <v-btn variant="text" rounded="lg" @click="() => (pointsMenuOpen = false)"> تړل </v-btn>
          </div>
        </v-card>
      </v-menu>
    </div>

    <!-- ✅ Separate details menu drawer -->
    <MapDetailsDrawer
      v-model="detailsOpen"
      :rtl="isRtl"
      :loading="detailsLoading"
      :item="selectedItem"
      :data="detailsData"
      @flyToItem="flyToItem"
    />
  </div>
</template>

<script setup>
  import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import maplibregl from 'maplibre-gl';
  import 'maplibre-gl/dist/maplibre-gl.css';
  import { PMTiles, Protocol } from 'pmtiles';
  import PmtilesSearchMap from './PmtilesSearchMap.vue';
  import MapDetailsDrawer from './MapDetailsDrawer.vue';
  import { ICON_MAP } from '../../../constants/moduleIcons';

  import { useMapBusStore } from '../../stores/MapBusStore';
  import AppBar from '@/components/AppBar.vue';

  const mapBus = useMapBusStore();

  const { locale } = useI18n();
  const isRtl = computed(() =>
    ['fa', 'prs', 'ps', 'ar'].includes(String(locale.value || '').toLowerCase())
  );

  const mapEl = ref(null);
  const map = ref(null);
  let protocol;
  const pmtilesUrl = '/pmtiles-range/afghanistan.pmtiles';

  // highlighted marker from mapBus flyTarget (OFFLINE)
  let highlightMarker = null;

  /* ---------- separate details drawer state ---------- */
  const detailsOpen = ref(false);
  const detailsLoading = ref(false);
  const selectedItem = ref(null);
  const detailsData = ref(null);

  const loadingAll = ref(false);

  /* ---------- ✅ points menu ---------- */
  const pointsMenuOpen = ref(false);

  /* ============================================================
 ✅ MODULE SOURCES (your endpoints)
============================================================ */
  // ✅ MAP sources with BOTH API paths + Router paths (based on your src/router/index.js)

  const MAP_SOURCES = [
    // ---------------- Core ----------------
    {
      type: 'office_employee',
      label: 'کارمند',
      list: '/api/office-employees',
      show: (id) => `/api/office-employees/${id}`,
      route: '/app/officeemployee',
      routeForm: (id) => (id ? `/app/officeemployeeform/${id}` : `/app/officeemployeeform`),
    },
    {
      type: 'resource',
      label: 'منبع',
      list: '/api/resources',
      show: (id) => `/api/resources/${id}`,
      route: '/app/resources',
      routeForm: (id) => (id ? `/app/resourcesform/${id}` : `/app/resourcesform`),
    },
    {
      type: 'information',
      label: 'اطلاعات',
      list: '/api/informations',
      show: (id) => `/api/informations/${id}`,
      route: '/app/informations',
      routeForm: (id) => (id ? `/app/informationform/${id}` : `/app/informationform`),
      routePrint: (id) => `/app/informations/print/${id}`,
    },
    {
      type: 'captive_delivery',
      label: 'تحویل اسیر',
      list: '/api/captive-deliveries',
      show: (id) => `/api/captive-deliveries/${id}`,
      route: '/app/delevirycaptive',
      routeForm: (id) => `/app/delevirycaptiveform/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'event',
      label: 'رویداد',
      list: '/api/events',
      show: (id) => `/api/events/${id}`,
      route: '/app/events',
      routeForm: (id) => (id ? `/app/eventsform/${id}` : `/app/eventsform`),
    },

    // ---------------- 56 Formats (Base) ----------------
    {
      type: 'fasad',
      label: 'شر او فساد',
      list: '/api/fasads',
      show: (id) => `/api/fasads/${id}`,
      route: '/app/fasads',
      routeForm: (id) => (id ? `/app/fasadform/${id}` : `/app/fasadform`),
    },
    {
      type: 'saeen_fasad',
      label: 'ساعین بالفساد',
      list: '/api/saeen-fasads',
      show: (id) => `/api/saeen-fasads/${id}`,
      route: '/app/saeenfasad',
      routeForm: (id) => (id ? `/app/saeen-fasadform/${id}` : `/app/saeen-fasadform`),
    },
    {
      type: 'heist',
      label: 'سرقت کوونکي',
      list: '/api/heists',
      show: (id) => `/api/heists/${id}`,
      route: '/app/heist',
      routeForm: (id) => (id ? `/app/heistform/${id}` : `/app/heistform`),
    },
    {
      type: 'captive',
      label: 'راخوشي شوي بندیان',
      list: '/api/captives',
      show: (id) => `/api/captives/${id}`,
      route: '/app/captives',
      routeForm: (id) => (id ? `/app/captiveform/${id}` : `/app/captiveform`),
    },
    {
      type: 'mulkano',
      label: 'د کوڅو نمایندګان',
      list: '/api/mulkanos',
      show: (id) => `/api/mulkanos/${id}`,
      route: '/app/mulkanos',
      routeForm: (id) => (id ? `/app/mulkanoform/${id}` : `/app/mulkanoform`),
    },
    {
      type: 'entry_exit',
      label: 'د ښار اړوند دخولي/خروجي دروازې او فرعي لارې',
      list: '/api/entry-exits',
      show: (id) => `/api/entry-exits/${id}`,
      route: '/app/entry-exits',
      routeForm: (id) => (id ? `/app/entryexitform/${id}` : `/app/entryexitform`),
    },
    {
      type: 'sarafyano',
      label: 'صرافیانې',
      list: '/api/sarafyanos',
      show: (id) => `/api/sarafyanos/${id}`,
      route: '/app/sarafyanos',
      routeForm: (id) => (id ? `/app/sarafyanosform/${id}` : `/app/sarafyanosform`),
    },
    {
      type: 'clubs',
      label: 'ورزشي کلپونه',
      list: '/api/clubs',
      show: (id) => `/api/clubs/${id}`,
      route: '/app/clubs',
      routeForm: (id) => (id ? `/app/clubform/${id}` : `/app/clubform`),
    },
    {
      type: 'mahajerat',
      label: 'نوي راغلي کورونه',
      list: '/api/mahajerats',
      show: (id) => `/api/mahajerats/${id}`,
      route: '/app/mahajerats',
      routeForm: (id) => (id ? `/app/mahajeratform/${id}` : `/app/mahajeratform`),
    },
    {
      type: 'soldier',
      label: 'پخواني چارواکې',
      list: '/api/soldiers',
      show: (id) => `/api/soldiers/${id}`,
      route: '/app/soldiers',
      routeForm: (id) => `/app/soldierform/${id ?? ''}`.replace(/\/$/, ''),
    },

    // ---------------- Format One ----------------
    {
      type: 'mawad',
      label: 'د نشه یی توکو پلورونکي',
      list: '/api/mawads',
      show: (id) => `/api/mawads/${id}`,
      route: '/app/mawads',
      routeForm: (id) => `/app/mawadform/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'lost_mujremeen',
      label: 'نادرکه شوي مجرمین',
      list: '/api/lost-mujremeens',
      show: (id) => `/api/lost-mujremeens/${id}`,
      route: '/app/lostmujremeens',
      routeForm: (id) => (id ? `/app/lost-mujremeen-form/${id}` : `/app/lost-mujremeen-form`),
    },
    {
      type: 'old_soldiers_homes',
      label: 'د پخواني چاروکو کورونه',
      list: '/api/old-soldiers-homes',
      show: (id) => `/api/old-soldiers-homes/${id}`,
      route: '/app/oldsoldiershomes',
      routeForm: (id) => (id ? `/app/old-soldiers-home-form/${id}` : `/app/old-soldiers-home-form`),
    },
    {
      type: 'robbers',
      label: 'اختطاف ګران',
      list: '/api/robbers',
      show: (id) => `/api/robbers/${id}`,
      route: '/app/robbers',
      routeForm: (id) => (id ? `/app/robbers/${id}` : `/app/robbers/create`),
    },
    {
      type: 'non_responsable_people',
      label: 'غیر مسؤوله وسله وال',
      list: '/api/non-responsable-people',
      show: (id) => `/api/non-responsable-people/${id}`,
      route: '/app/nonresponsablepeople',
      routeForm: (id) =>
        id ? `/app/non-responsable-people-form/${id}` : `/app/non-responsable-people-form`,
    },
    {
      type: 'mashkok_homes',
      label: 'مشکوکو کورونه',
      list: '/api/mashkok-homes',
      show: (id) => `/api/mashkok-homes/${id}`,
      route: '/app/mashkokhomes',
      routeForm: (id) => `/app/mashkok-home/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'damage_places',
      label: 'آسیب پذیره ساحې',
      list: '/api/damage-places',
      show: (id) => `/api/damage-places/${id}`,
      route: '/app/damageplaces',
      routeForm: (id) => `/app/damage-places/form/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'damage_rasan_places',
      label: 'آسیب رسانه ساحې',
      list: '/api/damage-rasan-places',
      show: (id) => `/api/damage-rasan-places/${id}`,
      route: '/app/damagerasanplaces',
      routeForm: (id) => (id ? `/app/damage-rasan-placeform/${id}` : `/app/damage-rasan-placeform`),
    },
    {
      type: 'mashkok_shops',
      label: 'مشکوک دوکانونه',
      list: '/api/mashkok-shops',
      show: (id) => `/api/mashkok-shops/${id}`,
      route: '/app/mashkokshops',
      routeForm: (id) => `/app/mashkok-shops/form/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'luchakanos',
      label: 'لوچکان او بی بند و باره افراد',
      list: '/api/luchakanos',
      show: (id) => `/api/luchakanos/${id}`,
      route: '/app/luchakanos',
      routeForm: (id) => (id ? `/app/luchakanoform/${id}` : `/app/luchakanoform`),
    },

    // ---------------- Format Two ----------------
    {
      type: 'schools',
      label: 'ښونځیو او روزنیز مرکزونو او روزنیز مراکز',
      list: '/api/schools',
      show: (id) => `/api/schools/${id}`,
      route: '/app/schools',
      routeForm: (id) => `/app/schools/form/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'hado_bus_stops',
      label: 'هډې',
      list: '/api/hado-bus-stops',
      show: (id) => `/api/hado-bus-stops/${id}`,
      route: '/app/hadobusstops',
      routeForm: (id) => (id ? `/app/hadabusstopform/${id}` : `/app/hadabusstopform`),
    },
    {
      type: 'transport_companies',
      label: 'ترانسپورتي شرکتونه',
      list: '/api/transport-companies',
      show: (id) => `/api/transport-companies/${id}`,
      route: '/app/transportcompanies',
      routeForm: (id) =>
        id ? `/app/transport-companies/${id}/edit` : `/app/transport-companies/new`,
    },
    {
      type: 'car_sales_companies',
      label: 'موټر پلورونځی',
      list: '/api/car-sales-companies',
      show: (id) => `/api/car-sales-companies/${id}`,
      route: '/app/carsalescompanies',
      routeForm: (id) => (id ? `/app/car-sales-companies/${id}` : `/app/car-sales-companies/new`),
    },
    {
      type: 'parkings',
      label: 'موټرو تمځای(پارکینګ)',
      list: '/api/parkings',
      show: (id) => `/api/parkings/${id}`,
      route: '/app/parkings',
      routeForm: (id) => (id ? `/app/parkingform/${id}` : `/app/parkingform`),
    },
    {
      type: 'banks',
      label: 'بانکونه',
      list: '/api/banks',
      show: (id) => `/api/banks/${id}`,
      route: '/app/banks',
      routeForm: (id) => (id ? `/app/bankform/${id}` : `/app/bankform`),
    },
    {
      type: 'apartments',
      label: 'رهایشي بلاکونه',
      list: '/api/apartments',
      show: (id) => `/api/apartments/${id}`,
      route: '/app/apartments',
      routeForm: (id) => (id ? `/app/apartmentform/${id}` : `/app/apartmentform`),
    },
    {
      type: 'rahnoma_mamlats',
      label: 'د معاملاتو رهنما',
      list: '/api/rahnoma-mamlats',
      show: (id) => `/api/rahnoma-mamlats/${id}`,
      route: '/app/rahnomamamlats',
      routeForm: (id) => (id ? `/app/rahnoma-mamlats/${id}` : `/app/rahnoma-mamlats/create`),
    },
    {
      type: 'hosppitals',
      label: 'روغتوتونه او صحي مراکز',
      list: '/api/hosppitals',
      show: (id) => `/api/hosppitals/${id}`,
      route: '/app/hosppitals',
      routeForm: (id) => (id ? `/app/hospitalform/${id}` : `/app/hospitalform`),
    },
    {
      type: 'work_shops',
      label: 'مستري خانې او مستري خانې',
      list: '/api/work-shops',
      show: (id) => `/api/work-shops/${id}`,
      route: '/app/workshops',
      routeForm: (id) => (id ? `/app/workshops/${id}` : `/app/workshops/create`),
    },

    // ---------------- Format Three ----------------
    {
      type: 'badmashanos',
      label: 'زورواکان او ولکه داران',
      list: '/api/badmashanos',
      show: (id) => `/api/badmashanos/${id}`,
      route: '/app/badmashanos',
      routeForm: (id) => (id ? `/app/badmashanoform/${id}` : `/app/badmashanoform`),
    },
    {
      type: 'emarat_offices',
      label: 'امارتي اداراتو،مسولینو کورونه او میلمسنونونه',
      list: '/api/emarat-offices',
      show: (id) => `/api/emarat-offices/${id}`,
      route: '/app/emaratoffices',
      routeForm: (id) => (id ? `/app/emarat-offices/form/${id}` : `/app/emarat-offices/form`),
    },
    {
      type: 'hostels',
      label: 'لیلې او کرایي اطاقونه',
      list: '/api/hostels',
      show: (id) => `/api/hostels/${id}`,
      route: '/app/hostels',
      routeForm: (id) => (id ? `/app/hostelform/${id}` : `/app/hostelform`),
    },
    {
      type: 'hotels',
      label: 'میلمستونونو،مسافرخانو،هوټلونه او رسټوردانټ',
      list: '/api/hotels',
      show: (id) => `/api/hotels/${id}`,
      route: '/app/hotels',
      routeForm: (id) => `/app/hotelform/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'madrasas',
      label: 'دینې مدارس',
      list: '/api/madrasas',
      show: (id) => `/api/madrasas/${id}`,
      route: '/app/madrasas',
      routeForm: (id) => `/app/madrasas/form/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'mashkok_people',
      label: 'مشکوک افراد',
      list: '/api/mashkok-people',
      show: (id) => `/api/mashkok-people/${id}`,
      route: '/app/mashkokpeoples',
      routeForm: (id) => (id ? `/app/mashkokpeopleform/${id}` : `/app/mashkokpeopleform`),
    },
    {
      type: 'mujahedinos',
      label: 'مجاهدین',
      list: '/api/mujahedinos',
      show: (id) => `/api/mujahedinos/${id}`,
      route: '/app/mujahedinos',
      routeForm: (id) => `/app/mujahedinoform/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'old_jahadis',
      label: 'پخواني جهادي قومندانان',
      list: '/api/old-jahadis',
      show: (id) => `/api/old-jahadis/${id}`,
      route: '/app/oldjahadis',
      routeForm: (id) => `/app/oldjahadisform/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'sarayanos',
      label: 'سرایونه',
      list: '/api/sarayanos',
      show: (id) => `/api/sarayanos/${id}`,
      route: '/app/sarayanos',
      routeForm: (id) => (id ? `/app/sarayanoform/${id}` : `/app/sarayanoform`),
    },
    {
      type: 'tujarans',
      label: 'علماوو ، متنفذینو ، تجارانو او عالي رتبه اشخاص اشخاص',
      list: '/api/tujarans',
      show: (id) => `/api/tujarans/${id}`,
      route: '/app/tujarans',
      routeForm: (id) => `/app/tujaransform/${id ?? ''}`.replace(/\/$/, ''),
    },

    // ---------------- Format Four ----------------
    {
      type: 'checkpoints',
      label: 'چک پواینټونه او پوستې',
      list: '/api/checkpoints',
      show: (id) => `/api/checkpoints/${id}`,
      route: '/app/checkpoints',
      routeForm: (id) => (id ? `/app/checkpointform/${id}` : `/app/checkpointform`),
    },
    {
      type: 'masjids',
      label: 'مساجد خاناقاوې او تکیه خانې',
      list: '/api/masjids',
      show: (id) => `/api/masjids/${id}`,
      route: '/app/masjids',
      routeForm: (id) => `/app/masjidform/${id ?? ''}`.replace(/\/$/, ''),
    },
    {
      type: 'highways',
      label: 'څلور لارې او درې لارې',
      list: '/api/highways',
      show: (id) => `/api/highways/${id}`,
      route: '/app/highways',
      routeForm: (id) => (id ? `/app/highwayform/${id}` : `/app/highwayform`),
    },
    {
      type: 'companies',
      label: 'فابریکې او شرکتونه',
      list: '/api/companies',
      show: (id) => `/api/companies/${id}`,
      route: '/app/companies',
      routeForm: (id) => (id ? `/app/companyform/${id}` : `/app/companyform`),
    },
    {
      type: 'done_lost_places',
      label: 'هغه ساحې چې اختطاف پکې ترسره شوی',
      list: '/api/done-lost-places',
      show: (id) => `/api/done-lost-places/${id}`,
      route: '/app/donelostplaces',
      routeForm: (id) => (id ? `/app/done-lost-places/form/${id}` : `/app/done-lost-places/form`),
    },
    {
      type: 'econemy_projects',
      label: 'مهم تاسیسات او اقتصادي پروژې',
      list: '/api/econemy-projects',
      show: (id) => `/api/econemy-projects/${id}`,
      route: '/app/econemyprojects',
      routeForm: (id) => (id ? `/app/econemyprojectform/${id}` : `/app/econemyprojectform`),
    },
    {
      type: 'criminal_capture_places',
      label: 'هغې ساحې چې مجرمین پکې نیول شوی',
      list: '/api/criminal-capture-places',
      show: (id) => `/api/criminal-capture-places/${id}`,
      route: '/app/criminalcaptureplaces',
      routeForm: (id) =>
        id ? `/app/criminal-capture-place-form/${id}` : `/app/criminal-capture-place-form`,
    },
    {
      type: 'pumps',
      label: 'پمپونه',
      list: '/api/pumps',
      show: (id) => `/api/pumps/${id}`,
      route: '/app/pumps',
      routeForm: (id) => (id ? `/app/pumpform/${id}` : `/app/pumpform`),
    },
    {
      type: 'shoras',
      label: 'محلي شوراګانې',
      list: '/api/shoras',
      show: (id) => `/api/shoras/${id}`,
      route: '/app/shoras',
      routeForm: (id) => (id ? `/app/shoraform/${id}` : `/app/shoraform`),
    },
    {
      type: 'governmentplaces',
      label: 'دولتي ځایونه',
      list: '/api/governmentplaces',
      show: (id) => `/api/governmentplaces/${id}`,
      route: '/app/governmentplaces',
      routeForm: (id) => (id ? `/app/governmentplaceform/${id}` : `/app/governmentplaceform`),
    },
    {
      type: 'supermarkets',
      label: 'تجارتي مراکز او سوپر مارکیټونه',
      list: '/api/supermarkets',
      show: (id) => `/api/supermarkets/${id}`,
      route: '/app/supermarkets',
      routeForm: (id) => (id ? `/app/supermarketform/${id}` : `/app/supermarketform`),
    },
    {
      type: 'parks',
      label: 'تفریحي پارکونه او سیاحتي سیمې',
      list: '/api/parks',
      show: (id) => `/api/parks/${id}`,
      route: '/app/parks',
      routeForm: (id) => (id ? `/app/parkform/${id}` : `/app/parkform`),
    },
    {
      type: 'complexes',
      label: 'داخلي موسسې او بنسټونه',
      list: '/api/complexes',
      show: (id) => `/api/complexes/${id}`,
      route: '/app/complexes',
      routeForm: (id) => (id ? `/app/complexform/${id}` : `/app/complexform`),
    },
    {
      type: 'deplomaticplaces',
      label: 'دیپلوماتیک ځایونه،خارجي موسسې،دبهرنیانو د اوسیدو او کار ځایونه',
      list: '/api/deplomaticplaces',
      show: (id) => `/api/deplomaticplaces/${id}`,
      route: '/app/deplomaticplaces',
      routeForm: (id) => (id ? `/app/deplomaticplaceform/${id}` : `/app/deplomaticplaceform`),
    },
    {
      type: 'universities',
      label: 'پوهنتونونو ، دلوړو زده کړو موسسو او انسټیټیوټونو',
      list: '/api/universities',
      show: (id) => `/api/universities/${id}`,
      route: '/app/universities',
      routeForm: (id) => (id ? `/app/universityform/${id}` : `/app/universityform`),
    },
    {
      type: 'wakilanos',
      label: 'وکیلان او ملکان',
      list: '/api/wakilanos',
      show: (id) => `/api/wakilanos/${id}`,
      route: '/app/wakilanos',
      routeForm: (id) => (id ? `/app/wakilanatform/${id}` : `/app/wakilanatform`),
    },

    // ---------------- Extra ----------------
    {
      type: 'jawasis',
      label: 'جواسیس',
      list: '/api/jawasis',
      show: (id) => `/api/jawasis/${id}`,
      route: '/app/jawasis',
      routeForm: (id) => (id ? `/jawasis/edit/${id}` : `/jawasis/create`),
    },
  ];

  /* ---------- session token helper ---------- */
  function getToken() {
    const t = sessionStorage.getItem('token');
    return t ? String(t) : '';
  }
  function authHeaders() {
    const token = getToken();
    return token
      ? { Accept: 'application/json', Authorization: `Bearer ${token}` }
      : { Accept: 'application/json' };
  }

  /* ---------- helpers ---------- */
  function parseCoordinates(str) {
    if (!str) return null;
    const m = String(str)
      .trim()
      .match(/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/);
    if (!m) return null;
    const lng = parseFloat(m[1]);
    const lat = parseFloat(m[2]);
    if (!isFinite(lng) || !isFinite(lat)) return null;
    return [lng, lat];
  }

  function buildTitle(row, src) {
    return (
      row?.name ||
      row?.title ||
      row?.block_name ||
      row?.park_name ||
      row?.responsible_name ||
      row?.code_number ||
      row?.id ||
      src.label
    );
  }

  function buildSubtitle(row) {
    const parts = [];
    if (row?.code_number) parts.push(`کد: ${row.code_number}`);
    if (row?.phone_number) parts.push(`تماس: ${row.phone_number}`);
    if (row?.phone) parts.push(`تماس: ${row.phone}`);
    if (row?.responsible_phone) parts.push(`تماس: ${row.responsible_phone}`);
    if (row?.note) parts.push(String(row.note).slice(0, 40));
    return parts.join(' • ');
  }

  function pickPicture(row) {
    return (
      row?.picture || row?.picture_url || row?.image_url || row?.photo_url || row?.photo || null
    );
  }

  /* ============================================================
 ✅ UNIFIED MODULE points loader
============================================================ */
  const allMapItems = ref([]);

  async function loadAllModulePoints() {
    loadingAll.value = true;
    try {
      const requests = MAP_SOURCES.map(async (src) => {
        const url = `${src.list}?perPage=all`;
        const res = await fetch(url, { headers: authHeaders() }).then((r) => r.json());
        const rows = Array.isArray(res?.data) ? res.data : Array.isArray(res) ? res : [];

        return rows
          .map((row) => ({
            type: src.type,
            typeLabel: src.label,
            id: row.id,
            coordinates:
              row.coordinates ||
              row.coordenates ||
              row.masjid_coordinates ||
              row.com_coordinates ||
              row.cordinate ||
              row.cordinates ||
              row.cordenate ||
              row.cordenates ||
              row.coordinate,
            title: buildTitle(row, src),
            subtitle: buildSubtitle(row),
            picture: pickPicture(row),
            _showUrl: src.show(row.id),
            _raw: row,
            is_active: src.type === 'resource' ? isRowActive(row) : true,
          }))
          .filter((x) => parseCoordinates(x.coordinates));
      });

      const results = await Promise.all(requests);
      allMapItems.value = results.flat();
      console.log('All module markers:', allMapItems.value.length);
    } catch (e) {
      console.error('loadAllModulePoints failed', e);
    } finally {
      loadingAll.value = false;
    }
  }

  async function openDetailsMenu(item) {
    selectedItem.value = item;
    detailsOpen.value = true;
    detailsLoading.value = true;
    detailsData.value = null;

    try {
      const res = await fetch(item._showUrl, { headers: authHeaders() }).then((r) => r.json());
      detailsData.value = res?.data ?? res;
    } catch (e) {
      console.error('details load failed', e);
      detailsData.value = null;
    } finally {
      detailsLoading.value = false;
    }
  }

  function flyToItem(item) {
    const coords = parseCoordinates(item?.coordinates);
    if (!coords || !map.value) return;
    const [lng, lat] = coords;

    map.value.flyTo({
      center: [lng, lat],
      zoom: 16,
      essential: true,
      speed: 1.3,
      curve: 1.3,
    });
  }

  /* ---------- bilingual labels (unchanged) ---------- */
  const LOCAL_NAME_ORDER = [
    'name:fa',
    'name:prs',
    'name:ps',
    'name:ar',
    'name:nonlatin',
    'name',
    'name:en',
    'name_en',
    'name:latin',
    'name_int',
  ];
  const LOCAL_NAME_EXPR = ['coalesce', ...LOCAL_NAME_ORDER.map((f) => ['get', f])];
  const FONT_STACK_Z_LT6 = ['Noto Naskh Arabic Regular', 'Noto Sans Regular'];
  const FONT_STACK_Z_GE6 = ['Noto Naskh Arabic Bold', 'Noto Sans Bold'];

  function applyBilingualLabels(style) {
    if (!style?.layers) return style;
    for (const l of style.layers) {
      if (l.type === 'symbol') {
        l.layout ||= {};
        l.layout['text-field'] = LOCAL_NAME_EXPR;
        l.layout['text-font'] = [
          'step',
          ['zoom'],
          ['literal', FONT_STACK_Z_LT6],
          6,
          ['literal', FONT_STACK_Z_GE6],
        ];
        l.paint ||= {};
        l.paint['text-halo-color'] = '#fff';
        l.paint['text-halo-width'] = 1.1;
      }
    }
    return style;
  }

  /* ============================================================
 ✅ MARKERS (OFFLINE + mdi font icons)
============================================================ */

  const LABEL_ZOOM = 11;

  // ✅ mdi icon names (Vuetify font icons)
  // ✅ All formats + proper icons (Material Design Icons / mdi-*)
  // ✅ Keep resource icon behavior EXACTLY as you already have in map
  const MAP_RESOURCE_ICON = 'mdi-circle'; // ✅ do not change resource part icon behavior

  // 🔁 Fix singular/plural mismatches between MAP_SOURCES.type and ICON_MAP keys
  const ICON_ALIASES = {
    entry_exit: 'entry_exits',
    sarafyano: 'sarafyanos',
    mahajerat: 'mahajerats',
    soldier: 'soldiers',
    mawad: 'mawads',
    lost_mujremeen: 'lost_mujremeens',
    mulkano: 'mulkanos',
  };

  function iconKeyOfType(type) {
    return ICON_ALIASES[type] || type;
  }

  function safeLabelFor(item) {
    const k = iconKeyOfType(item?.type);
    return ICON_MAP?.[k]?.label || item?.typeLabel || k || '';
  }

  function safeMdiFor(item) {
    const t = item?.type;

    // ❗ DO NOT change resource logic
    if (t === 'resource') return MAP_RESOURCE_ICON;

    const k = iconKeyOfType(t);
    return ICON_MAP?.[k]?.mdi || 'mdi-map-marker';
  }

  function createMarkerEl(item, showLabel) {
    const wrapper = document.createElement('div');
    wrapper.className = 'mmi-marker';
    wrapper.dir = isRtl.value ? 'rtl' : 'ltr';

    const bubble = document.createElement('div');
    bubble.className = 'mmi-bubble';

    const iconWrap = document.createElement('div');
    iconWrap.className = 'mmi-icon';

    // ✅ mdi font icon inside marker (NO SVG)
    const i = document.createElement('i');
    i.className = `mdi ${safeMdiFor(item)}`;
    i.style.fontSize = '18px';
    i.style.lineHeight = '1';

    if (item.type === 'resource') {
      i.style.color = item.is_active ? '#16a34a' : '#dc2626'; // ✅ green / red
    } else {
      i.style.color = '#1f2937';
    }

    iconWrap.appendChild(i);

    const label = document.createElement('div');
    label.className = 'mmi-label';
    label.textContent = safeLabelFor(item);
    if (!showLabel) label.style.display = 'none';

    bubble.appendChild(iconWrap);
    bubble.appendChild(label);

    wrapper.appendChild(bubble);
    return { wrapper, label };
  }

  const markers = ref([]);
  let zoomHandler = null;

  function clearMarkers() {
    markers.value.forEach((m) => m.marker.remove());
    markers.value = [];
  }

  function rebuildMarkers() {
    const m = map.value;
    if (!m) return;

    clearMarkers();

    const showLabel = m.getZoom() >= LABEL_ZOOM;

    (allMapItems.value || []).forEach((item) => {
      const coords = parseCoordinates(item?.coordinates);
      if (!coords) return;

      const { wrapper, label } = createMarkerEl(item, showLabel);

      const mk = new maplibregl.Marker({ element: wrapper, anchor: 'bottom' })
        .setLngLat(coords)
        .addTo(m);

      wrapper.addEventListener('click', (ev) => {
        ev.stopPropagation();
        openDetailsMenu(item);
      });

      markers.value.push({ marker: mk, label });
    });

    if (!zoomHandler) {
      zoomHandler = () => {
        const visible = m.getZoom() >= LABEL_ZOOM;
        markers.value.forEach((mm) => {
          if (mm?.label) mm.label.style.display = visible ? 'block' : 'none';
        });
      };
      m.on('zoom', zoomHandler);
    }
  }

  /* ============================================================
 ✅ Points menu data (group + count + items)
============================================================ */
  const groupedPoints = computed(() => {
    const arr = Array.isArray(allMapItems.value) ? allMapItems.value : [];
    const mapG = new Map();

    for (const it of arr) {
      const key = it.type || 'unknown';
      if (!mapG.has(key)) {
        mapG.set(key, {
          type: key,
          label: safeLabelFor(it) || it.typeLabel || key,
          items: [],
          count: 0,
        });
      }
      const g = mapG.get(key);
      g.items.push(it);
      g.count++;
    }

    return Array.from(mapG.values()).sort((a, b) => {
      if (b.count !== a.count) return b.count - a.count;
      return String(a.label).localeCompare(String(b.label), 'fa');
    });
  });

  /* ---------- Highlight marker (optional, OFFLINE) ---------- */
  function createHighlightElement(feature) {
    const props = feature?.properties || {};
    const photoUrl = props.photoUrl || '';
    const el = document.createElement('div');

    if (photoUrl) {
      el.style.width = '24px';
      el.style.height = '24px';
      el.style.borderRadius = '50%';
      el.style.overflow = 'hidden';
      el.style.background = '#ffffff';
      el.style.boxShadow = '0 0 0 2px #ffffff, 0 1px 4px rgba(0,0,0,0.35)';
      el.style.cursor = 'pointer';
      el.style.display = 'flex';
      el.style.alignItems = 'center';
      el.style.justifyContent = 'center';
      el.style.transform = 'translateY(-4px)';

      const img = document.createElement('img');
      img.src = photoUrl;
      img.alt = props.title || 'person';
      img.style.width = '100%';
      img.style.height = '100%';
      img.style.objectFit = 'cover';
      img.style.pointerEvents = 'none';
      el.appendChild(img);
      return el;
    }

    el.style.width = '20px';
    el.style.height = '20px';
    el.style.borderRadius = '50%';
    el.style.background = '#ffffff';
    el.style.boxShadow = '0 0 0 1px #2563eb, 0 1px 4px rgba(0,0,0,0.35)';
    el.style.display = 'flex';
    el.style.alignItems = 'center';
    el.style.justifyContent = 'center';
    el.style.cursor = 'pointer';
    el.style.transform = 'translateY(-4px)';

    const dot = document.createElement('div');
    dot.style.width = '10px';
    dot.style.height = '10px';
    dot.style.borderRadius = '999px';
    dot.style.background = '#2563eb';
    el.appendChild(dot);

    return el;
  }

  function flyTo(payload) {
    if (!map.value) return;
    const { lng, lat, zoom, feature } = payload;

    map.value.flyTo({
      center: [lng, lat],
      zoom: zoom || 14,
      essential: true,
      speed: 1,
      curve: 1.3,
    });

    if (highlightMarker) {
      highlightMarker.remove();
      highlightMarker = null;
    }

    const el = createHighlightElement(feature || {});
    highlightMarker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
      .setLngLat([lng, lat])
      .addTo(map.value);
  }

  function applyFlyTarget(val) {
    if (!val || !val.coords || !map.value) return;

    const coords = typeof val.coords === 'string' ? parseCoordinates(val.coords) : val.coords;
    if (!coords) return;

    const [lng, lat] = coords;
    const meta = val.meta || {};

    flyTo({
      lng,
      lat,
      zoom: meta.zoom || 16,
      feature: {
        type: meta.type || 'generic',
        properties: {
          title: meta.title,
          subtitle: meta.subtitle,
          photoUrl: meta.photoUrl || '',
        },
      },
    });
  }

  // for inactive active resource
  function isRowActive(row) {
    // support multiple possible back-end fields
    const v =
      row?.is_active ??
      row?.active ??
      row?.status ??
      row?.isActive ??
      row?.isActiveResource ??
      null;

    // boolean
    if (typeof v === 'boolean') return v;

    // numeric
    if (typeof v === 'number') return v === 1;

    // string
    const s = String(v || '')
      .trim()
      .toLowerCase();
    if (['1', 'true', 'yes', 'active', 'فعال'].includes(s)) return true;
    if (['0', 'false', 'no', 'inactive', 'غیر فعال', 'غير فعال'].includes(s)) return false;

    // default (treat unknown as active OR inactive; choose what you prefer)
    return true;
  }

  /* ---------- Initialize map (local assets served from same origin) ---------- */
  onMounted(async () => {
    const ORIGIN = window.location.origin;
    const STYLE_JSON = `${ORIGIN}/styles/bright-complete.json`;
    const SPRITE_URL = `${ORIGIN}/sprites/sprite`;
    const GLYPHS_URL = `${ORIGIN}/fonts/{fontstack}/{range}.pbf`;
    const RTL_PLUGIN_URL = `${ORIGIN}/vendor/maplibre/mapbox-gl-rtl-text.js`;

    protocol = new Protocol();

    try {
      maplibregl.addProtocol('pmtiles', protocol.tile);
    } catch {}

    const pm = new PMTiles(`${ORIGIN}${pmtilesUrl}`);
    protocol.add(pm);

    try {
      maplibregl.setRTLTextPlugin(RTL_PLUGIN_URL, null, true);
    } catch {}

    let style = await fetch(STYLE_JSON).then((r) => r.json());

    for (const [, src] of Object.entries(style.sources)) {
      if (src.type === 'vector') {
        src.url = `pmtiles://${ORIGIN}${pmtilesUrl}`;
        delete src.tiles;
      }
    }

    style.sprite = SPRITE_URL;
    style.glyphs = GLYPHS_URL;
    style = applyBilingualLabels(style);

    map.value = new maplibregl.Map({
      container: mapEl.value,
      style,
      center: [69.2, 34.5],
      zoom: 7,
      minZoom: 3,
      maxZoom: 18,
    });

    map.value.addControl(new maplibregl.NavigationControl(), 'top-right');
    map.value.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

    map.value.on('load', async () => {
      await loadAllModulePoints();
      rebuildMarkers();
      applyFlyTarget(mapBus.flyTarget);
    });
  });

  /* reload */
  watch(
    () => mapBus.reloadTs,
    async (ts) => {
      if (!ts || !map.value) return;
      await loadAllModulePoints();
      rebuildMarkers();
    }
  );

  watch(
    () => mapBus.flyTarget,
    (val) => {
      if (!val || !val.coords || !map.value) return;

      const coords = typeof val.coords === 'string' ? parseCoordinates(val.coords) : val.coords;
      if (!coords) return;

      const [lng, lat] = coords;
      const meta = val.meta || {};

      map.value.flyTo({
        center: [lng, lat],
        zoom: meta.zoom || 16,
        essential: true,
        speed: 1.3,
        curve: 1.3,
      });

      const skipHighlight = meta.type === 'office_employee' || meta.disableHighlight === true;
      if (skipHighlight) {
        if (highlightMarker) {
          highlightMarker.remove();
          highlightMarker = null;
        }
        return;
      }

      flyTo({
        lng,
        lat,
        zoom: meta.zoom || 16,
        feature: {
          type: meta.type || 'generic',
          properties: {
            title: meta.title,
            subtitle: meta.subtitle,
            photoUrl: meta.photoUrl || '',
          },
        },
      });
    },
    { deep: true }
  );

  watch(
    () => allMapItems.value,
    () => {
      if (!map.value) return;
      rebuildMarkers();
    },
    { deep: true }
  );

  watch(
    () => isRtl.value,
    () => {
      if (!map.value) return;
      rebuildMarkers();
    }
  );

  onBeforeUnmount(() => {
    if (highlightMarker) {
      highlightMarker.remove();
      highlightMarker = null;
    }
    const m = map.value;
    if (m && zoomHandler) {
      m.off('zoom', zoomHandler);
    }
    zoomHandler = null;
    clearMarkers();
    m?.remove();
  });
</script>

<style scoped>
  .map-wrap {
    position: relative;
  }
  .map {
    width: 100%;
    height: 100vh;
    background: #eef3f7;
  }

  .map-top-tools {
    position: absolute;
    top: calc(var(--v-layout-top, 12px) - 52px);
    left: -42px !important;
    z-index: 1;
    width: 520px;
    margin-right: 34px;
    max-width: calc(100vw - 12px);
    display: flex;
    align-items: center;
    gap: 10px;
  }

  :dir(rtl) .map-top-tools {
    left: auto;
    right: 12px;
  }

  /* ✅ points button */
  .points-btn {
    height: 40px;
    min-width: 140px;
    font-weight: 800;
  }

  .points-menu-card {
    width: min(560px, calc(100vw - 24px));
    max-height: 72vh;
    overflow: hidden;
  }

  .points-menu-head {
    padding: 12px 14px;
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
  }
  .points-title {
    font-weight: 900;
    font-size: 14px;
  }
  .points-sub {
    font-size: 12px;
    opacity: 0.75;
    font-weight: 700;
  }

  .points-menu-body {
    padding: 12px 12px 2px 12px;
    overflow: auto;
    max-height: 58vh;
  }

  .type-block {
    border: 1px solid rgba(17, 24, 39, 0.08);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 10px;
    background: #fff;
  }

  .type-head {
    padding: 10px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(37, 99, 235, 0.04);
  }

  .type-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 900;
    font-size: 13px;
  }

  .type-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
  }

  .type-count {
    font-size: 12px;
    font-weight: 900;
    opacity: 0.85;
  }

  .type-list {
    padding: 0;
  }

  .mini-title {
    font-weight: 800;
    font-size: 12.5px;
  }
  .mini-sub {
    font-size: 11px;
    opacity: 0.8;
  }

  .empty-points {
    padding: 14px;
    font-weight: 800;
    opacity: 0.7;
    text-align: center;
  }

  .points-menu-actions {
    padding: 10px 12px;
    display: flex;
    justify-content: flex-end;
  }

  /* ===== markers styles (mdi font icons) ===== */
  :deep(.mmi-marker) {
    transform: translateY(-4px);
    cursor: pointer;
  }

  :deep(.mmi-bubble) {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 8px;
    border-radius: 999px;
    background: #ffffff;
    box-shadow: 0 0 0 2px #ffffff, 0 1px 6px rgba(0, 0, 0, 0.3);
    white-space: nowrap;
  }

  :deep(.mmi-icon) {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(37, 99, 235, 0.08);
    color: #1f2937;
  }

  :deep(.mmi-label) {
    font-size: 12px;
    font-weight: 700;
    color: #111827;
    opacity: 0.95;
    line-height: 1;
  }
</style>
