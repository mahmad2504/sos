<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utility;
use App\Project;
use App\User;
use App\ProjectTree;
class SyncController extends Controller
{
	public function sync(Request $request)
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
		set_time_limit(300);
		if($request->projectid == null)
		{
			Utility::ConsoleLog(time(),'Params:Project Id Missing');
			exit();
		}
		if($request->rebuild == null)
			$request->rebuild = 0;
		else 
			$request->rebuild = 1;
		
		Utility::ConsoleLog(time(),$request->projectid);
		$project = Project::where('id',$request->projectid)->first();
		if($project == null)
    	{
    		Utility::ConsoleLog(time(),'Project does not exist');
			exit();
    	}
		$user = User::where('id',$project->user_id)->first();
		
		$tree  =  new ProjectTree($project);
		$tree->Sync($request->rebuild);
		
		//dd(Utility::GetJiraConfig($project->jirauri));
		
		
	}
    //
}
