@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/bootstrap.3.3.7.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/timechart.style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
@endsection
@section('style')
@endsection
@section('content')
<div id="container" style="width:90%; margin-left: auto; margin-right: auto; display:block" class="center">
	 <div class="gantt"></div>
</div>
<script src="{{ asset('js/utility.js') }}" ></script>
<script src="{{ asset('js/bootstrap.3.3.7.min.js') }}" ></script>
<script src="{{ asset('js/jquery.fn.gantt.js') }}" ></script>
<script src="{{ asset('js/msc-script.js') }}" ></script>

@endsection
@section('script')
var username = "{{$user->name}}";
var userid = {{$user->id}};
var projectid = {{$project->id}};
var isloggedin = {{$isloggedin}};

if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
}
$('#navbarDropdown').hide();

$(function() {
	$.ajax(
	{
		type:"GET",
		url:"{{ route('getdailytimechartdata',[$project->id]) }}",
		data:null,
		success: function(response)
		{
			$('.loading').hide();
			ShowTimeChart(response) ;
		},
		error: function (error)
		{
			$('.loading').hide();
			console.log(error);
			mscAlert('Error', 'Project Database Missing. Please sync with Jira and try again', function(){window.location.href = "/";})
		}
	});
});

function endOfMonth(date)
{
	var ndate = new Date(date.getFullYear(), date.getMonth()+1, 0);
	var year = ndate.getFullYear();
	var month = ndate.getMonth();
	var day = ndate.getDate();
	return year+"-"+("0" + (month + 1)).slice(-2)+"-"+day;
}
function endOfWeek(date)
{
    var lastday = date.getDate() - (date.getDay() - 1) + 6;
    var ndate = new Date(date.setDate(lastday));
		var year = ndate.getFullYear();
		var month = ndate.getMonth();
		var day = ndate.getDate();
		return year+"-"+("0" + (month + 1)).slice(-2)+"-"+day;
}
var weekdatasource = [];
var daydatasource = [];
var yeardatasource = [];
function ModifyData(data,datemodifer)
{
	var weekdata = [];
	for(var user in data)
	{
			var type = 'jira';
			for(var date in data[user][type])
			{
					//console.log(date);
					var weekenddate = datemodifer(new Date(date));
					if(weekdata[user] === undefined)
					{
						weekdata[user] = [];
						weekdata[user][type] = [];
						weekdata[user]['oa'] = [];
						weekdata[user]['name'] = data[user]['name'];
					}
					if(weekdata[user][type][weekenddate] == undefined)
					{
						weekdata[user][type][weekenddate] = [];
						weekdata[user][type][weekenddate]['decimal_hours'] = parseFloat(data[user][type][date]['decimal_hours']);
						weekdata[user][type][weekenddate]['approved'] = data[user][type][date]['approved'];
					}
					else
					{
						weekdata[user][type][weekenddate]['decimal_hours'] += parseFloat(data[user][type][date]['decimal_hours']);
						if(weekdata[user][type][weekenddate]['approved'] == true)
							weekdata[user][type][weekenddate]['approved'] = data[user][type][date]['approved'];
					}
			}
			type = 'oa';
			//console.log(weekdata);
			for(var date in data[user][type])
			{
					console.log(date);
					var weekenddate = datemodifer(new Date(date));

					if(weekdata[user] === undefined)
					{
						weekdata[user] = [];
						weekdata[user]['jira'] = [];
						weekdata[user][type] = [];
						weekdata[user]['name'] = data[user]['name'];
					}
					if(weekdata[user][type][weekenddate] == undefined)
					{
						weekdata[user][type][weekenddate] = [];
						weekdata[user][type][weekenddate]['decimal_hours'] = parseFloat(data[user][type][date]['decimal_hours']);
						weekdata[user][type][weekenddate]['approved'] = data[user][type][date]['approved'];
					}
					else
					{
						//console.log(weekdata[user][type][weekenddate]['decimal_hours']);
						weekdata[user][type][weekenddate]['decimal_hours'] += parseFloat(data[user][type][date]['decimal_hours']);
						//console.log(weekdata[user][type][weekenddate]['decimal_hours']);
						if(weekdata[user][type][weekenddate]['approved'] == true)
							weekdata[user][type][weekenddate]['approved'] = data[user][type][date]['approved'];
					}
			}
	}
	return weekdata;
}
function FormatDataForGantt(data)
{
	var datasource = [];
	var i=0;
	for(var user in data)
	{
			var worklog = data[user];
		//	console.log(worklog);
			// propertyName is what you want
			// you can get the value like this: myObject[propertyName]
			//console.log(worklog);
			var obj = {};
			obj.name = worklog['name'];
			obj.desc = 'Jira';
			obj.values = [];
			var j=0;
			for(var date in worklog['jira'])
			{
					var value = {};
					value.from = new Date(date).getTime();
					value.to = new Date(date).getTime();
					Math.ceil()
					if(worklog['jira'][date]['decimal_hours'] > 99)
							worklog['jira'][date]['decimal_hours'] = Math.ceil( worklog['jira'][date]['decimal_hours']);
					else
						worklog['jira'][date]['decimal_hours'] = Math.round( worklog['jira'][date]['decimal_hours'] * 10 ) / 10;
					value.label =  removeTrailingZeros(worklog['jira'][date]['decimal_hours']);
					value.customClass =  "ganttBlue";
					obj.values[j++] = value;
			}
			datasource[i++] = obj;

			var obj = {};
			obj.name = '';
			obj.desc = 'OpenAir';
			obj.values = [];
			var j=0;
			//console.log(worklog['oa']);
			for(var date in worklog['oa'])
			{
					var value = {};
					value.from = new Date(date).getTime();
					value.to = new Date(date).getTime();
					if(worklog['oa'][date]['decimal_hours'] > 99)
						worklog['oa'][date]['decimal_hours'] = Math.ceil( worklog['oa'][date]['decimal_hours']);
					else
						worklog['oa'][date]['decimal_hours'] = Math.round( worklog['oa'][date]['decimal_hours'] * 10 ) / 10;
					value.label =  removeTrailingZeros(worklog['oa'][date]['decimal_hours']) ;
					if(worklog['oa'][date]['approved'] === false)
					{
						value.customClass =  "ganttRed";
					}
					else
						value.customClass =  "ganttLightBlue";
					obj.values[j++] = value;
			}
			if(obj.values.length > 0)
					 datasource[i++] = obj;
	}
	return datasource;
}
 function ShowTimeChart(data)
 {
    //console.log(data);

    daydatasource = FormatDataForGantt(data);
		weekdatasource = FormatDataForGantt(ModifyData(data,endOfWeek));
		monthdatasource = FormatDataForGantt(ModifyData(data,endOfMonth));

		var scale = "days";
    var settings = {
        source: daydatasource,
        navigate: "scroll",
        scale: scale,
        maxScale: "months",
        minScale: "days",
        itemsPerPage: 100,
        scrollToToday: true,
        useCookie: true,
        onItemClick: function(data) {
            alert(data);
        },
        onAddClick: function(dt, rowId) {
            alert("Empty space clicked - add an item!");
        },
        onRender: function() {
            if (window.console && typeof console.log === "function") {
                console.log("chart rendered");
								$(".nav-zoomOut").click(function(event)
								{
									if(scale == 'days')
									{
									    scale = 'weeks';
											settings.source = weekdatasource;
									}
									else if(scale == 'weeks')
									{
									    scale = 'months';
											settings.source = monthdatasource;
									}
									else
									     return;
									 console.log("Zoom Out Clicked");
									 //settings.source = demoSource;
									 settings.scale = scale;
									 $(".gantt").gantt(settings);

								});

								$(".nav-zoomIn").click(function(event)
								{
									if(scale == 'months')
									{
											settings.source = weekdatasource;
									    scale = 'weeks';
									}
									else if(scale == 'weeks')
									{
										  settings.source = daydatasource;
									    scale = 'days';
									}
									else
									     return;
									 console.log("Zoom In Clicked");
									 //settings.source = demoSource;
									 settings.scale = scale;
									 $(".gantt").gantt(settings);

								});
            }
        }};
    $(".gantt").gantt(settings);
    /*$(".gantt").popover({
        selector: ".bar",
        title: function _getItemText() {
            return this.textContent;
        },
        container: '.gantt',
        content: "Here's some useful information.",
        trigger: "hover",
        placement: "auto right"
    });*/

    //prettyPrint();

}
@endsection
