<script setup>
  import { computed } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import { useMapBusStore } from '../stores/MapBusStore';
  import { toAbsoluteUrl } from '../../utils/assetUrl';

  const props = defineProps({
    coordinates: {
      type: [String, Array],
      required: false,
      default: null,
    },
    title: {
      type: String,
      default: '',
    },
    subtitle: {
      type: String,
      default: '',
    },
    type: {
      type: String,
      default: 'generic',
    },
    zoom: {
      type: Number,
      default: 16,
    },
    // 👇 new prop, but you already pass it from pages
    photoUrl: {
      type: String,
      default: '',
    },
  });

  const { t } = useI18n();
  const mapBus = useMapBusStore();
  const router = useRouter();

  const hasCoords = computed(() => !!props.coordinates);

  function handleClick() {
    if (!props.coordinates) return;

    mapBus.flyTo(props.coordinates, {
      title: props.title,
      subtitle: props.subtitle,
      type: props.type,
      zoom: props.zoom,
      // ✅ normalize once here
      photoUrl: toAbsoluteUrl(
        props.photoUrl || '' // fallback if empty
      ),
    });

    // use your actual map route name
    router.push({ name: 'MapAfg' }).catch(() => {});
  }
</script>

<template>
  <v-btn
    size="small"
    color="primary"
    variant="outlined"
    :disabled="!hasCoords"
    @click="handleClick"
  >
    <v-icon start>mdi-map-marker</v-icon>
    {{ t('دیدن در نقشه') }}
  </v-btn>
</template>
