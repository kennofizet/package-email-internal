<?php

namespace Package\Kennofizet\EmailInternal\Traits;

use Package\Kennofizet\EmailInternal\Model\EmailInternal;
use Package\Kennofizet\EmailInternal\Model\EmailReplyInternal;
use Package\Kennofizet\EmailInternal\Model\EmailInternalGallery;
use Package\Kennofizet\EmailInternal\Model\EmailReplyInternalGallery;
use Package\Kennofizet\EmailInternal\Model\EmailInternalGalleryShare;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use App\User;
use Validator;
use Storage;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait MainAble
{
    public function mail_news_active($limit = 6,$page = 1,$colunm_sort="updated_at",$order_by="DESC",$relationship_sender=[])
    {
        $mails = EmailInternal::orderBy($colunm_sort,$order_by)
        ->where(function ($query_sender_or_receiver)
        {
            $query_sender_or_receiver
            ->where(function ($query_sender) {
                $query_sender->where('sender_id',$this->id)
                ->where('sender_type',(string)get_class($this))
                ->where('sender_read',1);
            })
            ->orWhere(function ($query_receiver) {
                $query_receiver->where('receiver_id',$this->id)
                ->where('receiver_type',(string)get_class($this))
                ->where('receiver_read',1);
            });
        })
        ->with('files')
        ->with([
        'sender' => function ($subQuerySender) use ($relationship_sender)
            {
                if (!empty($relationship_sender)) {
                    $try_sender_relationship = true;
                }else{
                    $try_sender_relationship = false;
                }
                if ($try_sender_relationship) {
                    for ($i=0; $i < sizeof($relationship_sender); $i++) { 
                        $subQuerySender->with($relationship_sender[$i]);
                    }
                }
            }
        ])->paginate($limit, ['*'], 'page', $page);
        // dd($mails);

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
        // dd($new_data);
        return json_decode(json_encode($new_data));
    }

    public function count_mail_news_active()
    {
        $mails = EmailInternal::where(function ($query_sender_or_receiver)
        {
            $query_sender_or_receiver
            ->where(function ($query_sender) {
                $query_sender->where('sender_id',$this->id)
                ->where('sender_type',(string)get_class($this))
                ->where('sender_read',1);
            })
            ->orWhere(function ($query_receiver) {
                $query_receiver->where('receiver_id',$this->id)
                ->where('receiver_type',(string)get_class($this))
                ->where('receiver_read',1);
            });
        })->count();
        // dd($mails);

        
        return $mails;
    }
    public function mail_reply($data,$content,$colunm_get="token")
    {
        $new_message = [];
        $mail = $this->mail($data,$colunm_get);
        if ($this->is_sender_receiver($mail->id)) {
            $new_message[] = $this->newMailReply($this->id,(string)get_class($this),$content,$mail->id);
            return $new_message;
        }else{
            $new_message[] = "!is_sender_receiver";
            return $new_message;
        }
    }

    public function mail_search($key='',$colunm_search="subject",$relationship_sender=[],$relationship_receiver=[])
    {

        if ($key) {
            $mails = EmailInternal::where($colunm_search, 'LIKE', '%' . $key . '%')
            ->where('receiver_trash',1)
            ->where(function ($query) {
                $query->where('receiver_id',$this->id)
                ->where('receiver_type',(string)get_class($this));
            })
            ->with([
            'receiver' => function ($subQuerySender) use ($relationship_receiver)
                {
                    if (!empty($relationship_receiver)) {
                        $try_receicer_relationship = true;
                    }else{
                        $try_receicer_relationship = false;
                    }
                    if ($try_receicer_relationship) {
                        for ($i=0; $i < sizeof($relationship_receiver); $i++) { 
                            $subQuerySender->with($relationship_receiver[$i]);
                        }
                    }
                }
            ])
            ->with('files')
            ->with([
            'sender' => function ($subQuerySender) use ($relationship_sender)
                {
                    if (!empty($relationship_sender)) {
                        $try_sender_relationship = true;
                    }else{
                        $try_sender_relationship = false;
                    }
                    if ($try_sender_relationship) {
                        for ($i=0; $i < sizeof($relationship_sender); $i++) { 
                            $subQuerySender->with($relationship_sender[$i]);
                        }
                    }
                }
            ])
            ->get();

            return $mails;
        }
    }
    public function extension_file($extension)
    {
        if (
            strtolower($extension) == "apng" or
            strtolower($extension) == "avif" or
            strtolower($extension) == "gif" or
            strtolower($extension) == "jpg" or
            strtolower($extension) == "jpeg" or
            strtolower($extension) == "jfif" or
            strtolower($extension) == "pjpeg" or
            strtolower($extension) == "pjp" or
            strtolower($extension) == "png" or
            strtolower($extension) == "svg" or
            strtolower($extension) == "webp" or
            strtolower($extension) == "bmp" or
            strtolower($extension) == "bmpbmp" or
            strtolower($extension) == "ico" or
            strtolower($extension) == "cur" or
            strtolower($extension) == "tif" or
            strtolower($extension) == "tiff"
        ) {
            $type = "image";
        }else if (
            strtolower($extension) == "doc" or
            strtolower($extension) == "docx" or
            strtolower($extension) == "html" or
            strtolower($extension) == "htm" or
            strtolower($extension) == "odt" or
            strtolower($extension) == "pdf" or
            strtolower($extension) == "xls" or
            strtolower($extension) == "xlsx" or
            strtolower($extension) == "ods" or
            strtolower($extension) == "ppt" or
            strtolower($extension) == "pptx" or
            strtolower($extension) == "txt"
        ) {
            $type = "doc";
        }else{
            $type = "not_found";
        }

        return $type;
    }
    public function mail($data_get,$colunm_get="id",$relationship_sender=[],$relationship_receiver=[])
    {

        $new_message = [];
        $mail_try_get = EmailInternal::where($colunm_get,$data_get)->first();
        if (!empty($mail_try_get)) {
            if ($this->is_sender_receiver($mail_try_get->id)) {
                $mail = EmailInternal::where($colunm_get,$data_get)
                ->with([
                'receiver' => function ($subQuerySender) use ($relationship_receiver)
                    {
                        if (!empty($relationship_receiver)) {
                            $try_receicer_relationship = true;
                        }else{
                            $try_receicer_relationship = false;
                        }
                        if ($try_receicer_relationship) {
                            for ($i=0; $i < sizeof($relationship_receiver); $i++) { 
                                $subQuerySender->with($relationship_receiver[$i]);
                            }
                        }
                    }
                ])
                ->with(['mail_reply' => function ($subQueryReplySender) use ($relationship_receiver,$relationship_sender)
                    {
                        $subQueryReplySender
                        ->with([
                        'receiver' => function ($subQuerySender) use ($relationship_receiver,$relationship_sender)
                            {
                                if (!empty($relationship_receiver)) {
                                    $try_receicer_relationship = true;
                                }else{
                                    $try_receicer_relationship = false;
                                }
                                if ($try_receicer_relationship) {
                                    for ($i=0; $i < sizeof($relationship_receiver); $i++) { 
                                        $subQuerySender->with($relationship_receiver[$i]);
                                    }
                                }
                            }
                        ])
                        ->with([
                        'sender' => function ($subQuerySender) use ($relationship_sender)
                            {
                                if (!empty($relationship_sender)) {
                                    $try_sender_relationship = true;
                                }else{
                                    $try_sender_relationship = false;
                                }
                                if ($try_sender_relationship) {
                                    for ($i=0; $i < sizeof($relationship_sender); $i++) { 
                                        $subQuerySender->with($relationship_sender[$i]);
                                    }
                                }
                            }
                        ]);
                    }
                ])
                ->with('files')
                ->with([
                'sender' => function ($subQuerySender) use ($relationship_sender)
                    {
                        if (!empty($relationship_sender)) {
                            $try_sender_relationship = true;
                        }else{
                            $try_sender_relationship = false;
                        }
                        if ($try_sender_relationship) {
                            for ($i=0; $i < sizeof($relationship_sender); $i++) { 
                                $subQuerySender->with($relationship_sender[$i]);
                            }
                        }
                    }
                ])
                ->first();
                return json_decode($mail);
            }else{
                $new_message[] = "!is_sender_receiver";

                return $new_message;
            }
        }else{
            $new_message[] = "!is_sender_receiver";

            return $new_message;
        }
    }

    public function mails($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[])
    {
        $mails = EmailInternal::orderBy($colunm_sort,$order_by)
        // ->where('status',1)
        ->where(function ($query_sender) {
            $query_sender->where('sender_id',$this->id)
            ->where('sender_type',(string)get_class($this));
        })
        ->orWhere(function ($query_receiver) {
            $query_receiver->where('receiver_id',$this->id)
            ->where('receiver_type',(string)get_class($this));
        })
        ->with([
        'receiver' => function ($subQuerySender) use ($relationship_receiver)
            {
                if (!empty($relationship_receiver)) {
                    $try_receicer_relationship = true;
                }else{
                    $try_receicer_relationship = false;
                }
                if ($try_receicer_relationship) {
                    for ($i=0; $i < sizeof($relationship_receiver); $i++) { 
                        $subQuerySender->with($relationship_receiver[$i]);
                    }
                }
            }
        ])
        ->with('files')
        ->with([
        'sender' => function ($subQuerySender) use ($relationship_sender)
            {
                if (!empty($relationship_sender)) {
                    $try_sender_relationship = true;
                }else{
                    $try_sender_relationship = false;
                }
                if ($try_sender_relationship) {
                    for ($i=0; $i < sizeof($relationship_sender); $i++) { 
                        $subQuerySender->with($relationship_sender[$i]);
                    }
                }
            }
        ])
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


    public function mails_sended_inbox($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[])
    {
        $mails = EmailInternal::orderBy($colunm_sort,$order_by)
        ->where(function ($query_status) {
            $query_status
            ->where('status',1)
            ->orWhere('status',99);
        })
        ->where('sender_trash',1)
        ->where(function ($query) {
            $query->where('sender_id',$this->id)
            ->where('sender_type',(string)get_class($this));
        })
        ->with([
        'receiver' => function ($subQuerySender) use ($relationship_receiver)
            {
                if (!empty($relationship_receiver)) {
                    $try_receicer_relationship = true;
                }else{
                    $try_receicer_relationship = false;
                }
                if ($try_receicer_relationship) {
                    for ($i=0; $i < sizeof($relationship_receiver); $i++) { 
                        $subQuerySender->with($relationship_receiver[$i]);
                    }
                }
            }
        ])
        ->with('files')
        ->with([
        'sender' => function ($subQuerySender) use ($relationship_sender)
            {
                if (!empty($relationship_sender)) {
                    $try_sender_relationship = true;
                }else{
                    $try_sender_relationship = false;
                }
                if ($try_sender_relationship) {
                    for ($i=0; $i < sizeof($relationship_sender); $i++) { 
                        $subQuerySender->with($relationship_sender[$i]);
                    }
                }
            }
        ])
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

    public function mails_star_inbox($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[])
    {
        // dd($relationship_sender);
        $mails = EmailInternal::orderBy($colunm_sort,$order_by)
        ->where(function ($query_status) {
            $query_status
            ->where('status',1)
            ->orWhere('status',99);
        })
        ->where(function ($query_star)
        {
            $query_star
            ->where(function ($query) {
                $query->where('sender_id',$this->id)
                ->where('sender_type',(string)get_class($this))
                ->where('sender_star',1);
            })
            ->orWhere(function ($query_sub) {
                $query_sub->where('receiver_id',$this->id)
                ->where('receiver_type',(string)get_class($this))
                ->where('receiver_star',1);
            });
        })
        ->with([
        'sender' => function ($subQuerySender) use ($relationship_sender)
            {
                // dd($relationship_sender);
                if (!empty($relationship_sender)) {
                    $try_sender_relationship = true;
                }else{
                    $try_sender_relationship = false;
                }
                if ($try_sender_relationship) {
                    for ($i=0; $i < sizeof($relationship_sender); $i++) { 

                        $subQuerySender->with($relationship_sender[$i]);

                    }
                }
                // dd($subQuerySender->with($relationship_sender[0]));
            }
        ])
        ->with([
        'receiver' => function ($subQuerySender) use ($relationship_receiver)
            {
                // dd($relationship_sender);
                if (!empty($relationship_receiver)) {
                    $try_receicer_relationship = true;
                }else{
                    $try_receicer_relationship = false;
                }
                if ($try_receicer_relationship) {
                    for ($i=0; $i < sizeof($relationship_receiver); $i++) { 
                        $subQuerySender->with($relationship_receiver[$i]);
                    }
                }
            }
        ])
        ->with('files')
        // ->get();
        ->paginate($paginate, ['*'], 'page', $page);

        // dd($mails);

        // if (!empty($relationship_receiver)) {
        //     $try_receicer_relationship = true;
        // }else{
        //     $try_receicer_relationship = false;
        // }

        // if ($try_receicer_relationship) {
        //     foreach ($mails as $key_mail => $value_mail) {
        //         // dd($value_mail->sender_type);
        //         foreach ($relationship_receiver as $sender_model => $model_relationship) {
        //             if ($value_mail->sender_type == $sender_model) {
        //                 // dd($model_relationship);
        //                 for ($i=0; $i < sizeof($model_relationship); $i++) { 
        //                     // dd($model_relationship[$i]);
        //                     $value_mail->with('sender.'.$model_relationship[$i])->first();
        //                     // dd($value_mail);
        //                     $new_data_try = EmailInternal::where('id',$value_mail->id)->with('sender.'.$model_relationship[$i])->first();
        //                     dd($new_data_try);
        //                 }
        //                 // dd($value_mail);
                        
        //                 // $value_mail->with('receiver')->with($model_relationship);
        //             }
        //         }
                
        //     }
        // }
        


        

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

    public function mails_geted_trash($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[],$model_sender=[])
    {
        $status_mail_geter = 0;
        return $this->mails_geted($paginate,$page,$order_by,$colunm_sort,$relationship_sender,$relationship_receiver,$model_sender,$status_mail_geter);
    }

    public function mails_geted_inbox($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[],$model_sender=[])
    {
        $status_mail_geter = 1;
        return $this->mails_geted($paginate,$page,$order_by,$colunm_sort,$relationship_sender,$relationship_receiver,$model_sender,$status_mail_geter);
    }

    public function mails_geted($paginate,$page,$order_by,$colunm_sort,$relationship_sender,$relationship_receiver,$model_sender,$status_mail_geter)
    {
        // dd($paginate,$page,$order_by,$colunm_sort,$relationship_sender,$relationship_receiver,$model_sender,$status_mail_geter);
        $mails = EmailInternal::orderBy($colunm_sort,$order_by)
        ->where(function ($query_cate_mail) use ($status_mail_geter,$model_sender)
        {
            if ($status_mail_geter == 1) {
                $query_cate_mail->whereIn('sender_type',$model_sender);
            }
            
        })
        ->where(function ($query_status) {
            $query_status
            ->where('status',1)
            ->orWhere('status',99);
        })
        ->where(function ($query_in) use ($status_mail_geter)
        {
            $query_in->where(function ($query_in_in) use ($status_mail_geter) {
                $query_in_in->where('receiver_id',$this->id)
                ->where('receiver_type',(string)get_class($this))
                ->where('receiver_trash',$status_mail_geter);
            })
            ->orWhere(function ($query_in_in_sender_sub) use ($status_mail_geter) {
                $query_in_in_sender_sub->where('sender_id',$this->id)
                ->where('sender_type',(string)get_class($this))
                ->where('sender_trash',$status_mail_geter);
            });
        })
        ->with([
        'receiver' => function ($subQuerySender) use ($relationship_receiver)
            {
                if (!empty($relationship_receiver)) {
                    $try_receicer_relationship = true;
                }else{
                    $try_receicer_relationship = false;
                }
                if ($try_receicer_relationship) {
                    for ($i=0; $i < sizeof($relationship_receiver); $i++) { 
                        $subQuerySender->with($relationship_receiver[$i]);
                    }
                }
            }
        ])
        ->with('files')
        ->with([
        'sender' => function ($subQuerySender) use ($relationship_sender)
            {
                if (!empty($relationship_sender)) {
                    $try_sender_relationship = true;
                }else{
                    $try_sender_relationship = false;
                }
                if ($try_sender_relationship) {
                    for ($i=0; $i < sizeof($relationship_sender); $i++) { 
                        $subQuerySender->with($relationship_sender[$i]);
                    }
                }
            }
        ])
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
        // dd($response);
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
        $check_mail = $this->is_sender($id_mail);
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


    public function mail_reply_uploads($id_mail,$files)
    {
        $new_message = [];
        $check_mail = $this->is_sender_reply($id_mail);
        if ($check_mail) {
            $disk = config('email_internal.disk');
            $path_new = $this->new_path($this->id,$id_mail);

            $i = 0;
            if(!empty($files)){
                foreach($files as $file){
                    $imageName = $this->new_name_image($file,$path_new);
                    $new_message[] = $this->storeFileMailReply($file,$path_new,$imageName,$disk,$id_mail);
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

    public function mail_image_file($path)
    {
        // dd($path);
        $file_check = EmailInternalGallery::where('path',$path)->first();
        if (!empty($file_check)) {
            $id_mail = $file_check->mail_id;
            

            if ($this->is_sender_receiver($id_mail)) {
                return $file_check;
            }
        }
    }

    public function mail_reply_image_file($path)
    {
        $file_check = EmailReplyInternalGallery::where('path',$path)->first();
        // dd($file_check);
        if (!empty($file_check)) {
            $id_mail = $file_check->mail_id;
            // dd($this->is_sender_receiver_reply($id_mail));

            if ($this->is_sender_receiver_reply($id_mail)) {
                return $file_check;
            }
        }
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
            // $extension_try = $fileUploaded->extension();
            
            // if ($this->extension_file($extension_try) == "image") {
                
            // }else if($this->extension_file($extension_try) == "doc"){
            //     // dd(is_readable($fileUploaded));
            //     // $Content = \PhpOffice\PhpWord\IOFactory::load($fileUploaded);
            //     // dd($Content);
            //     // $fileUploaded = \PhpOffice\PhpWord\IOFactory::createWriter($Content,'PDF');
            // }else{
            //     return "fail";
            // }

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

            return "done";
            
        }
    }

    public function storeFileMailReply($fileUploaded,$folder,$new_name_file,$disk,$id_mail)
    {
        $folder = $folder;
        $rules = array('file' => 'required|mimes:'.implode(',',config('email_internal.file.extension')));
        
        $validator = Validator::make(array('file' => $fileUploaded), $rules);

        if($validator->passes())
        {
            Storage::disk($disk)->putFileAs(
                $folder,
                $fileUploaded,
                $new_name_file
            );

            $file_new_url = $folder.'/'.$new_name_file;


            $fileProperties = $this->fileProperties($file_new_url,$disk);
            // dd($fileProperties);
            $new_gallery = new EmailReplyInternalGallery;
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

        $linkFilenameTemp = strtolower(time(). date("Y_m") . $number  
            . $this->clean_charecter($fileName)
        );
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

    function clean_charecter($string) {
       $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.

       return preg_replace('/[^A-Za-z0-9\-]/', '_', $string); // Removes special chars.
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

        $new_path = $this->id.'/'.config('email_internal.path_folder').'/'.$this->clean_charecter($token)."/".$this->clean_charecter($id_mail.date("Y-m"))."/";
        return $new_path;

    }

    public function mailto($receiver,$content,$subject,$file,$receiver_type="",$colunm_model="id")
    {
        $new_message = [];
        $type_receiver = gettype($receiver);
        // dd($type_receiver);
        $sender_type = (string)get_class($this);
        // dd($receiver);
        if (!empty($receiver_type)) {}else{
            $receiver_type = $sender_type;
        }
        if ($type_receiver == "array") {
            for ($i=0; $i < count($receiver); $i++) {
                $new_message[] = $this->newMail($this->id,$receiver[$i],$sender_type,$receiver_type,$content,$subject,$file,$colunm_model);
            }
        }

        if ($type_receiver == "object") {
            $new_message[] = $this->newMail($this->id,$receiver->id,$sender_type,(string)get_class($receiver),$content,$subject,$file,'id');
        }

        if ($type_receiver == "string" or $type_receiver == "integer") {
            $new_message[] = $this->newMail($this->id,$receiver,$sender_type,$receiver_type,$content,$subject,$file,$colunm_model);
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
                $update->sender_trash = 0;
                $update->update();

                $new_message[] = "done";
            }
            if($this->is_receiver($id_mail)){
                $update = EmailInternal::find($id_mail);
                $update->receiver_trash = 0;
                $update->update();

                $new_message[] = "done";
            }

            $new_message[] = "!is_sender_receiver";
        }

        if ($id_mail_type == "array") {
            for ($i=0; $i < sizeof($id_mail); $i++) { 
                if ($this->is_sender($id_mail[$i])) {
                    $update = EmailInternal::find($id_mail[$i]);
                    $update->sender_trash = 0;
                    $update->update();

                    $new_message[] = "done";
                }
                if($this->is_receiver($id_mail[$i])){
                    $update = EmailInternal::find($id_mail[$i]);
                    $update->receiver_trash = 0;
                    $update->update();

                    $new_message[] = "done";
                }

                $new_message[] = "!is_sender_receiver";
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
                $update->sender_trash = 1;
                $update->update();

                $new_message[] = "done";
            }
            if($this->is_receiver($id_mail)){
                $update = EmailInternal::find($id_mail);
                $update->receiver_trash = 1;
                $update->update();

                $new_message[] = "done";
            }

            $new_message[] = "!is_sender_receiver";
        }

        if ($id_mail_type == "array") {
            for ($i=0; $i < sizeof($id_mail); $i++) { 
                if ($this->is_sender($id_mail[$i])) {
                    $update = EmailInternal::find($id_mail[$i]);
                    $update->sender_trash = 1;
                    $update->update();

                    $new_message[] = "done";
                }
                if($this->is_receiver($id_mail[$i])){
                    $update = EmailInternal::find($id_mail[$i]);
                    $update->receiver_trash = 1;
                    $update->update();

                    $new_message[] = "done";
                }

                $new_message[] = "!is_sender_receiver";
            }
        }
        
        return $new_message;
    }

    public function mail_delete_force()
    {
        $new_message = [];
        $update_sender = EmailInternal::where('sender_id',$this->id)->where('sender_type',(string)get_class($this))->where('status','<>',99)->get();
        
        if (count($update_sender) < 1) {
            // dd(count($update_sender));
            $new_message[] = "data_null";
        }
        foreach ($update_sender as $detail_update_sender) {
            $detail_update_sender->status = 99;
            $detail_update_sender->update();
            $new_message[] = "done";
        }


        $update_receiver = EmailInternal::where('receiver_id',$this->id)->where('receiver_type',(string)get_class($this))->where('status','<>',99)->get();
        foreach ($update_receiver as $detail_update_receiver) {
            $detail_update_receiver->status = 99;
            $detail_update_receiver->update();
            $new_message[] = "done";
        }

        return $new_message;
    }

    public function mail_unread($id_mail,$timestamp_status=false)
    {
        $new_message = [];
        if ($this->is_sender_receiver_status($id_mail,0)) {
            $update = EmailInternal::find($id_mail);
            if ($update->sender_id == $this->id) {
                $update->sender_read = 1;
                $update->timestamps = $timestamp_status;
                $update->save();
            }
            if ($update->receiver_id == $this->id) {
                $update->receiver_read = 1;
                $update->timestamps = $timestamp_status;
                $update->save();
            }
            $new_message[] = "done";
        }else{
            $new_message[] = "!is_sender_receiver_or_status";
        }

        return $new_message;
    }

    public function mail_read($id_mail,$timestamp_status=false)
    {
        $new_message = [];
        if ($this->is_sender_receiver_status($id_mail,1)) {
            $update = EmailInternal::find($id_mail);
            // dd($update);
            if ($update->sender_id == $this->id) {
                $update->sender_read = 0;
                $update->timestamps = $timestamp_status;
                $update->save();
            }
            if ($update->receiver_id == $this->id) {
                $update->receiver_read = 0;
                $update->timestamps = $timestamp_status;
                $update->save();
            }
            $new_message[] = "done";
        }else{
            $new_message[] = "!is_sender_receiver_or_status";
        }

        return $new_message;
    }

    public function mail_give_star($id_mail,$timestamp_status=false)
    {
        $new_message = [];
        if ($this->is_sender_receiver_status_star($id_mail,0)) {
            $update = EmailInternal::find($id_mail);
            // dd($update);
            if ($update->sender_id == $this->id) {
                $update->sender_star = 1;
                $update->timestamps = $timestamp_status;
                $update->save();
            }
            if ($update->receiver_id == $this->id) {
                $update->receiver_star = 1;
                $update->timestamps = $timestamp_status;
                $update->save();
            }
            $new_message[] = "done";
        }else{
            $new_message[] = "!is_sender_receiver_or_status_star";
        }

        return $new_message;
    }

    public function mail_remove_star($id_mail,$timestamp_status=false)
    {
        $new_message = [];
        if ($this->is_sender_receiver_status_star($id_mail,1)) {
            $update = EmailInternal::find($id_mail);
            // dd($update);
            if ($update->sender_id == $this->id) {
                $update->sender_star = 0;
                $update->timestamps = $timestamp_status;
                $update->save();
            }
            if ($update->receiver_id == $this->id) {
                $update->receiver_star = 0;
                $update->timestamps = $timestamp_status;
                $update->save();
            }
            $new_message[] = "done";
        }else{
            $new_message[] = "!is_sender_receiver_or_status_star";
        }

        return $new_message;
    }

    public function is_sender_receiver_status_star($id_mail,$status)
    {
        $check_sender = EmailInternal::where('id',$id_mail)
        ->where('sender_id',$this->id)
        ->where('sender_type',(string)get_class($this))
        ->where('sender_star',$status)
        ->first();

        $check_receiver = EmailInternal::where('id',$id_mail)
        ->where('receiver_id',$this->id)
        ->where('receiver_type',(string)get_class($this))
        ->where('receiver_star',$status)
        ->first();

        if (!empty($check_sender) or !empty($check_receiver)) {
            return true;
        }else{
            return false;
        }
    }

    public function is_sender_receiver_status($id_mail,$status)
    {
        $check_sender = EmailInternal::where('id',$id_mail)
        ->where('sender_id',$this->id)
        ->where('sender_type',(string)get_class($this))
        ->where('sender_read',$status)
        ->first();

        $check_receiver = EmailInternal::where('id',$id_mail)
        ->where('receiver_id',$this->id)
        ->where('receiver_type',(string)get_class($this))
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
        ->where('sender_type',(string)get_class($this))
        ->first();

        $check_receiver = EmailInternal::where('id',$id_mail)
        ->where('receiver_id',$this->id)
        ->where('receiver_type',(string)get_class($this))
        ->first();

        if (!empty($check_sender) or !empty($check_receiver)) {
            return true;
        }else{
            return false;
        }
    }

    public function is_sender_receiver_reply($id_mail)
    {
        $mail_reply_first = EmailReplyInternal::where('id',$id_mail)->first();

        // dd($mail_reply_first);

        
        if (!empty($mail_reply_first)) {}else{
            return false;
        }
        

        $check_sender = EmailReplyInternal::where('id',$mail_reply_first->id)
        ->where('sender_id',$this->id)
        ->where('sender_type',(string)get_class($this))
        ->first();

        $check_receiver = EmailInternal::where('id',$mail_reply_first->mail_id)
        ->where('receiver_id',$this->id)
        ->where('receiver_type',(string)get_class($this))
        ->first();

        $check_receiver_reply = EmailInternal::where('id',$mail_reply_first->mail_id)
        ->where('sender_id',$this->id)
        ->where('sender_type',(string)get_class($this))
        ->first();

        // dd($check_sender,$check_receiver,$check_receiver_reply,$this,$mail_reply_first);

        if (!empty($check_sender) or !empty($check_receiver) or !empty($check_receiver_reply)) {
            return true;
        }else{
            return false;
        }
    }

    public function is_sender($id_mail)
    {
        $check = EmailInternal::where('id',$id_mail)
        ->where('sender_id',$this->id)
        ->where('sender_type',(string)get_class($this))
        ->first();

        if (!empty($check)) {
            return true;
        }else{
            return false;
        }
    }


    public function is_sender_reply($id_mail)
    {
        $check = EmailReplyInternal::where('id',$id_mail)
        ->where('sender_id',$this->id)
        ->where('sender_type',(string)get_class($this))
        ->first();

        if (!empty($check)) {
            return true;
        }else{
            return false;
        }
    }


    public function is_receiver($id_mail)
    {
        $check = EmailInternal::where('id',$id_mail)
        ->where('receiver_id',$this->id)
        ->where('receiver_type',(string)get_class($this))
        ->first();

        if (!empty($check)) {
            return true;
        }else{
            return false;
        }
    }


    public function newMail($sender_id,$receiver_data,$sender_type,$receiver_type,$content,$subject,$file,$colunm_model)
    {
        // dd($sender_id,$receiver_data,$sender_type,$receiver_type,$content,$subject,$file,$colunm_model);
        $check_model_try = is_subclass_of($receiver_type, 'Illuminate\Database\Eloquent\Model');

        if ($check_model_try) {
            $model_receiver_try = $receiver_type::where($colunm_model,$receiver_data)->first();
            if (!empty($model_receiver_try)) {

                $new_token = hash("ripemd320", $subject . Carbon::now() . $sender_id . $sender_type . $this->generateRandomString());

                $check_new_token = EmailInternal::where('token',$new_token)->first();
                if (!empty($check_new_token)) {
                    $new_token = hash("ripemd320", $subject . Carbon::now() . $sender_id . $sender_type . $this->generateRandomString());
                }
                
                $new_mail = new EmailInternal;
                $new_mail->sender_id = $sender_id;
                $new_mail->receiver_id = $model_receiver_try->id;
                $new_mail->sender_type = $sender_type;
                $new_mail->receiver_type = $receiver_type;
                $new_mail->content = $content;
                $new_mail->subject = $subject;
                $new_mail->file = $file;
                $new_mail->status = 1;
                $new_mail->sender_read = 0;
                $new_mail->sender_trash = 1;
                $new_mail->sender_star = 0;
                $new_mail->receiver_read = 1;
                $new_mail->receiver_trash = 1;
                $new_mail->receiver_star = 0;
                $new_mail->token = $new_token;
                $new_mail->save();
                
                return ["message" => "done", "mail_id" => $new_mail->id];
            }else{
                return ["message" => "receiver_not_found"];
            }
        }else{
            return ["message" => "model_not_found"];
        }
    }


    public function newMailReply($sender_id,$sender_type,$content,$mail_id,$timestamp_status=true)
    {
        $new_mail = new EmailReplyInternal;
        $new_mail->mail_id = $mail_id;
        $new_mail->sender_id = $sender_id;
        $new_mail->sender_type = $sender_type;
        $new_mail->content = $content;
        $new_mail->save();

        $update_readed = EmailInternal::where('id',$mail_id)->first();
        if ($sender_id == $update_readed->sender_id and $sender_type == $update_readed->sender_type) {
            $update_readed->receiver_read = 1;
        }else{
            $update_readed->sender_read = 1;
        }
        $update_readed->timestamps = $timestamp_status;
        $update_readed->update();
        return ["message" => "done", "mail_id" => $new_mail->id];
    }

    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
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
