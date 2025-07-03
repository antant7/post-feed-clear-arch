<?php

namespace App\Infrastructure;

use PDO;

interface IDatabase {
    public function getConnection(): PDO;
}