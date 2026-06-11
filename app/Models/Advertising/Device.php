<?php

namespace App\Models\Advertising;

use App\Models\Assets\LocationAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Device extends Model
{
    use LogsActivity, SoftDeletes;

    protected $connection = 'advertising';
    protected $table = 'devices';

    protected $fillable = [
        'location_id',
        'device_name',
        'device_code',
        'orientation',
        'width',
        'height',
        'is_online',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('AIS')
            ->setDescriptionForEvent(fn(string $eventName) => "Device has been $eventName")
            ->logOnlyDirty();
    }

    public function location()
    {
        return $this->belongsTo(LocationAsset::class, 'location_id', 'id');
    }

    public function playback_logs()
    {
        return $this->hasMany(PlaybackLog::class, 'device_id');
    }
}
