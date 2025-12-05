<?php
session_start();

// only clear student keys (optional)
unset($_SESSION['student_id'], $_SESSION['student_name']);

// or full destroy:
// session_unset();
// session_destroy();

header("Location: student_login.php");
exit();
