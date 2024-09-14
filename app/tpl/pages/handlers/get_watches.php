<?php
header('Content-Type: application/json');

// Get the database connection
$conn = new \Database\MySQLi();

// Fetch the sailors from the database
$sailors = $conn->Rows("SELECT id, last_name FROM sailors");

// Return data as JSON
echo json_encode($sailors);
?>
