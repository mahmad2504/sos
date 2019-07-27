@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/jsgantt.css') }}" />
@endsection
@section('style')
.progress {height: 10px;}
.deadline-line {
      position: absolute;
      top: 0;
      width: 3px;
      height: 22px;
      background: #ff0000;
    }
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
function drawCustomElements(g) {
  for (const item of g.getList()) {
    if (item.getDataObject().deadline) {
		console.log((item.getDataObject().deadline));
		console.log((item.getDataObject().pJira));
      const x = g.chartRowDateToX(new Date(item.getDataObject().deadline));
      const td = item.getChildRow().querySelector('td');
      td.style.position = 'relative';
      const div = document.createElement('div');
      div.style.left = `${x}px`;
      div.classList.add('deadline-line');
      td.appendChild(div);
    }
  }
}

function ShowGantt(data)
{
	var g = new JSGantt.GanttChart(document.getElementById('GanttChartDIV'), 'day');
	g.setOptions({
	  vCaptionType: 'Caption',  // Set to Show Caption : None,Caption,Resource,Duration,Complete,     
	  vQuarterColWidth: 36,
	  vDateTaskDisplayFormat: 'day dd month yyyy', // Shown in tool tip box
	  vDayMajorDateDisplayFormat: 'mon yyyy - Week ww',// Set format to dates in the "Major" header of the "Day" view
	  vWeekMinorDateDisplayFormat: 'dd mon', // Set format to display dates in the "Minor" header of the "Week" view
	  vLang: 'en',
	  vShowTaskInfoLink: 0, // Show link in tool tip (0/1)
	  vShowEndWeekDate: 0,  // Show/Hide the date for the last day of the week in header for daily
	  vAdditionalHeaders: { 
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
	  vEvents: {
  
        beforeDraw: () => console.log('before draw listener'),
        afterDraw: () => {
          console.log('after draw listener');
		  drawCustomElements(g);
        }
      },	
	
	});
	g.setShowDur(1);
	
	g.setDateInputFormat('yyyy-mm-dd'); 
	g.setScrollTo('2018-07-02');
	// Load from a Json url
	for(var i=0;i<data.length;i++)
	{
		if(data[i].pJira.length > 0) 
		{
			var href=jiraurl+"/browse/"+data[i].pJira;
			data[i].pName = "<a class='taskname' href='"+href+"'>"+data[i].pName+"</a>";
			data[i].pCaption = "<a href='"+href+"'>"+data[i].pJira+"</a>";
		}
		g.AddTaskItemObject(data[i]);
	}
		
	//JSGantt.parseJSONString(data, g);
	
	g.Draw();
	
	$(".taskname").hover(function() {
        $(this).css('cursor','pointer').attr('title', $(this).text());
    }, function() {
        $(this).css('cursor','auto');
    });
	
	

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
	//console.log(response);
	jiraurl = response.jiraurl;
	$('#description').text(response.description);
}
function OnChartChangeClick(event)
{
	let style = $('.gmainleft')[0].getAttribute('style');
	if(style == null)
	{
		$('.gmainleft')[0].setAttribute('style','flex: 0 0 100%');
		$('#chart').text('Show Chart');
	}
	else if(style == 'flex: 0 0 100%')
	{
		$('.gmainleft')[0].setAttribute('style','flex: 0 0 30%');
		$('#chart').text('Hide Chart');
	}
	else if(style == 'flex: 0 0 30%')
	{
		$('.gmainleft')[0].setAttribute('style','flex: 0 0 100%');
		$('#chart').text('Hide Chart');
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