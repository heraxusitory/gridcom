<?php


namespace App\Http\Controllers\WebAPI\v1;


use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function index(Request $request)
    {
        $notifications = [];
        if ($this->user->hasCompany())
            $notifications = Notification::query()
                ->where('contr_agent_id', $this->user->contr_agent()->uuid)
                ->orderByDesc('created_at')
                ->get();
        return response()->json(['data' => $notifications]);
    }

    public function destroy(Request $request, $notification_id)
    {
        $notification = Notification::where('contr_agent_id', $this->user->contr_agent()->uuid)->find($notification_id);
        if ($notification) {
            $parent = $notification->notificationable;
            $parent->notifications()->delete();
        }
        return response('', 204);
    }
}
