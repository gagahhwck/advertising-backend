<?php

namespace App\Http\Middleware;

use App\Models\SSO\User;
use Closure;
use Illuminate\Http\Request;

class RestrictedDocsAccess
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next)
  {
    if (config('app.env') == 'local') {
      return $next($request);
    }

    if (session()->has('user')) {
      $user = session('user');
      if (User::find($user->id)) {
        return $next($request);
      } else {
        session()->forget('user');
        return redirect('ticketing/docs/api/login');
      }
    } else {
      return redirect('ticketing/docs/api/login');
    }
  }
}
