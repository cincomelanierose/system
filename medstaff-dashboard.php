<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'medstaff') {
    header("Location: login.php");
    exit;
}

$staff_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$whereStatus = '';
if (in_array($status_filter, ['Pending', 'Accepted', 'Admitted'])) {
    $whereStatus = "AND p.status = '" . $conn->real_escape_string($status_filter) . "'";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["appointment_id"], $_POST["status"])) {
    $appointment_id = intval($_POST["appointment_id"]);
    $status = $_POST["status"];
    $archived_at = ($status === 'Discharged') ? date('Y-m-d H:i:s') : null;

    $check = $conn->prepare("SELECT patients_id FROM patients WHERE patients_id = ?");
    $check->bind_param("i", $appointment_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        $getName = $conn->prepare("SELECT u.name FROM appointments a JOIN users u ON a.users_id = u.id WHERE a.appointments_id = ?");
        $getName->bind_param("i", $appointment_id);
        $getName->execute();
        $res = $getName->get_result();
        $name = ($res->fetch_assoc())['name'] ?? 'Unknown';
        $getName->close();

        $insert = $conn->prepare("INSERT INTO patients (patients_id, name, status, archived_at) VALUES (?, ?, ?, ?)");
        $insert->bind_param("isss", $appointment_id, $name, $status, $archived_at);
        $insert->execute();
        $insert->close();
    } else {
        $stmt = $conn->prepare("UPDATE patients SET status = ?, archived_at = ? WHERE patients_id = ?");
        $stmt->bind_param("ssi", $status, $archived_at, $appointment_id);
        $stmt->execute();
        $stmt->close();
    }
    echo "<script>location.href='medstaff-dashboard.php';</script>";
    exit;
}

$appointments = $conn->query("
    SELECT a.appointments_id, a.appointment_date, u.name AS patient_name, p.status
    FROM appointments a
    JOIN users u ON a.users_id = u.id
    LEFT JOIN patients p ON a.appointments_id = p.patients_id
    JOIN specialists s ON a.specialists_id = s.specialists_id
    WHERE s.user_id = $staff_id
    AND (p.archived_at IS NULL)
    $whereStatus
    ORDER BY a.appointment_date DESC
");

$archived = $conn->query("
    SELECT a.appointments_id, a.appointment_date, u.name AS patient_name, p.status, p.archived_at
    FROM appointments a
    JOIN users u ON a.users_id = u.id
    LEFT JOIN patients p ON a.appointments_id = p.patients_id
    JOIN specialists s ON a.specialists_id = s.specialists_id
    WHERE s.user_id = $staff_id
    AND p.archived_at IS NOT NULL
    AND p.archived_at > NOW() - INTERVAL 15 DAY
    ORDER BY p.archived_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Med Staff Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; background: #f9f9f9; }
    .sidebar { width: 220px; background: #fff; height: 100vh; position: fixed; top: 0; left: 0; padding: 30px 0; box-shadow: 2px 0 8px rgba(0,0,0,0.05); }
    .sidebar h2 { text-align: center; font-size: 22px; color: #27ae60; margin-bottom: 30px; }
    .sidebar ul { list-style: none; padding: 0 20px; }
    .sidebar ul li { margin: 20px 0; }
    .sidebar ul li a { text-decoration: none; color: #333; padding: 10px 20px; display: block; border-radius: 10px; transition: background 0.2s; }
    .sidebar ul li a:hover, .sidebar ul li a.active { background: #27ae60; color: #fff; }
    .main { margin-left: 220px; padding: 30px; transition: all 0.3s; }
    .filters { margin-bottom: 20px; }
    .filters button, .toggle-archive { margin-right: 10px; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; background: #27ae60; color: white; }
    table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    th, td { padding: 14px; border: 1px solid #eee; text-align: left; }
    th { background: #ecfef1; color: #27ae60; }
    .btn-del { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
    .btn-del:hover { background: #c0392b; }
    select { padding: 6px 10px; border-radius: 5px; }
    .archive-section { display: none; background: #fff; margin-top: 20px; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
  </style>
</head>
<body>
<div class="sidebar">
  <h2>MedStaff</h2>
  <ul>
    <li><a href="#" class="active">Patients</a></li>
    <li><a href="logout.php" style="background:#e74c3c; color:white;">Logout</a></li>
  </ul>
</div>

<div class="main">
  <h1>Appointments</h1>
  <div class="filters">
    <button onclick="location.href='?status='">All</button>
    <button onclick="location.href='?status=Pending'">Pending</button>
    <button onclick="location.href='?status=Accepted'">Accepted</button>
    <button onclick="location.href='?status=Admitted'">Admitted</button>
    <button class="toggle-archive" onclick="toggleArchive()">Show Archived</button>
  </div>
  <table>
    <tr>
      <th>Patient Name</th>
      <th>Appointment Date</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    <?php while ($row = $appointments->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['patient_name']) ?></td>
      <td><?= htmlspecialchars($row['appointment_date']) ?></td>
      <td>
        <form method="POST">
          <input type="hidden" name="appointment_id" value="<?= $row['appointments_id'] ?>">
          <select name="status" onchange="this.form.submit()">
            <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Accepted" <?= $row['status'] === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
            <option value="Admitted" <?= $row['status'] === 'Admitted' ? 'selected' : '' ?>>Admitted</option>
            <option value="Discharged" <?= $row['status'] === 'Discharged' ? 'selected' : '' ?>>Discharged</option>
          </select>
        </form>
      </td>
      <td>
        <a href="?delete=<?= $row['appointments_id'] ?>" onclick="return confirm('Delete this appointment?')">
          <button class="btn-del">Delete</button>
        </a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>

  <div id="archive" class="archive-section">
    <h2>Archived Patients (Discharged)</h2>
    <table>
      <tr>
        <th>Patient Name</th>
        <th>Discharged On</th>
      </tr>
      <?php while ($arch = $archived->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($arch['patient_name']) ?></td>
        <td><?= htmlspecialchars($arch['archived_at']) ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div>
<script>
  function toggleArchive() {
    const archive = document.getElementById('archive');
    archive.style.display = archive.style.display === 'block' ? 'none' : 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
</script>
</body>
</html>
