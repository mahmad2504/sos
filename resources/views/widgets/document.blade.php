@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
@endsection
@section('style')

#toc_container {
    background: #f9f9f9 none repeat scroll 0 0;
    border: 1px solid #aaa;
    display: table;
    font-size: 95%;
    margin-bottom: 1em;
    padding: 20px;
	width : 100%;
}

.toc_title {
    font-weight: 700;
    text-align: center;
}

#toc_container li, #toc_container ul, #toc_container ul li{
    list-style: outside none none !important;
}

th {
  height: 25px;
  font-weight: bold;
  text-align: left;
  background-color: #cccccc;
}
@endsection
@section('content')
<?php

$url = $project->jiraurl."/browse/";
function GetStatusBadge($task)
{
	if($task->ostatus == 'Requested')
		$status = '<span class="badge badge-warning">'.$task->ostatus.'</span>';
	
	else if($task->ostatus == 'Committed')
		$status = '<span class="badge badge-info">'.$task->ostatus.'</span>';
	
	else if($task->ostatus == 'Draft')
		$status = '<span class="badge badge-light">'.$task->ostatus.'</span>';
	else
		$status = '<span class="badge badge-success">'.$task->ostatus.'</span>';
	return $status;
	
}
function SpitTaskData($url,$task,$level,$firstcall=0,$fixversion)
{
	$i=1;
	if(IfShow($task,$fixversion)==0)
		return;
	
	if(!$firstcall)
	{
		$thisurl  = $url.$task->key;
		$badge = 'badge-secondary';
		if($task->status == 'RESOLVED')
			$badge = 'badge-success';
		$lev = $task->level;
		if($task->level > 6)
			$lev = 6;
		
		$header_tag = 'h'.$lev;
		$end_header_tag ='</h'.$lev.'>';
		
		$child_html = '';
		$count  = 0;
		foreach($task->children as $childtask)
		{
			//if($childtask->issuetype != 'REQUIREMENT')
			//	continue;
		
			$localurl = '#'.$childtask->linkid;
			$b = 'badge-secondary';
			if($childtask->status == 'RESOLVED')
				$b = 'badge-success';
			$child_html .= "<span style='font-size:15px;'><a title='Child Task' href='".$localurl."' class='badge-pill badge ".$b."'>".$childtask->linkid."</span></a>&nbsp";
			$count++;
			if($count%15==0)
				$child_html .= "<br>";
		}
		$status = GetStatusBadge($task);
		
		if(count($task->fixVersions)==0)
			$verstr = "<small'>No FixVersion</small>";
		else
			$verstr = "<small>".implode(",",$task->fixVersions)."</small>";
		
		if($task->isparent == 1)
		{
			$verstr = 'Title';
			$status = '';
		}
		
		echo 
			"<".$header_tag.
			" id='".$level."'>".$level."   ".
			$task->summary.
			//"<small title='Jira Task Status' style='margin-left:5px;float:right;font-size:15px;'><a href='".$thisurl."' class='badge ".$badge."'>".$status."</small>".''."</a>".
			"<small title='Jira Link' style='margin-left:5px; float:right;font-size:15px;'><a href='".$thisurl."' class='badge "."'>".$task->key."</small>".''."</a>".
		    "<small title='Jira Link' style='float:right;font-size:15px;'><a href='".$thisurl."' class='badge "."'>".$verstr."</small>".''."</a>".
			$end_header_tag;
		
		if(strlen(trim($task->description))==0)
			echo 'No Description<br>';
		else
			echo strip_tags($task->description)."<br>";
		echo "<small title='Jira Task Status' style='margin-left:5px;float:right;font-size:15px;'><a href='".$thisurl."' class='badge ".$badge."'>".$status."</small>".''."</a>";
			
		echo $child_html."";
		//echo $child_html;
		echo '<br><br><br>';
	}
	if($task->show_children)
	{
		foreach($task->children as $child)
		{
			//if($child->issuetype != 'REQUIREMENT')
			//	continue;
				
			$next_level = $level.".".$i++;
			SpitTaskData($url,$child,$next_level,0,$fixversion);
		}
	}
	else
	{
		echo '<table border="2" style="margin-left:200px;width:60%">';
		echo '<col width="60%"><col width="20%"><col width="20%">';
		echo '<tr><th>Feature</th>
		<th>Jira</th>
		<th>Status</th>
		</tr>';
		foreach($task->children as $child)
		{
			
			
			if(IfShow($child,$fixversion)==0)
				continue;
			
			$badge = 'badge-secondary';
			$thisurl  = $url.$child->key;
			if($child->status == 'RESOLVED')
				$badge = 'badge-success';
			$badge = '';
			//if($child->issuetype != 'REQUIREMENT')
			//	continue;
			if(count($child->fixVersions)==0)
				$verstr = "No Fixversion";
			else
				$verstr = implode(",",$child->fixVersions);
		
			 echo '<tr>';
			 echo '<td style="font-size:15px;">'.$child->summary.'</td>';
			 $key = "<small title='Jira Task' style='margin-left:5px;font-size:13px;'><a href='".$thisurl."' class=' ".$badge."'><small>".$child->key."</small></a>".''."</small>";
			
			 echo '<td style="">'.$key.'</td>';
			$status = "<small title='Jira Task Status' style='margin-left:5px;font-size:15px;'><a href='".$thisurl."' class='badge ".$badge."'>".$child->status."</a>".''."</small>";
			
			$status = GetStatusBadge($child);

		
			 echo '<td style="">'.$status.'<br><small></small></td>';
			
			 echo '</tr>';
		}
		echo '</table><br><br>';
	}
}
function IfShow($task,$fixversion=null)
{
	if($fixversion == null)
		return 1;
	if($task->isparent == 1)
	{
		if(!in_array ($fixversion,$task->allfixVersions))
			return 0;
	}
	else
	{
		if(!in_array ($fixversion,$task->fixVersions))
			return 0;
	}
	return 1;
}
function SpitSummaryTaskData($url,$task,$level=0,$firstcall=0,$count,$fixversion)
{
	$label  = 1;
	
	$i=1;
	if(IfShow($task,$fixversion)==0)
		return;
	
	
	if(!$firstcall)
	{
		for($j=1;$j<$task->level;$j++)
			echo '<ul>';
		$color = '';
		if($task->isparent == 0)
			$color = '#2A3439';
		$task->label = " ";
		 foreach($task->labels as $label)
		{
			if($label == 'format-as-table')
			{
				$task->show_children = 0;
				$task->label = 'Table';
				$color = '#2A3439';
				break;
			}
		}
		
		//if(($task->issuetype != 'TASK')&&($task->issuetype != 'DEFECT'))
		//	if($task->isparent == 0)
		//		$color = 'Red';
		//$color = 'Red';
		
		if(count($task->fixVersions)==0)
			$verstr = "No Fixversion";
		else
			$verstr = implode(",",$task->fixVersions);
		
		if($task->isparent == 1)
		{
			if($task->label == 'Table')
			{
				if($fixversion != null)
					$verstr  = $fixversion;
			}
			else
				$verstr  = '';
		}
		
		
		if(count($task->allfixVersions)==0)
			$allverstr = "No Fixversion";
		else
			$allverstr = implode(",",$task->allfixVersions);
		$allverstr ='';
		$ostatus = GetStatusBadge($task);
		if($task->isparent == 1)
		{
			$ostatus = '';
			if($task->label == 'Table')
				$ostatus = GetStatusBadge($task);
		}
		$jira_link = '<a href="'.$url.$task->key.'">'.$task->key.'</a>';
		$jira_link = $task->key;
		echo '<li><a style="color:'.$color.'" href="#'.$level.'">'.
			$level.'         -'.$task->_summary.'<small style="font-size:8px;position: absolute;right:400px;margin-top:7px">'.$jira_link.'</small><small style="position: absolute;right:350px;margin-top:7px">'.$task->label."</small><small style='position: absolute;right: 250px;margin-top:5px'>".$verstr."</small>".'<span style="float:right">'." <small>".$ostatus.'</small></span>'.
			'</a></li>';
		for($j=1;$j<$task->level;$j++)
			echo '</ul>';
		
		$task->linkid = $level;
		
	}
	else
		$task->parent->fixversions = [];
	
	$task->show_children = 1;
    foreach($task->labels as $label)
	{
		if($label == 'format-as-table')
		{
			$task->show_children = 0;
			break;
		}
	}
	foreach($task->fixVersions as $version)
	{
		$task->parent->fixversions[$version]=$version;
		
	}
	
	if($task->show_children)
	{
		foreach($task->children as $child)
		{
			//if($child->issuetype != 'REQUIREMENT')
			//	continue;
				
			$next_level = $level.".".$i++;
			SpitSummaryTaskData($url,$child,$next_level,0,$count++,$fixversion);
		}
	}
}
?>
<div style="width:80%; margin-left: auto; margin-right: auto" class="center">
    <a href="{{route('dashboard',[$user->name,$project->name])}}" style="float:left;margin-top:5px;margin-right:10px;"  rel="tooltip" title="Project Dashboard" class="float-right">Dashboard</a>
	<select id="filter" style="display: none">
		<?php
		$found = 0;
		foreach($task->allfixVersions as $fv)
		{
			if($fv == $fixversion)
			{
				$found = 1;
				echo '<option value="'.$fv.'" selected>'.$fv.'</option>';
			}
			else
				echo '<option value="'.$fv.'" >'.$fv.'</option>';
		}
		if($found == 0 )
		{
			echo '<option value="'.'all'.'" selected>'.'all'.'</option>';
		}
		else
			echo '<option value="'.'all'.'">'.'all'.'</option>';
		?>
	</select>
	
	<!-- <div id="filter" class="float-leftghabra" style="visibility4: hidden !important;float:left;width:600px;">
		<span class="float-left"> Filter</span>
		<select class="float-left"  name="states" id="versionselect" class="form-control"  multiple="multiple" style="width:300px!important">
		  <option value="AL">Alabama</option>
		  <option value="AK">Alaska</option>
		  <option value="AZ">Arizona</option>
		  <option value="AR">Arkansas</option>
		  <option selectedvalue="CA">California</option>
		</select>
		<span class="float-right"> Apply</span>
	</div> 

	<input type="text" id="justAnInputBox" placeholder="Select"/> -->
	
			
	<div id="toc_container">
		<H1 class="toc_title">Product Requirements</H1>
			<H3 class="toc_title"></H3><br><br><br><br>
			<ul class="toc_list">
				<!-- <li><a href="#schedule_summary">1 Schedule summary</a>
				<ul>
					<li><a href="#First_Sub_Point_1">1.1 First Sub Point 1</a></li>
					<li><a href="#First_Sub_Point_2">1.2 First Sub Point 2</a></li>
				</ul>
				</li>-->
				<li><a href="#product_requirement">1 Product Requirement</a>
				<?php
					SpitSummaryTaskData($url,$task,1,1,0,$fixversion);
				?>
				</li>
			</ul>
	</div>
	<div id="toc_container">
	<h2 id="product_requirement">1 Product Requirement - Details</h2>
	<?php
		SpitTaskData($url,$task,1,1,$fixversion);
		
	?>
	</div>
</div>
<script src="{{ asset('js/msc-script.js') }}" ></script>

@endsection
@section('script')
var user = @json($user);
var project =  @json($project);
var isloggedin = {{$isloggedin}};

if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
	
}

$(function() {
	$('#filter').show();
	$( "#filter" ).change(function() 
	{
		fixversion = $(this).children("option:selected").val();
			window.location.href  = '{{route('showdocument',[$user->name,$project->id])}}'+'?fixversion='+fixversion;
		
	});
});

@endsection
