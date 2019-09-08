<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utility;
use App\Project;
use App\User;
use App\ProjectTree;
use App\Tj;
use App\OA;
class SyncController extends Controller
{
	public function __construct()
	{
		set_time_limit(1000);
	}
	public function SyncJira(Request $request)
	{
		/*Input Params
		$request->projectid
		$request->rebuild
		*/
		if($request->debug == null)
		{
			header('Content-Type: text/event-stream');
			header('Cache-Control: no-cache');
		}
		
		if($request->projectid == null)
		{
			Utility::ConsoleLog(time(),'Params:Project Id Missing');
			exit();
		}
		if($request->rebuild == null)
			$request->rebuild = 0;
		else
			$request->rebuild = 1;

		if($request->worklogs == null)
			$request->worklogs = 0;
		else
			$request->worklogs = 1;

		if($request->baseline == null)
			$request->baseline = 0;
		else
			$request->baseline = 1;

		//Utility::ConsoleLog(time(),$request->projectid);
		$project = Project::where('id',$request->projectid)->first();
		if($project == null)
    	{
    		Utility::ConsoleLog(time(),'Project does not exist');
			exit();
    	}
		//$user = User::where('id',$project->user_id)->first();
		if($project->edate < Utility::GetToday('Y-m-d'))
		{
			Utility::ConsoleLog(time(),'Error::Cannot Sync. Project End Date is expired.');
			Utility::ConsoleLog(time(),'Error::Check Project Settings');
			return; 
		}
		$tree  =  new ProjectTree($project);
		$retval = $tree->SyncJira($request->rebuild,$request->worklogs);
		if($retval  == -1)
		{
			Utility::ConsoleLog(time(),"Error::Empty Project or Jira error");
			return;
		}
		

		//$project = Project::where('id',$projectid)->first();
		//$projecttree = new ProjectTree($project);


		$tj =  new Tj($tree);
		$tj->Execute();

		$tree->Save();
		//$tree->Reset();
		if($request->baseline==1)
			$tree->SaveBaseline();
		//dd(Utility::GetJiraConfig($project->jirauri));

		Utility::ConsoleLog(time(),"Success::Jira Sync Completed");
	}
	public function SyncOA(Request $request)
	{
		if($request->debug == null)
		{
			header('Content-Type: text/event-stream');
			header('Cache-Control: no-cache');
		}

		if($request->projectid == null)
		{
			Utility::ConsoleLog(time(),'Params:Project Id Missing');
			exit();
		}
		$project = Project::where('id',$request->projectid)->first();
		if($project == null)
    	{
    		Utility::ConsoleLog(time(),'Project does not exist');
			exit();
		}
		if($project->edate < Utility::GetToday('Y-m-d'))
		{
			Utility::ConsoleLog(time(),'Error::Cannot Sync. Project End Date is expired.');
			Utility::ConsoleLog(time(),'Error::Check Project Settings');
			return; 
		}
		$projecttree  =  new ProjectTree($project);
		$oa = new OA($projecttree);
		$oa->sync();
		$projecttree->Save();
		Utility::ConsoleLog(time(),"Success::OpenAir Sync Completed");
		
	}
}
