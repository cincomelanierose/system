<?php
// map.php
include 'includes/db.php';

// Get all facilities
$result = $conn->query("SELECT * FROM facilities WHERE latitude IS NOT NULL AND longitude IS NOT NULL");

$facilities = [];
while ($row = $result->fetch_assoc()) {
    $facilities[] = $row;
}

include 'includes/header-map.php';
include 'includes/map-view.php';
include 'includes/footer-map.php';
