<?php

namespace Package\Kennofizet\EmailInternal\Model;

use Illuminate\Database\Eloquent\Model;

class EmailInternal extends Model
{
    // const sender_id = null;

    protected $table = "email_internal";
    public $timestamps = true;
    protected $fillable = ['subject', 'content','file','sender_type', 'receiver_type','sender_id','receiver_id','token','status','sender_read','sender_trash','sender_star','receiver_read','receiver_trash','receiver_star'];

    public function files()
    {
        return $this->hasMany('Package\Kennofizet\EmailInternal\Model\EmailInternalGallery','mail_id','id');
    }

    public function sender()
    {
        return $this->morphTo();
    }
    public function receiver()
    {
        return $this->morphTo();
    }

    public function test($value='')
    {
        return $this->with($value);
    }

    public function mail_reply()
    {
        return $this->hasMany('Package\Kennofizet\EmailInternal\Model\EmailReplyInternal','mail_id','id')->orderBy('id','DESC')->with('files');
    }

    public function settingable() {
        return $this->morphTo();
    }

    public function senderable() {
        return $this->belongsTo('Package\Kennofizet\EmailInternal\Model\EmailInternal','id','id');
    }

    public function queryModel($model,$id)
    {
        return $model::find($id);
    }


}
