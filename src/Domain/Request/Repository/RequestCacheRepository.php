<?php

namespace App\Domain\Request\Repository;

use PDO;

class RequestCacheRepository {
    private $connection;

    public function __construct(PDO $connection) {
        $this->connection = $connection;
    }
}
