<?php

namespace App\Repositories;

interface IUserPostViewRepository
{
    /**
     * Mark post as viewed by user
     * 
     * @param int $userId
     * @param int $postId
     * @return bool
     */
    public function markAsViewed($userId, $postId): bool;
}