<?php
namespace App;
use App\Utility;


$estimation_method = 1; //'STORYPOINTS';
$estimation_method = 2; //'TIME ESATIMATE';
$estimation_method = 0; //'BOTH with priority to story points';

$estimation_method = 1;

$unestimated_count = 0;
class Jira
{
	static $url=null;
	static $curl=null;
	static $path=null;
	static $rebuild=0;
	public static function Initialize($url,$user,$pass,$path,$rebuild)
	{
		self::$curl = curl_init();
		curl_setopt_array(self::$curl, array(
			CURLOPT_USERPWD => $user.':'.$pass,
			CURLOPT_RETURNTRANSFER => true
		));
		self::$path = $path;
		self::$url = $url. '/rest/api/latest/';
		self::$rebuild = $rebuild;
	}
	public static function Search($query,$maxresults=1000,$fields=null)
	{
		$filename = self::$path."/".md5($query);
		$last_update_date = '';
		$tasks = new \StdClass();
		if(file_exists($filename)&&self::$rebuild==0)
		{
			$tasks = json_decode(file_get_contents($filename));
			$last_update_date = ' and updated>"'.date ("Y/m/d H:i" , filemtime($filename)).'"';
		}
		$query .= $last_update_date.' ORDER BY Rank ASC ';
		
		$query = str_replace(" ","%20",$query);
		
		$resource=self::$url."search?jql=".$query.'&maxResults='.$maxresults;
		
		if($fields != null)
			$resource.='&fields='.$fields;
		
		$utasks =  self::GetJiraResource($resource);
		//print_r($tasks);
		
		foreach($utasks as $key=>$utask)
			$tasks->$key = $utask;
		
		file_put_contents( $filename, json_encode( $tasks ) );
		$tasks = json_decode(file_get_contents($filename));
		return $tasks;
	}
	public static  function GetJiraResource($resource) 
	{
		$curl = self::$curl;
		//echo $resource;
		curl_setopt($curl, CURLOPT_URL,$resource);
		$result = curl_exec($curl);
		$ch_error = curl_error($curl); 
		$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
	
		if ($ch_error)
		{
			Utility::ConsoleLog(time(),'Error::'.$ch_error);
			exit();
			return [];
		}
		else if($code == 200)
		{
			
			$data = json_decode($result,true);
			
			$tasks = array();
			
			if(isset($data["issues"]))
			{
				if(count($data["issues"])==0)
				{
					return $tasks;
				}
				
				foreach($data["issues"] as $task)
				{
					$tasks[$task['key']] = $task;
				}
				return $tasks;
			}
			return $tasks;
		}
		else
		{
			Utility::ConsoleLog(time(),"Error::Code - ".$code);
			Utility::ConsoleLog(time(),"Check Jira Query");
			exit();
			return [];
		}
		//$data = json_decode($result);
		//var_dump($data);
	}
}

?>