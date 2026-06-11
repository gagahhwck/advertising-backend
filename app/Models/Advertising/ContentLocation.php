<?php

namespace App\Models\Advertising;

use App\Models\Assets\LocationAsset;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContentLocation extends Model
{
    use LogsActivity;

    protected $connection = 'advertising';
    protected $table = 'content_locations';

    protected $fillable = [
        'content_id',
        'location_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('ContentLocation')
            ->setDescriptionForEvent(fn(string $eventName) => "ContentLocation has been $eventName")
            ->logOnlyDirty();
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
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
