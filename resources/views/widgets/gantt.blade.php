@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/jsgantt.css') }}" />
@endsection
@section('style')
.progress {height: 10px;}
@endsection
@section('content')



<div id="container" style="width:90%; margin-left: auto; margin-right: auto; display:block" class="center">
	<div class="loading">Loading&#8230;</div>
	<button id="chart" type="button" class="float-right btn btn-outline-success btn-sm">Hide Chart</button>
	<p id='description'>Description</p>
</div>

<div style="position:relative" class="gantt" id="GanttChartDIV"></div>

<script src="{{ asset('js/jsgantt.js') }}" ></script>
@endsection
@section('script')

var username = "{{$user->name}}";
var userid = {{$user->id}};
var projectid = {{$project->id}};
var cur_row = null;
var jiraurl =  null;
function ShowGantt(data)
{
	var g = new JSGantt.GanttChart(document.getElementById('GanttChartDIV'), 'day');
	g.setOptions({
	  vCaptionType: 'Complete',  // Set to Show Caption : None,Caption,Resource,Duration,Complete,     
	  vQuarterColWidth: 36,
	  vDateTaskDisplayFormat: 'day dd month yyyy', // Shown in tool tip box
	  vDayMajorDateDisplayFormat: 'mon yyyy - Week ww',// Set format to dates in the "Major" header of the "Day" view
	  vWeekMinorDateDisplayFormat: 'dd mon', // Set format to display dates in the "Minor" header of the "Week" view
	  vLang: 'en',
	  vShowTaskInfoLink: 1, // Show link in tool tip (0/1)
	  vShowEndWeekDate: 0,  // Show/Hide the date for the last day of the week in header for daily
	  vAdditionalHeaders: { 
		  pIndex: {
			title: 'Index'
		  },
		  pStatus: {
			title: 'Status'
		  },
		  pPrioriy: {
			title: 'Priority'
		  },
		  pJira: {
			title: 'Jira'
		  },
		  pClosedOn : {
			title: 'Closed On'
		  }
		},
	  vUseSingleCell: 100000, // Set the threshold cell per table row (Helps performance for large data.
	  vFormatArr: ['Day', 'Week', 'Month', 'Quarter'], // Even with setUseSingleCell using Hour format on such a large chart can cause issues in some browsers,  
	});
	g.setShowDur(0);
	//g.setShowStartDate(0);
	g.setShowCost(1);
	g.setDateInputFormat('yyyy-mm-dd'); 
	g.setScrollTo('2018-07-02');
	// Load from a Json url
	console.log(data);
	for(var i=0;i<data.length;i++)
	{
		if(data[i].pJira.length > 0) 
			data[i].pName = "<a href='"+jiraurl+"/browse/"+data[i].pJira+"'>"+data[i].pName+"</a>";
		
		
		g.AddTaskItemObject(data[i]);
	}
		
	//JSGantt.parseJSONString(data, g);
	
	g.Draw();
}	

function LoadProjectData(url,data,onsuccess,onfail)
{
	$.ajax({
		type:"GET",
		url:url,
		cache: false,
		data:data,
		success: onsuccess,
		error: onfail
	});
}
function OnProjectDataReceived(response)
{
	console.log(response);
	jiraurl = response.jiraurl;
	$('#description').text(response.description);
}
function OnChartChangeClick(event)
{
	console.log("ddd");
	var val = $('.gmainleft').css("flex");
	if(val == '0 0 100%')
	{
		$('.gmainleft').css('flex','0 0 30%');
		$('#chart').text('Hide Chart');
	}
	else
	{
		$('.gmainleft').css('flex','0 0 100%');
		$('#chart').text('Show Chart');
	}
	
}
$(document).ready(function()
{
	if(username != null)
		$('.navbar').removeClass('d-none');
	
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
	$('#chart').on('click',OnChartChangeClick);
	LoadProjectData("{{route('getproject',['id'=>$project->id])}}",null,OnProjectDataReceived,function(response){});
	$.ajax(
	{
		type:"GET",
		url:"{{ route('getganttdata',[$project->id]) }}",
		data:null,
		success: function(response)
		{
			$('.loading').hide();
			ShowGantt(response) ;
		},
		error: function (error) 
		{
			$('.loading').hide();
			console.log(error);  
			mscAlert('Error', 'Project Database Missing. Please sync with Jira and try again', function(){window.location.href = "/";})
		}
	});
	return;
})
@endsection