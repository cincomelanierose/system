<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$user_name = null;
$is_logged_in = isset($_SESSION["user_id"]);

if ($is_logged_in) {
    include_once 'includes/db.php'; // Ensure correct path
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_name = $row["name"];
    }
    $stmt->close();
}
?>
<header class="navbar">
  <div class="logo">🔍 <span>Health Finder</span></div>
  <div class="auth-buttons">
    <?php if ($is_logged_in): ?>
      <span style="font-weight: 600;">Welcome, <?= htmlspecialchars($user_name); ?></span>
      <a href="logout.php" class="btn-outline" onclick="return confirmLogout()">Logout</a>
    <?php else: ?>
      <a href="login.php" class="btn-outline">Login</a>
      <a href="signup.php" class="btn-outline">Sign Up</a>
    <?php endif; ?>
  </div>
</header>

<script>
  function confirmLogout() {
    return confirm('Are you sure you want to logout?');
  }
</script>
