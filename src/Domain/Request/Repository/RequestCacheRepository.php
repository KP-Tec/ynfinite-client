<?php

namespace App\Domain\Request\Repository;

use App\Utils\Cache\StaticPageCache;
use Illuminate\Database\Capsule\Manager;

class RequestCacheRepository {
    private $connection;

    public function __construct(Manager $connection) {
        $this->connection = $connection;
    }

    public function createCache($filename, $cacheKey) {
        $values = [
            'cache_key' => $cacheKey,
            'filename' => $filename,
        ];

        $cache = $this->connection->table('static_page_cache')->where("cache_key", "=", $cacheKey)->first();

        if($cache->id) {
            var_dump("UPDATING");
            return (int) $this->connection->table('static_page_cache')->where("cache_key", "=", $cacheKey)->update($values);
        }
        else {
            var_dump("INSERTING");
            return (int) $this->connection->table('static_page_cache')->insert($values);
        }

        
    }

    public function invalidateCache($cacheKey) {
        $cache = $this->connection->table('static_page_cache')->where('cache_key', '=', $cacheKey)->first();

        $pageDeleted = StaticPageCache::invalidateCache($cache->filename);
        if($pageDeleted) {
            return $this->connection->table('static_page_cache')->where('cache_key', '=', $cacheKey)->delete();
        }
        
        return false;
    }
}
