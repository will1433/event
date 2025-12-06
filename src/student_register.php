<?php
session_start();
require __DIR__ . '/config/db.php';

$name = '';
$email = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($name === '')   $errors[] = "Name is required.";
    if ($email === '')  $errors[] = "Email is required.";
    if ($pass === '')   $errors[] = "Password is required.";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($errors)) {
        // check if email already used
        $emailEsc = $conn->real_escape_string($email);
        $res = $conn->query("SELECT id FROM students WHERE email = '$emailEsc' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $errors[] = "Email is already registered. Please login.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $nameEsc = $conn->real_escape_string($name);

            $sql = "INSERT INTO students (name, email, password)
                    VALUES ('$nameEsc', '$emailEsc', '$hash')";
            if ($conn->query($sql)) {
                $success = "Account created. You can now login.";
                // optional: auto-login
                // $_SESSION['student_id'] = $conn->insert_id;
                // $_SESSION['student_name'] = $name;
                // header("Location: student_events.php");
                // exit;
            } else {
                $errors[] = "Something went wrong, try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Register</title>
    <link rel="stylesheet" href="assets/css/student.css">
</head>
<body>
<div class="student-container">
    <h1 class="title">Student Register</h1>
    <p class="subtitle">Create an account to join events.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="join-box">
        <form method="POST">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn-join">Register</button>
        </form>

        <p style="margin-top:10px;font-size:13px;">
            Already have an account?
            <a href="student_login.php" style="color:#2563eb;">Login here</a>.
        </p>
    </div>
</div>
</body>
</html>
