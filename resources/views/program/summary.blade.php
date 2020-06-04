@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/table.css') }}" />
<link rel="stylesheet" href="{{ asset('css/tooltipster.bundle.min.css') }}" />
@endsection
@section('style')
.thistable{
  table-layout: fixed;
  width: 100%;
  white-space: nowrap;
}

tr:nth-child(even) {background: #ddd}
tr:nth-child(odd) {background: #fff}
thead th {
    background-color: #DDEFEF;
    border: solid 2px #cdcdcd;
    font: normal 15px Arial, sans-serif;
    font-weight: bold;
    color: #000;
    //padding: 10px;
    text-align: center;
    text-shadow: 1px 1px 1px #fff;
	height: 30px;
}

td {
    border: solid 2px #cdcdcd;
	font-size:12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
	text-align: center;
}
.col1 {
  width: 40%;
}
.col2 {
  width: 12%;
}
.col3 {
  width: 12%;
}
.col4 {
  width: 12%;
}
.col5 {
  width: 12%;
}
.col6 {
  width: 12%;
}
@endsection
@section('content')


<div style="width:60%; margin-left: auto; margin-right: auto" class="center">
	<!-- <h4 class="center" id="summary" style="width:60%;margin-bottom:-17px;">Projects Status</h4> -->
	<h3>Projects of {{ $user->name}}</h3>
	<div class="mainpanel">
	<table  class="thistable">
		<thead>
			<tr>
				<th width="29%" class="col1" style="text-align: left;">Project Name</th>
				<th width="15%" class="col2">Risk Level</th>
				<th width="15%" class="col3">Issue Level</th> 
				<th width="15%" class="col4">Escalations</th> 
				<th width="13%" class="col5">Progress</th> 
				<th width="10%" class="col6">Status</th>
			</tr>
		</thead>`
	<tbody>
		@for($i=0;$i<count($data);$i++)
		<tr>
			<td style="text-align: left;font-weight:bold" id="desc{{$i}}"></td>
			<td id="risks{{$i}}"></td>
			<td id="issues{{$i}}"></td>
			<td id="escalations{{$i}}"></td>
			<td id="progress{{$i}}"></td>
			<td id="status{{$i}}"></td>
		</tr>
		@endfor		
	</tbody>
	
    </table>
	</div>
</div>

<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('js/tooltipster.bundle.min.js') }}" ></script>
<script src="{{ asset('js/radialIndicator.min.js') }}" ></script>
@endsection
@section('script')
var user = @json($user);
var isloggedin = {{$isloggedin}};
var data = @json($data);


'use strict';
if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	
}
function FillEscalationsCell(type,data,i)
{	count=0;
	title = '';
	//$('#'+type+i).css("padding-left",'20px');
	for(var key in data)
	{
		title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
		count++;
	}
	if(count > 0)
	{
		$('#'+type+i).css("background-color",'#ff0000');
		$('#'+type+i).css("color",'#ffffff');
		badges = '<span  title="'+title+'"  class="tp badge badge-success">'+count+'</span>&nbsp&nbsp';
		$('#'+type+i).html('<span>Yes&nbsp&nbsp'+badges+'</span>');
	}
	else
	{
		$('#'+type+i).css("background-color",'#008000');
		$('#'+type+i).css("color",'#ffffff');
		$('#'+type+i).text('No');
	}
}		
function FillCell(type,data,i)
{
	//$('#'+type+i).css("padding-left",'20px');
	count = 0;
	title = '';
	if(data['Critical'] != undefined)
	{
		$('#'+type+i).css("background-color",'#ff0000');
		$('#'+type+i).css("color",'#ffffff');
		for(var key in risks['Critical'])
		{
			title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
			count++;
		}
		badges = '<span  title="'+title+'"  class="tp badge badge-success">'+count+'</span>&nbsp&nbsp';
		$('#'+type+i).html('<span>Critical&nbsp&nbsp'+badges+'</span>');
	}
	else if(data['High'] != undefined)
	{
		$('#'+type+i).css("background-color",'#FFA500');
		$('#'+type+i).css("color",'#ffffff');
		for(var key in risks['High'])
		{
			title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
			count++;
		}
		badges = '<span  title="'+title+'"  class="tp badge badge-success">'+count+'</span>&nbsp&nbsp';
		$('#'+type+i).html('<span>High&nbsp&nbsp'+badges+'</span>');
	}
	else if(data['Medium'] != undefined)
	{
		$('#'+type+i).css("background-color",'#FFFF00');
		$('#'+type+i).css("color",'#000000');
		for(var key in risks['Medium'])
		{
			title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
			count++;
		}
		badges = '<span  title="'+title+'"  class="tp badge badge-success">'+count+'</span>&nbsp&nbsp';
		$('#'+type+i).html('<span>Medium&nbsp&nbsp'+badges+'</span>');
	}
	else 
	{
		$('#'+type+i).css("background-color",'#008000');
		$('#'+type+i).css("color",'#ffffff');
		$('#'+type+i).text('Low');
	}
	return;
}
$(function() 
{
	console.log(data);
	for(i=0;i<data.length;i++)
	{
		project = data[i];
		blockers = project.risksissues.blockers;
		risks = project.risksissues.risks;
		issues = project.risksissues.issues;
		escalations = project.risksissues.escalations;
		
		status = project.status;
		progress = project.progress;
		
		url = '/dashboard/'+user.name+'/'+project.id;
		console.log(url);
		$('#desc'+i).html('<h6><a href="'+url+'">'+project.summary+'</a></h6>');
	
		FillCell('risks',risks,i);
		FillCell('issues',issues,i);
		FillEscalationsCell('escalations',escalations,i);
		$('#progress'+i).html('<div style="font-size:10px;padding-left:0%;background-color:#00ff00;width:'+Math.round(progress)+'%;">'+Math.round(progress)+'%</div>'); 
		$('#status'+i).html("<img width='80px' src='/images/"+status+".png'></img>"); 
	}
	$('.tp').tooltipster({ interactive: true, contentAsHTML: true});
	
});
@endsection