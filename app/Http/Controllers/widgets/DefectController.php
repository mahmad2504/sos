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

class DefectController extends Controller
{
	private $defects = [];
	function FindDefect($task,$lp=0,$up=100,$level=0)
	{
		$defects = [];
		
		if(count($task->children)>0)
		{
			foreach($task->children as $stask)
				$this->FindDefect($stask,$lp,$up,$level+1);
		}
		else
		{
			//echo $task->key." ".$task->priority."<br>";
			if(($task->issuetype == 'DEFECT')&&($task->priority >= $lp)&&($task->priority <= $up))
			{
				
			
				$weekclosed = null;
				if($task->closedon != null)
				{
					$dateclosed = new \DateTime($task->closedon);
					//$weekclosed = $dateclosed->format("y")."W".$dateclosed->format("W");
					$weekclosed = date('Y-m-d', strtotime('Last Monday', strtotime($task->closedon)));

				}
				//else
				//	echo $task->key."\n";
				
				$datecreated = new \DateTime($task->created);
				//if($datecreated->format("y") < 20)
				//	continue;
				$all_tasks[$task->key]=1;
					
				//$weekcreated = $datecreated->format("y")."W".$datecreated->format("W");
				$weekcreated = date('Y-m-d', strtotime('Last Monday', strtotime($task->created)));

				//echo $task->key." ".$weekcreated." ".$weekclosed." ".$task->fields->status." ".$task->fields->_status." ".$task->fields->_closedon."\n";
				//echo $weekcreated."<br>";
				
				if(!isset($this->defects[$weekcreated]))
				{
					$this->defects[$weekcreated] =  new \StdClass();
					$this->defects[$weekcreated]->created=0;
					$this->defects[$weekcreated]->closed = 0;
					$this->defects[$weekcreated]->acc_closed = 0;
					$this->defects[$weekcreated]->acc_created = 0;
					$this->defects[$weekcreated]->created_tasks=[];
				}
				$this->defects[$weekcreated]->created_tasks[$task->key]=1;
				$this->defects[$weekcreated]->created = count($this->defects[$weekcreated]->created_tasks);
				
					
				if($weekclosed == null)
					return;
				
				//if($task->key == 'MEIF-2322')
				//echo $task->key." ".$task->priority." ".$lp." ".$up." ".$task->closedon."<br>";
			
			
				if(!isset($this->defects[$weekclosed]))
				{
					$this->defects[$weekclosed] =  new \StdClass();
					$this->defects[$weekclosed]->closed=0;
					$this->defects[$weekclosed]->created=0;
					$this->defects[$weekclosed]->acc_closed = 0;
					$this->defects[$weekclosed]->acc_created = 0;
					$this->defects[$weekclosed]->closed_tasks = [];
				}
				$this->defects[$weekclosed]->closed_tasks[$task->key]=0;
				$this->defects[$weekclosed]->closed = count($this->defects[$weekclosed]->closed_tasks);
				
			}
		}
		if($level > 0)
			return;
		ksort($this->defects);
		
		$acc_created = 0;
		$acc_closed = 0;
		$remove = [];
		foreach($this->defects as $week=>$obj)
		{
			if(strtotime($week) < strtotime('-90 days')) 
			{
				//$remove[] = $week;
			}
			$obj->acc_created = $obj->created + $acc_created;
			$acc_created = $obj->acc_created;
			
			$obj->acc_closed = $obj->closed + $acc_closed;
			$acc_closed = $obj->acc_closed;
			
			//echo $week." ".$obj->created." ".$obj->closed." ".$obj->acc_created." ".$obj->acc_closed."\n";
		}
		if(count($this->defects)>16)
		{
			foreach($remove as $r)
			{
				unset($this->defects[$r]);
				if(count($this->defects)<=16)
					break;
			}
		}
		
	}
    public function Show(Request $request, $user, $project)
    {
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
		$key = $request->key;
		$iframe = $request->iframe;
		if($iframe == null)
			$iframe = 0;
		else
			$iframe  = 1;
		
		if($key==null)
		{
            $key = (string)1;
        }
		$key = (string)$key;
		
        $projecttree = new ProjectTree($project);
        
        if(array_key_exists($key,$projecttree->tasks))
             $head = $projecttree->tasks[$key];
        else
            abort(403, 'Key '.$key.' Not Found');
		
		$this->defects = [];
		$this->FindDefect($head,1,10);
		$all_defects = $this->defects;
		
		$this->defects = [];
		
		$this->FindDefect($head,1,2);
		$high_priority_defects = $this->defects;
		//dd($high_priority_defects);
		
		$this->defects = [];
		$this->FindDefect($head,3,10);
		$low_priority_defects = $this->defects;
		
		//dd($all_defects);
		//if(count($data->data)==0)
		//	abort(403, 'Burnup Chart Does Not Exist');
		return View('widgets.defects',compact('user','project','all_defects','high_priority_defects','low_priority_defects','key','iframe'));
	}
	
}
