<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

require __DIR__ . '/config/db.php';

$studentId = (int)$_SESSION['student_id'];

// ---- defaults so we never get "undefined variable" warnings ----
$studentName   = 'Student';
$studentEmail  = '';
$events        = [];
$myEvents      = [];
$noticeEvents  = [];
$todayEvents   = [];
$totalUpcoming = 0;
$totalMine     = 0;
$confirmedMine = 0;
$waitlistedMine = 0;

// ---- STUDENT INFO (students table) ----
$stuRes = $conn->query(
    "SELECT name, email FROM students WHERE id = $studentId LIMIT 1"
);
if ($stuRes && $stuRes->num_rows > 0) {
    $stu          = $stuRes->fetch_assoc();
    $studentName  = $stu['name']  ?: 'Student';
    $studentEmail = $stu['email'] ?: '';
}

// ---- UPCOMING EVENTS (events table) ----
$evRes = $conn->query("
    SELECT id, title, description, event_date, event_time, location
    FROM events
    WHERE event_date >= CURDATE()
    ORDER BY event_date, event_time
");
if ($evRes) {
    while ($row = $evRes->fetch_assoc()) {
        $events[] = $row;
    }
}
$totalUpcoming = count($events);

// ---- MY REGISTRATIONS (registrations table, match by student_email) ----
if ($studentEmail !== '') {
    $emailEsc = $conn->real_escape_string($studentEmail);

    $regRes = $conn->query("
        SELECT r.status,
               e.id AS event_id,
               e.title,
               e.event_date,
               e.event_time,
               e.location
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.student_email = '$emailEsc'
        ORDER BY e.event_date, e.event_time
    ");
    if ($regRes) {
        while ($row = $regRes->fetch_assoc()) {
            $myEvents[] = $row;
        }
    }

    $totalMine = count($myEvents);

    foreach ($myEvents as $m) {
        if ($m['status'] === 'confirmed') {
            $confirmedMine++;
        }
    }

    // ---- MY WAITLIST COUNT (waitlist table) ----
    $waitRes = $conn->query("
        SELECT COUNT(*) AS c
        FROM waitlist
        WHERE student_email = '$emailEsc'
    ");
    if ($waitRes) {
        $waitlistedMine = (int)$waitRes->fetch_assoc()['c'];
    }
}

// ---- NOTICE BOARD (latest 5 events) ----
$noteRes = $conn->query("
    SELECT title, description, event_date
    FROM events
    ORDER BY event_date DESC
    LIMIT 5
");
if ($noteRes) {
    while ($row = $noteRes->fetch_assoc()) {
        $noticeEvents[] = $row;
    }
}

// ---- TODAY’S TIMELINE (events happening today) ----
$todayRes = $conn->query("
    SELECT title, location, event_time
    FROM events
    WHERE event_date = CURDATE()
    ORDER BY event_time
");
if ($todayRes) {
    while ($row = $todayRes->fetch_assoc()) {
        $todayEvents[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- same styles as admin -->
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/events.css">
    <link rel="stylesheet" href="assets/css/student.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="layout">
    <!-- SIDEBAR (student version) -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-circle">SE</div>
            <div class="logo-text">
                <span class="logo-title">School Events</span>
                <span class="logo-sub">Dashboard</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="student_dashboard.php" class="nav-item">
                <i data-feather="home" class="nav-icon"></i>
                Dashboard
            </a>
            <a href="student_events.php" class="nav-item">
                <i data-feather="calendar" class="nav-icon"></i>
                Browse Events
            </a>
            <a href="student_logout.php" class="nav-item logout">
                <i data-feather="log-out" class="nav-icon"></i>
                Logout
            </a>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="content">
        <div class="student-container">

            <!-- Top bar -->
            <div class="student-topbar">
                <div>
                    <h1 class="title">Hi, <?php echo htmlspecialchars($studentName); ?></h1>
                    <p class="subtitle">Here’s your event overview.</p>
                </div>
            </div>

            <!-- Stat cards -->
            <div class="student-stats">
                <div class="student-stat-card">
                    <span class="student-stat-label">Upcoming events</span>
                    <span class="student-stat-value"><?php echo $totalUpcoming; ?></span>
                </div>
                <div class="student-stat-card">
                    <span class="student-stat-label">Events you joined</span>
                    <span class="student-stat-value"><?php echo $totalMine; ?></span>
                </div>
                <div class="student-stat-card">
                    <span class="student-stat-label">Confirmed spots</span>
                    <span class="student-stat-value"><?php echo $confirmedMine; ?></span>
                </div>
                <div class="student-stat-card">
                    <span class="student-stat-label">Waitlisted</span>
                    <span class="student-stat-value"><?php echo $waitlistedMine; ?></span>
                </div>
            </div>

            <!-- Overview + Calendar -->
            <div class="student-main-grid">
                <div class="student-card">
                    <h3>Events Overview</h3>
                    <div class="student-chart">Chart goes here</div>
                </div>

                <div class="student-card student-calendar">
                    <h3>Event Calendar</h3>
                    <div class="student-calendar-box">
                        Calendar here
                    </div>
                </div>
            </div>

            <!-- Notice board + today's timeline -->
            <div class="student-main-grid">
                <div class="student-card">
                    <h3>Notice Board</h3>
                    <?php if (empty($noticeEvents)): ?>
                        <p class="empty-msg">No announcements yet.</p>
                    <?php else: ?>
                        <?php foreach ($noticeEvents as $ev): ?>
                            <div class="student-notice-item">
                                <div class="student-notice-title">
                                    <?php echo htmlspecialchars($ev['title']); ?>
                                </div>
                                <div class="student-notice-body">
                                    <?php echo htmlspecialchars($ev['description'] ?: 'No description'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="student-card student-timeline">
                    <h3>Today's Timeline</h3>
                    <?php if (empty($todayEvents)): ?>
                        <p class="empty-msg">No events today.</p>
                    <?php else: ?>
                        <?php foreach ($todayEvents as $ev): ?>
                            <div class="student-timeline-item">
                                <?php echo htmlspecialchars($ev['event_time'] ?: 'All day'); ?>
                                — <?php echo htmlspecialchars($ev['title']); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- My Events -->
            <h2 class="section-title" style="margin-top:24px;">My Events</h2>
            <?php if ($totalMine === 0): ?>
                <p class="empty-msg">You haven’t joined any events yet.</p>
            <?php else: ?>
                <div class="event-list-compact">
                    <?php foreach ($myEvents as $ev): ?>
                        <div class="event-row">
                            <div>
                                <div class="event-row-title">
                                    <?php echo htmlspecialchars($ev['title']); ?>
                                </div>
                                <div class="event-row-meta">
                                    <?php
                                        $dateText = $ev['event_date']
                                            ? date('M j, Y', strtotime($ev['event_date']))
                                            : 'TBA';
                                        $timeText = $ev['event_time'] ?: 'All day';
                                        $loc      = $ev['location'] ?: 'TBA';
                                    ?>
                                    <?php echo $dateText . ' • ' . htmlspecialchars($timeText); ?> —
                                    <?php echo htmlspecialchars($loc); ?>
                                </div>
                            </div>
                            <div class="event-row-status">
                                <?php if ($ev['status'] === 'confirmed'): ?>
                                    <span class="badge-student badge-confirmed">Confirmed</span>
                                <?php else: ?>
                                    <span class="badge-student badge-waitlist">Waitlist</span>
                                <?php endif; ?>
                                <a href="student_event_details.php?id=<?php echo (int)$ev['event_id']; ?>"
                                   class="link-small">
                                    View
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Upcoming Events list -->
            <h2 class="section-title" style="margin-top:24px;">Upcoming Events</h2>
            <?php if ($totalUpcoming === 0): ?>
                <p class="empty-msg">No upcoming events.</p>
            <?php else: ?>
                <div class="event-list-compact">
                    <?php foreach ($events as $ev): ?>
                        <div class="event-row">
                            <div>
                                <div class="event-row-title">
                                    <?php echo htmlspecialchars($ev['title']); ?>
                                </div>
                                <div class="event-row-meta">
                                    <?php
                                        $dateText = $ev['event_date']
                                            ? date('M j, Y', strtotime($ev['event_date']))
                                            : 'TBA';
                                        $timeText = $ev['event_time'] ?: 'All day';
                                        $loc      = $ev['location'] ?: 'TBA';
                                    ?>
                                    <?php echo $dateText . ' • ' . htmlspecialchars($timeText); ?> —
                                    <?php echo htmlspecialchars($loc); ?>
                                </div>
                            </div>
                            <div class="event-row-status">
                                <a href="student_event_details.php?id=<?php echo (int)$ev['id']; ?>"
                                   class="link-small primary">
                                    View &amp; Join
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div><!-- /.student-container -->
    </div><!-- /.content -->
</div><!-- /.layout -->

<script src="https://unpkg.com/feather-icons"></script>
<script> feather.replace(); </script>

</body>
</html>
