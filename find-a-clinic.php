<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM facilities WHERE type = 'Clinic'");
$stmt->execute();
$result = $stmt->get_result();
$clinics = [];
while ($row = $result->fetch_assoc()) {
    $clinics[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Find a Clinic</title>
  <link rel="stylesheet" href="style/style.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; background: #f5f7fa; }
    h2 { text-align: center; margin: 20px 0; color: #2c3e50; }
    .search-box { display: block; margin: 0 auto 10px auto; padding: 10px; width: 90%; max-width: 500px; border-radius: 6px; border: 1px solid #ccc; }
    #map { height: 500px; width: 100%; position: relative; }
    #locateMeBtn { position: absolute; top: 15px; right: 15px; z-index: 999; background: #3498db; color: white; border: none; padding: 8px 12px; border-radius: 6px; font-weight: bold; cursor: pointer; }

    .slide-panel {
      position: fixed; right: -350px; top: 0; height: 100%; width: 320px;
      background: #fff; box-shadow: -2px 0 12px rgba(0,0,0,0.1);
      transition: right 0.3s ease; padding: 20px; overflow-y: auto;
      z-index: 1000;
    }
    .slide-panel.active { right: 0; }
    .slide-panel h3, .slide-panel p, .slide-panel label {
      color: #1a1a1a;
      line-height: 1.6;
      font-size: 15px;
    }
    .slide-panel form input,
    .slide-panel form select,
    .slide-panel form button {
      width: 100%;
      margin: 6px 0;
      padding: 10px;
      font-family: 'Poppins', sans-serif;
      border-radius: 6px;
      border: 1px solid #ccc;
      background: #f9f9f9;
      color: #333;
    }
    .slide-panel form button {
      background: #27ae60;
      color: #fff;
      font-weight: bold;
      border: none;
      cursor: pointer;
    }

    .back-btn {
      display: inline-block;
      margin: 15px;
      padding: 10px 15px;
      background: #2c3e50;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
    }

    #successModal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: 2000;
      align-items: center;
      justify-content: center;
    }

    #successModal .modal-content {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      text-align: center;
      max-width: 300px;
      margin: auto;
    }
  </style>
</head>
<body>

<a href="dashboard.php" class="back-btn">&larr; Back to Home</a>

<h2>Find a Clinic</h2>
<input type="text" id="clinicSearch" class="search-box" placeholder="Search clinic name or specialization..." />
<div id="map">
  <button id="locateMeBtn" onclick="locateUser()">📍 Locate Me</button>
</div>

<div class="slide-panel" id="clinicInfoPanel">
  <h3 id="clinicName"></h3>
  <p><strong>Address:</strong> <span id="clinicAddress"></span></p>
  <p><strong>Hours:</strong> <span id="clinicHours"></span></p>
  <p><strong>Description:</strong> <span id="clinicDesc"></span></p>
  <form method="POST" action="book-appointment.php">
    <input type="hidden" name="clinic_id" id="clinicId" />
    <label>Select Specialist:</label>
    <select name="specialist_id" id="specialistDropdown" required></select>
    <label>Appointment Date:</label>
    <input type="text" id="appointmentDate" name="appointment_date" placeholder="Select date" required />
    <button type="submit">Book Appointment</button>
  </form>
</div>

<div id="successModal">
  <div class="modal-content">
    <h3 style="color:#27ae60;">✅ Appointment Booked!</h3>
    <p>Thank you for booking.<br>We’ll get in touch soon.</p>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const clinics = <?= json_encode($clinics); ?>;
const map = L.map('map').setView([14.06313, 121.33862], 13);
let userMarker = null;
const panel = document.getElementById("clinicInfoPanel");
const markers = [];

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

clinics.forEach(clinic => {
  const marker = L.marker([clinic.latitude, clinic.longitude]).addTo(map);
  marker.clinicData = clinic;
  markers.push(marker);

  marker.on('click', () => {
    document.getElementById("clinicName").textContent = clinic.name;
    document.getElementById("clinicAddress").textContent = clinic.address;
    document.getElementById("clinicDesc").textContent = clinic.description || "No description.";
    document.getElementById("clinicHours").textContent = (clinic.open_time && clinic.close_time)
      ? `${clinic.open_time} - ${clinic.close_time}` : "Not specified";
    document.getElementById("clinicId").value = clinic.facilities_id;

    fetch(`get-specialists.php?facilities_id=${clinic.facilities_id}`)
      .then(res => res.json())
      .then(data => {
        const dropdown = document.getElementById("specialistDropdown");
        dropdown.innerHTML = "";
        if (data.length === 0) {
          const opt = document.createElement("option");
          opt.textContent = "No specialists available";
          opt.disabled = true;
          opt.selected = true;
          dropdown.appendChild(opt);
        } else {
          data.forEach(spec => {
            const opt = document.createElement("option");
            opt.value = spec.specialists_id;
            opt.textContent = `${spec.name} - ${spec.specialization}`;
            dropdown.appendChild(opt);
          });
        }
      });

    panel.classList.add("active");
  });
});

function locateUser() {
  if (!navigator.geolocation) {
    alert("Geolocation not supported.");
    return;
  }
  navigator.geolocation.getCurrentPosition(pos => {
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;
    if (userMarker) map.removeLayer(userMarker);
    userMarker = L.marker([lat, lng], {
      icon: L.icon({
        iconUrl: "https://cdn-icons-png.flaticon.com/512/447/447031.png",
        iconSize: [32, 32],
        iconAnchor: [16, 32]
      })
    }).addTo(map).bindPopup("You are here").openPopup();
    map.setView([lat, lng], 15);
  }, () => {
    alert("Unable to retrieve your location.");
  });
}

document.getElementById("clinicSearch").addEventListener("input", function () {
  const term = this.value.toLowerCase();
  markers.forEach(marker => {
    const clinic = marker.clinicData;
    const match = clinic.name.toLowerCase().includes(term) ||
                  (clinic.description || "").toLowerCase().includes(term);
    if (match) {
      if (!map.hasLayer(marker)) marker.addTo(map);
    } else {
      if (map.hasLayer(marker)) map.removeLayer(marker);
    }
  });
});

flatpickr("#appointmentDate", {
  minDate: "today",
  dateFormat: "Y-m-d",
  altInput: true,
  altFormat: "F j, Y",
  theme: "airbnb"
});

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
  document.getElementById("successModal").style.display = "flex";
  setTimeout(() => {
    document.getElementById("successModal").style.display = "none";
    history.replaceState(null, '', 'find-a-clinic.php');
    document.getElementById("clinicInfoPanel").classList.remove("active");
  }, 3000);
<?php endif; ?>
</script>
</body>
</html>
