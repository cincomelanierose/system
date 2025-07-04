<?php
include("includes/db.php);

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];

    $stmt = $conn->prepare("INSERT INTO specialists (name, specialization) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $specialization);
    
    if ($stmt->execute()) {
        echo "Specialist added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<form method="post">
    <input type="text" name="name" placeholder="Specialist Name" required><br><br>
    <input type="text" name="specialization" placeholder="Specialization" required><br><br>
    <button type="submit">Add Specialist</button>
</form>
