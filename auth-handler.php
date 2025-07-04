<?php
$login_error = "";
$signup_error = "";
$signup_success = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["login"])) {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $user = $result->fetch_assoc()) {
            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] === "admin") {
                    header("Location: admin.php");
                } elseif ($user["role"] === "medstaff") {
                    header("Location: medstaff-dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            }
        }

        $login_error = "Invalid email or password.";
        $stmt->close();
    }

    if (isset($_POST["signup"])) {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $role = "medicalstaff";

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $signup_error = "Email is already registered.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $role);
            if ($stmt->execute()) {
                $signup_success = "Account created! You can now log in.";
            } else {
                $signup_error = "Something went wrong. Try again.";
            }
            $stmt->close();
        }
        $check->close();
    }

    $conn->close();
}
?>
