<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MediaFile extends Model
{
    use LogsActivity;

    protected $connection = 'advertising';
    protected $table = 'media_files';

    protected $fillable = [
        'content_id',
        'media_type',
        'orientation',
        'file_path',
        'thumbnail',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('MediaFile')
            ->setDescriptionForEvent(fn(string $eventName) => "MediaFile has been $eventName")
            ->logOnlyDirty();
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function scopeInclude($query)
    {
        if (request()->has('include')) {
            return $query->with(explode(',', request('include')));
        }
    }

}
