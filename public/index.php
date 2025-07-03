<?php

require_once '../autoload.php';

use App\Infrastructure\Database;
use App\Controllers\PostController;
use App\Services\PostService;
use App\Repositories\PostRepository;
use App\Repositories\UserPostViewRepository;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $database = new Database();

    $postRepository = new PostRepository($database);
    $userPostViewRepository = new UserPostViewRepository($database);
    $postService = new PostService($postRepository, $userPostViewRepository);
    $postController = new PostController($postService);
    
    $requestUri = $_SERVER['REQUEST_URI'];
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    
    // Remove query string from URI
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Route handling
    switch ($path) {
        case '/api/posts':
            if ($requestMethod === 'GET') {
                $postController->getFeed();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case '/api/posts/view':
            if ($requestMethod === 'POST') {
                $postController->markAsViewed();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}