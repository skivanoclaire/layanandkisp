<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortlinkRequestLog extends Model
{
    protected $fillable = ['shortlink_request_id', 'actor_id', 'action', 'note'];

    public function request() { return $this->belongsTo(ShortlinkRequest::class, 'shortlink_request_id'); }
    public function actor()   { return $this->belongsTo(User::class, 'actor_id'); }
}
