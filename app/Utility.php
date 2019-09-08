<?php
namespace App;
use Redirect,Response;
use App;
use  App\Project;
class Utility
{
	public static function  IsVleocityLow($cv,$rv)
	{
		if($cv < (85/100)*$rv)
			return 1;
		return 0;
	}
	public static function   IsItHoliday($date)
	{
		$day = Date('D',strtotime($date));
		if($day == 'Sat' || $day == 'Sun')
			return 1;
		return 0;
	}
	public static function IsItFutureDate($date)
	{
		if(strtotime(Utility::GetToday('Y-m-d'))<strtotime($date))
			return 1;
		return 0;
	}
	public static function  DateRange($start,$end)
	{
		$ret = new \StdClass();
		$totaldays = 0;
		$remaingdays = 0;
		$data = array();
		$begin = new \DateTime($start);
		$end = date('Y-m-d', strtotime('+1 day', strtotime($end)));
		$end = new \DateTime($end);
		$interval = \DateInterval::createFromDateString('1 day');
		$period = new \DatePeriod($begin, $interval, $end);
		//iterator_count($period);
		foreach ( $period as $dt )
		{
			
			$date = $dt->format("Y-m-d");
			$day = Date('D',strtotime($date));
		
			$data[$date] =  new \StdClass();
			$data[$date]->holiday = Utility::IsItHoliday($date);
			if($data[$date]->holiday ==0) //  working day
			{
				$totaldays++;
				if(Utility::IsItFutureDate($date))
					$remaingdays++;
			}
			//echo $dt->format("Y-m-d").EOL;
		}
		$ret->data = $data;
		$ret->totaldays = $totaldays;
		$ret->remaingdays = $remaingdays;
		return $ret;
	}
	public static function array_insert(&$array, $position, $insert)
	{
		if (is_int($position)) {
			array_splice($array, $position, 0, $insert);
		} else {
			$pos   = array_search($position, array_keys($array));
			$array = array_merge(
				array_slice($array, 0, $pos),
				$insert,
				array_slice($array, $pos)
			);
		}
	}
	public static function DateDiffInDays($date1, $date2)  
	{ 
		// Calulating the difference in timestamps 
		$diff = strtotime($date2) - strtotime($date1); 
		  
		// 1 day = 24 hours 
		// 24 * 60 * 60 = 86400 seconds 
		return abs(round($diff / 86400)); 
	} 
	public static function GetDataPath($user,$project)
	{
		if(strpos(getcwd(),'public')==false)
			return 'public/storage/'.$user->name.'/'.$project->id;
		return 'storage/'.$user->name.'/'.$project->id;
	}
	public static function Error($message)
	{
		return Response::json(array(
                    'code'      =>  401,
                    'message'   =>  $message
                ), 401);
	}
	public static function GetToday($format)
	{
		//return "2017-08-12";
		return Date($format);
	}
	public static function ConsoleLog($id , $msg) 
    {
		if(App::runningInConsole())
		{
			echo $msg."\n";
			return;
		}
		
    	$msg = str_replace('"', "'", $msg);
    	
		echo "id: $id" . PHP_EOL;
		echo "data: {\n";
		echo "data: \"msg\": \"$msg\", \n";
		echo "data: \"id\": $id\n";
		echo "data: }\n";
		echo PHP_EOL;
		ob_flush();
		flush();
	}
	public static function GetJiraURL(Project $project)
	{
		return Self::GetJiraConfig($project)['uri'];
		
	}
	public static function GetJiraConfig(Project $project)
	{
		$slot = $project->jirauri;
		return config('jira.servers')[$slot];
	}
	public static function GetCountryInfo($timezone)
	{
		$timezone = trim($timezone);
		$data = config('calendar.coutryinfo')[0];
		if(isset($data[$timezone]))
		{
			
			return $data[$timezone];
		}
		else
		{
			return $data['X'];
		}
	}
	public static function GetAllCountryInfo()
	{
		return config('calendar.coutryinfo')[0];
	}
	public static function GetOAConfig()
	{
		return config('openair');
		
	}
	
	
}