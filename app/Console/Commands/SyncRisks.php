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
		$jql = 'labels = risk and duedate >=  '.$start->format('Y-m-d');
		$jira =  new Jira();
		$tickets = $jira->Sync($jql,null);
		
		$now = Carbon::now();
		
		foreach($tickets as $ticket)
		{
			$duedate = new Carbon();
			$duedate->setTimeStamp($ticket->duedate);
			$duedate->hour = 18;
			if($ticket->statuscategory != 'RESOLVED')
			{
				$delay = $duedate->diffInHours($now);
				if($duedate->getTimeStamp() > $now->getTimeStamp())
				{
					// Not delayed
					if($delay <= 24)
						$delay = 0;
					else if($delay <= 48)
						$delay = 2;
					else if($delay <= 72)
						$delay = 3;
					else if($delay <= 96)
						$delay = 4;
					else if($delay <= 120)
						$delay = 5;
					else if($delay <= 144)
						$delay = 6;
					else if($delay <= 168)
						$delay = 7;
					else if($delay <= 192)
						$delay = 8;
					
					if($delay <= 8)
					{
						if(($delay % 2)==0)
						{
							$this->SendEmail($ticket,$delay);
						}
					}
				}
				else
				{
					if($delay >= 24)
						$delay = round($delay / 24);
					else
						$delay = 1;
					
					
					if(($delay % 2)==0)
					{
						$this->SendEmail($ticket,$delay*-1);
					}
				}
			}
			else
			{ //Resolved
				if(file_exists('ticketdata/'.$ticket->key))
					unlink('ticketdata/'.$ticket->key);
			}
		}
		echo $jql;
    }
	function SendEmail($ticket,$delay)
	{
		//$email =  new Email();
		//$email->SendRiskReminder($ticket,['Mumtaz_Ahmad@mentor.com'],['Mumtaz_Ahmad@mentor.com']);
		//return;
		
		$now = Carbon::now()->format('Y-m-d');
		if(file_exists('ticketdata/'.$ticket->key))
		{
			$data = json_decode(file_get_contents('ticketdata/'.$ticket->key));
		
			$ticket->emails = $data->emails;
			
			if(!isset($ticket->emails->$now))
			{
				echo "Sending email for ".$ticket->key." delay is ".$delay."\n";
				
				$email =  new Email();
				$email->SendRiskReminder($ticket,$delay);
				$ticket->emails[$now] = 1;
				
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