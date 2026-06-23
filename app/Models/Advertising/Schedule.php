<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Schedule extends Model
{
    use LogsActivity;
    protected $connection = 'advertising';
    protected $table = 'schedules';

    protected $fillable = [
        'event_id',
        'start_at',
        'end_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Schedule')
            ->setDescriptionForEvent(fn(string $eventName) => "Schedule has been $eventName")
            ->logOnlyDirty();
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
