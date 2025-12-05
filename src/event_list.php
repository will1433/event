<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

// get all events
$events = [];
$res = $conn->query("SELECT * FROM events ORDER BY event_date DESC, event_time ASC");
if ($res) {
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
            <h1 class="page-title">All Events</h1>
        </div>
    </header>

    <div class="content">
        <div class="content-main">
            <section class="panel">
                <div class="panel-header">
                    <h2>Events List</h2>
                    <span class="panel-subtitle"><?php echo count($events); ?> event(s)</span>
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
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($events as $ev): ?>
        <tr>
            

            <td>
    <a href="event_details.php?id=<?php echo $ev['id']; ?>">
        <?php echo htmlspecialchars($ev['title']); ?>
    </a>
</td>
            <td><?php echo htmlspecialchars($ev['event_date']); ?></td>
            <td><?php echo htmlspecialchars($ev['event_time']); ?></td>
            <td><?php echo htmlspecialchars($ev['location']); ?></td>
            <td><?php echo htmlspecialchars($ev['capacity']); ?></td>
            <td>
                <a href="event_edit.php?id=<?php echo $ev['id']; ?>">✏️ Edit</a>
    &nbsp;|&nbsp;
    <a href="event_delete.php?id=<?php echo $ev['id']; ?>"
                   onclick="return confirm('Delete this event?');">
                    🗑 Delete
                </a>
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
