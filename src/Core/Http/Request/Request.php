<?php

namespace App\Core\Http\Request;

use Exception;

class Request
{
    /**
     * Get and decode JSON data from PHP input stream
     * 
     * @return array
     * @throws Exception
     */
    public static function getJsonData(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON format');
        }
        
        return $data ?? [];
    }
}