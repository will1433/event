<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid event ID.");
}

// fetch event
$sql = "SELECT * FROM events WHERE id = $id";
$res = $conn->query($sql);
$event = $res ? $res->fetch_assoc() : null;

if (!$event) {
    die("Event not found.");
}

// counts for this event
$capacity = (int)($event['capacity'] ?? 0);

// confirmed registrations
$regCount = 0;
$resReg = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE event_id = $id AND status = 'confirmed'");
if ($resReg) {
    $regCount = (int)$resReg->fetch_assoc()['c'];
}

// waitlist count
$waitCount = 0;
$resWait = $conn->query("SELECT COUNT(*) AS c FROM waitlist WHERE event_id = $id");
if ($resWait) {
    $waitCount = (int)$resWait->fetch_assoc()['c'];
}

// logic: spots left
$spotsLeft = $capacity > 0 ? max($capacity - $regCount, 0) : null; // null = unlimited

// message after register
$statusMsg = '';
if (isset($_GET['joined'])) {
    if ($_GET['joined'] === 'registered') {
        $statusMsg = 'You are registered for this event.';
    } elseif ($_GET['joined'] === 'waitlisted') {
        $statusMsg = 'Event is full. You have been added to the waitlist.';
    } elseif ($_GET['joined'] === 'error') {
        $statusMsg = 'Something went wrong. Please try again.';
    }
}

// formatted fields
$eventTitle = $event['title'];
$eventDate  = $event['event_date'] ? date('F j, Y', strtotime($event['event_date'])) : '—';
$eventTime  = $event['event_time'] ?: 'All day';
$location   = $event['location'] ?: 'TBA';
$createdBy  = $event['created_by'] ?: 'Admin';
$createdAt  = $event['created_at'] ?: '';
$imagePath  = $event['image_path'] ?? '';
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Event Details</h1>
        </div>
        <div class="topbar-right">
            <a href="event_list.php" class="btn-primary" style="text-decoration:none;display:inline-block;">
                ← Back to Events
            </a>
        </div>
    </header>

    <div class="content">
        <div class="content-main">
            <section class="panel">
                <div class="event-details-layout">
                    <div>
                        <!-- LEFT SIDE: image + info + description -->
                        <?php if ($imagePath): ?>
                            <div style="margin-bottom:16px;">
                                <img src="uploads/<?php echo htmlspecialchars($imagePath); ?>"
                                     style="width:100%;max-height:260px;object-fit:cover;border-radius:12px;">
                            </div>
                        <?php endif; ?>

                        <div class="event-details-meta">
                            <span class="badge">📅 <?php echo $eventDate; ?></span>
                            <span class="badge">⏰ <?php echo htmlspecialchars($eventTime); ?></span>
                            <span class="badge">📍 <?php echo htmlspecialchars($location); ?></span>
                            <span class="badge">👥 Capacity: <?php echo $capacity > 0 ? $capacity : 'No limit'; ?></span>
                            <span class="badge badge-green">✅ Registered: <?php echo $regCount; ?></span>
                            <span class="badge badge-amber">🕒 Waitlist: <?php echo $waitCount; ?></span>
                        </div>

                        <div style="font-size:14px;line-height:1.5;margin-bottom:16px;">
                            <h3 style="font-size:15px;font-weight:600;margin-bottom:6px;">Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($event['description'] ?: 'No description provided.')); ?></p>
                        </div>

                        <div style="font-size:12px;color:#6b7280;margin-top:10px;">
                            <p>Created by: <?php echo htmlspecialchars($createdBy); ?></p>
                            <?php if ($createdAt): ?>
                                <p>Created at: <?php echo htmlspecialchars($createdAt); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- RIGHT SIDE: join card -->
                    <div class="join-card">
                        <h3>Join this event</h3>
                        <?php if ($capacity > 0 && $spotsLeft <= 0): ?>
                            <p style="color:#b91c1c;">
                                This event is full. You will be added to the waitlist.
                            </p>
                        <?php else: ?>
                            <p>Seats are available. Fill in your details to join.</p>
                        <?php endif; ?>

                        <?php if ($statusMsg): ?>
                            <div style="padding:8px 10px;margin-bottom:10px;border-radius:8px;
                                        background:#ecfdf5;color:#166534;font-size:12px;">
                                <?php echo htmlspecialchars($statusMsg); ?>
                            </div>
                        <?php endif; ?>

                        <form action="event_register.php" method="POST" class="event-form">
    <input type="hidden" name="event_id" value="<?php echo (int)$event['id']; ?>">

    <div class="form-row">
        <label for="student_name">Your name</label>
        <input type="text" name="student_name" id="student_name" required>
    </div>

    <div class="form-row">
        <label for="student_email">Your email</label>
        <input type="email" name="student_email" id="student_email" required>
    </div>

    <button type="submit" class="btn-primary">
        Join Event
    </button>
</form>

                    </div>
                </div>
            </section>
        </div>

        <aside class="content-side">
            <section class="panel">
                <div class="panel-header">
                    <h2>Quick Actions</h2>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:14px;">
                    <a href="event_edit.php?id=<?php echo $event['id']; ?>" style="text-decoration:none;">
                        ✏️ Edit this event
                    </a>
                    <a href="event_delete.php?id=<?php echo $event['id']; ?>"
                       onclick="return confirm('Delete this event?');"
                       style="text-decoration:none;color:#dc2626;">
                        🗑 Delete this event
                    </a>
                    <a href="event_list.php" style="text-decoration:none;">
                        📋 Back to events list
                    </a>
                </div>
            </section>
        </aside>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
