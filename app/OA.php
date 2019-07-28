<?php

namespace App;
use App\OpenAir\OpenAir;
use App\OpenAir\Auth;
use App\Utility;
use App\ProjectTree;
class OA
{
	public $planned_hours = 0;
	public $approved_hours = 0;
	public $submitted_hours= 0;
	public $worklogs = [];
	public $users = [];
	public $projecttree = null;
	public function __construct(ProjectTree $projecttree)
	{
		if(isset($projecttree->oa))
		{
			$this->planned_hours = $projecttree->oa->planned_hours;
			$this->approved_hours =  $projecttree->oa->approved_hours;
			$this->submitted_hours =  $projecttree->oa->submitted_hours;
			$this->worklogs = $projecttree->oa->worklogs;
			$this->users = $projecttree->oa->users;
		}
		$this->projecttree = $projecttree;
	}
	public function Sync()
	{
		set_time_limit(300);
		$config = Utility::GetOAConfig();
		
		$api_key = $config['api_key'];
		$organization = $config['organization'];
		$url = $config['url'];
		$user = $config['user'];;
		$pass = $config['pass'];
		//$name = '7061|MEL,MEHV,Nucleus for ECU';
		//$name = '6753|AUTOSAR for OBC';
		//$id='7102';
		$name  = $this->projecttree->project->oaname;
		
		if(($name == null) || (strlen(trim($name))==0))
		{
			Utility::ConsoleLog(time(),'Open Air Project Name not given');
			return;
		}
	
		$oa = new OpenAir($api_key,"default",'1.0','agc','1.1',$url);
		$auth = new Auth($organization,$user,$pass);
		$oa->AddAuth($auth);
		Utility::ConsoleLog(time(),'Wait::Reading Project Information');
		$project = $oa->ReadProjectId($name);
		
		//$project = $oa->ReadProjectName($id);
		$user_data = [];
		
		if(count($project)>0)
		{
			Utility::ConsoleLog(time(),'Wait::Getting user list');
			$user_data = $oa->ReadUsersByProjectId($project[0]['id']);
		}
		else
		{
			$msg = "Warning :Project name[".$name."] is not an openair project";
			Utility::ConsoleLog(time(),'Error::'.$msg);
			return;
			
		}
		//dd($user_data);
		$this->planned_hours = $oa->ReadProjectPlannedHours($project[0]['id']);
		$users = array();
		foreach($user_data as $user)
		{
			$users[$user['id']] = $user['name'];
		}
		$this->users = $users;
		Utility::ConsoleLog(time(),'Wait::Getting worklogs');
		$this->worklogs = $oa->ReadWorkLogsByProjectId($project[0]['id'],true);
		$worklogs_submitted = $oa->ReadWorkLogsByProjectId($project[0]['id'],false);
		
		for($i=0;$i<count($this->worklogs);$i++)
		{
			$this->worklogs[$i]['approved'] = 1;
			$this->worklogs[$i]['username'] = $users[$this->worklogs[$i]['userid']];
		}
		foreach($worklogs_submitted as &$worklog_submitted)
		{
			$worklog_submitted['approved'] = 0;
			$worklog_submitted['username'] = $users[$worklog_submitted['userid']];
			$this->worklogs[] = $worklog_submitted;
		}
		$this->approved_hours = 0;
		$this->submitted_hours = 0;
		
		foreach($this->worklogs as $worklog)
		{
			if($worklog['nonbillable'] == 1)
			{
				$msg = "OA worklogs by ".$worklog['username']." is non billable";
				Utility::ConsoleLog(time(),'Warning::'.$msg);
			}
			$this->submitted_hours += $worklog['decimal_hours'];
			if($worklog['approved'] == 1)		
				$this->approved_hours += $worklog['decimal_hours'];
		}
		$data = new \StdClass();
		$data->planned_hours = $this->planned_hours;
		$data->approved_hours =  $this->approved_hours;
		$data->submitted_hours =  $this->submitted_hours;
		$data->worklogs = $this->worklogs;
		
		$this->projecttree->oa = $data;
		//dd($this->worklogs);
		//dd($this->approved_hours);
		//dd($this->submitted_hours);
		//dd($plannedhours);
	}
}