<?php

// Get the database connection
$conn = new \Database\MySQLi();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input values
    $lastName = $_POST['last_name'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $middleInitial = $_POST['middle_name'] ?? '';
    $muster = $_POST['muster'] ?? '';
    $schoolHouse = $_POST['schoolhouse'] ?? '';
    $beq = $_POST['beq'] ?? '';
    $roomNumber = $_POST['room_number'] ?? '';
    $phone = $_POST['phone_number'] ?? '';
    $basic = $_POST['basic_qualified'] ?? '';
    $secure = $_POST['secure_qualified'] ?? '';

    // Define the SQL query
    $query = "INSERT INTO sailors (last_name, first_name, middle_name, muster, schoolhouse, beq, room_number, phone_number, basic_qualified, secure_qualified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    // Execute the query with parameter binding

    // Check if the query was successful
    if ($conn->Query($query, $lastName, $firstName, $middleInitial, $muster, $schoolHouse, $beq, $roomNumber, $phone, $basic, $secure))
    {
    echo "Sailor added successfully!";
  } else {
    echo "Error.";
  }
} else {
    echo "Invalid request method.";
}
?>
