<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
  const map = L.map('map').setView([14.0684, 121.3257], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const facilities = <?= json_encode($facilities) ?>;

  facilities.forEach(fac => {
    if (fac.latitude && fac.longitude) {
      const marker = L.marker([fac.latitude, fac.longitude]).addTo(map);
      const popupHTML = `
        <div style="font-size:14px; max-width:220px;">
          ${fac.image_url ? `<img src="${fac.image_url}" alt="${fac.name}" style="width:100%; border-radius:6px; margin-bottom:6px;" onerror="this.style.display='none'">` : ''}
          <strong>${fac.name}</strong><br>
          ${fac.description}<br>
          <em>${fac.address}</em>
        </div>
      `;
      marker.bindPopup(popupHTML);
    }
  });
</script>

</body>
</html>
