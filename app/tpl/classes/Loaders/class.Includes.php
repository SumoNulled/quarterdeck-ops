<?php
namespace Loaders;

class Includes
{
    // Define the directory where the include files are located
    private static $directory = '/xampp/www/qdops.com/app/tpl/includes/';

    // Static method to include a file
    public static function includeFile($filename)
    {
        // Construct the full path to the file
        $filePath = self::$directory . $filename . '.php';

        // Check if the file exists
        if (file_exists($filePath)) {
            // Include the file
            include $filePath;
        } else {
            // Handle the error (file not found)
            throw new \Exception("File not found: $filePath");
        }
    }
}
?>
