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

function UpdateIssueOnUi(i)
{
    count = 0;
	issues = [];
	
	// Issue ////
    if(data[i]['risksissues']['issues']['Critical'] !==  undefined)
    {
        UpdateIssueOnUi('Critical');
    }else
    if(data[i]['risksissues']['issues']['High'] !==  undefined)
    {
        UpdateIssueOnUi('High');
    }else
    if(data[i]['risksissues']['issues']['Medium'] !==  undefined)
    {
        UpdateIssueOnUi('Medium');
    }
	
    for (var key in data[i]['risksissues']['issues'][severity]) 
    {
        issues[count] = key;
        count++
    }
    console.log(issues);
    if(issues.length > 0)
    {
        str = issues.toString();
        if(issues.length == 1)
            $('#issuecount'+i).text(issues.length+" Issue");
        else
            $('#issuecount'+i).text(issues.length+" Issues");

        if(severity == 'Critical')
        {
            $( "#issuecount"+i).addClass( "badge-danger" );
            $('#issuecount'+i).prop('title', 'Critical  - '+str);
        }
        else if(severity == 'High')
        {
            $( "#issuecount"+i).addClass( "badge-warning" );
            $('#issuecount'+i).prop('title', 'High severity - '+str);
        }
        else if(severity == 'Medium')
        {
            $( "#issuecount"+i).addClass( "badge-info" );
            $('#issuecount'+i).prop('title', 'Medium severity - '+str);
        }
    }
}
function UpdateBlockersOnUi(i)
{
    count = 0;
	blockers = [];
	
		
	if(data[i]['risksissues']['blockers'] ===  undefined)
		return;
	
    for (var key in data[i]['risksissues']['blockers']) 
    {
        blockers[count] = key;
        count++
    }
    if(blockers.length > 0)
    {
        str = blockers.toString();
        if(blockers.length == 1)
            $('#blockercount'+i).text(blockers.length+" Blocker");
        else
            $('#blockercount'+i).text(blockers.length+" Blockers");
        $("#blockercount"+i).addClass( "badge-danger" );
        $('#blockercount'+i).prop('title', 'Blocker  - '+str);
    }
}

function UpdateEscalationsOnUi(i)
{
    count = 0;
	escalations = [];
	
	if(data[0]['risksissues']['escalations'] ===  undefined)
		return;
	
    for (var key in data[i]['risksissues']['escalations']) 
    {
        escalations[count] = key;
        count++
    }
    if(escalations.length > 0)
    {
        str = escalations.toString();
        if(escalations.length == 1)
            $('#escalationscount'+i).text(escalations.length+" Escalation");
        else
            $('#escalationscount'+i).text(escalations.length+" Escalations");
        $("#escalationscount"+i).addClass( "badge-danger" );
        $('#escalationscount'+i).prop('title', 'Escalations  - '+str);
    }
}
function UpdateRiskOnUi(i)
{
	
    count = 0;
	risks = [];
	tag = '#riskcount'+i.toString();;
	
	if(data[i]['risksissues']['risks']['Critical'] !==  undefined)
    {
        severity = 'Critical';
    }
	else if(data[i]['risksissues']['risks']['High'] !==  undefined)
    {
        severity = 'High';
    }else if(data[i]['risksissues']['risks']['Medium'] !==  undefined)
    {
        severity = 'Medium';
    }
	
    for (var key in data[i]['risksissues']['risks'][severity]) 
    {
        risks[count] = key;
        count++
    }
	
    if(risks.length > 0)
    {
        str = risks.toString();
        $(tag).prop('title', str);

        if(risks.length == 1)
             $(tag).text(risks.length+" Risk");
        else
            $(tag).text(risks.length+" Risks");

        if(severity == 'Critical')
        {
            $(tag).addClass( "badge-danger" );
            $(tag).prop('title', 'Critical  - '+str);
        }
        else if(severity == 'High')
        {
            $(tag).addClass( "badge-warning" );
            $(tag).prop('title', 'High Severity  - '+str);
        }
        else if(severity == 'Medium')
        {
            $(tag).addClass( "badge-info" );
            $(tag).prop('title', 'Medium Severity  - '+str);
        }
    }
	
}

$(function() 
{
	console.log(data);
	for(i=0;i<data.length;i++)
	{
		
		var summary = '<span>'+data[i]['summary']+'<span>'+
		'<br><span id="riskcount'+i+'" class="d-flex float-right badge"></span>'+
		'<span class="d-flex float-right" >&nbsp</span>'+
        '<span id="issuecount'+i+'" class="d-flex float-right badge"></span>'+
		'<span class="d-flex float-right" >&nbsp</span>'+
        '<span id="blockercount'+i+'" class="d-flex float-right badge"></span>'+
		'<span class="d-flex float-right" >&nbsp</span>'+
        '<span id="escalationscount'+i+'" class="d-flex float-right badge"></span>';
       
		$('#desc'+i).html(summary);
		UpdateRiskOnUi(i);
		UpdateIssueOnUi(i);
		UpdateBlockersOnUi(i);
		UpdateEscalationsOnUi(i);
		
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
	$( "#riskcount" ).addClass( "badge-info" );
	
});
@endsection