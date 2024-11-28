<?php
namespace WatchBill;

use Database\MySQLi;

class DutyLocations
{
    /**
     * @var MySQLi $db The MySQLi database connection object.
     */
    private MySQLi $conn;

    /**
     * @var BuildingTypes $buildingTypes The BuildingTypes class instance.
     */
    private BuildingTypes $buildingTypes;

    /**
     * @var array $table Array to store watchbill data
     */
    private array $table;

    /**
     * Initialize the database connection and the BuildingTypes class.
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->buildingTypes = new BuildingTypes($conn);
        try {
          $query = "SELECT d.*, b.type FROM duty_locations d JOIN building_types b ON d.building_type_id = b.id";
          $this->table = $this->conn->Rows($query);
        } catch (\Exception $e) {
          echo $e->getMessage();
        }

    }

    /**
    * Magic method to get DutyLocation properties dynamically.
    * Example: $sailor->duty will call __get('duty')
    *
    * @param string $property The name of the property being accessed
    * @return mixed The value of the property, or null if not found
    */
     public function __get(string $property)
     {
         // Check if the requested property exists in the sailor array
         return $this->table[$property] ?? null;
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
            if ($row['id'] == $id) {
                // Return the requested property value if it exists in the row
                return $row[$property] ?? null;
            }
        }

        // Return null if no matching row or property is found
        return null;
    }

    /**
     * Insert a new location record into the duty_locations table.
     *
     * @param string $buildingName The name of the building.
     * @param string $buildingNumber The building number.
     * @param string $extension The extension of the building.
     * @param string $responsible The person responsible for the building.
     * @param int $buildingTypeId The ID of the building type.
     * @return bool Returns true on success, false on failure.
     */
    public function createLocation(string $buildingName, string $buildingNumber, string $extension, string $responsible, int $buildingTypeId) : bool
    {
        $query = "INSERT INTO duty_locations (building_name, building_number, extension, responsible, building_type_id)
                  VALUES (?, ?, ?, ?, ?)";
        return $this->conn->Insert($query, $buildingName, $buildingNumber, $extension, $responsible, $buildingTypeId);
    }

    /**
     * Retrieve a location record by its ID.
     *
     * @param int $id The ID of the building location.
     * @return array|bool Returns an array with location data or false on failure.
     */
    public function getLocationById(int $id) : array | bool
    {
        $query = "SELECT * FROM duty_locations WHERE id = ?";
        return $this->conn->Rows($query, $id);
    }

    /**
     * Update an existing location record.
     *
     * @param int $id The ID of the location to update.
     * @param string $buildingName The updated name of the building.
     * @param string $buildingNumber The updated building number.
     * @param string $extension The updated extension.
     * @param string $responsible The updated responsible person.
     * @param int $buildingTypeId The updated building type ID.
     * @return bool Returns true on success, false on failure.
     */
    public function updateLocation(int $id, string $buildingName, string $buildingNumber, string $extension, string $responsible, int $buildingTypeId) : bool
    {
        $query = "UPDATE duty_locations SET
                    building_name = ?,
                    building_number = ?,
                    extension = ?,
                    responsible = ?,
                    building_type_id = ?
                  WHERE id = ?";
        return $this->conn->Query($query, $buildingName, $buildingNumber, $extension, $responsible, $buildingTypeId, $id);
    }

    /**
     * Delete a location record by its ID.
     *
     * @param int $id The ID of the location to delete.
     * @return bool Returns true on success, false on failure.
     */
    public function deleteLocation(int $id) : bool
    {
        $query = "DELETE FROM duty_locations WHERE id = ?";
        return $this->conn->Query($query, $id);
    }

    /**
     * Retrieve all location records.
     *
     * @return array|bool Returns an array of all locations or false on failure.
     */
    public function getAllLocations() : array | bool
    {
        $query = "SELECT * FROM duty_locations";
        return $this->conn->Rows($query);
    }

    /**
     * Retrieve all locations that are used in the duty_watchbill_2 table.
     *
     * @return array|bool Returns an array of locations or false on failure.
     */
    public function getWatchbillLocations() : array | bool
    {
        $query = "SELECT DISTINCT d.id, d.building_name, d.building_number, d.extension, d.responsible, d.building_type_id
                  FROM duty_locations d
                  JOIN duty_watchbill_2 w ON d.id = w.watch_location";
        return $this->conn->Rows($query);
    }

    /**
     * Check if a building is classified as secured.
     *
     * @param int $id The ID of the building.
     * @return bool Returns true if the building is secured, false otherwise.
     */
    public function isBuildingSecured(int $id) : bool
    {
        $result = $this->getById($id, 'type');
        if (isset($result)) {
            return $this->getById($id, 'type') === 'SECURED';
        }
        return false;
    }

    /**
     * Retrieve the responsible person for a building.
     *
     * @param int $id The ID of the building.
     * @return string|null Returns the responsible person or null if not found.
     */
    public function getResponsibleForBuilding(int $id) : ?string
    {
        $query = "SELECT responsible FROM duty_locations WHERE id = ?";
        $result = $this->conn->Rows($query, $id);
        if ($result && isset($result[0]['responsible'])) {
            return $result[0]['responsible'];
        }
        return null;
    }

    /**
     * Retrieve the building type description for a given building.
     *
     * @param int $id The ID of the building.
     * @return string|null Returns the building type description or null if not found.
     */
    public function getBuildingTypeDescription(int $id) : ?string
    {
        $query = "SELECT b.description
                  FROM duty_locations d
                  JOIN building_types b ON d.building_type_id = b.id
                  WHERE d.id = ?";
        $result = $this->conn->Rows($query, $id);
        if ($result && isset($result[0]['description'])) {
            return $result[0]['description'];
        }
        return null;
    }

    public function getBuildingNameByBuildingNumber(int $buildingNumber) : ?string
    {
        $query = "SELECT building_name FROM duty_locations WHERE building_number = ?";
        $result = $this->conn->Rows($query, $buildingNumber);
        return $result[0]['building_name'];
    }

    public function getDetails()
    {
        $result['building_name'] = $this->building_name;
        $result['building_number'] = $this->building_number;
        $result['extension'] = $this->extension;
        $result['responsible'] = $this->getBuildingNameByBuildingNumber($this->responsible) . " ({$this->responsible})";
        $result['secured'] = $this->isBuildingSecured($this->id);

        return $result;
    }
}
?>
