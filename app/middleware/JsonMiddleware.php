<?php

class JsonMiddleware
{
    public static function handle()
    {
        header("Content-Type: application/json");

        $method = $_SERVER['REQUEST_METHOD'];

        $allowedMethods = ['POST', 'PUT', 'PATCH'];

        if (in_array($method, $allowedMethods)) {

            if (
                !isset($_SERVER['CONTENT_TYPE']) ||
                strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false
            ) {
                Response::json(false, "Content-Type must be application/json", [], 400);
            }

            $input = file_get_contents("php://input");

            if (!empty($input)) {
                $decoded = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Response::json(false, "Invalid JSON format", [], 400);
            }
                 $_REQUEST['body'] = $decoded;
            }       
            else {
                 $_REQUEST['body'] = [];
            }
        }
        
    }
}