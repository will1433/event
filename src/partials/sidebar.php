<?php
$role    = $_SESSION['role'] ?? 'student';
$isAdmin = ($role === 'admin');
?>

<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-circle">SE</div>
        <div class="logo-text">
            <span class="logo-title">School Events</span>
            <span class="logo-sub">Dashboard</span>
        </div>
    </div>

    <nav class="sidebar-nav">

        <a href="dashboard.php" class="nav-item">
            <i data-feather="home" class="nav-icon"></i>
            Dashboard
        </a>

        <a href="event_list.php" class="nav-item">
            <i data-feather="calendar" class="nav-icon"></i>
            Events List
        </a>

        <a href="event_create.php" class="nav-item">
            <i data-feather="plus-circle" class="nav-icon"></i>
            Create Event
        </a>

        <?php if ($isAdmin): ?>
        <a href="registrations.php" class="nav-item">
            <i data-feather="users" class="nav-icon"></i>
            Registrations
        </a>
        <?php endif; ?>

        <div class="nav-separator"></div>

        <a href="logout.php" class="nav-item logout">
            <i data-feather="log-out" class="nav-icon"></i>
            Logout
        </a>

    </nav>
</div>
