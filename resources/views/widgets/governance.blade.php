@extends('layouts.app')
@section('csslinks')

@endsection
@section('style')
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
@endsection
@section('content')
<div style="width:80%; margin-left: auto; margin-right: auto" class="center">
    <h2>Jira Governance Report - {{$fixversion}}</h2>
	<hr>
	<h3 style="display:none" class="table_sprints" >Project Sprints</h3>
	
	<table style="display:none" class="table_sprints" id="table_sprints">
		<tr>
			<th>ID</th>
			<th>Board</th>
			<th>Name</th>
			<th>Start</th>
			<th>Stop</th>
			<th>State</th>
			<th>Comments</th>
		<tr>
	</table>
	<hr>
	<h3 style="display:none" class="table_pcr" >PCR</h3>
	<table style="display:none" class="table_pcr" id="table_pcr">
		<tr>
			<th>Key</th>
			<th>Summary</th>
			<th>Type</th>
			<th>Status</th>
			<th>Assignee</th>
			<th>Created</th>
			<th>Comments</th>
		<tr>
	</table>
	<hr>
	<h3 style="display:none" class="table_risk" >RISK</h3>
	<table style="display:none" class="table_risk" id="table_risk">
		<tr>
			<th>Key</th>
			<th>Summary</th>
			<th>Type</th>
			<th>Priority</th>
			<th>Status</th>
			<th>Assignee</th>
			<th>Reporter</th>
			<th>Created</th>
			<th>Due</th>
		<tr>
	</table>
	<hr>

	<h3 style="display:none" class="table_no_estimates" >Missing Estimates</h3>
	<table style="display:none" class="table_no_estimates" id="table_no_estimates">
		<tr>
			<th>Key</th>
			<th>Summary</th>
			<th>Type</th>
			<th>Status</th>
			<th>Assignee</th>
			<th>Reporter</th>
			<th>Created</th>
		<tr>
	</table>
		
		
		
	<hr>
	<h3 style="display:none" class="table_out_of_sprints" >Missing sprint</h3>
	<table style="display:none" class="table_out_of_sprints" id="table_out_of_sprints">
		<tr>
			<th>Key</th>
			<th>Summary</th>
			<th>Type</th>
			<th>Status</th>
			<th>Estimate</th>
			<th>Assignee</th>
			<th>Reporter</th>
			<th>Created</th>
		<tr>
	</table>
	<hr>
	<h3 style="display:none" class="table_no_fixversion" >Missing Fixversion</h3>
	<table style="display:none" class="table_no_fixversion" id="table_no_fixversion">
		<tr>
			<th>Key</th>
			<th>Summary</th>
			<th>Type</th>
			<th>Status</th>
			<th>Assignee</th>
			<th>Reporter</th>
			<th>Created</th>
		<tr>
	</table>
</div>
@endsection

@section('script')
var user = @json($user);
var project =  @json($project);
var data = @json($data);
var sprints =data.sprints;
var jiraurl = "{{$jiraurl}}";
var out_of_sprint_tasks = data.out_of_sprint_tasks;
var no_fixversion_tasks =  data.no_fixversion_tasks;
var pcr =  data.pcr;
var risk = data.risks;
var unestimated = data.unestimated;
'use strict';
console.log(data);
function CreateSprintsRow(sprint)
{
	var row = $('<tr>');
	
	var col = $('<td>');
	col.html(sprint.no);
	col.html('<a href="'+jiraurl+'/secure/RapidBoard.jspa?rapidView='+sprint.board+'&sprint='+sprint.no+'">'+sprint.no+'</a>');
	
	col.css('padding-left','5px');
	col.css('padding-right','5px');
	row.append(col);


	var col = $('<td>');
	col.html('<a href="'+jiraurl+'/secure/RapidBoard.jspa?rapidView='+sprint.board+'">'+sprint.board+'</a>');
	col.css('padding-left','5px');
	col.css('padding-right','5px');
	row.append(col);
	
	var col = $('<td>');
	col.html(sprint.name);
	col.css('padding-left','5px');
	col.css('padding-right','5px');
	row.append(col);
	
	var col = $('<td>');
	col.html(sprint.start);
	col.css('padding-left','5px');
	col.css('padding-right','5px');
	row.append(col);
	
	var col = $('<td>');
	col.html(sprint.end);
	col.css('padding-left','5px');
	col.css('padding-right','5px');
	row.append(col);
	
	var col = $('<td>');
	col.html(sprint.state);
	col.css('padding-left','5px');
	col.css('padding-right','5px');
	row.append(col);
	
	var col = $('<td>');
	col.html(sprint.error);
	col.css('padding-left','5px');
	col.css('padding-right','5px');
	row.append(col);
	return row;
}
function ShowSprintsTable()
{
	for(var i in sprints)
	{
		var sprint =  sprints[i];
		if(sprint.state != 'CLOSED')
			continue;
		var row=CreateSprintsRow(sprint);
		if(sprint.error != null)
			row.css('color','red');
		else
			row.css('color','grey');
		
		row.css('font-weight','bold');
		$('#table_sprints').append(row);
		$('.table_sprints').show();
	}
	for(var i in sprints)
	{
		var sprint =  sprints[i];
		if(sprint.state != 'ACTIVE')
			continue;
		var row=CreateSprintsRow(sprint);
		if(sprint.error != null)
			row.css('color','red');
		else
			row.css('color','green');
		
		row.css('font-weight','bold');
		$('#table_sprints').append(row);
		$('.table_sprints').show();
	}
	for(var i in sprints)
	{
		var sprint =  sprints[i];
		if(sprint.state != 'FUTURE')
			continue;
		
		var row=CreateSprintsRow(sprint);
		if(sprint.error != null)
			row.css('color','red');
		row.css('font-weight','bold');
		$('#table_sprints').append(row);
		$('.table_sprints').show();
	}
}
function NoEstimatesTable()
{
	for(var i in unestimated)
	{
		var task =  unestimated[i];
		var row = $('<tr>');
		var col = $('<td>');
		col.html('<a href="'+jiraurl+'/browse/'+task.key+'">'+task.key+'</a>');
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.summary.substr(0,50));
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		col.attr('title',task.summary);
		row.append(col);
		
		var col = $('<td>');
		col.html(task.type);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.status);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.assignee);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.reporter);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		
		const timeDiff  = (new Date()) - (new Date(task.created));
		const days      = Math.round(timeDiff / (1000 * 60 * 60 * 24));
		var col = $('<td>');
		col.html(days+" days old");
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		
		
		row.css('color','red');
		$('#table_no_estimates').append(row);
		$('.table_no_estimates').show();
	}	
}
function OutOfSprintTable()
{
	var totalestimate = 0;
	for(var i in out_of_sprint_tasks)
	{
		var task =  out_of_sprint_tasks[i];
		totalestimate += task.estimate;
		var row = $('<tr>');
		var col = $('<td>');
		col.html('<a href="'+jiraurl+'/browse/'+task.key+'">'+task.key+'</a>');
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.summary.substr(0,50));
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		col.attr('title',task.summary);
		row.append(col);
		
		var col = $('<td>');
		col.html(task.type);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.status);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.estimate);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.assignee);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.reporter);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		const timeDiff  = (new Date()) - (new Date(task.created));
		const days      = Math.round(timeDiff / (1000 * 60 * 60 * 24));
		var col = $('<td>');
		col.html(days+" days old");
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		
		
		row.css('color','red');
		$('#table_out_of_sprints').append(row);
		$('.table_out_of_sprints').show();
	}	
	var row = $('<tr>');
	var col = $('<td>');
	col.html('Total');
	col.css('padding-left','5px');
	col.css('padding-right','5px');
	row.append(col);
	col = $('<td>');
	row.append(col);
	col = $('<td>');
	row.append(col);
	col = $('<td>');
	row.append(col);
	col = $('<td>');
	col.html(Math.round(totalestimate));
	col.css('padding-left','5px');
	col.css('padding-right','5px');
	row.append(col);
	$('#table_out_of_sprints').append(row);
}
function FixversionTable()
{
	for(var i in no_fixversion_tasks)
	{
		var task =  no_fixversion_tasks[i];
		var row = $('<tr>');
		var col = $('<td>');
		col.html('<a href="'+jiraurl+'/browse/'+task.key+'">'+task.key+'</a>');
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.summary.substr(0,50));
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		col.attr('title',task.summary);
		row.append(col);
		
		var col = $('<td>');
		col.html(task.type);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.status);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.assignee);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.reporter);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		const timeDiff  = (new Date()) - (new Date(task.created));
		const days      = Math.round(timeDiff / (1000 * 60 * 60 * 24));
		var col = $('<td>');
		col.html(days+" days old");
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		
		
		row.css('color','red');
		$('#table_no_fixversion').append(row);
		$('.table_no_fixversion').show();
	}
}
function PCRTable()
{
	for(var i in pcr)
	{
		var task =  pcr[i];
		console.log(task);
		var row = $('<tr>');
		var col = $('<td>');
		col.html('<a href="'+jiraurl+'/browse/'+task.key+'">'+task.key+'</a>');
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.summary.substr(0,50));
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		col.attr('title',task.summary);
		row.append(col);
		
		var col = $('<td>');
		col.html(task.type);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.status);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.assignee);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.reporter);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		const timeDiff  = (new Date()) - (new Date(task.created));
		const days      = Math.round(timeDiff / (1000 * 60 * 60 * 24));
		var col = $('<td>');
		col.html(days+" days old");
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html('Move it in Satisfied state');
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		row.css('color','red');
		$('#table_pcr').append(row);
		$('.table_pcr').show();
	}
}
function RISKTable()
{
	for(var i in risk)
	{
		var task =  risk[i];
		var row = $('<tr>');
		var col = $('<td>');
		col.html('<a href="'+jiraurl+'/browse/'+task.key+'">'+task.key+'</a>');
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.summary.substr(0,50));
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		col.attr('title',task.summary);
		row.append(col);
		
		var col = $('<td>');
		col.html(task.type);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.priority);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.status);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.assignee);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		col.html(task.reporter);
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		const timeDiff  = (new Date()) - (new Date(task.created));
		const days      = Math.round(timeDiff / (1000 * 60 * 60 * 24));
		var col = $('<td>');
		col.html(days+" days old");
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		
		var col = $('<td>');
		if(task.duedate != null)
		{
			console.log(task.duedate);
			const timeDiff  = (new Date(task.duedate)) - (new Date());
			const days      = Math.round(timeDiff / (1000 * 60 * 60 * 24));
			if(days < 0)
			{
				col.html(days*-1+" days ago");
				row.css('color','red');
			}
			else
				col.html(days+" days away");
		}
		col.css('padding-left','5px');
		col.css('padding-right','5px');
		row.append(col);
		

		
		$('#table_risk').append(row);
		$('.table_risk').show();
	}
}
$(function() 
{
	ShowSprintsTable();
	OutOfSprintTable();
	FixversionTable();
	PCRTable();
	RISKTable();
	NoEstimatesTable();
});
@endsection