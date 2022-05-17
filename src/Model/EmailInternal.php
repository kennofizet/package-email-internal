<?php

namespace Package\Kennofizet\EmailInternal\Model;

use Illuminate\Database\Eloquent\Model;

class EmailInternal extends Model
{
    protected $table = "email_internal";
    public $timestamps = true;
    protected $fillable = ['subject', 'content','file','model','sender_id','receiver_id'];
}
