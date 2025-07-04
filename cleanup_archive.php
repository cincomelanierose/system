<?php
include 'includes/db.php';


$conn->query("DELETE FROM patients WHERE archived_at IS NOT NULL AND archived_at <= NOW() - INTERVAL 15 DAY");
