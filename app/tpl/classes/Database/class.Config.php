<?php

namespace Database;

use Exceptions\DatabaseException;
use Exceptions\ConnectionException;

class Config
{
    /**
    * @var string $host The connection host.
    * @var string $username The connection username.
    * @var string $password The connection password.
    * @var string $dbname The connection database name.
    */
    private string $host, $username, $password, $dbname;

    /**
    * @var \mysqli $conn The mysqli connection object.
    */
    private \mysqli $conn;

    /**
    * Configures the Config object with the default connection string.
    *
    * @param array $config The config array, expects indexes of: host, username, password, and dbname.
    * @param mysqli $connection The optional mysqli connection object.
    */
    public function __construct(?array $config = null, \mysqli $connection = null)
    {
        if ($connection !== null && !($connection instanceof \mysqli))
        {
          throw new \InvalidArgumentException(
            "Invalid mysqli connection object passed. Instance of 'mysqli' expected, '" . gettype($connection) . "' given."
          );
        }

        if ($config === null)
        {
            // Retrieve an array of the connection data from the database.ini file.
            $config = parse_ini_file("database.ini");
        }

        // Retrieve the database credentials from the parsed file
        foreach($config as $key => $value)
        {
          $this->$key = $value ?? null;
        }

        // Verify that the config file properly sets up the variables.
        try
        {
            if (empty($this->host)
                || empty($this->username)
                || empty($this->dbname))
            {
                throw new \InvalidArgumentException("Connection string expects values for ['host', 'username', 'dbname']");
            }
        }
        catch (\InvalidArgumentException $e)
        {
            error_log("Missing Configuration Index: {$e}");
            throw $e;
        }

        // Create a new MySQLi connection
        $this->conn =
            $connection ??
            new \mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->dbname
            );
    }

    /**
    * Validates the connection.
    *
    * @return mysqli The mysqli connection object.
    */
    public function connect()
    {
        try
        {
            /**
            * Checks to see if there are any errors with the validated connection string.
            *
            * @var \mysqli $this->conn The connection object configured in the constructor.
            */
            if ($this->conn->connect_error)
            {
                throw new ConnectionException(
                  $this->conn->connect_error
                );
            }
            else
            {
                return $this->conn;
            }
        }
        catch (ConnectionException $e)
        {
            error_log("Connection Error: " . $e->getMessage());
            throw $e;
        }
        catch (DatabaseException $e)
        {
            error_log("Database Error: " . $e->getMessage());
            throw $e;
        }
        catch (Exception $e)
        {
            error_log("Error: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
