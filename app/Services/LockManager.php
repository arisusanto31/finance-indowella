<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

class LockManager
{
    protected $locks = [];

    public function acquire($key, $ttl = 30, $wait = 10)
    {
        if (!isset($this->locks[$key])) {
            $lock = Cache::lock($key, $ttl);
            $lock->block($wait);
            $this->locks[$key] = $lock;
        }

        return $this->locks[$key];
    }

    public function releaseAll()
    {
        foreach ($this->locks as $lock) {
            optional($lock)->release();
        }
    }
}
