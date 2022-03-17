<?php


namespace App\Models\ConsignmentNotes;


use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConsignmentNote extends Model
{
    use HasFactory;

    protected $table = 'consignment_notes';

    protected $fillable = [
        'number',
        'date',
        'order_id',
        'responsible_full_name',
        'responsible_phone',
        'comment',
    ];

    public function order(): hasOne
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }
}
