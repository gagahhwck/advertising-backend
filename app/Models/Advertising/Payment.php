<?php

namespace App\Models\Advertising;

use App\Models\Advertising\Content;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use LogsActivity;
    protected $connection = 'advertising';
    protected $table = 'payments';

    protected $fillable = [
        'content_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'evidence'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Payment')
            ->setDescriptionForEvent(fn(string $eventName) => "Payment has been $eventName")
            ->logOnlyDirty();
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
