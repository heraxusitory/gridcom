<?php


namespace App\Traits;


trait HasSyncStack
{
    public function entity()
    {
        return $this->hasOne(self::$model_class, 'id', 'entity_id');
    }
}
