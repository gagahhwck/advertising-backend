<?php

namespace App\Models\SSO;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class ModelHasPermission extends Model
{
  protected $connection = 'sso';
  protected $table = 'model_has_roles';
  public $timestamps = false;
  protected $fillable = [
    'permission_id',
    'model_type',
    'model_id',
  ];

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logAll()
      ->useLogName('SSO')
      ->setDescriptionForEvent(fn(string $eventName) => "Model Has Permission has been $eventName")
      ->logOnlyDirty();
  }

  public function permission()
  {
    return $this->belongsTo(Permission::class, 'permission_id', 'id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'model_id', 'id');
  }
}
