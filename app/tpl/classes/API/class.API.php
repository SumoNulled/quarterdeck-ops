<?php
namespace API;

class API
{
    /**
     * @var string $url The URL to the API endpoint.
     * @var string $response The placeholder for the curl response.
     */
    private string $url, $response;

    /**
     * @var array $data The placeholder array for POST payload.
     * @var array $headers The headers for the cURL request.
     */
    private array $data, $endpoints, $headers;

    /**
     * Configures the API object.
     *
     * @param string $url The API endpoint to be initialized into the object.
     * @param array $data The payload to be initialized into the object.
     */
    public function __construct(string $url = '', array $data = [], array $endpoints = [])
    {
        $this->url = $url;
        $this->data = $data;
        $this->endpoints = $endpoints;
        $this->headers = []; // Initialize headers as an empty array
    }

    /**
     * Set headers for the cURL request.
     *
     * @param array $headers Array of headers to be set.
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Initialize and execute cURL.
     */
    private function init()
    {
        // Initialize cURL session
        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set cURL options for JSON payload
        if (!empty($this->data))
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->data));
        }

        // Set headers if present
        if (!empty($this->headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        // Execute cURL session
        $this->response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $this->response = curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);
    }

    /**
     * Decode and format the JSON properly.
     *
     * @return array|null JSON Response from the cURL session or null on error.
     */
    public function response(): ?array
    {
        $this->init();

        // Check for errors and decode JSON response
        if ($this->response === false) {
            return null; // Returning null indicates an error
        } else {
            $jsonResponse = json_decode($this->response, true);

            // Check if JSON decoding was successful
            if ($jsonResponse === null) {
                return null; // Return null on decoding error
            } else {
                return $jsonResponse;
            }
        }
    }

    /**
     * Maintain array of endpoints.
     *
     */
    public function add($endpoint)
    {
        $this->endpoints[] = $endpoint;
    }

    /**
     * Open the endpoints.
     *
     */
    public function open(): void
    {
        global $conn;

        foreach ($this->endpoints as $endpoint)
        {
            if (str_contains($endpoint, "."))
            {
              $filePath = "app/tpl/pages/{$endpoint}";
            }
            else {
              $filePath = "app/tpl/pages/{$endpoint}.php";
            }
            if (file_exists($filePath)) {
                include_once($filePath);
            }
        }
    }
}
?>
