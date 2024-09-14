<?php

$conn = new \Database\MySQLi;
$sailor = new \Sailors\Sailors($conn);

$date = new DateTime('2024-09-06');
$watchbill = new \WatchBill\DutyWatchbill($conn, $date);
$timeslots = new \WatchBill\TimeSlots($conn);

try {
    $watchbill->setRow(33);
  //  echo $watchbill->watch_location;
} catch (Exception $e) {
    // Gracefully handle the exception
    echo "An error occurred: " . $e->getMessage();
}


function canStandWatch($sailor, $watchbill): bool
{
    $emptyWatches = $watchbill->getEmptyWatches();
    $sailors = $sailor->getSailorsArray();

    foreach($sailors as $seaman)
    {
      echo "--- START OF {$seaman['last_name']}<br/>";
      foreach($emptyWatches as $watch)
      {
        $class_hours = $sailor->getClassHours($seaman['id']);
        $is_qualified = $watchbill->isSailorQualified($seaman, $watch);

        if($is_qualified)
        {
            echo("{$seaman['last_name']} can stand this watch!<br/>");
        } else {
          echo("{$seaman['last_name']} cannot stand this watch!<br/>");
        }
      }
      /*
      if (!$sailor->isDutyDriver($s['id']))
      {
        foreach($emptyWatches as $row)
        {
          $secured = $watchbill->isSecureWatch($row['id']);
          $overlap = $watchbill->timeRangesOverlap($row['id'], $sailor->getClassHours($s['id'])) ? "OVERLAP" : "NO OVERLAP";

          $classification = $secured ? "SECURED" : "UNSECURED";
          echo "{$overlap} {$classification} [ID: {$row['id']}] BAW: {$row['baw_id']}, BRW: {$row['brw_id']}<br />";

          if (!$sailor->isLimitedDuty())
          {
            if (empty($row['brw_id']))
            {
              // Fill in the BAW, with constraints.
              echo "{$s->last_name} Can stand this brw.";
            }
          }

          if (empty($row['baw_id']))
          {
            echo "{$s->last_name} Can stand this baw.";
          }
        }
        //return true;
      }
      */
      echo "--- END OF {$seaman['last_name']}";
    }
    return false;
}

var_dump(canStandWatch($sailor, $watchbill));

?>
