<?php
session_start();
include 'includes/db.php';
include 'includes/auth-handler.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login / Sign Up</title>
  <link rel="stylesheet" href="style/login-style.css" />
</head>
<body>
  <div class="container" id="container">
    <div class="form-container sign-up-container">
      <form method="POST">
        <h1>Create Account</h1>
        <?php if (!empty($signup_error)) echo "<p style='color:red;'>$signup_error</p>"; ?>
        <?php if (!empty($signup_success)) echo "<p style='color:green;'>$signup_success</p>"; ?>
        <input type="text" name="name" placeholder="Name" required autocomplete="name" />
        <input type="email" name="email" placeholder="Email" required autocomplete="email" />
        <input type="password" name="password" placeholder="Password" required minlength="6" autocomplete="new-password" />
        <button type="submit" name="signup">Sign Up</button>
      </form>
    </div>

    <div class="form-container sign-in-container">
      <form method="POST">
        <img src="style/loginlogo.png" alt="Logo" style="height: 50px; margin-bottom: 10px;">
        <h1>Sign in</h1>
        <?php if (!empty($login_error)) echo "<p style='color:red;'>$login_error</p>"; ?>
        <input type="email" name="email" placeholder="Email" required autocomplete="email" />
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password" />
        <button type="submit" name="login">Login</button>
      </form>
    </div>

    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>Welcome Back!</h1>
          <p>Already have an account? Log in here.</p>
          <button class="ghost" id="signIn">Sign In</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>Hello, USER!</h1>
          <p>Don't have an account yet? Sign up here.</p>
          <button class="ghost" id="signUp">Sign Up</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const signUpButton = document.getElementById("signUp");
    const signInButton = document.getElementById("signIn");
    const container = document.getElementById("container");

    signUpButton.addEventListener("click", () => {
      container.classList.add("right-panel-active");
    });

    signInButton.addEventListener("click", () => {
      container.classList.remove("right-panel-active");
    });

    <?php if (!empty($signup_error) || !empty($signup_success)): ?>
      container.classList.add("right-panel-active");
    <?php endif; ?>
  </script>
</body>
</html>
