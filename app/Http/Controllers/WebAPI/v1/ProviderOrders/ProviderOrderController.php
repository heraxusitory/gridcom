<?php


namespace App\Http\Controllers\WebAPI\v1\ProviderOrders;


use App\Http\Controllers\Controller;
use App\Models\ProviderOrders\Corrections\RequirementCorrection;
use App\Models\ProviderOrders\Corrections\RequirementCorrectionPosition;
use App\Models\ProviderOrders\ProviderOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ProviderOrderController extends Controller
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function index(Request $request)
    {
        try {
            $provider_orders = ProviderOrder::query()->with([
                'base_positions',
                'actual_positions',
                'requirement_corrections.positions',
                'order_corrections.positions',
            ]);
            if ($this->user->isProvider()) {
                $provider_orders->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $provider_orders = $provider_orders->get();
            return response()->json($provider_orders);
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

    public function getOrder(Request $request, $provider_order_id)
    {
        try {
            $provider_order = ProviderOrder::query();
            if ($this->user->isProvider()) {
                $provider_order->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $provider_order = $provider_order->findOrFail($provider_order_id);
            return response()->json(['data' => $provider_order]);
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

    public function getRequirementCorrection(Request $request, $provider_order_id, $requirement_correction_id)
    {
        try {
            /** @var ProviderOrder $provider_order */
            $provider_order = ProviderOrder::query();
            if ($this->user->isProvider()) {
                $provider_order->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $provider_order = $provider_order->findOrFail($provider_order_id);

            $requirement_correction = $provider_order->requirement_corrections()->with(['positions.nomenclature', 'provider_order'])->findOrFail($requirement_correction_id);
            return response()->json(['data' => $requirement_correction]);
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

    public function getOrderCorrection(Request $request, $provider_order_id, $order_correction_id)
    {
        try {
            /** @var ProviderOrder $provider_order */
            $provider_order = ProviderOrder::query();
            if ($this->user->isProvider()) {
                $provider_order->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $provider_order = $provider_order->findOrFail($provider_order_id);

            $order_correction = $provider_order->order_corrections()->with(['positions.nomenclature', 'provider_order'])->findOrFail($order_correction_id);
            return response()->json(['data' => $order_correction]);
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

    public function reject(Request $request, $provider_order_id, $requirement_correction_id)
    {
        Validator::make($request->all(), [
            'comment' => 'required|string',
        ])->validate();

        try {
            /** @var ProviderOrder $order */
            $order = ProviderOrder::query();
            if ($this->user->isProvider()) {
                $order->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $order = $order->findOrFail($provider_order_id);
            /** @var RequirementCorrection $requirement_correction */
            $requirement_correction = $order->requirement_corrections()->findOrFail($requirement_correction_id);

            throw_if($requirement_correction->provider_status === RequirementCorrection::PROVIDER_STATUS_AGREED() ||
                $requirement_correction->provider_status === RequirementCorrection::PROVIDER_STATUS_PARTIALLY_AGREED()
                , new BadRequestException('Корректировка потребности уже согласована или частично согласована поставщиком', 400));
            throw_if($requirement_correction->provider_status === RequirementCorrection::PROVIDER_STATUS_NOT_AGREED()
                , new BadRequestException('Корректировка потребности уже отказана поставщиком', 400));

            $requirement_correction->positions()->update(['status' => RequirementCorrectionPosition::STATUS_REJECTED()]);
            $requirement_correction->provider_status = RequirementCorrection::PROVIDER_STATUS_NOT_AGREED();
//            $order_provider = $requirement_correction->provider()->firstOrFail();
//            $order_provider->rejected_comment = $request->comment;
//            $order_provider->save();
            $requirement_correction->save();
            return $requirement_correction;
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

    public function approve(Request $request, $provider_order_id, $requirement_correction_id)
    {
        Validator::make($request->all(), [
            'comment' => 'required|string',
        ])->validate();

        try {
            $order = ProviderOrder::query();
            if ($this->user->isProvider()) {
                $order->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $order = $order->findOrFail($provider_order_id);
            $requirement_correction = $order->requirement_corrections()->findOrFail($requirement_correction_id);


            throw_if($requirement_correction->provider_status === RequirementCorrection::PROVIDER_STATUS_AGREED() ||
                $requirement_correction->provider_status === RequirementCorrection::PROVIDER_STATUS_PARTIALLY_AGREED()
                , new BadRequestException('Корректировка потребности уже согласована или частично согласована поставщиком', 400));
            throw_if($requirement_correction->provider_status === RequirementCorrection::PROVIDER_STATUS_NOT_AGREED()
                , new BadRequestException('Корректировка потребности уже отказана поставщиком', 400));

            $requirement_correction->positions()
                ->where('status', '!=', RequirementCorrectionPosition::STATUS_REJECTED())
                ->update(['status' => RequirementCorrectionPosition::STATUS_AGREED()]);

            if ($requirement_correction->positions()->where('status', RequirementCorrectionPosition::STATUS_REJECTED())->count())
                $requirement_correction->provider_status = RequirementCorrection::PROVIDER_STATUS_PARTIALLY_AGREED();
            else
                $requirement_correction->provider_status = RequirementCorrection::PROVIDER_STATUS_AGREED();
//            $order_provider = $order->provider()->firstOrFail();
//            $order_provider->agreed_comment = $request->comment;
//            $order_provider->save();
            $requirement_correction->save();

            return $requirement_correction;
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

    public function rejectPositions(Request $request, $provider_order_id, $requirement_correction_id)
    {
        try {
            /** @var ProviderOrder $order */
            $order = ProviderOrder::query();
            if ($this->user->isProvider()) {
                $order->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $order = $order->findOrFail($provider_order_id);
            $requirement_correction = $order->requirement_corrections()->findOrFail($requirement_correction_id);
            $requirement_correction_positions_ids = $requirement_correction->positions()->pluck('id');
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
        Validator::make($request->all(), [
            'rejected_position_ids' => 'required|array',
            'rejected_position_ids.*' => ['required', 'integer', Rule::in($requirement_correction_positions_ids)],
            'comment' => 'required|string',
        ])->validate();

        try {
            throw_if(count($requirement_correction_positions_ids) === count($request->rejected_position_ids),
                new BadRequestException('Невозможно отказать сразу по всем выбранным позициям', 400));

            throw_if($requirement_correction->provider_status === RequirementCorrection::PROVIDER_STATUS_AGREED() ||
                $requirement_correction->provider_status === RequirementCorrection::PROVIDER_STATUS_PARTIALLY_AGREED()
                , new BadRequestException('Корректировка потребности уже согласована или частично согласована поставщиком', 400));
            throw_if($requirement_correction->provider_status === RequirementCorrection::PROVIDER_STATUS_NOT_AGREED()
                , new BadRequestException('Корректировка потребности уже отказана поставщиком', 400));

            throw_if($requirement_correction->positions()->where('status', RequirementCorrectionPosition::STATUS_REJECTED())->exists(), new BadRequestException('Отказанные позиции уже существуют', 400));
            $order->positions()->whereIn('id', $request->rejected_position_ids)->update(['status' => RequirementCorrectionPosition::STATUS_REJECTED()]);
            return $requirement_correction;
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
