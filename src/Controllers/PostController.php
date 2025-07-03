<?php

namespace App\Controllers;

use App\Services\IPostService;
use App\Core\Http\Response\Response;
use App\Core\Http\Request\Request;
use Exception;

class PostController {
    private IPostService $postService;
    
    public function __construct(IPostService $postService) {
        $this->postService = $postService;
    }
    
    public function getFeed(): void
    {
        try {
            $userId = $this->getUserId();
            $limit = (int)($_GET['limit'] ?? 20);
            $offset = (int)($_GET['offset'] ?? 0);
            $sortBy = $_GET['sort_by'] ?? 'hotness';
            $sortOrder = $_GET['sort_order'] ?? 'DESC';
            
            $posts = $this->postService->getFeed($userId, $limit, $offset, $sortBy, $sortOrder);
            
            Response::success([
                'posts' => $posts,
                'count' => count($posts)
            ]);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
    
    public function markAsViewed(): void
    {
        try {
            $data = Request::getJsonData();
            
            $userId = $this->getUserId($data);
            $postId = (int)($data['post_id'] ?? NULL);
            
            if (!isset($postId) || !is_numeric($postId)) {
                throw new Exception('post_id is required and must be numeric');
            }

            $result = $this->postService->markAsViewed($userId, $postId);
            
            if ($result)
                Response::success([], 'Post marked as viewed');
            else
                Response::error( 'Post already marked as viewed');
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
    
    private function getUserId(array $jsonArray = []): int
    {
        $userId = $jsonArray['user_id'] ?? $_GET['user_id'] ?? $_POST['user_id'] ?? null;
        
        if (!$userId || !is_numeric($userId)) {
            throw new Exception('user_id is required and must be numeric');
        }
        
        return (int)$userId;
    }
}