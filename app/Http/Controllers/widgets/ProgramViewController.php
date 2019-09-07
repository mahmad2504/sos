<?php

namespace App\Http\Controllers\Widgets;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Widgets\MilestoneController;
use Illuminate\Http\Request;

use App\User;
use App\Project;
use Redirect,Response;
use Auth;

use App\Utility;
use App\ProjectTree;

class ProgramViewController extends Controller
{
	public function ShowSummary($user)
    {
		$user = User::where('name',$user)->first();
    	if($user==null)
    	{
    		abort(403, 'Account Not Found');
		}
		$pc = new ProjectController();
		$request = new Request();
		$request->user_id = $user->id;
		$request->local = 1;
		$projects = $pc->GetProjects($request);
		$data = array();
		$i=0;
		foreach($projects as $project)
		{
			$ms = new MilestoneController();
			$status = $ms->GetStatus($project,"1");
			if($status != null)
			{
				$data[] = $status;
			}
			$i++;
		}
		dd($data);
		//$data = json_decode($projects);
        //dd($projects);
    }
}
