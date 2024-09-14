<?php
namespace WatchBill;

use Database\MySQLi;

class BuildingTypes
{
  /**
   * @var \Database\MySQLi $conn The database connection object
   */
  protected \Database\MySQLi $conn;

    /**
     * Initialize the database connection.
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Retrieve all building types.
     *
     * @return array|bool Returns an array of all building types or false on failure.
     */
    public function getAllBuildingTypes() : array | bool
    {
        $query = "SELECT * FROM building_types";
        return $this->conn->Rows($query);
    }

    /**
     * Retrieve a building type by its ID.
     *
     * @param int $id The ID of the building type.
     * @return array|bool Returns an array with building type data or false on failure.
     */
    public function getBuildingTypeById(int $id) : array | bool
    {
        $query = "SELECT type FROM building_types WHERE id = ?";
        return $this->conn->Rows($query, $id);
    }

    /**
     * Retrieve a building type by its description.
     *
     * @param string $description The description of the building type.
     * @return array|bool Returns an array with building type data or false on failure.
     */
    public function getBuildingTypeByDescription(string $description) : array | bool
    {
        $query = "SELECT * FROM building_types WHERE description = ?";
        return $this->conn->Rows($query, $description);
    }
}
?>
