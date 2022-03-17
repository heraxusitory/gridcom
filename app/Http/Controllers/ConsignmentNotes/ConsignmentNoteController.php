<?php


namespace App\Http\Controllers\ConsignmentNotes;


use App\Http\Controllers\Controller;
use App\Models\ConsignmentNotes\ConsignmentNote;
use App\Services\ConsignmentNotes\CreateConsignmentService;
use App\Services\ConsignmentNotes\GetConsignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConsignmentNoteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $consignments = (new GetConsignmentService($request->all()))->run();
            return response()->json(['data' => $consignments]);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function create(Request $request)
    {
        Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'responsible_full_name' => 'required|string|max:255',
            'responsible_phone' => 'required|string|max:255',
            'comment' => 'required|text',
            'positions' => 'required|array',
            'positions.*' => 'required',
            'positions.*.nomenclature_id' => 'required|integer|exists:nomenclature,id',
            'positions.*.unit_id' => 'required|integer|exists:nomenclature_unit,id',
            'positions.*.count' => 'required|numeric',
            'positions.*.price_without_vat' => 'required|numeric',
            //TODO отрефакторить ставку НДС
            'positions.*.vat_rate' => ['required', Rule::in([0, 13, 20, 30, 40])],
        ]);
        try {
            $consignment = (new CreateConsignmentService($request->all()))->run();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }
}
