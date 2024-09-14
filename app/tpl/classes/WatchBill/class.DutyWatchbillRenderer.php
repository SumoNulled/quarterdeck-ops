<?php

namespace WatchBill;

class DutyWatchbillRenderer
{
    protected $conn;
    protected $timeslots;
    protected $date;

    public function __construct($conn, $date)
    {
        $this->conn = $conn;
        $this->timeslots = new TimeSlots($this->conn);
        $this->date = $date;
    }

    /**
     * Fetch the left and right tables data based on responsibility
     * @return array An array with 'leftTables' and 'rightTables'
     */
    public function fetchTables()
    {
        $leftTables = [];
        $rightTables = [];

        foreach ($this->conn->Rows("SELECT * FROM duty_locations") as $row) {
            $backgroundColor = '';

            if ($row['responsible'] == 492) {
                $backgroundColor = ($row['building_number'] == 435) ? '' : '#dfebf7';
                $rightTables[] = ['backgroundColor' => $backgroundColor, 'row' => $row];
            } elseif ($row['responsible'] == 534) {
                $backgroundColor = '#faead4';
                $leftTables[] = ['backgroundColor' => $backgroundColor, 'row' => $row];
            }
        }

        return ['leftTables' => $leftTables, 'rightTables' => $rightTables];
    }

    /**
     * Fetch the time slot assignments by type
     * @param string $timeSlotCode The time slot type ('WD', 'WE')
     * @return string A comma-separated list of time slot assignment IDs
     */
    public function getTimeSlotAssignments($timeSlotCode)
    {
        $assignments = $this->timeslots->getTimeSlotAssignmentsByType($timeSlotCode, 1);

        if (is_array($assignments)) {
            return implode(', ', $assignments);
        }

        throw new \Exception('Failed to retrieve time slot assignments');
    }

    /**
     * Renders the tables for the given responsibility
     * @param array $tables Array of tables (left or right)
     * @param string $timeSlotAssignmentsList A list of time slot assignment IDs
     */
    public function renderTables($tables, $timeSlotAssignmentsList)
    {
        $query = "
           SELECT
              a.timeslot_assignment,
              a.watch_location,
              sa1.last_name AS baw_name,
              a.baw_id,
              a.baw_signed,
              sa2.last_name AS brw_name,
              a.brw_id,
              a.brw_signed,
              dta.description AS time
           FROM duty_watchbill_2 a
           LEFT JOIN sailors sa1 ON a.baw_id = sa1.id
           LEFT JOIN sailors sa2 ON a.brw_id = sa2.id
           LEFT JOIN duty_timeslot_assignments dta ON a.timeslot_assignment = dta.id
           WHERE a.watch_location = ?
           AND a.date = '{$this->date->format('Y-m-d')}'
           AND a.timeslot_assignment IN ({$timeSlotAssignmentsList})
           ORDER BY dta.sequence, a.watch_location
        ";

        foreach ($tables as $table) {
            echo "
            <div class='table-responsive' style='padding: 0;'>
                <table class='table table-bordered table-striped table-hover' style='margin: 0; padding: 0; width: 100%;'>
                    <thead>
                        <tr>
                            <th colspan='5' style='background-color: {$table['backgroundColor']}; text-align: center;'>
                                {$table['row']['building_name']} {$table['row']['building_number']} (x{$table['row']['extension']})
                            </th>
                        </tr>
                        <tr>
                            <th style='text-align: center;'>Time</th>
                            <th style='text-align: center;'>B.A.W</th>
                            <th style='text-align: center;'>INT</th>
                            <th style='text-align: center;'>B.R.W</th>
                            <th style='text-align: center;'>INT</th>
                        </tr>
                    </thead>
                    <tbody>";

            foreach ($this->conn->Rows($query, $table['row']['id']) as $row) {
                $this->renderRow($row);
            }

            echo "</tbody></table></div>";
        }
    }

    /**
     * Renders a row in the watchbill table
     * @param array $row Data of a single row
     */
    private function renderRow($row)
    {
        $timeSlot = $this->timeslots->getTimeRangeByAssignmentId($row['timeslot_assignment']);
        $timeSlot = $this->timeslots->timeRangeToString($timeSlot[0], $timeSlot[1]);

        $bawClass = $row['baw_signed'] ? 'green-bg' : 'default-bg';
        $brwClass = $row['brw_signed'] ? 'green-bg' : 'default-bg';

        echo "
        <tr>
            <td><center>{$timeSlot}</center></td>
            <td class='editable baw' data-id='{$row['baw_id']}' data-type='BAW' data-timeslot='{$row['timeslot_assignment']}' data-timerange='{$timeSlot}' data-location='{$row['watch_location']}'><center>{$row['baw_name']}</center></td>
            <td id='baw_int' data-baw_signed='{$row['baw_signed']}' class='toggleable {$bawClass}'></td>
            <td class='editable brw' data-id='{$row['brw_id']}' data-type='BRW' data-timeslot='{$row['timeslot_assignment']}' data-timerange='{$timeSlot}' data-location='{$row['watch_location']}'><center>{$row['brw_name']}</center></td>
            <td id='brw_int' data-brw_signed='{$row['brw_signed']}' class='toggleable {$brwClass}'></td>
        </tr>";
    }
}

?>
