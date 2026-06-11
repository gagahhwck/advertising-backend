<?php

namespace App\Models\Advertising;

use App\Models\Assets\LocationAsset;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RunningText extends Model
{
    use LogsActivity;
    
    protected $connection = 'advertising';
    protected $table = 'running_texts';

    protected $fillable = [
        'location_id',
        'text',
        'is_active',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('RunningText')
            ->setDescriptionForEvent(fn(string $eventName) => "RunningText has been $eventName")
            ->logOnlyDirty();
    }

    public function location()
    {
        return $this->belongsTo(LocationAsset::class, 'location_id', 'id');
    }
}
