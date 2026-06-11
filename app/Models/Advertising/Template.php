<?php

namespace App\Models\Advertising;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use SoftDeletes, BaseModel;

    protected $connection = 'advertising';
    protected $table = 'templates';

    protected $fillable = [
        'name',
        'type',
        'landscape_background',
        'portrait_background',
        'template_json',
        'created_by',
        'updated_by',
    ];

    public function contents()
    {
        return $this->hasMany(Content::class, 'template_id');
    }
}
