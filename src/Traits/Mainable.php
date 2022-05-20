<?php

namespace Package\Kennofizet\EmailInternal\Traits;

use Package\Kennofizet\EmailInternal\Model\EmailInternal;
use Package\Kennofizet\EmailInternal\Model\EmailInternalGallery;
use Package\Kennofizet\EmailInternal\Model\EmailInternalGalleryShare;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Validator;
use Storage;

trait MainAble
{
    public function mail($id_mail)
    {
        $new_message = [];
        if ($this->is_sender_receiver($id_mail)) {
            $mail = EmailInternal::find($id_mail);
            return json_decode($mail);
        }else{
            $new_message[] = "!is_sender_receiver";

            return $new_message;
        }
    }

    public function mails($paginate=5,$page=1)
    {
        $mails = EmailInternal::where(function ($query) {
            $query->where('sender_id',$this->id)
            ->where('model_sender',(string)get_class($this));
        })
        ->orWhere(function ($query) {
            $query->where('receiver_id',$this->id)
            ->where('model_receiver',(string)get_class($this));
        })
        ->paginate($paginate, ['*'], 'page', $page);

        $response = [
            'pagination' => [
                'total'        => $mails->total(),
                'per_page'     => $mails->perPage(),
                'current_page' => $mails->currentPage(),
                'last_page'    => $mails->lastPage(),
                'from'         => $mails->firstItem(),
                'to'           => $mails->lastItem()
            ],
            'mails' => $mails
        ];
        $new_data = [];
        $new_data['status'] = "ok";
        $new_data['mails'] = json_decode(response()->json($response)->content())->mails->data;
        $new_data['pagination'] = json_decode(response()->json($response)->content())->pagination;
        return json_decode(json_encode($new_data));

    }

    public function mail_update_file($id_mail,$file)
    {
        $new_message = [];
        if ($this->is_sender_receiver($id_mail)) {
            $mail = EmailInternal::find($id_mail);

            $mail->file = $file;
            $mail->update();

            $new_message[] = "done";

        }else{
            $new_message[] = "!is_sender_receiver";
        }
        return $new_message;
    }

    public function mail_uploads($id_mail,$files)
    {
        $new_message = [];
        $check_mail = $this->checkMailFound($id_mail);
        if ($check_mail) {
            $disk = config('email_internal.disk');
            $path_new = $this->new_path($this->id,$id_mail);

            $i = 0;
            if(!empty($files)){
                foreach($files as $file){
                    $imageName = $this->new_name_image($file,$path_new);
                    $new_message[] = $this->storeFileMail($file,$path_new,$imageName,$disk,$id_mail);
                    $i++;
                }
            }else{
                $new_message[] = "where_is_file";
            }
        }else{
            $new_message[] = "mail_not_found";
        }

        return $new_message;
    }

    public function canViewFile($path)
    {
        $gallery_check = EmailInternalGallery::where('path',$path)->first();
        if ($gallery_check) {
            $mail_id = $gallery_check->mail_id;
            $mail_get = EmailInternal::find($mail_id);
            if($mail_get){
                if ($this->is_sender_receiver($mail_id)) {
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function canEditFile($path)
    {
        $gallery_check = EmailInternalGallery::where('path',$path)->first();
        if ($gallery_check) {
            $mail_id = $gallery_check->mail_id;
            $mail_get = EmailInternal::find($mail_id);
            if($mail_get){
                if ($this->is_sender($mail_id)) {
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function checkMailFound($id_mail)
    {
        $check_mail = EmailInternal::find($id_mail);

        if (!empty($check_mail)) {
            return true;
        }else{
            return false;
        }
    }

    public function storeFileMail($fileUploaded,$folder,$new_name_file,$disk,$id_mail)
    {
        $folder = $folder;
        $rules = array('file' => 'required|mimes:'.implode(',',config('email_internal.file.extension')));
        
        $validator = Validator::make(array('file' => $fileUploaded), $rules);

        if($validator->passes())
        {
            // dd($new_name_file);

            //* quitar / si falla
            // $upload_success = $fileUploaded->move($folder, $new_name_file);

            Storage::disk($disk)->putFileAs(
                $folder,
                $fileUploaded,
                $new_name_file
            );

            $file_new_url = $folder.'/'.$new_name_file;


            $fileProperties = $this->fileProperties($file_new_url,$disk);
            // dd($fileProperties);
            $new_gallery = new EmailInternalGallery;
            $new_gallery->type = $fileProperties['type'];
            $new_gallery->path = $fileProperties['path'];
            $new_gallery->timestamp = $fileProperties['timestamp'];
            $new_gallery->basename = $fileProperties['basename'];
            $new_gallery->dirname = $fileProperties['dirname'];
            $new_gallery->size = $fileProperties['size'];
            $new_gallery->extension = $fileProperties['extension'];
            $new_gallery->filename = $fileProperties['filename'];

            $new_gallery->status = 2;
            $new_gallery->mail_id = $id_mail;
            $new_gallery->save();

            // $this->shareFileMail($id_mail,$new_gallery->id);

            return "done";
            
        }
    }

    public function shareFileMail($id_mail,$gallery_id)
    {
        $mail = EmailInternal::find($id_mail);
        // $new_share = new EmailInternalGalleryShare;
        // $new_share->id_file = $gallery_id;

    }


    public function fileProperties($path = null,$disk)
    {
        $file = Storage::disk($disk)->getMetadata($path);

        $pathInfo = pathinfo($path);

        $file['basename'] = $pathInfo['basename'];
        $file['dirname'] = $pathInfo['dirname'] === '.' ? ''
            : $pathInfo['dirname'];
        $file['extension'] = isset($pathInfo['extension'])
            ? $pathInfo['extension'] : '';
        $file['filename'] = $pathInfo['filename'];

        return $this->aclFilter($disk, [$file])[0];

    }
    // public function getAcl(): bool;
    public function aclFilter($disk, $content)
    {

        $withAccess = array_map(function ($item) use ($disk) {
            // add acl access level

            return $item;
        }, $content);

        return $withAccess;
    }

    public function new_name_image($file,$folder,$number="1")
    {
        $originalFileName = $file->getClientOriginalName();
        $fileName = pathinfo($originalFileName, PATHINFO_FILENAME);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        // dd($fileName);

        $linkFilenameTemp = strtolower(time(). date("Y_m") . $number  . $fileName);
        // dd($linkFilenameTemp);


        $linkFilename = $linkFilenameTemp.'.'.$fileExtension;

        $i = 1;
        while(file_exists($folder.'/'.$linkFilename))
        {
            $linkFilename = $i."_".time(). date("Y_m").$linkFilename;
            $i++;
        }


        return $linkFilename;
    }

    public function new_path($id_user,$id_mail,$randomLength=15)
    {
        
        $token = '';
        do {
            $bytes = random_bytes($randomLength);
            $token .= str_replace(
                ['.','/','='], 
                '',
                base64_encode($bytes)
            );
        } while (strlen($token) < $randomLength);

        $new_path = $this->id.'/'.config('email_internal.path_folder').'/'.$token."/".$id_mail.date("Y-m")."/";
        return $new_path;

    }

    public function mailto($receiver,$content,$subject,$file,$model_receiver="",$colunm_model="id")
    {
        $new_message = [];
        $type_receiver = gettype($receiver);
        // dd($type_receiver);
        $model_sender = (string)get_class($this);
        // dd($model_sender);
        if (!empty($model_receiver)) {}else{
            $model_receiver = $model_sender;
        }
        if ($type_receiver == "array") {
            for ($i=0; $i < count($receiver); $i++) {
                $new_message[] = $this->newMail($this->id,$receiver[$i],$model_sender,$model_receiver,$content,$subject,$file,$colunm_model);
            }
        }

        if ($type_receiver == "object") {
            $new_message[] = $this->newMail($this->id,$receiver->id,$model_sender,(string)get_class($receiver),$content,$subject,$file,'id');
        }

        if ($type_receiver == "string" or $type_receiver == "integer") {
            $new_message[] = $this->newMail($this->id,$receiver,$model_sender,$model_receiver,$content,$subject,$file,$colunm_model);
        }

        return $new_message;
    }

    public function mail_edit($content,$subject,$file,$id_mail)
    {
        $new_message = [];
        if ($this->is_sender($id_mail)) {
            $new_message[] = $this->updateMail($content,$subject,$file,$id_mail);
        }else{
            $new_message[] = "!is_sender";
        }
        return $new_message;
    }

    public function mail_delete($id_mail)
    {
        $new_message = [];
        $id_mail_type = gettype($id_mail);
        if ($id_mail_type == "string" or $id_mail_type == "integer") {
            if ($this->is_sender($id_mail)) {
                $update = EmailInternal::find($id_mail);
                $update->status = 99;
                $update->update();

                $new_message[] = "done";
            }else{
                $new_message[] = "!is_sender";
            }
        }

        if ($id_mail_type == "array") {
            for ($i=0; $i < sizeof($id_mail); $i++) { 
                if ($this->is_sender($id_mail[$i])) {
                    $update = EmailInternal::find($id_mail[$i]);
                    $update->status = 99;
                    $update->update();

                    $new_message[] = "done";
                }else{
                    $new_message[] = "!is_sender";
                }
            }
        }
        
        return $new_message;
    }

    public function mail_revert($id_mail)
    {
        $new_message = [];
        $id_mail_type = gettype($id_mail);
        if ($id_mail_type == "string" or $id_mail_type == "integer") {
            if ($this->is_sender($id_mail)) {
                $update = EmailInternal::find($id_mail);
                $update->status = 1;
                $update->update();

                $new_message[] = "done";
            }else{
                $new_message[] = "!is_sender";
            }
        }

        if ($id_mail_type == "array") {
            for ($i=0; $i < sizeof($id_mail); $i++) { 
                if ($this->is_sender($id_mail[$i])) {
                    $update = EmailInternal::find($id_mail[$i]);
                    $update->status = 1;
                    $update->update();

                    $new_message[] = "done";
                }else{
                    $new_message[] = "!is_sender";
                }
            }
        }
        
        return $new_message;
    }

    public function mail_delete_force()
    {
        $new_message = [];
        $update_sender = EmailInternal::where('sender_id',$this->id)->where('model_sender',(string)get_class($this))->where('status','<>',99)->get();
        
        if (count($update_sender) < 1) {
            // dd(count($update_sender));
            $new_message[] = "data_null";
        }
        foreach ($update_sender as $detail_update_sender) {
            $detail_update_sender->status = 99;
            $detail_update_sender->update();
            $new_message[] = "done";
        }


        $update_receiver = EmailInternal::where('receiver_id',$this->id)->where('model_receiver',(string)get_class($this))->where('status','<>',99)->get();
        foreach ($update_receiver as $detail_update_receiver) {
            $detail_update_receiver->content = "Mail deleted";
            $detail_update_receiver->subject = "Mail deleted";
            $detail_update_receiver->status = 99;
            $detail_update_receiver->file = "";
            $detail_update_receiver->update();
            $new_message[] = "done";
        }

        return $new_message;
    }

    public function mail_unread($id_mail)
    {
        $new_message = [];
        if ($this->is_sender_receiver_status($id_mail,0)) {
            $update = EmailInternal::find($id_mail);
            if ($update->sender_id == $this->id) {
                $update->sender_read = 1;
                $update->update();
            }
            if ($update->receiver_id == $this->id) {
                $update->receiver_read = 1;
                $update->update();
            }
            $new_message[] = "done";
        }else{
            $new_message[] = "!is_sender_receiver_or_status";
        }

        return $new_message;
    }

    public function mail_read($id_mail)
    {
        $new_message = [];
        if ($this->is_sender_receiver_status($id_mail,1)) {
            $update = EmailInternal::find($id_mail);
            if ($update->sender_id == $this->id) {
                $update->sender_read = 0;
                $update->update();
            }
            if ($update->receiver_id == $this->id) {
                $update->receiver_read = 0;
                $update->update();
            }
            $new_message[] = "done";
        }else{
            $new_message[] = "!is_sender_receiver_or_status";
        }

        return $new_message;
    }

    public function is_sender_receiver_status($id_mail,$status)
    {
        $check_sender = EmailInternal::where('id',$id_mail)
        ->where('sender_id',$this->id)
        ->where('model_sender',(string)get_class($this))
        ->where('sender_read',$status)
        ->first();

        $check_receiver = EmailInternal::where('id',$id_mail)
        ->where('receiver_id',$this->id)
        ->where('model_receiver',(string)get_class($this))
        ->where('receiver_read',$status)
        ->first();

        if (!empty($check_sender) or !empty($check_receiver)) {
            return true;
        }else{
            return false;
        }
    }

    public function is_sender_receiver($id_mail)
    {
        $check_sender = EmailInternal::where('id',$id_mail)
        ->where('sender_id',$this->id)
        ->where('model_sender',(string)get_class($this))
        ->first();

        $check_receiver = EmailInternal::where('id',$id_mail)
        ->where('receiver_id',$this->id)
        ->where('model_receiver',(string)get_class($this))
        ->first();

        if (!empty($check_sender) or !empty($check_receiver)) {
            return true;
        }else{
            return false;
        }
    }

    public function is_sender($id_mail)
    {
        $check = EmailInternal::where('id',$id_mail)
        ->where('sender_id',$this->id)
        ->where('model_sender',(string)get_class($this))
        ->first();

        if (!empty($check)) {
            return true;
        }else{
            return false;
        }
    }

    public function newMail($sender_id,$receiver_data,$model_sender,$model_receiver,$content,$subject,$file,$colunm_model)
    {
        // dd($sender_id,$receiver_data,$model_sender,$model_receiver,$content,$subject,$file,$colunm_model);
        $check_model_try = is_subclass_of($model_receiver, 'Illuminate\Database\Eloquent\Model');

        if ($check_model_try) {
            $model_receiver_try = $model_receiver::where($colunm_model,$receiver_data)->first();
            if (!empty($model_receiver_try)) {
                $new_mail = new EmailInternal;
                $new_mail->sender_id = $sender_id;
                $new_mail->receiver_id = $model_receiver_try->id;
                $new_mail->model_sender = $model_sender;
                $new_mail->model_receiver = $model_receiver;
                $new_mail->content = $content;
                $new_mail->subject = $subject;
                $new_mail->file = $file;
                $new_mail->status = 1;
                $new_mail->sender_read = 1;
                $new_mail->receiver_read = 1;
                $new_mail->save();
                return ["message" => "done", "mail_id" => $new_mail->id];
            }else{
                return ["message" => "receiver_not_found"];
            }
        }else{
            return ["message" => "model_not_found"];
        }
    }

    public function updateMail($content,$subject,$file,$id_mail)
    {
        $update_mail = EmailInternal::find($id_mail);
        if (!empty($update_mail)) {
            $update_mail->content = $content;
            $update_mail->subject = $subject;
            $update_mail->file = $file;
            $update_mail->save();

            return "done";
        }else{
            return "mail_not_found";
        }
    }

}
