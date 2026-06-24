<?php

namespace App\Models\Advertising;

use App\Models\Assets\LocationAsset;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventLocation extends Model
{
    use BaseModel, SoftDeletes;

    protected $connection = 'advertising';
    protected $table = 'event_locations';

    protected $fillable = [
        'event_id',
        'location_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function location()
    {
        return $this->belongsTo(LocationAsset::class, 'location_id');
    }
}
