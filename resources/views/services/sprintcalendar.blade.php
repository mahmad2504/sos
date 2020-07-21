<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>EPS Sprint Calendar</title>
		<link rel="stylesheet" href="{{ asset('rmo/rmo.css') }}" />
    <style>
		.flex-container {
			height: 100%;
			padding: 0;
			margin: 0;
			display: -webkit-box;
			display: -moz-box;
			display: -ms-flexbox;
			display: -webkit-flex;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.row {
			width: auto;
			
		}
		.flex-item {
			text-align: center;
		}

    </style>
    </head>
    <body>
		<div style="overflow-x: scroll;">
		
			<div id="table"></div>
		</div>
	</div>
    </body>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	<script src="{{ asset('rmo/rmo.js') }}" ></script>
	<script>
	//define data
	var tabledata = @json($tabledata);
	console.log(tabledata.months);
	$(document).ready(function()
	{
		console.log("Showing sprint table");
		var rmo = new Rmo(tabledata);	
		rmo.Show("table");
	});
	
	</script>
</html>
