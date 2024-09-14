<?php

// Get the database connection
$conn = new \Database\MySQLi();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $watchType = $_POST['watch_type'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $signed = $_POST['signed'];

    // Determine the column to update based on watchType
    $columnToUpdate = ($watchType === 'BAW') ? 'baw_signed' : 'brw_signed';

    $query = "
        UPDATE duty_watchbill_2
        SET {$columnToUpdate} = ?
        WHERE timeslot_assignment = ? AND watch_location = ?
    ";

    if ($conn->Query($query, $signed, $time, $location)) {
        echo "Success! Updated {$watchType} for time slot {$time} at location {$location} to {$signed}";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
