<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["appointment_id"], $_POST["status"])) {
    $appointment_id = intval($_POST["appointment_id"]);
    $status = $_POST["status"];

    // Determine if it should be archived
    $archived_at = ($status === 'Discharged') ? date('Y-m-d H:i:s') : null;

    // Check if patient record exists
    $check = $conn->prepare("SELECT patients_id FROM patients WHERE patients_id = ?");
    $check->bind_param("i", $appointment_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        // Get patient name
        $getName = $conn->prepare("SELECT u.name FROM appointments a JOIN users u ON a.users_id = u.id WHERE a.appointments_id = ?");
        $getName->bind_param("i", $appointment_id);
        $getName->execute();
        $res = $getName->get_result();
        $name = ($res->fetch_assoc())['name'] ?? 'Unknown';
        $getName->close();

        // Insert new patient
        $insert = $conn->prepare("INSERT INTO patients (patients_id, name, status, archived_at) VALUES (?, ?, ?, ?)");
        $insert->bind_param("isss", $appointment_id, $name, $status, $archived_at);
        $insert->execute();
        $insert->close();
    } else {
        // Update existing
        $stmt = $conn->prepare("UPDATE patients SET status = ?, archived_at = ? WHERE patients_id = ?");
        $stmt->bind_param("ssi", $status, $archived_at, $appointment_id);
        $stmt->execute();
        $stmt->close();
    }
}
