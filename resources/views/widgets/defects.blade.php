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
<div class="row">
<div style="width:96%; margin-left: auto; margin-right: auto" class="center">
	
			<div style="float:left;width:49%; box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.5);" class="card text-center">
				<div class="card-body">
					<div class="row">
					<div class="col-10">
						<div style="margin-left: auto; margin-right: auto;width:100%" id="all_graphdiv"></div>
					</div>
					<div class="col-2">
						<div id="all_status"></div>
					</div>
					</div>
				</div>
			</div>
			
			
			<div style="float:right;width:49%;box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.5);" class="card text-center">
				<div class="card-body">
					<div class="row">
					<div class="col-10">
						<div style="margin-left: auto; margin-right: auto;width:100%" id="high_graphdiv"></div>
					</div>
					<div class="col-2">
						<div  id="high_status"></div>
					</div>
				    </div>
				</div>
			</div>
			
</div>
</div>
<div class="row">
<div style="width:96%; margin-left: auto; margin-right: auto" class="center">
<div style="float:left;margin-left:10px" id="all_defects"></div>
<div style="float:right;margin-left:10px" id="high_defects"></div>
</div>
</div>			

<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('js/dygraph.min.js') }}" ></script>

@endsection
@section('script')
var user = @json($user);
var project =  @json($project);
var key = '{{$key}}';
var high_priority_defects = @json($high_priority_defects);
var all_defects = @json($all_defects);
var iframe = {{$iframe}};
'use strict';

var high_tickets = [];
if(iframe==1)
	$('#footer').hide();
for(var propertyName in high_priority_defects) {
  //console.log(high_priority_defects[propertyName]);
  for(var key in high_priority_defects[propertyName].created_tasks) {
	  high_tickets[key] = high_priority_defects[propertyName].created_tasks[key];
  }
  for(var key in high_priority_defects[propertyName].closed_tasks) {
	  high_tickets[key] = high_priority_defects[propertyName].closed_tasks[key];
  }
}

all_tickets = [];
for(var propertyName in all_defects) {
  //console.log(all_defects[propertyName]);
  for(var key in all_defects[propertyName].created_tasks) {
	  all_tickets[key] = all_defects[propertyName].created_tasks[key];
  }
  for(var key in all_defects[propertyName].closed_tasks) {
	  all_tickets[key] = all_defects[propertyName].closed_tasks[key];
  }
}
console.log("All Defect");
console.log(all_tickets);

function darkenColor(colorStr) {
	var color = Dygraph.toRGB_(colorStr);
	color.r = Math.floor((255 + color.r) / 2);
	color.g = Math.floor((255 + color.g) / 2);
	color.b = Math.floor((255 + color.b) / 2);
	return 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')';
	}


function barChartPlotter(e) {
	var ctx = e.drawingContext;
	var points = e.points;
	var y_bottom = e.dygraph.toDomYCoord(0);

	ctx.fillStyle = darkenColor(e.color);

	// Find the minimum separation between x-values.
	// This determines the bar width.
	var min_sep = Infinity;
	for (var i = 1; i < points.length; i++) {
	  var sep = points[i].canvasx - points[i - 1].canvasx;
	  if (sep < min_sep) min_sep = sep;
	}
	var bar_width = 7;//Math.floor(2.0 / 3 * min_sep);

	// Do the actual plotting.
	for (var i = 0; i < points.length; i++) {
	  var p = points[i];
	  if(e.seriesIndex==4)
		var center_x = p.canvasx-5;
	else
		var center_x = p.canvasx-12;
	  ctx.fillRect(center_x - bar_width / 2, p.canvasy,
		  bar_width, y_bottom - p.canvasy);

	  ctx.strokeRect(center_x - bar_width / 2, p.canvasy,
		  bar_width, y_bottom - p.canvasy);
	}
	}

$(function() 
{
	var i=0;
	for(var key in all_tickets) 
	{
		if(all_tickets[key]==1)
		{
			$('#all_defects').append(key+", ");
			i++;
			if(i%8==0)
				$('#all_defects').append('<br>');
		}
	}
	var i=0;
	
	console.log("High priority defects");
console.log(high_tickets);

	for(var key in high_tickets) 
	{
		if(high_tickets[key]==1)
		{
			$('#high_defects').append(key+", ");
			i++;
		    if(i%8==0)
			  $('#high_defects').append('<br>');
		}
	}

	//console.log(high_priority_defects);
	var graphdata = [];
	for(var date in all_defects)
	{
		var obj=all_defects[date];
		var row = [];
		row[0]=new Date(date);
		row[1]= obj.acc_created;
		row[2]= obj.acc_closed;
		row[3]= obj.acc_created-obj.acc_closed;
		row[4]= obj.created;
		row[5]= obj.closed;
		graphdata.push(row);
	}
	
	var sample_count = graphdata.length;
	var visi = [true, true, true,true,true];
	if(sample_count < 6)
	{
		visi = [true, true, false,true,true];
	}
	if(sample_count == 0)
	{
		var row = [];
		row[0]=new Date();
		row[1]= 0;
		row[2]= 0;
		row[3]= 0;
		row[4]= 0;
		row[5]= 0;
		graphdata.push(row);
	}
	g = new Dygraph
	(
		// containing div
		document.getElementById("all_graphdiv"),
		graphdata,
		{
			
			title: 'All Issues',
            ylabel: 'No Of Defects',
			xlabel: 'Date',
			xLabelHeight :12,
			yLabelWidth :14,
			labels: [ "Date", "Opened" ,"Fixed","Delta","Created","Closed"],
			
			labelsDiv: document.getElementById('all_status'),
            labelsSeparateLines: false,
            legend: 'always',
				
			showRangeSelector: false,	
			//strokeWidth: .5,
            //gridLineColor: 'rgb(123, 00, 00)',
			//fillGraph: [false,false,false],
			animatedZooms: true,
			
            colors: ['E69997', '#54A653', '#284785','#CD5C5C','#006400'],
            visibility: visi,
			series: 
			{
					'Opened': 
					{
                        fillGraph:true,
						color: '#FFB6C1',
						strokeWidth: 2,
                    },
					'Fixed': 
					{
                        fillGraph:true,
                        color: 'green',
                        strokeWidth: 2,
                    },
					'Delta': 
					{
                        fillGraph:true,
						color: '#6495ED',
						strokeWidth: 2,
                    },
					'Created': {
						plotter: barChartPlotter
					},
					'Closed': {
						plotter: barChartPlotter
					}
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
	var graphdata = [];
	for(var date in high_priority_defects)
	{
		var obj=high_priority_defects[date];
		var row = [];
		row[0]=new Date(date);
		row[1]= obj.acc_created;
		row[2]= obj.acc_closed;
		row[3]= obj.acc_created-obj.acc_closed;
		row[4]= obj.created;
		row[5]= obj.closed;
		graphdata.push(row);
	}
	var sample_count = graphdata.length;
	//var visi = [true, true, true,true,true];
	if(sample_count < 6)
	{
		visi = [true, true, false,true,true];
	}
	if(sample_count == 0)
	{
		var row = [];
		row[0]=new Date();
		row[1]= 0;
		row[2]= 0;
		row[3]= 0;
		row[4]= 0;
		row[5]= 0;
		graphdata.push(row);
	}
	g = new Dygraph
	(
		// containing div
		document.getElementById("high_graphdiv"),
		graphdata,
		{
			title: 'Major Issues',
            ylabel: 'No Of Defects',
			xlabel: 'Date',
			xLabelHeight :12,
			yLabelWidth :14,
			labels: [ "x", "Opened" ,"Fixed","Delta","Created","Closed"],
			labelsDiv: document.getElementById('high_status'),
            labelsSeparateLines: false,
            legend: 'always',
			showRangeSelector: false,	
			//strokeWidth: .5,
            //gridLineColor: 'rgb(123, 00, 00)',
			//fillGraph: [false,false,false],
			animatedZooms: true,
			
            colors: ['E69997', '#54A653', '#284785','#CD5C5C','#006400'],
            visibility: visi,
			series: 
			{
					'Opened': 
					{
                        fillGraph:true,
						color: '#FFB6C1',
						strokeWidth: 2,
                    },
					'Fixed': 
					{
                        fillGraph:true,
                        color: 'green',
                        strokeWidth: 2,
                    },
					'Delta': 
					{
                        fillGraph:true,
						color: '#6495ED',
						strokeWidth: 2,
                    },
					'Created': {
						plotter: barChartPlotter
					},
					'Closed': {
						plotter: barChartPlotter
					}
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