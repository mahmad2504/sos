@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/dygraph.css') }}" />
@endsection
@section('style')
.pill {font-size:.7rem;box-shadow: 0 0 2px 1px rgba(1, 0, 0, 0.5)}
@endsection
@section('content')
<?php $selected = 0;?>

<div id="container" style="width:90%; margin-left: auto; margin-right: auto; display:block" class="center">
	<div class="d-flex form-group">
			<label style="margin-top:3px;" for="jirauri">Milestones</label>
			<select style="margin-left:10px;" class="form-control-sm" id="milestones" name="jirauri">
				@for($i=0;$i<count($milestones);$i++)
					@if (strcmp($milestones[$i]->key,$key)==0)
						<option value="i" selected="selected">{{$milestones[$i]->summary}}</option>
						{{ $selected = 1}}
					@else
						<option value="i">{{$milestones[$i]->summary}}</option>
					@endif
				@endfor

				@if ($selected == 0)
					<option value="i" selected="selected">{{$key}}</option>
				@endif
			</select>
	</div>
	<div class="row">
		<div class="col-12">
			<div style="box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.5);" class="card text-center">
				<div class="card-header">
					<span class="float-left">Current Velocity  <span style id="cv"></span></span>
					<span >Progress = <span style="font-size:25px;" id="progress"></span></span>
					<span class="float-right">Required Velocity  <span style id="rv"></span></span>
				</div>
				<div class="card-body">
					<div style="margin-left: auto; margin-right: auto; width:100%;height:400px;" id="graphdiv"></div>
				</div>
				<div class="card-footer text-muted">
					<span class="float-left">Duedate  <span style id="duedate"></span></span>
					<span style id="title"></span>
					<span class="float-right">Expected Finish <span style id="finishingon"></span></span>
				</div>
			</div>
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
var milestones = @json($milestones);
var url = '{{route('showwburnupchart',[$user->name,$project->name])}}';
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
	$('#milestones').on('change', '', function (e) {
		var optionSelected = $('#milestones').prop('selectedIndex');
		console.log(optionSelected);
		milestone = milestones[optionSelected];
		url = url+"/"+milestone.key;
		ShowLoading();
		window.location.replace(url);
	
	});
	lowvelocity = IsVleocityLow(data.cv,data.rv);

	//console.log(data);
	$('#cv').html('<span class="badge badge-info">'+data.cv+'</span>');
	
	if(lowvelocity == 1)
		$('#rv').html('<span class="badge badge-danger">'+data.rv+'</span>');
	else
		$('#rv').html('<span class="badge badge-success">'+data.rv+'</span>');
	
	$('#progress').html('<span class="badge badge-success">&nbsp&nbsp'+data.progress+'%</span>');
	$('#title').text(data.summary);
	console.log(data);
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
		title =  title+'<span  class="pill badge badge-pill badge-warning">Low Velocity</span>&nbsp';
	
	if(data.duedate.length != 0)
	{
		$('#duedate').html('<span class="badge badge-info">'+MakeDate2(data.duedate)+'</span>'); 
		if(data.finishingon > data.duedate)
		{
			title =  title+'<span  class="pill badge badge-pill badge-danger">Delay</span>&nbsp';
			$('#finishingon').html('<span class="badge badge-danger">'+MakeDate2(data.finishingon)+'</span>');
		}
		else
		{
			if(data.status == 'RESOLVED')
				title =  title+'<span  class="pill badge badge-pill badge-success">Completed</span>&nbsp';
			else
				title =  title+'<span  class="pill badge badge-pill badge-success">On Track</span>&nbsp';
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
			xlabel: MakeDate2(data.start)+' - '+MakeDate2(data.end),
			labels: [ "x", "Target" ,"Earned","Past Target"],
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
					'Past Target': 
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
						
						return '<span style="font-size:10px;">'+Round(y)+'</span>';
                    }
                }
            }
        },
	);
});
@endsection