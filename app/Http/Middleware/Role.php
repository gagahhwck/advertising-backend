<?php

namespace App\Http\Middleware;

use App\Models\SSO\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class Role
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle($request, Closure $next, $role)
  {
    if (Auth::check() && $this->hasRoleTo($role)) {
      return $next($request);
    }
    return abort(403);
  }

  private function hasRoleTo($role)
  {
    $roles = is_array($role) ? $role : explode('|', $role);
    $user = User::where('id', Auth::id())->first();
    return $user->whereHas('roles', function ($query) use ($roles) {
      $query->whereIn('name', $roles);
    })->exists();
  }
}
