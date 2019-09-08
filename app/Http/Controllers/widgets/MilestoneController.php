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

class MilestoneController extends Controller
{	
	private function Sort($a,$b) 
	{
		$statusa = $a[9];
		$statusb = $b[9];

		if($statusa == 'RESOLVED')
			return -1;  // a should be on top
		else if($statusb == 'RESOLVED')
			return 1;   // b should be on top
		else if($statusa == 'INPROGRESS')
			return -1;  // a should be on top
		else if($statusb == 'INPROGRESS')
			return 1;   // b should be on top
		
		return 0;  // a should be on top
	}
	
    public function Show($user, $project,$key="1")
    {
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
		$projecttree = new ProjectTree($project);
		$head = $projecttree->GetTask($key);
		if($head == null)
        	abort(403, 'Key '.$key.' Not Found');
		

		//$data = $projecttree->GetBurnUpData($head);
		$milestones = $projecttree->GetMilestones($head);
		
		$baselinetree = $projecttree->ReadBaseline();
		$data = [];
		for($i=0;$i<count($milestones);$i++)
		{
			$task = $milestones[$i];
			$row = $this->GetStatus($project,$task->key);
			if($row != null)
				$data[] = $row;
		}
		$isloggedin = $this->isloggedin;
		
		return View('widgets.milestone',compact('user','project','isloggedin','data','key'));
	}
	private function FillStatusData($task,$baselinetask)
	{
		$data = array();
		$data['bend'] = '';
		$data['bestimate'] = '';	
		if($baselinetask != null)
		{
			$data['bend'] = $baselinetask->_tend;
			$data['bestimate'] =  $baselinetask->_orig_estimate;
		}
		$data['summary'] = $task->_summary;
		$data['tstart'] = $task->_tstart;
		$data['tend'] = $task->_tend;
		$data['end'] = $task->_sched_end;
		$data['estimate'] = $task->estimate;
		$data['progress'] = $task->progress;
		$data['consumed'] =  $task->timespent ;
		$data['remaining'] =  $task->estimate - $task->timespent ;
		$data['status'] = 'DELIVERED';
		if($task->status != 'RESOLVED')
		{
			$data['status'] = 'ONTRACK';
			if($data['end']>$data['tend'])
				$data['status'] = 'DELAYED';
			else
			{
				if(Utility::IsVleocityLow($task->cv,$task->rv))
					$data['status'] = 'STALL';
			}
		}
	
		return $data;
	}
	public function GetStatus(Project $project,$key)
	{
		$projecttree = new ProjectTree($project);
		if($projecttree->tree == null)
			return null;
		$task = $projecttree->GetTask($key);	
		$burnupdata = $projecttree->GetBurnUpData($task);
		$task->cv = $burnupdata->cv;
		$task->rv = $burnupdata->rv;
		$baselinetask = null;
		$baselinetree = $projecttree->ReadBaseline();
		if($baselinetree != null)
		{
			$baselinetask = $baselinetree->GetTask($key);
			$data['bend'] = $baselinetask->_tend;
			$data['bestimate'] =  $baselinetask->_orig_estimate;
		}
		$data = $this->FillStatusData($task,$baselinetask);
		$data['risksissues'] = $projecttree->GetRiskAndIssues($task);
		$data['jiraurl'] = Utility::GetJiraURL($project);
		return $data;
	}
	public function ShowStatus($user, $project,$key="1")
	{
		//echo $user." ".$project." ".$key;
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
		$data = $this->GetStatus($project,$key);
		if($data == null)
			abort(403, 'Key '.$key.' Not Found');
			
		$isloggedin = $this->isloggedin;
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

		return View('widgets.status',compact('user','project','isloggedin','data','key','milestones'));
	}
	
}
