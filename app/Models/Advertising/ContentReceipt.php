<?php

namespace App\Models\Advertising;

use App\Models\Advertising\Content;
use App\Models\SSO\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContentReceipt extends Model
{
    use LogsActivity, SoftDeletes;

    protected $connection = 'advertising';
    protected $table = 'content_receipts';

    protected $fillable = [
        'content_id',
        'title',
        'description',
        'to',
        'from',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('ContentReceipt')
            ->setDescriptionForEvent(fn(string $eventName) => "ContentReceipt has been $eventName")
            ->logOnlyDirty();
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function to_user()
    {
        return $this->belongsTo(User::class, 'to', 'username');
    }

    public function from_user()
    {
        return $this->belongsTo(User::class, 'from', 'username');
    }

    public function scopeInclude($query)
    {
        if (request()->has('include')) {
            return $query->with(explode(',', request('include')));
        }
    }


}
