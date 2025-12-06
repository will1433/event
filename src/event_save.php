<<<<<<< HEAD
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// accept either user_id or user_name as "logged in"
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

// --- user info from session ---
$role      = $_SESSION['role'] ?? 'admin';
$userName  = $_SESSION['user_name'] ?? 'admin';
$userEmail = $_SESSION['user_email'] ?? null;

// Decide created_by:
// - student -> their email (so "My Events" works)
// - admin   -> their username
if ($role === 'student' && $userEmail) {
    $created_by_raw = $userEmail;
} else {
    $created_by_raw = $userName ?: 'admin';
}

// --- form fields ---
$title       = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$event_date  = $_POST['event_date'] ?? null;
$event_time  = $_POST['event_time'] ?? '';
$location    = $_POST['location'] ?? '';
$capacity    = (int)($_POST['capacity'] ?? 0);

$image_path = null;

// handle image upload
if (!empty($_FILES['event_image']['name'])) {
    if (!is_dir(__DIR__ . '/uploads')) {
        mkdir(__DIR__ . '/uploads', 0777, true);
    }

    $fileName   = time() . "_" . basename($_FILES['event_image']['name']);
    $targetPath = __DIR__ . "/uploads/" . $fileName;

    if (move_uploaded_file($_FILES['event_image']['tmp_name'], $targetPath)) {
        // we just store the filename; your other pages already expect that
        $image_path = $fileName;
    }
}

if ($title && $event_date) {
    // escape values for SQL
    $title       = $conn->real_escape_string($title);
    $description = $conn->real_escape_string($description);
    $event_time  = $conn->real_escape_string($event_time);
    $location    = $conn->real_escape_string($location);
    $created_by  = $conn->real_escape_string($created_by_raw);

    $image_value = $image_path ? "'" . $conn->real_escape_string($image_path) . "'" : "NULL";

    $sql = "
        INSERT INTO events (title, description, event_date, event_time, location, capacity, created_by, image_path)
        VALUES ('$title', '$description', '$event_date', '$event_time', '$location', $capacity, '$created_by', $image_value)
    ";

    if ($conn->query($sql) === TRUE) {
        header("Location: event_list.php");
        exit();
    } else {
        echo "DB ERROR: " . $conn->error;
    }
} else {
    echo "Title and Date are required.";
}
=======
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

$title       = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$event_date  = $_POST['event_date'] ?? null;
$event_time  = $_POST['event_time'] ?? '';
$location    = $_POST['location'] ?? '';
$capacity    = (int)($_POST['capacity'] ?? 0);
$created_by  = $_SESSION['user'] ?? 'admin';

$image_path = null;

// handle image upload
if (!empty($_FILES['event_image']['name'])) {
    if (!is_dir(__DIR__ . '/uploads')) {
        mkdir(__DIR__ . '/uploads', 0777, true);
    }

    $fileName   = time() . "_" . basename($_FILES['event_image']['name']);
    $targetPath = __DIR__ . "/uploads/" . $fileName;

    if (move_uploaded_file($_FILES['event_image']['tmp_name'], $targetPath)) {
        $image_path = $fileName;
    }
}

if ($title && $event_date) {
    $title       = $conn->real_escape_string($title);
    $description = $conn->real_escape_string($description);
    $event_time  = $conn->real_escape_string($event_time);
    $location    = $conn->real_escape_string($location);
    $created_by  = $conn->real_escape_string($created_by);
    $image_value = $image_path ? "'" . $conn->real_escape_string($image_path) . "'" : "NULL";

    $sql = "
        INSERT INTO events (title, description, event_date, event_time, location, capacity, created_by, image_path)
        VALUES ('$title', '$description', '$event_date', '$event_time', '$location', $capacity, '$created_by', $image_value)
    ";

    if ($conn->query($sql) === TRUE) {
        header("Location: event_list.php");
        exit();
    } else {
        echo "DB ERROR: " . $conn->error;
    }
} else {
    echo "Title and Date are required.";
}
>>>>>>> 47c52acb341e02dac2adcee5692bda62f4bb8533
