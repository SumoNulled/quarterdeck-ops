<?php

$conn = new \Database\MySQLi;
$sailor = new \Sailors\Sailors($conn);

$date = new DateTime('2024-12-2');
$watchbill = new \WatchBill\DutyWatchbill($conn, $date);
$timeslots = new \WatchBill\TimeSlots($conn);

try {
    $watchbill->setRow(33);
  //  echo $watchbill->watch_location;
} catch (Exception $e) {
    // Gracefully handle the exception
    echo "An error occurred: " . $e->getMessage();
}

var_dump($watchbill->getById(33, "timeslot_assignment"));
var_dump(timeRangesOverlap(["21:00:00", "00:00:00"], ['17:00:00', '23:00:00'], $date));
echo "<br/><br/>";
var_dump(timeRangesOverlap(["03:00:00", "05:00:00"], ['00:00:00', '05:30:00'], $date));
echo "<br/><br/>";
var_dump(timeRangesOverlap(["00:00:00", "03:00:00"], ['00:00:00', '05:30:00'], $date));
echo "<br/><br/>";
var_dump(timeRangesOverlap(["21:00:00", "00:00:00"], ['20:00:00', '04:00:00'], $date));
echo "<br/><br/>";
var_dump(timeRangesOverlap(["21:00:00", "00:00:00"], ['07:00:00', '15:00:00'], $date));

//var_dump(timeRangesOverlap(35, ['17:00:00', '23:00:00'], $date));

/**
 * Determines if a given time range overlaps with the current time slot.
 *
 * @param int $id The timeslot_assignment id.
 * @param array $timeRange An array with start and end times in 'H:i' format (e.g., ['09:00', '12:00']).
 * @param \DateTime $date The date object for the evaluation.
 * @return bool
 * @throws \Exception If the time ranges are missing or invalid.
 */
function timeRangesOverlap(array $watchTime, array $timeRange, \DateTime $date): bool
{
    // Get the current watch time range based on the timeslot assignment
    //$watchTime = ["21:00:00", "00:00:00"]; // Example watch times

    $dateString = $date->format('Y-m-d');
    echo "Date: {$dateString}<br>";


    // Ensure both time ranges (watch and provided) have valid start and end times
    $start1 = $watchTime[0] ?? throw new \Exception("Invalid watch time range");
    $end1 = $watchTime[1] ?? throw new \Exception("Invalid watch time range");
    $start2 = $timeRange[0] ?? throw new \Exception("Invalid time range provided");
    $end2 = $timeRange[1] ?? throw new \Exception("Invalid time range provided");

    echo "Initial Watch Time: Start: {$start1}, End: {$end1}<br>";
    echo "Provided Time Range: Start: {$start2}, End: {$end2}<br>";

    // Convert time strings to DateTime objects
    $start1 = new \DateTime("{$start1} {$dateString}");
    $end1 = new \DateTime("{$end1} {$dateString}");
    $start2 = new \DateTime("{$start2} {$dateString}");
    $end2 = new \DateTime("{$end2} {$dateString}");

    $watch_start = (int) $start1->format('H');
    $watch_end = (int) $end1->format('H');

    //Correct for midnight rollovers for watch.
    switch(true)
    {
      case $watch_start < 16:
      $start1->modify("+1 day");
      $end1->modify("+1 day");
      break;

      case $watch_end < 16:
      $end1->modify("+1 day");
      break;
      default:

      break;
    }

      // Correct for midnight rollovers in provided time
      if ($end2 <= $start2 || $end2->format("H:i:s") === "00:00:00") {
          //echo "Provided time crosses midnight. Adjusting end time...<br>";
          $end2->modify("+1 day");
      }else if ($start2->format("H:i:s") === "00:00:00")
      {
        $start2->modify("+1 day");
        $end2->modify("+1 day");
      }

    echo "Adjusted Watch Time: Start: " . $start1->format('Y-m-d H:i:s') . ", End: " . $end1->format('Y-m-d H:i:s') . "<br>";
    echo "Adjusted Provided Time Range: Start: " . $start2->format('Y-m-d H:i:s') . ", End: " . $end2->format('Y-m-d H:i:s') . "<br>";

    // Apply the 30-minute early watch relief for more precise overlap checking
    echo "Applying 30-minute early relief adjustment to watch time...<br>";
    $start1->modify("-30 minutes");
    $end1->modify("-30 minutes");

    echo "Final Watch Time with Relief: Start: " . $start1->format('Y-m-d H:i:s') . ", End: " . $end1->format('Y-m-d H:i:s') . "<br>";

    // Check for time range overlap
    $overlap = $start1 <= $end2 && $end1 >= $start2;
    echo "Overlap Check: " . ($overlap ? "TRUE" : "FALSE") . "<br>";

    return $overlap;
}

/*// Correct for midnight rollovers in watch time
if ($start1->format("H:i:s") === "00:00:00") {
    //echo "Watch time starts after midnight. Adjusting start and end times to next day...<br>";
    $start1->modify("+1 day");
    $end1->modify("+1 day");
} else if($start1->format("H:i:s") < "16:00:00") {
  $start1->modify("+1 day");
  $end1->modify("+1 day");
} else if ($end1->format("H:i:s") === "00:00:00") {
  $end1->modify("+1 day");
}

// Correct for midnight rollovers in provided time
if ($end2 <= $start2 || $end2->format("H:i:s") === "00:00:00") {
    //echo "Provided time crosses midnight. Adjusting end time...<br>";
    $end2->modify("+1 day");
}else if ($start2->format("H:i:s") === "00:00:00")
{
  $start2->modify("+1 day");
  $end2->modify("+1 day");
}
*/

?>
