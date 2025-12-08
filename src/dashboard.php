<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/config/db.php';

// --- ROLE & USER INFO ---
$role      = $_SESSION['role'] ?? 'admin';
$isAdmin   = ($role === 'admin');
$userName  = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? null;

// ---- Calendar: events for current month ----
$calendarEventsByDay = [];

$currentYear  = date('Y');
$currentMonth = date('m');

// --- Calendar month handling (supports ?month=2025-03) ---
$monthParam = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

list($currentYear, $currentMonth) = explode('-', $monthParam);
$currentYear  = (int)$currentYear;
$currentMonth = (int)$currentMonth;

// First day timestamp
$firstDayTs   = strtotime(sprintf('%04d-%02d-01', $currentYear, $currentMonth));
$daysInMonth  = (int)date('t', $firstDayTs);

// Previous / next month strings
$prevMonthTs  = strtotime('-1 month', $firstDayTs);
$nextMonthTs  = strtotime('+1 month', $firstDayTs);

$prevMonthParam = date('Y-m', $prevMonthTs);
$nextMonthParam = date('Y-m', $nextMonthTs);

// --- Fetch events for this month ---
$calendarEventsByDay = [];

$sql = "
    SELECT id, title, event_date
    FROM events
    WHERE YEAR(event_date) = {$currentYear}
      AND MONTH(event_date) = {$currentMonth}
    ORDER BY event_date ASC
";

if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        // If your date column is different, change 'event_date' above
        $dayNumber = (int)date('j', strtotime($row['event_date']));
        if (!isset($calendarEventsByDay[$dayNumber])) {
            $calendarEventsByDay[$dayNumber] = [];
        }
        $calendarEventsByDay[$dayNumber][] = $row;
    }
}




// Admin stats (global)
$totalEvents        = 0;
$upcomingEvents     = 0;
$totalRegistrations = 0;
$totalWaitlisted    = 0;

// Student stats (personal)
$myEvents       = 0;
$myJoinedEvents = 0;
$myWaitlisted   = 0;
$openUpcoming   = 0;

// ========= ADMIN VIEW (STATS) =========
if ($isAdmin) {
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

    // Map to card labels/values
    $card1Label = 'Total Events';
    $card1Value = $totalEvents;

    $card2Label = 'Upcoming Events';
    $card2Value = $upcomingEvents;

    $card3Label = 'Registrations';
    $card3Value = $totalRegistrations;

    $card4Label = 'Waitlisted';
    $card4Value = $totalWaitlisted;

} else {
    // ========= STUDENT VIEW (STATS) =========
    if ($userEmail) {
        $emailEsc = $conn->real_escape_string($userEmail);

        // Events created by this student
        // (Make sure event_create.php saves created_by = $_SESSION['user_email'])
        $res = $conn->query("SELECT COUNT(*) AS c 
                             FROM events 
                             WHERE created_by = '{$emailEsc}'");
        if ($res) { $myEvents = (int)$res->fetch_assoc()['c']; }

        // Events they joined (confirmed)
        $res = $conn->query("SELECT COUNT(*) AS c 
                             FROM registrations 
                             WHERE student_email = '{$emailEsc}' 
                               AND status = 'confirmed'");
        if ($res) { $myJoinedEvents = (int)$res->fetch_assoc()['c']; }

        // Events they are waitlisted on
        $res = $conn->query("SELECT COUNT(*) AS c 
                             FROM waitlist 
                             WHERE student_email = '{$emailEsc}'");
        if ($res) { $myWaitlisted = (int)$res->fetch_assoc()['c']; }
    }

    // Open upcoming events (simple version: all future events)
    $res = $conn->query("SELECT COUNT(*) AS c 
                         FROM events 
                         WHERE event_date >= CURDATE()");
    if ($res) { $openUpcoming = (int)$res->fetch_assoc()['c']; }

    // Map to card labels/values for student
    $card1Label = 'My Events';
    $card1Value = $myEvents;

    $card2Label = 'Events I Joined';
    $card2Value = $myJoinedEvents;

    $card3Label = 'My Waitlist';
    $card3Value = $myWaitlisted;

    $card4Label = 'Open Upcoming Events';
    $card4Value = $openUpcoming;
}

// ---- Notice board: latest 5 events (for both views) ----
$noticeEvents = [];
$res = $conn->query("SELECT title, description, event_date FROM events ORDER BY event_date DESC LIMIT 5");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $noticeEvents[] = $row;
    }
}

// ---- Today’s timeline (for both views) ----
$todayEvents = [];
$res = $conn->query("SELECT title, location, event_time FROM events WHERE event_date = CURDATE() ORDER BY event_time");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $todayEvents[] = $row;
    }
}

// ---- EXTRA: STUDENT-SPECIFIC LISTS ----
$myCreatedEvents = [];
$joinedEvents    = [];
$waitlistEvents  = [];
$openEventsList  = [];

if (!$isAdmin && $userEmail) {
    $emailEsc = $conn->real_escape_string($userEmail);

    // My Created Events
    $sql = "SELECT id, title, event_date, event_time, location 
            FROM events 
            WHERE created_by = '{$emailEsc}'
            ORDER BY event_date ASC";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $myCreatedEvents[] = $row;
        }
    }

    // Events I Joined (confirmed)
    $sql = "SELECT e.id, e.title, e.event_date, e.event_time, e.location, r.status
            FROM registrations r
            JOIN events e ON r.event_id = e.id
            WHERE r.student_email = '{$emailEsc}'
            ORDER BY e.event_date ASC";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $joinedEvents[] = $row;
        }
    }

    // My Waitlist
    $sql = "SELECT e.id, e.title, e.event_date, e.event_time, e.location
            FROM waitlist w
            JOIN events e ON w.event_id = e.id
            WHERE w.student_email = '{$emailEsc}'
            ORDER BY e.event_date ASC";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $waitlistEvents[] = $row;
        }
    }

    // Open Upcoming Events (all upcoming events; later we can filter out ones they already joined)
    $sql = "SELECT id, title, event_date, event_time, location
            FROM events
            WHERE event_date >= CURDATE()
            ORDER BY event_date ASC
            LIMIT 10";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $openEventsList[] = $row;
        }
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

        <?php
            $displayName = $userName;
            $initial     = strtoupper(substr($displayName, 0, 1));
            $displayRole = $isAdmin ? 'Admin' : 'Student';
        ?>

        <div class="topbar-right">
            <button class="icon-btn"><i data-feather="globe"></i></button>
            <button class="icon-btn"><i data-feather="bell"></i></button>

            <div class="user-menu">
                <button class="user-chip" id="userMenuBtn" type="button">
                    <div class="avatar"><?php echo $initial; ?></div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($displayName); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($displayRole); ?></span>
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
                    <span class="stat-label"><?php echo $card1Label; ?></span>
                    <span class="stat-value"><?php echo $card1Value; ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-label"><?php echo $card2Label; ?></span>
                    <span class="stat-value"><?php echo $card2Value; ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-label"><?php echo $card3Label; ?></span>
                    <span class="stat-value"><?php echo $card3Value; ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-label"><?php echo $card4Label; ?></span>
                    <span class="stat-value"><?php echo $card4Value; ?></span>
                </div>
            </div>

            <?php if ($isAdmin): ?>

                <!-- ADMIN CENTER CONTENT -->

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

            <?php else: ?>

                <!-- STUDENT CENTER CONTENT -->

                <!-- My Created Events -->
                <section class="panel">
                    <div class="panel-header">
                        <h2>My Created Events</h2>
                        <span class="panel-subtitle">Events you created (pending or approved)</span>
                    </div>
                    <div class="notice-list">
                        <?php if (count($myCreatedEvents) === 0): ?>
                            <p>You haven't created any events yet.</p>
                        <?php else: ?>
                            <?php foreach ($myCreatedEvents as $ev): ?>
                                <div class="notice-item">
                                    <div class="notice-main">
                                        <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
                                        <p>
                                            <?php echo date('d M, Y', strtotime($ev['event_date'])); ?>
                                            <?php if (!empty($ev['event_time'])): ?>
                                                • <?php echo htmlspecialchars($ev['event_time']); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($ev['location'])): ?>
                                                • <?php echo htmlspecialchars($ev['location']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Events I Joined -->
                <section class="panel">
                    <div class="panel-header">
                        <h2>Events I Joined</h2>
                        <span class="panel-subtitle">Events you are registered for</span>
                    </div>
                    <div class="notice-list">
                        <?php if (count($joinedEvents) === 0): ?>
                            <p>You haven't joined any events yet.</p>
                        <?php else: ?>
                            <?php foreach ($joinedEvents as $ev): ?>
                                <div class="notice-item">
                                    <div class="notice-main">
                                        <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
                                        <p>
                                            <?php echo date('d M, Y', strtotime($ev['event_date'])); ?>
                                            <?php if (!empty($ev['event_time'])): ?>
                                                • <?php echo htmlspecialchars($ev['event_time']); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($ev['location'])): ?>
                                                • <?php echo htmlspecialchars($ev['location']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="notice-meta">
                                        <span class="badge">
                                            <?php echo htmlspecialchars($ev['status'] ?: 'confirmed'); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- My Waitlist -->
                <section class="panel">
                    <div class="panel-header">
                        <h2>My Waitlist</h2>
                        <span class="panel-subtitle">Events where you are waitlisted</span>
                    </div>
                    <div class="notice-list">
                        <?php if (count($waitlistEvents) === 0): ?>
                            <p>You are not on any waitlists.</p>
                        <?php else: ?>
                            <?php foreach ($waitlistEvents as $ev): ?>
                                <div class="notice-item">
                                    <div class="notice-main">
                                        <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
                                        <p>
                                            <?php echo date('d M, Y', strtotime($ev['event_date'])); ?>
                                            <?php if (!empty($ev['event_time'])): ?>
                                                • <?php echo htmlspecialchars($ev['event_time']); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($ev['location'])): ?>
                                                • <?php echo htmlspecialchars($ev['location']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="notice-meta">
                                        <span class="badge">Waitlisted</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Open Upcoming Events -->
                <section class="panel">
                    <div class="panel-header">
                        <h2>Open Upcoming Events</h2>
                        <span class="panel-subtitle">Events you can join</span>
                    </div>
                    <div class="notice-list">
                        <?php if (count($openEventsList) === 0): ?>
                            <p>No upcoming events listed yet.</p>
                        <?php else: ?>
                            <?php foreach ($openEventsList as $ev): ?>
                                <div class="notice-item">
                                    <div class="notice-main">
                                        <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
                                        <p>
                                            <?php echo date('d M, Y', strtotime($ev['event_date'])); ?>
                                            <?php if (!empty($ev['event_time'])): ?>
                                                • <?php echo htmlspecialchars($ev['event_time']); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($ev['location'])): ?>
                                                • <?php echo htmlspecialchars($ev['location']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

            <?php endif; ?>
        </div>

        <!-- Right side area (same for admin + student) -->
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
            <span><?php echo date('F Y', $firstDayTs); ?></span>
            <div class="calendar-arrows">
                <a href="?month=<?php echo $prevMonthParam; ?>">&lt;</a>
                <a href="?month=<?php echo $nextMonthParam; ?>">&gt;</a>
            </div>
        </div>

        <div class="calendar-grid">
            <!-- Weekday labels -->
            <span>Su</span><span>Mo</span><span>Tu</span>
            <span>We</span><span>Th</span><span>Fr</span><span>Sa</span>

            <?php
            // Blank cells before day 1
            $firstWeekday = (int)date('w', $firstDayTs);
            for ($b = 0; $b < $firstWeekday; $b++): ?>
                <div></div>
            <?php endfor; ?>

            <?php
            for ($day = 1; $day <= $daysInMonth; $day++):
                $isToday =
                    $currentYear == date('Y') &&
                    $currentMonth == date('m') &&
                    $day == date('j');

                $hasEvents = !empty($calendarEventsByDay[$day]);
                $classes = ['calendar-day'];
                if ($isToday) $classes[] = 'active-day';
            ?>
                <div class="<?php echo implode(' ', $classes); ?>">
                    <span class="day-number"><?php echo $day; ?></span>

                    <?php if ($hasEvents): ?>
                        <ul class="day-events">
                            <?php
                                foreach ($calendarEventsByDay[$day] as $idx => $event):
                                    if ($idx >= 2) {
                                        echo "<li>+ more...</li>";
                                        break;
                                    }
                            ?>
                                <li>
                                    <a href="event_details.php?id=<?php echo $event['id']; ?>">
                                        <?php echo htmlspecialchars($event['title']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
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
