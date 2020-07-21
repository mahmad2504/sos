<?php
namespace App\services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email
{
	public $addresses = [
		'mumtaz_ahmad@mentor.com',
		'Khula_Azmi@mentor.com',
		'Noor_Ahsan@mentor.com',
		'Bikram_Bhola@mentor.com',
		'Fakhir_Ansari@mentor.com',
		'mohamed_hussein@mentor.com',
		'Cedric_Hombourger@mentor.com',
		'Waqar_Humayun@mentor.com',
		'Srikanth_Krishnakar@mentor.com',
		'Atif_Raza@mentor.com',
		'Unika_Laset@mentor.com',
		'Mona_Desouky@mentor.com',
		'Rizwan_Rasheed@mentor.com',
		'Muhammad_Shafique@mentor.com'
	];
	function __construct()
	{
		$this->mail = new PHPMailer(true);	
		$this->mail->isSMTP();     
		$this->mail->Host = 'localhost';
		$this->mail->SMTPAuth = false;
		$this->mail->SMTPAutoTLS = false; 
		$this->mail->Port = 25; 
		$this->mail->Username   = 'support-bot@mentorg.com'; 
		$this->mail->setFrom('support-bot@mentorg.com', 'Support Bot');
		$this->mail->addAddress('mumtaz_ahmad@mentor.com');     // Add a recipient
		//$this->mail->addBCC("mumtazahmad2504@gmail.com", "Mumtaz Ahmad");
		$this->mail->addReplyTo('mumtaz_ahmad@mentor.com', 'Support Bot');
		
		$this->mail->isHTML(true);  
	}
	function SendSprintReminder($sprint_name, $start)
	{
		if($start)
			$this->mail->Subject = 'Notification Sprint '.$sprint_name.': Starts today';
		else
			$this->mail->Subject = 'Notification Sprint '.$sprint_name.' Close today';
			
		$msg = $this->mail->Subject.'<br>';
		
		$msg .= '<br>';
		$msg .= '<br>';
		$msg .= "This is an auto generated notification so please donot reply to this email<br>";
		$msg .= "If you are not interested in these notifications, please send an email to mumtaz_ahmad@mentor.com".'<br>';
		$msg .= '<br>';
		$msg .= "For complete sprint calender please <a href='https://sos.pkl.mentorg.com/sprintcalendar'>click here</a>";
        $this->mail->Body= $msg;
		//$this->mail->AltBody =$msg;
		//echo $msg;
		foreach($this->addresses as $address)
		{
		
			$this->mail->ClearAllRecipients( );
			$this->mail->addAddress($address);     // Add a recipient
	
			try {
				$this->mail->send();
			} 
			catch (phpmailerException $e) 
			{
				echo $e->errorMessage(); //Pretty error messages from PHPMailer
			} 
			catch (Exception $e) {
				echo $e->getMessage(); //Boring error messages from anything else!
			}
			echo "Sending notification to ".$address."\r\n";
		}
		//echo "Email sent for Time to resolution  alert for ".$ticket->key."\n";
		//echo 'MRC Approval mail  sent';
	}
}