<?php
namespace Loaders;

error_reporting(E_ALL);
ini_set("display_errors", 1);

date_default_timezone_set('America/New_York');

class Autoloader
{
    /**
     * Register the auto loader using the loadCLass() function from this class.
     */
    public static function register()
    {
        try {
            spl_autoload_register([self::class, "loadClass"]);
        } catch (Exception $e) {
            error_log("Autoloader failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse the "use" declaration, build the class file path from the namespace, then include the class.
     *
     * @param string $className The name of the class, including the namespace, if applicable.
     *                          The namespace will dictate the folder that the class is in.
     */
    private static function loadClass(string $className)
    {
        /**
         * Convert the class name to use proper directory separators.
         * Example: Database\Config becomes Database/Config
         *
         * @var string $className Replace back-slashes with the proper directory separator.
         */
        $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);

        /**
         * Convert the class name into an array of it's path.
         * Example: Database/Config becomes [0] => Database, [1] => Config
         *
         * @var array $namespace Explode the class name on the directory separator.
         */
        $namespace = explode(DIRECTORY_SEPARATOR, $className);

        /**
         * Build the class name from pre-defined constraints.
         * Class names must be in class.HelloWorld.php format to be autoloaded.
         *
         * @var string $fileName Take the last value from $namespace, presumably the class name,
         *                       and convert it into class.HelloWorld.php format.
         */
        $fileName = "class." . end($namespace) . ".php";

        /**
         * @var string $mainDirectory Get the main server's document root. In this development
         *                            environment, the server document root is var/www/api
         */
        $mainDirectory = $_SERVER["DOCUMENT_ROOT"];

        /**
         * Configure where all the classes will be held.
         *
         * Due to how the $namespace array parses the incoming class, sub-directories
         * in "classes" are auto loaded if they are properly included in the namespace.
         *
         * @var string $classesDirectory Configure where all the classes will be held.
         */
        $classesDirectory = "app/tpl/classes";

        /**
         * @var string $subDirectory If the class is in a sub-directory, retrieve it's proper path
         *                           and add a DIRECTORY_SEPARATOR to the end. If the class is not
         *                           in a sub-directory, simple out put an empty string.
         */
        $subDirectory =
            count($namespace) > 1
                ? implode(DIRECTORY_SEPARATOR, array_slice($namespace, 0, -1)) .
                    DIRECTORY_SEPARATOR
                : "";

        /**
         * Build the final file path.
         *
         * Example Output: /var/www/api/app/tpl/classes/Database/class.Config.php
         *
         * @var string $filePath Concatenate $mainDirectory, $classesDirectory,
         *                       the namespace/sub-directory, and finally, the fileName.
         */
        $filePath =
            $mainDirectory .
            DIRECTORY_SEPARATOR .
            $classesDirectory .
            DIRECTORY_SEPARATOR .
            $subDirectory .
            $fileName;

        // Check if the file exists
        if (file_exists($filePath)) {
            // Include the class file
            include_once $filePath;
        }
    }
}

// Register the autoloader
Autoloader::register();
?>
