<?php
namespace App;
use App\Utility;
use App\Jira;
use App\Resource;
use App\ProjectTree;
use App\Calendar;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CalendarController;

class Tj
{
	private $resources =[ ];
	private $planpath;
	private $datapath;
	private $projecttree;
	public function __construct(ProjectTree $projecttree)
	{
		//dd($projecttree);
		Utility::ConsoleLog(time(),'Generating Project Plan');
		
		$header = $this->FlushProjectHeader($projecttree->project);
		$header .= $this->FlushResourceHeader($projecttree->presources);
		$header .= $this->FlushTask($projecttree->tree);
		$header .= $this->FlushReportHeader();
		
		$this->datapath = $projecttree->datapath."/tjp";
		if(!file_exists($this->datapath))
    		mkdir($this->datapath, 0, true);
		
		$this->planpath = $this->datapath."/plan.tjp";
		//dd($header);
		$this->projecttree = $projecttree;
		file_put_contents($this->planpath,$header);
		//echo $projecttree->datapath;
		//dd($header);
	}
	/*function FlushLeavesHeader($head)
	{
		$header = "";
		$calendar = $head->Holidays;
		foreach($calendar as $holiday)
			$header = $header.'leaves holiday "holiday "'.$holiday."\n";
		return $header;
	}*/
	function DependsHeader($task)
	{
		$header = "";
		if(count($task->dependson) > 0)
		{
			
			$del = "";
			$count = count(explode(".",$task->extid));
			$pre = "";;
			while($count--)
				$pre = $pre."!";
			
			foreach($task->dependson as $skey)
			{
				//depends !!!t1.t1a1.t1a1a1,!!!t1.t1a2.t1a2a1 
				//echo $stask->ExtId." ";
				$stask = $task->parent->tasks[$skey];
				$post = "";
				$codes = explode(".",$stask->extid);
				$lastcode = "";
				for($i=0;$i<count($codes);$i++)
				{
					if($i == 0)
					{
						$lastcode = "t".$codes[$i];
						$post = $lastcode;
					}
					else
					{
						$lastcode = $lastcode."a".$codes[$i];
						$post  =  $post.".".$lastcode;
					}
				}
				$header = $header.$del.$pre.$post;
				$del=",";
				//echo $stask->ExtId." ";
				//echo "[".$pre.$post."]";
				//echo EOL;
			}
			return $header;
		}
		else
			return null;
		//echo $header.EOL;
	}
	function FlushTask($task)
	{	
		$tname = trim($task->extid)." ".substr($task->summary,0,10);
		$pos  = strpos($task->summary,'$');// Task name with $ sign causes schedular error
		if($pos != FALSE)
			$taskname = str_replace("$","-",$task->summary);
		else
			$taskname = $task->summary;
		
		$pos  = strpos($taskname,';');// Task name with $ sign causes schedular error
		if($pos != FALSE)
			$taskname = str_replace(";","-",$taskname);
	
		$pos  = strpos($taskname,'(');// Task name with $ sign causes schedular error
		if($pos != FALSE)
			$taskname = str_replace("(","-",$taskname);
		
		$pos  = strpos($taskname,'\\');// Task name with $ sign causes schedular error
		if($pos != FALSE)
			$taskname = str_replace("\\","-",$taskname);
		
		$taskname = trim($task->extid)." ".substr($taskname,0,15);
		$header = "";
		$spaces = "";
		for($i=0;$i<$task->level-1;$i++)
			$spaces = $spaces."     ";
		$tag = str_replace(".", "a", $task->extid);
		$header = $header.$spaces.'task t'.$tag.' "'.$taskname.'" {'."\n";
		
		if($task->isparent == 0)
			$header = $header.$spaces."   complete ".round($task->progress,0)."\n";
		
		$dheader = $this->DependsHeader($task);
		
		if($dheader != null)
			$header = $header.$spaces."   depends ".$dheader."\n";
		
		if($task->_startconstraint!=null)
		{
			if(strtotime($task->_startconstraint) > strtotime(Utility::GetToday("Y-m-d")))
				$header = $header.$spaces."   start ".$task->_startconstraint."\n";
			
		}
		if($task->isparent == 0)
		{
			$header = $header.$spaces.'   Jira "'.$task->key.'"'."\n";
			
			$header = $header.$spaces.'   priority '.$task->schedule_priority."\n";
			
			$remffort  = $task->estimate - $task->timespent;
			
			if(isset($task->isexcluded)||($task->duplicate==1))
			{
				$remffort = 0;
			}
			if( ($remffort > 0)&&($task->status != 'RESOLVED'))
			{
				$header = $header.$spaces."   effort ".$remffort."d"."\n";
				
				
				$presource = $this->resources[$task->assignee];
				
				$team = $presource->team;
				
				if($team != null)
				{
					$team =  explode(",",$team);
					if(count($team)>1)
					{
						$header = $header.$spaces."   allocate ".$team[0]." { alternative ";
						$delim = "";
						$str = "";
						$team =  explode(",",$team);
					
						for($i=1;$i<count($team);$i++)
						{
							$str = $str.$delim.$team[$i];
							$delim = ",";
						}
						$header = $header.$str." select order persistent }"."\n";
					}
					else
						$header = $header.$spaces."   allocate ".$team[0]."\n";
				}
				else
					$header = $header.$spaces."   allocate ".$task->assignee."\n";

			}
		}
		foreach($task->children as $stask)
			$header = $header.$this->FlushTask($stask);
		
		$header = $header.$spaces.'}'."\n";
		return $header;
		//dd($header);
		//dd($taskname);
	}
	function FlushResourceHeader($presources)
	{
		$header =  "macro allocate_developers ["."\n";
		
		foreach($presources as $presource)
		{
			$presource->name = $presource->resource()->first()->name;
			
			$presource->calendar =  $presource->resource()->first()->calendar()->first()->data;
			$header = $header."   allocate ".$presource->name."\n";
			$this->resources[$presource->name] = $presource;
		}

		$header = $header."]"."\n";
		$header = $header.'resource all "Developers" {'."\n";
		
		foreach($presources as $presource)
		{
			$calendar = $presource->calendar;
			$calendar = json_decode($presource->calendar);
			$ccaldata = [];
			if(strlen($presource->cc) < 2)
				$presource->cc = 'na';
			else
			{
				$cc =  $presource->cc;
				$ccal = CalendarController::GetcalenarData($cc);
				$ccaldata = json_decode($ccal->data);
			}
			$header = $header.'    resource '.$presource->name.' "'.$presource->name.'_'.$presource->cc.'" {'."\n";
			
			foreach($calendar as $obj)
			{
				$days = Utility::DateDiffInDays($obj->startDate,$obj->endDate)+1;
				$header = $header.'       leaves annual '.$obj->startDate." +".$days."d\n"; 
			}
			foreach($ccaldata as $obj)
			{
				$days = Utility::DateDiffInDays($obj->startDate,$obj->endDate)+1;
				$header = $header.'       leaves annual '.$obj->startDate." +".$days."d\n"; 
			}

			$presource->efficiency = round($presource->efficiency/100,1);
			$header = $header.'       efficiency '.$presource->efficiency."\n"; 			
			$header = $header.'    }'."\n";
		}
		$header = $header.'}'."\n";
		return $header;
	}
	function FlushProjectHeader(Project $project)
	{
		$today = Utility::GetToday("Y-m-d");
		$start = $project->start;
		$end  =  $project->end;
		if($end == null) // No end defined so schedule from start or from today
		{
			if(strtotime($start) < strtotime($today))
				$header =  'project acs "'.$project->name.'" '.$today;
			else
				$header =  'project acs "'.$project->name.'" '.$start;
		}
		else
		{
			if(strtotime($start) > strtotime($today))
			{
				$header =  'project acs "'.$project->name.'" '.$start;
			}
			else
			{
				if(strtotime($end) > strtotime($today))
					$header =  'project acs "'.$project->name.'" '.$today;
				else
					$header =  'project acs "'.$project->name.'" '.$end;
			}
		}
		$header = $header." +48m"."\n";
		$header = $header.'{ '."\n";
		$header = $header.'   timezone "Asia/Karachi"'."\n";
		$header = $header.'   timeformat "%Y-%m-%d"'."\n";
		$header = $header.'   numberformat "-" "" "," "." 1 '."\n";
		$header = $header.'   currencyformat "(" ")" "," "." 0 '."\n";
		$header = $header.'   now 2017-07-21-01:00'."\n";
		$header = $header.'   currency "USD"'."\n";
		$header = $header.'   scenario plan "Plan" {}'."\n";
		$header = $header.'   extend task { text Jira "Jira"}'."\n";
		$header = $header.'} '."\n";
		return $header;
	}
	function FlushReportHeader()
	{
		
		$header =
		# Now the project has been specified completely. Stopping here would
		# result in a valid TaskJuggler file that could be processed and
		# scheduled. But no reports would be generated to visualize the
		# results.
		
		
		
		# A traditional Gantt chart with a project overview.
		
		"
		
		taskreport monthreporthtml \"monthreporthtml\" {
			formats html
			columns bsi, name, start, end, effort,resources, complete,Jira, monthly
			# For this report we like to have the abbreviated weekday in front
			# of the date. %a is the tag for this.
			timeformat \"%a %Y-%m-%d\"
			loadunit hours
		    hideresource @all
		}
		
		taskreport monthreport \"monthreport\" {
			formats csv
			columns bsi { title \"ExtId\" },name, start { title \"Start\" }, end { title \"End\" }, resources { title \"Resource\" }, monthly
			# For this report we like to have the abbreviated weekday in front
			# of the date. %a is the tag for this.
			timeformat \"%Y-%m-%d\"
			loadunit hours
			hideresource @all
		}
		
		taskreport weekreporthtml \"weekreporthtml\" {
			formats html
			columns bsi, name, start, end, effort,resources, complete,Jira, weekly
			# For this report we like to have the abbreviated weekday in front
			# of the date. %a is the tag for this.
			timeformat \"%Y-%m-%d\"
			loadunit hours
			hideresource @all
		}
		
		taskreport weekreport \"weekreport\" {
			formats csv
			columns bsi { title \"ExtId\" },name, start { title \"Start\" }, end { title \"End\" }, resources { title \"Resource\" }, weekly
			# For this report we like to have the abbreviated weekday in front
			# of the date. %a is the tag for this.
			timeformat \"%Y-%m-%d\"
			loadunit hours
			hideresource @all
		}
		
	
		
		resourcereport resourcegraphhtm \"resourcehtml\" {
		   formats html
		   headline \"Resource Allocation Graph\"
		   columns no, name, effort, weekly 
		   #loadunit shortauto
	       # We only like to show leaf tasks for leaf resources.
		   hidetask ~(isleaf() & isleaf_())
		   sorttasks plan.start.up
		}
		
		resourcereport resourcegraph \"resource\" {
		   formats csv
		   headline \"Resource Allocation Graph\"
		   columns name, effort, weekly 
		   #loadunit shortauto
	       # We only like to show leaf tasks for leaf resources.
		   hidetask 1
		   #hidetask ~(isleaf() & isleaf_())
		   #sorttasks plan.start.up
		}
		
		
		
		
		";
		return $header;
	}
	function ReadOutputCsv()
	{
		$data = new \stdClass();
		$header = array();
		$colcount = 0;
		$handle = FALSE;
		
		$file = $this->datapath."/monthreport.csv";
		$handle = fopen($file, "r");
		$type = 'month';
		$data->headers = new \stdClass();
		$data->tasks = array();
		if($handle !== FALSE) 		
		{
			$i=0;
			while (($indata = fgetcsv($handle, 1000, ";")) !== FALSE) 
			{
				$num = count($indata);
				if($i==0)
				{
					$colcount = count($indata);
					for ($c=0; $c < $num; $c++) 
					{
						$header[] = $indata[$c];
					}
					//var_dump($header);
					$data->headers->$type = array_slice($header,5);
					$i++;
					continue;
				}
				if($colcount != $num)
				{
					LogMessage(ERROR,'TJ',"col count not same");
					//echo "col count not same";
				}
				$obj= new \stdClass();
				$dates = array();

				for ($j=0; $j < $num; $j++) 
				{
					$value = $indata[$j];
					$hf = $header[$j];
					if($header[$j] == 'Resource')
					{
						$resource = explode("(",$value);
						if( count($resource) > 1)
						{
							$res = explode(")",$resource[1]);
							$value = $res[0];
						}
						else
							$value = $resource[0];
						$obj->$hf=$value;
					}
					else if($header[$j] == 'Start')
						$obj->$hf=$value;
					else if($header[$j] == 'End')
						$obj->$hf=$value;
					else if($header[$j] == 'ExtId')
					{
						$obj->$hf=$value;
						
					}
					else if($header[$j] == 'Name')
					{
						//echo $value."<br>";
						$value = explode(' ',trim($value))[0];
						//$obj->$header[$j]=$value;
						//echo $value."<br>";
						if($obj->ExtId!==$value)
						{
							//echo "----------->".$obj->ExtId," ".$value."<br>";
							$obj->ExtId = $value;
						}
						$data->tasks[$obj->ExtId] = $obj;
					}
					else
					{
						$dates[] = $value;
					}
				}
				$obj->$type = [];
				for($i=0; $i< count($data->headers->$type); $i++)
					$obj->$type[$data->headers->$type[$i]] = $dates[$i];
				//$obj->$type = $dates;
				$i++;
			}
			fclose($handle);
		}
		return $data->tasks;
		
	}
	function Execute()
	{
		//." 2>&1"
		Utility::ConsoleLog(time(),'Wait::Generating Schedule ...');
		$cmd = "tj3 -o ".$this->datapath."  ".$this->planpath." 2>&1";
		exec($cmd,$result);
		$pos1 = strpos($result[0], 'Error');
		if ($pos1 != false)
		{
			Utility::ConsoleLog(time(),'Error::'.$result[0]);
			exit();
		}
		Utility::ConsoleLog(time(),'Schedule Created Successfully');
		$scheduled_data =  $this->ReadOutputCsv();
		//dd($scheduled_data);
		foreach($this->projecttree->tasks as $task)
		{
			$extid = $task->extid;
			
			if(!array_key_exists($extid,$scheduled_data))
			{
				Utility::ConsoleLog(time(),'Error::'.$extid.' does not exit');	
				//dd($scheduled_data);
			}
			
			$task->sched_start = $scheduled_data[$extid]->Start;
			$task->sched_end = $scheduled_data[$extid]->End;
			$task->sched_assignee = $scheduled_data[$extid]->Resource;
			$task->sched_estimatates_month = $scheduled_data[$extid]->month;
		}
		//dd($this->projecttree->tasks);
		//$this->projecttree->Save();
		
	}
}