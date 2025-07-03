<?php

namespace App\Core\Http\Response;

class Response
{
    /**
     * Send JSON success response
     */
    public static function success(array $data = [], string $message = '', int $statusCode = 200): void
    {
        http_response_code($statusCode);
        
        $response = ['success' => true];
        
        if (!empty($data)) {
            $response['data'] = $data;
        }
        
        if (!empty($message)) {
            $response['message'] = $message;
        }
        
        echo json_encode($response);
    }
    
    /**
     * Send JSON error response
     */
    public static function error(string $error, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
    }
    
    /**
     * Send JSON response with custom data
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }
}