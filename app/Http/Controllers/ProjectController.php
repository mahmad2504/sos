<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Widgets\MilestoneController;

use Illuminate\Http\Request;
use App\Project;
use App\User;
use App\Utility;
use App\ProjectTree;
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
		
		//echo Utility::GetJiraURL($projects[0]);
		
		if(count($projects) > 0)
		{
			$projects[0]->jiraurl = Utility::GetJiraURL($projects[0]);
			$ms = new MilestoneController();
			$projecttree = new ProjectTree($projects[0]);
			$burnupdata = $projecttree->GetBurnUpData($projecttree->tree);
			$status = $ms->GetStatus($projects[0],"1");
			if($status != null)
			{
				if($burnupdata != null)
				{
					$status['cv'] = $burnupdata->cv;
					$status['rv'] = $burnupdata->rv;
				}
				$projects[0]->status = $status;
			}
			
			return Response::json($projects[0]);
		}
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
			if ($request->task_description==null)
				$request->task_description = 0;
			if ($request->sdate==null)
			{
				$request->sdate = Utility::GetToday('Y-m-d');
				$request->edate = date("Y-m-d",strtotime(date("Y-m-d", strtotime($request->sdate)) . "+1 months"));
			}
			if ($request->edate==null)
				$request->edate = date("Y-m-d",strtotime(date("Y-m-d", strtotime($request->sdate)) . "+1 months"));
	
			if ($request->progress==null)
				$request->progress = 0;
			if($request->last_synced == null)
				$request->last_synced = 'Never';
			if($request->baseline == null)
				$request->baseline = '';
			if($request->state == null)
				$request->state = 'SYSTEM';
			if($request->visible == null)
				$request->visible = 'true';
			
			$project['user_id'] = $request->user_id;
			$project['name'] = $request->name;
			$project['oaname'] = $request->oaname;
			$project['description'] = $request->description;
			$project['jiraquery'] = $request->jiraquery;
			$project['last_synced'] = $request->last_synced;
			$project['baseline'] = $request->baseline;
			$project['jirauri'] = $request->jirauri;
			$project['sdate'] = $request->sdate;
			$project['edate'] = $request->edate;
			$project['jira_dependencies'] = $request->jira_dependencies;
			$project['task_description'] = $request->task_description;
			$project['estimation'] = $request->estimation;
			$project['progress'] = $request->progress;
			$project['state'] = $request->state;
			$project['visible'] = $request->visible;
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
			$project['oaname'] = $request->oaname;
			if($request->jiraquery != null)
				$project['jiraquery'] = $request->jiraquery;
			if ($request->description!=null)
				$project['description'] = $request->description;
			if ($request->jira_dependencies!=null)
				$project['jira_dependencies'] = $request->jira_dependencies;
			if ($request->task_description!=null)
				$project['task_description'] = $request->task_description;
			if ($request->sdate!=null)
				$project['sdate'] = $request->sdate;
			if ($request->edate!=null)
				$project['edate'] = $request->edate;
			if ($request->progress!=null)
				$project['progress'] = $request->progress;
			if ($request->state!=null)
				$project['state'] = $request->state;
			if ($request->last_synced!=null)
				$project->last_synced = $request->last_synced;
			if ($request->baseline!=null)
				$project->baseline = $request->baseline;
			if($request->jirauri != null)
				$project->jirauri = $request->jirauri;
			if($request->estimation != null)
				$project->estimation = $request->estimation;
			if($request->visible != null)
			{
				$project->visible = $request->visible;
			}
		}
		//dd($project);
		$project['dirty'] = 1;
		return $project;
  }
	public function Create(Request $request)
	{
		$project = self::ValidateRequest($request);
		if (!$project instanceof Project) 
			return Response::json(Utility::Error($project), 500);

			//$project->user()->name;
		//dd($project->user());
		$project->save();
		$tree = new ProjectTree($project);
		if(file_exists($tree->datapath))
		   rmdir($tree->datapath);
		//dd($tree);

		return Response::json($project);
	}
	
	public function Update(Request $request)
    {  
		if($request->id == null)
			abort(403, 'Missing Parameters - ProjectController@Update(project data with id)');
		
        $project = self::ValidateRequest($request);
		if (!$project instanceof Project) 
			return Response::json(Utility::Error($project), 500);
		
		$project->save();
		$ms = new MilestoneController();
		$status = $ms->GetStatus($project,"1");
		if($status != null)
		{
			$project->status = $status;
		}
		return Response::json($project);
    }
	public function GetProjects(Request $request)
	{		
		if($request->user_id == null)
			abort(403, 'Missing Parameters - ProjectController@GetProjects(user_id)');
		
		$lastdate = date("Y-m-d",strtotime("-15 days"));
		if($request->showclosedprojects == 1)
		{
			$projects = Project::where(
			[
				['user_id', '=', $request->user_id],
				['last_synced', '<=', $lastdate]
			]
			)->orderBy('edate','asc')->get();
			foreach($projects as $project)
				$project->archived = 1;
		}
		else
		{
			//$projects = Project::where('user_id',$request->user_id)->where('edate','>','2011-09-07')->where('progress','<',100)->orderBy('edate','asc')->get();
			$projects = Project::where(
				[
					['user_id', '=', $request->user_id],
					['last_synced', '>', $lastdate]
				]
				)->orderBy('edate','asc')->get();
				foreach($projects as $project)
					$project->archived = 0;
		}
		if($request->local)
			return $projects;
		return Response::json($projects);
	}
	public function Archive(Request $request)
	{
		$id = $request->id;
		$project = Project::where('id', $id)->first();
		
		if($project['archive'] == 1)
			Project::where('id', $id)->update(['archive' => 0]);
		else
			Project::where('id', $id)->update(['archive' => 1]);
	
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
		$datafolder = Utility::GetDataPath($user,$project);
		Utility::DeleteDir($datafolder);
		$project->delete();		
	}
	public static function UpdateProgressAndLastSync($id,$progress,$last_synced,$baseline=null)
	{
		if($baseline!=null)
			Project::where('id', $id)->update(array('last_synced' => $last_synced,'dirty'=>0, 'progress'=>$progress, 'baseline'=>$baseline));
		else
			Project::where('id', $id)->update(array('last_synced' => $last_synced,'dirty'=>0, 'progress'=>$progress));
	}
}
