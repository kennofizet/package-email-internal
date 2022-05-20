# package-email-internal

install : 
  ```
  + composer require kennofizet/email-internal
  + php artisan migrate
  ```
use :
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
