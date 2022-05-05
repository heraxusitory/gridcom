<?php


namespace App\Http\Controllers;


class AuthController extends Controller
{
    public function me()
    {
        $user = auth('webapi')->user();
        $user->contr_agent = $user->contr_agent();
        unset($user->token, $user->company_IN);
        return response()->json($user);
    }
}
