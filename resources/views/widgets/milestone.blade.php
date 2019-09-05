@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/table.css') }}" />
@endsection
@section('style')

@endsection
@section('content')
<div class="center" style="background-color:AliceBlue ;">
	<h4 id="summary" style="margin-bottom:-17px;">Milestones</h4>
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
			<td style="text-align: left;" id="desc{{$i}}"></td>
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
	for(i=0;i<data.length;i++)
	{
		var summary = data[i]['summary'];
		console.log(summary);
		$('#desc'+i).text(summary);

		var weekdate = ConvertDateFormat(data[i]['bend']);
		$('#bend'+i).html(ConvertDateToString(data[i]['bend'])+"<br><small class='grey-text'>"+weekdate+"</small>");

		var weekdate = ConvertDateFormat(data[i]['tend']);
		$('#tend'+i).html(ConvertDateToString(data[i]['tend'])+"<br><small class='grey-text'>"+weekdate+"</small>");

		var weekdate = ConvertDateFormat(data[i]['end']);
		$('#end'+i).html(ConvertDateToString(data[i]['end'])+"<br><small class='grey-text'>"+weekdate+"</small>");

		if(project.estimation == 0)
		{
			//$('#headerearned').text('Earned StoryPoints');
			$('#estimate'+i).text(data[i]['estimate']+" Points" );
			$('#bestimate'+i).text(data[i]['bestimate']+" Points" );
			$('#remaining'+i).text(data[i]['remaining']+" Points" );
		}
		else
		{
			//$('#headerearned').text('Time Spent');
			$('#estimate'+i).text(data[i]['estimate']+" Days" ); 
			$('#bestimate'+i).text(data[i]['bestimate']+" Days" ); 
			$('#remaining'+i).text(data[i]['remaining']+" Days" );
		}
		$('#progress'+i).text(data[i]['progress']+" %" );
		$('#status'+i).html("<img width='80px' src='/images/"+data[i]['status']+".png'></img>"); 
	}
   
	var weekdate = ConvertDateFormat(data['tstart']);
});
@endsection