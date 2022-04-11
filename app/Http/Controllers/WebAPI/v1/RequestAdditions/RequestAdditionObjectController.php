<?php

namespace App\Http\Controllers\WebAPI\v1\RequestAdditions;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestAdditionObjects\CreateRAObjectFormRequest;
use App\Http\Requests\RequestAdditionObjects\UpdateRAObjectFormRequest;
use App\Models\Orders\LKK\Order;
use App\Models\References\Organization;
use App\Models\RequestAdditions\RequestAdditionObject;
use App\Services\RequestAdditionObjects\CreateRequestAdditionObjectService;
use App\Services\RequestAdditionObjects\UpdateRequestAdditionObjectService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class RequestAdditionObjectController extends Controller
{
    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        try {
            $ra_objects = RequestAdditionObject::query()
                ->with(['work_agreement', 'provider_contract', 'organization', 'object']);

            if ($this->user->isProvider()) {
                $ra_objects->where('contr_agent_id', $this->user->contr_agent_id())->whereNotNull('provider_contract_id');
            } elseif ($this->user->isContractor()) {
                $ra_objects->where('contr_agent_id', $this->user->contr_agent_id()->whereNotNull('work_agreement_id'));
            }
            $ra_objects = $ra_objects->paginate();

            return response()->json($ra_objects);
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

    public function get(Request $request, $ra_object_id)
    {
        try {
            $ra_object = RequestAdditionObject::query()
                ->with(['work_agreement', 'provider_contract', 'organization', 'object']);

            if ($this->user->isProvider()) {
                $ra_object->where('contr_agent_id', $this->user->contr_agent_id())->whereNotNull('provider_contract_id');
            } elseif ($this->user->isContractor()) {
                $ra_object->where('contr_agent_id', $this->user->contr_agent_id()->whereNotNull('work_agreement_id'));
            }
            $ra_object = $ra_object->findOrFail($ra_object_id);
            return response()->json(['data' => $ra_object]);
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

    public function create(CreateRAObjectFormRequest $request)
    {
        try {
            return response()->json(['data' => (new CreateRequestAdditionObjectService($request->all()))->run()]);
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

    public function update(UpdateRAObjectFormRequest $request, $ra_object_id)
    {
        try {
            /** @var RequestAdditionObject $ra_object */
            $ra_object = RequestAdditionObject::query()->findOrFail($ra_object_id);
            throw_if($ra_object->organization_status !== RequestAdditionObject::ORGANIZATION_STATUS_DRAFT,
                new BadRequestException('Невозможно редактировать запрос на добавление НСИ. Запрос на добавление НСИ уже отправлен на согласование', 400));
            return response()->json(['data' => (new UpdateRequestAdditionObjectService($request->all(), $ra_object))->run()]);

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

    public function delete(Request $request, $ra_object_id)
    {
        try {
            $ra_object = RequestAdditionObject::query()->findOrFail($ra_object_id);
            $ra_object->delete();
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
        //TODO: костыль переделать , когда будут роли и юзеры
        Validator::validate($request->all(), [
            'contr_agent_type' => ['required', Rule::in(['provider', 'contractor']),
            ]
        ]);

        if ($request->contr_agent_type === 'contractor') {
            Validator::validate($request->all(), [
                'contract_id' => 'required|exists:work_agreements,id'
            ]);
            $organization_ids = Order::query()
                ->with('customer')
                ->whereRelation('customer', 'work_agreement_id', $request->contract_id)
                ->get()->pluck('customer.organization_id')->unique();

            return Organization::query()->whereIn('id', $organization_ids)->get();

        }
        if ($request->contr_agent_type === 'provider') {
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
}
