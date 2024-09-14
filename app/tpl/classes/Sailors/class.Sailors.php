<?php
namespace Sailors;

class Sailors
{
    /**
     * @var \Database\MySQLi $conn The database connection object
     */
    protected \Database\MySQLi $conn;

    /**
     * @var array $sailor Array to store sailor data
     */
    private array $sailor;

    /**
    * @var \Sailors\SailorsClassHours $sailorsClassHours The SailorsClassHours object.
    */
    private \Sailors\SailorsClassHours $sailorsClassHours;

    /**
     * @var DutyLocations $dutyLocation The DutyLocation class instance.
     */
    private \WatchBill\DutyLocations $dutyLocation;

    /**
     * Constructor method.
     * Initializes the sailor object by fetching their data from the database.
     *
     * @param \Database\MySQLi $conn The database connection object
     * @param int $sailorId The ID of the sailor to fetch data for
     */
    public function __construct(\Database\MySQLi $conn)
    {
        $this->conn = $conn;
        // Fetch the sailor data and assign it to the sailor array
        $this->sailor = $this->conn->Rows("SELECT * FROM sailors");
        $this->sailorsClassHours = new \Sailors\SailorsClassHours($this->conn);
        $this->dutyLocation = new \WatchBill\DutyLocations($this->conn);
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
         // Check if the requested property exists in the sailor array
         return $this->sailor[$property] ?? null;
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
        foreach ($this->sailor as $row) {
            if ($row['id'] == $id) {
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
         if ($this->conn->Query("UPDATE sailors SET {$property} = ? WHERE id = ?", $value, $this->sailor['id'])) {
             // If update is successful, update the local sailor array
             $this->sailor[$property] = $value;
         }
     }

     public function getClassHours(int $id): array
     {
       return $this->sailorsClassHours->getClassHoursById($this->getById($id, 'hours'));
     }
    /**
     * Check if the sailor has mandatory study.
     *
     * @return bool True if the sailor has mandatory study, false otherwise
     */
    public function hasMandatoryStudy(): bool
    {
        return !empty($this->sailor['mandatory_study']) && $this->sailor['mandatory_study'] === 'True';
    }

    /**
     * Check if the sailor is a duty driver.
     *
     * @return bool True if the sailor is a duty driver, false otherwise
     */
    public function isDutyDriver(int $id): bool
    {
        return !empty($this->getById($id, 'duty_driver')) && $this->getById($id, 'duty_driver') === '1';
    }

    /**
     * Check if the sailor is a duty driver.
     *
     * @return bool True if the sailor is on light limited duty, false otherwise
     */
    public function isLimitedDuty(int $id): bool
    {
        return !empty($this->getById($id, 'light_limited_duty')) && $this->getById($id, 'light_limited_duty') === '1';
    }

    public function getSailorsArray() : array
    {
       return $this->sailor;
    }

    // Additional methods can be added to expand sailor details as needed.
}
?>
