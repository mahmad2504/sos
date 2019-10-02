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
		
		echo 
			"<".$header_tag.
			" id='".$level."'>".$level."   ".
			$task->summary.
			"<small title='Jira Task Status' style='margin-left:5px;float:right;font-size:15px;'><a href='".$thisurl."' class='badge ".$badge."'>".$task->status."</small>".''."</a>".
			"<small title='Jira Link' style='float:right;font-size:15px;'><a href='".$thisurl."' class='badge ".$badge."'>".$task->key."</small>".''."</a>".
		    "<br><br>";
		if(strlen(trim($task->description))==0)
			echo 'No Description'."<br>";
		else
			echo $task->description."<br>";
		echo $child_html."<br><br>";
		//echo $child_html;
		echo '<br><br><br>';
	}
	foreach($task->children as $child)
	{
		$next_level = $level.".".$i++;
		SpitTaskData($url,$child,$next_level);
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
		echo '<li><a style="color:'.$color.'" href="#'.$level.'">'.
			$level.'         -'.$task->_summary.''.'<span style="float:right">'.$task->ostatus.'</span>'.
			'</a></li>';
		for($j=1;$j<$task->level;$j++)
			echo '</ul>';
		
		$task->linkid = $level;
		
	}
	foreach($task->children as $child)
	{
		$next_level = $level.".".$i++;
		SpitSummaryTaskData($child,$next_level,0,$count++);
	}
}


?>
			
<div style="width:80%; margin-left: auto; margin-right: auto" class="center">
    <a href="{{route('dashboard',[$user->name,$project->name])}}" style="float:left;margin-top:5px;margin-right:10px;"  rel="tooltip" title="Project Dashboard" class="float-right">Dashboard</a>

	<div id="toc_container">
		<H1 class="toc_title">Product Requirements</H1>
			<H3 class="toc_title">{{$project->description}}</H3><br><br><br><br>
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
       
});

@endsection
