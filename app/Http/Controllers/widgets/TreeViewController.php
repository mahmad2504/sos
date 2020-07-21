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
use Carbon\Carbon;
use App\services\Calendar;
class TreeViewController extends Controller
{
	private $treedata = []; 
	private $blockedtasks = [];
	private $jiraurl = null;
	private $teams = [];
	public function Reduce($task)
	{
		if(isset($task->fixVersions ))
		{
			if(count($task->fixVersions)>0)
				$task->fixVersions = implode(",",$task->fixVersions);
			else
				$task->fixVersions = $task->other_field;
		}
		
		foreach ($task as $property => $value)
		{
			if(
			($property == 'extid')||
			($property == 'summary')||
			($property == 'children')||
			($property == 'isparent')||
			($property == 'progress')||
			($property == 'status')||
			($property == 'ostatus')||
			($property == 'key')||
			($property == 'sprintname')||
			($property == 'sprintstate')||
			($property == 'sprintid')||
			($property == 'fixVersions')||
			($property == 'estimate')||
			($property == 'timespent')||
			($property == 'created')||
			($property == 'duplicate')||
			($property == 'issuetype')||
			($property == 'assignee')
			)
			{
				//$task->children=[];
				
				
			}
			else
				unset($task->$property);
		}
		if($task->isparent == 0)
			unset($task->children);
		else
		{
			foreach($task->children as $ctask)
			{
				$this->Reduce($ctask);
			}
		}
	
	}
	public function ProcessSprintName($task)
	{
		$sprints=[];
		foreach($task->allsprints as $sprint)
		{
			$sprint_name = $sprint;
			$sprint = preg_split("/ /", $sprint);
			if(count($sprint)==0)
			{
				continue;
				
			}
			
			$obj =new \StdClass();
			//dump($sprint);
			if(in_array('CB',$sprint))
				$obj->team = 'CB';
			else if(in_array('NUC',$sprint))
				$obj->team = 'NUC';
			else if(in_array('MEIF',$sprint)) 
				$obj->team = 'MEIF';
			else
				continue;
			$this->teams[$obj->team]= $obj->team;
			if(in_array('2019',$sprint))
				$obj->year = '2019';
			else if(in_array('2020',$sprint))
				$obj->year = '2020';
			else if(in_array('2021',$sprint))
				$obj->year = '2021';
			else if(in_array('2022',$sprint))
				$obj->year = '2022';
			else if(in_array('2023',$sprint))
				$obj->year = '2023';
			else if(in_array('2024',$sprint))
				$obj->year = '2024';
			else if(in_array('2025',$sprint))
				$obj->year = '2025';
			else
				continue;
			
			$obj->number = $sprint[count($sprint)-1];
			if(strlen($obj->number)==1)
				$obj->number = "0".$obj->number;
			$obj->origname = $sprint_name;
			$sprints[] = $obj;
		}
		return $sprints;
	}
	function cmp($a, $b) 
	{
		$a = $a->team.$a->year.$a->number;
		$b = $b->team.$b->year.$b->number;
		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}
	public function ProcessSprints($task,$firstcall=1)
	{
		$task->allsprints = $this->ProcessSprintName($task);
		usort( $task->allsprints, array( $this, 'cmp' ) ); 
		$task->sprintsplit = [];
		foreach($task->allsprints as $sprint)
		{
			if(!isset($task->sprintsplit[$sprint->team]))
				$task->sprintsplit[$sprint->team] = [];
			$task->sprintsplit[$sprint->team][] = $sprint;
		}
		krsort($task->sprintsplit);
		//dd($task->sprintsplit);
			
		foreach($task->children as $ctask)
		{
			$this->ProcessSprints($ctask,0);
		}
		
	}
	public function GetChildEpics($task)
	{
		$data = [];
		foreach($task->children as $child)
		{
			$obj = new \StdClass();
			$obj->key = $child->key;
			$obj->summary = $child->summary;
			$obj->sprintsplit = $child->sprintsplit;
			$data[] = $obj;
		}
		return $data;
	}
	//mujhey pata nahin kion aysi aurat buhut dilchasp lagit hey jo  nikah keh baad mard ki banhoun main lutf ley 
	public function ShowSprintSplit(Request $request)
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
		
		$iframe = $request->iframe;
		if($iframe == null)
			$iframe = 0;
		else
			$iframe  = 1;
		
		$projecttree = new ProjectTree($project);
		$url = $projecttree->GetJiraUrl();
		
		//dd($projecttree->tree->children[17]);
		$tree = $projecttree->GetHead();
		
		$this->ProcessSprints($tree);
		$data = [];
		foreach($tree->children as $child)
		{
			$obj = new \StdClass();
			$obj->key = $child->key;
			$obj->summary = $child->summary;
			$obj->sprintsplit = $child->sprintsplit;
			$obj->children = $this->GetChildEpics($child);
			foreach($obj->children as $cchild)
			{
				$c = $projecttree->tasks[$cchild->key];
				if($c->issuetype == 'REQUIREMENT')
				{
					$cchild->children = $this->GetChildEpics($c);
				}
				
			}
				//if($cchild->issuetype == 'REQUIREMENT')
				//{
					//$cchild->children[] = $this->GetChildEpics($cchild);
					//dd($cchild->children);
				//}
			//}
			$data[] = $obj;
		}
		
		$start = Carbon::now();
		$start->subDays(63);
		$end = Carbon::now();
		$end=  $end->addDays(800);
		
		//ob_start('ob_gzhandler');

		$calendar =  new Calendar($start,$end);
		$tabledata = $calendar->GetGridData();
		$teams = $this->teams;
		return View('widgets.sprintsplit',compact('url','user','project','isloggedin','data','tabledata','teams'));
		//dd("I am here");
		
	}
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
		
		$iframe = $request->iframe;
		if($iframe == null)
			$iframe = 0;
		else
			$iframe  = 1;
		
		$projecttree = new ProjectTree($project);
		//dd($projecttree->tree);
		$tree = $projecttree->GetHead();
		$this->Reduce($tree);
		$head[] = $tree;
		if($request->view==1)
			return View('widgets.treeview.treetable',compact('user','project','isloggedin','iframe','head'));
		if($request->view==2)
			return View('widgets.treeview.tabulator',compact('user','project','isloggedin','iframe','head'));
		return View('widgets.treeview.treetable',compact('user','project','isloggedin','iframe','head'));
    }
	public function GetData(Request $request)
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
		$row['issuesubtype'] = 'DEV';
		if(isset($task->issuesubtype))
			$row['issuesubtype'] = $task->issuesubtype;
		if(isset($task->created))
			$row['created'] = $task->created;
		//dd($task->description);
		$row['oissuetype'] = $task->oissuetype;
    	
		if(($task->isparent == 1)&&(isset($task->description)))
			$row['summary'] = $task->_summary." ";//.$task->description;
		else
    	$row['summary'] = $task->_summary;
		
    	$row['jiraurl'] = $this->jiraurl;
    	$row['key'] = $task->key;
		$row['estimate'] = $task->estimate;
		$row['timespent'] = $task->timespent;
		$row['progress'] = $task->progress;
		$row['duplicate'] = $task->duplicate;
		$row['status'] = $task->status;
		$row['ostatus'] = $task->ostatus;
		$row['priority'] = $task->priority;
		$row['dependson'] = $task->dependson;
		$row['sprintname'] = $task->sprintname;
		$row['sprintstate'] = $task->sprintstate;
		$row['sprintid'] = $task->sprintid;
		$row['assignee'] = $task->assignee;
		$row['backlog_priority'] = $task->_backlog_priority;
		$row['versions']  = '';
		if(isset($task->fixVersions ))
		{
			if(count($task->fixVersions)>0)
				$row['versions'] = implode(",",$task->fixVersions);
			else
				$row['versions'] = $task->other_field;
		}
		//$row['versions']  = count($task->fixVersions);
	
		
		$row['risk_severity'] = $task->risk_severity;
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
