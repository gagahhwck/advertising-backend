<?php

namespace App\Models\Advertising;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentReceipts extends Model
{
    use BaseModel, SoftDeletes;
    
    protected $connection ='advertising';
    protected $table= 'content_receipts';

    protected $fillable = [
        'content_id',
        'title',
        'description',
        'to',
        'from',
        'created_by',
        'status'
    ];
}
