<?php
session_start();
require __DIR__ . '/config/db.php';   // make sure this is correct

$login_error      = '';
$register_error   = '';
$register_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    // ===============================
    // LOGIN (ADMIN + STUDENT) - MYSQLI
    // ===============================
    if ($action === 'login') {
        $identifier = trim($_POST['identifier'] ?? ''); // admin username OR student email
        $password   = $_POST['password'] ?? '';

        if ($identifier === '' || $password === '') {
            $login_error = "Please enter your username/email and password.";
        } else {
            $passwordHash = hash('sha256', $password);

            // 1) Try admin (username)
            $sqlAdmin = "SELECT id, username FROM admins WHERE username = ? AND password = ?";
            if ($stmt = $conn->prepare($sqlAdmin)) {
                $stmt->bind_param("ss", $identifier, $passwordHash);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($adminId, $adminUsername);
                    $stmt->fetch();

                    $_SESSION['user_id']   = $adminId;
                    $_SESSION['user_name'] = $adminUsername;
                    $_SESSION['role']      = 'admin';

                    $stmt->close();
                    header("Location: dashboard.php");
                    exit;
                }
                $stmt->close();
            }

            // 2) Try student (email)
            $sqlStudent = "SELECT id, name, email FROM students WHERE email = ? AND password = ?";
            if ($stmt = $conn->prepare($sqlStudent)) {
                $stmt->bind_param("ss", $identifier, $passwordHash);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($studentId, $studentName, $studentEmail);
                    $stmt->fetch();

                    $_SESSION['user_id']    = $studentId;
                    $_SESSION['user_name']  = $studentName;
                    $_SESSION['user_email'] = $studentEmail;
                    $_SESSION['role']       = 'student';

                    $stmt->close();
                    header("Location: dashboard.php");
                    exit;
                }
                $stmt->close();
            }

            // If neither admin nor student matched
            $login_error = "Invalid credentials. Please try again.";
        }
    }

    // ===============================
    // STUDENT REGISTRATION - MYSQLI
    // ===============================
    if ($action === 'register') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        $confirm  = $_POST['reg_confirm_password'] ?? '';

        if ($name === '' || $email === '' || $password === '' || $confirm === '') {
            $register_error = "Please fill in all fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $register_error = "Please enter a valid email.";
        } elseif ($password !== $confirm) {
            $register_error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $register_error = "Password should be at least 6 characters.";
        } else {
            // Check if email already exists
            $sqlCheck = "SELECT id FROM students WHERE email = ?";
            if ($stmt = $conn->prepare($sqlCheck)) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $register_error = "This email is already registered.";
                    $stmt->close();
                } else {
                    $stmt->close();

                    $passwordHash = hash('sha256', $password);
                    $sqlInsert = "INSERT INTO students (name, email, password) VALUES (?, ?, ?)";
                    if ($stmt = $conn->prepare($sqlInsert)) {
                        $stmt->bind_param("sss", $name, $email, $passwordHash);
                        if ($stmt->execute()) {
                            $register_success = "Account created! You can log in now.";
                        } else {
                            $register_error = "Something went wrong. Please try again.";
                        }
                        $stmt->close();
                    } else {
                        $register_error = "Database error (insert).";
                    }
                }
            } else {
                $register_error = "Database error (check).";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Campus Event System - Login</title>
    <!-- ✅ Make sure this path matches: /var/www/html/assets/css/login.css -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="auth-container">

    <div class="tabs">
        <button class="tab-btn active" data-target="#login-form">Login</button>
        <button class="tab-btn" data-target="#register-form">Register</button>
    </div>

    <!-- LOGIN FORM -->
    <?php if ($login_error): ?>
        <div class="alert error"><?php echo htmlspecialchars($login_error); ?></div>
    <?php endif; ?>

    <form id="login-form" class="form active" method="POST">
        <input type="hidden" name="action" value="login">

        <div class="field">
            <label>Username (Admin) OR Email (Student)</label>
            <input type="text" name="identifier" placeholder="admin or student@school.edu" required>
        </div>

        <div class="field">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button class="btn-primary" type="submit">Log In</button>
    </form>

    <!-- REGISTER FORM -->
    <?php if ($register_error): ?>
        <div class="alert error"><?php echo htmlspecialchars($register_error); ?></div>
    <?php endif; ?>

    <?php if ($register_success): ?>
        <div class="alert success"><?php echo htmlspecialchars($register_success); ?></div>
    <?php endif; ?>

    <form id="register-form" class="form" method="POST">
        <input type="hidden" name="action" value="register">

        <div class="field">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="John Doe" required>
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email" placeholder="student@school.edu" required>
        </div>

        <div class="field">
            <label>Password</label>
            <input type="password" name="reg_password" required>
        </div>

        <div class="field">
            <label>Confirm Password</label>
            <input type="password" name="reg_confirm_password" required>
        </div>

        <button class="btn-primary" type="submit">Create Account</button>
    </form>

</div>

<script>
  const buttons = document.querySelectorAll('.tab-btn');
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');

  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      buttons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      if (btn.dataset.target === "#login-form") {
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
      } else {
        registerForm.classList.add('active');
        loginForm.classList.remove('active');
      }
    });
  });
</script>

</body>
</html>
