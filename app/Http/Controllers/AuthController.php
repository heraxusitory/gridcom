<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\DocBlock\Tags\Author;

class AuthController extends Controller
{
//    /**
//     * Create a new AuthController instance.
//     *
//     * @return void
//     */
//    public function __construct()
//    {
//        $this->middleware('auth:webapi', ['except' => ['login']]);
//    }
//
//    public function getLoginUrl()
//    {
//        $url = 'http://localhost:8080/realms/demo/protocol/openid-connect/auth?scope=openid&response_type=code&client_id=client-app&redirect_uri=http://google.com&state=' . bin2hex(random_bytes(16));;
////        $params = [
////            'scope' => 'openid',
////            'response_type' => 'code',
////            'client_id' => 'client-app',
////            'redirect_uri' => $this->callbackUrl,
//////            'state' => $this->getState(),
////        ];
//
//        return $url;
//    }

//    public function getAccessToken($credentials)
//    {
//        $url = 'http://localhost:8080/realms/demo/protocol/openid-connect/token';
//        $params = [
////            'code' => $code,
//            'client_id' => 'client-app',
//            'grant_type' => 'password',
////            'redirect_uri' => $this->callbackUrl,
//        ];
//
////        if (! empty($this->clientSecret)) {
//        $params['client_secret'] = '3mZWjlKAlBDhg75qrWC5t6CSis2Eb2Oq';
////        }
//        $params = array_merge($params, $credentials);
//
//        $token = [];
//
////        try {
//        $response = (new Client())->request('POST', $url, ['form_params' => $params]);
//
//        if ($response->getStatusCode() === 200) {
//            $token = $response->getBody()->getContents();
//            $token = json_decode($token, true);
//        }
////        } catch (GuzzleException $e) {
//////            if ($e->getCode() === 401) {
//////                return response()->json(['error' => 'Unauthorized'], 401);
//////            }
////            return response()->json(['message' => $e->getMessage()], $e->getCode());
////        }
//
//        return $token;
//    }
//
//    /**
//     * Get a JWT via given credentials.
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function login()
//    {
////        dd(Auth::hasRole('client-app','provider'));
////        return redirect($this->getLoginUrl());
//////        dd(auth('webapi')->user());
//        $credentials = request(['username', 'password']);
//        try {
//            $token = $this->getAccessToken($credentials);
//        } catch (GuzzleException $e) {
//            if ($e->getCode() === 401) {
//                return response()->json(['error' => 'Unauthorized'], 401);
//            }
//            return response()->json(['message' => $e->getMessage()], $e->getCode());
//        }
////        dd(auth('webapi')->token());
////        if (!$token = auth('webapi')->attempt($credentials)) {
////            return response()->json(['error' => 'Unauthorized'], 401);
////        }
////;     dd(auth('webapi')->setUser());
////        \Illuminate\Support\Facades\Cookie::queue('refresh_token', 12323);
//        return $this->respondWithToken($token);
//    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth('webapi')->user();
        $user->contr_agent = $user->contr_agent();
        unset($user->token, $user->company_IN);
        return response()->json($user);
    }

//    /**
//     * Log the user out (Invalidate the token).
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function logout()
//    {
//        auth()->logout();
//
//        return response()->json(['message' => 'Successfully logged out']);
//    }
//
//    /**
//     * Refresh a token.
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function refresh()
//    {
//        return $this->respondWithToken(auth('webapi')->refresh());
//    }
//
//    /**
//     * Get the token array structure.
//     *
//     * @param string $token
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    protected function respondWithToken($token)
//    {
////        dd($token);
//        return response()->json([
//            'access_token' => $token['access_token'],
//            'token_type' => 'Bearer',
////            'expires_in' => auth('webapi')->factory()->getTTL() * 60
//        ])->cookie('refresh_token', $token['refresh_token']);
//    }
}
