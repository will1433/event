<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}
require __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die("Invalid event ID.");

$eventRes = $conn->query("SELECT * FROM events WHERE id = $id");
if (!$eventRes || $eventRes->num_rows === 0) die("Event not found.");
$event = $eventRes->fetch_assoc();

$capacity = (int)$event['capacity'];
$confirmed = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE event_id=$id AND status='confirmed'")
                 ->fetch_assoc()['c'];
$waitlisted = $conn->query("SELECT COUNT(*) AS c FROM waitlist WHERE event_id=$id")
                  ->fetch_assoc()['c'];

$spotsLeft = ($capacity > 0) ? max(0, $capacity - $confirmed) : null;

$status = $_GET['joined'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($event['title']); ?></title>
    <link rel="stylesheet" href="assets/css/student.css">
</head>
<body>

<div class="student-container">

    <a href="student_events.php" class="back-link">← Back to events</a>

    <h1><?php echo htmlspecialchars($event['title']); ?></h1>
    <p class="subtitle">
        <?php echo date("F j, Y", strtotime($event['event_date'])); ?>
        • <?php echo $event['event_time'] ?: "All day"; ?>
        • <?php echo htmlspecialchars($event['location']); ?>
    </p>

    <?php if ($status == "registered"): ?>
        <div class="alert success">You successfully joined the event!</div>
    <?php elseif ($status == "waitlisted"): ?>
        <div class="alert warning">The event is full — you are on the waitlist.</div>
    <?php elseif ($status == "duplicate"): ?>
        <div class="alert danger">You already registered for this event.</div>
    <?php elseif ($status == "error"): ?>
        <div class="alert danger">There was an error. Try again.</div>
    <?php endif; ?>

    <div class="event-details">
        <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>

        <p><strong>Capacity:</strong>
            <?php echo $capacity > 0 ? "$confirmed / $capacity" : "Unlimited"; ?>
        </p>
    </div>

    <div class="join-box">
        <h3>Join this Event</h3>

        <form method="POST" action="event_register.php">
            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">

            <label>Your name</label>
            <input type="text" name="student_name" required>

            <label>Your email</label>
            <input type="email" name="student_email" required>

            <button type="submit" class="btn-join">Join Event</button>
        </form>
    </div>

</div>

</body>
</html>
