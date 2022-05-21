<?php

namespace Package\Kennofizet\EmailInternal\Model;

use Illuminate\Database\Eloquent\Model;

class EmailInternal extends Model
{
    // const sender_id = null;

    protected $table = "email_internal";
    public $timestamps = true;
    protected $fillable = ['subject', 'content','file','model','sender_id','receiver_id'];

    public function gallery_file()
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
