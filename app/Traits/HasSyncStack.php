<?php


namespace App\Traits;


trait HasSyncStack
{
    public function entity()
    {
        return hasOne($this->model, 'entity_id', 'id');
    }
}
