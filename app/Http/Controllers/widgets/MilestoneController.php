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
 
    public function Show(Request $request, $user, $project)
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
		$key = $request->key;
		if($key==null)
		{
            $key = (string)1;
        }
		
		$projecttree = new ProjectTree($project);
	
		

        if(array_key_exists($key,$projecttree->tasks))
             $head = $projecttree->tasks[$key];
        else
            abort(403, 'Key '.$key.' Not Found');
       
        $isloggedin = Auth::check();
		if($isloggedin)
			$isloggedin = 1;
		else
			$isloggedin = 0;
		//$data = $projecttree->GetBurnUpData($head);
		$milestones = $projecttree->GetMilestones($head);

		
	
		$header[]='Description';
		$header[]='Baseline End';
		$header[]='Current End';
		$header[]='Forecast End';

		$header[]='Baseline EAC';
		$header[]='Current EAC';
		$header[]='Remaining';
	
		$header[]='Progress';
		$header[]='Indicators';
		$header[]='Reports';
		
		$baselinetree = $projecttree->ReadBaseline();
		

		$data = [];
		for($i=0;$i<count($milestones);$i++)
		{
			$milestone = $milestones[$i];
			if(array_key_exists($milestone->key,$baselinetree->tasks))
				$baselinetask = $baselinetree->tasks[$milestone->key];
			else
				$baselinetask =  null;
	  		
			$burnupdata = $projecttree->GetBurnUpData($milestone);
			
			//baselinetree->tree() MUMTAZ
			
			//dd($burnupdata);
			$row =  array();
		
			$row[] = $milestone->_summary;
			if($baselinetask != null)
				$row[] = $baselinetask->_duedate;
			else
				$row[] = '';
			$row[] = $milestone->_duedate;
			if($milestone->status == 'RESOLVED')
				$row[] = '';
			else
				$row[]  = $milestone->_sched_end;
			if($baselinetask != null)
				$row[] =  $baselinetask->_orig_estimate;
			else
				$row[] = '';

			$row[] =  $milestone->estimate;
			$row[] =  $milestone->estimate - $milestone->timespent ;
			$row[] =  $milestone->progress;
			$row[] =  $milestone->status;
			$row[] = $burnupdata->cv; 
			$row[] = $burnupdata->rv; 
			$row[] = $milestone->key;
			$data[] = $row;
		}
		
		usort($data,array($this,'Sort'));
		$test[] = 'f';
		$data = array_merge($test,$data);
		$data[0] = $header;
		
		return View('widgets.milestone',compact('user','project','isloggedin','data','key'));
	}
	
}
