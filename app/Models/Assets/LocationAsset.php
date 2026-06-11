<?php

namespace App\Models\Assets;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LocationAsset extends Model
{
    use LogsActivity;

    protected $connection = 'db_general';
    protected $table = 'db_general.dbo.location_assets';

    protected $fillable = [
        'code',
        'fullname',
        'desc',
        'capacity',
        'building',
        'floor',
        'room',
        'uniq_code',
        'type_location_id',
        'status',
        'nominal',
        'is_bookingable',
        'cordinates',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('AIS')
            ->setDescriptionForEvent(fn(string $eventName) => "Location Asset has been $eventName")
            ->logOnlyDirty();
    }

    protected $appends = [
        'status_display',
    ];

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

    public function getStatusDisplayAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }
}
