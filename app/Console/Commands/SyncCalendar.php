<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\services\Calendar;
use Carbon\Carbon;
use App\services\Email;

class SyncCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:calendar {--beat=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync';

    /**
     * Create a new command instance.
     *
     * @return void
     */
	
    public function __construct()
    {
		date_default_timezone_set('Asia/Karachi');
        parent::__construct();
    }
	
    public function handle()
    {
		$minutes = $this->option('beat');
		if($minutes % 10 != 0)
			return;
		
		file_get_contents("https://script.google.com/macros/s/AKfycbwCNrLh0BxlYtR3I9iW2Z-4RQK88Hryd4DEC03lIYLoLCce80A/exec?func=alive&device=sos");
		
		$start = Carbon::now();
		$curenthours =$start->format('G');
		
		$end = Carbon::now();
		$end->addDays(10);
		
		
		$start = Carbon::now();
		$start->subDays(63);
		$end = Carbon::now();
		$end=  $end->addDays(365*4);
		
		$cal = new Calendar($start,$end);
		$sstart = Carbon::parse($cal->GetCurrentSprintStart()->date);
		$send =   Carbon::parse($cal->GetCurrentSprintEnd()->date);
		$send->subDays(2);///since sprint closes on friday and now sunday
		$sprint = $cal->GetCurrentSprint();
		$path = 'public/storage/events';
		if(!file_exists($path))
		{
			mkdir($path, 0777, true);
		}
		
		if($sstart->IsToday())
		{
			$name = $path."/".$sprint."_1";
			if(($curenthours*1 >= 9)&&(!file_exists($name)))
			{
				$email =  new Email();
				$email->SendSprintReminder($sprint,1);
				file_put_contents($name,"sent");
				echo "Sent email eminder for start of sprint ".$sprint;
			}
		}
		if($send->IsToday())
		{
			$name = $path."/".$sprint."_0";
			if(($curenthours*1 >= 17)&&(!file_exists($name)))
			{
				$email =  new Email();
				$email->SendSprintReminder($sprint,0);
				file_put_contents($name,"sent");
				echo "Sent email eminder for closure of sprint ".$sprint;
			}
		}
		echo "Sprint Calender event notifications Job done";
		//$email =  new Email();
		//$email->SendSprintReminder($sprint,1);
		
		//if($start->IsToday())
		//{
			
		//}
    }
}
