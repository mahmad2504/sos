@extends('layouts.app')
@section('csslinks')

@endsection
@section('style')

.widget {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 5px;
  width: 150px;
  box-shadow: 5px 5px 5px grey;"
}
.widget:hover {
  box-shadow: 0 0 8px 4px rgba(0, 140, 186, 0.5);
}

figure {
    display: inline-block;
    margin: 20px; /* adjust as needed */
}

figure figcaption {
    text-align: center;
	font-weight:bold;
}
.isDisabled {
  cursor: not-allowed;
  opacity: 0.5;
}
.isDisabled > a {
  color: currentColor;
  display: inline-block;  /* For IE11/ MS Edge bug */
  pointer-events: none;
  text-decoration: none;
}
@endsection
@section('content')

<div style="width:80%; margin-left: auto; margin-right: auto" class="center">
	<h3>{{ $project->name}}</h3>
	<div class="mainpanel">
	<div class="paneltitle">
		<a href="#" style="margin-top:5px;margin-right:10px;"  rel="tooltip" title="Not Available" class="isDisabled float-right" disabled>Settings</a>
		<a href="#" style="margin-top:5px;margin-right:10px;"  rel="tooltip" title="Not Available" class="isDisabled float-right">Sync</a>
		<a href="{{route('showtaskproperties',[$project->id])}}" style="margin-top:5px;margin-right:10px;"  rel="tooltip" title="Configure Milestones" class="float-right">Milestones</a>
		<a href="{{route('showprojectresources',[$project->id])}}"style="margin-top:5px;margin-right:10px;"  rel="tooltip" title="Configure Resources" class="float-right">Resources</a>
		<h3>Dashboard</h3>
	</div>
	<hr>
	<div class="row">
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showtreeview',[$user->name,$project->id])}}"><img class="widget" src="/images/treeview.gif"></img></a>
				<figcaption styleclass="caption">Tree View</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showgantt',[$user->name,$project->id])}}"><img class="widget" src="/images/gantt.png"></img></a>
				<figcaption styleclass="caption">Gantt Chart</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showtimechart',[$user->name,$project->id])}}"><img class="widget" src="/images/timechart.jpg"></img></a>
				<figcaption styleclass="caption">Time Chart</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showweeklyreport',[$user->name,$project->id])}}"><img class="widget" src="/images/report.gif"></img></a>
				<figcaption styleclass="caption">Weekly Report</figcaption>
			</figure>
		</div>
	</div>
	<div class="row">
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showwburnupchart',[$user->name,$project->id])}}"><img class="widget" src="/images/burnup.png"></img></a>
				<figcaption styleclass="caption">Burnup</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showwmilestonereport',[$user->name,$project->id])}}"><img class="widget" src="/images/milestone.jpg"></img></a>
				<figcaption styleclass="caption">Milestone</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showwmilestonestatus',[$user->name,$project->id])}}"><img class="widget" src="/images/status.png"></img></a>
				<figcaption styleclass="caption">Summary</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showdocument',[$user->name,$project->id])}}"><img class="widget" src="/images/requirement.jpg"></img></a>
				<figcaption styleclass="caption">Requirement</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showsprints',[$user->name,$project->id])}}"><img class="widget" src="/images/sprint.png"></img></a>
				<figcaption styleclass="caption">Sprint View</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
			
				<a href="/widget/milestone/{{$user->name}}/{{$project->id}}?view=1"><img class="widget" src="/images/allstatus.jpeg"></img></a>
				<figcaption styleclass="caption">Complete Status</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showdefectchart',[$user->name,$project->id])}}"><img class="widget" src="/images/defects.png"></img></a>
				<figcaption styleclass="caption">Defect Closed Vs Resolved</figcaption>
			</figure>
  </div>
  </div>
  
  <div style="margin-top:10px;" class="row">
		<div class="col-3">
			
		</div>
		<div class="col-3">
			
		</div>
		<div class="col-3">
			
		</div>
		<div class="col-3">
  </div>
  </div>
</div>
@endsection
@section('script')
var username = "{{$user->name}}";
var userid = "{{$user->id}}";
var projectid = {{$project->id}};
function LoadProjectsData(url,data,onsuccess,onfail)
{
	$.ajax({
		type:"GET",
		url:url,
		cache: false,
		data:data,
		success: onsuccess,
		error: onfail
	});
}
function OnProjectDataReceived(response)
{
	//console.log(response.description);
	//$('#description').append(response.description);
}
$(document).ready(function()
{
	if(username != null)
		$('.navbar').removeClass('d-none');
	LoadProjectsData("{{route('getproject',['id'=>$project->id])}}",null,OnProjectDataReceived,function(response){});
	
})
@endsection

