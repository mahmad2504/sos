<?php
namespace App;
use Redirect,Response;
class Utility
{
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
	public static function GetJiraConfig($slot)
	{
		return config('jira.servers')[$slot];
	}
	
}