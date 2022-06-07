<?php

namespace App\Http\Controllers\WebAPI\v1\RequestAdditions;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestAdditionNomenclatures\CreateRANomenclatureFormRequest;
use App\Http\Requests\RequestAdditionNomenclatures\UpdateRANomenclatureFormRequest;
use App\Models\Orders\Order;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\References\Organization;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\User;
use App\Services\RequestAdditionNomenclatures\CreateRequestAdditionNomenclatureService;
use App\Services\RequestAdditionNomenclatures\UpdateRequestAdditionNomenclatureService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class RequestAdditionNomenclatureController extends Controller
{

    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function index(Request $request)
    {
        try {
            $ra_nomenclatures = RequestAdditionNomenclature::query()
                ->with(['work_agreement', 'provider_contract', 'organization', 'nomenclature']);
            if ($this->user->isProvider()) {
                $ra_nomenclatures->where('contr_agent_id', $this->user->contr_agent_id())->whereNotNull('provider_contract_id');
            } elseif ($this->user->isContractor()) {
                $ra_nomenclatures->where('contr_agent_id', $this->user->contr_agent_id())->whereNotNull('work_agreement_id');
            }
            $ra_nomenclatures = $ra_nomenclatures->get();
            return response()->json($ra_nomenclatures);
        } catch
        (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function get(Request $request, $ra_nomenclature_id)
    {
        try {
            $ra_nomenclature = RequestAdditionNomenclature::query()
                ->with(['work_agreement', 'provider_contract', 'organization', 'nomenclature']);

            if ($this->user->isProvider()) {
                $ra_nomenclature->where('contr_agent_id', $this->user->contr_agent_id())->whereNotNull('provider_contract_id');
            } elseif ($this->user->isContractor()) {
                $ra_nomenclature->where('contr_agent_id', $this->user->contr_agent_id()->whereNotNull('work_agreement_id'));
            }
            $ra_nomenclature = $ra_nomenclature->findOrFail($ra_nomenclature_id);
            return response()->json(['data' => $ra_nomenclature]);
        } catch
        (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function create(CreateRANomenclatureFormRequest $request)
    {
        try {
            return response()->json(['data' => (new CreateRequestAdditionNomenclatureService($request->all()))->run()]);
        } catch
        (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function update(UpdateRANomenclatureFormRequest $request, $ra_nomenclature_id)
    {
        try {
            /** @var RequestAdditionNomenclature $ra_nomenclature */
            $ra_nomenclature = RequestAdditionNomenclature::query()->findOrFail($ra_nomenclature_id);
            throw_if($ra_nomenclature->organization_status !== RequestAdditionNomenclature::ORGANIZATION_STATUS_DRAFT,
                new BadRequestException('Невозможно редактировать запрос на добавление НСИ. Запрос на добавление НСИ уже отправлен на согласование', 400));
            return response()->json(['data' => (new UpdateRequestAdditionNomenclatureService($request->all(), $ra_nomenclature))->run()]);

        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function delete(Request $request, $ra_nomenclature_id)
    {
        try {
            $ra_nomenclature = RequestAdditionNomenclature::query()->findOrFail($ra_nomenclature_id);
            $ra_nomenclature->delete();
            return response()->json('', 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function getOrganizations(Request $request)
    {
//        //TODO: костыль переделать , когда будут роли и юзеры
//        Validator::validate($request->all(), [
//            'contr_agent_type' => ['required', Rule::in(['provider', 'contractor']),
//            ]
//        ]);

        if ($this->user->isContractor()) {
            Validator::validate($request->all(), [
                'contract_id' => 'required|exists:work_agreements,id'
            ]);
            $organization_ids = Order::query()
                ->with('customer')
                ->whereRelation('customer', 'work_agreement_id', $request->contract_id)
                ->get()->pluck('customer.organization_id')->unique();

            return Organization::query()->whereIn('id', $organization_ids)->get();

        }
        if ($this->user->isProvider()) {
            Validator::validate($request->all(), [
                'contract_id' => 'required|exists:provider_contracts,id'
            ]);
            $organization_ids = Order::query()
                ->with('customer')
                ->whereRelation('provider', 'provider_contract_id', $request->contract_id)
                ->get()
                ->pluck('customer.organization_id')->unique();

            return Organization::query()->whereIn('id', $organization_ids)->get();
        }
    }

    public function downloadFile(Request $request, $ra_nomenclature_id)
    {
        /** @var User $user */
        $user = auth('webapi')->user();
        try {
            $ra_nomenclature = RequestAdditionNomenclature::query()->where('contr_agent_id', $user->contr_agent_id())->findOrFail($ra_nomenclature_id);
            if (Storage::exists($ra_nomenclature->file_url)) {
                return response()->download(Storage::path($ra_nomenclature->file_url));
            }
            return response('', 204);
        } catch
        (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
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
