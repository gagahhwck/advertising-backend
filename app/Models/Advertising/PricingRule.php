<?php

namespace App\Models\Advertising;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    use BaseModel;

    protected $connection = 'advertising';
    protected $table = 'pricing_rules';

    protected $fillable = [
        'event_category_id',
        'price',
        'reason',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function type()
    {
        return $this->belongsTo(EventCategory::class,'event_category_id');
    }
}
