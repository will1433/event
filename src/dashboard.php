<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

// ---- STATS ----
$totalEvents = 0;
$upcomingEvents = 0;
$totalRegistrations = 0;
$totalWaitlisted = 0;

// Total events
$res = $conn->query("SELECT COUNT(*) AS c FROM events");
if ($res) { $totalEvents = (int)$res->fetch_assoc()['c']; }

// Upcoming events (today or later)
$res = $conn->query("SELECT COUNT(*) AS c FROM events WHERE event_date >= CURDATE()");
if ($res) { $upcomingEvents = (int)$res->fetch_assoc()['c']; }

// Registrations
$res = $conn->query("SELECT COUNT(*) AS c FROM registrations");
if ($res) { $totalRegistrations = (int)$res->fetch_assoc()['c']; }

// Waitlist
$res = $conn->query("SELECT COUNT(*) AS c FROM waitlist");
if ($res) { $totalWaitlisted = (int)$res->fetch_assoc()['c']; }

// ---- Notice board: latest 5 events ----
$noticeEvents = [];
$res = $conn->query("SELECT title, description, event_date FROM events ORDER BY event_date DESC LIMIT 5");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $noticeEvents[] = $row;
    }
}

// ---- Today’s timeline ----
$todayEvents = [];
$res = $conn->query("SELECT title, location, event_time FROM events WHERE event_date = CURDATE() ORDER BY event_time");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $todayEvents[] = $row;
    }
}
?>

<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<div class="main">
    <!-- Top bar -->
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Dashboard</h1>
            <div class="search-box">
                <input type="text" placeholder="Search events...">
            </div>
        </div>
        <<?php
    $userName   = isset($_SESSION['user']) ? $_SESSION['user'] : 'Admin';
    $userInitial = strtoupper(substr($userName, 0, 1));
?>
<?php
    $userName   = isset($_SESSION['user']) ? $_SESSION['user'] : 'admin';
    $userInitial = strtoupper(substr($userName, 0, 1));
?>
<div class="topbar-right">
    <button class="icon-btn"><i data-feather="globe"></i></button>
    <button class="icon-btn"><i data-feather="bell"></i></button>

    <div class="user-menu">
        <button class="user-chip" id="userMenuBtn" type="button">
            <div class="avatar"><?php echo $userInitial; ?></div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                <span class="user-role">Event Manager</span>
            </div>
            <i data-feather="chevron-down" class="user-caret"></i>
        </button>

        <div class="user-dropdown" id="userMenuDropdown">
            <a href="#" class="dropdown-item">
                <i data-feather="settings"></i>
                <span>Settings</span>
            </a>
            <a href="logout.php" class="dropdown-item logout-item">
                <i data-feather="log-out"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>


    </header>

    <!-- Main content: center + right -->
    <div class="content">
        <!-- Center area -->
        <div class="content-main">
            <!-- Stats cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <span class="stat-label">Total Events</span>
                    <span class="stat-value"><?php echo $totalEvents; ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Upcoming Events</span>
                    <span class="stat-value"><?php echo $upcomingEvents; ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Registrations</span>
                    <span class="stat-value"><?php echo $totalRegistrations; ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Waitlisted</span>
                    <span class="stat-value"><?php echo $totalWaitlisted; ?></span>
                </div>
            </div>

            <!-- Chart placeholder -->
            <section class="panel">
                <div class="panel-header">
                    <h2>Events Overview 2025</h2>
                    <span class="panel-subtitle">Events per month (demo data)</span>
                </div>
                <div class="chart-placeholder">
                    Chart goes here
                </div>
            </section>

            <!-- Notice board -->
            <section class="panel">
                <div class="panel-header">
                    <h2>Notice Board</h2>
                    <span class="panel-subtitle">Latest school events & announcements</span>
                </div>
                <div class="notice-list">
                    <?php if (count($noticeEvents) === 0): ?>
                        <p>No events created yet.</p>
                    <?php else: ?>
                        <?php foreach ($noticeEvents as $ev): ?>
                            <div class="notice-item">
                                <div class="notice-main">
                                    <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($ev['description'] ?: 'No description'); ?></p>
                                </div>
                                <div class="notice-meta">
                                    <span class="badge">
                                        <?php echo date('d M, Y', strtotime($ev['event_date'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Right side area -->
        <aside class="content-side">
            <!-- Calendar -->
            <section class="panel">
                <div class="panel-header">
                    <h2>Event Calendar</h2>
                    <div class="small-tabs">
                        <button class="tab active">Day to day</button>
                        <button class="tab">Monthly</button>
                    </div>
                </div>
                <div class="calendar-box">
                    <div class="calendar-header">
                        <span><?php echo date('F Y'); ?></span>
                        <div class="calendar-arrows">‹ ›</div>
                    </div>
                    <div class="calendar-grid">
                        <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                        <?php
                        $daysInMonth = date('t');
                        for ($i = 1; $i <= $daysInMonth; $i++):
                            $isToday = ($i == date('j'));
                        ?>
                            <div class="calendar-day <?php echo $isToday ? 'active-day' : ''; ?>">
                                <?php echo $i; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </section>

            <!-- Today’s timeline -->
            <section class="panel">
                <div class="panel-header">
                    <h2>Today’s Timeline</h2>
                </div>
                <div class="timeline">
                    <?php if (count($todayEvents) === 0): ?>
                        <p>No events today.</p>
                    <?php else: ?>
                        <?php foreach ($todayEvents as $ev): ?>
                            <div class="timeline-item">
                                <div class="timeline-time">
                                    <?php echo htmlspecialchars($ev['event_time'] ?: 'All day'); ?>
                                </div>
                                <div class="timeline-main">
                                    <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($ev['location'] ?: ''); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
