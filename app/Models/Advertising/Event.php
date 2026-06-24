<?php

namespace App\Models\Advertising;

use App\Models\Advertising\Content;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes, BaseModel;

    protected $connection = 'advertising';
    protected $table = 'events';

    protected $fillable = [
        'title',
        'description',
        'event_category_id',
        'created_by',
        'updated_by'
    ];

    public function category()
    {
        return $this->belongsTo(EventCategory::class,'event_category_id');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'event_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'event_id');
    }

    public function locations()
    {
        return $this->belongsTo(EventLocation::class, 'event_id');
    }


}
