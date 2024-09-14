<?php
namespace WatchBill;

use DutyLocations;

class DutyWatchbill
{
    /**
     * @var \Database\MySQLi $conn The database connection object
     */
    protected \Database\MySQLi $conn;

    /**
     * @var string $date The date string
     */
    protected string $date;

    /**
     * @var array $table Array to store watchbill data
     */
    private array $table;

    /**
     * @var int|null $rowID Variable to store the current row id. Defaults to null until set.
     */
    private ?int $rowID = null;

    /**
     * @var DutyLocations $dutyLocation The DutyLocation class instance.
     */
    private \WatchBill\DutyLocations $dutyLocation;

    /**
     * @var Sailors $sailor The Sailors class instance.
     */
    private \Sailors\Sailors $sailor;

    /**
     * @var TimeSlots $dutyLocation The DutyLocation class instance.
     */
    private \WatchBill\TimeSlots $timeSlot;

    /**
     * Constructor method.
     * Initializes the sailor object by fetching their data from the database.
     *
     * @param \Database\MySQLi $conn The database connection object
     */

    public function __construct(\Database\MySQLi $conn, \DateTime $date)
    {
        $this->conn = $conn;
        $this->date = $date->format("Y-m-d");
        $this->sailor = new \Sailors\Sailors($this->conn);
        $this->timeSlot = new \WatchBill\TimeSlots($this->conn);
        $this->dutyLocation = new \WatchBill\DutyLocations($this->conn);
        // Get the day of the week (1 for Monday, 7 for Sunday)
        $dayOfWeek = $date->format("N") >= 6 ? "WE" : "WD";
        $relevantTimeSlots = $this->timeSlot->getTimeSlotAssignmentsByType(
            $dayOfWeek,
            1
        );
        $relevantTimeSlots = implode(",", $relevantTimeSlots);
        $query = "SELECT dw.*, dta.sequence FROM duty_watchbill_2 dw JOIN duty_timeslot_assignments dta ON dta.id = dw.timeslot_assignment WHERE dw.date = ? AND dw.timeslot_assignment IN ({$relevantTimeSlots}) ORDER BY sequence ASC";
        $this->table = $this->conn->Rows($query, $this->date);
    }

    public function setRow(int $rowID)
    {
        $this->rowID = $rowID;
    }

    /**
     * Magic method to get sailor properties dynamically.
     * Example: $sailor->duty will call __get('duty')
     *
     * @param string $property The name of the property being accessed
     * @return mixed The value of the property, or null if not found
     */
    public function __get(string $property)
    {
        // Check if rowID is set
        if ($this->rowID === null) {
            throw new \Exception("rowID is not set. Use setRow() to set it.");
        }

        // Search through the table array to find the specific row with matching ID
        foreach ($this->table as $row) {
            if ($row["id"] == $this->rowID) {
                // Return the requested property value if it exists in the row
                return $row[$property] ?? null;
            }
        }

        // Return null if no matching row or property is found
        return null;
    }

    /**
     * Magic method to get specific rows or attributes dynamically.
     * Example: $sailor->getById(33, 'baw_id') will return the BAW ID for the record with ID 33.
     *
     * @param int $id The specific watchbill ID you want to access
     * @param string $property The specific column (attribute) you want to access
     * @return mixed The value of the property, or null if not found
     */
    public function getById(int $id, string $property)
    {
        // Search through the table array to find the specific row with matching ID
        foreach ($this->table as $row) {
            if ($row["id"] == $id) {
                // Return the requested property value if it exists in the row
                return $row[$property] ?? null;
            }
        }

        // Return null if no matching row or property is found
        return null;
    }

    /**
     * Magic method to set sailor properties dynamically.
     * Example: $sailor->duty = 'New Duty' will call __set('duty', 'New Duty')
     *
     * @param string $property The name of the property being set
     * @param mixed $value The value to set for the property
     */
    public function __set(string $property, $value)
    {
        // Update the sailor property in the database
        if (
            $this->conn->Query(
                "UPDATE sailors SET {$property} = ? WHERE id = ?",
                $value,
                $this->sailor["id"]
            )
        ) {
            // If update is successful, update the local sailor array
            $this->sailor[$property] = $value;
        }
    }

    /**
     * Returns the building name for the current watch location.
     *
     * @return string
     */
    public function printWatchLocation()
    {
        // Fetch the building name from the dutyLocation object
        $buildingName = $this->dutyLocation->getBuildingName(
            $this->watch_location
        );

        // Return the building name
        return $buildingName ?? "Unknown Location";
    }

    /**
     * Checks if the current watch location is secured.
     *
     * @return bool
     */
    public function isSecureWatch(int $id): bool
    {
        // Check if the watch location is marked as 'SECURED'
        if (
            $this->dutyLocation->isBuildingSecured(
                $this->getById($id, "watch_location")
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the current watch location is secured.
     *
     * @return bool
     */
    public function isWeekendWatch(int $id): bool
    {
        // Check if the watch location is marked as 'WEEKEND'
        if (
            $this->timeSlot->getTimeslotType(
                $this->getById($id, "timeslot_assignment")
            ) == "WE"
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determines if a given time range overlaps with the current time slot.
     *
     * @param array $timeRange An array with start and end times in 'H:i' format (e.g., ['09:00', '12:00']).
     * @return bool
     * @throws \Exception If the time ranges are missing or invalid.
     */
    public function timeRangesOverlap(int $id, array $timeRange): bool
    {
        // Get the current watch time range based on the timeslot assignment
        $watchTime = $this->timeSlot->getTimeRangeByAssignmentId(
            $this->getById($id, "timeslot_assignment")
        );

        // Ensure both time ranges (watch and provided) have valid start and end times

        $start1 =
            $watchTime[0] ??
            throw new \Exception("Invalid watch time range: {$watchTime}");
        $end1 =
            $watchTime[1] ?? throw new \Exception("Invalid watch time range");
        $start2 =
            $timeRange[0] ??
            throw new \Exception("Invalid time range provided");
        $end2 =
            $timeRange[1] ??
            throw new \Exception("Invalid time range provided");

        // Convert time strings to DateTime objects
        $start1 = new \DateTime($start1 . " " . $this->date);
        $end1 = new \DateTime($end1 . " " . $this->date);
        $start2 = new \DateTime($start2 . " " . $this->date);
        $end2 = new \DateTime($end2 . " " . $this->date);

        // Handle cases where times roll over midnight
        if ($end1 <= $start1) {
            $end1->modify("+1 day");
        }

        if ($end2 <= $start2 || $end2->format("H:i:s") === "00:00:00") {
            $end2->modify("+1 day");
        }

        if ($start1->format("H:i:s") === "00:00:00") {
            $start1->modify("+1 day");
            $end1->modify("+1 day");
        }

        if ($start2->format("H:i:s") === "00:00:00") {
            $start2->modify("+1 day");
            $end2->modify("+1 day");
        }

        // Apply the 30 minutes early watch relief for more precise overlap checking.
        $start1->modify("-30 minutes");
        $end1->modify("-30 minutes");

        // Check for time range overlap
        return $start1 <= $end2 && $end1 >= $start2;
    }

    /**
     * Determines if a sailor is qualified for a specific watch slot.
     *
     * @param array $sailor - Sailor details (e.g., qualifications, status)
     * @param array $watch - Watch slot details (e.g., location, position)
     * @return bool - True if the sailor is qualified, false otherwise
     */
    public function isSailorQualified($sailor, $watch): bool
    {
        // Sailor is a Duty Driver and therefore exempt from standing watch
        if ($sailor["duty_driver"] === 1) {
            return false;
        }

        // Sailor on Light Limited Duty cannot stand BRW watches
        if (
            $sailor["light_limited_duty"] === 1 &&
            isset($watch["position"]) && $watch['position'] == "BRW"
        ) {
            return false;
        }

        // Check if sailor has class hours conflict during the week. (Excludes weekends).
        if (
            $this->timeRangesOverlap(
                $watch["id"],
                $this->sailor->getClassHours($sailor["id"])
            ) &&
            !$this->isWeekendWatch($watch["id"])
        ) {
            return false;
        }

        // Check if sailor has mandatory study conflict during the week. (Excludes weekends).
        // Add code here once ready.

        // Check if the watch location is a SECURED building
        $isSecured = $this->isSecureWatch($watch["id"]);

        // For SECURED buildings, sailor must be Secure qualified
        if ($isSecured && !$sailor["secure_qualified"]) {
            return false;
        }

        // For UNSECURED buildings, sailor must be Basic qualified
        if (!$isSecured && !$sailor["basic_qualified"]) {
            return false;
        }

        // Sailor is qualified for this watch slot
        return true;
    }

    /**
     * Fills the watchbill with available sailors.
     *
     * This method assigns sailors to empty watch slots for BAW and BRW positions.
     * It first attempts to assign sailors to any empty slots, and then fills remaining empty slots by doubling up sailors if needed.
     *
     * @return array Returns an array of empty watch slots remaining after assignment.
     */
     public function fillWatchBill(): array
     {
     // Get the list of sailors and shuffle to randomize assignment
     $sailors = $this->sailor->getSailorsArray();
     shuffle($sailors);

     // Get the IDs of sailors already assigned to any watches
     $usedSailors = explode(",", $this->getSailorsWithWatches());

     // Pre-fetch empty watch slots
     $emptyWatches = $this->getEmptyWatches();

     // Create a map of sailor IDs to sailor data for quick lookup
     $sailorMap = [];
     foreach ($sailors as $sailor) {
         $sailorMap[$sailor["id"]] = $sailor;
     }

     // Update watch slots
     $updateQueries = [];
     foreach ($emptyWatches as $row) {
         // Assign BAW if empty
         if (is_null($row["baw_id"])) {
           $row['position'] = "BAW";
             foreach ($sailors as $sailor) {
                 if (
                     !in_array($sailor["id"], $usedSailors) &&
                     $this->isSailorQualified($sailor, $row)
                 ) {
                     $row["baw_id"] = $sailor["id"];
                     $usedSailors[] = $sailor["id"];
                     $updateQueries[] = [
                         "id" => $row["id"],
                         "field" => "baw_id",
                         "value" => $sailor["id"]
                     ];
                     break;
                 }
             }
         }

         // Assign BRW if empty
         if (is_null($row["brw_id"])) {
           $row['position'] = "BRW";
             foreach ($sailors as $sailor) {
                 if (
                     !in_array($sailor["id"], $usedSailors) &&
                     $this->isSailorQualified($sailor, $row)
                 ) {
                     $row["brw_id"] = $sailor["id"];
                     $usedSailors[] = $sailor["id"];
                     $updateQueries[] = [
                         "id" => $row["id"],
                         "field" => "brw_id",
                         "value" => $sailor["id"]
                     ];
                     break;
                 }
             }
         }
     }

     // Perform batch update for all queries
     foreach ($updateQueries as $queryData) {
         $this->conn->Query(
             "UPDATE duty_watchbill_2 SET {$queryData['field']} = ? WHERE id = ?",
             $queryData['value'],
             $queryData['id']
         );
     }

     // Check for remaining empty watches and handle doubling up if needed
     $emptyWatches = $this->getEmptyWatches();
     if (!empty($emptyWatches)) {
         foreach ($emptyWatches as $emptyRow) {
             shuffle($sailors); // Shuffle for randomness in doubling up

             // Handle BAW empty slots first
             if (is_null($emptyRow["baw_id"])) {
               $emptyRow['position'] = "BAW";
                 foreach ($sailors as $sailor) {
                     if ($this->canDoubleUp($sailor, $emptyRow)) {
                         $emptyRow["baw_id"] = $sailor["id"];
                         $this->conn->Query(
                             "UPDATE duty_watchbill_2 SET baw_id = ? WHERE id = ?",
                             $sailor["id"],
                             $emptyRow["id"]
                         );
                         break;
                     }
                 }
             }

             // Handle BRW empty slots
             if (is_null($emptyRow["brw_id"])) {
               $emptyRow['position'] = "BRW";
                 foreach ($sailors as $sailor) {
                     if ($this->canDoubleUp($sailor, $emptyRow)) {
                         $emptyRow["brw_id"] = $sailor["id"];
                         $this->conn->Query(
                             "UPDATE duty_watchbill_2 SET brw_id = ? WHERE id = ?",
                             $sailor["id"],
                             $emptyRow["id"]
                         );
                         break;
                     }
                 }
             }
         }
     }

     // Return remaining empty watch slots
     return $this->getEmptyWatches();
 }


    /**
     * Checks if a sailor can be assigned to a watch position even if they are already assigned to another.
     *
     * This method ensures that a sailor meets qualifications and is not assigned to more than the allowed number of watches for the day.
     *
     * @param array $sailor The sailor's data.
     * @param array $row The current watch slot data.
     * @return bool Returns true if the sailor can be doubled up, false otherwise.
     */
    public function canDoubleUp(array $sailor, array $row): bool
    {
        if (
            !$this->isSailorQualified($sailor, $row) ||
            $this->isSailorAlreadyAssignedToTimeslot($sailor, $row)
        ) {
            return false;
        }

        $date = $row["date"]; // Assuming 'date' field in the row provides the relevant date
        $watchCount = $this->countSailorAssignmentsForDate(
            $sailor["id"],
            $date
        );

        // Check if the sailor is already assigned to 2 watches for the day
        return $watchCount < 2;
    }

    /**
     * Counts the number of watches a sailor is assigned to on a specific date.
     *
     * @param int $sailorId The ID of the sailor.
     * @param string $date The date for which to count assignments.
     * @return int Returns the count of assignments for the sailor on the given date.
     */
    public function countSailorAssignmentsForDate(
        int $sailorId,
        string $date
    ): int {
        $query = "SELECT COUNT(*) AS count
                FROM duty_watchbill_2
                WHERE (baw_id = ? OR brw_id = ?)
                AND DATE(date) = ?";

        $result = $this->conn->Rows($query, $sailorId, $sailorId, $date);

        return (int) $result[0]["count"]; // Return count as integer
    }

    /**
     * Checks if a sailor is already assigned to a specific timeslot.
     *
     * @param array $sailor The sailor's data.
     * @param array $row The current watch slot data.
     * @return bool Returns true if the sailor is already assigned to this timeslot, false otherwise.
     */
    public function isSailorAlreadyAssignedToTimeslot(
        array $sailor,
        array $row
    ): bool {
        $timeslotAssignmentId = $row["timeslot_assignment"];

        $query = "SELECT COUNT(*) AS count
                FROM duty_watchbill_2
                WHERE (baw_id = ? OR brw_id = ?)
                AND timeslot_assignment = ?";

        $result = $this->conn->Rows(
            $query,
            $sailor["id"],
            $sailor["id"],
            $timeslotAssignmentId
        );

        return $result[0]["count"] > 0; // Return true if count is greater than 0
    }

    /**
     * Empties all the slots in the watchbill.
     *
     * This method sets all BAW and BRW IDs and signed statuses to null, effectively clearing the watchbill.
     */
    public function emptyWatchBill(): void
    {
        $query =
            "UPDATE duty_watchbill_2 SET brw_id = NULL, baw_id = NULL, brw_signed = NULL, baw_signed = NULL";
        $this->conn->Query($query);
    }

    /**
     * Retrieves all empty watch slots from the current table data.
     *
     * @return array Returns an array of rows representing empty watch slots.
     */
    public function getEmptyWatches(): array
    {
        $emptyWatches = [];

        // Loop through the already fetched rows in $this->table
        foreach ($this->table as $row) {
            if (is_null($row["baw_id"]) || is_null($row["brw_id"])) {
                $emptyWatches[] = [
                    "id" => $row["id"],
                    "watch_location" => $row["watch_location"],
                    "timeslot_assignment" => $row["timeslot_assignment"],
                    "baw_id" => $row["baw_id"],
                    "brw_id" => $row["brw_id"],
                    "date" => $row["date"],
                ];
            }
        }

        return $emptyWatches;
    }

    /**
     * Retrieves a comma-separated list of sailor IDs who are currently assigned to any watches.
     *
     * @return string Returns a comma-separated list of sailor IDs.
     */
    public function getSailorsWithWatches(): string
    {
        $query = "
          SELECT DISTINCT baw_id AS sailor_id
          FROM duty_watchbill_2
          WHERE baw_id IS NOT NULL
          UNION
          SELECT DISTINCT brw_id AS sailor_id
          FROM duty_watchbill_2
          WHERE brw_id IS NOT NULL
      ";

        $result = $this->conn->Rows($query);

        $sailorIDs = array_column($result, "sailor_id");

        return implode(",", $sailorIDs); // Return IDs as a comma-separated string
    }

    /**
     * Retrieves sailors who are not currently assigned to any watches.
     *
     * @return array Returns an array of sailors who do not have any current watch assignments.
     */
    public function getSailorsWithoutWatches(): array
    {
        $sailorIDs = !empty($this->getSailorsWithWatches())
            ? $this->getSailorsWithWatches()
            : 0;

        $query = "SELECT id, last_name, first_name FROM sailors WHERE id NOT IN ({$sailorIDs})";

        return $this->conn->Rows($query); // Return array of sailors not assigned to watches
    }
}
?>
