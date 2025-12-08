<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require __DIR__ . '/config/db.php';

$role       = $_SESSION['role'] ?? null;
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['user_name']);

// Only students can join events
if (!$isLoggedIn || $role !== 'student') {
    $eventParam = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
    header("Location: event_details.php?id={$eventParam}&joined=forbidden");
    exit();
}

$detailsPage = 'event_details.php';

// Get event_id from POST first (form), fallback to GET (if someone used a link)
$event_id = 0;
if (isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];
} elseif (isset($_GET['event_id'])) {
    $event_id = (int)$_GET['event_id'];
}

$student_name  = trim($_POST['student_name']  ?? '');
$student_email = trim($_POST['student_email'] ?? '');

// Use session values so the one-click join link works for logged-in students
if ($student_name === '' && !empty($_SESSION['user_name'])) {
    $student_name = $_SESSION['user_name'];
}
if ($student_email === '' && !empty($_SESSION['user_email'])) {
    $student_email = $_SESSION['user_email'];
}

// Basic validation
if ($event_id <= 0 || $student_name === '' || $student_email === '') {
    header("Location: {$detailsPage}?id={$event_id}&joined=error");
    exit();
}

// Make sure the event actually exists
$eventRes = $conn->query("SELECT capacity FROM events WHERE id = $event_id");
if (!$eventRes || $eventRes->num_rows === 0) {
    header("Location: {$detailsPage}?id=0&joined=error");
    exit();
}
$event    = $eventRes->fetch_assoc();
$capacity = (int)$event['capacity'];

// Count current confirmed registrations
$res = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE event_id = $event_id AND status = 'confirmed'");
$row = $res ? $res->fetch_assoc() : ['c' => 0];
$confirmedCount = (int)$row['c'];

$spotsLeft = ($capacity > 0) ? max(0, $capacity - $confirmedCount) : null;

// Duplicate check: same email already registered or waitlisted
$student_name_esc  = $conn->real_escape_string($student_name);
$student_email_esc = $conn->real_escape_string($student_email);

$dupRes = $conn->query("
    SELECT id FROM registrations
    WHERE event_id = $event_id AND student_email = '$student_email_esc'
    LIMIT 1
");
$dupWaitRes = $conn->query("
    SELECT id FROM waitlist
    WHERE event_id = $event_id AND student_email = '$student_email_esc'
    LIMIT 1
");

if (($dupRes && $dupRes->num_rows > 0) || ($dupWaitRes && $dupWaitRes->num_rows > 0)) {
    header("Location: {$detailsPage}?id={$event_id}&joined=duplicate");
    exit();
}

// Decide: confirmed or waitlist
if ($capacity > 0 && $spotsLeft <= 0) {
    // event full -> add to waitlist
    $sql = "
        INSERT INTO waitlist (event_id, student_name, student_email)
        VALUES ($event_id, '$student_name_esc', '$student_email_esc')
    ";
    if ($conn->query($sql) === TRUE) {
        header("Location: {$detailsPage}?id={$event_id}&joined=waitlisted");
        exit();
    } else {
        header("Location: {$detailsPage}?id={$event_id}&joined=error");
        exit();
    }
} else {
    // seats available (or unlimited) -> confirmed
    $sql = "
        INSERT INTO registrations (event_id, student_name, student_email, status)
        VALUES ($event_id, '$student_name_esc', '$student_email_esc', 'confirmed')
    ";
    if ($conn->query($sql) === TRUE) {
        header("Location: {$detailsPage}?id={$event_id}&joined=registered");
        exit();
    } else {
        header("Location: {$detailsPage}?id={$event_id}&joined=error");
        exit();
    }
}
