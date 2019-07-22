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

class GanttController extends Controller
{
	private $treedata = []; 
	private $blockedtasks = [];
	private $jiraurl = null;
	private $data = [];
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
    	
		return View('widgets.gantt',compact('user','project'));

    }
	public function GetData(Request $request)
	{
		//return file_get_contents('data.json');
		
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
		$projecttree = new ProjectTree($project);
		
		$head = $projecttree->GetHead();
		$this->FormatForGantt($head,1);
		return $this->data;
	}
	private function FormatForGantt($task,$first=0)
    {
		$row['pID'] = $task->extid;
		$row['pName'] = $task->summary;
		
		if(!isset($task->sched_start))
			$row['pStart'] = $task->twin->sched_start;
		else
			$row['pStart'] = $task->sched_start;
		
		if(!isset($task->sched_end))
			$row['pEnd'] = $task->twin->sched_end;
		else
			$row['pEnd'] = $task->sched_end;
		
		if(!isset($task->sched_assignee))
			$row['pRes'] = $task->twin->sched_assignee;
		else
			$row['pRes'] = $task->sched_assignee;
		
		$row['pPlanStart'] =  "";
		$row['pPlanEnd'] =  "";
		
		
		if( $task->isparent )
			$row['pClass'] = "ggroupblack";
		else
		{
			if($task->status == 'INPROGRESS')
				$row['pClass'] = 'gtaskgreen';
			else
				$row['pClass'] = 'gtaskblue';
		}
		
		$row['pLink'] = "";
		$row['pMile'] = 0;
		$row['pComp'] = 0;
		$row['pGroup'] = $task->isparent;
		$row['pOpen'] = 1;
		$row['pDepend'] = '';
		$row['pCaption'] = 'FFFF';
		$row['pNotes'] = 'Some Notes text';
		
		$row['pStatus'] = $task->status;
		$row['pPrioriy'] = $task->schedule_priority;
		$row['pJira'] = $task->key;
		
		$this->data[] = $row;
    	foreach($task->children as $ctask)
    		$this->FormatForGantt($ctask);
    }
}
