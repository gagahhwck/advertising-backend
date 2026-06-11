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

    public function scopeInclude($query)
    {
        if (request()->has('include')) {
            return $query->with(explode(',', request('include')));
        }
    }

    public function scopeFilter($query)
    {
        if (request()->has('q')) {
            $query->where(function ($q) {
            foreach ($this->fillable as $key => $column) {
                if ($key == 0) {
                $q->where($column, 'like', '%' . request('q') . '%');
                } else {
                $q->orWhere($column, 'like', '%' . request('q') . '%');
                }
            }
            });
        }

        if (request()->has('filters') && is_array(request('filters'))) {
            $query->where(function ($q) {
            foreach (request('filters') as $column => $value) {
                if (in_array($column, $this->fillable)) {
                $q->where($column, $value);
                }
            }
            });
        }

        return $query;
    }


}
