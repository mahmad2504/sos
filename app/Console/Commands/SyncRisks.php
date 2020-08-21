<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Http\Request;
use App\Jira\Jira;
use Carbon\Carbon;
use App\services\Email;
class SyncRisks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:risks  {--beat=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
	 
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$minutes = $this->option('beat');
		if($minutes % 60 == 0)// Every 60 minutes
		{ }
		else
			return;
		
		$start = Carbon::now();
		$start->subDays(90);
		
		$dt = new \DateTime();
		$jql = 'labels in (risk,milestone) and duedate >=  '.$start->format('Y-m-d');
		$jira =  new Jira();
		
		$tickets = $jira->Sync($jql,null);
	
		$now = Carbon::now();
		$now->hour = 00;
		$now->minute =00;
		foreach($tickets as $ticket)
		{
			$duedate = new Carbon();
			$duedate->setTimeStamp($ticket->duedate);
			$duedate->hour = 23;
			$duedate->minute = 59;
			if($ticket->statuscategory != 'RESOLVED')
			{
				$this->SendEmail($ticket,null);
				$delay = $duedate->diffInDays($now);
				dump($ticket->key);
				dump($delay);
				if($duedate->getTimeStamp() > $now->getTimeStamp())
				{
					if($delay <= 8)
					{
						if(($delay==8) || ($delay==6) || ($delay==4) || ($delay==2) ||($delay==0))
							$this->SendEmail($ticket,$delay);
					}
				}
				else
				{
					if((($delay%2) == 0)||($delay ==0)) 					
						$this->SendEmail($ticket,$delay*-1);
				}
			}
			else
			{ //Resolved
				$email =  new Email();
		        //echo "Closed ".$ticket->key."\n";
				//if($ticket->key == 'SB-15218')
					//$email->SendRiskClosedNotification($ticket);
				if(file_exists('ticketdata/'.$ticket->key))
				{
					$email =  new Email();
					unlink('ticketdata/'.$ticket->key);
					$email->SendRiskClosedNotification($ticket);
					
				}
			}
		}
		echo $jql;
    }
	function SendEmail($ticket,$delay)
	{
		//$email =  new Email();
		//$email->SendRiskReminder($ticket,['Mumtaz_Ahmad@mentor.com'],['Mumtaz_Ahmad@mentor.com']);
		//return;
		if ($delay === null)
		{
			if(!file_exists('ticketdata/'.$ticket->key))
			{
				$email =  new Email();
				$email->SendRiskCreatedNotification($ticket);
				$now = Carbon::now()->format('Y-m-d');
				$ticket->emails[$now] = 1;
				file_put_contents('ticketdata/'.$ticket->key,json_encode($ticket));
			}
			return;
		}
	
		$now = Carbon::now()->format('Y-m-d');
		if(file_exists('ticketdata/'.$ticket->key))
		{
			echo "Sending email for ".$ticket->key." delay is ".$delay."\n";
			$data = json_decode(file_get_contents('ticketdata/'.$ticket->key));
			$ticket->emails = $data->emails;
			if(!isset($ticket->emails->$now))
			{
				echo "Sending email for ".$ticket->key." delay is ".$delay."\n";
				
				$email =  new Email();
				$email->SendRiskReminder($ticket,$delay);
				$ticket->emails->$now = 1;
				
				file_put_contents('ticketdata/'.$ticket->key,json_encode($ticket));
			}
		}
		else
		{
			echo "Sending email for ".$ticket->key." delay is ".$delay."\n";
			$ticket->emails = [];
			$email =  new Email();
			$email->SendRiskReminder($ticket,$delay);
			$ticket->emails[$now] = 1;
			file_put_contents('ticketdata/'.$ticket->key,json_encode($ticket));
		}
		//file_put_contents('ticketdata/'.$ticket->key,json_encode($ticket));
	}
}