<?php

namespace App\Models\Advertising;

use App\Models\Assets\LocationAsset;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RunningText extends Model
{
    use BaseModel, SoftDeletes;
    
    protected $connection = 'advertising';
    protected $table = 'running_texts';

    protected $fillable = [
        'location_id',
        'event_id',
        'priority',
        'message',
        'start_at',
        'end_at',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function location()
    {
        return $this->belongsTo(LocationAsset::class, 'location_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(Event::class,'event_id');
    }
}
