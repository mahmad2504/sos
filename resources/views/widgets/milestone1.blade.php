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

<?php

$date = new \DateTime($projectdata['tstart']);
$tstart = $date->format('d F Y');

$date = new \DateTime($projectdata['tend']);
$tend = $date->format('d F Y');

$date = new \DateTime($projectdata['end']);
$end = $date->format('d F Y');



$bend = '';

if(strlen(trim($projectdata['bend']))>0)
{	
	$date = new \DateTime($projectdata['bend']);
	$bend = 'Baseline-'.$date->format('d F Y');
}

$bestimate = '';
if(strlen(trim($projectdata['bestimate']))>0)
{
	$bestimate = 'Baseline-'.round($projectdata['bestimate']);
}

$now = time(); // or your date as well
$end_date = strtotime($projectdata['tend']);

$datediff =  $end_date-$now;
$days_remaining = 0;
if($datediff > 0)
	$days_remaining = round($datediff / (60 * 60 * 24));


?>
<div style="width:80%; margin-left: auto; margin-right: auto" class="center">
	<!-- <h4 class="center" id="summary" style="width:60%;margin-bottom:-17px;">Projects Status</h4> -->
	<div>
		<h3 style="margin-bottom:-5px;">{{$projectdata['summary']}}
			<span style="float:right;font-size:10px;" class="badge badge-info">{{$tend}}</span>
			<span style="float:right;font-size:10px;">-</span>
			<span style="float:right;font-size:10px;" class="badge badge-info">{{$tstart}}</span>
			
		</h3>
		<span style="float:right;font-size:10px;" class="">{{$days_remaining}} Days remaning</span><br>
		
		<table  class="thistable">
			<thead>
				<tr>
					<th width="15%" class="">Estimated<br><small>Effort<small></th>
					<th width="15%" class="">Finish<br><small>Date<small></th>
					<th width="15%" class="">Progress<br><small>Percentage<small></th> 
					<th width="15%" class="">Remaining<br><small>Effort<small></th></th>
					<th width="13%" class="">Risk Levels</th> 
					<th width="13%" class="">Issue Levels</th> 
					<th width="13%" class="">Escalations</th>
					<th width="15%" class="">Status</th> 
				</tr>
			</thead>`
			<tbody>
				<tr>
					<td>{{round($projectdata['estimate']) }} Man Days<br><small>{{$bestimate }}</small></td>
					<td>{{$tend }} <br><small>{{$bend}}</small></td>
					<td>{{$projectdata['progress']}}%</td>
					<td>{{$projectdata['remaining'] }}<br><small>Man Days</small></td>
					
					<td id="risks0"></td>
					<td id="issues0"></td>
					<td id="escalations0"></td>
					<td><img width='100px' src='/images/{{$projectdata['status']}}.png'></img></td>
				</tr>
			</tbody>
		</table>
	</div>
	<span style="margin-right:10px;float:right;font-size:10px;" class="">Expected to finish on {{$end}}</span> 
	<div class="mainpanel">
	<table  class="thistable">
		<thead>
			<tr>
				<th width="29%" class="col1" style="text-align: left;">Milestone</th>
				<th width="15%" class="">Estimated<br><small>Effort<small></th>
				<th width="15%" class="">Finish<br><small>Date<small></th> 
				<th width="15%" class="">Progress<br><small>Percentage<small></th> 
				<th width="15%" class="">Remaining<br><small>Effort<small></th></th>
				<th width="15%" class="">Remaining<br><small>Days<small></th></th>
				<th width="10%" class="">Status</th>
			</tr>
		</thead>`
	<tbody>
		@for($i=0;$i<count($data);$i++)
		<tr>
			<td style="text-align: left;font-weight:bold" id="desc{{$i}}"></td>
			<td id="estimate{{$i}}"></td>
			<td id="finish{{$i}}"></td>
			<td id="progress{{$i}}"></td>
			<td id="remaining{{$i}}"></td>
			<td id="remainingdays{{$i}}"></td>
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
var projectdata = @json($projectdata);
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
	console.log(projectdata);
	FillCell('risks',projectdata.risksissues.risks,0);
	FillCell('issues',projectdata.risksissues.issues,0);
	FillEscalationsCell('escalations',projectdata.risksissues.escalations,0);
	for(i=0;i<data.length;i++)
	{
		$('#desc'+i).html(data[i]['summary']);
		
		bestimate = '';
		
		if(data[i]['bestimate'] >0)
		{
			bestimate = 'Baseline-'+Math.round(data[i]['bestimate']);
		}
		
		html = Math.round(data[i]['estimate'])+' Man Days<br><small>'+bestimate+'</small>';
		$('#estimate'+i).html(html);
		
		var tend = new Date(data[i]['tend']); 
		
		bend = '';
		if(data[i]['bend'].trim().length >0)
		{
			bend = new Date(data[i]['bend']); 
			bend = 'Baseline-'+bend.toString().substring(3, 15);
		}	
		html = tend.toString().substring(3, 15)+'<br><small>'+bend+'</small>';
		$('#finish'+i).html(html);
		
		html = data[i]['progress']+'%';
		$('#progress'+i).html(html);
		
		html = Math.round(data[i]['remaining'])+'<br><small>Man Days</small>';
		$('#remaining'+i).html(html);
		
		
		$('#remainingdays'+i).html(data[i]['days_remaining']);
		
		html = "<img width='80px' src='/images/"+data[i]['status']+".png'></img>";
		$('#status'+i).html(html);
	}
	
	$('.tp').tooltipster({ interactive: true, contentAsHTML: true});
	
});
@endsection