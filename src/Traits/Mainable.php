<?php

namespace Package\Kennofizet\EmailInternal\Traits;

use Package\Kennofizet\EmailInternal\Model\EmailInternal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;


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

    public function mailto($receiver,$content,$subject,$file,$model_receiver="",$colunm_model="id")
    {
        $new_message = [];
        $type_receiver = gettype($receiver);
        // dd($type_receiver);
        $model_sender = (string)get_class($this);
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
        if ($this->is_sender($id_mail)) {
            $update = EmailInternal::find($id_mail);
            $update->content = "Mail deleted";
            $update->subject = "Mail deleted";
            $update->file = "";
            $update->update();

            $new_message[] = "done";
        }else{
            $new_message[] = "!is_sender";
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
            $detail_update_sender->content = "Mail deleted";
            $detail_update_sender->subject = "Mail deleted";
            $detail_update_sender->status = 99;
            $detail_update_sender->file = "";
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
                return "done";
            }else{
                return "receiver_not_found";
            }
        }else{
            return "model_not_found";
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
