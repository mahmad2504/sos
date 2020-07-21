<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect,Response;
use Carbon\Carbon;
use App\services\Calendar;
use App\Jira\Jira;
class ServicesController extends Controller
{
 	public function ShowCalendar(Request $request)
	{
		$start = Carbon::now();
		$start->subDays(63);
		$end = Carbon::now();
		$end=  $end->addDays(500);
		
		//ob_start('ob_gzhandler');

		$calendar =  new Calendar($start,$end);
		$tabledata = $calendar->GetGridData();
		
		return View('services.sprintcalendar',compact('tabledata'));
	}
	public function ShowRisksCalendar(Request $request)
	{
		$now = Carbon::now();
		$start = Carbon::now();
		$start->subDays(90);
		$end = Carbon::now();
		$end=  $end->addDays(365);
		$calendar =  new Calendar($start,$end);
		$tabledata = $calendar->GetGridData();
		
		$jql = 'labels = risk and duedate >=  '.$start->format('Y-m-d');
		$jira =  new Jira();
		$tickets = $jira->Sync($jql,null);
		foreach($tickets as $ticket)
		{
			$carbon = new Carbon();
			$carbon->setTimeStamp($ticket->duedate);
			$ticket->expired = 0;
			if($carbon < $now)
			{
				$ticket->expired = $carbon->diffInDays($now);
			}
			$ticket->duedate = $carbon->format('Y-m-d');
			$ticket->dueday = $carbon->format('d');
			$ticket->dueweek=$carbon->isoWeekYear()."_".$carbon->isoWeek();
		}
		$url = env('JIRA_EPS_URL');
		return View('services.riskcalendar',compact('tabledata','tickets','url'));
	}
}
