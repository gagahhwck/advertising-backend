<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Status extends Model
{
    use LogsActivity;

    protected $connection = 'email';
    protected $table = 'email.dbo.statuses';

    protected $fillable = [
        'transmission_id',
        'status',
        'reason',
        'timestamp',
    ];

    protected $appends = [
        'status_display',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Email')
            ->setDescriptionForEvent(fn(string $eventName) => "Status has been $eventName")
            ->logOnlyDirty();
    }

    public function message()
    {
        return $this->belongsTo(Message::class, 'transmission_id', 'transmission_id');
    }

    public function scopeInclude($query)
    {
        if (request()->has('include')) {
            return $query->with(explode(',', request('include')));
        }
    }

    public function scopeFilter($query)
    {
        $query->when(request('q'), function ($query) {
            $query->where(function ($query) {
                foreach ($this->fillable as $key => $column) {
                    if ($key == 0) {
                        $query->where($column, 'like', '%' . request('q') . '%');
                    } else {
                        $query->orWhere($column, 'like', '%' . request('q') . '%');
                    }
                }
            });
        });

        if (request('filters') && is_array(request('filters'))) {
            $query->where(function ($query) {
                foreach (request('filters') as $key => $filter) {
                    if (in_array($key, $this->fillable)) {
                        $query->where($key, "$filter");
                    }
                }
            });
        }

        return $query;
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'injection' => '<span title="'. $this->reason .'" data-bs-toggle="tooltip" class="badge bg-label-dark">Injection</span>',
            'delivery'  => '<span title="'. $this->reason .'" data-bs-toggle="tooltip" class="badge bg-success">Delivery</span>',
            'delay'     => '<span title="'. $this->reason .'" data-bs-toggle="tooltip" class="badge bg-warning">Delay</span>',
            'bounce'    => '<span title="'. $this->reason .'" data-bs-toggle="tooltip" class="badge bg-danger">Bounce</span>',
            'open'      => '<span title="'. $this->reason .'" data-bs-toggle="tooltip" class="badge bg-info">Open</span>',
            default     => $this->status,
        };
    }
}
