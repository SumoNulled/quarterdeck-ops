<?php
namespace WatchBill;

use Database\MySQLi;

class TimeSlots
{
    /**
     * @var MySQLi $conn The MySQLi connection object.
     */
    private MySQLi $conn;

    /**
     * @var array $table Array to store watchbill data
     */
    private array $table;

    /**
     * Initialize the TimeSlots class with a database connection.
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
        $query = "SELECT dta.id AS id, dt.start_time, dt.end_time FROM duty_timeslot_assignments dta JOIN duty_timeslots dt ON dta.timeslot_id = dt.id;";
        $this->table = $this->conn->Rows($query);
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
           if ($row['id'] == $this->rowID) {
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
   public function getById(int $id, string $property = null)
   {
       // Search through the table array to find the specific row with matching ID
       foreach ($this->table as $row) {
           if ($row['id'] == $id) {
               if (isset($property))
               {
                 return $row[$property] ?? null;
               }
               return $row;
           }
       }

       // Return null if no matching row or property is found
       return null;
   }

    /**
     * Get all time slots with their details.
     *
     * @return array|bool Returns an array of time slots or false on failure.
     */
    public function getAllTimeSlots() : array | bool
    {
        $query = "SELECT * FROM duty_timeslots";
        return $this->conn->Rows($query);
    }

    /**
     * Get all time slot assignments based on a type (weekday, weekend, other).
     *
     * @param string $type The type of timeslot ('WD', 'WE', 'OTH').
     * @param int $option Switches between return only IDs or the whole data row.
     * @return array|bool Returns an array of time slot assignments or false on failure.
     */
    public function getTimeSlotAssignmentsByType(string $type, int $option = 0) : array | bool
    {
        $type = strtoupper($type);

        $query = "SELECT dta.id, dt.start_time, dt.end_time, dta.description, dta.sequence, dta.type
                  FROM duty_timeslot_assignments dta
                  JOIN duty_timeslots dt ON dta.timeslot_id = dt.id
                  WHERE dta.type = ? ORDER BY dta.sequence ASC";

                  switch ($option)
                  {
                    case 1:
                    foreach ($this->conn->Rows($query, $type) as $row)
                    {
                      $result[] = $row['id'];
                    }
                    break;
                    default:
                    $result = $this->conn->Rows($query, $type);
                    break;
                  }

        return $result ?? false;
    }

    /**
     * Convert a time range to a string format (e.g., 09-12).
     *
     * @param string $startTime The start time in 'HH:MM:SS' format.
     * @param string $endTime The end time in 'HH:MM:SS' format.
     * @return string The formatted time range.
     */
    public function timeRangeToString(string $startTime, string $endTime) : string
    {
        $start = date('H', strtotime($startTime));
        $end = date('H', strtotime($endTime));
        return "{$start}-{$end}";
    }

    /**
     * Get the time range for a specific timeslot assignment ID.
     *
     * @param int $assignmentId The timeslot assignment ID.
     * @return array|bool Returns an array of the time range in start =>, end => format.
     */
    public function getTimeRangeByAssignmentId(int $id) : array | bool
    {
        $result = $this->getById($id);

        if ($result && isset($result['start_time'], $result['end_time'])) {
            $startTime = $result['start_time'];
            $endTime = $result['end_time'];

            // Use timeRangeToString to format the time range
            return [$startTime, $endTime];
        }

        return false;
    }

    /**
     * Get timeslot details including type.
     *
     * @param int $id The timeslot ID.
     * @return array|bool Returns an array of timeslot details or false on failure.
     */
    public function getTimeslotDetails(int $id) : array | bool
    {
        $query = "SELECT dt.*, dtt.type AS timeslot_type
                  FROM duty_timeslots dt
                  JOIN duty_timeslot_types dtt ON dt.id = dtt.type_id
                  WHERE dt.id = ?";
                  return $this->getById($id);
        return $this->conn->Rows($query, $id);
    }

    /**
     * Get the description for a timeslot type.
     *
     * @param string $type The type (weekday, weekend, other).
     * @return string|bool Returns the description of the timeslot type or false on failure.
     */
    public function getTimeslotType(int $id) : string | bool
    {
        $query = "SELECT type FROM duty_timeslot_assignments WHERE id = ?";
        $result = $this->conn->Rows($query, $id);

        if ($result && isset($result[0]['type'])) {
            return $result[0]['type'];
        }

        return false;
    }
}
?>
