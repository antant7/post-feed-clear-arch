<?php

namespace App\Repositories;

use App\Infrastructure\IDatabase;
use PDO;

class UserPostViewRepository implements IUserPostViewRepository {
    private PDO $pdo;
    
    public function __construct(IDatabase $database) {
        $this->pdo = $database->getConnection();
    }
    
    public function markAsViewed($userId, $postId): bool
    {
        // 1) в запросе проверяется существование поста в таблице постов
        // 2) защита от дублей за счет unique составного индекса (user_id, post_id)
        $sql = "
        INSERT INTO user_post_views (user_id, post_id) 
        SELECT :user_id, :post_id WHERE EXISTS (
            SELECT 1 FROM posts WHERE id = :post_id
        )
        ON CONFLICT (user_id, post_id) DO NOTHING;
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
}