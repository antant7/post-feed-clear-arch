<?php

namespace App\Repositories;

interface IPostRepository
{
    /**
     * Get feed posts for user
     * 
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $sortOrder
     * @return array
     */
    public function getFeedPosts($userId, $limit, $offset, string $orderBy, string $sortOrder): array;

    /**
     * Update view count for post
     * 
     * @param int $postId
     * @return void
     */
    public function updViewCount($postId): void;
}