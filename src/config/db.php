<?php 
$DB_HOST = 'db'; // service name from docker-compose 
$DB_USER = 'user'; 
$DB_PASS = 'userpassword'; 
$DB_NAME = 'eventsystem'; 
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME); 
if ($conn->connect_error) 
{ die("Database connection failed: " . $conn->connect_error); } ?>