<<<<<<< HEAD
<?php 
$DB_HOST = 'db'; // service name from docker-compose 
$DB_USER = 'user'; 
$DB_PASS = 'userpassword'; 
$DB_NAME = 'eventsystem'; 
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME); 
if ($conn->connect_error) 
=======
<?php 
$DB_HOST = 'db'; // service name from docker-compose 
$DB_USER = 'user'; 
$DB_PASS = 'userpassword'; 
$DB_NAME = 'eventsystem'; 
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME); 
if ($conn->connect_error) 
>>>>>>> 47c52acb341e02dac2adcee5692bda62f4bb8533
{ die("Database connection failed: " . $conn->connect_error); } ?>