<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use App\Calendar;
use App\Tj;
use App\Project;
use App\ProjectTree;
use App\OA;
use Redirect,Response;
class TaskPropertyController extends Controller
{
    //
	public function Show($projectid)
	{
		$project = Project::where('id',$projectid)->first();
		if($project==null)
    	{
    		abort(403, 'Project Not Found');
		}
		$user = $project->user()->first();
		return view('taskproperty',compact('user','project'));
	}
	public function SavePosition($projectid,Request $request)
	{
		$project = Project::where('id',$projectid)->first();
		if($project==null)
    	{
			$returnData = array(
				'status' => 'error',
				'message' => 'Resource Not found'
			);
			return Response::json($returnData, 500);
		}
		$projecttree = new ProjectTree($project);

		$data = $request->all();
		foreach($data as $pos=>$key)
		{
			
			if(array_key_exists($key,$projecttree->tasks)  )
			{
				$task = $projecttree->tasks[$key];
				$task->position = $pos;
				
			}
			
		}
		$projecttree->save();
		return $data;
		
	}
	public function Save($projectid,Request $request)
	{
		/*$returnData = array(
			'status' => 'error',
			'message' => 'Resource Not found'
		);
		return Response::json($returnData, 500);*/

		$project = Project::where('id',$projectid)->first();
		if($project==null)
    	{
			$returnData = array(
				'status' => 'error',
				'message' => 'Resource Not found'
			);
			return Response::json($returnData, 500);
		}
		$projecttree = new ProjectTree($project);
		$taskdata = $request->all();
	
		$key = $taskdata['key'];
		$task = $projecttree->tasks[$key];
		$task->position = $taskdata['position'];
		$task->isconfigured = $taskdata['isconfigured'];
		if($task->isconfigured == "true")
			$task->isconfigured = true;
		else
			$task->isconfigured = false;
		
		$task->ismilestone = $taskdata['ismilestone'];
		if($task->ismilestone == "true")
			$task->ismilestone = true;
		else
			$task->ismilestone = false;

		$task->atext = $taskdata['atext'];
		$task->tstart = $taskdata['tstart'];
		$task->tend = $taskdata['tend'];
		

		$projecttree->save();
		return $taskdata;
	}
	public function GetTreeData(Request $request)
	{
		if($request->projectid==null)
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'No record found'
			);
			return Response::json($returnData, 500);
		}
		$projects = Project::where('id',$request->projectid)->get();
    	if(count($projects)==0)
    	{
    		$returnData = array(
				'status' => 'error',
				'message' => 'Project Not Found'
			);
			return Response::json($returnData, 500);
    	}
		$project = $projects[0];
    	$user = $project->user()->first();
		$projecttree = new ProjectTree($project);
		$head = $projecttree->GetHead();
		//dd($head);
		$this->jiraurl = $projecttree->GetJiraUrl();
		$this->FormatForTreeView($head,1);
		//echo json_encode($this->blockedtasks);
		//$this->treedata = array_values($this->treedata);
    	echo json_encode($this->treedata);
		
	}
	private function FormatForTreeView($task,$first=0)
    {
    	$row = [];
		$row['extid'] = $task->extid;
    	$row['pextid'] = $task->pextid;
    	$row['issuetype'] = $task->issuetype;
    	$row['summary'] = $task->summary;
    	$row['jiraurl'] = $this->jiraurl;
    	$row['key'] = $task->key;
    	$row['estimate'] = $task->estimate;
    	$row['progress'] = $task->progress;
		$row['status'] = $task->status;
		$row['priority'] = $task->priority;
		$row['ismilestone'] = $task->ismilestone;
		$row['isconfigured'] = $task->isconfigured;
		$row['atext'] = $task->atext;
		$row['duedate'] = $task->duedate;
		$row['tstart'] = $task->tstart;
		$row['tend'] = $task->tend;
		$row['position'] = $task->position;
		$row['duplicate'] = $task->duplicate;
		$this->treedata[$task->extid] = $row;
    	foreach($task->children as $ctask)
    		$this->FormatForTreeView($ctask);
    }
}