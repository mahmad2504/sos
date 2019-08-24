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
		private $j=1;
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
		
		//dd($projecttree->tree->children[4]);
		$this->FormatForGantt($head,1);
		//dd($this->data);
		return $this->data;
	}
	private function FormatForGantt($task,$first=0)
    {
		$row['pID'] = $task->extid;
		$row['pName'] = $task->_summary;
		$row['pDepend'] = '';
		if(count($task->dependson)>0)
		{
			 $del = "";
			foreach($task->dependson as $key)
			{
			   //echo $task->key." depends on ".$key."\r\n";
			   if(isset($task->parent->tasks[$key]))
			   {
					$predecessor = $task->parent->tasks[$key];
					$row['pDepend'] =  $row['pDepend'].$del.$predecessor->extid;
					$del = ",";
			   }
			}
		}
		$row['pStart'] = $task->_sched_start;
		$row['pEnd'] = $task->_sched_end;
		$row['pRes'] = $task->_sched_assignee;
		
		if($row['pRes'] == 'unassigned')
			$row['pRes'] = '';
		
		if($task->assignee != $row['pRes'])
			$row['pRes'] = '<span style="color:orange;">'.$row['pRes'].'</span>';

		if(isset($task->estimate))
			$row['pEstimate'] = $task->estimate;

		if(isset($task->timespent))
			$row['pTimeSpent'] = $task->timespent;
	
		if(isset($task->closedon))
			$row['pClosedOn'] = $task->closedon;
			
		$row['pPlanStart'] =  "";
		$row['pPlanEnd'] =  "";
		$row['pParent'] = $task->pextid;
		
		if( $task->isparent )
			$row['pClass'] = "ggroupblack";
		else
		{
			if($task->status == 'INPROGRESS')
			{
				$row['pClass'] = 'gtaskgreen';
				if($task->estimate == 0)
					$row['pClass'] = 'gtaskgreenunestimated';
				
				
			}
			else if($task->status == 'OPEN')
			{
				$row['pClass'] = 'gtaskopen';//'gtaskblue';
				if($task->estimate == 0)
					$row['pClass'] = 'gtaskopenunestimated';
			}
			else
				$row['pClass'] = 'gtaskclosed';//'gtaskblue';
		}
		
		$row['pLink'] = '/browse/'.$task->key;
		$row['pMile'] = 0;
		$row['pComp'] = $task->progress;
		$row['pGroup'] = $task->isparent;
		$row['pOpen'] = 1;
		if($task->status == 'RESOLVED')
			$row['pOpen'] = 0;
		
		$row['pCaption'] = '';
		$row['pNotes'] = 'Some Notes text';
	
		$row['pStatus'] = $task->status;
		$row['pPrioriy'] = $task->schedule_priority;
		$row['pJira'] = $task->key;
		$row['deadline'] = $task->_duedate;
		if($row['deadline'] == null)
			$row['deadline'] = '';

		$this->data[] = $row;
		foreach($task->children as $ctask)
		{
			$this->FormatForGantt($ctask);
		}
    }
}
