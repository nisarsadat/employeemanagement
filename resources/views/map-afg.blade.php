<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Offline Map – Afghanistan</title>
  @vite(['resources/js/app.js'])
  <style>
    html, body { margin:0; height:100%; }
    #map { height: 100vh; width: 100%; }
  </style>
</head>
<body>
<div id="map"></div>

<script type="module">
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { PMTiles, PMTilesLeafletLayer } from "pmtiles";

// Path to your file (served via Laravel storage)
const url = "http://127.0.0.1:8000/storage/tiles/afghanistan.pmtiles";

// Afghanistan bounds (approx)
const afgBounds = L.latLngBounds([29.3, 60.5], [38.5, 75.2]);

// Init map
const map = L.map('map', {
  center: [34.5, 69.2],
  zoom: 6,
  maxBounds: afgBounds,
  maxBoundsViscosity: 1.0,
});

// Create PMTiles source
const pmtiles = new PMTiles(url);

// Add Leaflet tile layer
const layer = new PMTilesLeafletLayer(pmtiles, {
  attribution: '&copy; OpenStreetMap contributors',
});

layer.addTo(map);
</script>
</body>
</html>
