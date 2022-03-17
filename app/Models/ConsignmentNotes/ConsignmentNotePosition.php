<?php


namespace App\Models\ConsignmentNotes;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentNotePosition extends Model
{
    use HasFactory;

    protected $table = 'consignment_note_positions';

    protected $fillable = [
        'consignment_note_id',
        'nomenclature_id',
        'unit_id',
        'count',
        'price_without_vat',
        'amount_without_vat',
        'vat_rate',
        "amount_with_vat",
        'country',
        'cargo_custom_declaration',
        'declaration',
    ];
}
