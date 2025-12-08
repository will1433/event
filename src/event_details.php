<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

$role      = $_SESSION['role'] ?? 'student';
$isAdmin   = ($role === 'admin');
$userName  = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? null;

// ---- Get event id from URL ----
$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($eventId <= 0) {
    die("Invalid event.");
}

// ---- Fetch event ----
$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $eventId);
$stmt->execute();
$res   = $stmt->get_result();
$event = $res->fetch_assoc();
$stmt->close();

if (!$event) {
    die("Event not found.");
}

// ---- Stats: registrations + waitlist ----
$regCount  = 0;
$waitCount = 0;

$sql = "SELECT COUNT(*) AS c FROM registrations WHERE event_id = ? AND status = 'confirmed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$res  = $stmt->get_result();
$row  = $res->fetch_assoc();
$stmt->close();
$regCount = (int)$row['c'];

$sql = "SELECT COUNT(*) AS c FROM waitlist WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$res  = $stmt->get_result();
$row  = $res->fetch_assoc();
$stmt->close();
$waitCount = (int)$row['c'];

// ---- Student status (joined / waitlisted / none) ----
$myStatus = 'Not joined';
if (!$isAdmin && $userEmail) {
    // registrations
    $sql = "SELECT status FROM registrations 
            WHERE event_id = ? AND student_email = ? 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $eventId, $userEmail);
    $stmt->execute();
    $res  = $stmt->get_result();
    $reg  = $res->fetch_assoc();
    $stmt->close();

    if ($reg) {
        $myStatus = 'Joined';
    } else {
        // waitlist
        $sql = "SELECT 1 FROM waitlist 
                WHERE event_id = ? AND student_email = ? 
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $eventId, $userEmail);
        $stmt->execute();
        $res  = $stmt->get_result();
        $w    = $res->fetch_assoc();
        $stmt->close();

        if ($w) {
            $myStatus = 'Waitlisted';
        }
    }
}

// ---- Format data ----
$dateText = $event['event_date'] 
    ? date('l, d M Y', strtotime($event['event_date'])) 
    : '';

$timeText = $event['event_time'] ?: '';
$location = $event['location'] ?? '';
$capacity = (int)$event['capacity'];

$imagePath = null;
if (!empty($event['image_path'])) {
    $imagePath = 'uploads/' . $event['image_path'];
}
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Event Details</h1>
        </div>
    </header>

    <div class="content">
        <div class="content-main">
            <section class="panel">
                <div class="panel-header event-header-wrap">
                    <div class="event-title-block">
                        <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                        <div class="event-meta-line">
                            <span class="meta-item"><?php echo htmlspecialchars($dateText); ?></span>
                            <?php if ($timeText): ?>
                                <span class="meta-dot">•</span>
                                <span class="meta-item"><?php echo htmlspecialchars($timeText); ?></span>
                            <?php endif; ?>
                            <?php if ($location): ?>
                                <span class="meta-dot">•</span>
                                <span class="meta-item"><?php echo htmlspecialchars($location); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="event-stat-chips">
                        <div class="stat-chip stat-capacity">
                            <span class="stat-label">Capacity</span>
                            <span class="stat-value"><?php echo $capacity; ?></span>
                        </div>
                        <div class="stat-chip stat-registered">
                            <span class="stat-label">Registered</span>
                            <span class="stat-value"><?php echo $regCount; ?></span>
                        </div>
                        <div class="stat-chip stat-waitlist">
                            <span class="stat-label">Waitlist</span>
                            <span class="stat-value"><?php echo $waitCount; ?></span>
                        </div>
                    </div>
                </div>

                <div class="event-details-layout">
                    <div class="event-main event-body-card">
                        <div class="event-image-layout">
                            <div class="event-image">
                                <?php if ($imagePath): ?>
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Event image">
                                <?php else: ?>
                                    <div class="event-image-placeholder">
                                        <span>No image uploaded for this event</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="event-description">
                                <h3>About this event</h3>
                                <p>
                                    <?php echo nl2br(htmlspecialchars($event['description'] ?: 'No description provided.')); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <aside class="event-side">
                        <div class="detail-card">
                            <h4>Summary</h4>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($dateText); ?></p>
                            <p><strong>Time:</strong> <?php echo htmlspecialchars($timeText ?: 'TBA'); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($location ?: 'TBA'); ?></p>
                            <p><strong>Capacity:</strong> <?php echo $capacity; ?></p>
                            <p><strong>Registered:</strong> <?php echo $regCount; ?></p>
                            <p><strong>Waitlist:</strong> <?php echo $waitCount; ?></p>
                        </div>

                        <?php if (!$isAdmin): ?>
                            <div class="detail-card">
                                <h4>My Status</h4>
                                <?php if ($myStatus === 'Joined'): ?>
                                    <span class="badge" style="background:#dcfce7; color:#166534;">Joined</span>
                                <?php elseif ($myStatus === 'Waitlisted'): ?>
                                    <span class="badge" style="background:#fef3c7; color:#92400e;">Waitlisted</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#e5e7eb; color:#374151;">Not joined</span>
                                    <div style="margin-top:10px;">
                                        <a href="event_register.php?event_id=<?php echo $eventId; ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
                                            <i data-feather="user-plus"></i> Join this event
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="detail-card">
                                <h4>Admin Actions</h4>
                                <a href="event_edit.php?id=<?php echo $eventId; ?>" class="btn-link">
                                    <i data-feather="edit"></i> Edit Event
                                </a>
                                <br>
                                <a href="event_delete.php?id=<?php echo $eventId; ?>" class="btn-link"
                                   onclick="return confirm('Delete this event?');">
                                    <i data-feather="trash-2"></i> Delete Event
                                </a>
                            </div>
                        <?php endif; ?>
                    </aside>
                </div>
            </section>
        </div>

        <aside class="content-side"></aside>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
