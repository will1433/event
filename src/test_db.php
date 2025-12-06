<<<<<<< HEAD
<?php
require __DIR__ . '/config/db.php';

$result = $conn->query("SELECT 1 AS test_value");
$row = $result->fetch_assoc();
echo "<h1>DB test value: " . $row['test_value'] . "</h1>";
=======
<?php
require __DIR__ . '/config/db.php';

$result = $conn->query("SELECT 1 AS test_value");
$row = $result->fetch_assoc();
echo "<h1>DB test value: " . $row['test_value'] . "</h1>";
>>>>>>> 47c52acb341e02dac2adcee5692bda62f4bb8533
