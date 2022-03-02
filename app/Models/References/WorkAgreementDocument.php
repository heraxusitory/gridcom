<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class WorkAgreementDocument extends Model
{
    protected $table = 'work_agreements';

    protected $fillable = [
        'number',
        'date',
    ];
}
