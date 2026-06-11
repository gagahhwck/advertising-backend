<?php

namespace App\Models\SSO;

use App\Models\EMA\MappingEmployeeUser;
use App\Models\Lecturer\Profile;
use App\Models\UAIS\StudentProfile;
use App\Models\UAIS\StudyProgram;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
  use LogsActivity, HasFactory, Notifiable;

  protected $connection = 'sso';
  protected $table = 'sso.dbo.users';

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logAll()
      ->useLogName('SSO')
      ->setDescriptionForEvent(fn(string $eventName) => "User has been $eventName")
      ->logOnlyDirty();
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'id',
    'name',
    'first_name',
    'last_name',
    'username_changed',
    'username',
    'organization_unit_id',
    'avatar',
    'email',
    'email_verified_at',
    'password',
    'expo_token',
    'remember_token',
    'created_at',
    'updated_at',
    'gender_id',
    'status',
    'nik',
    'cookie_device',
    'place_of_birth',
    'email_personal',
    'address',
    'phone_number',
    'birth_date',
    'bank',
    'bank_account_name',
    'bank_account_number',
    'nick_name',
    'country_id',
    'nik_finance',
    'npwp',
    'house_phone',
    'nationality',
    'jalan',
    'dusun',
    'rt',
    'rw',
    'kelurahan',
    'kode_pos',
    'transportation_id',
    'residence_type_id',
    'religion_id',
    'front_title',
    'back_title',
    'marital_status',
    'bio',
    'region_id',
    'sisa_cuti',
    'sisa_cuti_tahun_lalu',
    'old_username',
    'password2',
    'instagram',
    'linkedIn',
    'is_external'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'roles',
    'permissions',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  /**
   * Get the identifier that will be stored in the subject claim of the JWT.
   *
   * @return mixed
   */
  public function getJWTIdentifier()
  {
    return $this->getKey();
  }

  /**
   * Return a key value array, containing any custom claims to be added to the JWT.
   *
   * @return array
   */
  public function getJWTCustomClaims()
  {
    return [
      'guard' => 'api',
      'user'  => [
        'id'        => $this->id,
        'name'      => $this->name,
        'username'  => $this->username,
        'email'     => $this->email,
      ]
    ];
  }

  public function roles()
  {
    return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')->where('is_active', 1);
  }

  public function hasRole($role)
  {
    $roles = is_array($role) ? $role : explode('|', $role);
    return $this->roles->whereIn('name', $roles)->count() > 0;
  }

  public function hasPermission($permission)
  {
    $permissions = is_array($permission) ? $permission : explode('|', $permission);
    foreach ($this->roles as $role) {
      if ($role->permissions->whereIn('name', $permissions)->count() > 0) {
        return true;
      }
    }
    foreach ($this->special_permissions as $perm) {
      if (in_array($perm->name, $permissions)) {
        return true;
      }
    }
    return false;
  }

  public function special_permissions()
  {
    return $this->belongsToMany(Permission::class, 'model_has_permissions', 'model_id', 'permission_id');
  }

  public function mapping_employee_user()
  {
    return $this->hasOne(MappingEmployeeUser::class, 'user_id', 'email');
  }

  public function lecturer()
  {
    return $this->hasOne(Profile::class, 'user_id', 'username');
  }
}
