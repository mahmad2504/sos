@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/table.css') }}" />

@endsection
@section('style')
 
@endsection
@section('content')
<div class="center" style="background-color:AliceBlue ;">
        <h4 class="d-flex;" id="summary" style="margin-bottom:-17px;"></h4>
        <span id="riskcount" class="d-flex float-right badge"></span>
        <span class="d-flex float-right" >&nbsp&nbsp</span>
        <span id="issuecount" class="d-flex float-right badge"></span>
        <span class="d-flex float-right" >&nbsp&nbsp</span>
        <span id="blockercount" class="d-flex float-right badge"></span>
        
    	<table class="zui-table">
        <thead>
            <tr>
                <th>Start Date</th>
                <th>Baseline End</th>
                <th>Current Deadline</th>
                <th>Forecast Finish</th> 
                <th>Progress</th>
                <th>Status</th>
            </tr>
        </thead>`
        <tbody>
            <tr>
                <td id="tstart"></td>
                <td id="bend"></td>
                <td id="tend"></td>
                <td id="end"></td>
            
                <td id="progress"></td>
                <td id="status"></td>
            </tr>
           
        </tbody>
    </table>
    <table style="margin-top:-17px;"class="zui-table">
        <thead>
            <tr>
                <th>Baseline EAC</th>
                <th>Current EAC</th>
                <th id="headerearned"></th>
                <th>Remaining</th> 
            </tr>
        </thead>`
        <tbody>
            <tr>
                <td id='bestimate'></td>
                <td id='estimate'></td>
                <td id='consumed'></td>
                <td id='remaining'></td>
            </tr>
           
        </tbody>
    </table>
   
	
</div>
<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('js/radialIndicator.min.js') }}" ></script>

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
function UpdateRiskOnUi(severity)
{
    count = 0;
    for (var key in data['risksissues']['risks'][severity]) 
    {
        risks[count] = key;
        count++
    }

    if(risks.length > 0)
    {
        str = risks.toString();
        $('#riskcount').prop('title', str);

        if(risks.length == 1)
             $('#riskcount').text(risks.length+" Risk");
        else
            $('#riskcount').text(risks.length+" Risks");

        if(severity == 'Critical')
        {
            $( "#riskcount" ).addClass( "badge-danger" );
            $('#riskcount').prop('title', 'Critical  - '+str);
        }
        else if(severity == 'High')
        {
            $("#riskcount").addClass( "badge-warning" );
            $('#riskcount').prop('title', 'High Severity  - '+str);
        }
        else if(severity == 'Medium')
        {
            $( "#riskcount" ).addClass( "badge-info" );
            $('#riskcount').prop('title', 'Medium Severity  - '+str);
        }
    }
}
function UpdateIssueOnUi(severity)
{
    count = 0;
    for (var key in data['risksissues']['issues'][severity]) 
    {
        issues[count] = key;
        count++
    }
    console.log(issues);
    if(issues.length > 0)
    {
        str = issues.toString();
        if(issues.length == 1)
            $('#issuecount').text(issues.length+" Issue");
        else
            $('#issuecount').text(issues.length+" Issues");

        if(severity == 'Critical')
        {
            $( "#issuecount" ).addClass( "badge-danger" );
            $('#issuecount').prop('title', 'Critical  - '+str);
        }
        else if(severity == 'High')
        {
            $( "#issuecount" ).addClass( "badge-warning" );
            $('#issuecount').prop('title', 'High severity - '+str);
        }
        else if(severity == 'Medium')
        {
            $( "#issuecount" ).addClass( "badge-info" );
            $('#issuecount').prop('title', 'Medium severity - '+str);
        }
    }
}
function UpdateBlockersOnUi(severity)
{
    count = 0;
    for (var key in data['risksissues']['blockers']) 
    {
        blockers[count] = key;
        count++
    }
    if(blockers.length > 0)
    {
        str = blockers.toString();
        if(blockers.length == 1)
            $('#blockercount').text(blockers.length+" Blocker");
        else
            $('#blockercount').text(blockers.length+" Blockers");
        $("#blockercount").addClass( "badge-danger" );
        $('#blockercount').prop('title', 'Blocker  - '+str);
    }
}
$(function() 
{
    if(data['summary'] === undefined)
        return;
    $('#summary').text('Status '+data['summary']);

    var weekdate = ConvertDateFormat(data['tstart']);
    $('#tstart').html(ConvertDateToString(data['tstart'])+"<br><small class='grey-text'>"+weekdate+"</small>");

    var weekdate = ConvertDateFormat(data['bend']);
    $('#bend').html(ConvertDateToString(data['bend'])+"<br><small class='grey-text'>"+weekdate+"</small>");
    $('#bend').html(ConvertDateToString(data['bend'])+"<br><small class='grey-text'>"+weekdate+"</small>");

    var weekdate = ConvertDateFormat(data['tend']);
    $('#tend').html(ConvertDateToString(data['tend'])+"<br><small class='grey-text'>"+weekdate+"</small>");

    var weekdate = ConvertDateFormat(data['end']);
    $('#end').html(ConvertDateToString(data['end'])+"<br><small class='grey-text'>"+weekdate+"</small>");

    estimate = Math.round(data['estimate']);
    bestimate = Math.round(data['bestimate']);
    consumed = Math.round(data['consumed']);
    remaining = estimate - consumed;

    if(project.estimation == 0)
    {
        $('#headerearned').text('Earned StoryPoints');
        
        if(estimate > 0)
            $('#estimate').text(estimate+" Points" );
        
        if(bestimate > 0)
            $('#bestimate').text(bestimate+" Points" );
        
        if(consumed > 0)
            $('#consumed').text(consumed+" Points" );

        if(remaining >0)
            $('#remaining').text(remaining+" Points" );
    }
    else
    {
        $('#headerearned').text('Time Spent');
        if(estimate > 0)
            $('#estimate').text(estimate+" Days" ); 

        if(bestimate > 0)
            $('#bestimate').text(bestimate+" Days" ); 
        
        if(consumed > 0)
            $('#consumed').text(consumed+" Days" );

        if(remaining > 0)
            $('#remaining').text(remaining+" Days" );
    }
    //$('#progress').text(data['progress']+" %" ); 
    $('#status').html("<img width='80px' src='/images/"+data['status']+".png'></img>"); 

    console.log(data['risksissues']['risks']);
    risks = [];
    issues = [];
    blockers = [];
    // Risks ////
    if(data['risksissues']['risks']['Critical'] !==  undefined)
    {
        UpdateRiskOnUi('Critical');
    }else
    if(data['risksissues']['risks']['High'] !==  undefined)
    {
        UpdateRiskOnUi('High');
    }else
    if(data['risksissues']['risks']['Medium'] !==  undefined)
    {
        UpdateRiskOnUi('Medium');
    }

    // Issue ////
    if(data['risksissues']['issues']['Critical'] !==  undefined)
    {
        UpdateIssueOnUi('Critical');
    }else
    if(data['risksissues']['issues']['High'] !==  undefined)
    {
        UpdateIssueOnUi('High');
    }else
    if(data['risksissues']['issues']['Medium'] !==  undefined)
    {
        UpdateIssueOnUi('Medium');
    }
    // Blockers
    if(data['risksissues']['blockers'] !==  undefined)
    {
        UpdateBlockersOnUi(blockers,'Blocker');
    }
    $('#progress').radialIndicator({
        barColor: '#2E8B57',
        radius:15,
        barWidth: 6,
        initValue: Math.round(data['progress']),
        roundCorner : true,
        percentage: true
    });

});
@endsection