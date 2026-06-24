<?php

namespace App\Models\Advertising;

use App\Models\Assets\LocationAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EventLocation extends Model
{
    use LogsActivity, SoftDeletes;

    protected $connection = 'advertising';
    protected $table = 'event_locations';

    protected $fillable = [
        'event_id',
        'location_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Event Location')
            ->setDescriptionForEvent(fn(string $eventName) => "Event Location has been $eventName")
            ->logOnlyDirty();
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event');
    }

    public function location()
    {
        return $this->belongsTo(LocationAsset::class, 'location_id');
    }

    public function scopeInclude($query)
    {
        if (request()->has('include')) {
            return $query->with(explode(',', request('include')));
        }
    }
}
