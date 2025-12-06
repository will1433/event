<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
if ($role !== 'admin') {
    // Only admin can see registrations page
    header("Location: dashboard.php");
    exit();
}

require __DIR__ . '/config/db.php';

$userName = $_SESSION['user_name'] ?? 'Admin';

// ---------- FETCH CONFIRMED REGISTRATIONS ----------
$registrations = [];

$sql = "
    SELECT 
        r.id,
        r.student_name,
        r.student_email,
        r.status,
        r.registered_at,
        e.id   AS event_id,
        e.title AS event_title,
        e.event_date,
        e.event_time,
        e.location
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    ORDER BY e.event_date DESC, e.event_time ASC, r.registered_at DESC
";

if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $registrations[] = $row;
    }
}

// ---------- FETCH WAITLIST ----------
$waitlist = [];

$sql2 = "
    SELECT 
        w.id,
        w.student_name,
        w.student_email,
        w.created_at,
        e.id   AS event_id,
        e.title AS event_title,
        e.event_date,
        e.event_time,
        e.location
    FROM waitlist w
    JOIN events e ON w.event_id = e.id
    ORDER BY e.event_date DESC, e.event_time ASC, w.created_at DESC
";

if ($res2 = $conn->query($sql2)) {
    while ($row = $res2->fetch_assoc()) {
        $waitlist[] = $row;
    }
}
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <!-- Top bar -->
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Registrations</h1>
        </div>
    </header>

    <div class="content">
        <div class="content-main">

            <!-- Confirmed registrations -->
            <section class="panel">
                <div class="panel-header">
                    <h2>Confirmed Registrations</h2>
                    <span class="panel-subtitle">
                        <?php echo count($registrations); ?> record(s)
                    </span>
                </div>

                <?php if (count($registrations) === 0): ?>
                    <p>No confirmed registrations yet.</p>
                <?php else: ?>
                    <table class="events-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date / Time</th>
                                <th>Location</th>
                                <th>Student Name</th>
                                <th>Student Email</th>
                                <th>Status</th>
                                <th>Registered At</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($registrations as $row): ?>
                            <?php
                                $dateText = $row['event_date']
                                    ? date('Y-m-d', strtotime($row['event_date']))
                                    : '';
                                $timeText = $row['event_time'] ?: '';
                            ?>
                            <tr>
                                <td>
                                    <a href="event_details.php?id=<?php echo $row['event_id']; ?>">
                                        <?php echo htmlspecialchars($row['event_title']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars(trim($dateText . ' ' . $timeText)); ?></td>
                                <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['student_email']); ?></td>
                                <td>
                                    <span class="badge" style="background:#dcfce7; color:#166534;">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['registered_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <!-- Waitlist -->
            <section class="panel">
                <div class="panel-header">
                    <h2>Waitlist</h2>
                    <span class="panel-subtitle">
                        <?php echo count($waitlist); ?> student(s) on waitlist
                    </span>
                </div>

                <?php if (count($waitlist) === 0): ?>
                    <p>No students on waitlist.</p>
                <?php else: ?>
                    <table class="events-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date / Time</th>
                                <th>Location</th>
                                <th>Student Name</th>
                                <th>Student Email</th>
                                <th>Added At</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($waitlist as $row): ?>
                            <?php
                                $dateText = $row['event_date']
                                    ? date('Y-m-d', strtotime($row['event_date']))
                                    : '';
                                $timeText = $row['event_time'] ?: '';
                            ?>
                            <tr>
                                <td>
                                    <a href="event_details.php?id=<?php echo $row['event_id']; ?>">
                                        <?php echo htmlspecialchars($row['event_title']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars(trim($dateText . ' ' . $timeText)); ?></td>
                                <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['student_email']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>

        <aside class="content-side"></aside>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
