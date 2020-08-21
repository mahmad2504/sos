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
			<select class="form-control-sm" id="versions" name="versions">
				@for($i=0;$i<count($versions);$i++)
					@if (strcmp($versions[$i],'all')==0)
						<option value="i" selected="selected">{{$versions[$i]}}</option>
					@else
						<option value="i">{{$versions[$i]}}</option>
					@endif
				@endfor
			</select>			
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
	var versions = @json($versions);
    var url = "{{$url}}";
	var rows = [];
	$(document).ready(function()
	{
		//console.log("Showing sprint table");
		
		$('#versions').on('change', '', function (e) 
		{
			var optionSelected = $('#versions').prop('selectedIndex');
			console.log(versions[optionSelected]);
			if(optionSelected == 'all')
			{
				for(var row in rows)
				{
					rows[row].show();
				}
			}
			else
			{
				for(var row in rows)
				{
					if(rows[row].ticket.fixVersions.length > 0)
					{
						if(versions[optionSelected] == rows[row].ticket.fixVersions[0])
							rows[row].show();
						else
							rows[row].hide();
					}
					else
							rows[row].hide();
				}
			}
			//milestone = milestones[optionSelected];
			//url = url+"/"+milestone.key;
			//ShowLoading();
			//window.location.replace(url);
	
		});
	
		var rmo = new Rmo(tabledata);	
		rmo.Show("table");
		for(var i=0;i<tickets.length;i++)
		{
			var ticket = tickets[i];
			var row = rmo.GenerateWeekRowT2(ticket.key);
			row.ticket = ticket;
			rmo.AppendRow(row);
			rows.push(row);
			
			
			
			var id = '#'+ticket.key+"1";
			var jurl = url + "/browse/"+ ticket.key;
			$(id).html('<a href="'+jurl+'">'+ticket.key+'</a>');
			$(id).html('&nbsp&nbsp&nbsp'+ticket.summary);
			$(id).attr('title',ticket.summary);
			
			
			if(ticket.statuscategory == 'RESOLVED')
				$(id).css('color','grey');
			else
			{
				$(id).css('font-weight','bold');
				if(ticket.delayed>0)
					$(id).css('color','red');
				else
					$(id).css('color','green');
			}
			
			id = '#'+ticket.key+"2";
			if(ticket.assignee.displayName !== undefined)
				$(id).html('&nbsp'+ticket.assignee.displayName+'&nbsp');
			id = '#'+ticket.key+"_"+ticket.dueweek;
			//$(id).css('font-size','12px');
			
			var message = '';
			if(ticket.statuscategory != 'RESOLVED')
			{
				if(ticket.delayed>0)
				{
					message = " Delayed by "+ticket.delayed+" days";
					$(id).css('background-color','orange');
				}
				else
				{
			
				}
				$(id).html('<a style="" href="'+jurl+'">'+ticket.dueday+'</a>');
			}
			else
			{
				if(ticket.delayed>0)
				{
					color = 'red';
					message = " Resolved "+ticket.delayed+" days after its due date";
				}
				else
				{
					color= 'white';
				}
				$(id).css('background-color','green');
				$(id).html('<a style="color:'+color+';" href="'+jurl+'">'+ticket.dueday+'</a>');
			}
			
			$(id).attr('title',ticket.key+message);
			
			id = '#'+ticket.key+"3";
			
			for(var j=0;j<ticket.fixVersions.length;j++)
			{
				$(id).html('&nbsp'+ticket.fixVersions[j]+'&nbsp');
				break;
			}
					
			
			
		}
		$('#r5c2').html('Assignee');
		$('#r5c3').html('Product');
		$('#r5c1').html('Details');
		console.log(rows);
	});
	
	</script>
</html>
