@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/dygraph.css') }}" />
@endsection
@section('style')

@endsection
@section('content')
<div id="container" style="width:50%; margin-left: auto; margin-right: auto; display:block" class="center">
	<div class="row">
		<div class="col-1">
		</div>
		<div class="col-10">
			<div class="card text-center">
				<div class="card-header">
					<span class="float-left">Current Velocity  <span style id="cv"></span></span>
					<span >Progress = <span style id="progress"></span></span>
					<span class="float-right">Required Velocity  <span style id="rv"></span></span>
				</div>
				<div class="card-body">
					<div style="margin-left: auto; margin-right: auto; width:100%;height:300px;" id="graphdiv"></div>
				</div>
				<div class="card-footer text-muted">
					<span class="float-left">Duedate  <span style id="duedate"></span></span>
					<span class="float-right">Expected Finish <span style id="finishingon"></span></span>
				</div>
			</div>
		</div>
		<div class="col-1">
		</div>
	</div>
</div>
<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('js/dygraph.min.js') }}" ></script>
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

$(function() 
{
	var graphdata = [];
	i = 0;
	lowvelocity = 0;
	if(data.cv < (85/100)*data.rv)
		lowvelocity = 1;
	console.log(data);
	$('#cv').html('<span class="badge badge-info">'+data.cv+'</span>');
	
	if(lowvelocity == 1)
		$('#rv').html('<span class="badge badge-danger">'+data.rv+'</span>');
	else
		$('#rv').html('<span class="badge badge-success">'+data.rv+'</span>');
	
	$('#progress').html('<span class="badge badge-success">&nbsp&nbsp'+data.progress+'%</span>');

	
	for(var date in data.data)
	{
		var row = [];
		row[0]=new Date(date);
		row[3]= data.data[date].tv;
		row[2]= data.data[date].ev;
		row[1]= data.data[date].ftv;
		graphdata[i++] = row;
	}
	title = 'Earned Value Graph&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
	
	if(lowvelocity == 1)
		title =  title+'<span class="badge badge-pill badge-warning">Low Velocity</span>';
	
	
	if(data.duedate.length != 0)
	{
		$('#duedate').html('<span class="badge badge-info">'+MakeDate2(data.duedate)+'</span>'); 
		if(data.finishingon > data.duedate)
		{
			title =  title+'<span class="badge badge-pill badge-danger">Delay</span>';
			$('#finishingon').html('<span class="badge badge-danger">'+MakeDate2(data.finishingon)+'</span>');
		}
		else
		{
			title =  title+'<span class="badge badge-pill badge-success">On Track</span>';
			$('#finishingon').html('<span class="badge badge-success">'+MakeDate2(data.finishingon)+'</span>');
		}
	}
	else
	{
		$('#duedate').html('<span class="badge badge-info">No Deadline</span>');
	}
	

	g = new Dygraph
	(
		// containing div
		document.getElementById("graphdiv"),
		graphdata,
		{
			title: title,
            ylabel: 'Earned Values (Days of work)',
			xlabel: 'Period '+MakeDate2(data.start)+' - '+MakeDate2(data.end),
			labels: [ "x", "Target" ,"Earned","Past"],
			showRangeSelector: false,
			//strokeWidth: .5,
            //gridLineColor: 'rgb(123, 00, 00)',
			//fillGraph: [false,false,false],
			animatedZooms: true,
			width: 640,
            height: 480,
            colors: ['E69997', '#54A653', '#284785','#284785' ],
            visibility: [true, true, true],
			series: 
			{
					'Past': 
					{
                        fillGraph:true,
						color: 'red',
						strokeWidth: 2,
                    },
					Earned: 
					{
                        fillGraph:true,
                        color: 'green',
                        strokeWidth: 2,
                    },
					'Target': 
					{
                        fillGraph:false,
						color: 'grey',
						strokeWidth: 2,
                    },
			},
			axes: 
			{
                x: 
				{
                	axisLabelFormatter: function(x) 
					{
						v = MakeDate2(x);
                        return '<span style="font-size:10px;">'+v+'</span>';
                    }
            	},
                y: 
				{
                    axisLabelFormatter: function(y) 
					{
						return '<span style="font-size:10px;">'+y+'</span>';
                    }
                }
            }
        },
	);
});
@endsection