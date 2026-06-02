<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

class LockManager
{
    protected $locks = [];
    protected $allCodeGroups = [];
    protected $modeNoRecalculate=false;
    protected $journals=[];

    public function acquire($key, $ttl = 30, $wait = 10)
    {
        // $bookID= bookID();
        // $key=$bookID.'_'.$key;
        // if (!isset($this->locks[$key])) {
        //     $lock = Cache::lock($key, $ttl);
        //     $lock->block($wait);
        //     $this->locks[$key] = $lock;
        //     info('make lock '.$key);
        // }

        // return $this->locks[$key];
    }
  

    public function addCodeGroup($codeGroup){
        if(!in_array($codeGroup,$this->allCodeGroups)){
            $this->allCodeGroups[]=$codeGroup;
        }
    }

    public function addJournal($journal){
        // $this->journals[]=$journal;
    }
    public function getAllJournals(){
        return $this->journals;
    }

    public function setModeNoRecalculate($modeNoRecalculate){
        $this->modeNoRecalculate=$modeNoRecalculate;
    }

    public function getModeNoRecalculate(){
        return $this->modeNoRecalculate;
    }

    public function releaseAll()
    {
        foreach ($this->locks as  $key =>$lock) {
            optional($lock)->release();
            info('release lock '.$key);
        }
    
    }
}
