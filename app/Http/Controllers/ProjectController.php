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
	public function GetProject(Request $request) // $request->name input paramter
    {
		if(($request->id == null)&&($request->name == null))
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'Some Missing Parameters [project name/id]'
			);
			return Response::json($returnData, 500);
		}
			
		if($request->id  != null)
			$projects = Project::where('id',$request->id)->get();
		else
			$projects = Project::where('name',$request->name)->get();
		if(count($projects) > 0)
			return Response::json($projects[0]);
		else
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'No record found'
			);
			return Response::json($returnData, 500);
		}
    }
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
		if($project == null)// Create Project Case
		{
			$project = new Project;
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
		}
		else // Update case
		{
			if($request->name != null)
			{
				$projects  = Project::where('user_id', $request->user_id)->where('name', $request->name)->get();
				if(count($projects)>0)
				{
					foreach($projects as $project)
					{
						if($projects[0]['id'] != $request->id)
							return 'Project Name already taken';
					}
					
				}
				$project['name'] = $request->name;
			}
			if($request->jiraquery != null)
				$project['jiraquery'] = $request->jiraquery;
			if ($request->description!=null)
				$project['description'] = $request->description;
			if ($request->jira_dependencies!=null)
				$project['jira_dependencies'] = $request->jira_dependencies;
			if ($request->sdate!=null)
				$project['sdate'] = $request->sdate;
			if ($request->edate!=null)
				$project['edate'] = $request->edate;
			if ($request->progress!=null)
				$project['progress'] = $request->progress;
			if ($request->last_synced!=null)
				$request->last_synced = $request->last_synced;
			
			if($request->jirauri != null)
				$request->jirauri = $request->jirauri;
			
		}
		$project['dirty'] = 1;
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
		if($request->id == null)
			abort(403, 'Missing Parameters - ProjectController@Update(project data with id)');
		
        $project = self::ValidateRequest($request);
		if (!$project instanceof Project) 
			return Response::json(Utility::Error($project), 500);
		//dd($project);
		$project->save();
		return Response::json($project);
    }
	public function GetProjects(Request $request)
	{
		if($request->user_id == null)
			abort(403, 'Missing Parameters - ProjectController@GetProjects(user_id)');
		
		$projects = Project::where('user_id',$request->user_id)->get();
        return Response::json($projects);
	}
	public function Delete(Request $request)
	{
		
		
		if($request->id == null)
			abort(403, 'Missing Parameters - ProjectController@Delete(id)');
		
		$project = Project::find($request->id);
		$user = $project->user()->first();
		
		$datafolder = Utility::GetDataPath($user,$project);
		
		$presources = $project->resources()->get();
		foreach($presources as $presource)
			$presource->delete();
		$project->delete();
		
		$datafolder = Utility::GetDataPath($user,$project);
		array_map('unlink', glob("$datafolder/*"));
		rmdir($datafolder);
		
		
	}
	public static function UpdateProgressAndLastSync($id,$progress,$last_synced)
	{
		Project::where('id', $id)->update(array('last_synced' => $last_synced,'dirty'=>0, 'progress'=>$progress));
	}
}
