<?php

namespace App\Traits;

trait AutoPopulateHash
{
    /**
     * Boot the auto populate hash trait
     */
    protected static function bootAutoPopulateHash()
    {
        static::retrieved(function ($model) {
            $model->ensureHashIsPopulated();
        });
    }
    
    /**
     * Ensure hash is populated in database if it's empty
     */
    protected function ensureHashIsPopulated()
    {
        // Check if hash column exists and is empty
        if (array_key_exists('hash', $this->getAttributes())
            && $this->hasHashColumn()
            && empty($this->getAttributeFromArray('hash'))) {
            $hash = $this->idToHash($this->getKey());
            
            // Fill only an explicitly selected empty hash. Preserve historical timestamps
            // and never overwrite a hash written concurrently by another request.
            \Illuminate\Support\Facades\DB::table($this->getTable())
                ->where($this->getKeyName(), $this->getKey())
                ->where(function ($query) {
                    $query->whereNull('hash')->orWhere('hash', '');
                })
                ->update(['hash' => $hash]);
            
            // Update the model's attributes
            $this->setAttribute('hash', $hash);
        }
    }
    
    /**
     * Check if the model has a hash column
     */
    protected function hasHashColumn()
    {
        return \Schema::hasColumn($this->getTable(), 'hash');
    }
}