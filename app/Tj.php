<?php
namespace App;
use App\Utility;
use App\Jira;
use App\Resource;
use App\ProjectTree;
use App\Calendar;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ProjectController;

class Tj
{
	public function __construct(ProjectTree $projecttree)
	{
		$this->FlushProjectHeader($projecttree->project);
		$this->FlushResourceHeader($projecttree->presources);
	}
	/*function FlushLeavesHeader($head)
	{
		$header = "";
		$calendar = $head->Holidays;
		foreach($calendar as $holiday)
			$header = $header.'leaves holiday "holiday "'.$holiday."\n";
		return $header;
	}*/
	function FlushResourceHeader($presources)
	{
		$header =  "macro allocate_developers ["."\n";
		
		foreach($presources as $presource)
		{
			$presource->name = $presource->resource()->first()->name;
			
			$presource->calendar =  $presource->resource()->first()->calendar()->first()->data;
			$header = $header."   allocate ".$presource->name."\n";
		}
 
		$header = $header."]"."\n";
		$header = $header.'resource all "Developers" {'."\n";
		
		foreach($presources as $presource)
		{
			$calendar = $presource->calendar;
			$calendar = json_decode($presource->calendar);
			$header = $header.'    resource '.$presource->name.'_'.$presource->cc.' "'.$presource->name.'" {'."\n";
			
			foreach($calendar as $obj)
			{
				$days = Utility::DateDiffInDays($obj->startDate,$obj->endDate)+1;
				$header = $header.'       leaves annual '.$obj->startDate." +".$days."d\n"; 
			}
			$header = $header.'       efficiency '.$presource->efficiency."\n"; 			
			$header = $header.'    }'."\n";
		}
		$header = $header.'}'."\n";

		dd($header);
		return $header;
	}
	function FlushProjectHeader(Project $project)
	{
		$today = Utility::GetToday("Y-m-d");
		$start = $project->start;
		$end  =  $project->end;
		if($end == null) // No end defined so schedule from start or from today
		{
			if(strtotime($start) < strtotime($today))
				$header =  'project acs "'.$project->name.'" '.$today;
			else
				$header =  'project acs "'.$project->name.'" '.$start;
		}
		else
		{
			if(strtotime($start) > strtotime($today))
			{
				$header =  'project acs "'.$project->name.'" '.$start;
			}
			else
			{
				if(strtotime($end) > strtotime($today))
					$header =  'project acs "'.$project->name.'" '.$today;
				else
					$header =  'project acs "'.$project->name.'" '.$end;
			}
		}
		$header = $header." +48m"."\n";
		$header = $header.'{ '."\n";
		$header = $header.'   timezone "Asia/Karachi"'."\n";
		$header = $header.'   timeformat "%Y-%m-%d"'."\n";
		$header = $header.'   numberformat "-" "" "," "." 1 '."\n";
		$header = $header.'   currencyformat "(" ")" "," "." 0 '."\n";
		$header = $header.'   now 2017-07-21-01:00'."\n";
		$header = $header.'   currency "USD"'."\n";
		$header = $header.'   scenario plan "Plan" {}'."\n";
		$header = $header.'   extend task { text Jira "Jira"}'."\n";
		$header = $header.'} '."\n";
		return $header;
	}
}