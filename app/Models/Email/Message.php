<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Message extends Model
{
    use LogsActivity;

    protected $connection = 'email';
    protected $table = 'email.dbo.messages';

    protected $fillable = [
        'application_code',
        'event_id',
        'message_id',
        'transmission_id',
        'subject',
        'body',
        'sender',
        'sending_ip',
        'ip_address',
        'timestamp',
        'created_by',
        'created_type'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Email')
            ->setDescriptionForEvent(fn(string $eventName) => "Message has been $eventName")
            ->logOnlyDirty();
    }

    public function recipients()
    {
        return $this->hasMany(Recipient::class, 'transmission_id', 'transmission_id');
    }

    public function create_by()
    {
        return $this->morphTo('create_by', 'created_type', 'created_by', 'username');
    }

    public function statuses()
    {
        return $this->hasMany(Status::class, 'transmission_id', 'transmission_id');
    }

    public function last_status()
    {
        return $this->hasOne(Status::class, 'transmission_id', 'transmission_id')->latest('created_at');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'transmission_id', 'transmission_id');
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
                $query->orWhereHas('recipients', function ($query) {
                    $query->where('email', 'like', '%' . request('q') . '%')
                      ->orWhere('name', 'like', '%' . request('q') . '%');
                });
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
}
