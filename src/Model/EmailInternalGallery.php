<?php

namespace Package\Kennofizet\EmailInternal\Model;

use Illuminate\Database\Eloquent\Model;

class EmailInternalGallery extends Model
{
    protected $table = "email_internal_gallery";
    public $timestamps = true;
    protected $fillable = ['path', 'type','dirname','basename','timestamp','size','extension','filename ','mail_id','status'];
}
