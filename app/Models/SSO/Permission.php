<?php

namespace App\Models\SSO;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Permission extends Model
{
  protected $connection = 'sso';
  protected $table = 'permissions';

  protected $fillable = [
    'name',
    'guard_name',
  ];

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logAll()
      ->useLogName('SSO')
      ->setDescriptionForEvent(fn(string $eventName) => "Permission has been $eventName")
      ->logOnlyDirty();
  }

  public function roles()
  {
    return $this->belongsToMany(Role::class, 'role_has_permissions', 'permission_id', 'role_id');
  }

  public function users()
  {
    return $this->belongsToMany(User::class, 'model_has_permissions', 'permission_id', 'user_id');
  }
}
