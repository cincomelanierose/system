<?php
include 'includes/db.php';

if (isset($_GET['facilities_id'])) {
    $facilities_id = intval($_GET['facilities_id']);

    $stmt = $conn->prepare("SELECT specialists_id, name, specialization FROM specialists WHERE facilities_id = ?");
    $stmt->bind_param("i", $facilities_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $specialists = [];
    while ($row = $result->fetch_assoc()) {
        $specialists[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($specialists);
}
?>
