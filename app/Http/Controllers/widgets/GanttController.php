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
		if(ctype_digit($project))
			$project = $user->projects()->where('id',$project)->first();
		else
			$project = $user->projects()->where('name',$project)->first();
		if($project==null)
		{
			abort(403, 'Project Not Found');
		}
		if($project==null)
		{
			abort(403, 'Project Not Found');
		}
		$isloggedin = Auth::check();
		if($isloggedin)
			$isloggedin = 1;
		else
			$isloggedin = 0;
		$key = $request->key;
		if($key==null)
		{
			$key = (string)1;
		}
		$projecttree = new ProjectTree($project);
		$ms =  $projecttree->GetMilestones($projecttree->tree);
		$milestones =  array();
		foreach($ms as $m)
		{
			$milestone = new \StdClass();
			$milestone->summary = $m->_summary;
			$milestone->key = $m->key;
			$milestones[] = $milestone;
		}
		return View('widgets.gantt',compact('user','project','isloggedin','key','milestones'));
	}
	public function GetData(Request $request)
	{

		//return file_get_contents('data.json');		
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
		$projecttree = new ProjectTree($project);
		
		$key = $request->key;
		if($key==null)
		{
			$key = (string)1;
		}
	
		if(array_key_exists($key,$projecttree->tasks))
             $head = $projecttree->tasks[$key];
        else
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
		$row['pIssuesubtype'] = 'DEV';
		if(isset($task->issuesubtype))
			$row['pIssuesubtype'] = $task->issuesubtype;
		
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
		if($first == 1)
			$row['pParent'] = 0;
		else
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
		$row['oStatus'] = $task->ostatus;
		$row['pPrioriy'] = $task->schedule_priority;
		$row['pJira'] = $task->key;
		if($task->status == 'RESOLVED')
			$row['deadline'] = '';
		else
		{
			$row['deadline'] = $task->_duedate;
			if($row['deadline'] == null)
				$row['deadline'] = '';
		}
		$this->data[] = $row;
		foreach($task->children as $ctask)
		{
			$this->FormatForGantt($ctask);
		}
    }
}
