<?php
namespace Handlers;

class EndpointHandler
{
    protected $_endpoint;

    public function __construct()
    {
        $this->_endpoint = '/' . basename(__FILE__, '.php');
    }

    protected function validateEndpoint()
    {
        if ($_SERVER['REQUEST_URI'] !== $this->_endpoint) {
            http_response_code(404); // Optional: send a 404 response if the endpoint is incorrect
            echo json_encode(['error' => 'No Rows Found']);
            exit('Invalid endpoint');
        }
    }
}
?>
