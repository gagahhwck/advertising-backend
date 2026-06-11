<?php

use App\Models\SSO\Role;
use App\Models\SSO\User;
use App\Models\SSO\Permission;
use App\Models\SSO\ModelHasRole;
use App\Models\SSO\RoleHasPermission;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

if (!function_exists('createRoleWithPermissions')) {
  function createRoleWithPermissions(string $roleName, array $permissions)
  {
    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
    RoleHasPermission::where('role_id', $role->id)->delete(); // Clear existing permissions
    $data_role_permissions = [];
    foreach ($permissions as $permissionName) {
      $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'api']);
      $data_role_permissions[] = ['role_id' => $role->id, 'permission_id' => $permission->id];
    }
    RoleHasPermission::insert(array_unique($data_role_permissions, SORT_REGULAR)); // Insert new permissions
    return $role;
  }
}

if (!function_exists('syncUserToNewRole')) {
  function syncUserToNewRole($old_role, $id_new_role)
  {
    $users = User::whereHas('roles', fn($q) => $q->whereName($old_role))->get();
    $users->each(function ($user) use ($id_new_role) {
      ModelHasRole::firstOrCreate([
        'model_type'    => User::class,
        'model_id'      => $user->id,
        'role_id'       => $id_new_role
      ]);
    });
  }

  if (!function_exists('send_email')) {
    function send_email($data = [])
    {
        if (!isset($data['to'])) {
            throw new HttpException(422, 'Email address is required');
        }
        if (!isset($data['subject'])) {
            throw new HttpException(422, 'Subject is required');
        }
        if (!isset($data['bodyText']) && !isset($data['bodyHtml'])) {
            throw new HttpException(422, 'Body Text or Body HTML is required');
        }
        if (!isset($data['from'])) {
            $data['from'] = [
                'name'  => config('mail.from.name'),
                'email' => config('mail.from.address')
            ];
        }
        $response = Http::withoutVerifying()
                        ->withToken(config('mail.mailers.smtp.password'))
                        ->timeout(300)
                        ->post(config('setting.mail_api_url'), $data);
        return $response;
    }
}
}
