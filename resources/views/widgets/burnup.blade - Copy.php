@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/chart.min.css') }}" />
@endsection
@section('style')

@endsection
@section('content')
<div id="container" style="width:50%; margin-left: auto; margin-right: auto; display:block" class="center">
    <canvas id="burnupchart" style="width:400px;height:300px;"></canvas>
</div>
<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('js/chart.min.js') }}" ></script>
@endsection
@section('script')
var user = @json($user);
var project =  @json($project);
var isloggedin = {{$isloggedin}};
var data = @json($data);
var key = '{{$key}}';

'use strict';

window.chartColors = {
	red: 'rgb(255, 99, 132)',
	orange: 'rgb(255, 159, 64)',
	yellow: 'rgb(255, 205, 86)',
	green: 'rgb(75, 192, 192)',
	blue: 'rgb(54, 162, 235)',
	purple: 'rgb(153, 102, 255)',
	grey: 'rgb(201, 203, 207)'
};

(function(global) {
	var MONTHS = [
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	];

	var COLORS = [
		'#4dc9f6',
		'#f67019',
		'#f53794',
		'#537bc4',
		'#acc236',
		'#166a8f',
		'#00a950',
		'#58595b',
		'#8549ba'
	];

	var Samples = global.Samples || (global.Samples = {});
	var Color = global.Color;

	Samples.utils = {
		// Adapted from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
		srand: function(seed) {
			this._seed = seed;
		},

		rand: function(min, max) {
			var seed = this._seed;
			min = min === undefined ? 0 : min;
			max = max === undefined ? 1 : max;
			this._seed = (seed * 9301 + 49297) % 233280;
			return min + (this._seed / 233280) * (max - min);
		},

		numbers: function(config) {
			var cfg = config || {};
			var min = cfg.min || 0;
			var max = cfg.max || 1;
			var from = cfg.from || [];
			var count = cfg.count || 8;
			var decimals = cfg.decimals || 8;
			var continuity = cfg.continuity || 1;
			var dfactor = Math.pow(10, decimals) || 0;
			var data = [];
			var i, value;

			for (i = 0; i < count; ++i) {
				value = (from[i] || 0) + this.rand(min, max);
				if (this.rand() <= continuity) {
					data.push(Math.round(dfactor * value) / dfactor);
				} else {
					data.push(null);
				}
			}

			return data;
		},

		labels: function(config) {
			var cfg = config || {};
			var min = cfg.min || 0;
			var max = cfg.max || 100;
			var count = cfg.count || 8;
			var step = (max - min) / count;
			var decimals = cfg.decimals || 8;
			var dfactor = Math.pow(10, decimals) || 0;
			var prefix = cfg.prefix || '';
			var values = [];
			var i;

			for (i = min; i < max; i += step) {
				values.push(prefix + Math.round(dfactor * i) / dfactor);
			}

			return values;
		},

		months: function(config) {
			var cfg = config || {};
			var count = cfg.count || 12;
			var section = cfg.section;
			var values = [];
			var i, value;

			for (i = 0; i < count; ++i) {
				value = MONTHS[Math.ceil(i) % 12];
				values.push(value.substring(0, section));
			}

			return values;
		},

		color: function(index) {
			return COLORS[index % COLORS.length];
		},

		transparentize: function(color, opacity) {
			var alpha = opacity === undefined ? 0.5 : 1 - opacity;
			return Color(color).alpha(alpha).rgbString();
		}
	};

	// DEPRECATED
	window.randomScalingFactor = function() {
		return Math.round(Samples.utils.rand(-100, 100));
	};

	// INITIALIZATION

	Samples.utils.srand(Date.now());

	// Google Analytics
	/* eslint-disable */
	if (document.location.hostname.match(/^(www\.)?chartjs\.org$/)) {
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		ga('create', 'UA-28909194-3', 'auto');
		ga('send', 'pageview');
	}
	/* eslint-enable */

}(this));

if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
}

$(function() 
{
    var ctx = $('#burnupchart');
	console.log(data);
	
    dataset1 = 
    {
        type: 'line',
        label: 'Dataset 1',
		fill: false,
        backgroundColor: window.chartColors.green,
		pointRadius: 1,
        data: [
			1,
            2,
            4,
            4,
            6,
            8,
            9,
            10
        ]
	};
    dataset2 = 
    {
        type: 'line',
        label: 'Dataset 2',
        backgroundColor: window.chartColors.red,
        data: [
            1,
            2,
            4,
            4,
            6,
            8,
            9,
            10
        ],
        borderColor: 'white',
        borderWidth: 1
    };
    dataset3 = 
    {
        type: 'line',
        label: 'Dataset 3',
        borderColor: window.chartColors.blue,
        borderWidth: 2,
        fill: true,
        data: [
			1,
            2,
            4,
            4,
            6,
            8,
            9,
            10
        ]
    }

    var chartData = 
	{
			labels: [],
			datasets: [dataset1]
	};
	
	i=0;
	j=0;
	for(var date in data.data){
		if(j < 50)
		{
			j++;
			continue;
		}
		chartData.labels[i]=date;
		dataset1.data[i] = data.data[date].tv;
		i++;
		if(i==50)
			break;
	}
	console.log(dataset1);
	
	for(var i=0;i<50;i++)
	{
		
		dataset2.data[i] = i/2;
	}
	ticks = {
					min: 0,
					max: 200,
					stepSize: 5,
					autoskip: true,
					autoSkipPadding: 5
				};
	ticks2 = {
					min: 0,
					max: 90,
					stepSize: 5,
					autoskip: false,
					autoSkipPadding: 30
				};

    options={
        responsive: true,
		drawBorder: true,
		showLines: true,
		elements: {
            line: {
                tension: 0 // disables bezier curves
            }
        },
        title: {
            display: true,
            text: 'Chart.js Combo Bar Line Chart'
        },
        tooltips: {
            mode: 'index',
            intersect: false
        },
		scales: {
			xAxes: [{
				gridLines: {display: true},
				scaleLabel: {
					display: true,
					labelString: 'probability'
				},
				ticks : {
					min: 0,
					max: 20,
					stepSize: 5,
					autoskip: false,
					autoSkipPadding: 30
				}
				
			}],
			yAxes: [{
				gridLines: {display: true},
				scaleLabel: {
					display: true,
					labelString: 'probability'
				}
			}]
		},
		legend: {
            display: true,
            position: 'bottom',
            labels: {
                fontColor: '#f00'
            }
        }

    }
    var myChart = new Chart(ctx, 
    {
        type: 'bar',
        data: chartData,
        options: options
    });
});

@endsection
