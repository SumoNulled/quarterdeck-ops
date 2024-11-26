<?php

namespace Sailors;

class SailorsMandatoryStudy
{
    /**
     * @var \Database\MySQLi $conn The database connection object
     */
    protected \Database\MySQLi $conn;

    /**
     * Constructor to initialize the database connection.
     *
     * @param \MySQLi $db The database connection object.
     */

     /**
      * @var array $table Array to store class_hours data
      */
     private array $table;


    public function __construct($conn)
    {
        $this->conn = $conn;

        $query = "SELECT id, start_time, end_time FROM mandatory_study_periods";
        $this->table = $this->conn->Rows($query);
    }

    /**
    * Magic method to get mandatory study properties dynamically.
    * Example: $mando->duty will call __get('duty')
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
      * Example: $SailorsMandatoryStudy->getById(3, 'start_time') will return the class start time.
      *
      * @param int $id The specific watchbill ID you want to access
      * @param string $property The specific column (attribute) you want to access
      * @return mixed The value of the property, or null if not found
      */
     public function getById(?int $id, string $property)
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
     * Fetches the class start and end times for the given sailors_class_hours ID.
     *
     * @param int $id The ID from the sailors_class_hours table.
     * @return array An array where index 0 is the class_start and index 1 is the class_end.
     * @throws \Exception If no record is found for the given ID.
     */
    public function getStudyHoursById(?int $id): array
    {

        $class_start = $this->getById($id, 'start_time');
        $class_end = $this->getById($id, 'end_time');

        if(!empty($class_start) && !empty($class_end))
        {
          return [$class_start, $class_end];
        } else {
            // If no record is found, throw an exception
            throw new \Exception("No class hours found for ID: {$id}");
        }
    }
}
