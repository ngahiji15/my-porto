<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // Menonaktifkan fitur timestamps
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'cart',
        'total_amount',
        'invoice_number',
        'payment_channel',
        'user_id',
        'status',
        'type',
        'order_type',
        'create_date',
        'update_date',
        'payment_code',
    ];

    protected $casts = [
        'cart' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

