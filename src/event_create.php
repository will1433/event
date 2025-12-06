<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// optional: if you want to know role on this page too
$role      = $_SESSION['role'] ?? 'admin';
$userName  = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? null;
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Create Event</h1>
        </div>
    </header>

    <div class="content">
        <div class="content-main">
            <section class="panel">
                <div class="panel-header">
                    <h2>New School Event</h2>
                </div>

                <!-- IMPORTANT: enctype for file upload -->
                <form action="event_save.php" method="POST" enctype="multipart/form-data" class="event-form">

                    <div class="form-row">
                        <label>Title</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="form-row">
                        <label>Description</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>

                    <div class="form-row split">
                        <div>
                            <label>Date</label>
                            <input type="date" name="event_date" required>
                        </div>
                        <div>
                            <label>Time</label>
                            <input type="text" name="event_time" placeholder="10:00 am">
                        </div>
                    </div>

                    <div class="form-row split">
                        <div>
                            <label>Location</label>
                            <input type="text" name="location" placeholder="Auditorium">
                        </div>
                        <div>
                            <label>Capacity</label>
                            <input type="number" name="capacity" min="0" value="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Event Image (optional)</label>
                        <input type="file" name="event_image" accept="image/*">
                    </div>

                    <button type="submit" class="btn-primary">Save Event</button>
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
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Create Event</h1>
        </div>
    </header>

    <div class="content">
        <div class="content-main">
            <section class="panel">
                <div class="panel-header">
                    <h2>New School Event</h2>
                </div>

                <!-- IMPORTANT: enctype for file upload -->
                <form action="event_save.php" method="POST" enctype="multipart/form-data" class="event-form">


                    <div class="form-row">
                        <label>Title</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="form-row">
                        <label>Description</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>

                    <div class="form-row split">
                        <div>
                            <label>Date</label>
                            <input type="date" name="event_date" required>
                        </div>
                        <div>
                            <label>Time</label>
                            <input type="text" name="event_time" placeholder="10:00 am">
                        </div>
                    </div>

                    <div class="form-row split">
                        <div>
                            <label>Location</label>
                            <input type="text" name="location" placeholder="Auditorium">
                        </div>
                        <div>
                            <label>Capacity</label>
                            <input type="number" name="capacity" min="0" value="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Event Image (optional)</label>
                        <input type="file" name="event_image" accept="image/*">
                    </div>

                    <button type="submit" class="btn-primary">Save Event</button>
                </form>
            </section>
        </div>
        <aside class="content-side"></aside>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
>>>>>>> 47c52acb341e02dac2adcee5692bda62f4bb8533
