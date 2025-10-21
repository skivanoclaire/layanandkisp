<?php

// app/Models/SimpegCheck.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimpegCheck extends Model
{
    protected $fillable = [
        'nik','user_id','is_nik_valid','nip','name_from_simpeg',
        'name_match','phone_match','email_match','raw_response',
        'created_by','ip','user_agent'
    ];

    protected $casts = [
        'raw_response' => 'array',
        'is_nik_valid' => 'boolean',
        'name_match'   => 'boolean',
        'phone_match'  => 'boolean',
        'email_match'  => 'boolean',
        'raw_response' => 'array',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }
}
