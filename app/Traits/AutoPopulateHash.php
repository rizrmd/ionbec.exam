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
        if ($this->hasHashColumn() && empty($this->getAttributeFromArray('hash'))) {
            $hash = $this->idToHash($this->getKey());
            
            // Update database without triggering events
            $this->newQueryWithoutScopes()->where($this->getKeyName(), $this->getKey())->update(['hash' => $hash]);
            
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