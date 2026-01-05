<?php
// START SESSION IMMEDIATELY
require 'db.php';

// HANDLE REQUESTS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. REGISTER
    if (isset($_POST['register'])) {
        $u = $conn->real_escape_string($_POST['username']);
        $p = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Check if user exists
        $check = $conn->query("SELECT id FROM users WHERE username='$u'");
        if ($check->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $sql = "INSERT INTO users (username, password) VALUES ('$u', '$p')";
            if ($conn->query($sql) === TRUE) {
                $success = "Account created! Please Login.";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
    
    // 2. LOGIN
    if (isset($_POST['login'])) {
        $u = $conn->real_escape_string($_POST['username']);
        $p = $_POST['password'];
        
        $result = $conn->query("SELECT * FROM users WHERE username='$u'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($p, $row['password'])) {
                // SUCCESS: Set Session and Redirect
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                // Use JavaScript for reliable redirect on free hosting
                echo "<script>window.location.href='index.php';</script>";
                exit();
            } else {
                $error = "Wrong password.";
            }
        } else {
            $error = "User not found. Please Register.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Phoenix Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #0f0f13; color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #1d1d21; padding: 30px; border-radius: 12px; width: 320px; text-align: center; border: 1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        input { width: 100%; padding: 12px; margin: 8px 0; background: #000; border: 1px solid #333; color: white; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #00e676; border: none; font-weight: bold; cursor: pointer; border-radius: 6px; margin-top: 10px; font-size: 1rem; }
        .switch { margin-top: 20px; font-size: 0.9rem; color: #888; cursor: pointer; text-decoration: underline; }
        .msg { padding: 10px; margin-bottom: 10px; border-radius: 5px; font-size: 0.9rem; }
        .err { background: rgba(255, 68, 68, 0.1); color: #ff4444; border: 1px solid #ff4444; }
        .suc { background: rgba(0, 230, 118, 0.1); color: #00e676; border: 1px solid #00e676; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="color: #00e676; letter-spacing: 2px; margin-top: 0;">PHOENIX</h2>
        
        <?php if(isset($error)) echo "<div class='msg err'>$error</div>"; ?>
        <?php if(isset($success)) echo "<div class='msg suc'>$success</div>"; ?>
        
        <form id="login-form" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">ENTER PROTOCOL</button>
            <div style="margin-top: 20px; font-size: 0.8rem; color: #666; font-style: italic;">
    "For those who prefer a War Map over a Wellness Spa."
</div>
            <div class="switch" onclick="toggleForm()">New here? Create Account</div>
        </form>

        <form id="register-form" method="POST" style="display:none;">
            <input type="text" name="username" placeholder="Choose Username" required>
            <input type="password" name="password" placeholder="Choose Password" required>
            <button type="submit" name="register" style="background: #ff9800;">CREATE ACCOUNT</button>
            <div class="switch" onclick="toggleForm()">Have an account? Login</div>
        </form>
    </div>

    <script>
        function toggleForm() {
            var x = document.getElementById("login-form");
            var y = document.getElementById("register-form");
            if (x.style.display === "none") { x.style.display = "block"; y.style.display = "none"; } 
            else { x.style.display = "none"; y.style.display = "block"; }
        }
    </script>
</body>
</html>