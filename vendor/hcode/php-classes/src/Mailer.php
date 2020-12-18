<?php

namespace Hcode;

use Rain\Tpl;

class Mailer{

  const USERNAME = "email";
  const PASSWORD = "password";
  const NAMEFROM = "Hcode Store";

  private $mail;

  public function __construct($toAddress, $toName, $subject, 
    $tplName, $data = []){

      $config = array(
        "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] ."/views/email/",
        "cache_dir"     => $_SERVER["DOCUMENT_ROOT"] ."/views-cache/",
        "debug"         => false, // set to false to improve the speed
      );
      
      Tpl::configure( $config );
      
      $tpl = new Tpl;
      
      foreach ($data as $key => $value) {
        $tpl->assign($key, $value);
      }

      $html = $tpl->draw($tplName, true);
      
      // inicio PHPMailer
      $this->mail = new \PHPMailer();
      
      $this->mail->isSMTP();
      
      $this->mail->SMTPOptions = array(
        'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,   
        'allow_self_signed' => true
        )
      );
      
      //Enable SMTP debugging
      //SMTP::DEBUG_OFF = off (for production use)
      // SMTP::DEBUG_CLIENT = client messages
      // SMTP::DEBUG_SERVER = client and server messages
      $this->mail->SMTPDebug = 0;
      
      $this->mail->Host = 'smtp.gmail.com';
      
      $this->mail->Port = 587;
      
      $this->mail->SMTPAuth = true;
      
      $this->mail->Username = Mailer::USERNAME;
      
      $this->mail->Password = Mailer::PASSWORD;
      
      $this->mail->setFrom(Mailer::USERNAME, Mailer::NAMEFROM);
      
      $this->mail->addAddress($toAddress, $toName);
      
      $this->mail->Subject = $subject;
      
      $this->mail->msgHTML($html);
      
      $this->mail->AltBody = 'This is a plain-text message body';

      if (!$this->mail->send()) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message sent!';
        //Section 2: IMAP
        //Uncomment these to save your message in the 'Sent Mail' folder.
        #if (save_mail($mail)) {
        #    echo "Message saved!";
        #}
    }

    }

    public function send(){

      return $this->mail->send();

    }

}

?>