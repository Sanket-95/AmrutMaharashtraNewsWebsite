  <?php
// components/db_config.php

// Production database configuration  for .com
// define('DB_HOST', 'localhost');
// define('DB_USER', 'u153621952_Amrut123');
// define('DB_PASS', 'Mahaamrut@123');
// define('DB_NAME', 'u153621952_Mahaamrut');



// Production database configuration   for .org
// define('DB_HOST', 'localhost');
// define('DB_USER', 'u153621952_Amrut1234');   
// define('DB_PASS', 'Mahaamrut@123456789');
// define('DB_NAME', 'u153621952_Mahaamrutdb');    





// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'amrutmaharashtra');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");
?> 