<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

$role        = $_SESSION['role'] ?? 'student';
$userName    = $_SESSION['user_name'] ?? 'User';
$userEmail   = $_SESSION['user_email'];

// 1) Get event_id
$eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
if ($eventId <= 0) {
    die("Invalid event.");
}

// 2) Check: user actually attended (confirmed registration)
$checkSql = "
    SELECT r.id, e.title, e.event_date
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.event_id = ? 
      AND r.student_email = ?
      AND r.status = 'confirmed'
";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("is", $eventId, $userEmail);
$stmt->execute();
$attendedResult = $stmt->get_result();
$attendedRow    = $attendedResult->fetch_assoc();
$stmt->close();

if (!$attendedRow) {
    die("You can only leave feedback for events you are registered for.");
}

$eventTitle = $attendedRow['title'];
$eventDate  = $attendedRow['event_date'];

$feedback_error   = '';
$feedback_success = '';

// 3) Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating   = (int)($_POST['rating'] ?? 0);
    $comments = trim($_POST['comments'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $feedback_error = "Please select a rating between 1 and 5.";
    } else {
        // Check if feedback already exists (unique index also enforces this)
        $checkFb = $conn->prepare("
            SELECT id FROM event_feedback
            WHERE event_id = ? AND student_email = ?
        ");
        $checkFb->bind_param("is", $eventId, $userEmail);
        $checkFb->execute();
        $existing = $checkFb->get_result()->fetch_assoc();
        $checkFb->close();

        if ($existing) {
            // Update existing feedback
            $upd = $conn->prepare("
                UPDATE event_feedback
                SET rating = ?, comments = ?
                WHERE id = ?
            ");
            $upd->bind_param("isi", $rating, $comments, $existing['id']);
            $upd->execute();
            $upd->close();
            $feedback_success = "Your feedback has been updated. Thank you!";
        } else {
            // Insert new feedback
            $ins = $conn->prepare("
                INSERT INTO event_feedback (event_id, student_email, rating, comments)
                VALUES (?, ?, ?, ?)
            ");
            $ins->bind_param("isis", $eventId, $userEmail, $rating, $comments);
            $ins->execute();
            $ins->close();
            $feedback_success = "Your feedback has been submitted. Thank you!";
        }
    }
}
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <header class="topbar">
        <div>
            <h1 class="page-title">Event Feedback</h1>
            <p class="panel-subtitle">
                Share your experience for: <strong><?php echo htmlspecialchars($eventTitle); ?></strong>
                (<?php echo date('d M Y', strtotime($eventDate)); ?>)
            </p>
        </div>

        <div class="topbar-right">
            <div class="user-chip" id="userMenuBtn">
                <div class="avatar">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                    <span class="user-role"><?php echo htmlspecialchars($role); ?></span>
                </div>
                <span class="user-caret">▾</span>
            </div>
            <div class="user-dropdown" id="userMenuDropdown">
                <a href="dashboard.php" class="dropdown-item">
                    <span>Dashboard</span>
                </a>
                <a href="logout.php" class="dropdown-item logout-item">
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <div class="content">
        <div class="content-main">
            <section class="panel">
                <div class="panel-header">
                    <h2>Rate this Event</h2>
                    <span class="panel-subtitle">Your feedback helps organizers improve future events.</span>
                </div>

                <?php if ($feedback_error): ?>
                    <div class="alert error">
                        <?php echo htmlspecialchars($feedback_error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($feedback_success): ?>
                    <div class="alert success">
                        <?php echo htmlspecialchars($feedback_success); ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="event-form">
                    <div class="form-row">
                        <label for="rating">Rating (1–5)</label>
                        <select name="rating" id="rating">
                            <option value="">Select rating</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="comments">Comments (optional)</label>
                        <textarea name="comments" id="comments" rows="4"
                            placeholder="What went well? What could be improved?"></textarea>
                    </div>

                    <button type="submit" class="btn-primary">Submit Feedback</button>
                </form>
            </section>
        </div>

        <aside class="content-side"></aside>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
