<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // if later you use registrations + waitlist, these lines will clean them too
    $conn->query("DELETE FROM registrations WHERE event_id = $id");
    $conn->query("DELETE FROM waitlist WHERE event_id = $id");

    // delete event itself
    $conn->query("DELETE FROM events WHERE id = $id");
}

// go back to list page
header("Location: event_list.php");
exit();



