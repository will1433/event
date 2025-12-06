<<<<<<< HEAD
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
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Edit Event</h1>
        </div>
    </header>

    <div class="content">
        <div class="content-main">
            <section class="panel">
                <div class="panel-header">
                    <h2>Edit: <?php echo htmlspecialchars($event['title']); ?></h2>
                </div>

                <form action="event_update.php" method="POST"
                      enctype="multipart/form-data" class="event-form">

                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                    <input type="hidden" name="current_image"
                           value="<?php echo htmlspecialchars($event['image_path'] ?? ''); ?>">

                    <div class="form-row">
                        <label>Title</label>
                        <input type="text" name="title"
                               value="<?php echo htmlspecialchars($event['title']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label>Description</label>
                        <textarea name="description" rows="3"><?php
                            echo htmlspecialchars($event['description'] ?? '');
                        ?></textarea>
                    </div>

                    <div class="form-row split">
                        <div>
                            <label>Date</label>
                            <input type="date" name="event_date"
                                   value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
                        </div>
                        <div>
                            <label>Time</label>
                            <input type="text" name="event_time"
                                   value="<?php echo htmlspecialchars($event['event_time'] ?? ''); ?>"
                                   placeholder="10:00 am">
                        </div>
                    </div>

                    <div class="form-row split">
                        <div>
                            <label>Location</label>
                            <input type="text" name="location"
                                   value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>"
                                   placeholder="Auditorium">
                        </div>
                        <div>
                            <label>Capacity</label>
                            <input type="number" name="capacity"
                                   value="<?php echo (int)$event['capacity']; ?>" min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Current Image</label>
                        <?php if (!empty($event['image_path'])): ?>
                            <div style="margin-bottom:8px;">
                                <img src="uploads/<?php echo htmlspecialchars($event['image_path']); ?>"
                                     width="120" height="80"
                                     style="object-fit:cover;border-radius:6px;">
                            </div>
                        <?php else: ?>
                            <p>No image uploaded.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <label>Change Image (optional)</label>
                        <input type="file" name="event_image" accept="image/*">
                    </div>

                    <button type="submit" class="btn-primary">Update Event</button>
                </form>
            </section>
        </div>
        <aside class="content-side"></aside>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
=======
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
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Edit Event</h1>
        </div>
    </header>

    <div class="content">
        <div class="content-main">
            <section class="panel">
                <div class="panel-header">
                    <h2>Edit: <?php echo htmlspecialchars($event['title']); ?></h2>
                </div>

                <form action="event_update.php" method="POST"
                      enctype="multipart/form-data" class="event-form">

                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                    <input type="hidden" name="current_image"
                           value="<?php echo htmlspecialchars($event['image_path'] ?? ''); ?>">

                    <div class="form-row">
                        <label>Title</label>
                        <input type="text" name="title"
                               value="<?php echo htmlspecialchars($event['title']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label>Description</label>
                        <textarea name="description" rows="3"><?php
                            echo htmlspecialchars($event['description'] ?? '');
                        ?></textarea>
                    </div>

                    <div class="form-row split">
                        <div>
                            <label>Date</label>
                            <input type="date" name="event_date"
                                   value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
                        </div>
                        <div>
                            <label>Time</label>
                            <input type="text" name="event_time"
                                   value="<?php echo htmlspecialchars($event['event_time'] ?? ''); ?>"
                                   placeholder="10:00 am">
                        </div>
                    </div>

                    <div class="form-row split">
                        <div>
                            <label>Location</label>
                            <input type="text" name="location"
                                   value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>"
                                   placeholder="Auditorium">
                        </div>
                        <div>
                            <label>Capacity</label>
                            <input type="number" name="capacity"
                                   value="<?php echo (int)$event['capacity']; ?>" min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Current Image</label>
                        <?php if (!empty($event['image_path'])): ?>
                            <div style="margin-bottom:8px;">
                                <img src="uploads/<?php echo htmlspecialchars($event['image_path']); ?>"
                                     width="120" height="80"
                                     style="object-fit:cover;border-radius:6px;">
                            </div>
                        <?php else: ?>
                            <p>No image uploaded.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <label>Change Image (optional)</label>
                        <input type="file" name="event_image" accept="image/*">
                    </div>

                    <button type="submit" class="btn-primary">Update Event</button>
                </form>
            </section>
        </div>
        <aside class="content-side"></aside>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
>>>>>>> 47c52acb341e02dac2adcee5692bda62f4bb8533
