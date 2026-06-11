<?php

namespace App\Models\SSO;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class ModelHasRole extends Model
{
  protected $connection = 'sso';
  protected $table = 'model_has_roles';
  public $timestamps = false;
  protected $fillable = [
    'role_id',
    'model_type',
    'model_id',
    'is_active',
  ];

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logAll()
      ->useLogName('SSO')
      ->setDescriptionForEvent(fn(string $eventName) => "Model Has Role has been $eventName")
      ->logOnlyDirty();
  }

  public function role()
  {
    return $this->belongsTo(Role::class, 'role_id', 'id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'model_id', 'id');
  }
}
