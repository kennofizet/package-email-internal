<?php

namespace Package\Kennofizet\EmailInternal\Model;

use Illuminate\Database\Eloquent\Model;

class EmailInternalGallery extends Model
{
    protected $table = "email_internal_gallery_share";
    public $timestamps = true;
    protected $fillable = ['id_file', 'id_can','can_status'];
}
