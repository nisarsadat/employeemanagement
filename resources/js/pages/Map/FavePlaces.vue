<template>
  <div class="fav-box">
    <h3 class="title">💚 My Favorite / ⭐ Important Places</h3>

    <SearchMap
      class="search-panel"
      :gazetteer-url="'/pmtiles-range/afg_gazetteer.json'"
      @flyTo="flyTo"
    />

    <ul class="fav-list">
      <li v-for="(f, i) in filtered" :key="i" class="fav-item" @click="flyTo(f)">
        <span v-if="f.type === 'favorite'">💚</span>
        <span v-else>⭐</span>
        <b>{{ f.name }}</b> — {{ f.province }}
        <small>({{ f.lng.toFixed(2) }}, {{ f.lat.toFixed(2) }})</small>
      </li>
    </ul>
  </div>
</template>

<script setup>
  import { ref, computed } from 'vue';
  const props = defineProps({ favorites: { type: Array, default: () => [] } });
  const emit = defineEmits(['flyTo']);
  const search = ref('');
  const filtered = computed(() => {
    const q = search.value.toLowerCase();
    return props.favorites.filter(
      (f) => f.name.toLowerCase().includes(q) || f.province.toLowerCase().includes(q)
    );
  });
  function flyTo(f) {
    emit('flyTo', { lng: f.lng, lat: f.lat, zoom: 13 });
  }
</script>

<style scoped>
  .fav-box {
    background: #ffffffee;
    border-radius: 12px;
    padding: 10px;
    backdrop-filter: blur(4px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    font-family: system-ui, Noto Sans, Noto Naskh Arabic, Arial;
  }
  .title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 6px;
  }
  .input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-bottom: 6px;
  }
  .fav-list {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 35vh;
    overflow: auto;
    border-top: 1px solid #eee;
  }
  .fav-item {
    padding: 6px 4px;
    border-bottom: 1px dashed #eee;
    cursor: pointer;
  }
  .fav-item:hover {
    background: #f7f9fc;
  }
  small {
    font-size: 11px;
    color: #666;
    margin-left: 4px;
  }
</style>
