<?php
namespace Database;

use Database\Config;

class MySQLi
{

  /**
  * @var \mysqli $conn The mysqli connection object.
  */
  private \mysqli $conn;

  /**
  * Initialize the connection between the database config.
  */
  public function __construct()
  {
      $conn = new Config;
      $this->conn = $conn->connect();
  }

  /**
  * Method for getting the connection variable.
  *
  * @return \mysqli | bool $this->conn The mysqli object configured in the constructor.
  */
  public function getConnection() : \mysqli | bool
  {
    return $this->conn;
  }

  /**
  * Close the connection once the object destructs.
  */
  public function __destruct()
  {
      $this->conn->close();
  }

  /**
  * Build a parameterized query to protect against SQL injections by default.
  *
  * @param string $query The SQL query string with placeholders for parameters.
  * @param mixed ...$params The array? of parameters to be bound to the query.
  * @return \mysqli_result|bool Returns the result of the query or false if an error is encountered.
  */
  public function Query(string $query, ...$params) : \mysqli_result | bool
  {
      try {
        // Prepare the SQL statement or trigger an error if preparation fails.
        $statement = $this->getConnection()->prepare($query) or trigger_error($this->getConnection()->error, E_USER_ERROR);
      } catch (\Exception $e) {
        throw new \Exception("Query Error: " . $e->getMessage());
        return false;
      }


      //if (!$statement) return false;

      // Build the parameters if provided.
      if(!empty($params))
      {
          $types = [];
          $bindParams = [];

          foreach ($params as $param)
          {
              // Get the type of each parameter: i, d, s, or b.
              $types[] = $this->getParamType($param);
              $bindParams[] = $param;
          }

          try
          {
              $types = implode("", $types);
              $statement->bind_param($types, ...$bindParams);
          }
          catch (\ArgumentCountError $e)
          {
              throw new \Exception($e);
              return false;
          }
      }

      // Execute the statement and return the result if successful
      if ($statement->execute())
      {
        try
        {
        $result = $statement->get_result();
          if ($result === false)
          {
            // Bypass errors revolving around get_results if query was succesful, but there are no results to get.
            if (!preg_match('/^\s*(INSERT|UPDATE|DELETE)\s+/i', $query))
            {
              throw new \Exception("Error fetching result: " . $statement->error);
            } else {
              $result = true;
            }
          }
        } catch (\Exception $e) {
          // Handle or rethrow the exception as needed
          throw $e;
        }

          $statement->close();
          return $result;
      }

      return false;
  }

  /**
  * Insert rows of data into a database table.
  *
  * @param string $query The SQL query string with placeholders for parameters.
  * @param mixed ...$params The array? of parameters to be bound to the query.
  */
  public function Insert(string $query, ...$params)
  {

  }

  /**
  * Fetch rows of data from a database table.
  *
  * @param string $query The SQL query string with placeholders for parameters.
  * @param mixed ...$params The array? of parameters to be bound to the query.
  * @return array|false Returns an array of rows on success, or false on failure.
  */
  public function Rows(string $query, ...$params) : array | bool
  {
    if($result = $this->Query($query, ...$params))
    {
        $rows = [];

        while ($row = $result->fetch_assoc())
        {
            $rows[] = $row;
        }

        return $rows;
    }

    return false;
  }

  /**
 * Get the parameter type for binding in a prepared statement.
 *
 * @param mixed $param The parameter value.
 * @return string Returns the parameter type character.
 */
  private function getParamType($param) : string
  {
      if (is_int($param))
      {
          return "i";
      }
      elseif (is_float($param))
      {
          return "d";
      }
      elseif (is_string($param))
      {
          return "s";
      }
      else
      {
          return "b";
      }
  }

  public function getSetValues($column)
  {
      // Assuming $conn is your database connection object

      // Query to get the column definition
      $columnQuery = "SHOW COLUMNS FROM sailors LIKE '{$column}'";
      $result = $this->conn->Query($columnQuery);

      if ($result) {
          $row = $result->fetch_assoc();
          $setValues = $row['Type']; // This contains the set values in a string format, e.g., "set('value1','value2',...)"

          // Extract the values using a regular expression
          preg_match("/^enum\((.+)\)$/", $setValues, $matches);

          // Remove quotes and split the values into an array
          $setArray = explode(",", str_replace("'", "", $matches[1]));

          // Use array_combine to make the indexes the same as the values
          return array_combine($setArray, $setArray);
      }

      return []; // Return an empty array if the query fails
  }

}
?>
