# package-email-internal

install : 
  ```
  + composer require kennofizet/email-internal
  + php artisan migrate
  + use Package\Kennofizet\EmailInternal\Traits\MainAble as EmailInternal; // model
  + use EmailInternal;
  ```
use : v2
```
  $model->
          mail_news_active($limit = 6,$page = 1,$colunm_sort="updated_at",$order_by="DESC",$relationship_sender=[]);
          count_mail_news_active();
          mail_reply($data,$content,$colunm_get="token");
          mail_search($key='',$colunm_search="subject",$relationship_sender=[],$relationship_receiver=[]);
          mail($data_get,$colunm_get="id",$relationship_sender=[],$relationship_receiver=[]);
          mails($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[]);
          mails_sended_inbox($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[]);
          mails_star_inbox($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[]);
          mails_geted_trash($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[],$model_sender=[]);
          mails_geted_inbox($paginate=5,$page=1,$order_by="DESC",$colunm_sort="updated_at",$relationship_sender=[],$relationship_receiver=[],$model_sender=[]);
          mails_geted($paginate,$page,$order_by,$colunm_sort,$relationship_sender,$relationship_receiver,$model_sender,$status_mail_geter);
          mail_update_file($id_mail,$file);
          mail_uploads($id_mail,$files);
          mail_reply_uploads($id_mail,$files);
          mail_image_file($path);
          mail_reply_image_file($path);
          canViewFile($path);
          canEditFile($path);
          checkMailFound($id_mail);
          storeFileMail($fileUploaded,$folder,$new_name_file,$disk,$id_mail);
          storeFileMailReply($fileUploaded,$folder,$new_name_file,$disk,$id_mail);
          shareFileMail($id_mail,$gallery_id);
          fileProperties($path = null,$disk);
          new_name_image($file,$folder,$number="1");
          new_path($id_user,$id_mail,$randomLength=15);
          mailto($receiver,$content,$subject,$file,$receiver_type="",$colunm_model="id");
          mail_edit($content,$subject,$file,$id_mail);
          mail_delete($id_mail);
          mail_revert($id_mail);
          mail_delete_force();
          mail_unread($id_mail,$timestamp_status=false);
          mail_read($id_mail,$timestamp_status=false);
          mail_give_star($id_mail,$timestamp_status=false);
          mail_remove_star($id_mail,$timestamp_status=false);
          is_sender_receiver_status_star($id_mail,$status);
          is_sender_receiver_status($id_mail,$status);
          is_sender_receiver($id_mail);
          is_sender_receiver_reply($id_mail);
          is_sender($id_mail);
          is_sender_reply($id_mail);
          is_receiver($id_mail);
          newMail($sender_id,$receiver_data,$sender_type,$receiver_type,$content,$subject,$file,$colunm_model);
          newMailReply($sender_id,$sender_type,$content,$mail_id,$timestamp_status=true);
          updateMail($content,$subject,$file,$id_mail);
```
use : v1
  ```
  //send mail
  $model->mailto(['value1','value2', 'value3'],'content','subject',['link_file'],'class_model_receiver','colunm_model_receiver');
 
  example: 
    + 
      $model_user = \App\User::find(1900);
      $model_user->mailto(['email1@abc.xyz','email2@abc.xyz'],'Content test','Subject test',['link_to_image'],'App\BlogUserRegisterNews','email');
      or
      $model_user->mailto("email1@abc.xyz",'Content test','Subject test',['link_to_image'],'App\BlogUserRegisterNews','email');
      or
      $model_user->mailto("email1@abc.xyz",'Content test','Subject test',['link_to_image'],'','email');//default class_model_receiver is model send mail
      or
      $model_user->mailto(2 ,'Content test','Subject test',['link_to_image']);//default colunm_model_receiver is id
  
  //delete this mail
  $model->mail_delete(id_mail)
  example:
    + $model_user->mail_delete(12);
  *delete just change content, subject to text "Mail deleted"", file to null and status to 99*
  
  //delete all mail this user send and receiver
  $model->mail_delete_force()
  example:
    + $model_user->mail_delete_force();
  *delete just change content, subject to text "Mail deleted"", file to null and status to 99*
  
  //read and unread mail
  $model->mail_read(id_mail)
  $model->mail_unread(id_mail)
  
  //show data
  $model->mail(mail_id)
  $model->mails(perpage,page)
  ```
