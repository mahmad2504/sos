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
		
		$jql = 'labels in (risk,milestone) and duedate >=  '.$start->format('Y-m-d').'  ORDER By duedate ASC';
		$jira =  new Jira();
		$tickets = $jira->Sync($jql,null);
		$versions = [];
		$versions[] = 'all';
		foreach($tickets as $ticket)
		{
			$duedate = new Carbon();
			$duedate->setTimeStamp($ticket->duedate);
			$ticket->delayed = 0;
			if($ticket->statuscategory != 'RESOLVED')
			{
				if($duedate->getTimeStamp() < $now->getTimeStamp())
				{
					$ticket->delayed = $duedate->diffInDays($now);
				}
			}
			else //If resolved then find how much delayed
			{	
				$resolutiondate = new Carbon();
				$resolutiondate->setTimeStamp($ticket->resolutiondate);
				
				if($duedate->getTimeStamp() < $resolutiondate->getTimeStamp())
					$ticket->delayed = $duedate->diffInDays($resolutiondate);
			}
			foreach($ticket->fixVersions as &$fixVersion)
			{
				$fixVersion = strtolower($fixVersion);
				$versions[$fixVersion] = $fixVersion;
				break;
			}
							
			$ticket->duedate = $duedate->format('Y-m-d');
			$ticket->dueday = $duedate->format('d');
			$ticket->dueweek=$duedate->isoWeekYear()."_".$duedate->isoWeek();
		}
		
		$url = env('JIRA_EPS_URL');
		$versions = array_values($versions);
		return View('services.riskcalendar',compact('tabledata','tickets','url','versions'));
	}
	public function ShowEpicDetails(Request $request)
	{
		if($request->jql== null)
		{
			dd("Invalid query");
		}
		$jql = $request->jql;
		$jira =  new Jira();
		$tickets = $jira->Sync($jql,null,'IESD');
		foreach($tickets as $ticket)
		{
			if($ticket->issuetype == 'epic')
			{
				$jql = '"Epic Link" = '.$ticket->key;
				$stickets = $jira->Sync($jql,null,'IESD');
				if(count($stickets) > 0)
				{
					$ticket->timeoriginalestimate  = 0;
					$ticket->timespent  = 0;
					foreach($stickets as $child)
					{
						$ticket->timeoriginalestimate += $child->timeoriginalestimate;
						$ticket->timespent += $child->timespent;
					}
				}
			}
			echo $ticket->key." ".$ticket->issuetype." Estimate=".round($ticket->timeoriginalestimate/(60*60*8),2)." Days  Timespent=".round($ticket->timespent/(60*60*8),2)."Days<br>";
		}
		
		//return View('services.riskcalendar',compact('tabledata','tickets','url'));
	}
}
