<?php

namespace App\Models\Advertising;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes, BaseModel;

    protected $connection = 'advertising';
    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'gateway',
        'transaction_id',
        'payment_url',
        'status',
        'paid_at',
        'raw_response'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }
}
