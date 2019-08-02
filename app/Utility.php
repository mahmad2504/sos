<?php
namespace App;
use Redirect,Response;
use App;
use  App\Project;
class Utility
{
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