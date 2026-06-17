<?php

namespace App\Http\Controllers;

use App\Models\SSO\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class DocsApiController extends Controller
{
  public function index()
  {
    return view('docs.login');
  }

  public function login(Request $request)
  {
    try {
      $token = $request->input('token');
      if ($token) {
        $data_user = JWTAuth::setToken($token)->getPayload()->get('user');
        $user = User::where($data_user)->first();
        if ($user) {
          session(['user' => $user]);
          return redirect(config('app.env') == 'local' ? '/docs/api' : '/ads/docs/api');
        }
      }
    } catch (\Throwable $th) {
      return view('docs.login', ['error' => 'User not found or token is invalid.', 'remove_token' => true]);
    }
    return redirect(env('VITE_SSO_URL', '/'));
  }

  public function logout(Request $request)
  {
    $request->session()->forget('user');
    return redirect(env('VITE_SSO_URL', '/'));
  }
}
