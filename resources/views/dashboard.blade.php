@extends('layouts.app')
@section('csslinks')

@endsection
@section('style')
img {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 5px;
  width: 150px;
  box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.5);
}
img:hover {
  box-shadow: 0 0 2px 1px rgba(0, 140, 186, 0.5);
}

figure {
    display: inline-block;
    margin: 20px; /* adjust as needed */
}
figure img {
    vertical-align: top;
}
figure figcaption {
    text-align: center;
}
@endsection
@section('content')
<div  class="container">
	<h1>Dashboard</h1>
	
	<p id='description'>&nbsp</p>
	<hr>
	<div class="row">
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showtreeview',[$user->name,$project->name])}}"><img src="/images/treeview.gif"></img></a>
				<figcaption styleclass="caption">Tree View</figcaption>
			</figure>
		</div>
		<div class="col-3">
			<figure class="item">
				<a href="{{route('showgantt',[$user->name,$project->name])}}"><img src="/images/gantt.png"></img></a>
				<figcaption styleclass="caption">Gantt Chart</figcaption>
			</figure>
		</div>
		<div class="col-3">
			
		</div>
		<div class="col-3">
			
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
	console.log(response.description);
	$('#description').append(response.description);
}
$(document).ready(function()
{
	if(username != null)
		$('.navbar').removeClass('d-none');
	LoadProjectsData("{{route('getproject',['id'=>$project->id])}}",null,OnProjectDataReceived,function(response){});
	
})
@endsection

