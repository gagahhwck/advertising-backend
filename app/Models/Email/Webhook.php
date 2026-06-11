<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Webhook extends Model
{
    use LogsActivity;

    protected $connection = 'email';
    protected $table = 'email.dbo.webhooks';

    protected $fillable = [
        'payload',
        'ip_address',
        'user_agent',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Email')
            ->setDescriptionForEvent(fn(string $eventName) => "Webhook has been $eventName")
            ->logOnlyDirty();
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
                        $query->where($key, $filter);
                    }
                }
            });
        }

        return $query;
    }
}
