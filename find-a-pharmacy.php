<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM facilities WHERE type = 'Pharmacy'");
$stmt->execute();
$result = $stmt->get_result();
$facilities = [];
while ($row = $result->fetch_assoc()) {
    $facilities[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find a Pharmacy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 500px; width: 100%; position: relative; }
        .back-btn {
            margin: 15px;
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        #locateMeBtn {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1000;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn">&larr; Back to Home</a>
    <h2>Find a Pharmacy</h2>

    <div id="map">
        <button id="locateMeBtn" onclick="locateUser()">📍 Locate Me</button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const facilities = <?php echo json_encode($facilities); ?>;
        const map = L.map('map').setView([14.06313, 121.33862], 13);
        let userMarker;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        facilities.forEach(facility => {
            const marker = L.marker([facility.latitude, facility.longitude]).addTo(map);
            marker.bindPopup(`<b>${facility.name}</b><br><a target="_blank" href="https://www.google.com/maps/dir/?api=1&destination=${facility.latitude},${facility.longitude}">Start Navigation</a>`);
        });

        function locateUser() {
            if (!navigator.geolocation) {
                alert("Geolocation not supported.");
                return;
            }

            navigator.geolocation.getCurrentPosition(position => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                if (userMarker) {
                    map.removeLayer(userMarker);
                }

                userMarker = L.marker([lat, lng], {
                    icon: L.icon({
                        iconUrl: "https://cdn-icons-png.flaticon.com/512/447/447031.png",
                        iconSize: [32, 32],
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -32]
                    })
                }).addTo(map).bindPopup("You are here").openPopup();

                map.setView([lat, lng], 15);
            }, () => {
                alert("Unable to retrieve your location.");
            });
        }
    </script>
</body>
</html>
