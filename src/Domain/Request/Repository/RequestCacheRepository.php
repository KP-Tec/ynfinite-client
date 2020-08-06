<?php

namespace App\Domain\Request\Repository;

use App\Utils\Cache\StaticPageCache;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Builder;

class RequestCacheRepository {
    private $connection;

    public function __construct(Manager $connection) {
        $this->connection = $connection;
    }

    public function createCache($filename, $cacheKey) {
        $values = [
            'cache_key' => $cacheKey,
            'filename' => $filename,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];

        $cache = $this->connection->table('static_page_cache')->where("cache_key", "=", $cacheKey)->where("filename", "=", $filename)->first();

        if($cache->id) {
            return (int) $this->connection->table('static_page_cache')->where("cache_key", "=", $cacheKey)->where("filename", "=", $filename)->update($values);
        }
        else {
            return (int) $this->connection->table('static_page_cache')->insert($values);
        }

        
    }

    public function invalidateCache($cacheKey) {
        $cachedPages = $this->connection->table('static_page_cache')->where('cache_key', '=', $cacheKey)->get();
        
        $deletedItems = 0;
        foreach($cachedPages as $cachedPage) {
            $pageDeleted = StaticPageCache::invalidateCache($cachedPage->filename);
            
            if($pageDeleted) {
                $success = $this->connection->table('static_page_cache')->where('id', '=', $cachedPage->id)->delete();
                if($success) {
                    $deletedItems++;
                }
            }

        }
        
        return $deletedItems;
    }
}
