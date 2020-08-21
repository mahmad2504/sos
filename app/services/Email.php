<?php
namespace App\services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Carbon\Carbon;
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
	public $manager = [];
	function __construct()
	{
		$this->manager['FLEX_12.0.0']=['Noor_Ahsan@mentor.com'];
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
	function SendRiskCreatedNotification($ticket)
	{
		$duedate =  new Carbon();
		$duedate->setTimeStamp($ticket->duedate);
		
		$to = [];
		$cc = [];
		$url = env('JIRA_EPS_URL');
		$ticketurl = '<a href="'.$url.'/browse/'.$ticket->key.'">'.$ticket->key.'</a>';
		$this->mail->Subject = 'Risk/Dependency Notification  ';
		$cc[]=$ticket->reporter['emailAddress'];
		$cc[]='Mumtaz_Ahmad@mentor.com';
		$msg = ' Hi, ';
		
		if($ticket->assignee['name'] == 'none')
		{
			$to[]=$ticket->reporter['emailAddress'];
			$msg .= $ticket->reporter['displayName']."<br><br>";
			$msg .= 'You are receiving this email because Jira dependency  ticket '.$ticketurl ." was created by you<br><br>";
			$msg .= '<span style="color:red">'.'This ticket is not assigned to anybody yet'.'</span><br>';
		}
		else
		{
			$to[]=$ticket->assignee['emailAddress'];
			$msg .= $ticket->assignee['displayName']."<br><br>";
			$msg .= 'You are receiving this email because Jira dependency ticket '.$ticketurl." is assigned to you<br>";
			
		}
		if($ticket->fixVersions[0] == 'none')
		{
			$msg .= '<span style="color:red">'.'This dependency ticket does not have any fix version'.'</span><br>';
		}
		else
		{
			if(isset($this->manager[$ticket->fixVersions[0]]))
			{
				foreach($this->manager[$ticket->fixVersions[0]] as $email)
				$cc[] = $email;
			}
		}
		$msg .= '<br><span style="font-weight:bold;">This ticket is due on '.$duedate->isoFormat('MMMM Do YYYY').'</span><br><br>';
		
		$msg .= "If you think deliverable against this ticket cannot be delivered by due date or ticket is mistakenly assigned to you, then please send an email to ticket reporter ".$ticket->reporter['emailAddress']."<br><br>";
		$msg .= "[THIS IS AN AUTOMATED EMAIL - PLEASE DO NOT REPLY DIRECTLY TO THIS EMAIL]<br>"; 
		$msg .= "For complete  calender please <a href='http://sos.pkl.mentorg.com/riskcalendar'>click here</a>";
		$this->mail->ClearAllRecipients( );
		foreach($to as $add)
		{
			$this->mail->addAddress($add);
		}
		foreach($cc as $add)
		{
			$this->mail->addCC($add);
		}	
		//$this->mail->ClearAllRecipients( );
	    //$this->mail->addAddress('Mumtaz_Ahmad@mentor.com');
		//$msg .= "For complete  calender please <a href='https://sos.pkl.mentorg.com/riskcalendar'>click here</a>";
        $this->mail->Body= $msg;
		try 
		{
			$this->mail->send();
		} 
		catch (phpmailerException $e) 
		{
			echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} 
		catch (Exception $e) 
		{
			echo $e->getMessage(); //Boring error messages from anything else!
		}
		
	}
	function SendRiskClosedNotification($ticket)
	{
		$to = [];
		$cc = [];
		$url = env('JIRA_EPS_URL');
		$ticketurl = '<a href="'.$url.'/browse/'.$ticket->key.'">'.$ticket->key.'</a>';
		$this->mail->Subject = 'Risk/Dependency Notification  ';
		$cc[]=$ticket->reporter['emailAddress'];
		$cc[]='Mumtaz_Ahmad@mentor.com';
		$msg = '';
		if($ticket->assignee['name'] == 'none')
		{
			$to[]=$ticket->reporter['emailAddress'];
			//$msg .= $ticket->reporter['displayName']."<br><br>";
			//$msg .= 'You are receiving this email because Jira ticket '.$ticketurl ." was created by you<br><br>";
		}
		else
		{
			$to[]=$ticket->assignee['emailAddress'];
			//$msg .= $ticket->assignee['displayName']."<br><br>";
			//$msg .= 'You are receiving this email because Jira ticket '.$ticketurl." is assigned to you<br>";
			
		}
		
		$msg .= 'This ticket '.$ticketurl.' is marked closed today <br><br>';
		
		
		$msg .= "[THIS IS AN AUTOMATED EMAIL - PLEASE DO NOT REPLY DIRECTLY TO THIS EMAIL]<br>"; 
		$msg .= "For complete  calender please <a href='http://sos.pkl.mentorg.com/riskcalendar'>click here</a>";
		$this->mail->ClearAllRecipients( );
		foreach($to as $add)
		{
			$this->mail->addAddress($add);
		}
		foreach($cc as $add)
		{
			$this->mail->addCC($add);
		}	
		//$this->mail->ClearAllRecipients( );
		//$this->mail->addAddress('Mumtaz_Ahmad@mentor.com');
		//$msg = "For complete sprint calender please <a href='http://sos.pkl.mentorg.com/sprintcalendar'>click here</a>";
        $this->mail->Body= $msg;
		try 
		{
			$this->mail->send();
		} 
		catch (phpmailerException $e) 
		{
			echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} 
		catch (Exception $e) 
		{
			echo $e->getMessage(); //Boring error messages from anything else!
		}
	}
	function SendRiskReminder($ticket,$delay)
	{
		$duedate =  new Carbon();
		$duedate->setTimeStamp($ticket->duedate);
		
		$to = [];
		$cc = [];
		$url = env('JIRA_EPS_URL');
		$ticketurl = '<a href="'.$url.'/browse/'.$ticket->key.'">'.$ticket->key.'</a>';
		
		$this->mail->Subject = 'Risk/Dependency Notification  ';
		$cc[]=$ticket->reporter['emailAddress'];
		$cc[]='Mumtaz_Ahmad@mentor.com';
		$msg = ' Hi, ';
		if($ticket->assignee['name'] == 'none')
		{
			$to[]=$ticket->reporter['emailAddress'];
			$msg .= $ticket->reporter['displayName']."<br><br>";
			$msg .= 'You are receiving this email because Jira ticket '.$ticketurl ." was created by you<br><br>";
			$msg .= '<span style="color:red">'.'This ticket is not assigned to anybody yet'.'</span><br>';
		}
		else
		{
			$to[]=$ticket->assignee['emailAddress'];
			$msg .= $ticket->assignee['displayName']."<br><br>";
			$msg .= 'You are receiving this email because Jira ticket '.$ticketurl." is assigned to you<br>";
			
		}
		
		if($ticket->fixVersions[0] == 'none')
		{
			$msg .= '<span style="color:red">'.'This ticket does not have any fix version'.'</span><br>';
		}
		else
		{
			if(isset($this->manager[$ticket->fixVersions[0]]))
			{
				foreach($this->manager[$ticket->fixVersions[0]] as $email)
				$cc[] = $email;
			}
		}
		if($delay > 0)
		{
			$msg .= '<br><span style="font-weight:bold;">This ticket is due on '.$duedate->isoFormat('MMMM Do YYYY').' in '.$delay.' days</span><br><br>';
		}
		else if($delay == 0)
		{
			$msg .= '<br><span style="font-weight:bold;">This ticket is due today</span><br><br>';
		}
		else
		{
			$msg .= '<br><span style="color:red">'.'This ticket was due on  '.$duedate->isoFormat('MMMM Do YYYY').' Delayed by '.$delay.' days'.'</span><br><br>';
		}
		$msg .= "If you think deliverable against this ticket cannot be delivered by due date or ticket is mistakenly assigned to you, then please send an email to ticket reporter ".$ticket->reporter['emailAddress']."<br><br>";
		$msg .= "[THIS IS AN AUTOMATED EMAIL - PLEASE DO NOT REPLY DIRECTLY TO THIS EMAIL]<br>"; 
		$msg .= "For complete  calender please <a href='http://sos.pkl.mentorg.com/riskcalendar'>click here</a>";
		$this->mail->ClearAllRecipients( );
		foreach($to as $add)
		{
			$this->mail->addAddress($add);
		}
		foreach($cc as $add)
		{
			$this->mail->addCC($add);
		}	
		//$this->mail->ClearAllRecipients( );
		//$this->mail->addAddress('Mumtaz_Ahmad@mentor.com');
		//$msg .= "For complete  calender please <a href='https://sos.pkl.mentorg.com/riskcalendar'>click here</a>";
        $this->mail->Body= $msg;
		try 
		{
			echo "Sending email";
			$this->mail->send();
		} 
		catch (phpmailerException $e) 
		{
			echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} 
		catch (Exception $e) 
		{
			echo $e->getMessage(); //Boring error messages from anything else!
		}
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
		$msg .= "For complete sprint calender please <a href='http://sos.pkl.mentorg.com/sprintcalendar'>click here</a>";
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