@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />

@endsection
@section('style')
    .zui-table 
    {
        width: 100%;
        border: solid 1px #000000;
        border-collapse: collapse;
        border-spacing: 0;
        font: normal 13px Arial, sans-serif;
        background-color: #fff;
        box-shadow: 3px 3px 3px grey;
       
        border-radius: 5px;
    }
    .zui-table thead th {
        background-color: #DDEFEF;
        border: solid 2px #cdcdcd;
        font: normal 15px Arial, sans-serif;
        font-weight: bold;
        color: #000;
        padding: 10px;
        text-align: center;
        text-shadow: 1px 1px 1px #fff;
    }
    .zui-table tbody td {
        border: solid 2px #cdcdcd;
        color: #333;
        padding: 10px;
        text-align: center;
        text-shadow: 1px 1px 1px #fff;
    }
    .small {
        font: normal 13px Arial, sans-serif;
    }

@endsection
@section('content')
<div width="100%" class="container" >
    <h4 id="summary" style="margin-bottom:-17px;"></h4>
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


$('#summary').text(data['summary']);

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


'use strict';
if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
}

$(function() 
{

   
});
@endsection