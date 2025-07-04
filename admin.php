<?php
session_start();
include 'includes/db.php';

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get admin name
$admin_name = "Admin";
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ? AND role = 'admin'");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
if ($admin = $result->fetch_assoc()) {
    $admin_name = htmlspecialchars($admin["name"]);
}
$stmt->close();

// Get medstaff subscriptions
$sql = "
SELECT s.subscriptions_id, s.plan, s.status, s.subscribed_on,
       u.name AS staff_name, u.email
FROM subscriptions s
JOIN users u ON s.users_id = u.id
WHERE u.role = 'medicalstaff'
ORDER BY s.subscribed_on DESC
";
$subscriptions = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - Subscriptions</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <style>
    :root {
      --bg: #222d32;
      --content-bg: #293845;
      --text: #ecf0f1;
      --accent: #f39c12;
      --success: #27ae60;
      --danger: #e74c3c;
      --info: #3498db;
      --border: #3f4e5a;
    }
    body {
      margin: 0;
      background: var(--bg);
      font-family: sans-serif;
      color: var(--text);
      display: flex;
    }
    .sidebar {
      width: 240px;
      background: var(--bg);
      padding: 20px;
      height: 100vh;
    }
    .sidebar h2 {
      text-align: center;
      margin-bottom: 10px;
    }
    .sidebar ul {
      list-style: none;
      padding: 0;
    }
    .sidebar ul li a {
      display: block;
      padding: 10px 20px;
      color: var(--text);
      text-decoration: none;
    }
    .sidebar ul li a:hover,
    .sidebar ul li a.active {
      background: var(--content-bg);
      border-left: 4px solid var(--accent);
    }
    .main {
      flex-grow: 1;
      padding: 30px;
      background: var(--content-bg);
    }
    h1 {
      margin-top: 0;
    }
    table {
      width: 100%;
      margin-top: 20px;
      border-collapse: collapse;
      background: #34495e;
    }
    th, td {
      padding: 12px;
      border: 1px solid var(--border);
    }
    th {
      background: #3a4956;
      color: var(--accent);
    }
    .badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
      text-transform: capitalize;
    }
    .badge.active {
      background: #eafaf1;
      color: var(--success);
    }
    .badge.expired {
      background: #fff4e5;
      color: #f39c12;
    }
    .badge.cancelled {
      background: #ffe6e6;
      color: #e74c3c;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>Admin</h2>
  <ul>
    <li><a href="#subscriptions" class="active"><i class="fa fa-user-clock"></i> Subscriptions</a></li>
    <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main">
  <h1>Welcome, <?= $admin_name ?>!</h1>

  <section id="subscriptions">
    <h2>Medical Staff Subscriptions</h2>
    <table>
      <tr>
        <th>Staff Name</th>
        <th>Email</th>
        <th>Plan</th>
        <th>Status</th>
        <th>Subscribed On</th>
        <th>Ends On</th>
      </tr>
      <?php if ($subscriptions && $subscriptions->num_rows > 0): ?>
        <?php while ($row = $subscriptions->fetch_assoc()): ?>
          <?php
            $start = new DateTime($row['subscribed_on']);
            $end = clone $start;
            if ($row['plan'] === '1 month') $end->modify('+1 month');
            elseif ($row['plan'] === '5 months') $end->modify('+5 months');
            elseif ($row['plan'] === '1 year') $end->modify('+1 year');
          ?>
          <tr>
            <td><?= htmlspecialchars($row['staff_name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['plan']) ?></td>
            <td><span class="badge <?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
            <td><?= date("F j, Y", strtotime($row['subscribed_on'])) ?></td>
            <td><?= $end->format("F j, Y") ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6">No subscriptions found.</td></tr>
      <?php endif; ?>
    </table>
  </section>
</div>

</body>
</html>
