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
		set_time_limit(300);
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
		
		//Utility::ConsoleLog(time(),$request->projectid);
		$project = Project::where('id',$request->projectid)->first();
		if($project == null)
    	{
    		Utility::ConsoleLog(time(),'Project does not exist');
			exit();
    	}
		//$user = User::where('id',$project->user_id)->first();
		
		$tree  =  new ProjectTree($project);
		$tree->SyncJira($request->rebuild);
		
		
		//$project = Project::where('id',$projectid)->first();
		//$projecttree = new ProjectTree($project);
		
		
		$tj =  new Tj($tree);
		$tj->Execute();
		
		$tree->Save();
		
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
		$tree  =  new ProjectTree($project);
		$oa = new OA($tree);
		$oa->sync();
		$tree->Save();
		Utility::ConsoleLog(time(),"Success::OpenAir Sync Completed");
		
	}
   
}
