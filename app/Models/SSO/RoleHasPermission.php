<?php

namespace App\Models\SSO;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class RoleHasPermission extends Model
{
  protected $connection = 'sso';
  protected $table = 'role_has_permissions';
  public $timestamps = false;
  protected $fillable = [
    'permission_id',
    'role_id',
  ];

  protected static $logName = 'SSO';
  protected static $recordEvents = ['created', 'updated', 'deleted'];
  protected static $logAttributes = ['*'];
  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logAll()
      ->useLogName('SSO')
      ->setDescriptionForEvent(fn(string $eventName) => "Role Has Permission has been $eventName")
      ->logOnlyDirty();
  }

  public function permission()
  {
    return $this->belongsTo(Permission::class, 'permission_id', 'id');
  }

  public function role()
  {
    return $this->belongsTo(Role::class, 'role_id', 'id');
  }
}
