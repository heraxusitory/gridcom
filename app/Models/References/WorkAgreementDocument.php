<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class WorkAgreementDocument extends Model
{
    protected $table = 'work_agreements';

    protected $fillable = [
        'uuid',
        'number',
        'date',
        'is_visible_to_client',
    ];

    public function getDateAttribute($value)
    {
        return (new Carbon($value))->format('Y-m-d');
    }
}
