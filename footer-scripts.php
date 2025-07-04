<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
  // Initialize Flatpickr calendar
  flatpickr("#calendar", {
    dateFormat: "Y-m-d", // Date format: YYYY-MM-DD
    minDate: "today",     // Disallow past dates
    defaultDate: "today"  // Set today as the default selected date
  });

  // Initialize Leaflet map
  // Set view to San Pablo City coordinates (14.0684, 121.3257) with zoom level 13
  var map = L.map('map').setView([14.0684, 121.3257], 13);

  // Add OpenStreetMap tile layer to the map
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  // Assuming 'facilities' data is correctly passed from PHP
  const facilities = <?= json_encode($facilities) ?>;

  // Iterate through facilities to add markers to the map
  facilities.forEach(fac => {
    // Ensure latitude and longitude exist before creating a marker
    if (fac.latitude && fac.longitude) {
      const marker = L.marker([fac.latitude, fac.longitude]).addTo(map);

      // Construct popup HTML with accessibility attributes
      const popupHTML = `
        <div class="popup-content" role="dialog" aria-label="${fac.name} facility details">
          ${fac.image_url ? `<img src="${fac.image_url}" alt="Image of ${fac.name}" onerror="this.style.display='none'">` : ''}
          <strong><a href="facility.php?id=${fac.id}" target="_blank" aria-label="Visit page for ${fac.name}">${fac.name}</a></strong><br>
          ${fac.description}<br>
          <em>${fac.address}</em>
        </div>
      `;
      marker.bindPopup(popupHTML); // Bind the popup to the marker
    }
  });
</script>