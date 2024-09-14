<?php

$conn = new \Database\MySQLi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $date = $_POST['date'] ?? '';
  $date = new DateTime($date);
  
  $watchbill = new \WatchBill\DutyWatchbill($conn, $date);

    try {
        if ($action === 'fill') {
            // Fill the watchbill based on constraints
            $watchbill->fillWatchbill();
            echo 'Watchbill filled successfully!';
        } elseif ($action === 'empty') {
            // Empty the watchbill (reset positions)
            $watchbill->emptyWatchBill();
            echo 'Watchbill emptied successfully!';
        } else {
            echo 'Invalid action.';
        }
    } catch (Exception $e) {
        // Handle exceptions and errors
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo 'Invalid request method.';
}
?>
