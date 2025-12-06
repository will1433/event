<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

$id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title       = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$event_date  = $_POST['event_date'] ?? null;
$event_time  = $_POST['event_time'] ?? '';
$location    = $_POST['location'] ?? '';
$capacity    = (int)($_POST['capacity'] ?? 0);
$current_img = $_POST['current_image'] ?? '';

if ($id <= 0 || !$title || !$event_date) {
    die("Invalid data.");
}

$image_path = $current_img;

// handle new image upload if provided
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

// escape values
$title       = $conn->real_escape_string($title);
$description = $conn->real_escape_string($description);
$event_time  = $conn->real_escape_string($event_time);
$location    = $conn->real_escape_string($location);
$image_sql   = $image_path ? "'" . $conn->real_escape_string($image_path) . "'" : "NULL";

// update query
$sql = "
    UPDATE events
    SET title       = '$title',
        description = '$description',
        event_date  = '$event_date',
        event_time  = '$event_time',
        location    = '$location',
        capacity    = $capacity,
        image_path  = $image_sql
    WHERE id = $id
";

if ($conn->query($sql) === TRUE) {
    header("Location: event_list.php");
    exit();
} else {
    echo "DB ERROR: " . $conn->error;
}
