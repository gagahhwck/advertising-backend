<?php

namespace App\Models\Advertising;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCategory extends Model
{
    use SoftDeletes, BaseModel;

    protected $connection = 'advertising';
    protected $table = 'event_categories';

    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
        'is_active'
    ];

    public function events()
    {
        return $this->hasMany(Event::class,'event_category_id');
    }
}
