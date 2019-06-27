<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\User;
use App\Utility;
use Auth;
use Redirect,Response;

class ProjectController extends Controller
{
	private static function ValidateRequest($request)
    {
		$project = null;
		//dd($request->all());
		if($request->id != null)		
		{
			$projects = Project::where('id', $request->id)->get();
			if(count($projects)==0)
				return 'Invalid Project';
			$project = $projects[0];
		}
		
		if($request->user_id == null)
			$request->user_id = Auth::user()->id;
		
		if(($request->name == null)||(strlen(trim($request->name))==0))
			return 'Project Name is missing';
		
		$users = User::where('id', $request->user_id)->get();
		if(count($users)==0)
			return 'Invalid User';
		

		$projects  = Project::where('user_id', $request->user_id)->where('name', $request->name)->get();
		
		if(count($projects)>0)
		{
			if($project == null)
				return 'Project Name already taken';
			if($project['id'] != $projects[0]['id'])
				return 'Project Name already taken';
		}
		
		if($request->jiraquery == null)
			return 'Jira Query is missing';
		
		if (($request->description==null)||(strlen($request->description)==0))
            $request->description = 'No Description';
		
		if ($request->jira_dependencies==null)
            $request->jira_dependencies = 0;
		
		if ($request->sdate==null)
		{
            $request->sdate = Utility::GetToday('Y-m-d');
			$request->edate = date("Y-m-d",strtotime(date("Y-m-d", strtotime($request->sdate)) . "+6 months"));
		}
		if ($request->edate==null)
            $request->edate = date("Y-m-d",strtotime(date("Y-m-d", strtotime($request->sdate)) . "+6 months"));
		
		
		if ($request->progress==null)
            $request->progress = 0;
	
		if($request->last_synced == null)
			$request->last_synced = 'Never';
		
		if($project == null)
			$project = new Project;
			
		$project['user_id'] = $request->user_id;
		$project['name'] = $request->name;
		$project['description'] = $request->description;
		$project['jiraquery'] = $request->jiraquery;
		$project['last_synced'] = $request->last_synced;
		$project['jirauri'] = $request->jirauri;
		$project['sdate'] = $request->sdate;
		$project['edate'] = $request->edate;
		$project['jira_dependencies'] = $request->jira_dependencies;
		$project['progress'] = $request->progress;
		
		return $project;
    }
	public function Create(Request $request)
	{
		$project = self::ValidateRequest($request);
		if (!$project instanceof Project) 
			return Response::json(Utility::Error($project), 500);
		
		$project->save();
		return Response::json($project);
	}
	
	public function Update(Request $request)
    {  
        $project = self::ValidateRequest($request);
		if (!$project instanceof Project) 
			return Response::json(Utility::Error($project), 500);
		//dd($project);
		$project->save();
		return Response::json($project);
    }
	public function GetProjects(Request $request)
	{
		$projects = Project::where('user_id',$request->user_id)->get();
        return Response::json($projects);
	}
	public function Delete(Request $request)
	{
		$project = Project::find($request->id);
		$project->delete();
	}
}
