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
	static $path=null;
	static $rebuild=0;
	static $user = null;
	static $pass =  null;
	public static function Initialize($jiraconfig,$path,$rebuild=0)
	{
		$url = $jiraconfig['uri'];
		$user = $jiraconfig['username'];
		$pass = $jiraconfig['password'];

		self::$path = $path;
		self::$url = $url;
		self::$rebuild = $rebuild;
		self::$user = $user;
		self::$pass = $pass;
	}
	public static function Search($query,$maxresults=1000,$fields=null,$order=null)
	{
		$filename = self::$path."/".md5($query);
		$last_update_date = '';
		$tasks = new \StdClass();
		if(file_exists($filename)&&self::$rebuild==0)
		{
			$tasks = json_decode(file_get_contents($filename));
			$last_update_date = ' and updated>"'.date ("Y/m/d H:i" , filemtime($filename)).'"';
		}
		$query .= $last_update_date.' '.$order;

		$query = str_replace(" ","%20",$query);

		$resource=self::$url.'/rest/api/latest/'."search?jql=".$query.'&maxResults='.$maxresults;

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
	public static  function GetJiraResource($resource,$data=null)
	{
		//echo $resource."<br>";
		$curl = curl_init();
		//curl_reset($curl);
		curl_setopt_array($curl, array(
		CURLOPT_USERPWD => self::$user.':'.self::$pass,
		CURLOPT_URL =>$resource,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => array('Content-type: application/json')));

		if($data != null)
		{
			curl_setopt_array($curl, array(
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $data
				));
		}
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
			if(isset($data["worklogs"]))
			{
				return $data["worklogs"];
			}
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
			else if(isset($data['forestUpdates']))
			{
				return $data['forestUpdates'][1]['formula'];
			}
			return $data;
		}
		else
		{
			//dd($result);
			Utility::ConsoleLog(time(),"Error::Code - ".$code);
			Utility::ConsoleLog(time(),"Check Jira Query");
			exit();
			return [];
		}
		//$data = json_decode($result);
		//var_dump($data);
	}
	static  function GetWorkLogs($key)
	{
		//echo "Getting worklogs of ".$key."<br>";
		$resource=self::$url.'/rest/api/latest/issue/'.$key.'/worklog';
		$worklogs = self::GetJiraResource($resource);
		$data = [];
		foreach($worklogs as $worklog)
		{
			//dd($worklog);
			$obj =  new \StdClass();
			$date = explode('T', $worklog['started'])[0];
			$hours =  round($worklog['timeSpentSeconds']/(60*60),1);
			$author = $worklog['author']['name'];
			$comment = $worklog['comment'];
			if(isset($data[$date][$author]))
			{
				$data[$date][$author]->hours += $hours;
				$data[$date][$author]->comment = '#$&@'.$comment;
			}
			else
			{
				$data[$date][$author] =  new \StdClass();
				$data[$date][$author]->hours = $hours;
				$data[$date][$author]->name = $worklog['author']['name'];
				$data[$date][$author]->displayname =  $worklog['author']['displayName'];
				$data[$date][$author]->email =  $worklog['author']['emailAddress'];
				$data[$date][$author]->timeZone =  $worklog['author']['timeZone'];
				$data[$date][$author]->comment = $comment;
			}
		}
		return $data;
	}
	static  function GetStructure($structid)
	{
		$jdata = '{"forests":[{"spec":{"type":"clipboard"},"version":{"signature":898732744,"version":0}},{"spec":{"structureId":'.$structid.',"title":true},"version":{"signature":0,"version":0}}],"items":{"version":{"signature":-157412296,"version":43401}}}';
		$resource=self::$url.'/rest/structure/2.0/poll';
		$formula = self::GetJiraResource($resource,$jdata );
		$formula_array = explode(",",$formula);
		$objects = array();
		foreach($formula_array as $formula)
		{
			$detail = explode(":",$formula);
			$obj = new \StdClass();

			$obj->rwoid = $detail[0];
			$obj->level = $detail[1];
			$obj->taskid = $detail[2];
			if(strpos($detail[2], "/")>0)
			{}
			else
			{
				$objects[$obj->taskid] = $obj;
			}
		}
		return $objects;
	}

}

?>
