<?php

namespace App\Models\SSO;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Role extends Model
{
  protected $connection = 'sso';
  protected $table = 'roles';

  protected $fillable = [
    'name',
    'guard_name',
  ];

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logAll()
      ->useLogName('SSO')
      ->setDescriptionForEvent(fn(string $eventName) => "Role has been $eventName")
      ->logOnlyDirty();
  }

  public function model_has_roles()
  {
    return $this->hasMany(ModelHasRole::class, 'role_id', 'id');
  }

  public function role_has_permissions()
  {
    return $this->hasMany(RoleHasPermission::class, 'role_id', 'id');
  }

  public function permissions()
  {
    return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
  }
}
