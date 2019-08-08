<?php

namespace App\Http\Controllers\Widgets;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;
use App\Project;
use Redirect,Response;
use Auth;

use App\Utility;
use App\ProjectTree;

class TimeChartController extends Controller
{

    public function Show(Request $request)
    {
		if($request->user == null || $request->project==null)
			abort(403, 'Some Missing Parameters ShowTreeView(project id/name)');
		$user = $request->user;
		$project = $request->project;

    	$user = User::where('name',$user)->first();
    	if($user==null)
    	{
    		abort(403, 'Account Not Found');
    	}
		$project = $user->projects()->where('name',$project)->first();
		if($project==null)
    	{
    		abort(403, 'Project Not Found');
    	}
		if($project==null)
    	{
    		abort(403, 'Project Not Found');
    	}
    	$isloggedin = Auth::check();
		if($isloggedin)
			$isloggedin = 1;
		else
			$isloggedin = 0;
		return View('widgets.timechart',compact('user','project','isloggedin'));

    }
	public function GetData($project_id)
	{
		$projects = Project::where('id',$project_id)->get();
		if(count($projects)==0)
    {
    		$returnData = array
				(
					'status' => 'error',
					'message' => 'Project Not Found'
				);
				return Response::json($returnData, 500);
    }
		$project = $projects[0];
		$projecttree = new ProjectTree($project);
		return $projecttree->GetTimeLog();
	}
}
