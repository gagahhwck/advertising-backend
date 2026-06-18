<?php

namespace App\Models\Advertising;

use App\Models\Advertising\ContentLocation;
use App\Models\Advertising\MediaFile;
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
        'display_duration',
        'priority',
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

    public function playback_logs()
    {
        return $this->hasMany(PlaybackLog::class, 'content_id');
    }

    public function content_locations()
    {
        return $this->hasMany(ContentLocation::class, 'content_id');
    }

    public function content_receipts()
    {
        return $this->hasMany(ContentReceipt::class, 'content_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class,'content_id');
    }
}
