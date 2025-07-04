<?php
function getAdminName($conn) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("i", $_SESSION["admin_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($admin = $result->fetch_assoc()) {
        return $admin["name"];
    }
    return "Admin";
}
