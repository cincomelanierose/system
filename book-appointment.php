<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $chosen_specialist_id = intval($_POST["specialist_id"]);
    $appointment_date = $_POST["appointment_date"];

    // ✅ Get the medstaff's user_id from the specialists table
    $stmt = $conn->prepare("SELECT user_id FROM specialists WHERE specialists_id = ?");
    $stmt->bind_param("i", $chosen_specialist_id);
    $stmt->execute();
    $stmt->bind_result($medstaff_user_id);
    $stmt->fetch();
    $stmt->close();

    // ✅ Save the appointment with medstaff's user_id as specialists_id
    $stmt = $conn->prepare("INSERT INTO appointments (specialists_id, appointment_date, users_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $medstaff_user_id, $appointment_date, $user_id);
    $stmt->execute();

    // ✅ Auto-insert into patients table
    $appointment_id = $conn->insert_id;
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO patients (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();

    header("Location: find-a-clinic.php?success=1");
    exit;
}
?>
