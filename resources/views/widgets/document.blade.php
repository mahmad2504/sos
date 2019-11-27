@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/BsMultiSelect.min.css') }}" />
<link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
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
function SpitTaskData($url,$task,$level,$firstcall=0)
{
	$i=1;
	if(!$firstcall)
	{
		$thisurl  = $url.$task->key;
		$badge = 'badge-secondary';
		if($task->status == 'RESOLVED')
			$badge = 'badge-success';
		$header_tag = 'h5';
		
		$child_html = '';
		$count  = 0;
		foreach($task->children as $childtask)
		{
			if($childtask->issuetype != 'REQUIREMENT')
				continue;
			$localurl = '#'.$childtask->linkid;
			$b = 'badge-secondary';
			if($childtask->status == 'RESOLVED')
				$b = 'badge-success';
			$child_html .= "<span style='font-size:15px;'><a title='Child Task' href='".$localurl."' class='badge-pill badge ".$b."'>".$childtask->linkid."</span></a>&nbsp";
			$count++;
			if($count%15==0)
				$child_html .= "<br>";
		}
		
		//if(($task->type != 'TASK')&&($task->type != 'DEFECT'))
		//	if($task->isparent == 0)
		//		dd($task);
		if(count($task->fixVersions)==0)
			$verstr = "<small'>No FixVersion</small>";
		else
			$verstr = "<small>".implode(",",$task->fixVersions)."</small>";
		echo 
			"<".$header_tag.
			" id='".$level."'>".$level."   ".
			$task->summary.
			"<small title='Jira Task Status' style='margin-left:5px;float:right;font-size:15px;'><a href='".$thisurl."' class='badge ".$badge."'>".$task->status."</small>".''."</a>".
			"<small title='Jira Link' style='margin-left:5px; float:right;font-size:15px;'><a href='".$thisurl."' class='badge ".$badge."'>".$task->key."</small>".''."</a>".
		    "<small title='Jira Link' style='float:right;font-size:15px;'><a href='".$thisurl."' class='badge ".$badge."'>".$verstr."</small>".''."</a>".
			"</h5>";
		if(strlen(trim($task->description))==0)
			echo 'No Description'."<br>";
		else
			echo strip_tags($task->description)."<br>";
		echo $child_html."";
		//echo $child_html;
		echo '<br><br><br>';
	}
	if($task->show_children)
	{
		foreach($task->children as $child)
		{
			if($child->issuetype != 'REQUIREMENT')
				continue;
				
			$next_level = $level.".".$i++;
			SpitTaskData($url,$child,$next_level);
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
			$badge = 'badge-secondary';
			$thisurl  = $url.$child->key;
			if($child->status == 'RESOLVED')
				$badge = 'badge-success';
		
			if($child->issuetype != 'REQUIREMENT')
				continue;
			 echo '<tr>';
			 echo '<td style="font-size:15px;">'.$child->summary.'</td>';
			 $key = "<small title='Jira Task' style='margin-left:5px;font-size:13px;'><a href='".$thisurl."' class=' ".$badge."'>".$child->key."</a>".''."</small>";
			
			 echo '<td style="">'.$key.'</td>';
			$status = "<small title='Jira Task Status' style='margin-left:5px;font-size:15px;'><a href='".$thisurl."' class='badge ".$badge."'>".$child->status."</a>".''."</small>";
			
			 echo '<td style="">'.$status.'</td>';
			 echo '</tr>';
		}
		echo '</table><br><br>';
	}
}
function SpitSummaryTaskData($task,$level=0,$firstcall=0,$count)
{
	$label  = 1;
	$i=1;
	if(!$firstcall)
	{
		for($j=1;$j<$task->level;$j++)
			echo '<ul>';
		$color = '';
		if($task->isparent == 0)
			$color = '#2A3439';
		
		//if(($task->issuetype != 'TASK')&&($task->issuetype != 'DEFECT'))
		//	if($task->isparent == 0)
		//		$color = 'Red';
		//$color = 'Red';
		if(count($task->fixVersions)==0)
			$verstr = "No Fixversion";
		else
			$verstr = implode(",",$task->fixVersions);
		echo '<li><a style="color:'.$color.'" href="#'.$level.'">'.
			$level.'         -'.$task->_summary."<small style='position: absolute;right: 300px;'>".$verstr."</small>".'<span style="float:right">'." ".$task->ostatus.'</span>'.
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
			if($child->issuetype != 'REQUIREMENT')
				continue;
				
			$next_level = $level.".".$i++;
			SpitSummaryTaskData($child,$next_level,0,$count++);
		}
	}
}
?>
<div style="width:80%; margin-left: auto; margin-right: auto" class="center">
    <a href="{{route('dashboard',[$user->name,$project->name])}}" style="float:left;margin-top:5px;margin-right:10px;"  rel="tooltip" title="Project Dashboard" class="float-right">Dashboard</a>
	
	 <div id="filter" class="float-leftghabra" style="visibility4: hidden !important;float:left;width:600px;">
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

	<input type="text" id="justAnInputBox" placeholder="Select"/>
	
			
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
					SpitSummaryTaskData($task,1,1,0);
				?>
				</li>
			</ul>
	</div>
	<div id="toc_container">
	<h2 id="product_requirement">1 Product Requirement - Details</h2>
	<?php
		SpitTaskData($url,$task,1,1);
		
	?>
	</div>
</div>
<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('js/BsMultiSelect.min.js') }}" ></script>
<script src="{{ asset('js/icontains.js') }}" ></script>
<script src="{{ asset('js/comboTreePlugin.js') }}" ></script>

@endsection
@section('script')
var user = @json($user);
var project =  @json($project);
var isloggedin = {{$isloggedin}};

var SampleJSONData = [
    {
        id: 0,
        title: 'choice 1  '
    }, 
	{
        id: 1,
        title: 'choice 2',
    }, {
        id: 2,
        title: 'choice 3'
    }, {
        id: 3,
        title: 'choice 4'
    }
];

if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
}
$(function() {
	$("select").bsMultiSelect();
	$("#filter").css('visibility', 'visible');
	$( "#versionselect" ).change(function(event) {
		 var brands = $('#versionselect option:selected');
		 var selected = [];
         $(brands).each(function(index, brand){
            selected.push([$(this).val()]);
         });
         console.log(selected);
	});
	$("#versionselect").focusout(function(){
		$(this).css("background-color", "#FFFFFF");
		console.log("ddd");
	});
	comboTree1 = $('#justAnInputBox').comboTree({
			source : SampleJSONData,
			isMultiple: true,
			cascadeSelect: false
		});
		

});

@endsection
