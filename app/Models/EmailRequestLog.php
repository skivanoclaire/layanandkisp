<?php

// app/Models/EmailRequestLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailRequestLog extends Model
{
    protected $fillable = ['email_request_id','actor_id','action','note'];
    public function request(){ return $this->belongsTo(EmailRequest::class,'email_request_id'); }
    public function actor(){ return $this->belongsTo(User::class,'actor_id'); }
}
