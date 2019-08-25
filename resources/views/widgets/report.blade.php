@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
@endsection
@section('style')
body { font-family: Arial, Helvetica, sans-serif;font-size: 11px;background-color: #AAB4BF;}

.box {width: 100em;background-color: white;color: #666;border: 1px #cccccc solid;margin: 50px auto 0px auto;padding: 0px 20px 0 20px;box-shadow: 10px 5px 5px grey;}
#content {color: #666;font-family: 'Gill Sans', Verdana, Helvetica, sans-serif;margin: 0px 0px 0px 0px;padding: 10px 10px 10px 10px;}
#content h1 {font-size: 12px;font-weight: bold;}
#content h1 a:link, a:visited, a:active {color: #74a8f5;font-family: 'Gill Sans', Verdana, Helvetica, sans-serif;font-weight: normal;}
#content h2 { font-size: 10px;letter-spacing: 3px;text-transform: uppercase;}
@endsection
@section('content')

<div id="container" style="width:70%; margin-left: auto; margin-right: auto; display:block" class="center">
    <div>
        <label>Year</label>
        <label style=margin-left:20px;">Week</label>
    </div>
    <select id="select_year" name="year"></select>
    <select id="select_week" name="week"></select>
    <button id="viewreport" type="button" class="btn-outline  btn-primary">View Report</button>
    <span style="color:red" id="error"></span>
    <div class="box">
        <h1 style="margin-top:40px;font-weight: bold; color:CornflowerBlue;font-size:20px;">Report for the period <span id="from"></span>&nbsp-&nbsp<span id="to"><span></h1> 
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

var dataurl = '{{route('getweeklyreport',[$user->name,$project->name])}}';
console.log(dataurl);
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
    month = $('#select_week').val();
    LoadReport(year,month,OnReportDataReceived)
   
}

function LoadReport(year,month,onsuccess)
{
    ShowLoading();
	$.ajax({
		type:"GET",
		url:dataurl+"/"+year+"/"+month,
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
            $('#select_week').append('<option value="'+week+'" selected="selected">'+week+'</option>');
        else
            $('#select_week').append('<option value="'+week+'">'+week+'</option>');
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
