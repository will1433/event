<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

// --- user + role ---
$role      = $_SESSION['role'] ?? 'student';   // default to student if not set
$isAdmin   = ($role === 'admin');
$userName  = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? null;

// ---------- FETCH EVENTS ----------
$events = [];

if ($isAdmin) {
    // Admin sees all events + counts
    $sql = "
        SELECT 
            e.*,
            (SELECT COUNT(*) FROM registrations r 
             WHERE r.event_id = e.id AND r.status = 'confirmed') AS reg_count,
            (SELECT COUNT(*) FROM waitlist w 
             WHERE w.event_id = e.id) AS wait_count
        FROM events e
        ORDER BY e.event_date DESC, e.event_time ASC
    ";
} else {
    // Student sees all events + their own status
    $emailEsc = $userEmail ? $conn->real_escape_string($userEmail) : '';

    $sql = "
        SELECT 
            e.*,
            (SELECT COUNT(*) FROM registrations r2 
             WHERE r2.event_id = e.id AND r2.status = 'confirmed') AS reg_count,
            (SELECT COUNT(*) FROM waitlist w2 
             WHERE w2.event_id = e.id) AS wait_count,
            (SELECT r3.status 
               FROM registrations r3 
              WHERE r3.event_id = e.id 
                AND r3.student_email = '{$emailEsc}'
              LIMIT 1) AS my_reg_status,
            (SELECT 1
               FROM waitlist w3
              WHERE w3.event_id = e.id
                AND w3.student_email = '{$emailEsc}'
              LIMIT 1) AS my_wait_flag
        FROM events e
        ORDER BY e.event_date DESC, e.event_time ASC
    ";
}

if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $events[] = $row;
    }
}
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">
                <?php echo $isAdmin ? 'All Events' : 'Events'; ?>
            </h1>
        </div>
    </header>

    <div class="content">
        <div class="content-main">
            <section class="panel">
                <div class="panel-header">
                    <h2>Events List</h2>
                    <span class="panel-subtitle">
                        <?php echo count($events); ?> event(s)
                        <?php echo $isAdmin ? ' • Manage events' : ' • View and join events'; ?>
                    </span>
                </div>

                <?php if (count($events) === 0): ?>
                    <p>No events yet.</p>
                <?php else: ?>
                    <table class="events-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Capacity</th>
                                <th>Registered</th>
                                <th>Waitlist</th>
                                <?php if (!$isAdmin): ?>
                                    <th>My Status</th>
                                <?php endif; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($events as $ev): ?>
                            <?php
                                $dateText = $ev['event_date'] 
                                    ? date('Y-m-d', strtotime($ev['event_date']))
                                    : '';

                                $timeText = $ev['event_time'] ?: '';
                                $location = $ev['location'] ?? '';
                                $capacity = (int)$ev['capacity'];

                                $regCount  = isset($ev['reg_count']) ? (int)$ev['reg_count'] : 0;
                                $waitCount = isset($ev['wait_count']) ? (int)$ev['wait_count'] : 0;

                                // For student status
                                $myStatus = 'Not joined';
                                if (!$isAdmin) {
                                    if (!empty($ev['my_reg_status'])) {
                                        $myStatus = 'Joined';
                                    } elseif (!empty($ev['my_wait_flag'])) {
                                        $myStatus = 'Waitlisted';
                                    }
                                }
                            ?>
                            <tr>
                                <td>
                                    <a href="event_details.php?id=<?php echo $ev['id']; ?>">
                                        <?php echo htmlspecialchars($ev['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($dateText); ?></td>
                                <td><?php echo htmlspecialchars($timeText); ?></td>
                                <td><?php echo htmlspecialchars($location); ?></td>
                                <td><?php echo $capacity; ?></td>
                                <td><?php echo $regCount; ?></td>
                                <td><?php echo $waitCount; ?></td>

                                <?php if (!$isAdmin): ?>
                                    <td>
                                        <?php if ($myStatus === 'Joined'): ?>
                                            <span class="badge" style="background:#dcfce7; color:#166534;">Joined</span>
                                        <?php elseif ($myStatus === 'Waitlisted'): ?>
                                            <span class="badge" style="background:#fef3c7; color:#92400e;">Waitlisted</span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#e5e7eb; color:#374151;">Not joined</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>

                                <td>
                                    <?php if ($isAdmin): ?>
                                        <a href="event_edit.php?id=<?php echo $ev['id']; ?>"><i data-feather="edit"></i> Edit</a>
                                        &nbsp;|&nbsp;
                                        <a href="event_delete.php?id=<?php echo $ev['id']; ?>"
                                           onclick="return confirm('Delete this event?');">
                                            <i data-feather="trash-2"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <?php if ($myStatus === 'Not joined'): ?>
                                            <a href="event_register.php?event_id=<?php echo $ev['id']; ?>">
                                                <i data-feather="user-plus"></i>Join
                                            </a>
                                        <?php else: ?>
                                            <a href="event_details.php?id=<?php echo $ev['id']; ?>">
                                                <i data-feather="eye"></i>View
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
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
