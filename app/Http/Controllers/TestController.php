<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use App\Calendar;
use App\Tj;
use App\Project;
use App\ProjectTree;
use App\OA;
use App\Jira;
use App\Utility;
use Redirect,Response;
class TestController extends Controller
{
    //
	public function ShowTree($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		if($project == null)
		{
			echo "Project id=".$projectid." not found";
			return;
		}
		$projecttree = new ProjectTree($project);
		//dd($projecttree->tree->oa->worklogs);
		dd($projecttree);
	}
	public function Test($task)
	{
		if(isset($task->twin))
			echo($task->twin->sch_start);
		for($i=0;$i<count($task->children);$i++)
			$this->Test($task->children[$i]);
	}
	public function TJTest($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		$projecttree = new ProjectTree($project);
		//$this->Test($projecttree->tree);
		//exit();
		//dd($projecttree ->tree);
		$tj =  new Tj($projecttree);
		$tj->Execute();
	}
	public function JiraSync(Request $request)
	{
		$synccontroller = new SyncController();
		$request->debug=1;
		$request->rebuild=1;
		$request->worklogs=1;
		$synccontroller->SyncJira($request);
	}
	public function OASync($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		if($project == null)
		{
			echo "Project id=".$projectid." not found";
			return;
		}
		$projecttree = new ProjectTree($project);

		$oa = new OA($projecttree);
		$oa->sync();
		dd(	$projecttree->tree->oa);
	}
	public function OAWorklogs($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		if($project == null)
		{
			echo "Project id=".$projectid." not found";
			return;
		}
		$projecttree = new ProjectTree($project);
		if(isset($projecttree->tree->oa))
			dd($projecttree->tree->oa);
		dd("OA Worklogs not found");
	}
	public function GetJiraWorklogs($projectid,$jira_key)
	{
		echo $jira_key."<br>";
		$project = Project::where('id',$projectid)->first();
		if($project == null)
		{
			echo "Project id=".$projectid." not found";
			return;
		}
		$projecttree = new ProjectTree($project);
		//dd($projecttree);
		$jiraconf = $this->jiraconfig = Utility::GetJiraConfig($project);
		Jira::Initialize($jiraconf ,$projecttree->datapath);
		$worklogs = Jira::GetWorkLogs($jira_key);
		//foreach($worklogs as $date=>$userdata)
		//	foreach($userdata as $user=>$data)
		//		dd($data);
		dd($worklogs);
		//dd($worklogs);
		//$project = Project::where('id',$projectid)->first();
		//$projecttree = new ProjectTree($project);
		//dd($projecttree);
	}
	public function ResourceTimeLogs($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		if($project == null)
		{
			echo "Project id=".$projectid." not found";
			return;
		}
		$projecttree = new ProjectTree($project);

		return $projecttree->GetTimeLog();
	}
	
}
