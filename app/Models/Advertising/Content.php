<?php

namespace App\Models\Advertising;

use App\Models\Advertising\ContentLocation;
use App\Models\Advertising\MediaFile;
use App\Models\Advertising\Payment;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use SoftDeletes, BaseModel;

    protected $connection = 'advertising';
    protected $table = 'contents';

    protected $fillable = [
        'template_id',
        'event_id',
        'created_by',
        'updated_by',
        'title',
        'description',
        'orientation',
        'display_duration',
        'priority',
        'auto_resize',
        'is_active',
        'status',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class,'event_id');
    }    
    
    public function media_files()
    {
        return $this->hasMany(MediaFile::class, 'content_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'content_id');
    }

    public function playback_logs()
    {
        return $this->hasMany(PlaybackLog::class, 'content_id');
    }

    public function content_locations()
    {
        return $this->hasMany(ContentLocation::class, 'content_id');
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
