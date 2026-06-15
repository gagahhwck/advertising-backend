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
        'event_type',
        'created_by',
        'updated_by'
    ];

    public function type()
    {
        return $this->belongsTo(EventCategory::class,'event_type');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'event_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'event_id');
    }


}
