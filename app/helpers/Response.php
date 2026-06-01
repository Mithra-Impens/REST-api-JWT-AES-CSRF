<?php

class Response
{
    public static function json($status, $message, $data = [], $code = 200)
    {
        http_response_code($code);

        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);

        exit;
    }
}