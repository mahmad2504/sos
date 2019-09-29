<?php
namespace App;
use App\Utility;
use App\Jira;
use App\Resource;
use App\Calendar;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CalendarController;

class Task
{
	public $children = array();
	public $parent = null;

	public function __get($field) 
	{
		switch($field)
		{
			case '_tstart':
				$t = $this;
				while(strlen(trim($t->tstart)) == 0)
				{
					$parent=$t->_parenttask;
					if($parent == null)
						return $this->_project_start;
					$t = $parent;
				}
				return $t->tstart;
				break;
			case '_tend':
				$t = $this;
				while(strlen(trim($t->_duedate)) == 0)
				{
					$parent=$t->_parenttask;
					if($parent == null)
						return $this->_project_end;
					$t = $parent;
				}
				return $t->_duedate;
				break;
			case '_project_end':
				return $this->parent->project->edate;
				break;
			case '_project_start':
				return $this->parent->project->sdate;
				break;
			case '_parenttask':
				if(array_key_exists($this->pextid,$this->parent->tasksbyextid))
				{
					return $this->parent->tasksbyextid[$this->pextid];
				}
				return null;
			case '_projectestimation':
				return $this->parent->project->estimation;
				break;
			case '_orig_estimate':
				if(isset($this->oestimate))
					return $this->oestimate; 
				if($this->parent->project->estimation == 0)// Story points
					return  $this->storypoints;
				else
					return $this->timeestimate;
				break;
			case '_sched_start';
				if(!isset($this->sched_start))
					$start = $this->twin->sched_start;
				else
					$start  = $this->sched_start;
				return $start;
			case '_sched_end';
				if(!isset($this->sched_end))
					$end = $this->twin->sched_end;
				else
					$end = $this->sched_end;
				return $end;
			case '_sched_assignee':
				if(!isset($this->sched_assignee))
					$pres = $this->twin->sched_assignee;
				else
					$pres =  $this->sched_assignee;
				if(strlen(trim($pres))==0)
					$pres = $this->assignee;
				return $pres;
			case '_startconstraint':
				if(($this->isconfigured == "true")||($this->isconfigured == 1))
				{
					if(strlen(trim($this->tstart)) > 0)
						return $this->tstart;
				}
				return null;
				break;
			case '_summary':
				if(($this->isconfigured == "true")||($this->isconfigured == 1))
				{
					if(strlen(trim($this->atext)) > 0)
						return $this->atext;
					else
						return $this->summary;
				}
				return $this->summary;
				break;
			case '_duedate':
				if(($this->extid == "1")&&($this->level==1))
				{
					if(strlen(trim($this->tend)) > 0)
						return $this->tend;

					return $this->_project_end;
				}

				if(($this->isconfigured == "true")||($this->isconfigured == 1))
				{
					if(strlen(trim($this->tend)) > 0)
						return $this->tend;
					else
						return $this->duedate;
				}
				return $this->duedate;
				break;
		}
	}
	function __construct($parent,$level,$pextid,$pos,$summary=null,$query=null)
	{
		$this->worklogs = [];
		$this->isconfigured = false;
		$this->position = -1;
		$this->ismilestone = false;
		$this->atext = '';
		$this->summary = $summary;
		$this->query = $query;
		$this->duedate = '';
		$this->tstart = '';
		$this->tend = '';
		$this->level = $level;
		$this->pos = $pos;
		$this->pextid = $pextid;
		$this->parent = $parent;
		if($pextid == 0)
			$this->extid = $level;
		else
			$this->extid = $this->pextid.".".$this->pos;
		$this->key = $this->extid;
		$this->closedon = null;
		$this->schedule_priority = 0;
		$this->duplicate = 0;
		$this->instancecount = 1;
		$this->storypoints = 0;
		$this->estimate = 0;
		$this->timeestimate = 0;
		$this->timespent = 0;
		$this->updated = 0;
		$this->otimespent = 0;
		$this->isparent = 0;
		$this->priority = 0;
		$this->ostatus ='';
		$this->status = 'OPEN';
		$this->progress = 0;
		$this->oissuetype = '';
		$this->sprintname = '';
		$this->sprintstate = '';
		$this->sprintid = '';
		$this->sprintno = -1;
		$this->issuetype = 'PROJECT';
		$this->assignee = 'unassigned';
		$this->description = '';
		$this->risk_severity = 'None';
		$this->dependencies_present = 0; // valid only for head
		$this->blockers_present = 0; // valid only for head
		$this->dependson = [];
	}

	function MapIssueType($issuetype,$key)
	{
		if( ($issuetype=='Feature')||($issuetype == ' Customer Requirement')||($issuetype=='ESD Requirement')||($issuetype=='BSP Requirement')||($issuetype=='Requirement'))
			return 'REQUIREMENT';

		if(($issuetype=='Workpackage')||($issuetype=='Project')||($issuetype=='Subproject'))
			return 'WORKPACKAGE';

		if($issuetype=='Bug')
			return 'DEFECT';

		if($issuetype=='Epic')
			return 'EPIC';

		if(($issuetype=='Documentation')||($issuetype=='Action')||($issuetype=='Dependency')||($issuetype=='Sub-task')||($issuetype=='Issue')||($issuetype=='Risk')||($issuetype=='Bug')||($issuetype=='Task')||($issuetype=='Story')||($issuetype=='Product Change Request')||($issuetype=='New Feature')||($issuetype=='Improvement'))
			return 'TASK';

		Utility::ConsoleLog(time(),"Error::Unmapped type=[".$key." ".$issuetype."]");
		return 'TASK';
		//
	}
	function MapStatus($status)
	{
		if( ($status=='Created')||($status=='Blocked')||($status=='To Do')||($status=='Requested')||($status=='Open')||($status == 'Committed')||($status == 'Draft')||($status == 'Withdrawn')||($status == 'Reopened')||($status == 'New'))
			return 'OPEN';
		if(($status=='Verified')||($status=='Done')||($status=='Closed')||($status=='Resolved')||($status=='Implemented')||($status=='Validated')||($status=='Satisfied'))
			return 'RESOLVED';

		if(($status == 'Monitored')||($status == 'In Analysis')||($status == 'In Progress')||($status == 'Code Review')||($status == 'In Review')||($status == 'RC: Release')||($status == 'PROJECT DEFINITION')||($status == 'PROJECT PLANNING')||($status == 'CLOSE DOWN'))
			return 'INPROGRESS';
		Utility::ConsoleLog(time(),"Unmapped status=".$status);
		return 'OPEN';
	}

	private function SearchInJira($query,$jiraconf,$order=null)
	{
		//echo $query."<br>";
		$story_points = $jiraconf['storypoints']; // custom field
		$risk_severity = $jiraconf['risk_severity']; // custom field
		
		$sprint = $jiraconf['sprint']; // custom field
		$fields = 'updated,duedate,id,subtasks,resolutiondate,description,summary,status,issuetype,priority,assignee,issuelinks,';
		if($this->parent->project->task_description==1)
			$fields .= ',description';
		
		$tasks = Jira::Search($query,1000,$fields.','.$story_points.','.$risk_severity.',timeoriginalestimate,timespent,'.$sprint,$order);
		//dd($tasks);
		return $tasks;
	}
	public function CreateTask($jiraconf,$task,$level,$pextid,$pos)
	{
		global $unestimated_count;
		$story_points = $jiraconf['storypoints']; // custom field
		$risk_severity = $jiraconf['risk_severity']; // custom field
		$link_implemented_by  = $jiraconf['link_implemented_by'];
		$link_parentof = $jiraconf['link_parentof']; 
		
		$sprint = $jiraconf['sprint']; // custom field

		$ntask = new Task($this->parent,$level,$pextid,$pos);
		
		$ntask->key = $task->key;;
		$ntask->id = $task->id;
		$ntask->ostatus = $task->fields->status->name;
		$ntask->updated = $task->fields->updated;
		if(isset($task->fields->resolutiondate))
			$ntask->closedon = explode('T',$task->fields->resolutiondate)[0];
		//echo $ntask->key." ".$ntask->closedon."<br>";
		$ntask->status = $this->MapStatus($task->fields->status->name);
		if(($ntask->status == 'RESOLVED') && ($ntask->closedon == null) && !isset($this->parent->tempflag))
		{
			$this->parent->tempflag = 1;
			Utility::ConsoleLog(time(),"Error::"."Warning::Closedon date missing for resolved task. Check Your Jira Configurations");
			$ntask->closedon = $ntask->updated;
			//dd($task);
		}
		$ntask->summary = $task->fields->summary;
		$ntask->oissuetype = $task->fields->issuetype->name;
		//$ntask->updated = $task->fields->updated;
	
		$ntask->issuetype = $this->MapIssueType($task->fields->issuetype->name,$task->key);
		$sprintname = '';
		$sprintstate = '';
		$sprintid = '';
		$sprintno = -1;
		if(isset($task->fields->$sprint))
		{
			$last_sequence = 0;
			//echo count($task->fields->$sprint);
			for($j=0;$j<count($task->fields->$sprint);$j++)
			{
				$str = $task->fields->$sprint[$j];
				$sequence = explode('sequence=',$str)[1];
				$sequence = explode(']',$sequence)[0];
				//echo $sequence;
				if($sequence < $last_sequence)
				{
					continue;
				}
				$last_sequence = $sequence;
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
			}
			$ntask->sprintname = $sprintname;
			$ntask->sprintstate = $sprintstate;
			$ntask->sprintid = $sprintid;
			$ntask->sprintno = $last_sequence;
		}
		if(isset($task->fields->assignee))
		{
			$resource =  new Resource;
			$resource->name = str_replace (".", "_", $task->fields->assignee->name);
			$resource->displayname = $task->fields->assignee->displayName;
			$resource->email = '';
			if(isset($task->fields->assignee->emailAddress))
				$resource->email = $task->fields->assignee->emailAddress;
			$resource->timeZone = $task->fields->assignee->timeZone;

			$this->parent->resources[$task->fields->assignee->name]	= $resource;
			$ntask->assignee = $resource->name;
		}
		else
		{
			$resource =  new Resource;
			$resource->name = 'unassigned';
			$resource->displayname = 'unassigned';
			$resource->email = '';
			$resource->timeZone = '';

			$this->parent->resources['unassigned']	= $resource;
			$ntask->assignee = 'unassigned';
		}
		$ntask->query = null;
		$link_parentof = 'Is Parent of';
		if(($ntask->issuetype == 'REQUIREMENT')||($ntask->issuetype == 'WORKPACKAGE'))
			$ntask->query = 'issue in linkedIssues("'.$ntask->key.'","'.$link_implemented_by.'") || issue in linkedIssues("'.$ntask->key.'","'.$link_parentof.'")';
		if($ntask->issuetype == 'EPIC')
			$ntask->query = "'Epic Link'=".$ntask->key;
		if(count($task->fields->subtasks)>0)
			$ntask->query = "parent=".$ntask->key;
		
		if(isset($task->fields->description))
			$ntask->description = $task->fields->description;
		
		if(isset($task->fields->timeoriginalestimate))
			$ntask->timeestimate = round($task->fields->timeoriginalestimate/(28800),1);
		if(isset($task->fields->$story_points))
			$ntask->storypoints = $task->fields->$story_points;

		if(isset($task->fields->$risk_severity))
		{
			$ntask->risk_severity = $task->fields->$risk_severity->value;
		}

		$ntask->estimate = $ntask->_orig_estimate;
		$ntask->priority = $task->fields->priority->id;
		//if($ntask->key == 'IP-72')
		//	dd($task);
		$ntask->dependson = [];
		foreach($task->fields->issuelinks as $issuelink)
		{
			if($this->parent->project->jira_dependencies==1)
			{
				if( strtolower($issuelink->type->name) == 'dependency')
				{
					if(isset($issuelink->outwardIssue))
					{
						$ntask->dependson[] = $issuelink->outwardIssue->key;
						Utility::ConsoleLog(time(),$ntask->key." depends on".$issuelink->outwardIssue->key);
					}
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
		if(isset($task->fields->timespent))
		{
			//echo $ntask->key." ".$task->fields->timespent."<br>";
			$ntask->timespent =  round($task->fields->timespent/(28800),1);
			if(($task->fields->timespent > 0)&&($ntask->timespent==0))
			{
				$ntask->timespent = round($task->fields->timespent/(28800),3);
				//echo $ntask->key." ".$task->fields->timespent." ".$ntask->timespent."<br>";
			}
			//echo $ntask->key." ".$task->fields->timespent." ".$ntask->timespent."<br>";
			$ntask->otimespent = $ntask->timespent;
		}
		if(isset($task->fields->duedate)&&($ntask->status != 'RESOLVED'))
		{
			$ntask->duedate = $task->fields->duedate;
			if( $ntask->duedate < Utility::GetToday('Y-m-d'))
				Utility::ConsoleLog(time(),'Error::'.$task->key." has a invalid Jira duedata (Expired)");
			else if($ntask->duedate < $ntask->_project_start)
				Utility::ConsoleLog(time(),'Error::'.$task->key." has a invalid Jira duedata (Before Project start)");
			else if($ntask->duedate < $ntask->_project_end)
				Utility::ConsoleLog(time(),'Error::'.$task->key." has a invalid Jira duedata (After Project end)");	
		}


		//echo $ntask->timespent;

		$buffer = '';
		if($ntask->issuetype == 'TASK')
			if($ntask->estimate == 0)
				$unestimated_count++;

		$ntask->parent->tasksbyextid[$ntask->extid.""]=$ntask;
		//if($ntask->key == 'IP-83')
		// 	dd($ntask);
		return $ntask;

		//$ntask->key = $jiratask->key;
	}
	public function AddChild(Task $task)
	{
		$this->children[] = $task;
		$this->isparent = 1;
	}
	public function ExecuteQuery($jiraconf)
	{
		$story_points = $jiraconf['storypoints']; // custom field
		$sprint = $jiraconf['sprint']; // custom field

		global $unestimated_count;
		//Utility::ConsoleLog(time(),$this->level." ".$this->key);

		if($this->query == null)
			return 1;

		else if(substr( $this->query, 0, 10 ) === "structure=")
		{
			$structure_id = explode('structure=',$this->query)[1];
			Utility::ConsoleLog(time(),'Wait::Reading Structure '.$structure_id);
			$objects = Jira::GetStructure($structure_id);
			if($objects == null)
				return -1;
			$query = 'id in (' ;
			$del = "";

			foreach($objects as $object)
			{
				$query = $query.$del.$object->taskid;
				$del = ",";
			}
			$query = $query.")";
			$tasks = $this->SearchInJira($query,$jiraconf);
			
			if($tasks == null)
				return -1;
			foreach($tasks as $key=>$jtask)
			{
				$objects[$jtask->id]->data=$jtask;
			}
			$taskatlevel[0] = $this;
			foreach($objects as $object)
			{
				$level = $object->level;
				$parent = $taskatlevel[$level-1];
				//echo $level." Parent is ".$parent->key."<br>";
				$ntask = $this->CreateTask($jiraconf,$object->data,$level,$parent->extid,count($parent->children));
				$parent->AddChild($ntask);


				//echo $ntask->key."  ".$level."<br>";
				$taskatlevel[$level] = $ntask;
			}
			return 0;
		}
		else
		{
			Utility::ConsoleLog(time(),"Running Query ".$this->query);
			$tasks = $this->SearchInJira($this->query,$jiraconf,'ORDER BY Rank ASC');
			if($tasks == null)
				return -1;

			$j=0;

			$sprintno = 0;
			$values = explode('sprint=', strtolower($this->query));
			if(count($values)>1)
				$sprintno = explode(' ', $values[1])[0];
			
			foreach($tasks as $key=>$task)
			{
				//Utility::ConsoleLog(time(),$key);
				$ntask = $this->CreateTask($jiraconf,$task,$this->level+1,$this->extid,$j++);
				//Utility::ConsoleLog(time(),$ntask->sprintno." ".$sprintno);
				if($sprintno > 0)
				{
					//Utility::ConsoleLog(time(),$ntask->sprintno." ".$sprintno);
					if($ntask->sprintno == $sprintno)
					{
						if(($ntask->status != 'RESOLVED')&&($ntask->sprintstate == 'CLOSED'))
						{
							// ignore
						}
						else
							$this->AddChild($ntask);
					}
				}
				else
					$this->AddChild($ntask);
			}
			return 1;
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
	public $tasksbyextid = [];
	public $presources = [];
	public $resources = [];
	private $weeklylogs = [];
	private $milestones = [];
	function __construct(Project $project)
	{
		$user = $project->user()->first();
		$this->presources = $project->resources()->get();
		$this->datapath = Utility::GetDataPath($user,$project);
		if(!file_exists($this->datapath))
    		mkdir($this->datapath, 0, true);
		$this->treepath = $this->datapath."/tree";
		$this->baselinepath = $this->datapath."/baseline";
		$this->user = $user;
		$this->project  = $project;

		$this->jiraconfig = Utility::GetJiraConfig($project);
		$project->jiraurl = Utility::GetJiraURL($project);
		if(file_exists($this->treepath))
		{
			$this->tree = unserialize(file_get_contents($this->treepath));
			$this->FindDuplicates($this->tree);
			//dd($this->tasks);
			//echo "ff";
			//dd($this->tree->oa);
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
		$restval = $task->ExecuteQuery($this->jiraconfig);
		if($restval==1)
		{
			foreach($task->children as $stask)
			{
				$restval = $this->Populate($stask);
			}
		}
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
	function ComputeOEstimate($task)
	{
		if($task->isparent == 0)
		{
			if($task->duplicate == 1)
				return 0;
			return $task->_orig_estimate;
		}
		$children = $task->children;
		$acc = 0;
		foreach($task->children as $child)
			$acc += $this->ComputeOEstimate($child);

		$task->oestimate = $acc; // Only set for group tasks which is fine.
		return $task->oestimate;
	}
	function ComputeEstimate($task)
	{
		if($task->isparent == 0)
		{
			if($task->_projectestimation == 0)//Story points
			{
				if($task->duplicate == 1)
					return 0;
				return $task->estimate;

			}
			if($task->timespent > $task->estimate)
				$task->estimate = $task->timespent;
			if($task->status == 'RESOLVED')
			{
				//if($task->key == 'NUC4-2516')
				//	dd($task);
				if($task->timespent > 0) // if no work logged , make work= estimes
					$task->estimate = $task->timespent;
			}
			if($task->duplicate == 1)
				return 0;
			return $task->estimate;
		}
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
			{
				if($task->timespent == 0) // if no work logged , make work= estimes
					$task->timespent = $task->estimate;
				if($task->_projectestimation == 0)//Story points
					$task->timespent = $task->estimate;
			}
			if($task->duplicate == 1)
				return 0;
			return $task->timespent;
		}
		$children = $task->children;
		$acc = 0;
		foreach($task->children as $child)
			$acc += $this->ComputeTimeSpent($child);

		$task->timespent = $acc;
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
		//if($task->isparent == 0)
		//echo $task->key." ".$task->isconfigured."\r\n";
		//echo $task->summary." ".$task->isconfigured."\r\n";
		
		//echo $task->summary."\r\n";
		{
			if(array_key_exists($task->key,$this->tasks))
			{
				$this->tasks[$task->key]->instancecount++;
				$task->instancecount++;
				if($task->instancecount > 1)
				{
					$task->duplicate = 1;
					$task->twin = $this->tasks[$task->key];
				}
				//Utility::ConsoleLog(time(),'Error::Duplicate');
			}
			else
			{
				$this->tasks[$task->key]=$task;
				$this->tasksbyextid[$task->extid]=$task;
			}
		}
		foreach($task->children as $stask)
			$this->FindDuplicates($stask);
	}
	function UpdateDependencies($head)
	{
		$schedule_priority = 1000;
		foreach($this->tasks as $task)
		{
			if(($task->status == 'INPROGRESS')&&($task->isparent == 0))
			{
				if($task->duplicate ==0)
				{
					$task->schedule_priority = $schedule_priority;
					$schedule_priority--;
				}
			}


			//Utility::ConsoleLog(time(),'********'.count($task->dependson));
			if(($task->priority == 1)&&($task->status != 'RESOLVED'))
				$head->blockers_present = 1;

			$del = [];
			if(($task->duplicate == 1)||($task->status == 'RESOLVED'))
			{
				$task->dependson = [];
				
			}
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
				{
					$del[] = $i;
					Utility::ConsoleLog(time(),'Error::'.$task->key." dependency ".$key." not part of project");
				}
			}
			foreach($del as $d)
			{
				Utility::ConsoleLog(time(),'Removing '.$task->dependson[$d]." from dependency of ".$task->key." [Marked Done]");
				unset($task->dependson[$d]);
			}
		}
		foreach($this->tasks as $task)
		{
			if(($task->schedule_priority == 0)&&($task->isparent == 0)&&($task->status == 'OPEN'))
			{
				$task->schedule_priority = $schedule_priority;
				$schedule_priority--;
			}
			if($schedule_priority == 0)
				break;
		}
	}
	function SyncJira($rebuild=0,$rebuild_worklogs=0)
	{
		if($rebuild == 1)
			Utility::ConsoleLog(time(),'Rebuilding Project - '.$this->project->name);
		else
			Utility::ConsoleLog(time(),'Building Project - '.$this->project->name);
		Jira::Initialize($this->jiraconfig,$this->datapath,$rebuild);

		$queries = preg_replace('~[\r\n]+~', '@', $this->project->jiraquery);
		
		$queries = explode('@',$queries);
		$queries = array_filter($queries);

		if(count($queries)>1)
		{
			$task = new Task($this,1,0,0,$this->project->name,null);
			$pos = 0;
			foreach($queries as $query)
			{
				$query = explode(":",$query);
				$name = $query[0];
				if(count($query)>1)
				{
					$name = $query[1];
					
				}
				$name = str_replace('"',"'",$name);
				//dd($name);
				$query = $query[0];
				//dd($query);
				$ctask = new Task($this,$task->level+1,$task->extid,$pos++,$name,$query);
				$task->AddChild($ctask);
			}
		}
		else
			$task = new Task($this,1,0,0,$this->project->name,$queries[0]);


		$this->Populate($task);
		
		if(Jira::$error==1)
			return -1;
		
		$otasks = $this->tasks;

		$this->tasks = [];
		$this->tasksbyextid = [];
		
	
		$this->FindDuplicates($task);

		$this->ComputeStatus($task);
		$this->ComputeEstimate($task);
		$this->ComputeTimeSpent($task);
		$this->ComputeProgress($task);
		$this->ComputeOEstimate($task);

	

		//$otasks = $this->tasks;
		//$this->tasks = [];
		//$this->FindDuplicates($task);
		foreach($this->tasks as $stask)
		{
			if($stask->instancecount > 1)
				Utility::ConsoleLog(time(),'Warning::'.$stask->key." Duplicate in plan");
		}
		$this->UpdateDependencies($task);
		$this->ComputeTotalCorrectedEstimates($task);

		// Read Any old value from treepath
		$oa = null;
		
		if(isset($this->tree->oa))
			$oa = $this->tree->oa;

		/////////////////////////////////////////////////////////////////////
		$this->tree = $task;
		$this->tree->oa = $oa;

		foreach($this->tasks as $t)
		{
			//echo $t->key." ".$t->otimespent."<br>";
			// RETRIEVE OLD VALUES
			if(isset($otasks[$t->key]))
			{
				$otask = $otasks[$t->key];
				$isconfigured  = 0;
				if(isset($otask->isconfigured))
					$isconfigured = $otask->isconfigured;

				if($isconfigured)
				{
					$t->isconfigured = $otask->isconfigured;
					$t->ismilestone  = $otask->ismilestone;
					$t->atext  = $otask->atext;
					$t->tend =  $otask->tend;
					$t->tstart = $otask->tstart;
					$t->position = $otask->position;
				}
				else
				{
					$t->isconfigured = false;
					$t->ismilestone  = false;
					$t->atext  = "";
					$t->tend = '';
					$t->tstart = '';
					$t->position = -1;
				}
			}
			if($t->otimespent > 0) // All tasks with worklog
			{
				if(isset($otasks[$t->key]))
				{
					$otask = $otasks[$t->key];
					//dd($otask);
					if(($otask->updated != $t->updated)||($rebuild_worklogs==1))
					{
						Utility::ConsoleLog(time(),'Wait::Reading worklogs of '.$t->key);
						$t->worklogs =  Jira::GetWorkLogs($t->key);
					}
					else
						$t->worklogs = $otasks[$t->key]->worklogs;
				}
				else
				{
					Utility::ConsoleLog(time(),'Wait::Reading worklogs of '.$t->key);
					$t->worklogs = Jira::GetWorkLogs($t->key);
				}
				if(isset($t->worklogs))
				{
						foreach($t->worklogs as $date=>$userdata)
						{
							foreach($userdata as $user=>$data)
							{
								if(!isset($this->resources[$user]))
								{
									$resource =  new Resource;
									$resource->name = $data->name;
									$resource->displayname =$data->displayname;
									$resource->email = $data->email;
									$resource->timeZone = $data->timeZone;
									$this->resources[$user]	= $resource;
								}
							}
						}
					}
				}
			}

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
			$info = Utility::GetCountryInfo($resource->timezone);
			//echo $resource->timezone;
			//print_r($info);
			if($info[1] == 'Unknown')
			{
				if(strlen(trim($resource->timezone))>0)
					Utility::ConsoleLog(time(),'Error::'.'Country Info for timezone '.$resource->timezone.' not configured ['.$resource->name.']');
			}
			$cc = $info[0];
			$cn = $info[1];

			$projectresource = ProjectResource::where('resource_id',$resource->id)->where('project_id',$this->project->id)->first();
			if($projectresource !=  null)
			{
				$projectresource->active = 1;
				//Utility::ConsoleLog(time(),'Project Resource ='.$projectresource." ".$projectresource->cc);
				$projectresource->save(); /// Updates resource for a project
			}
			else
			{
				$projectresource =  new ProjectResource;
				$projectresource->project_id =$this->project->id;
				$projectresource->cc = $cc;
				$projectresource->oaid = null;
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
	
		//dd($this);
		/*$data = serialize($task);
    	file_put_contents($this->treepath, $data);

		$last_synced = date ("Y/m/d H:i" , filemtime($this->treepath));
		ProjectController::UpdateProgressAndLastSync($this->project->id,$task->progress,$last_synced);*/
	    //dd($this->tasks);
		//dd($this->tasks);
    	Utility::ConsoleLog(time(),"Jira Sync Completed");
	}
	function ReadBaseline()
	{
		if(file_exists($this->baselinepath))
		{
			$this->baselinetree = unserialize(file_get_contents($this->baselinepath));
			$baselineproject = new ProjectTree($this->project);
			return $baselineproject;
		}
		return null;

	}
	function SaveBaseline()
	{
		$data = serialize($this->tree);
		file_put_contents($this->baselinepath, $data);
		$baseline = date ("Y-m-d" , filemtime($this->baselinepath));
		Utility::ConsoleLog(time(),"Baseline Generated");
		ProjectController::UpdateProgressAndLastSync($this->project->id,$this->tree->progress,$this->last_synced,$baseline);
	}
	function Save()
	{
		//dd($this->tree);
		$data = serialize($this->tree);
		file_put_contents($this->treepath, $data);
		$this->last_synced = date ("Y-m-d" , filemtime($this->treepath));
		ProjectController::UpdateProgressAndLastSync($this->project->id,$this->tree->progress,$this->last_synced);
	}
	function GetHead()
	{
		return $this->tree;
	}

	function ComputeTotalCorrectedEstimates($task) // Removes Duplicates
	{
		$totalestimate = 0;
		$totaltimespent = 0;
		foreach($this->tasks as $stask)
		{
			if($stask->isparent ==0 )
			{
				$totalestimate += $stask->estimate;
				$totaltimespent += $stask->timespent;
			}
		}
		$totalprogress  = 0;
		if($totalestimate > 0)
			$totalprogress = round($totaltimespent/$totalestimate*100,1);
		$task->progress = $totalprogress;
		$task->estimate = $totalestimate;
		$task->timespent = $totaltimespent;
	}
	private static function cmp($a,$b) 
	{
		var_dump($a);
		return true;
	 }
	private function _GetWeeklyWorkLog($task)
	{
		if($task->isparent == 0)
		{
			//$acc = 0;
			foreach($task->worklogs as $date=>$worklog)
			{
				$dte = new \DateTime($date);
				foreach($worklog as $user=>$worklog)
				{
					$y = $dte->format("Y");
					$w = $dte->format("W");
					$w = (int)$w;
					if($w == 1) // some time last few days of year fall in week of next year
					{
						$m = $dte->format("m");
						if($m == 12)
							$w = 53;
					}
					$worklog->jira = $task->key;
					$worklog->summary = $task->summary;
					unset($worklog->timeZone);
					unset($worklog->email);
					$this->weeklylogs[$y][$w][$date][] = $worklog;
					//$acc += $worklog->hours; 
				}
			}
			//echo $task->key." ".$task->timespent." ".($acc/8)."<br>";
		}
		else foreach($task->children as $stask)
			$this->_GetWeeklyWorkLog($stask);
	}
	public function GetWeeklyWorkLog($task)
	{
		$this->weeklylogs = [];
		$this->_GetWeeklyWorkLog($task);
		foreach($this->weeklylogs as $year=>$d)
		{
			foreach($this->weeklylogs[$year] as $week=>$w)
			{
				ksort($this->weeklylogs[$year][$week]);
			}
			ksort($this->weeklylogs[$year]);
		}
		ksort($this->weeklylogs);
	
		return $this->weeklylogs;
	}
	private function _GetWeeklyStorypoints($task)
	{

		if($task->isparent == 0)
		{
			//$acc = 0;
			//echo $task->key." ".$task->closedon."<br>";
			$dte = new \DateTime($task->closedon);
			$user = $task->assignee;
			$worklog =  new \StdClass();
			$y = $dte->format("Y");
			$w = $dte->format("W");
			$w = (int)$w;
			{
				$m = $dte->format("m");
				if($m == 12)
					$w = 53;
			}
			$worklog->jira = $task->key;
			$worklog->summary = $task->summary;
			$worklog->hours = $task->timespent * 8;
			$this->weeklylogs[$y][$w][$task->closedon][] = $worklog;
		}
		else foreach($task->children as $stask)
			$this->_GetWeeklyStorypoints($stask);
	}
	public function GetWeeklyStorypoints($task)
	{
		$this->_GetWeeklyStorypoints($task);
		foreach($this->weeklylogs as $year=>$d)
		{
			foreach($this->weeklylogs[$year] as $week=>$w)
			{
				ksort($this->weeklylogs[$year][$week]);
			}
			ksort($this->weeklylogs[$year]);
		}
		ksort($this->weeklylogs);
		return $this->weeklylogs;
	}
	function GetTimeLog()
	{
		$data = [];
		//dd( $this->tree->oa);
		//dd($this->presources);

		foreach($this->presources as $presource)
		{
				if($presource->active)
				{
					$resource = $presource->resource()->get();
					//$cal = $resource->calendar();
					$resource = $resource[0];
					$cal = CalendarController::GetcalenarData($resource->name);
					//	if($cal == null)
					//	dd($resource->name);
					$obj = new \StdClass();
					$obj->name = $resource->displayname;
					$obj->oaid = $presource->oaid;
					$obj->jira = null;
					$obj->oa = null;
					if($this->tree->oa != null)
					{
						if(array_key_exists($obj->oaid, $this->tree->oa->worklogs))
						{
				  			$obj->oa = $this->tree->oa->worklogs[$obj->oaid];
						}
					}
					$data[$resource->name] = $obj;
					$caldata = json_decode($cal->data);
					foreach($caldata as $holiday)
					{
						$d = new \StdClass();
						$d->approved = true;

						$d->enddate = $holiday->endDate;
						$earlier = new \DateTime($holiday->startDate);
						$later = new \DateTime($holiday->endDate);
						$diff = $later->diff($earlier)->format("%a");

						$d->decimal_hours = ($diff+1)*8;
						$data[$resource->name]->cal[$holiday->startDate] = $d;
					}
					/////////////////////// Country Calendar ////////////////
					$cc =  $presource->cc;
					if(strlen($cc) > 1)
					{
						$ccal = CalendarController::GetcalenarData($cc);
						if($ccal != null)
						{
							$ccaldata = json_decode($ccal->data);
							foreach($ccaldata as $holiday)
							{
								$d = new \StdClass();
								$d->approved = true;

								$d->enddate = $holiday->endDate;
								$earlier = new \DateTime($holiday->startDate);
								$later = new \DateTime($holiday->endDate);
								$diff = $later->diff($earlier)->format("%a");
								$d->decimal_hours = ($diff+1)*8;
								$data[$resource->name]->ccal[$holiday->startDate] = $d;

							}
						}
					}
					//$data[$resource->name]->cal = $cal->data;
				}
		}
		foreach($this->tasks as $task)
		{
			if(isset($task->worklogs))
			{
				foreach($task->worklogs as $date=>$worklogdata)
				{
					foreach($worklogdata as $resource=>$worklog)
					{
						/*if($resource == 'amhamza')
						{
								echo $resource." ".$date." ".$worklog->hours."\r\n";
								if(isset($data[$resource]->jira[$date]->decimal_hours))
								{
										echo ($data[$resource]->jira[$date]->decimal_hours);
											echo "\r\n";
								}
						}*/
						if(isset($data[$resource]->jira[$date]->decimal_hours))
						{
								$data[$resource]->jira[$date]->decimal_hours += $worklog->hours;
						}
						else
						{
							$d = new \StdClass();
							$d->approved = true;
							$d->decimal_hours = $worklog->hours;
							$data[$resource]->jira[$date] = $d;
						}
					}
				}
			}
		}
		foreach($data as $user=>$userdata)
		{
				if(($userdata->oa == null)&&($userdata->jira == null))
					unset($data[$user]);

		}
		return $data;
	}
	function GetBurnUpData($task)
	{
		$this->weeklylogs = array();
		//dd($task->_tstart." ".$task->_tend);
		$range = Utility::DateRange($task->_tstart,$task->_tend);
	
		$cv = 0;
		if(($range->totaldays - $range->remaingdays)>0)
			$cv = round($task->timespent/($range->totaldays - $range->remaingdays),1);

		$rv = 0;
		$remainingwork = $task->estimate - $task->timespent;
		if($range->remaingdays > 0)
			$rv = round(($task->estimate-$task->timespent)/$range->remaingdays,1);
		else
			$rv = $remainingwork;

		//$logs = $this->GetWeeklyWorkLog($task);
		if($this->project->estimation == 0)// story points
			$logs = $this->GetWeeklyStorypoints($task);
		else
			$logs = $this->GetWeeklyWorkLog($task);

		$processedworklogs = [];
		$acchours  = 0.0;
		
		$previousworkdate = null;
		$lastdate  = '';
		//dd($logs);
		foreach($logs as $year=>$weeklydata)
		{
			foreach($weeklydata as $weekno=>$worklogs)
			{
				foreach($worklogs as $date=>$workloglist)
				{
					//echo $date."<br>";
					//if($lastdate > $date)
					//	dd($worklogs);
					$lastdate = $date;
					foreach($workloglist as $worklog)
					{
						if(array_key_exists($date,$processedworklogs))
						{
							$processedworklogs[$date]->hours +=  $worklog->hours;
							$processedworklogs[$date]->acchours = $acchours + $worklog->hours;
							$acchours = $processedworklogs[$date]->acchours;
						}
						else
						{
							$processedworklogs[$date] = new \StdClass();
							$processedworklogs[$date]->hours =  $worklog->hours;
							$processedworklogs[$date]->acchours = $acchours + $worklog->hours;
							$acchours = $processedworklogs[$date]->acchours;
						}
					}
					if(($date < $task->_tstart)&&($date > $previousworkdate))
					{
						$previousworkdate = $date;
					}
				}	
			}
		}
		
		//dd($previousworkdate);
		//dd($range);
		ksort($processedworklogs);
		//dd($processedworklogs);
		$accdays = $acchours/8;
		$unloggedwork = $task->timespent - $accdays;
		//echo $task->estimate."<br>";
		$remainingwork = $task->estimate - $task->timespent;
		
		if($previousworkdate == null)
			$previouswork = 0;
		else
			$previouswork = ($processedworklogs[$previousworkdate]->acchours/8);
		
		//echo $task->estimate - $previouswork;
		if($range->totaldays == 0)
			$deltaofwork = 0;
		else
			$deltaofwork = ($task->estimate - $previouswork) /$range->totaldays;

		//echo "Project Duration ".$task->_tstart." - ".$task->_tend."<br>";
		$range->start = $task->_tstart;
		$range->end = $task->_tend;
		//echo "Project estimation ".$task->estimate."<br>";
		$range->totalestimate = $task->estimate;
		//echo "Project Total working days = ".$range->totaldays."<br>";
		//echo "Project Remaining working days = ".$range->remaingdays."<br>";
		//echo "Logged  work = ".$accdays." days <br>";
		//echo "Computed  work = ".$task->timespent." days <br>";
		$range->summary = $task->_summary;
		$range->timespent = $task->timespent;
		$range->status = $task->status;
		//echo "Unlogged work = ".$unloggedwork." days <br>";
		//echo "Remanining work = ".$remainingwork." days <br>";
		$range->remainingwork = $task->remainingwork;
		//echo "Previous work date from start =".$previousworkdate."<br>";
		//echo "Previous workdone =".$previouswork." days<br>";
		$range->previouswork = $task->previouswork;
		//echo "Delta = ".$deltaofwork."<br>";
		//echo "Current Velocity  = ".$cv."<br>";
		$range->cv=round($cv,1);
		//echo "Required Velocity  = ".$rv."<br>";
		$range->progress = $task->progress;
		$range->duedate = $task->_duedate;
		if( $task->status == 'RESOLVED')
			$range->finishingon = '';
		else
			$range->finishingon = $task->_sched_end;
		$range->rv=round($rv,1);
		$lastwork = $previouswork;
		$lastev = $previouswork;
		/*echo "Previous work = ".$previouswork;
		echo "Previous date = ".$previousworkdate;
		echo "Delta = ".$deltaofwork;*/
		foreach($range->data as $date=>$daydata)
		{
			$daydata->tv = $lastwork;
			$daydata->ev = $lastev;
			if($daydata->holiday == 0)
			{
				$daydata->tv = $lastwork + $deltaofwork;
				$lastwork = $daydata->tv;
				

				if(array_key_exists($date,$processedworklogs))
				{
					$lastev = $daydata->ev = $processedworklogs[$date]->acchours/8;
				}
				else
					$daydata->ev = $lastev;
				
			}
			$daydata->ftv = $daydata->tv; 
			if( $date > Utility::GetToday('Y-m-d'))
			{
				$daydata->ev =  null;
				$daydata->ftv = $daydata->tv; 
				$daydata->tv = null;
			}
			else if( $date == Utility::GetToday('Y-m-d'))
				$daydata->ftv = $daydata->tv; 
			else
			{
				$daydata->ftv = null;
			}
			
			
		}

		if(floor($lastwork) > $task->estimate)
		{
			dd("Not possible ".floor($lastwork)." > ".$task->estimate." ".__file__." ".__line__);
		}
			
		//dd($range);
		//dd($processedworklogs);
		/*dd($range);*/

		return $range;

		//dd($range);
		
		//dd($this->project);
		//foreach($task->children as $child)
		//	dd($task);

	}
	
	function GetMilestones($task,$firstcall=1)
	{
		if($firstcall)
			$this->milestones = array();
		if(($task->isconfigured )&&($task->ismilestone))
		{
			$this->milestones[] = $task;
		}
		foreach($task->children as $child)
		{
			$this->GetMilestones($child,0);
		}
		return $this->milestones;
	}
	function GetTask($key)
	{
		if(array_key_exists($key,$this->tasks))
            return $this->tasks[$key];
		return null;
	}
	
	function GetRiskAndIssues($task,$firstcall=1)
	{
		if($firstcall)
		{
			$this->risks = array();
			$this->issues = array();
			$this->blockers = array();
		}
		if(($task->risk_severity != 'None')&&($task->status != 'RESOLVED'))
		{
			if($task->oissuetype == 'Issue')
				$this->issues[$task->risk_severity][$task->key] = $task->risk_severity;
			else if($task->oissuetype == 'Risk')
				$this->risks[$task->risk_severity][$task->key] = $task->risk_severity;
		}
		if(($task->priority == 1)&&($task->status != 'RESOLVED'))
			$this->blockers[$task->key] =$task->key;
			
		foreach($task->children as $child)
		{
			$this->GetRiskAndIssues($child,0);
		}
		$data['risks'] =  $this->risks;
		$data['issues'] =  $this->issues;
		$data['blockers'] =  $this->blockers;
		
		return $data;
	}
	
}
