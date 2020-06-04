@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/dygraph.css') }}" />
@endsection
@section('style')
.pill {font-size:.7rem;box-shadow: 0 0 2px 1px rgba(1, 0, 0, 0.5)}
body {
	background-color: #fff;
}
@endsection
@section('content')
<?php $selected = 0;?>

<div style="width:80%; margin-left: auto; margin-right: auto" class="center">
	<div id="selectbox" style="display:none;">
	<h3 >{{ $project->name}}</h3>
	<div class="d-flex form-group">
			<label  for="jirauri"></label>
			<select class="form-control-sm" id="milestones" name="jirauri">
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
	</div>
	
	<div class="row">
		<div class="col-7">
			<div style="box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.5);" class="card text-center">
				<div class="card-body">
					<div class="row">
					<div class="col-12">
						<div style="margin-left: auto; margin-right: auto;width:100%" id="graphdiv"></div>
					</div>
					<!--<div class="col-3" style="font-size:12px;">
						<span class="float-left">Current Velocity  <span style id="cv"></span></span><br>
						<span class="float-left">Required Velocity  <span style id="rv"></span></span><br>
						<span class="float-left">Duedate  <span style id="duedate"></span></span><br>
						<span class="float-left">Expected Finish  <span style id="finishingon"></span></span>
					</div>-->
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
var iframe = {{$iframe}};
var milestones = @json($milestones);
var url = '{{route('showwburnupchart',[$user->name,$project->id])}}';
'use strict';

if(isloggedin)
{
	if(iframe==0)
	{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
	}
}
if(iframe==1)
{
	$('#selectbox').hide();
	$('#footer').hide();
}
else
	$('#selectbox').show();

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
	if(data.sprintinfo !== undefined)
		$('#title').text(data.sprintinfo.name);
	
	//console.log(data);
	for(var date in data.data)
	{
		var row = [];
		row[0]=new Date(date);
		row[3]= data.data[date].tv;
		row[2]= data.data[date].ev;
		row[1]= data.data[date].ftv;
		graphdata[i++] = row;
	}
	title = '';
	title =  title+'<span  class="pill badge badge-pill badge-info">'+data.progress+'% </span>&nbsp';
	//if(lowvelocity == 1)
	//	title =  title+'<span  class="pill badge badge-pill badge-warning">Low Velocity</span>&nbsp';
	
	
	
	if(data.duedate.length != 0)
	{
		$('#duedate').html('<span class="badge badge-info">'+MakeDate2(data.duedate)+'</span>'); 
		if(data.finishingon > data.duedate)
		{
			if(lowvelocity)
				title =  title+'<span  class="pill badge badge-pill badge-warning">Lagging</span>&nbsp';
			else
				title =  title+'<span  class="pill badge badge-pill badge-success">On Track</span>&nbsp';
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
		title += '<span style="font-size:10px;" class="pill badge badge-pill badge-info">'+"CV "+data.cv+'</span>&nbsp';
		if(lowvelocity == 1)
			title += '<span style="font-size:10px;" class="pill badge badge-pill badge-danger">'+"RV "+data.rv+'</span>&nbsp';
		else
			title += '<span style="font-size:10px;" class="pill badge badge-pill badge-success">'+"RV "+data.rv+'</span>&nbsp';
		
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
            ylabel: 'Mandays / Points',
			y2label: 'Mumtaz',
			xlabel: MakeDate2(data.start)+' - '+MakeDate2(data.end),
			xLabelHeight :12,
			yLabelWidth :14,
			labels: [ "x", "Target" ,"Earned","Past Target"],
			showRangeSelector: false,	
			//strokeWidth: .5,
            //gridLineColor: 'rgb(123, 00, 00)',
			//fillGraph: [false,false,false],
			animatedZooms: true,
			
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