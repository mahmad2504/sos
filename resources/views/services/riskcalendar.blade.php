<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>EPS Risks Calendar</title>
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
	var tickets = @json($tickets);
    var url = "{{$url}}";
	$(document).ready(function()
	{
		//console.log("Showing sprint table");
		var rmo = new Rmo(tabledata);	
		rmo.Show("table");
		for(var i=0;i<tickets.length;i++)
		{
			var ticket = tickets[i];
			var row = rmo.GenerateWeekRowT2(ticket.key);
			rmo.AppendRow(row);
			var id = '#'+ticket.key+"1";
			var jurl = url + "/browse/"+ ticket.key;
			$(id).html('<a href="'+jurl+'">'+ticket.key+'</a>');
			$(id).html('&nbsp&nbsp&nbsp'+ticket.summary);
			$(id).attr('title',ticket.summary);
			
			id = '#'+ticket.key+"2";
			$(id).html('&nbsp'+ticket.assignee.displayName+'&nbsp');
			id = '#'+ticket.key+"_"+ticket.dueweek;
			$(id).css('font-size','15px');
			
			
			if((ticket.statuscategory != 'RESOLVED')&&(ticket.expired > 0))
				$(id).html('<a style="color:red;" href="'+jurl+'">'+ticket.dueday+'</a>');
			else
				$(id).html('<a style="color:blue;" href="'+jurl+'">'+ticket.dueday+'</a>');
			
			
			$(id).attr('title',ticket.key);
			
			id = '#'+ticket.key+"3";
			console.log(ticket);
			for(var j=0;j<ticket.fixVersions.length;j++)
			{
				$(id).html('&nbsp'+ticket.fixVersions[j]+'&nbsp');
				break;
			}
					
			
			
		}
		$('#r5c2').html('Assignee');
	});
	
	</script>
</html>
