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

    public function invalidateAllCache() {
        $cachedPages = $this->connection->table("static_page_cache")->get();
        return $this->deletePages($cachedPages);
    }

    public function invalidateCache($cacheKeys) {
        if(!is_array($cacheKeys)) $cacheKeys = array($cacheKeys);
        $cachedPages = $this->connection->table('static_page_cache')->whereIn('cache_key', $cacheKeys)->get();

        return $this->deletePages($cachedPages);
    }

    private function deletePages($cachedPages) {
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
