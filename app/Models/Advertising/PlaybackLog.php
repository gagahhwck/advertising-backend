<?php

namespace App\Models\Advertising;

use App\Models\Advertising\Content;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PlaybackLog extends Model
{
    use LogsActivity;

    protected $connection = 'advertising';
    protected $table = 'playback_logs';

    protected $fillable = [
        'content_id',
        'device_id',
        'played_at',
        'duration',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('PlaybackLog')
            ->setDescriptionForEvent(fn(string $eventName) => "PlaybackLog has been $eventName")
            ->logOnlyDirty();
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function scopeInclude($query)
    {
        if (request()->has('include')) {
            return $query->with(explode(',', request('include')));
        }
    }
}
