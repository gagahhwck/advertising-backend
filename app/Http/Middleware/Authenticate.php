<?php

namespace App\Http\Middleware;

use App\Models\SSO\User;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next)
  {
    Auth::forgetGuards();

    if ($token = JWTAuth::getToken()) {
      // Paksa pakai guard ini
      $data_user = JWTAuth::getPayload($token)->get('user');
      $user = User::where($data_user)->first();
      if ($user) {
        Auth::setUser($user);
        return $next($request);
      }
    }

    return response()->json([
      'message' => 'Unauthenticated',
    ], 401);
  }
}
