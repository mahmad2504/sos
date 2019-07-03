<?php
namespace App;
use App\Utility;
use App\Jira;
use App\Resource;
use App\Calendar;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ProjectController;

class Task
{
	public $children = array();
	private $parent = null;
	function __construct($parent,$level,$pextid,$pos,$summary=null,$query=null)
	{
		$this->summary = $summary;
		$this->query = $query;
		$this->key = '';
		$this->level = $level;
		$this->pos = $pos;
		$this->pextid = $pextid;
		$this->parent = $parent;
		if($pextid == 0)
			$this->extid = $level;
		else
			$this->extid = $this->pextid.".".$this->pos;
		
		$this->instancecount = 1;
		$this->storypoints = 0;
		$this->estimate = 0;
		$this->timeestimate = 0;
		$this->timespent = 0;
		$this->isparent = 0;
		$this->priority = 0;
		$this->ostatus ='';
		$this->status = 'OPEN';
		$this->progress = 0;
		$this->oissuetype = '';
		$this->sprintname = '';
		$this->sprintstate = '';
		$this->sprintid = '';
		$this->issuetype = 'PROJECT';
		$this->assignee = 'unassigned';
		$this->dependencies_present = 0; // valid only for head
		$this->blockers_present = 0; // valid only for head
		$this->dependson = [];
	}
	
	function MapIssueType($issuetype)
	{
		if(($issuetype=='ESD Requirement')||($issuetype=='BSP Requirement'))
			return 'REQUIREMENT';
		
		if(($issuetype=='Workpackage')||($issuetype=='Project'))
			return 'WORKPACKAGE';
		
		if($issuetype=='Bug')
			return 'DEFECT';
		
		if($issuetype=='Epic')
			return 'EPIC';
		
		if(($issuetype=='Issue')||($issuetype=='Risk')||($issuetype=='Bug')||($issuetype=='Task')||($issuetype=='Story')||($issuetype=='Product Change Request')||($issuetype=='New Feature')||($issuetype=='Improvement'))
			return 'TASK';
		
		Utility::ConsoleLog(time(),"Unmapped type=".$issuetype);
		exit();
		//
	}
	function MapStatus($status)
	{
		if(($status=='Requested')||($status=='Open')||($status == 'Committed')||($status == 'Draft')||($status == 'Withdrawn')||($status == 'Reopened')||($status == 'New'))
			return 'OPEN';
		if(($status=='Closed')||($status=='Resolved')||($status=='Implemented')||($status=='Validated')||($status=='Satisfied'))
			return 'RESOLVED';
		
		if($status=='Open')
			return 'OPEN';
		
		if(($status == 'In Analysis')||($status == 'In Progress')||($status == 'Code Review')||($status == 'In Review'))
			return 'INPROGRESS';
		Utility::ConsoleLog(time(),"Unmapped status=".$status);
		exit();
	}

	public function ExecuteQuery($jiraconf)
	{
		$story_points = $jiraconf['storypoints']; // custom field
		$sprint = $jiraconf['sprint']; // custom field
		
		global $estimation_method;
		global $unestimated_count;
		//Utility::ConsoleLog(time(),$this->level." ".$this->key);
		if($this->query == null)
			return;
	      
		Utility::ConsoleLog(time(),"Running Query ".$this->query);
		$fields = 'description,summary,status,issuetype,priority,assignee,issuelinks,';
		
		if($estimation_method == 1)
			$tasks = Jira::Search($this->query,1000,$fields.$story_points.",".$sprint);
		else if($estimation_method == 2)
			$tasks = Jira::Search($this->query,1000,$fields.'timeoriginalestimate,aggregatetimespent,'.$sprint);
		else
			$tasks = Jira::Search($this->query,1000,'timeoriginalestimate,aggregatetimespent,'.$fields.$story_points.",".$sprint);
		
		
		$j=0;
		foreach($tasks as $key=>$task)
		{
			$ntask = new Task($this->parent,$this->level+1,$this->extid,$j++);
			$ntask->key = $key;
			$ntask->id = $task->id;
			$ntask->otatus = $task->fields->status->name;
			$ntask->status = $this->MapStatus($task->fields->status->name);
			$ntask->summary = $task->fields->summary;
			$ntask->oissuetype = $task->fields->issuetype->name;
			$ntask->issuetype = $this->MapIssueType($task->fields->issuetype->name);
			
			$sprintname = '';
			$sprintstate = '';
			$sprintid = '';
			if(isset($task->fields->$sprint))
			{
				$str = $task->fields->$sprint[count($task->fields->$sprint)-1];
				$sprint_info = explode(',',$str);
				for($i=0;$i<count($sprint_info);$i++)
				{
					$keyvalue = explode('=',$sprint_info[$i]);
					if($keyvalue[0] =='name')
					{
						$sprintname = $keyvalue[1];
					}
					else if($keyvalue[0] =='state')
					{
						$sprintstate = $keyvalue[1];
					}
					else if($keyvalue[0] == 'rapidViewId')
					{
						$sprintid = $keyvalue[1];
					}
				}
				$ntask->sprintname = $sprintname;
				$ntask->sprintstate = $sprintstate;
				$ntask->sprintid = $sprintid;
			}
			
			if(isset($task->fields->assignee))
			{
				/*ResourcesContainer::Create($task->fields->assignee->name,
								  $task->fields->assignee->displayName,
								  $task->fields->assignee->emailAddress,
								  $task->fields->assignee->timeZone);*/
				$resource =  new Resource;
				$resource->name = $task->fields->assignee->name;
				$resource->displayname = $task->fields->assignee->displayName;
				$resource->email = $task->fields->assignee->emailAddress;
				$resource->timeZone = $task->fields->assignee->timeZone;
			
				$this->parent->resources[$task->fields->assignee->name]	= $resource;				
				$ntask->assignee = $task->fields->assignee->name;
			}
			else
			{
				//ResourcesContainer::Create('unassigned','unassigned','','');
				$resource =  new Resource;
				$resource->name = 'unassigned';
				$resource->displayname = 'unassigned';
				$resource->email = '';
				$resource->timeZone = '';
				
				$this->parent->resources['unassigned']	= $resource;	
				$ntask->assignee = 'unassigned';
			}
				
			$ntask->query = null;
			if(($ntask->issuetype == 'REQUIREMENT')||($ntask->issuetype == 'WORKPACKAGE'))
				$ntask->query = 'issue in linkedIssues("'.$ntask->key.'","implemented by")';
			if($ntask->issuetype == 'EPIC')
				$ntask->query = "'Epic Link'=".$ntask->key;
			
			if(isset($task->fields->$story_points))
				$ntask->storypoints = $task->fields->$story_points;
			
			if(isset($task->fields->timeoriginalestimate))
				$ntask->timeestimate = round($task->fields->timeoriginalestimate/(28800),1);
			$ntask->estimate = $ntask->timeestimate;
			if($ntask->storypoints>0)
				$ntask->estimate = $ntask->storypoints;
			$ntask->priority = $task->fields->priority->id;
			$ntask->dependson = [];
			foreach($task->fields->issuelinks as $issuelink)
			{
				if( strtolower($issuelink->type->name) == 'dependency')
				{
					if(isset($issuelink->outwardIssue))
					{
						$ntask->dependson[] = $issuelink->outwardIssue->key;
					}
				}
			}
			//var_dump($ntask->dependson);
			$ntask->timespent =  0;
			//Utility::ConsoleLog(time(),"Estimate is ".$ntask->estimate);
			
			if($ntask->timespent > 0)
			{
				if($ntask->status == 'OPEN')
					$ntask->status = 'INPROGRESS';
			}
			//if($ntask->status == 'RESOLVED')
			//	$ntask->timespent = $ntask->estimate;
			//else
				
			if(isset($task->fields->aggregatetimespent))
				$ntask->timespent =  round($task->fields->aggregatetimespent/(28800),1);
			
			//echo $ntask->timespent;
			$this->isparent = 1;
			$this->children[] = $ntask;
			$buffer = '';
			if($ntask->issuetype == 'TASK')
				if($ntask->estimate == 0)
					$unestimated_count++;
			//for($i=0;$i<$ntask->level;$i++)
			//	$buffer .= '      ';
			//Utility::ConsoleLog(time(),$buffer." ".$ntask->extid." ".$ntask->priority." ".$ntask->key."  ".$ntask->estimate."/".$ntask->storypoints." ".$ntask->query." ".$ntask->status);
		}
	}
}
class ProjectTree
{
	private $datapath=null;
	private $treepath=null;
	private $tree=null;
	private $project=null;
	private $user=null;
	private $jiraconfig = null;
	private $tasks = [];
	public $presources = [];
	public $resources = [];
	function __construct(Project $project)
	{
		$user = $project->user()->first();
		$this->presources = $project->resources()->get();
		$this->datapath = Utility::GetDataPath($user,$project);
		if(!file_exists($this->datapath))
    		mkdir($this->datapath, 0, true);
		$this->treepath = $this->datapath."/tree";
		$this->user = $user;
		$this->project  = $project;
		
		$this->jiraconfig = Utility::GetJiraConfig($project->jirauri);
		if(file_exists($this->treepath))
		{
			$this->tree = unserialize(file_get_contents($this->treepath));
		}
	}
	public function __get($property) 
	{
		switch($property)
		{
			case 'start':
				return $this->project->start;
			case 'end':
				return $this->project->end;
			case 'name':
				return $this->project->name;
			
		}
		if (property_exists($this, $property)) {
		return $this->$property;
    }
  }
	function GetJiraUrl()
	{
		return $this->jiraconfig['uri'];
	}
	function Populate($task)
	{
		$task->ExecuteQuery($this->jiraconfig);
		foreach($task->children as $stask)
			$this->Populate($stask);
	}
	function ComputeStatus($task)
	{
		if($task->isparent == 0)
		{
			if($task->status == 'OPEN')
				if($task->timespent > 0)
					$task->status = 'INPROGRESS';
				
			return $task->status;
		}
		$children = $task->children;
		foreach($task->children as $child)
		{
			$status = $this->ComputeStatus($child);
			$status_array[$status] = 1;
		}
		
		if (array_key_exists("INPROGRESS",$status_array))
			$task->status = "INPROGRESS";
		else if (array_key_exists("OPEN",$status_array))
			$task->status = "OPEN";
		else if (array_key_exists("RESOLVED",$status_array))
			$task->status = "RESOLVED";
		
		return $task->status;
	}
	function ComputeEstimate($task)
	{
		if($task->isparent == 0)
			return $task->estimate;
		$children = $task->children;
		$acc = 0;
		foreach($task->children as $child)
			$acc += $this->ComputeEstimate($child);
		
		$task->estimate = $acc;
		return $task->estimate;
	}
	function ComputeTimeSpent($task)
	{
		if($task->isparent == 0)
		{
			if($task->status == 'RESOLVED')
				$task->timespent = $task->estimate;
			return $task->timespent;
		}
		$children = $task->children;
		$acc = 0;
		foreach($task->children as $child)
			$acc += $this->ComputeTimeSpent($child);
		
		$task->timespent = $acc;
		if($task->status == 'RESOLVED')
			$task->timespent = $task->estimate;
		
		return $task->timespent;
	}
	function ComputeProgress($task)
	{
		$estimate = $task->estimate;
		if($estimate == 0)
			$estimate = 1;
				
		$task->progress = round($task->timespent/$estimate*100,1);
		//echo $task->progress." ".$task->timespent." ".$estimate."\r\n";
		if($task->progress > 100)
			$task->progress = 100;
		
		if($task->status == 'RESOLVED')
			$task->progress = 100;
		$children = $task->children;
		foreach($task->children as $child)
			$this->ComputeProgress($child);
	}
	function FindDuplicates($task)
	{
		if($task->isparent == 0)
		{
			if(array_key_exists($task->key,$this->tasks))
			{
				ConsoleLog::Send(time(),'Warning::'.$task->key." Duplicate in plan");
				$this->tasks[$task->key]->instancecount++;
			}
			else
			{
				$this->tasks[$task->key]=$task;
			}
		}
		foreach($task->children as $stask)
			$this->FindDuplicates($stask);
	}
	function UpdateDependencies($head)
	{
		foreach($this->tasks as $task)
		{
			//Utility::ConsoleLog(time(),'********'.count($task->dependson));
			if(($task->priority == 1)&&($task->status != 'RESOLVED'))
				$head->blockers_present = 1;
			
			$del = [];
			for($i=0;$i<count($task->dependson);$i++)
			{
				$key = $task->dependson[$i];
				//Utility::ConsoleLog(time(),'###########'.$key);
				if(array_key_exists($key,$this->tasks))
				{
					//Utility::ConsoleLog(time(),'###########'.'exist');
					$ptask = $this->tasks[$key];
					if($ptask->status == 'RESOLVED')
						$del[] = $i;
					else
						$head->dependencies_present = 1;
				}
				else
					$head->dependencies_present = 1;
			}
			foreach($del as $d)
				unset($task->dependson[$d]);
		}
	}
	function Sync($rebuild=0)
	{
		if($rebuild == 1)
			Utility::ConsoleLog(time(),'Rebuilding Project - '.$this->project->name);
		else
			Utility::ConsoleLog(time(),'Building Project - '.$this->project->name);
		
		Jira::Initialize($this->jiraconfig['uri'],$this->jiraconfig['username'],$this->jiraconfig['password'],$this->datapath,$rebuild);
		
		
		$task = new Task($this,1,0,0,$this->project->name,$this->project->jiraquery);
		$this->Populate($task);
		$this->ComputeStatus($task);
		$this->ComputeEstimate($task);
		$this->ComputeTimeSpent($task);
		$this->ComputeProgress($task);
		$this->FindDuplicates($task);
		$this->UpdateDependencies($task);
		$this->ComputeTotalCorrectedEstimates($task);
		$this->tree = $task;
		
		
		$allresources = ProjectResource::where('project_id',$this->project->id)->get();
		
		foreach($allresources as $resource)
		{
			$resource->active = 0;
			$resource->save();
		}
		
			
		// Update Resources Database 
		foreach($this->resources as $res)
		{
			$resource = Resource::where('name',$res->name)->first();
			if($resource != null)
			{
				$resource->Modify($res);
				
			}
			else // new resource
			{
				$resource = $res;
				$resource->save();
			}
			$projectresource = ProjectResource::where('resource_id',$resource->id)->where('project_id',$this->project->id)->first();
			if($projectresource !=  null)
			{
				$projectresource->active = 1;
				$projectresource->save(); /// Updates resource for a project
			}
			else
			{
				$projectresource =  new ProjectResource;
				$projectresource->project_id =$this->project->id;
				$projectresource->resource_id = $resource->id;
				$projectresource->save(); // Creates new resource for a project
			}
			$calendar = Calendar::where('resource_id',$resource->id)->first();
			if($calendar == null)
			{
				$cal =  new Calendar;
				$cal->resource_id = $resource->id;
				$cal->save();
			}
		}
		$this->presources = $this->project->resources()->get();
		//var_dump($this->resources);
		//dd($this->tree);
		$data = serialize($task);
    	file_put_contents($this->treepath, $data);
		
		$last_synced = date ("Y/m/d H:i" , filemtime($this->treepath));
		ProjectController::UpdateProgressAndLastSync($this->project->id,$task->progress,$last_synced);
    	Utility::ConsoleLog(time(),"Sync Completed Successfully");
	}
	function GetHead()
	{
		return $this->tree;
	}
	
	function ComputeTotalCorrectedEstimates($task) // Removes Duplicates
	{
		$totalestimate = 0;
		$totaltimespent = 0;
		
		foreach($this->tasks as $task)
		{
			$totalestimate += $task->estimate;
			$totaltimespent += $task->timespent;
		}
		$totalprogress  = 0;
		if($totalestimate > 0)
			$totalprogress = round($totaltimespent/$totalestimate*100,1);
		$task->progress = $totalprogress;
		$task->estimate = $totalestimate;
		$task->timespent = $totaltimespent;
	}
}