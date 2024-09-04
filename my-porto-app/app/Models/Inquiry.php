<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'path_url',
        'request_body',
        'request_header',
        'response_body',
        'response_header',
        'user_id',
        'http_code_status',
        'transaction_invoice_number',
        'type',
        'path_url_token',
        'clientid', // tambahkan kolom-kolom baru di sini
        'sharedkey',
        'privatekey',
        'dokupublickey',
    ];
    

    protected $casts = [
        'request_body' => 'array',
        'request_header' => 'array',
        'response_body' => 'array',
        'response_header' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
