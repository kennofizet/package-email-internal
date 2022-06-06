<?php

namespace Package\Kennofizet\EmailInternal\Model;

use Illuminate\Database\Eloquent\Model;

class EmailReplyInternal extends Model
{
    // const sender_id = null;

    protected $table = "email_reply_internal";
    public $timestamps = true;
    protected $fillable = ['sender_type', 'content','sender_id','string'];

    public function files()
    {
        return $this->hasMany('Package\Kennofizet\EmailInternal\Model\EmailReplyInternalGallery','mail_id','id');
    }

    public function sender()
    {
        return $this->morphTo();
    }


    public function receiver()
    {
        return $this->morphTo();
    }

}
