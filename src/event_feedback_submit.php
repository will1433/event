<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require __DIR__ . '/config/db.php';

$role       = $_SESSION['role'] ?? null;
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['user_name']);
$userEmail  = $_SESSION['user_email'] ?? '';
$userName   = $_SESSION['user_name'] ?? '';

// Only logged-in students may post feedback
if (!$isLoggedIn || $role !== 'student' || !$userEmail) {
    $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    header("Location: event_details.php?id={$eventId}&feedback=forbidden");
    exit();
}

$eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
$rating  = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = trim($_POST['comments'] ?? '');

// Ensure feedback table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'event_feedback'");
if (!$tableCheck || $tableCheck->num_rows === 0) {
    header("Location: event_details.php?id={$eventId}&feedback=error");
    exit();
}

if ($eventId <= 0 || $rating < 1 || $rating > 5) {
    header("Location: event_details.php?id={$eventId}&feedback=error");
    exit();
}

// Confirm event exists
$evtStmt = $conn->prepare("SELECT id FROM events WHERE id = ?");
$evtStmt->bind_param("i", $eventId);
$evtStmt->execute();
$evtRes = $evtStmt->get_result();
$event  = $evtRes->fetch_assoc();
$evtStmt->close();

if (!$event) {
    header("Location: event_details.php?id=0&feedback=error");
    exit();
}

// Confirm student is registered (status confirmed)
$regStmt = $conn->prepare("
    SELECT id FROM registrations
    WHERE event_id = ? AND student_email = ? AND status = 'confirmed'
    LIMIT 1
");
$regStmt->bind_param("is", $eventId, $userEmail);
$regStmt->execute();
$regRes = $regStmt->get_result();
$regRow = $regRes->fetch_assoc();
$regStmt->close();

if (!$regRow) {
    header("Location: event_details.php?id={$eventId}&feedback=forbidden");
    exit();
}

// Upsert feedback (unique on event_id + student_email)
$existingStmt = $conn->prepare("
    SELECT id FROM event_feedback
    WHERE event_id = ? AND student_email = ?
    LIMIT 1
");
$existingStmt->bind_param("is", $eventId, $userEmail);
$existingStmt->execute();
$existingRes = $existingStmt->get_result();
$existing    = $existingRes->fetch_assoc();
$existingStmt->close();

if ($existing) {
    $upd = $conn->prepare("
        UPDATE event_feedback
        SET rating = ?, comments = ?
        WHERE id = ?
    ");
    $upd->bind_param("isi", $rating, $comment, $existing['id']);
    $ok = $upd->execute();
    $upd->close();
    $flag = $ok ? 'updated' : 'error';
} else {
    $ins = $conn->prepare("
        INSERT INTO event_feedback (event_id, student_email, rating, comments)
        VALUES (?, ?, ?, ?)
    ");
    $ins->bind_param("isis", $eventId, $userEmail, $rating, $comment);
    $ok = $ins->execute();
    $ins->close();
    $flag = $ok ? 'submitted' : 'error';
}

header("Location: event_details.php?id={$eventId}&feedback={$flag}");
exit();
