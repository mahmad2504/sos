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

class SprintsController extends Controller
{
	public function ShowSprints(Request $request, $user, $project)
    {
		$loggeduser = null;
		$loggeduser = Auth::user();
		
		$user = User::where('name',$user)->first();
    	if($user==null)
    	{
    		abort(403, 'Account Not Found');
		}
		if(ctype_digit($project))
			$project = $user->projects()->where('id',$project)->first();
		else
			$project = $user->projects()->where('name',$project)->first();
		if($project==null)
    	{
    		abort(403, 'Project Not Found');
		}
		$projecttree = new ProjectTree($project);
		$t = null;
		
		if($request->key != null)
		{
			foreach($projecttree ->tasks as $t)
			{
					if($t->key."a" == $request->key."a")
					{
						break;
					}
			}
		}
		else
			$t = $projecttree->tree;
		
		if($t == null)
			abort(403, 'No Sprint is configured');
		
		$sprints = $projecttree->GetSprintsData($t,1);
		
		if(count($sprints)==0)
			abort(403, 'No Sprint is configured');
		//dd($projecttree->GetAllSprints());
		foreach($sprints as $sprint)
		{
			if(isset($sprint['startDate']))
			{
				$sprint['startDate'] = explode("T",$sprint['startDate'])[0];
			}
			else
				$sprint['startDate'] = '';
			
		}
		$iframe = $request->iframe;
		if($iframe == null)
			$iframe = 0;
		else
			$iframe  = 1;
		
		return View('widgets.sprints',compact('user','project','loggeduser','sprints','iframe'));
    }
}
