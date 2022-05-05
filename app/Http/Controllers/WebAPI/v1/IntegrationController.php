<?php


namespace App\Http\Controllers\WebAPI\v1;


use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class IntegrationController extends Controller
{

    public function index(Request $request)
    {
        return response(['data' => IntegrationUser::all()]);
    }

    public function getIntegration(Request $request, $user_id)
    {
        $user = IntegrationUser::query()->findOrFail($user_id);
        return response()->json($user);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:integration_users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['nullable', Rule::in(['provider', 'contractor'])],
            'contr_agent_id' => 'required_with:role|integer|exists:contr_agents,id',
        ]);

        $user = IntegrationUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => optional($request)->role,
            'contr_agent_id' => optional($request)->contr_agent_id,
        ]);

        return response()->json($user);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

            }
        );

        return response()->json();
    }

    public function delete(Request $request, $user_id)
    {
        IntegrationUser::destroy($request->user_id);

        return response(null, 204);
    }
}
