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
        <span id="risklabel"  class="d-flex float-right" ></span>
        <span id="riskcount" class="d-flex float-right badge"></span>
        <span class="d-flex float-right" >&nbsp&nbsp</span>
        <span id="issuelabel" class="d-flex float-right" ></span>
        <span id="issuecount" class="d-flex float-right badge"></span>

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
function UpdateRiskOnUi(risks, severity)
{
    if(risks.length > 0)
    {
        str = risks.toString();
        $('#riskcount').prop('title', str);
        $('#riskcount').text(risks.length+" Issues");

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
function UpdateIssueOnUi(risks, severity)
{
    if(issues.length > 0)
    {
        str = issues.toString();

        $('#issuecount').text(issues.length+" Risks");

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
$(function() 
{

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


    if(project.estimation == 0)
    {
        $('#headerearned').text('Earned StoryPoints');
        $('#estimate').text(data['estimate']+" Points" );
        $('#bestimate').text(data['bestimate']+" Points" );
        $('#consumed').text(data['consumed']+" Points" );
        $('#remaining').text(data['remaining']+" Points" );
    }
    else
    {
        $('#headerearned').text('Time Spent');
        $('#estimate').text(data['estimate']+" Days" ); 
        $('#bestimate').text(data['bestimate']+" Days" ); 
        $('#consumed').text(data['consumed']+" Days" );
        $('#remaining').text(data['remaining']+" Days" );
    }
    $('#progress').text(data['progress']+" %" ); 
    $('#status').html("<img width='80px' src='/images/"+data['status']+".png'></img>"); 

    console.log(data['risksissues']['risks']);
    risks = [];
    issues = [];
    // Risks ////
    if(data['risksissues']['risks']['Critical'] !==  undefined)
    {
        console.log(data['risksissues']['risks']['Critical'].length, 'Critical', data['risksissues']['risks']);
        count = 0;
        for (var key in data['risksissues']['risks']['Critical']) 
        {
            risks[count] = key;
            count++
        }
        UpdateRiskOnUi(risks,'Critical');
    }else
    if(data['risksissues']['risks']['High'] !==  undefined)
    {
        console.log(data['risksissues']['risks']['High'].length, 'High', data['risksissues']['risks']);
        count = 0;
        for (var key in data['risksissues']['risks']['High']) 
        {
            risks[count] = key;
            count++
        }
        UpdateRiskOnUi(risks,'High');
    }else
    if(data['risksissues']['risks']['Medium'] !==  undefined)
    {
        console.log(data['risksissues']['risks']['Medium'].length, 'High', data['risksissues']['risks']);
        count = 0;
        for (var key in data['risksissues']['risks']['Medium']) 
        {
            risks[count] = key;
            count++
        }
        UpdateRiskOnUi(risks,'Medium');
    }else
    {
        console.log('Low', data['risksissues']['risks']);
    }
    // Issue ////
    if(data['risksissues']['issues']['Critical'] !==  undefined)
    {
        console.log(data['risksissues']['issues']['Critical'].length, 'Critical', data['risksissues']['issues']);
        count = 0;
        for (var key in data['risksissues']['issues']['Critical']) 
        {
            issues[count] = key;
            count++
        }
        UpdateIssueOnUi(issues,'Critical');
    }else
    if(data['risksissues']['issues']['High'] !==  undefined)
    {
        console.log(data['risksissues']['issues']['High'].length, 'High', data['risksissues']['issues']);
        count = 0;
        for (var key in data['risksissues']['issues']['High']) 
        {
            issues[count] = key;
            count++
        }
        UpdateIssueOnUi(issues,'High');
    }else
    if(data['risksissues']['issues']['Medium'] !==  undefined)
    {
        console.log(data['risksissues']['issues']['Medium'].length, 'High', data['risksissues']['issues']);
        count = 0;
        for (var key in data['risksissues']['issues']['Medium']) 
        {
            issues[count] = key;
            count++
        }
        UpdateIssueOnUi(issues,'Medium');
    }else
    {
        console.log('Low', data['risksissues']['issues']);
    }
});
@endsection