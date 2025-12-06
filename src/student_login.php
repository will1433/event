<?php
session_start();
require __DIR__ . '/config/db.php';

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $errors[] = "Email and password are required.";
    } else {
        $emailEsc = $conn->real_escape_string($email);
        $res = $conn->query("SELECT id, name, email, password FROM students WHERE email = '$emailEsc' LIMIT 1");
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($pass, $row['password'])) {
                // login success
                $_SESSION['student_id']   = $row['id'];
                $_SESSION['student_name'] = $row['name'];
                $_SESSION['student_email'] = $row['email'];

                header("Location: student_dashboard.php");
                exit();
            } else {
                $errors[] = "Incorrect email or password.";
            }
        } else {
            $errors[] = "Incorrect email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <link rel="stylesheet" href="assets/css/student.css">
</head>
<body>
<div class="student-container">
    <h1 class="title">Student Login</h1>
    <p class="subtitle">Login to see and join events.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
        </div>
    <?php endif; ?>

    <div class="join-box">
        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn-join">Login</button>
        </form>

        <p style="margin-top:10px;font-size:13px;">
            Don’t have an account?
            <a href="student_register.php" style="color:#2563eb;">Register here</a>.
        </p>
    </div>
</div>
</body>
</html>
