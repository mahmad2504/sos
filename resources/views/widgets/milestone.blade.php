@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/table.css') }}" />
@endsection
@section('style')

@endsection
@section('content')

<div style="width:80%; margin-left: auto; margin-right: auto" class="center">
	<h3>{{ $project->name}}</h3>
	<div class="mainpanel">
	<div style="background-color:#F0F0F0">
		<h4 id="summary" style="margin-bottom:-17px;">Milestones</h4>
	</div>
	<table class="zui-table">
		<thead>
			<tr>
				<th style="text-align: left;">Description</th>
				<th>Baseline End</th>
				<th>Current Deadline</th>
				<th>Forecast Finish</th> 
				<th>Baseline EAC</th>
				<th>Current EAC</th>
				<th>Remaining</th>
				<th>Progress</th>
				<th>Status</th>
			</tr>
		</thead>`
	<tbody>
		@for($i=0;$i<count($data);$i++)
		<tr>
			<td style="text-align: left;font-weight:bold" id="desc{{$i}}"></td>
			<td id="bend{{$i}}"></td>
			<td id="tend{{$i}}"></td>
			<td id="end{{$i}}"></td>
			<td id="bestimate{{$i}}"></td>
			<td id="estimate{{$i}}"></td>
			<td id="remaining{{$i}}"></td>
			<td id="progress{{$i}}"></td>
			<td id="status{{$i}}"></td>
		</tr>
		@endfor		
	</tbody>
    </table>
	</div>
</div>

<script src="{{ asset('js/msc-script.js') }}" ></script>
@endsection
@section('script')
var user = @json($user);
var project =  @json($project);
var isloggedin = {{$isloggedin}};
var data = @json($data);
var key = '{{$key}}';

'use strict';
if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
}

$(function() 
{
	console.log(data);
	for(i=0;i<data.length;i++)
	{
		var summary = data[i]['summary'];
		
		$('#desc'+i).text(summary);

		var weekdate = ConvertDateFormat(data[i]['bend']);
		$('#bend'+i).html(ConvertDateToString(data[i]['bend'])+"<br><small class='grey-text'>"+weekdate+"</small>");

		var weekdate = ConvertDateFormat(data[i]['tend']);
		$('#tend'+i).html(ConvertDateToString(data[i]['tend'])+"<br><small class='grey-text'>"+weekdate+"</small>");

		var weekdate = ConvertDateFormat(data[i]['end']);
		if(data[i]['status'] != 'DELIVERED')
			$('#end'+i).html(ConvertDateToString(data[i]['end'])+"<br><small class='grey-text'>"+weekdate+"</small>");

		if(project.estimation == 0)
			units = 'Points';
		else
			units = 'Days of work';
		if(data[i]['estimate'] > 0)
			$('#estimate'+i).html(Round(data[i]['estimate'])+"<br><small class='grey-text'>"+units+"</small>" );
		if(data[i]['bestimate'] > 0)
			$('#bestimate'+i).html(Round(data[i]['bestimate'])+"<br><small class='grey-text'>"+units+"</small>");
		if(data[i]['remaining'] > 0)
			$('#remaining'+i).html(Round(data[i]['remaining'])+"<br><small class='grey-text'>"+units+"</small>");

		$('#progress'+i).text(Round(data[i]['progress'])+" %" );
		$('#status'+i).html("<img width='80px' src='/images/"+data[i]['status']+".png'></img>"); 
	}
   
	var weekdate = ConvertDateFormat(data['tstart']);
});
@endsection