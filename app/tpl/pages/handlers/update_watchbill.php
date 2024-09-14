<?php

// Get the database connection
$conn = new \Database\MySQLi();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $watchType = $_POST['watch_type'];
    $timeslot = $_POST['time'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $newID = $_POST['new_id'] === '' ? null : $_POST['new_id']; // Convert 'null' string to actual null

    // Determine which column to update based on the watch type (BAW or BRW)
    $column = ($watchType === 'BAW') ? 'baw_id' : 'brw_id';

    // Update the sailor's name in the appropriate column
    $query = "
        UPDATE duty_watchbill_2
        SET {$column} = ?
        WHERE timeslot_assignment = ? AND watch_location = ? AND date = '{$date}'
    ";

    if ($conn->Query($query, $newID, $timeslot, $location)) {
        echo "Success";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
