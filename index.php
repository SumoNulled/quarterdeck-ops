<?php
//header("Content-type: application/json");

$endpoint = new \API\API();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify if the API key is set in the headers
$headers = apache_request_headers();

// Convert all header keys to lowercase
$headers = array_change_key_case($headers, CASE_LOWER);

$headers['authorization'] = "Code";
// Check for the 'authorization' header in a case-insensitive manner
$apiKey = isset($headers['authorization']) ? ($headers['authorization'] == "Code" ?? null) : null;

$backupKey = isset($_GET['key']) ? $_GET['key'] : null;

// Look for the URI in the URL and remove the slash. (EXample: /get_permissions -> get_permissions)
$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (str_contains($urlPath, "/handlers") || str_contains($urlPath, "/scripts"))
{
  // Remove the .php extension
  $endpointURI = preg_replace('/\.php$/', '', $urlPath);
} else {
  $endpointURI = basename($urlPath, '.php');
}

if ((!$apiKey && $backupKey !== 'Homelander') || (!file_exists("app/tpl/pages/{$endpointURI}.php") && !file_exists("app/tpl/pages{$endpointURI}")))
{
    // API key is not set, return an error response with headers
    http_response_code(401);
    echo json_encode(['error' => 'Invalid usage of the API.', 'invalid_endpoint' => $endpointURI], JSON_PRETTY_PRINT);
    exit;
}
else
{
    /**
    * Send a response code of 200 if an API key was received.
    *
    * Development enviroment usage only
    *
    * @return json_encode Return a success message for the WPF Application to catch.
    *
    */
    http_response_code(200);
    //echo json_encode(['success' => 'Connection to the API was successful.'], JSON_PRETTY_PRINT);

    // Include the endpoint files.
    $endpoint->add($endpointURI);
    $endpoint->open();
    exit;
}
?>
