<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

require __DIR__ . '/config/db.php';

$sql = "SELECT id, title, description, event_date, event_time, location, capacity
        FROM events 
        WHERE event_date >= CURDATE()
        ORDER BY event_date, event_time";
$res = $conn->query($sql);
$events = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>School Events</title>
    <link rel="stylesheet" href="assets/css/student.css">
</head>
<body>

<div class="student-container">
    <h1 class="title">Upcoming Events</h1>
    <p class="subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>. Browse upcoming events.</p>

    <div class="events-list">
        <?php if (count($events) === 0): ?>
            <div class="empty-msg">No upcoming events.</div>
        <?php else: ?>
            <?php foreach ($events as $ev): ?>
                <div class="event-card">
                    <h2><?php echo htmlspecialchars($ev['title']); ?></h2>
                    <p class="desc">
                        <?php echo htmlspecialchars($ev['description']); ?>
                    </p>

                    <div class="meta">
                        <span><?php echo date("F j, Y", strtotime($ev['event_date'])); ?></span>
                        <span>•</span>
                        <span><?php echo $ev['event_time'] ?: 'All day'; ?></span>
                        <span>•</span>
                        <span><?php echo htmlspecialchars($ev['location']); ?></span>
                    </div>

                    <a class="btn-view" href="student_event_details.php?id=<?php echo $ev['id']; ?>">
                        View & Join
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
