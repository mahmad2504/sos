@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
@endsection
@section('style')
body { font-family: Arial, Helvetica, sans-serif;font-size: 11px;}
.box {border-radius: 15px;width: 100em;background-color: white;color: #666;border: 1px #cccccc solid;margin: 50px auto 0px auto;padding: 0px 20px 0 20px;box-shadow: 10px 5px 5px grey;}
#content {color: #666;font-family: 'Gill Sans', Verdana, Helvetica, sans-serif;margin: 0px 0px 0px 0px;padding: 10px 10px 10px 10px;}
#content h1 {font-size: 12px;font-weight: bold;}
#content h1 a:link, a:visited, a:active {color: #74a8f5;font-family: 'Gill Sans', Verdana, Helvetica, sans-serif;font-weight: normal;}
#content h2 { font-size: 10px;letter-spacing: 3px;text-transform: uppercase;}
.select-css {
    
    font-size: 10px;
    font-family: sans-serif;
    font-weight: 700;
    color: #444;
    line-height: 1.3;
    padding: .6em 1.4em .5em .8em;
    width: 10%;
    max-width: 100%; 
    box-sizing: border-box;
    margin: 0;
    border: 1px solid #aaa;
    box-shadow: 0 1px 0 1px rgba(0,0,0,.04);
    border-radius: .5em;
    -moz-appearance: none;
    -webkit-appearance: none;
    appearance: none;
    background-color: #fff;
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007CB2%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'),
      linear-gradient(to bottom, #ffffff 0%,#e5e5e5 100%);
    background-repeat: no-repeat, repeat;
    background-position: right .7em top 50%, 0 0;
    background-size: .65em auto, 100%;
}
.select-css::-ms-expand {
    display: none;
}
.select-css:hover {
    border-color: #888;
}
.select-css:focus {
    border-color: #aaa;
    box-shadow: 0 0 1px 3px rgba(59, 153, 252, .7);
    box-shadow: 0 0 0 3px -moz-mac-focusring;
    color: #222; 
    outline: none;
}
.select-css option {
    font-weight:normal;
}
@endsection
@section('content')

<div id="container" style="width:70%; margin-left: auto; margin-right: auto; display:block" class="center">
    <select rel="tooltip" title="Select Year" class="select-css" id="select_year" name="year"></select>
    <select rel="tooltip" title="Select Week Number" style="width: 8%;" class="select-css" id="select_week" name="week"></select>
    <button id="viewreport" type="button" class="btn-outline  btn-primary">View Report</button>
    <span style="color:red" id="error"></span>
    <div class="box">
        <h1 style="margin-top:40px;font-weight: bold; color:CornflowerBlue;font-size:20px;"><span id="key">{{$key}}</span>&nbsp&nbspReport for the period <span id="from"></span>&nbsp-&nbsp<span id="to"><span></h1> 
        <hr>
        <div id="content">
         <h1 style="font-weight: bold; color:CornflowerBlue;font-size:20px;">Report for the period <span id="from"></span>&nbsp-&nbsp<span id="to"><span></h1>
        </div>
    </div>
</div>
<script src="{{ asset('js/msc-script.js') }}" ></script>
@endsection
@section('script')
var user = @json($user);
var project =  @json($project);
var isloggedin = {{$isloggedin}};
var data = @json($data);
var key= '{{$key}}';
var dataurl = '{{route('getweeklyreport',[$user->name,$project->name])}}';

if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
}

function OnReportDataReceived(indata)
{
    HideLoading();
    data = indata;
    console.log(data);
    PopulatePage();
}
function OnViewReportClick()
{
    console.log("OnViewReportClicked");
    year = $('#select_year').val();
    week = $('#select_week').val();
    LoadReport(year,week,OnReportDataReceived)
   
}

function LoadReport(year,weekno,onsuccess)
{
    ShowLoading();
	$.ajax({
		type:"GET",
		url:dataurl+"?year="+year+"&weekno="+weekno+"&key="+key,
		cache: false,
		data:null,
		success: onsuccess,
		error: function(data){ console.log(data.responseJSON.message);
            HideLoading();
            $('#error').text(data.responseJSON.message);
            setTimeout(function(){ $('#error').text('');}, 2000);
        }
	});
}
function PopulateWorkLog(link,summary,comment,displayname,worklogdate,timespent)
{
	var h1 = document.createElement("h1");  // Create with DOM
	h1.innerHTML = link+"  "+summary;
	$("#content").append(h1); 

    var p = document.createElement("p");  // Create with DOM
	if(comment.length == 0)
        comment = 'No Comments';
	p.innerHTML = '<li>'+comment+'</li>';
	$("#content").append(p);

    var p = document.createElement("p");  // Create with DOM
	p.setAttribute("align", "right");
	p.innerHTML = '<a href="">'+displayname+'</a> logged <a href="">'+timespent+' hour(s) on </a>'+
	'<span style="font-size: xx-small;">'+worklogdate+'&nbsp&nbsp&nbsp</span>';
	$("#content").append(p);
	
}
function PopulateYearSelect()
{
    $('#select_year').empty();
    for(var year in data['lists']) 
    {
        if(year == data['year'])
            $('#select_year').append('<option value="'+year+'" selected="selected">'+year+'</option>');
        else
            $('#select_year').append('<option value="'+year+'">'+year+'</option>');
    }
}
function PopulateWeekSelect()
{
    $('#select_week').empty();
    for(var week in data['lists'][data['year']]) 
    {
        if(week == data['week'])
            $('#select_week').append('<option value="'+week+'" selected="selected">Week '+week+'</option>');
        else
            $('#select_week').append('<option value="'+week+'">Week '+week+'</option>');
    }
}
function PopulatePage()
{
    console.log(data);
    PopulateYearSelect();
    PopulateWeekSelect();
    
    range = getDateRangeOfWeek(data.week,data.year);
    console.log(range);
    $('#from').text(MakeDate2(range.from));
    $('#to').text(MakeDate2(range.to));
    $("#content").empty();
    for(var task in data.worklogs) 
    {
        i=0;
        for(var date in data.worklogs[task])
        {
            worklog = data.worklogs[task][date];
            link = project.jiraurl+"/browse/"+worklog.jira;
            link = "<a href='"+link+"'>"+worklog.jira+"</a>";
            summary = worklog.summary;
            if(i++ > 0)
            {
                link = '';
                summary = '';
            }
            PopulateWorkLog( link, summary, worklog.comment,worklog.displayname,date,worklog.hours);
        }

    }
}
$(function() {
    $('#viewreport').click(OnViewReportClick);
   
    $('#select_year').on('change', function() {
        data['year'] = this.value;
        PopulateWeekSelect();
    });
    onchange="myFunction()"
    PopulatePage();
    
});

@endsection
