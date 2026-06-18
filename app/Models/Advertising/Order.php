<?php

namespace App\Models\Advertising;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes, BaseModel;

    protected $connection = 'advertising';
    protected $table = 'orders';

    protected $fillable = [
        'content_id',
        'order_code',
        'amount',
        'status',
        'reason',
        'created_by',
        'updated_by',
        'paid_at',
        'reject_at',
        'failed_at'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function payments()
    {
        return $this->hasOne(Payment::class,'order_id');
    }
}
