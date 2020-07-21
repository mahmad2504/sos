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

class GovernanceController extends Controller
{
	public function cmp($a, $b) 
	{
      return $a->no > $b->no;
	}

	public function Show(Request $request, $user, $project)
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
		$jiraurl = Utility::GetJiraURL($project);
		
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
			abort(403, 'Not Found');
		
		$fixversion=$projecttree->settings->filter_fixversion;
		$data = $projecttree->JiraGovernance($t);
		
		usort($data->sprints,[$this,'cmp']);

		
		//dd($obj->sprints);
		
		//dump($projecttree->sprint_info);
		//dump($projecttree->out_of_sprint_tasks);
		//return;
		
		return View('widgets.governance',compact('fixversion','jiraurl','user','project','loggeduser','data'));
    }
}
