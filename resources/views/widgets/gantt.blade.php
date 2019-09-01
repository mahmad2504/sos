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

<div id="container" style="width:95%; margin-left: auto; margin-right: auto; display:block" class="center">
	<div class="loading">Loading&#8230;</div>
	<!-- <button id="chart" style="display:hidden" type="button" class="float-right btn btn-outline-success btn-sm">Hide Chart</button> -->
	<p id='description'>Description</p>
	<div style="border:1px solid black; position:relative" class="gantt" id="GanttChartDIV"></div>
</div>


<script src="{{ asset('js/jsgantt.js') }}" ></script>
@endsection
@section('script')

var username = "{{$user->name}}";
var userid = {{$user->id}};
var projectid = {{$project->id}};
var cur_row = null;
var jiraurl =  null;
var  vMaxDate = null;
var isloggedin = {{$isloggedin}};
function drawCustomElements(g) {
  for (const item of g.getList()) {
    if (item.getDataObject().deadline) {
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
	for(var i=0;i<data.length;i++)
	{
		//if(data[i].pStatus == 'RESOLVED')
		var href = "#";
		if(data[i].pJira != data[i].pID)
			href=jiraurl+"/browse/"+data[i].pJira;
		else
			data[i].pJira = '';

		style = '';
		data[i].pEstimate = Math.round(data[i].pEstimate);
		
		if(data[i].pStatus == 'RESOLVED')
		{
			style = 'color:lightgrey;';
			data[i].pComp = "<span style='font-size:9px;"+style+"'>Done</span>";
			data[i].pEndString = "<span style='font-size:9px;"+style+"'>"+data[i].pClosedOn+"</span>";
			data[i].pEstimate = "<span style='font-size:9px;"+style+"'>"+data[i].pEstimate+"</span>";
		}
		else if(data[i].pStatus == 'INPROGRESS')
		{
			data[i].pEndString = '<span style="font-size:9px;">'+MakeDate2(data[i].pEnd)+'</span>';
			data[i].pComp = data[i].pComp+"%";
			data[i].pComp = "<span style='font-size:9px;"+style+"'>"+data[i].pComp+"</span>";
			data[i].pEstimate = "<span style='font-size:9px;'>"+data[i].pEstimate+"</span>";
		}
		else
		{
			data[i].pEndString = '<span style="font-size:9px;">'+MakeDate2(data[i].pEnd)+'</span>';
			data[i].pEstimate = "<span style='font-size:9px;'>"+data[i].pEstimate+"</span>";
			data[i].pComp = '';
		}

		data[i].pName = "<a style='"+style+"' class='taskname' href='"+href+"'>"+data[i].pName+"</a>";
		if(data[i].pStatus == 'RESOLVED')
			data[i].pCaption = "<a href='"+href+"'>"+data[i].pJira+"</a>";
		else
			data[i].pCaption = "&nbsp<span style='color:orange;'>"+data[i].pRes+"</span>&nbsp<a href='"+href+"'>"+data[i].pJira+"</a>";
		
		
		if(data[i].deadline.length > 0)
		{
			if(vMaxDate == null)
			{
				vMaxDate = data[i].deadline;
			}
			else
			{
				var d1 = dates.convert(vMaxDate);
				var d2 = dates.convert(data[i].deadline);
				if(dates.compare(d1,d2)<0)
					vMaxDate = data[i].deadline;
			}
		}
		g.AddTaskItemObject(data[i]);
	}
	
	
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
			pEstimate: {
				title: 'Estimate'
			},
			pComp: {
				title: 'Progress'
			},
			pEndString: {
				title: 'End'
		    },
		  //pStatus: {
		//	title: 'Status'
		 // },
		 // pPrioriy: {
		//	title: 'Priority'
		 // },
		 // pJira: {
		//	title: 'Jira'
		 // },
		 // pClosedOn : {
		//	title: 'Closed On'
		 // },
		 // pEstimate : {
		//	title: 'Estimate'
		 // },
		 // pTimeSpent : {
		//	title: 'TimeSpent'
		 // },
		},
	  vMaxDate : vMaxDate,
	  vUseSingleCell: 100000, // Set the threshold cell per table row (Helps performance for large data.
	  vFormatArr: ['Day', 'Week', 'Month'], // Even with setUseSingleCell using Hour format on such a large chart can cause issues in some browsers,  
	  vEvents: {
  
        beforeDraw: () => console.log('before draw listener'),
        afterDraw: () => {
          console.log('after draw listener');
		  drawCustomElements(g);
        }
      },	
	
	});
	//console.log(vMaxDate);
	
	//g.setShowDur(1);
	g.setShowRes(0);
	g.setShowStartDate(0);
	g.setShowEndDate(0);
	
	g.setDateInputFormat('yyyy-mm-dd'); 
	//g.setScrollTo('2018-07-02');
	
	
		
	//JSGantt.parseJSONString(data, g);
	
	g.Draw();
	
	
	$(".gtaskclosed").hover(function() {
        $(this).css('cursor','pointer').attr('title', 'Closed Task');
    }, function() {
        $(this).css('cursor','auto');
    });
	
	$(".gtaskopen").hover(function() {
        $(this).css('cursor','pointer').attr('title', 'Open Task');
    }, function() {
        $(this).css('cursor','auto');
    });
	$(".gtaskopenunestimated").hover(function() {
        $(this).css('cursor','pointer').attr('title', 'Unestimated Open Task');
    }, function() {
        $(this).css('cursor','auto');
    });
	
	$(".gtaskgreenunestimated").hover(function() {
        $(this).css('cursor','pointer').attr('title', 'Unestimated Task In Progress');
    }, function() {
        $(this).css('cursor','auto');
    });

	$(".gtaskgreen").hover(function() {
        $(this).css('cursor','pointer').attr('title', 'Task In Progress');
    }, function() {
        $(this).css('cursor','auto');
    });
	
	
	$(".taskname").hover(function() {
        $(this).css('cursor','pointer').attr('title', $(this).text());
    }, function() {
        $(this).css('cursor','auto');
    });
	
	$(".deadline-line").hover(function() {
        $(this).css('cursor','pointer').attr('title','Deadline');
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
	if(isloggedin)
	{
		$('.navbar').removeClass('d-none');
		$('#dashboard_menuitem').show();
		$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
	}

	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
	$('#chart').on('click',OnChartChangeClick);
	LoadProjectData("{{route('getproject',['id'=>$project->id])}}",null,OnProjectDataReceived,function(response){});
	$.ajax(
	{
		type:"GET",
		url:"{{ route('getganttdata',[$project->id]) }}/?key={{$key}}",
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