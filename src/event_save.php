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
