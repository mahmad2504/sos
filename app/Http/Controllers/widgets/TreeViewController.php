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

class TreeViewController extends Controller
{
	private $treedata = []; 
	private $blockedtasks = [];
	private $jiraurl = null;
    public function ShowTreeView(Request $request)
    {
		if($request->user == null || $request->project==null)
			abort(403, 'Some Missing Parameters ShowTreeView(project id/name)');
		$user = $request->user;
		$project = $request->project;
		
    	$users = User::where('name',$user)->get();
    	if(count($users)==0)
    	{
    		abort(403, 'Account Not Found');
    	}
    	$projects = Project::where('name',$project)->get();
    	if(count($projects)==0)
    	{
    		abort(403, 'Project Not Found');
    	}
    	$user = $users[0];
    	$project = $projects[0];
		return View('widgets.treeview',compact('user','project'));

    }
	public function GetTreeViewData(Request $request)
	{
		if($request->id==null)
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'No record found'
			);
			return Response::json($returnData, 500);
		}
		$projects = Project::where('id',$request->id)->get();
    	if(count($projects)==0)
    	{
    		$returnData = array(
				'status' => 'error',
				'message' => 'Project Not Found'
			);
			return Response::json($returnData, 500);
    	}
		$project = $projects[0];
    	$users =  User::where('id',$project->user_id)->get();
		if(count($users)==0)
    	{
    		$returnData = array(
				'status' => 'error',
				'message' => 'User Not Found'
			);
			return Response::json($returnData, 500);
    	}
		$user = $users[0];
		$projecttree = new ProjectTree($project);
		$head = $projecttree->GetHead();
		//dd($head);
		$this->jiraurl = $projecttree->GetJiraUrl();
		$this->FormatForTreeView($head,1);
		
		foreach($this->blockedtasks as $task)
		{
			$ids = explode(".",$task->extid);
			$last = '';
			$del = '';
			foreach($ids as $id)
			{
				$parentid = $last.$del.$id;
				$del = '.';
				$last = $parentid;
				if($parentid == $task->extid)
					break;
				//echo $parentid."<br>";
				//var_dump($this->treedata[$parentid]);
				if(!array_key_exists('blockedtasks',$this->treedata[$parentid]))
					$this->treedata[$parentid]['blockedtasks'] = array();
				$this->treedata[$parentid]['blockedtasks'][$task->key] = $task->key;
			}
		}
		//echo json_encode($this->blockedtasks);
		//$this->treedata = array_values($this->treedata);
    	echo json_encode($this->treedata);
		
	}
	private function FormatForTreeView($task,$first=0)
    {
    	$row = [];
		if(($task->priority == 1)&&($task->status != 'RESOLVED'))
			$this->blockedtasks[$task->key] = $task;
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
		$row['priority'] = $task->priority;
		$row['dependson'] = $task->dependson;
		$row['sprintname'] = $task->sprintname;
		$row['sprintstate'] = $task->sprintstate;
		$row['sprintid'] = $task->sprintid;
		if($first)
		{
			$row['blockers'] = $task->blockers_present;
			$row['dependencies'] = $task->dependencies_present;
		}
		
    	$this->treedata[$task->extid] = $row;
    	foreach($task->children as $ctask)
    		$this->FormatForTreeView($ctask);
    }
}
