<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
	Class Name 		: 	Email model
	Created By		: 	aayusha k
	Created Date 	: 	25-06-2019

*/
class Email_model extends MY_Model
{
	function send_mail($sub = '', $html = '', $to_mail,$from_mail='',$to_name='',$to_ccc='' ) 
	{
		require_once APPPATH . "/third_party/phpmail/PHPMailerAutoload.php";
		
		$mail = new PHPMailer();
		
		$mail->IsSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = $this->config->item('smtp_debug');

		//Ask for HTML-friendly debug output
		$mail->Debugoutput = $this->config->item('debug_output');
		
		//Set the hostname of the mail server
		$mail->Host = $this->config->item('smtp_host');

		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port = $this->config->item('smtp_port');

		//Whether to use SMTP authentication
		$mail->SMTPAuth = $this->config->item('smtp_auth');
		
		// secure transfer enabled REQUIRED for Gmail
		$mail->SMTPSecure = $this->config->item('smtp_secure'); //tls
		
		//Username to use for SMTP authentication
		$mail->Username = $this->config->item('smtp_user');
		
		//Password to use for SMTP authentication
		$mail->Password = $this->config->item('smtp_pass');


		$mail->SMTPOptions = array (
            'ssl' => array (
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
		
		//Set who the message is to be sent from
		$mail->SetFrom($from_mail, $this->config->item('mail_lable'));
		
		//Set an alternative reply-to address
		$mail->addReplyTo($this->config->item('reply_to'), $this->config->item('mail_lable'));
		
		//Set who the message is to be sent to
		if (is_array($to_mail)) {

			foreach($to_mail as $emails)
			{
				if (!empty($emails['email']) && !empty($emails['full_name'])) {
					$mail->addAddress($emails['email'], $emails['full_name']);
				}
			}

		}else{
			//$to_array = explode(',', $to);
			$mail->addAddress($to_mail, $to_name);
		}
		
		if (is_array($to_ccc)) {

			foreach($to_ccc as $emails)
			{
				if (!empty($emails['email']) && !empty($emails['full_name'])) {

					$mail->addCC($emails['email'], $emails['full_name']);
				}
			}

		}

		
		
		//
		//$mail->addCC('dd@gmail.com', $this->config->item('mail_lable'));
		$mail->addBCC($this->config->item('add_cc'), $this->config->item('mail_lable'));
		//$mail->addBCC($from_mail, $this->config->item('mail_lable'));

		// to send email to CRM in BCC 
		/*$check_string = "SPOCHUB : Thank you for your payment for AA+ Covid-19 Testing Solution.";

		if(strpos($sub, $check_string) !== false){
			
			$mail->addBCC($this->config->item('crm_email'), $this->config->item('mail_lable'));
		}*/

		//Set the subject line
		$mail->Subject = stripslashes($sub);
		
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$mail->Body = $html;
		$mail->IsHTML(true);
		// $mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
		
		// Replace the plain text body with one created manually
		// $mail->AltBody = 'This is a plain-text message body';
		
		//Attach an image file
		// $mail->addAttachment('images/phpmailer_mini.png');
		
		//send the message, check for errors
		if (!$mail->send()) {
			//echo "Mailer Error: " . $mail->ErrorInfo;
			return false;
		} else {
			return true;
		}
	}
}
?>