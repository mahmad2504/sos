@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />

@endsection
@section('style')
.pill {font-size:.7rem;box-shadow: 0 0 2px 1px rgba(1, 0, 0, 0.5)}


bodydd {
    width: 90%;
    margin: 40px auto;
    font-family: 'trebuchet MS', 'Lucida sans', Arial;
    font-size: 14px;
    color: #444;
	background-color: #eee;f00
}

table {
	table-layout: fixed;
	font-family: 'trebuchet MS', 'Lucida sans', Arial;
    *border-collapse: collapse; /* IE7 and lower */
    border-spacing: 1;
    width: 100%;    
	white-space: nowrap;
	background-color: #fff;
	font-size: 12px;
	box-shadow: 10px 5px 5px grey;
}

.bordered {
    border: solid #ccc 1px;
    -moz-border-radius: 6px;
    -webkit-border-radius: 6px;
    border-radius: 6px;
    -webkit-box-shadow: 0 1px 1px #ccc; 
    -moz-box-shadow: 0 1px 1px #ccc; 
    box-shadow: 5px 3px 3px grey;       
}

.bordered tr:hover {
    background: #fbf8e9;
    -o-transition: all 0.1s ease-in-out;
    -webkit-transition: all 0.1s ease-in-out;
    -moz-transition: all 0.1s ease-in-out;
    -ms-transition: all 0.1s ease-in-out;
    transition: all 0.1s ease-in-out;     
}  


.bordered tr th:nth-child(1){/* Description */
       width: 25%;
     }
.bordered tr th:nth-child(2){/* Baseline */
       width: 8%;
     }
.bordered tr th:nth-child(3){/* Current */
       width: 8%;
     }
.bordered tr th:nth-child(4){/* Expected */
       width: 8%;
     }
.bordered tr th:nth-child(5){/* Status */
       width: 8%;
     }
	 
.bordered tr th:nth-child(6){/* Baseline EAC*/
       width: 8%;
     }
.bordered tr th:nth-child(7){/* EAC */
       width: 8%;
     }
.bordered tr th:nth-child(8){/* Remaning */
       width: 8%;
     }
.bordered tr th:nth-child(9){ /* % Complete */
       width: 8%;
     }
.bordered tr th:nth-child(10){ /* State */
       width: 5%;
     }
.bordered tr th:nth-child(11){ /* % Tools */
       width: 0%;
     }
	 
.bordered td, .bordered th {
    border-left: 1px solid #ccc;
    border-top: 1px solid #ccc;
    padding: 10px;
    text-align: left;  
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;	
}

.bordered th {
    background-image: -webkit-gradient(linear, left top, left bottom, from(#b0e0e6), to(#c3e8ec));
    background-image: -webkit-linear-gradient(top, #b0e0e6, #c3e8ec);
    background-image:    -moz-linear-gradient(top, #b0e0e6, #c3e8ec);
    background-image:     -ms-linear-gradient(top, #b0e0e6, #c3e8ec);
    background-image:      -o-linear-gradient(top, #b0e0e6, #c3e8ec);
    background-image:         linear-gradient(top, #b0e0e6, #c3e8ec);
    -webkit-box-shadow: 0 1px 0 rgba(255,255,255,.8) inset; 
    -moz-box-shadow:0 1px 0 rgba(255,255,255,.8) inset;  
    box-shadow: 0 1px 0 rgba(255,255,255,.8) inset;        
    border-top: none;
    text-shadow: 0 1px 0 rgba(255,255,255,.5); 
}

.bordered td:first-child, .bordered th:first-child {
    border-left: none;
}

.bordered th:first-child {
    -moz-border-radius: 6px 0 0 0;
    -webkit-border-radius: 6px 0 0 0;
    border-radius: 6px 0 0 0;
}

.bordered th:last-child {
    -moz-border-radius: 0 6px 0 0;
    -webkit-border-radius: 0 6px 0 0;
    border-radius: 0 6px 0 0;
}

.bordered th:only-child{
    -moz-border-radius: 6px 6px 0 0;
    -webkit-border-radius: 6px 6px 0 0;
    border-radius: 6px 6px 0 0;
}

.bordered tr:last-child td:first-child {
    -moz-border-radius: 0 0 0 6px;
    -webkit-border-radius: 0 0 0 6px;
    border-radius: 0 0 0 6px;
}

.bordered tr:last-child td:last-child {
    -moz-border-radius: 0 0 6px 0;
    -webkit-border-radius: 0 0 6px 0;
    border-radius: 0 0 6px 0;
}



/*----------------------*/

.zebra td, .zebra th {
    padding: 10px;
    border-bottom: 1px solid #f2f2f2;    
}

.zebra tbody tr:nth-child(even) {
    background: #f5f5f5;
    -webkit-box-shadow: 0 1px 0 rgba(255,255,255,.8) inset; 
    -moz-box-shadow:0 1px 0 rgba(255,255,255,.8) inset;  
    box-shadow: 0 1px 0 rgba(255,255,255,.8) inset;        
}

.zebra th {
    text-align: left;
    text-shadow: 0 1px 0 rgba(255,255,255,.5); 
    border-bottom: 1px solid #ccc;
    background-color: #eee;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#f5f5f5), to(#eee));
    background-image: -webkit-linear-gradient(top, #f5f5f5, #eee);
    background-image:    -moz-linear-gradient(top, #f5f5f5, #eee);
    background-image:     -ms-linear-gradient(top, #f5f5f5, #eee);
    background-image:      -o-linear-gradient(top, #f5f5f5, #eee); 
    background-image:         linear-gradient(top, #f5f5f5, #eee);
}

.zebra th:first-child {
    -moz-border-radius: 6px 0 0 0;
    -webkit-border-radius: 6px 0 0 0;
    border-radius: 6px 0 0 0;  
}

.zebra th:last-child {
    -moz-border-radius: 0 6px 0 0;
    -webkit-border-radius: 0 6px 0 0;
    border-radius: 0 6px 0 0;
}

.zebra th:only-child{
    -moz-border-radius: 6px 6px 0 0;
    -webkit-border-radius: 6px 6px 0 0;
    border-radius: 6px 6px 0 0;
}

.zebra tfoot td {
    border-bottom: 0;
    border-top: 1px solid #fff;
    background-color: #f1f1f1;  
}

.zebra tfoot td:first-child {
    -moz-border-radius: 0 0 0 6px;
    -webkit-border-radius: 0 0 0 6px;
    border-radius: 0 0 0 6px;
}

.zebra tfoot td:last-child {
    -moz-border-radius: 0 0 6px 0;
    -webkit-border-radius: 0 0 6px 0;
    border-radius: 0 0 6px 0;
}

.zebra tfoot td:only-child{
    -moz-border-radius: 0 0 6px 6px;
    -webkit-border-radius: 0 0 6px 6px
    border-radius: 0 0 6px 6px
}
  

@endsection
@section('content')
<div id="container" style="width:95%; margin-left: auto; margin-right: auto; display:block" class="center">
	<div id="table"></div>
</div>
<script src="{{ asset('js/msc-script.js') }}" ></script>

@endsection
@section('script')
var user = @json($user);
var project =  @json($project);
var isloggedin = {{$isloggedin}};
var data = @json($data);
var key = '{{$key}}';
var gantturl = '{{route('showgantt',[$user->name,$project->name])}}';
var burnupurl = '{{route('showwburnupchart',[$user->name,$project->name])}}';

'use strict';
console.log(gantturl);
if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
}
function CreateaCell(row,col,text)
{
	j=col;
	i=row;
	var cell = document.createElement("td");
	cell.setAttribute("id","id_"+(i-1)+"_"+j);

	var cellText = document.createElement('span');
	cellText.innerHTML = text;
	cell.appendChild(cellText);
	return cell;
}

function drawTable(anchor,data) 
{
	// get the reference for the body
	var div1 = document.getElementById(anchor);
	// creates a <table> element
	var tbl = document.createElement("table");
	tbl.setAttribute('class', 'bordered');
	tbl.setAttribute('border', '1');
	div1.appendChild(tbl);
	// create header
	var datarow = data[0];
	var row = document.createElement("tr");
	for (var j = 0; j < datarow.length; j++) 
	{
		var cell = document.createElement("th");
		var cellText = document.createTextNode(datarow[j]);
		cell.appendChild(cellText);
		row.appendChild(cell);
	}           
	tbl.appendChild(row); // add the row to the end of the table body
		
	// creating rows
	
	for (var i = 1; i < data.length; i++) 
	{
		var datarow = data[i];
	
		var row = document.createElement("tr");
		row.setAttribute("id","row_"+(i-1));

	
		var weekdate = null;
		var datestr =  null;
		
		
		summary = datarow[0];
		baseline_end = datarow[1];
		current_end = datarow[2];
		forecast_end = datarow[3];
		baseline_eac = datarow[4];
		current_eac = datarow[5];
	

		remaining_estimate = datarow[6];
		progress = datarow[7];
		status = datarow[8];
		cv = datarow[9];
		rv = datarow[10];
		key = datarow[11];
		console.log(key);

		status_message = null;

		if(status != 'RESOLVED')
		{
			status_badge = 'badge-success';
			status_message = "On Track";
			if( IsVleocityLow(cv,rv) == 1)
			{
				status_badge = 'badge-warning';
				status_message = "Velocity";
			}
			if(forecast_end > current_end)
			{
				status_badge = 'badge-danger';
				status_message = "&nbsp&nbspDelay&nbsp&nbsp";
			}
		}
		else
		{
			status_badge = 'badge-light';
			status_message = "Complete";
		}


		units = " Days";
		if(project.estimation == 0)// Story points
			units = " Points";
		

		row.appendChild(CreateaCell(i,j++,summary));
		baseline_end = ConvertDateToString(baseline_end);
		row.appendChild(CreateaCell(i,j++,baseline_end));

		current_end = ConvertDateToString(current_end);
		row.appendChild(CreateaCell(i,j++,current_end));

		forecast_end = ConvertDateToString(forecast_end);
		row.appendChild(CreateaCell(i,j++,forecast_end));

		row.appendChild(CreateaCell(i,j++,Round(baseline_eac)+units));
		if(current_eac == 0)
			row.appendChild(CreateaCell(i,j++,'None'));
		else
			row.appendChild(CreateaCell(i,j++,Round(current_eac)+units));
		if(remaining_estimate > 0)
			row.appendChild(CreateaCell(i,j++,Round(remaining_estimate)+units));
		else
			row.appendChild(CreateaCell(i,j++,''));
		if(progress > 0)
			row.appendChild(CreateaCell(i,j++,Round(progress)+'%'));
		else
			row.appendChild(CreateaCell(i,j++,''));

		if(status_message != null)
			row.appendChild(CreateaCell(i,j++,'<span class="badge '+status_badge+'">'+status_message+'</span>'));
		else
			row.appendChild(CreateaCell(i,j++,'Completed'));
		tbl.appendChild(row);

		if(status == 'RESOLVED')
		{
			document.getElementById("row_"+(i-1)).style.color = 'lightgrey';
		}
		else if(status == 'INPROGRESS')	
			document.getElementById("row_"+(i-1)).style.color = 'green';
		url_gantt = "<a href='"+gantturl+"?key="+key+"'>"+'<i class="fas fa-tasks"></i>'+"</a>";
		url_burnup = "<a href='"+burnupurl+"?key="+key+"'>"+'<i class="fas fa-chart-area"></i>'+"</a>";
		row.appendChild(CreateaCell(i,j++,url_gantt+"&nbsp"+url_burnup));
	
	}

	/*	for (var j = 0; j < datarow.length; j++) 
		{
			var cell = document.createElement("td");
			cell.setAttribute("id","id_"+(i-1)+"_"+j);
			
			if((j==2)||(j==3)||(j==4))
			{
				weekdate = ConvertDateFormat(data[i][j]);
				datestr = ConvertDateToString(data[i][j]);
			}
			
			if((j==5)||(j==6)||(j==7))
			{
				if(data[i][j].length!=0)
				{
					var text = "<span>"+data[i][j]*8+" Hours</span><p style='margin-top:0;color:grey;font-size:10px;'>"+data[i][j]+" days</p>";
			
					data[i][j] = text;
				}
	
			}
			
			if(j==10)
			{
				//delay risk done ontrack
				var img = document.createElement ("img");
				img.width = "80";
				img.height = "20";
				if(data[i][j] == 'ontrack')
					img.setAttribute ("src", '/../dgantt/modules/milestone/assets/ontrack.png');
				
				if(data[i][j] == 'done')
					img.setAttribute ("src", '/../dgantt/modules/milestone/assets/delivered.png');
				
				if(data[i][j] == 'risk')
					img.setAttribute ("src", '/../dgantt/modules/milestone/assets/issues.png');
				
				if(data[i][j] == 'delay')
					img.setAttribute ("src", '/../dgantt/modules/milestone/assets/delayed.png');
				
				cell.appendChild(img);
			}
			else
			{
				var cellText = document.createElement('span');
				if(weekdate != null)
				{
					cellText.innerHTML = "<span>"+weekdate+"</span><p style='margin-top:0;color:grey;font-size:10px;'>"+datestr+"</p>";
					weekdate = null;
				}
				else
					cellText.innerHTML = data[i][j];
				cell.appendChild(cellText);
			}
			
			if(data[i][j] == 'RESOLVED')	
			{
				//backgroundColor = 'Gainsboro';
				color = 'Grey';
			}
			if(data[i][j] == 'INPROGRESS')	
			{
				//backgroundColor = '#ecf5c1';
				color = 'Green';
			}
			
			row.appendChild(cell);
		}           
		tbl.appendChild(row); // add the row to the end of the table body
		if(color != null)
		{
			document.getElementById("row_"+(i-1)).style.backgroundColor = backgroundColor;
			document.getElementById("row_"+(i-1)).style.color = color;
		}
	}*/

}

$(function() 
{
	console.log(data);
	
	console.log(project.estimation);
	drawTable("table",data);
   
});
@endsection